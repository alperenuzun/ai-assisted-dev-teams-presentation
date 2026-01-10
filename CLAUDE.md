# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7.3 DDD (Domain-Driven Design) blog application demonstrating modern PHP architecture patterns. The project uses PHP 8.3, PostgreSQL 16, and runs in Docker containers. It was created for the presentation "From Tools to Teammates: Build AI Assisted Teams".

**Port Information**: The application runs on **port 8081** (changed from 8080 due to LocalStack conflict).

## Essential Commands

### Docker & Development

```bash
# Start containers
make up                    # or: docker-compose up -d

# Stop containers
make down                  # or: docker-compose down

# Access PHP container
make bash                  # or: docker exec -it blog-php sh

# Install dependencies
docker exec blog-php composer install

# Clear Symfony cache (important after config changes)
docker exec blog-php php bin/console cache:clear
```

### Database Operations

```bash
# Create database
docker exec blog-php php bin/console doctrine:database:create

# Create schema from Doctrine mappings (NO migrations used)
docker exec blog-php php bin/console doctrine:schema:create

# Load test data (creates 2 users, 3 posts)
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction

# Reset database
docker exec blog-php php bin/console doctrine:database:drop --force --if-exists
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:schema:create
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction

# Validate Doctrine mappings and schema
docker exec blog-php php bin/console doctrine:schema:validate
```

### Testing

```bash
# Run all tests
docker exec blog-php vendor/bin/pest

# Run tests with verbose output
docker exec blog-php vendor/bin/pest -v

# Run specific test suite
docker exec blog-php vendor/bin/pest tests/Unit
```

### JWT Authentication

```bash
# Generate JWT keys (required for login)
docker exec blog-php php bin/console lexik:jwt:generate-keypair
```

### Code Style

```bash
# Check code style
docker exec blog-php vendor/bin/pint --test

# Fix code style
docker exec blog-php vendor/bin/pint
```

### API Testing

```bash
# Test endpoints (note: port 8081)
curl http://localhost:8081/
curl http://localhost:8081/admin/dashboard

# Login and get JWT token
curl -X POST http://localhost:8081/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}'

# Use token for authenticated requests
curl http://localhost:8081/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Architecture Deep Dive

### DDD Bounded Contexts

The codebase is organized into three bounded contexts, each with strict layering:

```
src/
├── SharedKernel/          # Shared across all contexts
│   ├── Domain/
│   │   ├── ValueObject/   # Uuid, Email, CreatedAt
│   │   └── Exception/     # DomainException, ValidationException
│   └── Infrastructure/
│       └── Persistence/
│           └── Doctrine/
│               └── Type/  # Custom Doctrine types for value objects
│
├── Api/                   # REST API context
│   ├── Domain/            # Pure business logic, no dependencies
│   │   ├── Post/
│   │   │   ├── Entity/           # Post aggregate root
│   │   │   ├── ValueObject/      # PostTitle, PostContent, PostStatus
│   │   │   └── Repository/       # Interfaces only
│   │   └── User/
│   │       ├── Entity/           # User entity (implements UserInterface)
│   │       ├── ValueObject/      # UserRole
│   │       └── Repository/       # Interfaces only
│   │
│   ├── Application/       # Use cases (CQRS)
│   │   ├── Post/
│   │   │   ├── Command/          # CreatePost, PublishPost + Handlers
│   │   │   └── Query/            # ListPosts, GetPost + Handlers
│   │   └── User/
│   │       └── Command/          # RegisterUser + Handler
│   │
│   └── Infrastructure/    # Framework-specific implementations
│       ├── Controller/           # REST endpoints
│       └── Persistence/
│           └── Doctrine/
│               └── Repository/   # DoctrinePostRepository, DoctrineUserRepository
│
├── Admin/                 # Admin panel context
│   └── Infrastructure/
│       └── Controller/    # DashboardController
│
└── Web/                   # Public web context
    └── Infrastructure/
        └── Controller/    # HomeController
```

### Critical Architecture Decisions

#### 1. Doctrine Value Object Hydration

**Problem**: Doctrine cannot automatically convert database primitives to value objects.

**Solution**: Custom Doctrine types are registered for all value objects:
- `UuidType` → `Uuid` value object
- `EmailType` → `Email` value object
- `CreatedAtType` → `CreatedAt` value object
- `PostTitleType` → `PostTitle` value object
- `PostContentType` → `PostContent` value object
- `PostStatusType` → `PostStatus` value object
- `UserRoleType` → `UserRole` value object

**Location**:
- Types: `src/SharedKernel/Infrastructure/Persistence/Doctrine/Type/` and `src/Api/Infrastructure/Persistence/Doctrine/Type/`
- Registration: `config/packages/doctrine.yaml` under `dbal.types`
- XML Mappings: `config/doctrine/*.orm.xml` use these types (e.g., `type="uuid_vo"`)

**When adding new value objects**:
1. Create custom Doctrine type in appropriate Infrastructure layer
2. Register in `doctrine.yaml`
3. Use custom type in XML mappings
4. Clear Symfony cache

#### 2. Doctrine XML Mappings (Not Annotations)

**Why XML**:
- Keeps domain layer pure (no framework dependencies)
- Better for DDD architecture
- Mappings in `config/doctrine/`

**Important**:
- File names: `Post.Entity.Post.orm.xml` (NOT `Api.Domain.Post.Entity.Post.orm.xml`)
- Entity name must be fully qualified: `name="App\Api\Domain\Post\Entity\Post"`
- Repository class must be fully qualified in XML

#### 3. CQRS with Symfony Messenger

Commands and Queries are handled through Symfony Messenger:
- **Commands** (writes): `CreatePostCommand`, `PublishPostCommand`, `RegisterUserCommand`
- **Queries** (reads): `ListPostsQuery`, `GetPostQuery`
- **Handlers**: Decorated with `#[AsMessageHandler]` attribute
- **Routing**: Explicit class routing in `config/packages/messenger.yaml` (no wildcards)

#### 4. No Database Migrations

This project uses **schema creation directly from mappings**:
```bash
doctrine:schema:create    # NOT doctrine:migrations:migrate
```

This is intentional for the demo/presentation nature of the project.

## Common Development Workflows

### Adding a New Endpoint

1. Create Command/Query in `Application/`
2. Create Handler with `#[AsMessageHandler]`
3. Add routing in `config/packages/messenger.yaml`
4. Create Controller method using `MessageBusInterface`
5. Add route attribute: `#[Route('/path', name: 'name', methods: ['GET'])]`
6. Test endpoint with curl

### Adding a New Value Object

1. Create value object in `Domain/ValueObject/`
2. Create custom Doctrine type in `Infrastructure/Persistence/Doctrine/Type/`
3. Register type in `config/packages/doctrine.yaml`
4. Update XML mapping to use custom type
5. Clear cache: `php bin/console cache:clear`
6. Recreate schema if needed

### Modifying Domain Entities

1. Update entity in `Domain/Entity/`
2. Update XML mapping in `config/doctrine/`
3. Drop and recreate schema (no migrations):
   ```bash
   docker exec blog-php php bin/console doctrine:database:drop --force
   docker exec blog-php php bin/console doctrine:database:create
   docker exec blog-php php bin/console doctrine:schema:create
   docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
   ```

### Authentication Flow

- Default users: `admin@blog.com` / `password` and `user@blog.com` / `password`
- JWT tokens valid for 1 hour (3600 seconds)
- Token in `Authorization: Bearer <token>` header
- User ID retrieved via `$this->getUser()->getId()->toString()` in controllers

## Important Constraints

### Value Objects Are Immutable and Self-Validating

All value objects validate in constructor:
```php
PostTitle::fromString('abc');  // Throws ValidationException (min 3 chars)
Email::fromString('invalid');  // Throws ValidationException
```

Handle validation exceptions at the controller/application layer.

### Aggregate Root Encapsulation

`Post` is an aggregate root - all modifications must go through its methods:
```php
// CORRECT
$post->publish();

// WRONG - don't modify internals directly
$post->status = PostStatus::published();  // Won't work (readonly)
```

### Repository Interfaces in Domain

Repository interfaces live in Domain layer, implementations in Infrastructure:
```php
// Domain
interface PostRepositoryInterface { }

// Infrastructure
class DoctrinePostRepository implements PostRepositoryInterface { }
```

Never import infrastructure classes into domain layer.

## Testing Notes

- **Pest PHP** is the testing framework (not PHPUnit syntax)
- Test structure: `test('description', function() { ... })`
- Expectations: `expect($value)->toBeTrue()`
- Only Unit tests are currently configured (no Integration/Feature test directories)
- Tests use `KernelTestCase` for Symfony integration

## Troubleshooting

### Doctrine Type Errors

If you see "Cannot assign X to property Y of type Z":
1. Check if custom Doctrine type exists for the value object
2. Verify type is registered in `doctrine.yaml`
3. Confirm XML mapping uses the custom type
4. Clear cache and try again

### JWT Authentication Errors

If login returns "Invalid credentials":
1. Check password hash in database matches expected format
2. Regenerate JWT keys if missing
3. Verify security.yaml configuration

### Port Conflicts

The app uses port **8081** (not 8080). If you see connection errors:
- Check `docker-compose.yml` for port mappings
- Verify no other service is using 8081
- Use `http://localhost:8081/` in all curl commands

### Permission Errors

```bash
docker exec blog-php chmod -R 777 var/
```

### Container Issues

```bash
# View logs
docker-compose logs -f php
docker-compose logs -f nginx

# Rebuild
docker-compose down
docker-compose up -d --build
```
