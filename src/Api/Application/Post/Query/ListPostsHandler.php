<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Query;

use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * List Posts Query Handler
 */
#[AsMessageHandler]
final readonly class ListPostsHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(ListPostsQuery $query): array
    {
        if ($query->onlyPublished) {
            return $this->postRepository->findPublished();
        }

        return $this->postRepository->findAll();
    }
}
