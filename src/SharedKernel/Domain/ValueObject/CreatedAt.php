<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\ValueObject;

/**
 * CreatedAt Value Object
 *
 * Represents a creation timestamp
 */
final readonly class CreatedAt
{
    private function __construct(
        private \DateTimeImmutable $value
    ) {
    }

    public static function now(): self
    {
        return new self(new \DateTimeImmutable());
    }

    public static function fromDateTime(\DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        return new self(new \DateTimeImmutable($value));
    }

    public function toDateTime(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function toString(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(self $other): bool
    {
        return $this->value == $other->value;
    }

    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function isAfter(self $other): bool
    {
        return $this->value > $other->value;
    }
}
