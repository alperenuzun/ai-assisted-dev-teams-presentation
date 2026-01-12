# Generate Entity Skill

Generate a complete DDD entity with all required components following project patterns.

## Usage

```
/generate-entity {EntityName} {BoundedContext} [--fields="field1:type,field2:type"]
```

## Examples

```
/generate-entity Comment Api --fields="content:string,authorId:uuid"
/generate-entity Category Api --fields="name:string,slug:string"
/generate-entity Tag SharedKernel --fields="name:string,color:string"
```

## What This Skill Creates

For entity `Comment` in `Api` context:

```
src/Api/
├── Domain/
│   └── Comment/
│       ├── Entity/
│       │   └── Comment.php              # Aggregate root entity
│       ├── ValueObject/
│       │   ├── CommentContent.php       # Value object for content
│       │   └── CommentId.php            # Optional: custom ID type
│       └── Repository/
│           └── CommentRepositoryInterface.php
│
├── Application/
│   └── Comment/
│       ├── Command/
│       │   ├── CreateCommentCommand.php
│       │   └── CreateCommentCommandHandler.php
│       └── Query/
│           ├── GetCommentQuery.php
│           ├── GetCommentQueryHandler.php
│           ├── ListCommentsQuery.php
│           └── ListCommentsQueryHandler.php
│
└── Infrastructure/
    └── Persistence/
        └── Doctrine/
            ├── Repository/
            │   └── DoctrineCommentRepository.php
            └── Type/
                └── CommentContentType.php   # Custom Doctrine type

config/
└── doctrine/
    └── Comment.Entity.Comment.orm.xml       # Doctrine XML mapping
```

## Entity Template

```php
<?php

declare(strict_types=1);

namespace App\{Context}\Domain\{Entity}\Entity;

use App\SharedKernel\Domain\ValueObject\Uuid;
use App\SharedKernel\Domain\ValueObject\CreatedAt;
// ... other imports

final class {Entity}
{
    private function __construct(
        private readonly Uuid $id,
        private {ValueObject} ${field},
        private readonly CreatedAt $createdAt,
    ) {}

    public static function create(
        Uuid $id,
        {ValueObject} ${field},
    ): self {
        return new self(
            id: $id,
            {field}: ${field},
            createdAt: CreatedAt::now(),
        );
    }

    // Getters...
}
```

## Value Object Template

```php
<?php

declare(strict_types=1);

namespace App\{Context}\Domain\{Entity}\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

final readonly class {ValueObject}
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        if (empty(trim($value))) {
            throw ValidationException::fromMessage('{ValueObject} cannot be empty');
        }

        // Add more validations as needed

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

## Doctrine XML Mapping Template

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping">
    <entity name="App\{Context}\Domain\{Entity}\Entity\{Entity}"
            table="{table_name}"
            repository-class="App\{Context}\Infrastructure\Persistence\Doctrine\Repository\Doctrine{Entity}Repository">

        <id name="id" type="uuid_vo" column="id"/>

        <field name="{field}" type="{custom_type}" column="{column_name}"/>

        <field name="createdAt" type="created_at_vo" column="created_at"/>
    </entity>
</doctrine-mapping>
```

## Post-Generation Steps

After generating, the skill will:

1. Register custom Doctrine types in `config/packages/doctrine.yaml`
2. Add message routing in `config/packages/messenger.yaml`
3. Clear cache: `docker exec blog-php php bin/console cache:clear`
4. Validate mapping: `docker exec blog-php php bin/console doctrine:schema:validate`

## Field Type Mappings

| Input Type | PHP Type | Value Object | Doctrine Type |
|------------|----------|--------------|---------------|
| `string` | `string` | `{Entity}{Field}` | `{entity}_{field}_vo` |
| `uuid` | `Uuid` | Shared `Uuid` | `uuid_vo` |
| `email` | `Email` | Shared `Email` | `email_vo` |
| `int` | `int` | Native | `integer` |
| `bool` | `bool` | Native | `boolean` |
| `datetime` | `DateTimeImmutable` | `CreatedAt` | `created_at_vo` |
| `text` | `string` | `{Entity}{Field}` | `{entity}_{field}_vo` |

## Demo Scenario

**Presenter says**: "Let's add a Comment feature to our blog. Watch how Claude generates all the DDD components..."

```
/generate-entity Comment Api --fields="content:text,postId:uuid"
```

**What audience sees**:
1. Entity class created with proper encapsulation
2. Value objects with validation
3. Repository interface (Domain) and implementation (Infrastructure)
4. CQRS Commands and Queries with Handlers
5. Doctrine mapping configured
6. All wiring done automatically

**Then test it**:
```bash
docker exec blog-php php bin/console doctrine:schema:validate
```
