---
name: Create API Endpoint
description: Create a new REST API endpoint following DDD patterns
---

# Create API Endpoint Prompt

Create a new REST API endpoint following DDD architecture and CQRS pattern.

## Input Parameters

- **path**: {{path}}
- **method**: {{method}}
- **entity**: {{entity}}
- **request_params**: {{request_params}}
- **response_format**: {{response_format}}

## Architecture

Follow this layered structure:

```
1. Domain Layer (if new entity)
   ├── Entity/{{Entity}}.php
   ├── ValueObject/{{Entity}}*.php
   └── Repository/{{Entity}}RepositoryInterface.php

2. Application Layer
   ├── Command/{{Action}}{{Entity}}Command.php (for POST/PUT/DELETE)
   ├── Handler/{{Action}}{{Entity}}Handler.php
   ├── Query/{{Action}}{{Entity}}Query.php (for GET)
   └── Handler/{{Action}}{{Entity}}Handler.php

3. Infrastructure Layer
   ├── Controller/{{Entity}}Controller.php
   └── Persistence/Doctrine/Repository/Doctrine{{Entity}}Repository.php
```

## Requirements

1. Use strict PHP typing: `declare(strict_types=1);`
2. Follow CQRS with Symfony Messenger
3. Add `#[AsMessageHandler]` to handlers
4. Update `config/packages/messenger.yaml` routing
5. Create Pest PHP tests

## Command Template

```php
<?php

declare(strict_types=1);

namespace App\Api\Application\{{Entity}}\Command;

final readonly class {{Action}}{{Entity}}Command
{
    public function __construct(
        // Add parameters
    ) {}
}
```

## Handler Template

```php
<?php

declare(strict_types=1);

namespace App\Api\Application\{{Entity}}\Command;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class {{Action}}{{Entity}}Handler
{
    public function __construct(
        private {{Entity}}RepositoryInterface $repository
    ) {}

    public function __invoke({{Action}}{{Entity}}Command $command): string
    {
        // Implementation
    }
}
```

## Controller Template

```php
#[Route('/{{path}}', name: '{{entity}}_{{action}}', methods: ['{{method}}'])]
public function {{action}}(Request $request): JsonResponse
{
    $command = new {{Action}}{{Entity}}Command(
        // Map request to command
    );
    
    $envelope = $this->messageBus->dispatch($command);
    $result = $envelope->last(HandledStamp::class)?->getResult();
    
    return $this->json(['id' => $result], Response::HTTP_CREATED);
}
```

## Post-Generation

1. Update messenger.yaml:
   ```yaml
   App\Api\Application\{{Entity}}\Command\{{Action}}{{Entity}}Command: sync
   ```
2. Clear cache: `docker exec blog-php php bin/console cache:clear`
3. Run tests: `docker exec blog-php vendor/bin/pest`
