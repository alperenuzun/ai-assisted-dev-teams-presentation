# API Design Patterns

## RESTful API Standards

### HTTP Methods

| Method | Purpose | Idempotent | Safe |
|--------|---------|-----------|------|
| GET | Retrieve resources | Yes | Yes |
| POST | Create resources | No | No |
| PUT | Update/Replace entire resource | Yes | No |
| PATCH | Partial update | No | No |
| DELETE | Remove resource | Yes | No |

### URL Structure

```
# Resources (use plural nouns)
GET    /api/v1/users           # List all users
GET    /api/v1/users/:id       # Get specific user
POST   /api/v1/users           # Create new user
PUT    /api/v1/users/:id       # Replace user
PATCH  /api/v1/users/:id       # Update user
DELETE /api/v1/users/:id       # Delete user

# Nested resources
GET    /api/v1/users/:id/posts          # Get user's posts
POST   /api/v1/users/:id/posts          # Create post for user
GET    /api/v1/users/:id/posts/:postId  # Get specific post

# Filtering, sorting, pagination
GET    /api/v1/users?status=active&sort=name&page=2&limit=20
```

### HTTP Status Codes

#### Success (2xx)
- **200 OK**: Request succeeded (GET, PUT, PATCH, DELETE)
- **201 Created**: Resource created (POST)
- **204 No Content**: Success with no response body (DELETE)

#### Client Errors (4xx)
- **400 Bad Request**: Invalid request format
- **401 Unauthorized**: Authentication required
- **403 Forbidden**: Authentication succeeded but not authorized
- **404 Not Found**: Resource doesn't exist
- **409 Conflict**: Conflict with current state (duplicate)
- **422 Unprocessable Entity**: Validation failed
- **429 Too Many Requests**: Rate limit exceeded

#### Server Errors (5xx)
- **500 Internal Server Error**: Generic server error
- **502 Bad Gateway**: Upstream service error
- **503 Service Unavailable**: Service temporarily unavailable

## Response Format

### Success Response
```typescript
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "meta": {
    "timestamp": "2024-01-10T10:30:00Z",
    "version": "v1"
  }
}
```

### List Response with Pagination
```typescript
{
  "success": true,
  "data": [
    { "id": 1, "name": "User 1" },
    { "id": 2, "name": "User 2" }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 100,
    "totalPages": 5,
    "hasNext": true,
    "hasPrev": false
  },
  "meta": {
    "timestamp": "2024-01-10T10:30:00Z"
  }
}
```

### Error Response
```typescript
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": [
      {
        "field": "email",
        "message": "Invalid email format"
      },
      {
        "field": "age",
        "message": "Must be at least 18"
      }
    ]
  },
  "meta": {
    "timestamp": "2024-01-10T10:30:00Z",
    "requestId": "abc-123-def"
  }
}
```

## Authentication Patterns

### JWT Authentication
```typescript
// Login endpoint
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}

// Response
{
  "success": true,
  "data": {
    "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 3600,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}

// Using access token
GET /api/v1/users/me
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Token Refresh
```typescript
POST /api/v1/auth/refresh
{
  "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}

// Response
{
  "success": true,
  "data": {
    "accessToken": "new-access-token...",
    "expiresIn": 3600
  }
}
```

## Validation Patterns

### Input Validation
```typescript
import Joi from 'joi';

const userSchema = Joi.object({
  name: Joi.string().min(2).max(50).required(),
  email: Joi.string().email().required(),
  age: Joi.number().integer().min(18).max(120),
  role: Joi.string().valid('user', 'admin', 'moderator'),
  address: Joi.object({
    street: Joi.string(),
    city: Joi.string(),
    country: Joi.string()
  }).optional()
});

// Validation middleware
export const validate = (schema: Joi.Schema) => {
  return (req: Request, res: Response, next: NextFunction) => {
    const { error, value } = schema.validate(req.body, {
      abortEarly: false,
      stripUnknown: true
    });

    if (error) {
      const details = error.details.map(detail => ({
        field: detail.path.join('.'),
        message: detail.message
      }));

      return res.status(422).json({
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'Invalid input data',
          details
        }
      });
    }

    req.body = value;
    next();
  };
};
```

## Pagination Patterns

### Offset-based Pagination
```typescript
interface PaginationQuery {
  page?: number;
  limit?: number;
}

async function getUsers(query: PaginationQuery) {
  const page = query.page || 1;
  const limit = query.limit || 20;
  const offset = (page - 1) * limit;

  const [users, total] = await Promise.all([
    db.users.find().skip(offset).limit(limit),
    db.users.countDocuments()
  ]);

  return {
    data: users,
    pagination: {
      page,
      limit,
      total,
      totalPages: Math.ceil(total / limit),
      hasNext: page * limit < total,
      hasPrev: page > 1
    }
  };
}
```

### Cursor-based Pagination
```typescript
interface CursorQuery {
  cursor?: string;
  limit?: number;
}

async function getUsers(query: CursorQuery) {
  const limit = query.limit || 20;
  const cursor = query.cursor ? parseInt(query.cursor) : 0;

  const users = await db.users
    .find({ id: { $gt: cursor } })
    .limit(limit + 1)
    .sort({ id: 1 });

  const hasNext = users.length > limit;
  const data = hasNext ? users.slice(0, -1) : users;
  const nextCursor = hasNext ? data[data.length - 1].id : null;

  return {
    data,
    pagination: {
      cursor: nextCursor,
      hasNext
    }
  };
}
```

## Filtering & Sorting

### Query Parameters
```typescript
GET /api/v1/users?status=active&role=admin&sort=-createdAt&fields=name,email

interface QueryParams {
  status?: string;
  role?: string;
  sort?: string;
  fields?: string;
  search?: string;
}

async function getUsers(query: QueryParams) {
  const filter: any = {};

  // Filtering
  if (query.status) filter.status = query.status;
  if (query.role) filter.role = query.role;

  // Search
  if (query.search) {
    filter.$or = [
      { name: { $regex: query.search, $options: 'i' } },
      { email: { $regex: query.search, $options: 'i' } }
    ];
  }

  // Sorting (- prefix for descending)
  const sort: any = {};
  if (query.sort) {
    const sortFields = query.sort.split(',');
    sortFields.forEach(field => {
      if (field.startsWith('-')) {
        sort[field.substring(1)] = -1;
      } else {
        sort[field] = 1;
      }
    });
  }

  // Field selection
  const projection = query.fields
    ? query.fields.split(',').reduce((acc, field) => {
        acc[field] = 1;
        return acc;
      }, {} as any)
    : undefined;

  return db.users.find(filter, { projection }).sort(sort);
}
```

## Error Handling Patterns

### Centralized Error Handler
```typescript
class AppError extends Error {
  constructor(
    public statusCode: number,
    public message: string,
    public code: string,
    public isOperational = true
  ) {
    super(message);
    Object.setPrototypeOf(this, AppError.prototype);
  }
}

// Error handling middleware
export const errorHandler = (
  err: Error | AppError,
  req: Request,
  res: Response,
  next: NextFunction
) => {
  if (err instanceof AppError) {
    return res.status(err.statusCode).json({
      success: false,
      error: {
        code: err.code,
        message: err.message
      }
    });
  }

  // Unexpected errors
  console.error('Unexpected error:', err);
  res.status(500).json({
    success: false,
    error: {
      code: 'INTERNAL_ERROR',
      message: 'An unexpected error occurred'
    }
  });
};

// Usage
throw new AppError(404, 'User not found', 'USER_NOT_FOUND');
```

## Rate Limiting

### Express Rate Limit
```typescript
import rateLimit from 'express-rate-limit';

// General API rate limit
const apiLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // Limit each IP to 100 requests per windowMs
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'Too many requests, please try again later'
    }
  }
});

// Strict rate limit for auth endpoints
const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 5,
  skipSuccessfulRequests: true
});

app.use('/api/', apiLimiter);
app.use('/api/auth/', authLimiter);
```

## Versioning

### URL Versioning
```typescript
// Version in URL (recommended)
app.use('/api/v1', v1Router);
app.use('/api/v2', v2Router);

GET /api/v1/users
GET /api/v2/users
```

### Header Versioning
```typescript
// Version in header
app.use('/api', (req, res, next) => {
  const version = req.header('API-Version') || '1.0';
  // Route to appropriate version
  next();
});

GET /api/users
Headers: API-Version: 2.0
```

## Caching

### Response Caching
```typescript
// Cache-Control headers
app.get('/api/v1/users/:id', (req, res) => {
  res.set('Cache-Control', 'public, max-age=300'); // 5 minutes
  // Return user data
});

// ETag support
app.get('/api/v1/users/:id', async (req, res) => {
  const user = await getUserById(req.params.id);
  const etag = generateETag(user);

  if (req.header('If-None-Match') === etag) {
    return res.status(304).end();
  }

  res.set('ETag', etag);
  res.json(user);
});
```

## Webhooks

### Webhook Pattern
```typescript
interface WebhookPayload {
  event: string;
  data: any;
  timestamp: string;
  signature: string;
}

// Sending webhooks
async function sendWebhook(url: string, event: string, data: any) {
  const payload: WebhookPayload = {
    event,
    data,
    timestamp: new Date().toISOString(),
    signature: generateSignature(data)
  };

  try {
    await axios.post(url, payload, {
      headers: {
        'Content-Type': 'application/json',
        'X-Webhook-Signature': payload.signature
      },
      timeout: 5000
    });
  } catch (error) {
    // Retry logic or queue for later
  }
}

// Receiving webhooks
app.post('/webhooks/service-name', (req, res) => {
  const signature = req.header('X-Webhook-Signature');

  if (!verifySignature(req.body, signature)) {
    return res.status(401).json({ error: 'Invalid signature' });
  }

  // Process webhook
  processWebhook(req.body);

  res.status(200).json({ received: true });
});
```

## GraphQL Patterns

### Query Structure
```graphql
type Query {
  user(id: ID!): User
  users(
    filter: UserFilter
    sort: UserSort
    pagination: PaginationInput
  ): UserConnection!
}

type User {
  id: ID!
  name: String!
  email: String!
  posts: [Post!]!
}

input UserFilter {
  status: UserStatus
  role: UserRole
  search: String
}

type UserConnection {
  nodes: [User!]!
  pageInfo: PageInfo!
  totalCount: Int!
}
```

## Real-time Updates

### WebSocket Pattern
```typescript
import { WebSocketServer } from 'ws';

const wss = new WebSocketServer({ port: 8080 });

wss.on('connection', (ws, req) => {
  // Authentication
  const token = new URL(req.url, 'ws://localhost').searchParams.get('token');
  const user = verifyToken(token);

  if (!user) {
    ws.close(4001, 'Unauthorized');
    return;
  }

  // Subscribe to user-specific events
  ws.on('message', (data) => {
    const message = JSON.parse(data.toString());

    switch (message.type) {
      case 'subscribe':
        subscribeToChannel(ws, message.channel);
        break;
      case 'unsubscribe':
        unsubscribeFromChannel(ws, message.channel);
        break;
    }
  });

  // Send updates
  ws.send(JSON.stringify({
    type: 'notification',
    data: { message: 'Connected successfully' }
  }));
});

// Broadcasting updates
function broadcastToChannel(channel: string, data: any) {
  wss.clients.forEach(client => {
    if (client.readyState === WebSocket.OPEN && isSubscribed(client, channel)) {
      client.send(JSON.stringify(data));
    }
  });
}
```

## API Documentation

### OpenAPI/Swagger Example
```typescript
/**
 * @swagger
 * /api/v1/users:
 *   get:
 *     summary: Get all users
 *     tags: [Users]
 *     parameters:
 *       - in: query
 *         name: page
 *         schema:
 *           type: integer
 *         description: Page number
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *         description: Items per page
 *     responses:
 *       200:
 *         description: List of users
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                 data:
 *                   type: array
 *                   items:
 *                     $ref: '#/components/schemas/User'
 *       401:
 *         description: Unauthorized
 */
```
