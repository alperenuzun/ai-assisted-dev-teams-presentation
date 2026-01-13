<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Command;

/**
 * Publish Post Command
 */
final readonly class PublishPostCommand
{
    public function __construct(
        public string $postId
    ) {}
}
