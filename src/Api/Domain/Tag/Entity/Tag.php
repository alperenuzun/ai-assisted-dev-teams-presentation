<?php

declare(strict_types=1);

namespace App\Api\Domain\Tag\Entity;

use App\Api\Domain\Tag\ValueObject\TagName;
use App\Api\Domain\Tag\ValueObject\TagSlug;
use App\Api\Domain\Tag\ValueObject\TagColor;
use App\SharedKernel\Domain\ValueObject\CreatedAt;
use App\SharedKernel\Domain\ValueObject\Uuid;

/**
 * Tag Entity
 *
 * Represents a tag for categorizing content
 */
class Tag
{
    private Uuid $id;

    private TagName $name;

    private TagSlug $slug;

    private TagColor $color;

    private CreatedAt $createdAt;

    private function __construct(
        Uuid $id,
        TagName $name,
        TagSlug $slug,
        TagColor $color,
        CreatedAt $createdAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->color = $color;
        $this->createdAt = $createdAt;
    }

    /**
     * Factory method to create a new tag
     */
    public static function create(
        Uuid $id,
        TagName $name,
        TagSlug $slug,
        TagColor $color
    ): self {
        return new self(
            $id,
            $name,
            $slug,
            $color,
            CreatedAt::now()
        );
    }

    /**
     * Update tag properties
     */
    public function updateProperties(TagName $name, TagSlug $slug, TagColor $color): void
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->color = $color;
    }

    // Getters
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): TagName
    {
        return $this->name;
    }

    public function getSlug(): TagSlug
    {
        return $this->slug;
    }

    public function getColor(): TagColor
    {
        return $this->color;
    }

    public function getCreatedAt(): CreatedAt
    {
        return $this->createdAt;
    }
}