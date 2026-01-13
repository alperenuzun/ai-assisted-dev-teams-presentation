<?php

declare(strict_types=1);

namespace App\Api\Application\Comment\Query;

final readonly class ListCommentsQuery
{
    public function __construct(
        public string $postId,
    ) {}
}
