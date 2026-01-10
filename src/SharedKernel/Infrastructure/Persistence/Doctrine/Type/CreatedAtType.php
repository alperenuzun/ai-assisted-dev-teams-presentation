<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Persistence\Doctrine\Type;

use App\SharedKernel\Domain\ValueObject\CreatedAt;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class CreatedAtType extends Type
{
    private const NAME = 'created_at_vo';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CreatedAt
    {
        if ($value === null || $value instanceof CreatedAt) {
            return $value;
        }

        if ($value instanceof \DateTimeImmutable) {
            return CreatedAt::fromDateTime($value);
        }

        if ($value instanceof \DateTime) {
            return CreatedAt::fromDateTime(\DateTimeImmutable::createFromMutable($value));
        }

        $dateTime = \DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value
        );

        if ($dateTime === false) {
            return null;
        }

        return CreatedAt::fromDateTime($dateTime);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CreatedAt) {
            return $value->toDateTime()->format($platform->getDateTimeFormatString());
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
