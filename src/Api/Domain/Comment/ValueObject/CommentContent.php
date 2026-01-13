<?php

declare(strict_types=1);

namespace App\Api\Domain\Comment\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Comment Content Value Object
 */
final readonly class CommentContent
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new ValidationException('Comment content cannot be empty');
        }

        if (strlen($trimmed) < 3) {
            throw new ValidationException('Comment content must be at least 3 characters long');
        }

        if (strlen($trimmed) > 1000) {
            throw new ValidationException('Comment content cannot exceed 1000 characters');
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(CommentContent $other): bool
    {
        return $this->value === $other->value;
    }
}
