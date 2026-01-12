---
description: Create a complete REST API endpoint with service layer, validation, and tests
---

# Create New Endpoint with Services

## Purpose

Scaffolds a complete, production-ready REST API endpoint following best practices including controller, service layer, repository pattern, validation, error handling, and comprehensive tests.

## Usage

```
/create-endpoint <resource-name> [--methods GET,POST,PUT,DELETE] [--auth required|optional|none]
```

**Examples:**

```bash
# Create full CRUD endpoint for users
/create-endpoint user --methods GET,POST,PUT,DELETE --auth required

# Create read-only endpoint
/create-endpoint product --methods GET --auth none

# Create custom endpoint
/create-endpoint order --methods GET,POST --auth required
```

## Generated File Structure

```
src/
├── controllers/
│   └── {resource}.controller.ts       # HTTP request/response handling
├── services/
│   └── {resource}.service.ts          # Business logic
├── repositories/
│   └── {resource}.repository.ts       # Data access layer
├── models/
│   └── {resource}.model.ts            # Database model/entity
├── validators/
│   └── {resource}.validator.ts        # Input validation schemas
├── routes/
│   └── {resource}.routes.ts           # Route definitions
└── types/
    └── {resource}.types.ts            # TypeScript interfaces

tests/
├── unit/
│   ├── {resource}.service.test.ts     # Service layer tests
│   └── {resource}.repository.test.ts  # Repository tests
└── integration/
    └── {resource}.routes.test.ts      # API endpoint tests

docs/
└── api/
    └── {resource}.openapi.yaml        # API documentation
```

## Architecture Pattern

### Layered Architecture

```
┌─────────────────────────────────────┐
│         Controller Layer            │  ← HTTP handling, validation
│  (routes, controllers, middleware)  │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│         Service Layer               │  ← Business logic
│    (services, orchestration)        │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│       Repository Layer              │  ← Data access
│    (repositories, queries)          │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│         Database Layer              │  ← PostgreSQL, MongoDB, etc.
└─────────────────────────────────────┘
```

## Generated Code Examples

### 1. Model (Entity)

```typescript
// src/models/user.model.ts
import {
  Entity,
  PrimaryGeneratedColumn,
  Column,
  CreateDateColumn,
  UpdateDateColumn,
} from "typeorm";

@Entity("users")
export class User {
  @PrimaryGeneratedColumn("uuid")
  id: string;

  @Column({ unique: true })
  email: string;

  @Column()
  name: string;

  @Column({ select: false })
  password: string;

  @Column({ default: "user" })
  role: string;

  @Column({ default: true })
  isActive: boolean;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;

  @Column({ nullable: true })
  deletedAt?: Date;
}
```

### 2. Repository (Data Access)

```typescript
// src/repositories/user.repository.ts
import { Repository } from "typeorm";
import { AppDataSource } from "../config/database";
import { User } from "../models/user.model";

export class UserRepository {
  private repository: Repository<User>;

  constructor() {
    this.repository = AppDataSource.getRepository(User);
  }

  async findAll(
    page: number = 1,
    limit: number = 10
  ): Promise<{ users: User[]; total: number }> {
    const [users, total] = await this.repository.findAndCount({
      skip: (page - 1) * limit,
      take: limit,
      where: { deletedAt: null },
      order: { createdAt: "DESC" },
    });

    return { users, total };
  }

  async findById(id: string): Promise<User | null> {
    return this.repository.findOne({
      where: { id, deletedAt: null },
    });
  }

  async findByEmail(email: string): Promise<User | null> {
    return this.repository.findOne({
      where: { email, deletedAt: null },
    });
  }

  async create(userData: Partial<User>): Promise<User> {
    const user = this.repository.create(userData);
    return this.repository.save(user);
  }

  async update(id: string, userData: Partial<User>): Promise<User | null> {
    await this.repository.update(id, userData);
    return this.findById(id);
  }

  async softDelete(id: string): Promise<boolean> {
    const result = await this.repository.update(id, { deletedAt: new Date() });
    return result.affected > 0;
  }

  async hardDelete(id: string): Promise<boolean> {
    const result = await this.repository.delete(id);
    return result.affected > 0;
  }
}
```

### 3. Service (Business Logic)

```typescript
// src/services/user.service.ts
import { UserRepository } from "../repositories/user.repository";
import { User } from "../models/user.model";
import { NotFoundError, ConflictError } from "../utils/errors";
import bcrypt from "bcrypt";

export class UserService {
  private userRepository: UserRepository;

  constructor() {
    this.userRepository = new UserRepository();
  }

  async getUsers(page: number, limit: number) {
    const { users, total } = await this.userRepository.findAll(page, limit);

    return {
      data: users,
      meta: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async getUserById(id: string): Promise<User> {
    const user = await this.userRepository.findById(id);

    if (!user) {
      throw new NotFoundError(`User with ID ${id} not found`);
    }

    return user;
  }

  async createUser(userData: {
    email: string;
    name: string;
    password: string;
  }): Promise<User> {
    // Check if user already exists
    const existingUser = await this.userRepository.findByEmail(userData.email);
    if (existingUser) {
      throw new ConflictError("User with this email already exists");
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(userData.password, 10);

    // Create user
    const user = await this.userRepository.create({
      ...userData,
      password: hashedPassword,
    });

    return user;
  }

  async updateUser(id: string, userData: Partial<User>): Promise<User> {
    // Verify user exists
    await this.getUserById(id);

    // If email is being updated, check for conflicts
    if (userData.email) {
      const existingUser = await this.userRepository.findByEmail(
        userData.email
      );
      if (existingUser && existingUser.id !== id) {
        throw new ConflictError("Email already in use");
      }
    }

    // Hash password if provided
    if (userData.password) {
      userData.password = await bcrypt.hash(userData.password, 10);
    }

    const updatedUser = await this.userRepository.update(id, userData);

    if (!updatedUser) {
      throw new NotFoundError(`User with ID ${id} not found`);
    }

    return updatedUser;
  }

  async deleteUser(id: string): Promise<void> {
    // Verify user exists
    await this.getUserById(id);

    // Soft delete
    const deleted = await this.userRepository.softDelete(id);

    if (!deleted) {
      throw new NotFoundError(`User with ID ${id} not found`);
    }
  }
}
```

### 4. Validator (Input Validation)

```typescript
// src/validators/user.validator.ts
import Joi from "joi";

export const createUserSchema = Joi.object({
  email: Joi.string().email().required().messages({
    "string.email": "Please provide a valid email address",
    "any.required": "Email is required",
  }),

  name: Joi.string().min(2).max(100).required().messages({
    "string.min": "Name must be at least 2 characters long",
    "string.max": "Name cannot exceed 100 characters",
    "any.required": "Name is required",
  }),

  password: Joi.string()
    .min(8)
    .pattern(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/)
    .required()
    .messages({
      "string.min": "Password must be at least 8 characters long",
      "string.pattern.base":
        "Password must contain at least one uppercase letter, one lowercase letter, and one number",
      "any.required": "Password is required",
    }),

  role: Joi.string().valid("user", "admin").default("user"),
});

export const updateUserSchema = Joi.object({
  email: Joi.string().email(),
  name: Joi.string().min(2).max(100),
  password: Joi.string()
    .min(8)
    .pattern(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/),
  role: Joi.string().valid("user", "admin"),
  isActive: Joi.boolean(),
}).min(1); // At least one field must be provided

export const getUsersQuerySchema = Joi.object({
  page: Joi.number().integer().min(1).default(1),
  limit: Joi.number().integer().min(1).max(100).default(10),
});
```

### 5. Controller (HTTP Handling)

```typescript
// src/controllers/user.controller.ts
import { Request, Response, NextFunction } from "express";
import { UserService } from "../services/user.service";
import {
  createUserSchema,
  updateUserSchema,
  getUsersQuerySchema,
} from "../validators/user.validator";
import { ValidationError } from "../utils/errors";

export class UserController {
  private userService: UserService;

  constructor() {
    this.userService = new UserService();
  }

  getUsers = async (req: Request, res: Response, next: NextFunction) => {
    try {
      // Validate query parameters
      const { error, value } = getUsersQuerySchema.validate(req.query);
      if (error) {
        throw new ValidationError(error.details[0].message);
      }

      const { page, limit } = value;
      const result = await this.userService.getUsers(page, limit);

      res.status(200).json({
        success: true,
        ...result,
      });
    } catch (error) {
      next(error);
    }
  };

  getUserById = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { id } = req.params;
      const user = await this.userService.getUserById(id);

      res.status(200).json({
        success: true,
        data: user,
      });
    } catch (error) {
      next(error);
    }
  };

  createUser = async (req: Request, res: Response, next: NextFunction) => {
    try {
      // Validate request body
      const { error, value } = createUserSchema.validate(req.body);
      if (error) {
        throw new ValidationError(error.details[0].message);
      }

      const user = await this.userService.createUser(value);

      res.status(201).json({
        success: true,
        data: user,
        message: "User created successfully",
      });
    } catch (error) {
      next(error);
    }
  };

  updateUser = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { id } = req.params;

      // Validate request body
      const { error, value } = updateUserSchema.validate(req.body);
      if (error) {
        throw new ValidationError(error.details[0].message);
      }

      const user = await this.userService.updateUser(id, value);

      res.status(200).json({
        success: true,
        data: user,
        message: "User updated successfully",
      });
    } catch (error) {
      next(error);
    }
  };

  deleteUser = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { id } = req.params;
      await this.userService.deleteUser(id);

      res.status(204).send();
    } catch (error) {
      next(error);
    }
  };
}
```

### 6. Routes

```typescript
// src/routes/user.routes.ts
import { Router } from "express";
import { UserController } from "../controllers/user.controller";
import { authenticate } from "../middleware/auth.middleware";
import { authorize } from "../middleware/authorize.middleware";

const router = Router();
const userController = new UserController();

// Public routes
router.post("/users", userController.createUser);

// Protected routes
router.use(authenticate); // All routes below require authentication

router.get("/users", userController.getUsers);
router.get("/users/:id", userController.getUserById);
router.put("/users/:id", userController.updateUser);
router.delete("/users/:id", authorize(["admin"]), userController.deleteUser);

export default router;
```

### 7. Tests

```typescript
// tests/unit/user.service.test.ts
import { UserService } from "../../src/services/user.service";
import { UserRepository } from "../../src/repositories/user.repository";
import { NotFoundError, ConflictError } from "../../src/utils/errors";

jest.mock("../../src/repositories/user.repository");

describe("UserService", () => {
  let userService: UserService;
  let userRepository: jest.Mocked<UserRepository>;

  beforeEach(() => {
    userService = new UserService();
    userRepository = (userService as any).userRepository;
  });

  describe("getUserById", () => {
    it("should return user when found", async () => {
      const mockUser = {
        id: "123",
        email: "test@example.com",
        name: "Test User",
      };
      userRepository.findById.mockResolvedValue(mockUser as any);

      const result = await userService.getUserById("123");

      expect(result).toEqual(mockUser);
      expect(userRepository.findById).toHaveBeenCalledWith("123");
    });

    it("should throw NotFoundError when user not found", async () => {
      userRepository.findById.mockResolvedValue(null);

      await expect(userService.getUserById("123")).rejects.toThrow(
        NotFoundError
      );
    });
  });

  describe("createUser", () => {
    it("should create user successfully", async () => {
      const userData = {
        email: "new@example.com",
        name: "New User",
        password: "Password123",
      };
      const mockUser = { id: "123", ...userData };

      userRepository.findByEmail.mockResolvedValue(null);
      userRepository.create.mockResolvedValue(mockUser as any);

      const result = await userService.createUser(userData);

      expect(result).toBeDefined();
      expect(userRepository.create).toHaveBeenCalled();
    });

    it("should throw ConflictError when email exists", async () => {
      const userData = {
        email: "existing@example.com",
        name: "User",
        password: "Password123",
      };
      userRepository.findByEmail.mockResolvedValue({ id: "456" } as any);

      await expect(userService.createUser(userData)).rejects.toThrow(
        ConflictError
      );
    });
  });
});
```

```typescript
// tests/integration/user.routes.test.ts
import request from "supertest";
import app from "../../src/app";
import { AppDataSource } from "../../src/config/database";

describe("User API Endpoints", () => {
  beforeAll(async () => {
    await AppDataSource.initialize();
  });

  afterAll(async () => {
    await AppDataSource.destroy();
  });

  describe("POST /api/v1/users", () => {
    it("should create a new user", async () => {
      const userData = {
        email: "test@example.com",
        name: "Test User",
        password: "Password123",
      };

      const response = await request(app)
        .post("/api/v1/users")
        .send(userData)
        .expect(201);

      expect(response.body.success).toBe(true);
      expect(response.body.data.email).toBe(userData.email);
      expect(response.body.data.password).toBeUndefined(); // Password should not be returned
    });

    it("should return 400 for invalid email", async () => {
      const userData = {
        email: "invalid-email",
        name: "Test User",
        password: "Password123",
      };

      const response = await request(app)
        .post("/api/v1/users")
        .send(userData)
        .expect(400);

      expect(response.body.success).toBe(false);
      expect(response.body.error).toBeDefined();
    });
  });

  describe("GET /api/v1/users", () => {
    it("should return paginated users", async () => {
      const response = await request(app)
        .get("/api/v1/users")
        .query({ page: 1, limit: 10 })
        .set("Authorization", "Bearer valid-token")
        .expect(200);

      expect(response.body.success).toBe(true);
      expect(response.body.data).toBeInstanceOf(Array);
      expect(response.body.meta).toBeDefined();
    });
  });
});
```

## Best Practices Implemented

✅ **Separation of Concerns**: Controller → Service → Repository
✅ **Input Validation**: Joi schemas for all inputs
✅ **Error Handling**: Custom error classes and centralized error middleware
✅ **Security**: Password hashing, authentication, authorization
✅ **Testing**: Unit tests for services, integration tests for endpoints
✅ **Documentation**: OpenAPI/Swagger documentation
✅ **Type Safety**: Full TypeScript support
✅ **Database**: ORM with migrations support
✅ **Pagination**: Built-in pagination for list endpoints
✅ **Soft Delete**: Soft delete by default, hard delete available

## Related Commands

- `/generate-tests` - Generate additional tests
- `/apidoc-check` - Validate API documentation
- `/review-code` - Review generated code
- `/security-scan` - Security check

## Tools and Resources

- [TypeORM Documentation](https://typeorm.io/)
- [Joi Validation](https://joi.dev/)
- [Express.js](https://expressjs.com/)
- [Jest Testing](https://jestjs.io/)
