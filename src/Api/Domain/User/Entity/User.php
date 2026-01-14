<?php

declare(strict_types=1);

namespace App\Api\Domain\User\Entity;

use App\Api\Domain\User\ValueObject\UserRole;
use App\SharedKernel\Domain\ValueObject\CreatedAt;
use App\SharedKernel\Domain\ValueObject\Email;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Entity
 *
 * Implements Symfony UserInterface for authentication
 */
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    private Uuid $id;

    private Email $email;

    private string $password;

    private UserRole $role;

    private CreatedAt $createdAt;

    private function __construct(
        Uuid $id,
        Email $email,
        string $hashedPassword,
        UserRole $role,
        CreatedAt $createdAt
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $hashedPassword;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public static function create(
        Uuid $id,
        Email $email,
        string $hashedPassword,
        ?UserRole $role = null
    ): self {
        return new self(
            $id,
            $email,
            $hashedPassword,
            $role ?? UserRole::user(),
            CreatedAt::now()
        );
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }

    public function changePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }

    public function promoteToAdmin(): void
    {
        $this->role = UserRole::admin();
    }

    // Symfony UserInterface methods
    public function getUserIdentifier(): string
    {
        return $this->email->toString();
    }

    public function getRoles(): array
    {
        return [$this->role->toString()];
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase as we don't store plain password
    }
}
