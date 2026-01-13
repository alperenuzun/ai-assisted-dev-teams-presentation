<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Email Value Object
 *
 * Represents a valid email address with built-in validation
 */
final readonly class Email
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(
                sprintf('Invalid email format: %s', $this->value)
            );
        }

        if (strlen($this->value) > 255) {
            throw new ValidationException(
                'Email address cannot be longer than 255 characters'
            );
        }
    }
}
