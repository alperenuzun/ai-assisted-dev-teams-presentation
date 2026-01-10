<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Command;

/**
 * Create Post Command
 */
final readonly class CreatePostCommand
{
    public function __construct(
        public string $title,
        public string $content,
        public string $authorId
    ) {
    }
}
