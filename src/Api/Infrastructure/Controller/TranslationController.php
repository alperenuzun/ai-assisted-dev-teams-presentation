<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Api\Application\Translation\Query\GetTranslationsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

/**
 * TranslationController
 *
 * Handles translation-related API endpoints
 */
#[Route('/translations', name: 'api_translations_')]
class TranslationController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    /**
     * Get available locales
     */
    #[Route('/locales', name: 'locales', methods: ['GET'])]
    public function getAvailableLocales(): JsonResponse
    {
        // For now, return the locales we have translation files for
        $availableLocales = [
            [
                'code' => 'en',
                'name' => 'English',
                'native' => 'English',
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
                'native' => 'Türkçe',
            ],
        ];

        return $this->json([
            'locales' => $availableLocales,
            'default' => 'en',
        ]);
    }

    /**
     * Get available translation domains
     */
    #[Route('/domains', name: 'domains', methods: ['GET'])]
    public function getAvailableDomains(): JsonResponse
    {
        $availableDomains = [
            [
                'name' => 'messages',
                'description' => 'General application messages',
            ],
            [
                'name' => 'validators',
                'description' => 'Form validation messages',
            ],
        ];

        return $this->json([
            'domains' => $availableDomains,
        ]);
    }

    /**
     * Get all translations for a specific locale
     *
     * @param  string  $locale  The locale to get translations for (e.g., 'en', 'tr')
     */
    #[Route('/{locale}', name: 'get', methods: ['GET'])]
    public function getTranslations(string $locale, Request $request): JsonResponse
    {
        // Validate locale format (2 character ISO code)
        if (! preg_match('/^[a-z]{2}$/', $locale)) {
            return $this->json([
                'error' => 'Invalid locale format. Use 2-character ISO codes (e.g., en, tr)',
                'code' => 'INVALID_LOCALE',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get optional domain parameter
        $domain = $request->query->get('domain', 'messages');

        try {
            // Dispatch query through message bus
            $envelope = $this->messageBus->dispatch(new GetTranslationsQuery($locale, $domain));

            // Get the result from the handled stamp
            $handledStamp = $envelope->last(HandledStamp::class);
            if (! $handledStamp) {
                return $this->json([
                    'error' => 'Translation query was not handled',
                    'code' => 'HANDLER_ERROR',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $result = $handledStamp->getResult();

            return $this->json($result);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to retrieve translations',
                'message' => $e->getMessage(),
                'code' => 'TRANSLATION_ERROR',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
