<?php

declare(strict_types=1);

namespace App\Api\Domain\Post\Entity;

use App\Api\Domain\Post\ValueObject\PostContent;
use App\Api\Domain\Post\ValueObject\PostStatus;
use App\Api\Domain\Post\ValueObject\PostTitle;
use App\SharedKernel\Domain\Exception\DomainException;
use App\SharedKernel\Domain\ValueObject\CreatedAt;
use App\SharedKernel\Domain\ValueObject\Uuid;

/**
 * Post Aggregate Root
 *
 * Represents a blog post with its business logic and invariants
 */
class Post
{
    private Uuid $id;

    private PostTitle $title;

    private PostContent $content;

    private PostStatus $status;

    private Uuid $authorId;

    private CreatedAt $createdAt;

    private ?\DateTimeImmutable $publishedAt = null;

    private function __construct(
        Uuid $id,
        PostTitle $title,
        PostContent $content,
        PostStatus $status,
        Uuid $authorId,
        CreatedAt $createdAt,
        ?\DateTimeImmutable $publishedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->status = $status;
        $this->authorId = $authorId;
        $this->createdAt = $createdAt;
        $this->publishedAt = $publishedAt;
    }

    /**
     * Factory method to create a new draft post
     */
    public static function create(
        Uuid $id,
        PostTitle $title,
        PostContent $content,
        Uuid $authorId
    ): self {
        return new self(
            $id,
            $title,
            $content,
            PostStatus::draft(),
            $authorId,
            CreatedAt::now()
        );
    }

    /**
     * Publish a draft post
     *
     * @throws DomainException if post is already published
     */
    public function publish(): void
    {
        if ($this->status->isPublished()) {
            throw new DomainException('Post is already published');
        }

        if ($this->status->isArchived()) {
            throw new DomainException('Cannot publish an archived post');
        }

        $this->status = PostStatus::published();
        $this->publishedAt = new \DateTimeImmutable;
    }

    /**
     * Archive a published post
     */
    public function archive(): void
    {
        if ($this->status->isArchived()) {
            throw new DomainException('Post is already archived');
        }

        $this->status = PostStatus::archived();
    }

    /**
     * Update post content
     */
    public function updateContent(PostTitle $title, PostContent $content): void
    {
        if ($this->status->isPublished()) {
            // In real application, might create a new version instead
            throw new DomainException('Cannot update published post');
        }

        $this->title = $title;
        $this->content = $content;
    }

    // Getters
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): PostTitle
    {
        return $this->title;
    }

    public function getContent(): PostContent
    {
        return $this->content;
    }

    public function getStatus(): PostStatus
    {
        return $this->status;
    }

    public function getAuthorId(): Uuid
    {
        return $this->authorId;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
