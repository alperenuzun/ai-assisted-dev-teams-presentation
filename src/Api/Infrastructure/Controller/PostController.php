<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Api\Application\Post\Command\CreatePostCommand;
use App\Api\Application\Post\Command\PublishPostCommand;
use App\Api\Application\Post\Query\GetPostQuery;
use App\Api\Application\Post\Query\ListPostsQuery;
use App\Api\Domain\Post\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/posts', name: 'posts_')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $envelope = $this->messageBus->dispatch(new ListPostsQuery);
        /** @var Post[] $posts */
        $posts = $envelope->last(HandledStamp::class)?->getResult() ?? [];

        return $this->json(array_map(fn (Post $post) => [
            'id' => $post->getId()->toString(),
            'title' => $post->getTitle()->toString(),
            'content' => $post->getContent()->toString(),
            'status' => $post->getStatus()->toString(),
            'authorId' => $post->getAuthorId()->toString(),
            'createdAt' => $post->getCreatedAt()->toString(),
            'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
        ], $posts));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $envelope = $this->messageBus->dispatch(new GetPostQuery($id));
        /** @var Post|null $post */
        $post = $envelope->last(HandledStamp::class)?->getResult();

        if (! $post) {
            return $this->json(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $post->getId()->toString(),
            'title' => $post->getTitle()->toString(),
            'content' => $post->getContent()->toString(),
            'status' => $post->getStatus()->toString(),
            'authorId' => $post->getAuthorId()->toString(),
            'createdAt' => $post->getCreatedAt()->toString(),
            'publishedAt' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (! isset($data['title'], $data['content'])) {
            return $this->json(['error' => 'Missing title or content'], Response::HTTP_BAD_REQUEST);
        }

        // Get current user ID
        $user = $this->getUser();
        if (! $user) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $authorId = $user->getId()->toString();

        $command = new CreatePostCommand(
            $data['title'],
            $data['content'],
            $authorId
        );

        try {
            $envelope = $this->messageBus->dispatch($command);
            $postId = $envelope->last(HandledStamp::class)?->getResult();

            return $this->json(['id' => $postId], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/publish', name: 'publish', methods: ['POST'])]
    public function publish(string $id): JsonResponse
    {
        try {
            $this->messageBus->dispatch(new PublishPostCommand($id));

            return $this->json(['message' => 'Post published successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
