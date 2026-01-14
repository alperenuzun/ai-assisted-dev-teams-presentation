<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Query;

/**
 * Get Post Query
 */
final readonly class GetPostQuery
{
    public function __construct(
        public string $postId
    ) {}
}
