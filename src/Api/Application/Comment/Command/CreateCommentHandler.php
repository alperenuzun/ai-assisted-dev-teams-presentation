<?php

declare(strict_types=1);

namespace App\Api\Application\Comment\Command;

use App\Api\Domain\Comment\Entity\Comment;
use App\Api\Domain\Comment\Repository\CommentRepositoryInterface;
use App\Api\Domain\Comment\ValueObject\CommentContent;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateCommentHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
    ) {}

    public function __invoke(CreateCommentCommand $command): string
    {
        $commentId = Uuid::generate();
        $content = CommentContent::fromString($command->content);
        $postId = Uuid::fromString($command->postId);
        $authorId = Uuid::fromString($command->authorId);

        $comment = Comment::create(
            $commentId,
            $content,
            $postId,
            $authorId
        );

        $this->commentRepository->save($comment);

        return $commentId->toString();
    }
}
