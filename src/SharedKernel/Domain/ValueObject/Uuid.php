<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\ValueObject;

use App\SharedKernel\Domain\Exception\ValidationException;

/**
 * UUID Value Object
 *
 * Represents a universally unique identifier with validation
 */
final readonly class Uuid
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function generate(): self
    {
        return new self(self::generateV4());
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
        if (! self::isValid($this->value)) {
            throw new ValidationException(
                sprintf('Invalid UUID format: %s', $this->value)
            );
        }
    }

    private static function isValid(string $value): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        return preg_match($pattern, $value) === 1;
    }

    private static function generateV4(): string
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
