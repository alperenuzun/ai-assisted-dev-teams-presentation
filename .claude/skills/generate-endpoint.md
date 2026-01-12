# Generate Endpoint Skill

Generate a complete REST API endpoint with controller, command/query, handler, and tests.

## Usage

```
/generate-endpoint {METHOD} {path} {BoundedContext} [--entity=EntityName]
```

## Examples

```
/generate-endpoint GET /api/comments Api --entity=Comment
/generate-endpoint POST /api/comments Api --entity=Comment
/generate-endpoint GET /api/comments/{id} Api --entity=Comment
/generate-endpoint DELETE /api/posts/{id} Api --entity=Post
```

## What This Skill Creates

For `POST /api/comments`:

```
src/Api/
├── Application/
│   └── Comment/
│       └── Command/
│           ├── CreateCommentCommand.php
│           └── CreateCommentCommandHandler.php
│
└── Infrastructure/
    └── Controller/
        └── CommentController.php (or adds method to existing)

config/packages/
└── messenger.yaml  (routing added)

tests/
└── Unit/
    └── Application/
        └── Comment/
            └── CreateCommentCommandHandlerTest.php
```

## Endpoint Types

### GET (List) - Query Pattern
```php
#[Route('/api/{entities}', name: 'api_{entities}_list', methods: ['GET'])]
public function list(MessageBusInterface $bus): JsonResponse
{
    $result = $bus->dispatch(new List{Entities}Query());
    // ...
}
```

### GET (Single) - Query Pattern
```php
#[Route('/api/{entities}/{id}', name: 'api_{entity}_get', methods: ['GET'])]
public function get(string $id, MessageBusInterface $bus): JsonResponse
{
    $result = $bus->dispatch(new Get{Entity}Query($id));
    // ...
}
```

### POST (Create) - Command Pattern
```php
#[Route('/api/{entities}', name: 'api_{entity}_create', methods: ['POST'])]
public function create(Request $request, MessageBusInterface $bus): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $id = $bus->dispatch(new Create{Entity}Command(...$data));
    // ...
}
```

### PUT/PATCH (Update) - Command Pattern
```php
#[Route('/api/{entities}/{id}', name: 'api_{entity}_update', methods: ['PUT'])]
public function update(string $id, Request $request, MessageBusInterface $bus): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $bus->dispatch(new Update{Entity}Command($id, ...$data));
    // ...
}
```

### DELETE - Command Pattern
```php
#[Route('/api/{entities}/{id}', name: 'api_{entity}_delete', methods: ['DELETE'])]
public function delete(string $id, MessageBusInterface $bus): JsonResponse
{
    $bus->dispatch(new Delete{Entity}Command($id));
    // ...
}
```

## Controller Template

```php
<?php

declare(strict_types=1);

namespace App\{Context}\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

final class {Entity}Controller extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('{path}', name: '{route_name}', methods: ['{METHOD}'])]
    public function {action}({params}): JsonResponse
    {
        // Implementation
    }
}
```

## Handler Template

```php
<?php

declare(strict_types=1);

namespace App\{Context}\Application\{Entity}\{Type};

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class {Action}{Entity}{Type}Handler
{
    public function __construct(
        private {Entity}RepositoryInterface $repository,
    ) {}

    public function __invoke({Action}{Entity}{Type} ${type}): mixed
    {
        // Implementation
    }
}
```

## Post-Generation Steps

1. Add routing to `messenger.yaml`
2. Clear cache
3. Generate test file
4. Verify with curl

## Automatic Test Generation

```php
<?php

declare(strict_types=1);

use App\{Context}\Application\{Entity}\{Type}\{Action}{Entity}{Type};
use App\{Context}\Application\{Entity}\{Type}\{Action}{Entity}{Type}Handler;

test('{action} {entity} {type} handler works correctly', function () {
    // Arrange
    $repository = mock({Entity}RepositoryInterface::class);
    $handler = new {Action}{Entity}{Type}Handler($repository);
    ${type} = new {Action}{Entity}{Type}(/* params */);

    // Act
    $result = $handler(${type});

    // Assert
    expect($result)->toBeString(); // or appropriate assertion
});
```

## Demo Scenario

**Presenter says**: "Now let's expose our Comment entity via REST API..."

```
/generate-endpoint POST /api/comments Api --entity=Comment
/generate-endpoint GET /api/comments Api --entity=Comment
/generate-endpoint GET /api/comments/{id} Api --entity=Comment
```

**Then test**:
```bash
# Create a comment
curl -X POST http://localhost:8081/api/comments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Great post!","postId":"uuid-here"}'

# List comments
curl http://localhost:8081/api/comments \
  -H "Authorization: Bearer $TOKEN"
```
