<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Persistence\DoctrineTypes;

use App\SharedKernel\Domain\ValueObject\Uuid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UuidType extends Type
{
    private const TYPE_NAME = 'uuid_vo';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Uuid
    {
        if ($value === null) {
            return null;
        }

        return Uuid::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value->toString();
        }

        return $value;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
