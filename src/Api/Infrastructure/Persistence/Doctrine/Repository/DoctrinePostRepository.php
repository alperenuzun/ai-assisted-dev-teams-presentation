<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Doctrine\Repository;

use App\Api\Domain\Post\Entity\Post;
use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post): void
    {
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();
    }

    public function findById(Uuid $id): ?Post
    {
        return $this->find($id->toString());
    }

    public function findAll(): array
    {
        return $this->findBy([], ['createdAt' => 'DESC']);
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function delete(Post $post): void
    {
        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();
    }
}
