<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Controller;

use App\Api\Domain\Post\Repository\PostRepositoryInterface;
use App\Api\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    #[Route('', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $posts = $this->postRepository->findAll();
        $users = $this->userRepository->findAll();
        $publishedPosts = $this->postRepository->findPublished();

        return $this->json([
            'posts_count' => count($posts),
            'users_count' => count($users),
            'published_posts' => count($publishedPosts),
            'draft_posts' => count($posts) - count($publishedPosts),
        ]);
    }
}
