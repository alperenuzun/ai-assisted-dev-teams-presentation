<?php

declare(strict_types=1);

namespace App\Api\Domain\User\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * User Role Value Object
 */
final readonly class UserRole
{
    private const ROLE_USER = 'ROLE_USER';

    private const ROLE_ADMIN = 'ROLE_ADMIN';

    private const VALID_ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
    ];

    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function user(): self
    {
        return new self(self::ROLE_USER);
    }

    public static function admin(): self
    {
        return new self(self::ROLE_ADMIN);
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

    public function isAdmin(): bool
    {
        return $this->value === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->value === self::ROLE_USER;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (! in_array($this->value, self::VALID_ROLES, true)) {
            throw new ValidationException(
                sprintf(
                    'Invalid user role: %s. Allowed values: %s',
                    $this->value,
                    implode(', ', self::VALID_ROLES)
                )
            );
        }
    }
}
