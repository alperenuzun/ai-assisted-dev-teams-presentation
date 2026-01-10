<?php

declare(strict_types=1);

use App\SharedKernel\Domain\Exception\ValidationException;
use App\SharedKernel\Domain\ValueObject\Uuid;

test('can generate a valid uuid', function () {
    $uuid = Uuid::generate();

    expect($uuid)->toBeInstanceOf(Uuid::class)
        ->and($uuid->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

test('can create uuid from valid string', function () {
    $uuidString = '550e8400-e29b-41d4-a716-446655440000';
    $uuid = Uuid::fromString($uuidString);

    expect($uuid->toString())->toBe($uuidString);
});

test('throws exception for invalid uuid format', function () {
    Uuid::fromString('invalid-uuid-format');
})->throws(ValidationException::class, 'Invalid UUID format');

test('two uuids with same value are equal', function () {
    $uuidString = '550e8400-e29b-41d4-a716-446655440000';
    $uuid1 = Uuid::fromString($uuidString);
    $uuid2 = Uuid::fromString($uuidString);

    expect($uuid1->equals($uuid2))->toBeTrue();
});

test('two different uuids are not equal', function () {
    $uuid1 = Uuid::generate();
    $uuid2 = Uuid::generate();

    expect($uuid1->equals($uuid2))->toBeFalse();
});

test('uuid can be cast to string', function () {
    $uuidString = '550e8400-e29b-41d4-a716-446655440000';
    $uuid = Uuid::fromString($uuidString);

    expect((string) $uuid)->toBe($uuidString);
});
