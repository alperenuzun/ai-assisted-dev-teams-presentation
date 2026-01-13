<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Doctrine\Repository;

use App\Api\Domain\Comment\Entity\Comment;
use App\Api\Domain\Comment\Repository\CommentRepositoryInterface;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineCommentRepository implements CommentRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $this->entityManager->getRepository(Comment::class);
    }

    public function save(Comment $comment): void
    {
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }

    public function findById(Uuid $id): ?Comment
    {
        return $this->repository->find($id);
    }

    public function findByPostId(Uuid $postId): array
    {
        return $this->repository->findBy(['postId' => $postId]);
    }

    public function delete(Comment $comment): void
    {
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
    }
}
