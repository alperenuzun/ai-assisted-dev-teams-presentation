<?php

declare(strict_types=1);

namespace App\Api\Domain\Comment\Repository;

use App\Api\Domain\Comment\Entity\Comment;
use App\SharedKernel\Domain\ValueObject\Uuid;

interface CommentRepositoryInterface
{
    public function save(Comment $comment): void;

    public function findById(Uuid $id): ?Comment;

    /**
     * @return Comment[]
     */
    public function findByPostId(Uuid $postId): array;

    public function delete(Comment $comment): void;
}
