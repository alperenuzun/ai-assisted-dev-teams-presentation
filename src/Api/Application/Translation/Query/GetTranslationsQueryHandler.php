<?php

declare(strict_types=1);

namespace App\Api\Application\Translation\Query;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GetTranslationsQueryHandler
 *
 * Handles the GetTranslationsQuery to return all translations for a locale
 */
#[AsMessageHandler]
final readonly class GetTranslationsQueryHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private string $projectDir
    ) {}

    /**
     * Handle the GetTranslationsQuery and return array of translations
     *
     * @return array{locale: string, translations: array<string, string>}
     */
    public function __invoke(GetTranslationsQuery $query): array
    {
        $locale = $query->locale;
        $domain = $query->domain ?? 'messages';

        try {
            // Load translation files manually since we need all keys
            $translationFiles = $this->loadTranslationFiles($locale, $domain);
            $flattenedMessages = $this->flattenArray($translationFiles);

            return [
                'locale' => $locale,
                'domain' => $domain,
                'translations' => $flattenedMessages,
            ];
        } catch (\Exception $e) {
            // Return empty translations if locale not found
            return [
                'locale' => $locale,
                'domain' => $domain,
                'translations' => [],
            ];
        }
    }

    /**
     * Load translation files manually
     */
    private function loadTranslationFiles(string $locale, string $domain): array
    {
        $translationsPath = $this->projectDir.'/translations';
        $filename = sprintf('%s/%s.%s.yaml', $translationsPath, $domain, $locale);

        if (! file_exists($filename)) {
            return [];
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            return [];
        }

        return Yaml::parse($content) ?: [];
    }

    /**
     * Flatten nested array to dot notation
     *
     * @param  array<string, mixed>  $array
     * @return array<string, string>
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix !== '' ? $prefix.'.'.$key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = (string) $value;
            }
        }

        return $result;
    }
}
