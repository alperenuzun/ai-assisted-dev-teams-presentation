<?php

declare(strict_types=1);

namespace App\Api\Domain\Post\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Post Content Value Object
 *
 * Represents a valid blog post content with minimum length constraint
 */
final readonly class PostContent
{
    private const MIN_LENGTH = 10;

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
                sprintf('Post content must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        if (trim($this->value) === '') {
            throw new ValidationException('Post content cannot be empty or only whitespace');
        }
    }
}
