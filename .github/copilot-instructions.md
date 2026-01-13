# GitHub Copilot Instructions

## Project Overview

This is a Symfony 7.3 DDD (Domain-Driven Design) blog application. It demonstrates modern PHP architecture patterns with PHP 8.3, PostgreSQL 16, and Docker containers.

## Technology Stack

- **Language**: PHP 8.3 with strict types
- **Framework**: Symfony 7.3
- **Database**: PostgreSQL 16
- **ORM**: Doctrine with XML mappings
- **Testing**: Pest PHP
- **Container**: Docker with docker-compose
- **Architecture**: DDD with CQRS pattern

## Project Structure

```
src/
├── Api/
│   ├── Domain/           # Entities, Value Objects, Repository Interfaces
│   ├── Application/      # Commands, Queries, Handlers (CQRS)
│   └── Infrastructure/   # Controllers, Doctrine Repositories
├── Admin/
│   └── Infrastructure/   # Admin controllers
├── Web/
│   └── Infrastructure/   # Public web controllers
└── SharedKernel/
    ├── Domain/           # Shared Value Objects, Exceptions
    └── Infrastructure/   # Shared Doctrine Types
```

## Critical Development Rules

### Docker Commands
Always use Docker for PHP commands:
```bash
docker exec blog-php composer install
docker exec blog-php php bin/console cache:clear
docker exec blog-php vendor/bin/pest
```

### Port Configuration
- Application: `http://localhost:8081`
- NOT port 8080 (LocalStack conflict)

### Doctrine Configuration
- Use XML mappings in `config/doctrine/`
- NO annotations in entity classes
- Custom types for Value Objects in `doctrine.yaml`

### Database Operations
```bash
# Reset database
docker exec blog-php php bin/console doctrine:database:drop --force --if-exists
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:schema:create
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
```

## Code Style Guidelines

### Entity Pattern
```php
<?php

declare(strict_types=1);

namespace App\Api\Domain\Entity;

use App\SharedKernel\Domain\ValueObject\Uuid;
use App\SharedKernel\Domain\ValueObject\CreatedAt;

class Entity
{
    private Uuid $id;
    private CreatedAt $createdAt;

    private function __construct(Uuid $id, CreatedAt $createdAt)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public static function create(): self
    {
        return new self(Uuid::generate(), CreatedAt::now());
    }
}
```

### Controller Pattern
```php
<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;

#[Route('/resource', name: 'resource_')]
class ResourceController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Use message bus for CQRS
    }
}
```

### Test Pattern (Pest PHP)
```php
<?php

declare(strict_types=1);

test('entity can be created', function () {
    $entity = Entity::create();
    
    expect($entity->getId())->toBeInstanceOf(Uuid::class);
});
```

## Available AI Agents

The project includes specialized AI agents in `.ai-pack/shared/agents/`:

| Agent | Purpose |
|-------|---------|
| `qa-staging-endpoint-agent` | Creates QA automation endpoints in StageController |
| `backend-feature-agent` | Implements backend features with DDD patterns |
| `test-writer-agent` | Generates Pest PHP tests |
| `security-auditor-agent` | Reviews code for security issues |
| `tech-document-writer-agent` | Creates API documentation |

## QA Staging Endpoints

For QA automation, use the StageController at `/api/stage/`:
- `GET /api/stage/health` - Health check
- `GET /api/stage/users` - Mock user list
- `POST /api/stage/echo` - Echo request parameters
- `GET /api/stage/users/{id}` - Get mock user by ID
- `POST /api/stage/users` - Create mock user
- `PUT /api/stage/users/{id}` - Update mock user
- `DELETE /api/stage/users/{id}` - Delete mock user

Create new endpoints using the qa-staging-endpoint-agent.

## Authentication

- JWT-based authentication via LexikJWTAuthenticationBundle
- Default users:
  - admin@blog.com / password
  - user@blog.com / password

## Helpful Resources

- Agent definitions: `.ai-pack/shared/agents/`
- Commands: `.ai-pack/shared/commands/`
- Project documentation: `CLAUDE.md`

## Using Agents in GitHub Copilot

### In Copilot Chat
Reference agent files when asking questions:
```
@workspace Using the qa-staging-endpoint-agent pattern, create a new endpoint for orders.
```

### In Copilot Edits
Request changes following agent guidelines:
```
Following the backend-feature-agent patterns, implement a Category entity.
```

## Code Generation Guidelines

When generating code with Copilot:

1. **Always include** `declare(strict_types=1);`
2. **Use readonly** properties in PHP 8.3
3. **Follow DDD layers** - Domain → Application → Infrastructure
4. **Add PHPDoc** for all public methods
5. **Create tests** using Pest PHP syntax
6. **Use CQRS** - Commands for writes, Queries for reads
