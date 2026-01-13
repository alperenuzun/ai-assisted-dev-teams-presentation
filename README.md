# Personal Blog Multi-Domain API

> A Symfony 7.3 DDD project for the presentation: **"From Tools to Teammates: Build AI Assisted Teams"**

This project demonstrates Domain-Driven Design (DDD) architecture with PHP 8.3 and Symfony 7.3, showcasing how modern development practices combined with AI assistance can accelerate team productivity.

## 🏗️ Architecture Overview

### Bounded Contexts

The application is divided into three main bounded contexts:

```
src/
├── SharedKernel/          # Common code across contexts
│   └── Domain/
│       ├── ValueObject/   # Uuid, Email, CreatedAt
│       └── Exception/     # DomainException, ValidationException
│
├── Api/                   # REST API for mobile/external apps
│   ├── Domain/            # Pure business logic
│   ├── Application/       # Use cases (CQRS)
│   └── Infrastructure/    # Controllers, Repositories
│
├── Admin/                 # Admin panel domain
│   ├── Domain/
│   ├── Application/
│   └── Infrastructure/
│
└── Web/                   # Web frontend domain
    ├── Domain/
    ├── Application/
    └── Infrastructure/
```

### DDD Layered Architecture

Each bounded context follows strict layering:

1. **Domain Layer** (`Domain/`)
   - Pure PHP, no framework dependencies
   - Entities, Value Objects, Domain Services
   - Repository Interfaces
   - Domain Events
   - Business logic and invariants

2. **Application Layer** (`Application/`)
   - Use cases orchestration
   - Commands (writes) and Queries (reads) - CQRS pattern
   - Command/Query Handlers
   - DTOs (Data Transfer Objects)
   - Mappers

3. **Infrastructure Layer** (`Infrastructure/`)
   - Framework-specific code
   - Controllers (REST API, Web)
   - Repository Implementations (Doctrine)
   - External service integrations
   - Persistence mappings

## 🐳 Docker Setup

### Services

- **nginx**: Web server (port 8080)
- **php**: PHP 8.3-FPM with extensions
- **postgres**: PostgreSQL 16 database

### Network

All services communicate via `blog-network`.

### Containers

- `blog-nginx` - Nginx web server
- `blog-php` - PHP-FPM application
- `blog-postgres` - PostgreSQL database

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- Make (optional, for convenience commands)

### Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd wonderful-rosalind

# 2. Start Docker containers
make up
# or: docker-compose up -d

# 3. Install PHP dependencies
make composer ARGS=install
# or: docker exec blog-php composer install

# 4. Generate JWT keys
docker exec blog-php php bin/console lexik:jwt:generate-keypair

# 5. Create database and run migrations
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:migrations:migrate

# 6. Load fixtures (sample data)
docker exec blog-php php bin/console doctrine:fixtures:load
```

### One-Command Setup

```bash
make setup
```

This command will:
- Start all Docker containers
- Install Composer dependencies
- Generate JWT keys
- Create database
- Run migrations
- Load fixtures

## 📋 Available Make Commands

```bash
make help      # Show all available commands
make up        # Start Docker containers
make down      # Stop Docker containers
make bash      # Access PHP container shell
make composer  # Run composer (e.g., make composer ARGS='require package')
make test      # Run Pest tests
make pint      # Check code style
make migrate   # Run database migrations
make fixtures  # Load database fixtures
```

## 🎯 Design Patterns Implemented

### 1. Chain of Responsibility

Domain services use the Chain of Responsibility pattern:

```php
// Example: Post service chain
PostServiceChain
  ├── CreatePostService
  ├── PublishPostService
  └── ArchivePostService
```

### 2. CQRS (Command Query Responsibility Segregation)

Separate commands (writes) from queries (reads):

```php
// Commands
CreatePostCommand + CreatePostHandler
PublishPostCommand + PublishPostHandler

// Queries
ListPostsQuery + ListPostsHandler
GetPostQuery + GetPostHandler
```

### 3. Repository Pattern

Abstract data access with interfaces:

```php
// Domain
interface PostRepositoryInterface

// Infrastructure
class DoctrinePostRepository implements PostRepositoryInterface
```

### 4. Value Objects

Immutable, self-validating objects:

```php
Uuid, Email, CreatedAt
PostTitle, PostContent, PostStatus
```

### 5. Aggregate Root

Post entity controls its consistency boundary

## 🔌 API Endpoints (Planned)

### Authentication

```bash
# Login
POST /api/login
Content-Type: application/json

{
  "email": "admin@blog.com",
  "password": "password"
}

# Response
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Posts

```bash
# List all posts
GET /api/posts
Authorization: Bearer <token>

# Get single post
GET /api/posts/{id}
Authorization: Bearer <token>

# Create post
POST /api/posts
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "My First Post",
  "content": "This is the content of my first blog post."
}

# Publish post
POST /api/posts/{id}/publish
Authorization: Bearer <token>
```

### Admin

```bash
# Dashboard statistics
GET /admin/dashboard

# Response
{
  "posts_count": 10,
  "users_count": 5,
  "published_posts": 7
}
```

### Web

```bash
# Homepage with published posts
GET /
```

## 🧪 Testing

### Run All Tests

```bash
make test
# or: docker exec blog-php vendor/bin/pest
```

### Test Structure

```
tests/
├── Unit/              # Domain logic tests
│   ├── Api/
│   │   └── Domain/
│   └── SharedKernel/
├── Integration/       # Repository tests with DB
│   └── Api/
│       └── Infrastructure/
└── Feature/           # HTTP endpoint tests
    └── Api/
```

### Example Test

```php
test('can create a post', function () {
    $post = Post::create(
        Uuid::generate(),
        PostTitle::fromString('Test Title'),
        PostContent::fromString('Test Content'),
        Uuid::generate()
    );

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->getStatus()->isDraft())->toBeTrue();
});
```

## 🎨 Code Style

This project uses Laravel Pint for code style enforcement:

```bash
# Check code style
make pint

# Fix code style
docker exec blog-php vendor/bin/pint
```

Configuration: `pint.json`

## 🗄️ Database

### Technology

- PostgreSQL 16
- Doctrine ORM with XML mappings

### Migrations

```bash
# Generate migration
docker exec blog-php php bin/console doctrine:migrations:diff

# Run migrations
docker exec blog-php php bin/console doctrine:migrations:migrate
```

### Fixtures

Sample data for testing:

```bash
docker exec blog-php php bin/console doctrine:fixtures:load
```

## 📁 Project Status

### ✅ Completed Foundation

1. **Docker Infrastructure**
   - docker-compose.yml with nginx, php 8.3, postgres 16
   - Dockerfile with all required PHP extensions (pdo_pgsql, intl, opcache)
   - Nginx configuration optimized for Symfony
   - Makefile with convenient commands
   - Proper .gitignore

2. **Symfony Configuration**
   - Framework bundle configuration (7.3)
   - Doctrine ORM with PostgreSQL
   - Symfony Messenger for CQRS
   - Security with JWT authentication
   - Route configuration for all three contexts
   - Pint (code style) configuration
   - Pest (testing) configuration

3. **SharedKernel Implementation**
   - Uuid value object with validation
   - Email value object with validation
   - CreatedAt value object
   - DomainException base class
   - ValidationException for business rules
   - Unit tests for Uuid value object

4. **Api Domain - Post Aggregate (Partial)**
   - PostTitle value object (3-255 chars validation)
   - PostContent value object (min 10 chars validation)
   - PostStatus value object (draft, published, archived)
   - Post entity (aggregate root) with business logic
   - PostRepositoryInterface

### 🚧 To Be Completed

This project provides a **solid foundation** demonstrating DDD architecture with Symfony. To have a fully working application, the following components need to be implemented:

5. **Complete Post Domain**
   - Domain Services with Chain of Responsibility pattern
   - Domain Events (PostCreatedEvent, PostPublishedEvent)
   - Additional unit tests

6. **User Aggregate**
   - User entity implementing Symfony UserInterface
   - UserRole value object
   - UserRepositoryInterface
   - Password hashing integration

7. **Application Layer (CQRS)**
   - Post Commands (CreatePost, PublishPost) + Handlers
   - Post Queries (ListPosts, GetPost) + Handlers
   - User Commands (RegisterUser) + Handler
   - DTOs for request/response transformation

8. **Infrastructure Layer**
   - PostController (REST API endpoints)
   - UserController (registration, login)
   - DoctrinePostRepository implementation
   - DoctrineUserRepository implementation
   - Doctrine XML mappings for Post and User
   - Database fixtures for sample data

9. **Database Setup**
   - Initial migration files
   - Seed data for testing

10. **Admin Context**
    - StatisticsService for dashboard
    - GetStatisticsQuery + Handler
    - DashboardController

11. **Web Context**
    - Install Twig bundle
    - HomeController for public pages
    - Templates for displaying posts
    - GetPublishedPostsQuery + Handler

12. **Comprehensive Testing**
    - Unit tests for all value objects and entities
    - Integration tests for repositories
    - Feature tests for all API endpoints
    - Test fixtures and factories

13. **Documentation**
    - ARCHITECTURE.md with detailed DDD explanations
    - API.md with endpoint documentation
    - PHPDoc comments on all classes
    - Inline code documentation

## 🎓 Educational Value

This project demonstrates:

1. **Domain-Driven Design**: Proper bounded contexts, aggregates, value objects
2. **Clean Architecture**: Separation of concerns across layers
3. **SOLID Principles**: Single Responsibility, Open/Closed, Dependency Inversion
4. **Design Patterns**: Chain of Responsibility, CQRS, Repository, Factory
5. **Modern PHP**: PHP 8.3 features (readonly, typed properties, constructor promotion)
6. **Testing Best Practices**: Unit, Integration, and Feature tests with Pest
7. **DevOps**: Dockerized development environment
8. **Code Quality**: Automated code style with Pint

## 🎯 Next Steps to Complete

Follow the detailed implementation plan at:
```
~/.claude/plans/partitioned-whistling-kernighan.md
```

Key files that need implementation (in order):

1. `src/Api/Domain/Post/Service/*.php` - Domain services
2. `src/Api/Domain/User/Entity/User.php` - User entity
3. `src/Api/Application/Post/Command/*.php` - Command handlers
4. `src/Api/Infrastructure/Controller/PostController.php` - REST API
5. `src/Api/Infrastructure/Persistence/Doctrine/Repository/*.php` - Repositories
6. `config/doctrine/*.orm.xml` - Doctrine mappings
7. `src/Api/Infrastructure/Persistence/Fixtures/*.php` - Sample data

## 🤝 Contributing

This is an educational/presentation project demonstrating DDD with Symfony.

### Git Conventions

Branch naming:
```
feature/BLOG-<id>-<description>
bugfix/BLOG-<id>-<description>
```

Commit messages (Conventional Commits):
```
feat(BLOG-001): add user registration endpoint
fix(BLOG-002): resolve JWT token expiration issue
docs(BLOG-003): update API documentation
test(BLOG-004): add unit tests for Post entity
```

## 📝 License

MIT

## 👥 Author

Created for the presentation: **"From Tools to Teammates: Build AI Assisted Teams"**

This project showcases how AI-assisted development (Claude Code) can:
- Accelerate implementation of complex architectural patterns
- Maintain consistent code quality and style
- Generate comprehensive documentation
- Follow best practices and design patterns
- Reduce boilerplate and setup time

---

## 🔧 Troubleshooting

### Docker Issues

```bash
# Rebuild containers
docker-compose down
docker-compose up -d --build

# View logs
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f postgres
```

### Permission Issues

```bash
# Fix permissions
docker exec blog-php chown -R www:www /var/www/html/var
docker exec blog-php chmod -R 775 /var/www/html/var
```

### Composer Issues

```bash
# Clear Composer cache
docker exec blog-php composer clear-cache

# Update dependencies
docker exec blog-php composer update
```

### Database Issues

```bash
# Reset database
docker exec blog-php php bin/console doctrine:database:drop --force
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:migrations:migrate --no-interaction

# Validate schema
docker exec blog-php php bin/console doctrine:schema:validate
```

## Presentation Commands

```bash

# feature implementation example
/add-localization

# jira to pr (workflow example)
/jira-to-pr

# confluence documentation
/write-to-confluence https://pozitim.atlassian.net/wiki/spaces/~995900398/pages/3214180359/AI+Blog+API+Documentation

# figma to code (MCP example)
/figma-to-code https://www.figma.com/design/UUTDSi1H39dcbwlPwszSa5/sunu?node-id=1-30597&t=TCqOsoZde975PvgP-0

# Parallel feature implementation example
/parallel-features "Comment"
/parallel-features "Tag"
```

## 📞 Support

For implementation guidance, refer to:
- Plan file: `~/.claude/plans/partitioned-whistling-kernighan.md`
- Symfony docs: https://symfony.com/doc/current/index.html
- Doctrine docs: https://www.doctrine-project.org/
- DDD resources: https://martinfowler.com/bliki/DomainDrivenDesign.html

---

**Built with ❤️ using Symfony 7.3, PHP 8.3, DDD principles, and Claude Code AI assistance**
