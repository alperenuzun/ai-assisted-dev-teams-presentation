<?php

declare(strict_types=1);

namespace App\Api\Domain\User\Repository;

use App\Api\Domain\User\Entity\User;
use App\SharedKernel\Domain\ValueObject\Email;
use App\SharedKernel\Domain\ValueObject\Uuid;

/**
 * User Repository Interface
 */
interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(Uuid $id): ?User;

    public function findByEmail(Email $email): ?User;

    /**
     * @return User[]
     */
    public function findAll(): array;

    public function delete(User $user): void;
}
