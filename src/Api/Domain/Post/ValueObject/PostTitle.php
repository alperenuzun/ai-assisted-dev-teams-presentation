<?php

declare(strict_types=1);

namespace App\Api\Domain\Post\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Post Title Value Object
 *
 * Represents a valid blog post title with length constraints
 */
final readonly class PostTitle
{
    private const MIN_LENGTH = 3;

    private const MAX_LENGTH = 255;

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
        $length = mb_strlen($this->value);

        if ($length < self::MIN_LENGTH) {
            throw new ValidationException(
                sprintf('Post title must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new ValidationException(
                sprintf('Post title cannot be longer than %d characters', self::MAX_LENGTH)
            );
        }

        if (trim($this->value) === '') {
            throw new ValidationException('Post title cannot be empty or only whitespace');
        }
    }
}
