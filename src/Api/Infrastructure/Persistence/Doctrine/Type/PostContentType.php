<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Doctrine\Type;

use App\Api\Domain\Post\ValueObject\PostContent;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PostContentType extends Type
{
    private const NAME = 'post_content';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PostContent
    {
        if ($value === null || $value instanceof PostContent) {
            return $value;
        }

        return PostContent::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof PostContent) {
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
