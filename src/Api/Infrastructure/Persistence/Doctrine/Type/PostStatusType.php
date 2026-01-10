<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Doctrine\Type;

use App\Api\Domain\Post\ValueObject\PostStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PostStatusType extends Type
{
    private const NAME = 'post_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 50]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PostStatus
    {
        if ($value === null || $value instanceof PostStatus) {
            return $value;
        }

        return PostStatus::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof PostStatus) {
            return $value->toString();
        }

        return $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
