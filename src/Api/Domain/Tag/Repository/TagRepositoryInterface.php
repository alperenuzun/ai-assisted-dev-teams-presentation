<?php

declare(strict_types=1);

namespace App\Api\Domain\Tag\Repository;

use App\Api\Domain\Tag\Entity\Tag;
use App\Api\Domain\Tag\ValueObject\TagSlug;
use App\SharedKernel\Domain\ValueObject\Uuid;

interface TagRepositoryInterface
{
    public function save(Tag $tag): void;

    public function findById(Uuid $id): ?Tag;

    public function findBySlug(TagSlug $slug): ?Tag;

    /**
     * @return Tag[]
     */
    public function findAll(): array;

    public function delete(Tag $tag): void;
}