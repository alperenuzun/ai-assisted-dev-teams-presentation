<?php

declare(strict_types=1);

namespace App\Api\Domain\Tag\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Tag Slug Value Object
 */
final readonly class TagSlug
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new ValidationException('Tag slug cannot be empty');
        }

        if (strlen($trimmed) < 2) {
            throw new ValidationException('Tag slug must be at least 2 characters long');
        }

        if (strlen($trimmed) > 50) {
            throw new ValidationException('Tag slug cannot exceed 50 characters');
        }

        // Check for valid slug format (lowercase letters, numbers, hyphens)
        if (!preg_match('/^[a-z0-9\-]+$/', $trimmed)) {
            throw new ValidationException('Tag slug must contain only lowercase letters, numbers, and hyphens');
        }

        // Must not start or end with hyphen
        if (str_starts_with($trimmed, '-') || str_ends_with($trimmed, '-')) {
            throw new ValidationException('Tag slug cannot start or end with a hyphen');
        }

        return new self($trimmed);
    }

    public static function fromName(TagName $name): self
    {
        $slug = strtolower($name->toString());
        $slug = preg_replace('/[^a-z0-9\s\-_]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = trim($slug, '-');

        if (empty($slug)) {
            throw new ValidationException('Cannot generate valid slug from tag name');
        }

        return new self($slug);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(TagSlug $other): bool
    {
        return $this->value === $other->value;
    }
}