<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Doctrine\Type;

use App\Api\Domain\Comment\ValueObject\CommentContent;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class CommentContentType extends Type
{
    private const NAME = 'comment_content';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CommentContent
    {
        if ($value === null || $value instanceof CommentContent) {
            return $value;
        }

        return CommentContent::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CommentContent) {
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
