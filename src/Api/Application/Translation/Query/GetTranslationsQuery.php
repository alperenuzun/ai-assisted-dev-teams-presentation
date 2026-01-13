<?php

declare(strict_types=1);

namespace App\Api\Application\Translation\Query;

/**
 * GetTranslationsQuery
 *
 * Query to retrieve all translation messages for a given locale
 */
final readonly class GetTranslationsQuery
{
    public function __construct(
        public string $locale,
        public ?string $domain = null
    ) {}
}
