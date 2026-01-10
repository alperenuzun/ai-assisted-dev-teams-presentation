# Coding Standards

## General Principles

### SOLID Principles
- **S**ingle Responsibility: Each module should have one reason to change
- **O**pen/Closed: Open for extension, closed for modification
- **L**iskov Substitution: Subtypes must be substitutable for their base types
- **I**nterface Segregation: Many specific interfaces better than one general
- **D**ependency Inversion: Depend on abstractions, not concretions

### Clean Code Principles
- **DRY**: Don't Repeat Yourself
- **KISS**: Keep It Simple, Stupid
- **YAGNI**: You Aren't Gonna Need It
- **Separation of Concerns**: Different concerns in different modules
- **Principle of Least Surprise**: Code should behave as expected

## TypeScript Standards

### Type Safety
```typescript
// ✅ Good: Explicit types
interface User {
  id: number;
  name: string;
  email: string;
}

function getUser(id: number): Promise<User> {
  return fetchUser(id);
}

// ❌ Bad: Any types
function getUser(id: any): any {
  return fetchUser(id);
}
```

### Use Strict Mode
```json
{
  "compilerOptions": {
    "strict": true,
    "noImplicitAny": true,
    "strictNullChecks": true,
    "strictFunctionTypes": true
  }
}
```

### Prefer Interfaces over Types for Objects
```typescript
// ✅ Good
interface User {
  id: number;
  name: string;
}

// ⚠️ Use type for unions, intersections
type Status = 'active' | 'inactive';
type UserWithStatus = User & { status: Status };
```

## React Standards

### Component Structure
```typescript
// ✅ Good: Functional component with proper typing
import React, { useState, useEffect } from 'react';

interface UserProfileProps {
  userId: number;
  onUpdate?: (user: User) => void;
}

export const UserProfile: React.FC<UserProfileProps> = ({
  userId,
  onUpdate
}) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchUser(userId).then(setUser).finally(() => setLoading(false));
  }, [userId]);

  if (loading) return <LoadingSpinner />;
  if (!user) return <ErrorMessage />;

  return (
    <div className={styles.container}>
      {/* Component content */}
    </div>
  );
};
```

### Hooks Rules
- Only call hooks at the top level
- Only call hooks from React functions
- Use custom hooks for reusable logic
- Name custom hooks with "use" prefix

```typescript
// ✅ Good: Custom hook
function useUserData(userId: number) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchUser(userId)
      .then(setUser)
      .finally(() => setLoading(false));
  }, [userId]);

  return { user, loading };
}
```

### State Management
```typescript
// ✅ Good: Proper state updates
const [count, setCount] = useState(0);
setCount(prevCount => prevCount + 1);

// ❌ Bad: Direct mutation
const [user, setUser] = useState({ name: 'John' });
user.name = 'Jane'; // DON'T mutate state directly
```

## Backend Standards

### API Route Structure
```typescript
// ✅ Good: Proper route structure
router.post('/users',
  authenticate,
  validate(userSchema),
  async (req, res, next) => {
    try {
      const user = await userService.create(req.body);
      res.status(201).json({ success: true, data: user });
    } catch (error) {
      next(error);
    }
  }
);
```

### Error Handling
```typescript
// ✅ Good: Centralized error handling
class AppError extends Error {
  constructor(
    public statusCode: number,
    public message: string,
    public isOperational = true
  ) {
    super(message);
  }
}

// Error middleware
app.use((err: AppError, req: Request, res: Response, next: NextFunction) => {
  const { statusCode = 500, message } = err;

  res.status(statusCode).json({
    success: false,
    message,
    ...(process.env.NODE_ENV === 'development' && { stack: err.stack })
  });
});
```

### Async/Await
```typescript
// ✅ Good: Async/await with error handling
async function getUser(id: number): Promise<User> {
  try {
    const user = await db.users.findById(id);
    if (!user) {
      throw new AppError(404, 'User not found');
    }
    return user;
  } catch (error) {
    if (error instanceof AppError) throw error;
    throw new AppError(500, 'Database error');
  }
}

// ❌ Bad: Promise chains
function getUser(id: number): Promise<User> {
  return db.users.findById(id)
    .then(user => {
      if (!user) throw new Error('Not found');
      return user;
    })
    .catch(error => {
      throw error;
    });
}
```

## Database Standards

### Queries
```typescript
// ✅ Good: Parameterized queries
const user = await db.query(
  'SELECT * FROM users WHERE id = $1',
  [userId]
);

// ❌ Bad: String concatenation (SQL injection risk)
const user = await db.query(
  `SELECT * FROM users WHERE id = ${userId}`
);
```

### Migrations
```typescript
// ✅ Good: Reversible migrations
export async function up(db: Database) {
  await db.schema.createTable('users', (table) => {
    table.increments('id').primary();
    table.string('email').unique().notNullable();
    table.timestamps(true, true);
  });
}

export async function down(db: Database) {
  await db.schema.dropTable('users');
}
```

## Testing Standards

### Unit Tests
```typescript
// ✅ Good: AAA pattern (Arrange, Act, Assert)
describe('UserService', () => {
  describe('createUser', () => {
    it('should create a user with valid data', async () => {
      // Arrange
      const userData = { name: 'John', email: 'john@example.com' };
      const mockCreate = jest.fn().mockResolvedValue(userData);

      // Act
      const result = await userService.create(userData);

      // Assert
      expect(result).toEqual(userData);
      expect(mockCreate).toHaveBeenCalledWith(userData);
    });

    it('should throw error for invalid email', async () => {
      // Arrange
      const invalidData = { name: 'John', email: 'invalid' };

      // Act & Assert
      await expect(userService.create(invalidData))
        .rejects
        .toThrow('Invalid email');
    });
  });
});
```

### Test Naming
- Use descriptive test names
- Follow pattern: "should [expected behavior] when [condition]"
- Group related tests with describe blocks

## Security Standards

### Input Validation
```typescript
// ✅ Good: Validation middleware
const userSchema = Joi.object({
  name: Joi.string().min(2).max(50).required(),
  email: Joi.string().email().required(),
  age: Joi.number().integer().min(18).max(120)
});

router.post('/users', validate(userSchema), createUser);
```

### Authentication
```typescript
// ✅ Good: Secure password handling
import bcrypt from 'bcrypt';

async function hashPassword(password: string): Promise<string> {
  const saltRounds = 10;
  return bcrypt.hash(password, saltRounds);
}

async function verifyPassword(password: string, hash: string): Promise<boolean> {
  return bcrypt.compare(password, hash);
}
```

### Environment Variables
```typescript
// ✅ Good: Never commit secrets
const config = {
  jwtSecret: process.env.JWT_SECRET,
  dbUrl: process.env.DATABASE_URL,
  apiKey: process.env.API_KEY
};

// ❌ Bad: Hardcoded secrets
const config = {
  jwtSecret: 'my-secret-key', // NEVER DO THIS
};
```

## Naming Conventions

### Files
- **React Components**: `PascalCase.tsx` (e.g., `UserProfile.tsx`)
- **Other files**: `kebab-case.ts` (e.g., `user-service.ts`)
- **Test files**: `*.test.ts` or `*.spec.ts`
- **Type files**: `*.types.ts` or `*.interface.ts`

### Variables & Functions
```typescript
// ✅ Good naming
const userCount = 10;
const isAuthenticated = true;
const hasPermission = checkPermission();

function getUserById(id: number): User { }
function calculateTotalPrice(items: Item[]): number { }

// ❌ Bad naming
const x = 10;
const flag = true;
const data = getData();

function get(id: number) { }
function calc(items: any[]) { }
```

### Constants
```typescript
// ✅ Good: UPPER_SNAKE_CASE for constants
const MAX_LOGIN_ATTEMPTS = 5;
const API_BASE_URL = 'https://api.example.com';
const DEFAULT_PAGE_SIZE = 20;
```

## Comments & Documentation

### JSDoc Comments
```typescript
/**
 * Fetches a user by their ID
 * @param id - The unique user identifier
 * @returns A promise that resolves to the user object
 * @throws {AppError} When user is not found
 * @example
 * const user = await getUserById(123);
 */
async function getUserById(id: number): Promise<User> {
  // Implementation
}
```

### Inline Comments
```typescript
// ✅ Good: Explain WHY, not WHAT
// Using exponential backoff to prevent API rate limiting
await retry(fetchData, { maxAttempts: 3, backoff: 'exponential' });

// ❌ Bad: States the obvious
// Increment counter by 1
counter = counter + 1;
```

## Code Organization

### Folder Structure
```
src/
├── components/
│   ├── common/          # Shared components
│   ├── features/        # Feature-specific components
│   └── layouts/         # Layout components
├── hooks/               # Custom React hooks
├── services/            # API and business logic
├── store/               # State management
├── types/               # TypeScript types
├── utils/               # Utility functions
└── tests/               # Test utilities
```

### Import Order
```typescript
// 1. External libraries
import React from 'react';
import { useSelector } from 'react-redux';

// 2. Internal modules
import { UserService } from '@/services/user-service';
import { Button } from '@/components/common';

// 3. Types
import type { User } from '@/types';

// 4. Styles
import styles from './UserProfile.module.css';
```

## Performance Best Practices

### React Performance
```typescript
// ✅ Good: Memoization
const MemoizedComponent = React.memo(ExpensiveComponent);

const memoizedValue = useMemo(() => {
  return expensiveCalculation(data);
}, [data]);

const memoizedCallback = useCallback(() => {
  doSomething(data);
}, [data]);
```

### Backend Performance
```typescript
// ✅ Good: Database query optimization
// Select only needed fields
const users = await db.users.find(
  { status: 'active' },
  { projection: { name: 1, email: 1 } }
);

// Use indexes for frequently queried fields
await db.users.createIndex({ email: 1 });
```

## Linting & Formatting

### ESLint Configuration
```json
{
  "extends": [
    "eslint:recommended",
    "plugin:@typescript-eslint/recommended",
    "plugin:react/recommended",
    "plugin:react-hooks/recommended"
  ],
  "rules": {
    "no-console": "warn",
    "no-unused-vars": "error",
    "@typescript-eslint/explicit-function-return-type": "warn"
  }
}
```

### Prettier Configuration
```json
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": true,
  "printWidth": 100,
  "tabWidth": 2
}
```

## Git Commit Standards

### Commit Message Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples
```
feat(auth): add JWT authentication

Implemented JWT-based authentication with refresh tokens.
Added middleware for protected routes.

Closes #123
```

## Review Checklist

Before submitting code for review:
- [ ] All tests pass
- [ ] Code coverage maintained/improved
- [ ] No linting errors
- [ ] TypeScript compiles without errors
- [ ] Documentation updated
- [ ] No console.log statements
- [ ] No commented-out code
- [ ] Meaningful commit messages
- [ ] No hardcoded secrets
- [ ] Performance considerations addressed
