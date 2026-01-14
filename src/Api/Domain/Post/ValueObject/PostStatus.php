<?php

declare(strict_types=1);

namespace App\Api\Domain\Post\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Post Status Value Object
 *
 * Represents the publication status of a blog post
 */
final readonly class PostStatus
{
    private const STATUS_DRAFT = 'draft';

    private const STATUS_PUBLISHED = 'published';

    private const STATUS_ARCHIVED = 'archived';

    private const VALID_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function draft(): self
    {
        return new self(self::STATUS_DRAFT);
    }

    public static function published(): self
    {
        return new self(self::STATUS_PUBLISHED);
    }

    public static function archived(): self
    {
        return new self(self::STATUS_ARCHIVED);
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

    public function isDraft(): bool
    {
        return $this->value === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->value === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->value === self::STATUS_ARCHIVED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (! in_array($this->value, self::VALID_STATUSES, true)) {
            throw new ValidationException(
                sprintf(
                    'Invalid post status: %s. Allowed values: %s',
                    $this->value,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }
    }
}
