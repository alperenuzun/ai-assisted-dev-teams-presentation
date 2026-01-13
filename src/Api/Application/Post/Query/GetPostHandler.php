<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Query;

use App\Api\Domain\Post\Entity\Post;
use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Get Post Query Handler
 */
#[AsMessageHandler]
final readonly class GetPostHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    public function __invoke(GetPostQuery $query): ?Post
    {
        return $this->postRepository->findById(Uuid::fromString($query->postId));
    }
}
