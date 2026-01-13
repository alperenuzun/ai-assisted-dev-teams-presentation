<?php

declare(strict_types=1);

namespace App\Api\Domain\Comment\Entity;

use App\Api\Domain\Comment\ValueObject\CommentContent;
use App\SharedKernel\Domain\ValueObject\CreatedAt;
use App\SharedKernel\Domain\ValueObject\Uuid;

/**
 * Comment Entity
 *
 * Represents a comment on a blog post
 */
class Comment
{
    private Uuid $id;

    private CommentContent $content;

    private Uuid $postId;

    private Uuid $authorId;

    private CreatedAt $createdAt;

    private function __construct(
        Uuid $id,
        CommentContent $content,
        Uuid $postId,
        Uuid $authorId,
        CreatedAt $createdAt
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->postId = $postId;
        $this->authorId = $authorId;
        $this->createdAt = $createdAt;
    }

    /**
     * Factory method to create a new comment
     */
    public static function create(
        Uuid $id,
        CommentContent $content,
        Uuid $postId,
        Uuid $authorId
    ): self {
        return new self(
            $id,
            $content,
            $postId,
            $authorId,
            CreatedAt::now()
        );
    }

    /**
     * Update comment content
     */
    public function updateContent(CommentContent $content): void
    {
        $this->content = $content;
    }

    // Getters
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getContent(): CommentContent
    {
        return $this->content;
    }

    public function getPostId(): Uuid
    {
        return $this->postId;
    }

    public function getAuthorId(): Uuid
    {
        return $this->authorId;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }
}
