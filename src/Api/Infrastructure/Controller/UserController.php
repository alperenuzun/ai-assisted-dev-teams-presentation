<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Api\Application\User\Command\RegisterUserCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/register', name: 'user_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Missing email or password'], Response::HTTP_BAD_REQUEST);
        }

        $command = new RegisterUserCommand(
            $data['email'],
            $data['password']
        );

        try {
            $envelope = $this->messageBus->dispatch($command);
            $userId = $envelope->last(HandledStamp::class)?->getResult();

            return $this->json([
                'id' => $userId,
                'message' => 'User registered successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
