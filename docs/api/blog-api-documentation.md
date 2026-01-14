# AI Blog API Documentation

## Overview

This is a Symfony 7.3 DDD (Domain-Driven Design) blog application REST API. The API follows CQRS patterns with Symfony Messenger and provides endpoints for managing blog posts, users, comments, and translations.

**Base URL**: `http://localhost:8081/api`

---

## Architecture

### Technology Stack
- **Framework**: Symfony 7.3
- **Language**: PHP 8.3
- **Database**: PostgreSQL 16
- **Authentication**: JWT (LexikJWTAuthenticationBundle)
- **Pattern**: Domain-Driven Design with CQRS

### Data Flow
```
Request → Controller → Command/Query → Handler → Repository → Database
```

### Components Structure
```
src/Api/
├── Domain/           # Pure business logic
│   ├── Entity/       # Aggregate roots
│   ├── ValueObject/  # Immutable value types
│   └── Repository/   # Interface definitions
├── Application/      # Use cases (CQRS)
│   ├── Command/      # Write operations
│   └── Query/        # Read operations
└── Infrastructure/   # Framework integrations
    ├── Controller/   # REST endpoints
    └── Persistence/  # Doctrine implementations
```

---

## Authentication

The API uses JWT (JSON Web Tokens) for authentication.

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@blog.com",
  "password": "password"
}
```

**Response**:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Using the Token

Include the JWT token in the `Authorization` header:
```
Authorization: Bearer <your-token>
```

### Default Users

| Email | Password | Role |
|-------|----------|------|
| admin@blog.com | password | ROLE_ADMIN |
| user@blog.com | password | ROLE_USER |

---

## Endpoints

### Posts API

#### List All Posts

```http
GET /api/posts
```

**Authentication**: Not required

**Response**:
```json
[
  {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "My First Post",
    "content": "This is the content of my first blog post...",
    "status": "published",
    "authorId": "550e8400-e29b-41d4-a716-446655440001",
    "createdAt": "2024-01-15 10:30:00",
    "publishedAt": "2024-01-15 11:00:00"
  }
]
```

---

#### Get Single Post

```http
GET /api/posts/{id}
```

**Authentication**: Not required

**Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| id | UUID | Post unique identifier |

**Response (200)**:
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "title": "My First Post",
  "content": "This is the content of my first blog post...",
  "status": "published",
  "authorId": "550e8400-e29b-41d4-a716-446655440001",
  "createdAt": "2024-01-15 10:30:00",
  "publishedAt": "2024-01-15 11:00:00"
}
```

**Response (404)**:
```json
{
  "error": "Post not found"
}
```

---

#### Create Post

```http
POST /api/posts
Content-Type: application/json
Authorization: Bearer <token>
```

**Authentication**: Required

**Request Body**:
```json
{
  "title": "My New Post Title",
  "content": "The full content of my blog post..."
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | Yes | Min 3 characters |
| content | string | Yes | Min 10 characters |

**Response (201)**:
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440002"
}
```

**Response (400)**:
```json
{
  "error": "Missing title or content"
}
```

**Response (401)**:
```json
{
  "error": "Authentication required"
}
```

---

#### Publish Post

```http
POST /api/posts/{id}/publish
Authorization: Bearer <token>
```

**Authentication**: Required

**Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| id | UUID | Post unique identifier |

**Response (200)**:
```json
{
  "message": "Post published successfully"
}
```

**Response (400)**:
```json
{
  "error": "Post is already published"
}
```

---

### Comments API

#### List Comments for Post

```http
GET /api/posts/{postId}/comments
```

**Authentication**: Not required

**Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| postId | UUID | Post unique identifier |

**Response (200)**:
```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440010",
      "content": "Great post!",
      "postId": "550e8400-e29b-41d4-a716-446655440000",
      "authorId": "550e8400-e29b-41d4-a716-446655440001",
      "createdAt": "2024-01-15 12:00:00"
    }
  ]
}
```

---

#### Create Comment

```http
POST /api/posts/{postId}/comments
Content-Type: application/json
Authorization: Bearer <token>
```

**Authentication**: Required

**Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| postId | UUID | Post unique identifier |

**Request Body**:
```json
{
  "content": "This is my comment on this post."
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| content | string | Yes | Non-empty string |

**Response (201)**:
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440011",
  "message": "Comment created successfully"
}
```

**Response (400)**:
```json
{
  "error": "Content is required"
}
```

**Response (401)**:
```json
{
  "error": "Authentication required"
}
```

---

### User Registration API

#### Register New User

```http
POST /api/register
Content-Type: application/json
```

**Authentication**: Not required

**Request Body**:
```json
{
  "email": "newuser@example.com",
  "password": "securepassword123"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| email | string | Yes | Valid email format |
| password | string | Yes | Non-empty string |

**Response (201)**:
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440020",
  "message": "User registered successfully"
}
```

**Response (400)**:
```json
{
  "error": "Missing email or password"
}
```

---

### Translations API

#### Get Available Locales

```http
GET /api/translations/locales
```

**Authentication**: Not required

**Response**:
```json
{
  "locales": [
    {
      "code": "en",
      "name": "English",
      "native": "English"
    },
    {
      "code": "tr",
      "name": "Turkish",
      "native": "Türkçe"
    }
  ],
  "default": "en"
}
```

---

#### Get Available Domains

```http
GET /api/translations/domains
```

**Authentication**: Not required

**Response**:
```json
{
  "domains": [
    {
      "name": "messages",
      "description": "General application messages"
    },
    {
      "name": "validators",
      "description": "Form validation messages"
    }
  ]
}
```

---

#### Get Translations for Locale

```http
GET /api/translations/{locale}?domain=messages
```

**Authentication**: Not required

**Parameters**:
| Parameter | Type | Location | Description |
|-----------|------|----------|-------------|
| locale | string | path | 2-character ISO code (e.g., en, tr) |
| domain | string | query | Translation domain (default: messages) |

**Response (200)**:
```json
{
  "locale": "en",
  "domain": "messages",
  "translations": {
    "post.title": "Title",
    "post.content": "Content",
    "post.status.draft": "Draft",
    "post.status.published": "Published"
  }
}
```

**Response (400)**:
```json
{
  "error": "Invalid locale format. Use 2-character ISO codes (e.g., en, tr)",
  "code": "INVALID_LOCALE"
}
```

---

### Staging API (QA Automation)

The staging endpoints are mock endpoints designed for QA automation testing. They do **NOT**:
- Require authentication
- Access the database
- Contain business logic
- Store data persistently

All responses follow a consistent format:
```json
{
  "status": "success" | "error",
  "data": { ... },
  "timestamp": "ISO 8601 format"
}
```

#### Health Check

```http
GET /api/stage/health
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "healthy": true,
    "service": "stage-api",
    "version": "1.0.0"
  },
  "timestamp": "2024-01-15T12:00:00+00:00"
}
```

#### Echo Endpoint

```http
POST /api/stage/echo
Content-Type: application/json

{
  "test": "data"
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "received": {
      "body": { "test": "data" },
      "query": {},
      "headers": { "Content-Type": "application/json" },
      "method": "POST",
      "path": "/api/stage/echo"
    }
  },
  "timestamp": "2024-01-15T12:00:00+00:00"
}
```

#### Mock Users CRUD

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/stage/users | List mock users |
| GET | /api/stage/users/{id} | Get mock user by ID |
| POST | /api/stage/users | Create mock user |
| PUT | /api/stage/users/{id} | Update mock user |
| DELETE | /api/stage/users/{id} | Delete mock user |

#### Mock Products CRUD

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/stage/products | List mock products |
| GET | /api/stage/products/{id} | Get mock product by ID |
| POST | /api/stage/products | Create mock product |
| PUT | /api/stage/products/{id} | Update mock product |
| DELETE | /api/stage/products/{id} | Delete mock product |
| POST | /api/stage/products/bulk | Bulk operations |

---

## Domain Models

### Post Entity

| Property | Type | Description |
|----------|------|-------------|
| id | UUID | Unique identifier |
| title | PostTitle | Post title (min 3 chars) |
| content | PostContent | Post content (min 10 chars) |
| status | PostStatus | draft / published / archived |
| authorId | UUID | Reference to User |
| createdAt | CreatedAt | Creation timestamp |
| publishedAt | DateTime? | Publication timestamp (nullable) |

**Post Status Values**:
- `draft` - Initial state, editable
- `published` - Visible to public, not editable
- `archived` - Hidden from public

**Business Rules**:
- Posts are created in `draft` status
- Only draft posts can be published
- Archived posts cannot be published
- Published posts cannot be edited

---

### User Entity

| Property | Type | Description |
|----------|------|-------------|
| id | UUID | Unique identifier |
| email | Email | User email (validated format) |
| password | string | Hashed password |
| role | UserRole | User role |
| createdAt | CreatedAt | Registration timestamp |

**User Role Values**:
- `ROLE_USER` - Standard user
- `ROLE_ADMIN` - Administrator

---

### Comment Entity

| Property | Type | Description |
|----------|------|-------------|
| id | UUID | Unique identifier |
| content | CommentContent | Comment text |
| postId | UUID | Reference to Post |
| authorId | UUID | Reference to User |
| createdAt | CreatedAt | Creation timestamp |

---

## Error Codes

| HTTP Code | Description |
|-----------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Authentication required |
| 404 | Not Found - Resource doesn't exist |
| 500 | Internal Server Error |

---

## cURL Examples

### Complete Workflow Example

```bash
# 1. Login and get token
TOKEN=$(curl -s -X POST http://localhost:8081/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}' \
  | jq -r '.token')

# 2. Create a new post
curl -X POST http://localhost:8081/api/posts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"My API Post","content":"This post was created via the API!"}'

# 3. List all posts
curl http://localhost:8081/api/posts

# 4. Publish the post (replace {id} with actual ID)
curl -X POST http://localhost:8081/api/posts/{id}/publish \
  -H "Authorization: Bearer $TOKEN"

# 5. Add a comment
curl -X POST http://localhost:8081/api/posts/{id}/comments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Great post!"}'
```

---

## Related Documentation

- [Project README](/README.md)
- [Setup Guide](/SETUP.md)
- [Architecture Guide](/CLAUDE.md)

---

*Last Updated: January 2026*
*Generated by Claude AI for the AI Blog project*
