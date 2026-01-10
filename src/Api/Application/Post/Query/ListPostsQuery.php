<?php

declare(strict_types=1);

namespace App\Api\Application\Post\Query;

/**
 * List Posts Query
 */
final readonly class ListPostsQuery
{
    public function __construct(
        public bool $onlyPublished = false
    ) {
    }
}
