<?php

declare(strict_types=1);

namespace App\Web\Infrastructure\Controller;

use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $publishedPosts = $this->postRepository->findPublished();

        return $this->json([
            'message' => 'Welcome to our blog!',
            'published_posts' => array_map(fn($post) => [
                'id' => $post->getId()->toString(),
                'title' => $post->getTitle()->toString(),
                'content' => substr($post->getContent()->toString(), 0, 200) . '...',
                'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
            ], $publishedPosts)
        ]);
    }
}
