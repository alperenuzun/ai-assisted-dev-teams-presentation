<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * StageController - QA Automation Staging Endpoints
 * 
 * This controller contains mock endpoints for QA automation testing.
 * These endpoints do NOT:
 * - Require authentication
 * - Access the database
 * - Contain business logic
 * - Store data persistently
 * 
 * All endpoints return JSON responses with a consistent format:
 * {
 *   "status": "success" | "error",
 *   "data": { ... },
 *   "timestamp": "ISO 8601 format"
 * }
 * 
 * @see .ai-pack/shared/agents/qa-staging-endpoint-agent.json for agent configuration
 * @see .ai-pack/shared/commands/create-staging-endpoint.md for usage
 */
#[Route('/stage', name: 'stage_')]
class StageController extends AbstractController
{
    /**
     * Health check endpoint for QA automation
     * 
     * Request: GET /api/stage/health
     * Response: {"status":"success","data":{"healthy":true},"timestamp":"..."}
     */
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => [
                'healthy' => true,
                'service' => 'stage-api',
                'version' => '1.0.0',
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

    /**
     * Echo endpoint - returns the received parameters
     * Useful for testing request/response handling
     * 
     * Request: POST /api/stage/echo
     * Body: Any JSON object
     * Response: {"status":"success","data":{"received":{...}},"timestamp":"..."}
     */
    #[Route('/echo', name: 'echo', methods: ['POST'])]
    public function echo(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $queryParams = $request->query->all();
        $headers = [];
        
        // Extract safe headers for debugging
        foreach (['Content-Type', 'Accept', 'X-Request-ID'] as $headerName) {
            if ($request->headers->has($headerName)) {
                $headers[$headerName] = $request->headers->get($headerName);
            }
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'received' => [
                    'body' => $body,
                    'query' => $queryParams,
                    'headers' => $headers,
                    'method' => $request->getMethod(),
                    'path' => $request->getPathInfo(),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

    /**
     * Mock users list endpoint for QA testing
     * 
     * Request: GET /api/stage/users?page=1&limit=10&status=active
     * Response: {"status":"success","data":{"users":[...],"total":100,"page":1,"limit":10},"timestamp":"..."}
     */
    #[Route('/users', name: 'users_list', methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 10)));
        $status = $request->query->get('status', 'active');

        $users = [];
        for ($i = 1; $i <= $limit; $i++) {
            $userId = ($page - 1) * $limit + $i;
            $users[] = [
                'id' => sprintf('user-%08d', $userId),
                'name' => 'Test User ' . $userId,
                'email' => sprintf('user%d@test.staging.com', $userId),
                'status' => $status,
                'role' => $userId % 5 === 0 ? 'admin' : 'user',
                'createdAt' => (new \DateTimeImmutable('-' . ($userId * 3600) . ' seconds'))->format('c'),
            ];
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'users' => $users,
                'total' => 100,
                'page' => $page,
                'limit' => $limit,
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

    /**
     * Get mock user by ID
     * 
     * Request: GET /api/stage/users/{id}
     * Response: {"status":"success","data":{"user":{...}},"timestamp":"..."}
     */
    #[Route('/users/{id}', name: 'users_get', methods: ['GET'])]
    public function getUser(string $id): JsonResponse
    {
        // Extract numeric part from user ID for consistent mock data
        $numericId = (int) preg_replace('/\D/', '', $id) ?: 1;

        return $this->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $id,
                    'name' => 'Test User ' . $numericId,
                    'email' => sprintf('user%d@test.staging.com', $numericId),
                    'status' => 'active',
                    'role' => $numericId % 5 === 0 ? 'admin' : 'user',
                    'profile' => [
                        'bio' => 'This is a test user bio for user ' . $numericId,
                        'avatar' => sprintf('https://api.dicebear.com/7.x/avataaars/svg?seed=user%d', $numericId),
                        'location' => 'Test City ' . ($numericId % 10),
                    ],
                    'createdAt' => (new \DateTimeImmutable('-' . ($numericId * 3600) . ' seconds'))->format('c'),
                    'updatedAt' => (new \DateTimeImmutable('-' . ($numericId * 1800) . ' seconds'))->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

    /**
     * Create mock user endpoint
     * 
     * Request: POST /api/stage/users
     * Body: {"name":"string","email":"string","role":"user|admin"}
     * Response: {"status":"success","data":{"user":{...}},"timestamp":"..."}
     */
    #[Route('/users', name: 'users_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        // Validate required fields
        $requiredFields = ['name', 'email'];
        $missingFields = array_diff($requiredFields, array_keys($data));
        
        if (!empty($missingFields)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable())->format('c'),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Generate mock user ID
        $userId = sprintf('user-%08d', random_int(1000000, 9999999));

        return $this->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $userId,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'status' => 'active',
                    'role' => $data['role'] ?? 'user',
                    'createdAt' => (new \DateTimeImmutable())->format('c'),
                    'updatedAt' => (new \DateTimeImmutable())->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update mock user endpoint
     * 
     * Request: PUT /api/stage/users/{id}
     * Body: {"name":"string","email":"string","status":"active|inactive"}
     * Response: {"status":"success","data":{"user":{...}},"timestamp":"..."}
     */
    #[Route('/users/{id}', name: 'users_update', methods: ['PUT'])]
    public function updateUser(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $numericId = (int) preg_replace('/\D/', '', $id) ?: 1;

        return $this->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $id,
                    'name' => $data['name'] ?? 'Test User ' . $numericId,
                    'email' => $data['email'] ?? sprintf('user%d@test.staging.com', $numericId),
                    'status' => $data['status'] ?? 'active',
                    'role' => $data['role'] ?? ($numericId % 5 === 0 ? 'admin' : 'user'),
                    'createdAt' => (new \DateTimeImmutable('-' . ($numericId * 3600) . ' seconds'))->format('c'),
                    'updatedAt' => (new \DateTimeImmutable())->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

    /**
     * Delete mock user endpoint
     * 
     * Request: DELETE /api/stage/users/{id}
     * Response: {"status":"success","data":{"message":"..."},"timestamp":"..."}
     */
    #[Route('/users/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function deleteUser(string $id): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => [
                'message' => sprintf('User %s has been deleted', $id),
                'deletedId' => $id,
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

    // ============================================================================
    // QA AUTOMATION: Add your custom staging endpoints below this line
    // Use the /create-staging-endpoint command to generate new endpoints
    // ============================================================================
}
