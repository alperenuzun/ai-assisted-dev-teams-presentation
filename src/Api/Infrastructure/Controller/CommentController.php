<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Api\Application\Comment\Command\CreateCommentCommand;
use App\Api\Application\Comment\Query\ListCommentsQuery;
use App\SharedKernel\Domain\Exception\DomainException;
use App\SharedKernel\Domain\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class CommentController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {}

    #[Route('/posts/{postId}/comments', name: 'create_comment', methods: ['POST'])]
    public function createComment(string $postId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (! isset($data['content'])) {
                return $this->json(['error' => 'Content is required'], Response::HTTP_BAD_REQUEST);
            }

            // Get current user ID from security context
            $user = $this->getUser();
            if (! $user) {
                return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
            }

            $command = new CreateCommentCommand(
                content: $data['content'],
                postId: $postId,
                authorId: $user->getId()->toString(),
            );

            $envelope = $this->messageBus->dispatch($command);
            $commentId = $envelope->last(HandledStamp::class)?->getResult();

            return $this->json([
                'id' => $commentId,
                'message' => 'Comment created successfully',
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (DomainException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/posts/{postId}/comments', name: 'list_comments', methods: ['GET'])]
    public function listComments(string $postId): JsonResponse
    {
        try {
            $query = new ListCommentsQuery($postId);
            $envelope = $this->messageBus->dispatch($query);
            $comments = $envelope->last(HandledStamp::class)?->getResult() ?? [];

            return $this->json(['data' => $comments]);

        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
