<?php

declare(strict_types=1);

namespace App\Api\Application\Comment\Command;

final readonly class CreateCommentCommand
{
    public function __construct(
        public string $content,
        public string $postId,
        public string $authorId,
    ) {}
}
