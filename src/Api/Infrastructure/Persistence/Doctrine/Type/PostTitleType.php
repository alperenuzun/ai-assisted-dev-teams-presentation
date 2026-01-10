<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Doctrine\Type;

use App\Api\Domain\Post\ValueObject\PostTitle;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PostTitleType extends Type
{
    private const NAME = 'post_title';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 255]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PostTitle
    {
        if ($value === null || $value instanceof PostTitle) {
            return $value;
        }

        return PostTitle::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof PostTitle) {
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
