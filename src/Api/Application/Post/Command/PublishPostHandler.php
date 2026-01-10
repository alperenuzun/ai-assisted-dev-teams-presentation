<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Command;

use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Publish Post Command Handler
 */
#[AsMessageHandler]
final readonly class PublishPostHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(PublishPostCommand $command): void
    {
        $post = $this->postRepository->findById(Uuid::fromString($command->postId));

        if (!$post) {
            throw new \RuntimeException('Post not found');
        }

        $post->publish();

        $this->postRepository->save($post);
    }
}
