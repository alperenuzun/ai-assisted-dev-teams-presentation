<?php

declare(strict_types=1);

namespace App\Api\Application\User\Command;

/**
 * Register User Command
 */
final readonly class RegisterUserCommand
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }
}
