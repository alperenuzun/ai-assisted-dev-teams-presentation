<?php

declare(strict_types=1);

namespace App\Api\Application\Comment\Query;

use App\Api\Domain\Comment\Repository\CommentRepositoryInterface;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListCommentsHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
    ) {}

    public function __invoke(ListCommentsQuery $query): array
    {
        $postId = Uuid::fromString($query->postId);
        $comments = $this->commentRepository->findByPostId($postId);

        return array_map(function ($comment) {
            return [
                'id' => $comment->getId()->toString(),
                'content' => $comment->getContent()->toString(),
                'postId' => $comment->getPostId()->toString(),
                'authorId' => $comment->getAuthorId()->toString(),
                'createdAt' => $comment->getCreatedAt()->toDateTime()->format('Y-m-d H:i:s'),
            ];
        }, $comments);
    }
}
