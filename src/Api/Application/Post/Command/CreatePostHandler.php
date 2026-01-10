<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Command;

use App\Api\Domain\Post\Entity\Post;
use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use App\Api\Domain\Post\ValueObject\PostContent;
use App\Api\Domain\Post\ValueObject\PostTitle;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Create Post Command Handler
 */
#[AsMessageHandler]
final readonly class CreatePostHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(CreatePostCommand $command): string
    {
        $post = Post::create(
            Uuid::generate(),
            PostTitle::fromString($command->title),
            PostContent::fromString($command->content),
            Uuid::fromString($command->authorId)
        );

        $this->postRepository->save($post);

        return $post->getId()->toString();
    }
}
