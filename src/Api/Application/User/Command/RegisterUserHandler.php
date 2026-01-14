<?php

declare(strict_types=1);

namespace App\Api\Application\User\Command;

use App\Api\Domain\User\Entity\User;
use App\Api\Domain\User\Repository\UserRepositoryInterface;
use App\SharedKernel\Domain\ValueObject\Email;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Register User Command Handler
 */
#[AsMessageHandler]
final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(RegisterUserCommand $command): string
    {
        $email = Email::fromString($command->email);

        // Check if user already exists
        if ($this->userRepository->findByEmail($email)) {
            throw new \RuntimeException('User with this email already exists');
        }

        $user = User::create(
            Uuid::generate(),
            $email,
            '', // Temporary empty password
            null
        );

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $command->password);
        $user->changePassword($hashedPassword);

        $this->userRepository->save($user);

        return $user->getId()->toString();
    }
}
