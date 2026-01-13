<?php

declare(strict_types=1);

namespace App\Api\Domain\Tag\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Tag Name Value Object
 */
final readonly class TagName
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new ValidationException('Tag name cannot be empty');
        }

        if (strlen($trimmed) < 2) {
            throw new ValidationException('Tag name must be at least 2 characters long');
        }

        if (strlen($trimmed) > 50) {
            throw new ValidationException('Tag name cannot exceed 50 characters');
        }

        // Check for valid characters (letters, numbers, spaces, hyphens, underscores)
        if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $trimmed)) {
            throw new ValidationException('Tag name contains invalid characters');
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(TagName $other): bool
    {
        return $this->value === $other->value;
    }
}