<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Persistence\Doctrine\Type;

use App\SharedKernel\Domain\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class EmailType extends Type
{
    private const NAME = 'email_vo';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 255]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email
    {
        if ($value === null || $value instanceof Email) {
            return $value;
        }

        return Email::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Email) {
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
