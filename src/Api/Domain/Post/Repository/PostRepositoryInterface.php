<?php

declare(strict_types=1);

namespace App\Api\Domain\Post\Repository;

use App\Api\Domain\Post\Entity\Post;
use App\SharedKernel\Domain\ValueObject\Uuid;

/**
 * Post Repository Interface
 *
 * Defines the contract for Post persistence operations
 */
interface PostRepositoryInterface
{
    public function save(Post $post): void;

    public function findById(Uuid $id): ?Post;

    /**
     * @return Post[]
     */
    public function findAll(): array;

    /**
     * @return Post[]
     */
    public function findPublished(): array;

    public function delete(Post $post): void;
}
