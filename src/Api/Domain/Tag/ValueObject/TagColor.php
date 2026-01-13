<?php

declare(strict_types=1);

namespace App\Api\Domain\Tag\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * Tag Color Value Object
 */
final readonly class TagColor
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new ValidationException('Tag color cannot be empty');
        }

        // Support hex colors (with or without #)
        if (!str_starts_with($trimmed, '#')) {
            $trimmed = '#' . $trimmed;
        }

        // Validate hex color format
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $trimmed)) {
            throw new ValidationException('Tag color must be a valid hex color (e.g., #FF0000 or #F00)');
        }

        // Convert 3-digit hex to 6-digit hex
        if (strlen($trimmed) === 4) {
            $r = $trimmed[1];
            $g = $trimmed[2];
            $b = $trimmed[3];
            $trimmed = "#$r$r$g$g$b$b";
        }

        return new self(strtoupper($trimmed));
    }

    public static function blue(): self
    {
        return new self('#3B82F6');
    }

    public static function green(): self
    {
        return new self('#10B981');
    }

    public static function red(): self
    {
        return new self('#EF4444');
    }

    public static function yellow(): self
    {
        return new self('#F59E0B');
    }

    public static function purple(): self
    {
        return new self('#8B5CF6');
    }

    public static function gray(): self
    {
        return new self('#6B7280');
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(TagColor $other): bool
    {
        return $this->value === $other->value;
    }
}