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
            'timestamp' => (new \DateTimeImmutable)->format('c'),
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
            'timestamp' => (new \DateTimeImmutable)->format('c'),
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
                'name' => 'Test User '.$userId,
                'email' => sprintf('user%d@test.staging.com', $userId),
                'status' => $status,
                'role' => $userId % 5 === 0 ? 'admin' : 'user',
                'createdAt' => (new \DateTimeImmutable('-'.($userId * 3600).' seconds'))->format('c'),
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
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }

    /**
     * Get mock user by ID
     *
     * Request: GET /api/stage/users/{id}
     * Response: {"status":"success","data":{"user":{...}},"timestamp":"..."}
     */
    #[Route('/users/{id}', name: 'users_get', methods: ['GET'])]
    public function getUserById(string $id): JsonResponse
    {
        // Extract numeric part from user ID for consistent mock data
        $numericId = (int) preg_replace('/\D/', '', $id) ?: 1;

        return $this->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $id,
                    'name' => 'Test User '.$numericId,
                    'email' => sprintf('user%d@test.staging.com', $numericId),
                    'status' => 'active',
                    'role' => $numericId % 5 === 0 ? 'admin' : 'user',
                    'profile' => [
                        'bio' => 'This is a test user bio for user '.$numericId,
                        'avatar' => sprintf('https://api.dicebear.com/7.x/avataaars/svg?seed=user%d', $numericId),
                        'location' => 'Test City '.($numericId % 10),
                    ],
                    'createdAt' => (new \DateTimeImmutable('-'.($numericId * 3600).' seconds'))->format('c'),
                    'updatedAt' => (new \DateTimeImmutable('-'.($numericId * 1800).' seconds'))->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
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

        if (! empty($missingFields)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: '.implode(', ', $missingFields),
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable)->format('c'),
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
                    'createdAt' => (new \DateTimeImmutable)->format('c'),
                    'updatedAt' => (new \DateTimeImmutable)->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
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
                    'name' => $data['name'] ?? 'Test User '.$numericId,
                    'email' => $data['email'] ?? sprintf('user%d@test.staging.com', $numericId),
                    'status' => $data['status'] ?? 'active',
                    'role' => $data['role'] ?? ($numericId % 5 === 0 ? 'admin' : 'user'),
                    'createdAt' => (new \DateTimeImmutable('-'.($numericId * 3600).' seconds'))->format('c'),
                    'updatedAt' => (new \DateTimeImmutable)->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
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
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }

    // ============================================================================
    // QA AUTOMATION: Add your custom staging endpoints below this line
    // Use the /create-staging-endpoint command to generate new endpoints
    // ============================================================================

    /**
     * Mock products list endpoint for QA testing
     *
     * Request: GET /api/stage/products?page=1&limit=10&category=electronics&status=available
     * Response: {"status":"success","data":{"products":[...],"total":250,"page":1,"limit":10},"timestamp":"..."}
     */
    #[Route('/products', name: 'products_list', methods: ['GET'])]
    public function listProducts(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 10)));
        $category = $request->query->get('category', 'electronics');
        $status = $request->query->get('status', 'available');

        $categories = ['electronics', 'clothing', 'books', 'home', 'sports'];
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'Microsoft', 'Dell', 'HP'];

        $products = [];
        for ($i = 1; $i <= $limit; $i++) {
            $productId = ($page - 1) * $limit + $i;
            $selectedCategory = in_array($category, $categories) ? $category : $categories[$productId % count($categories)];
            $products[] = [
                'id' => sprintf('prod-%08d', $productId),
                'name' => sprintf('%s Product %d', ucfirst($selectedCategory), $productId),
                'sku' => sprintf('SKU-%s-%04d', strtoupper(substr($selectedCategory, 0, 3)), $productId),
                'category' => $selectedCategory,
                'brand' => $brands[$productId % count($brands)],
                'price' => round(random_int(999, 99999) / 100, 2),
                'currency' => 'USD',
                'status' => $status,
                'stock' => random_int(0, 100),
                'description' => sprintf('This is a mock %s product for QA testing purposes.', $selectedCategory),
                'createdAt' => (new \DateTimeImmutable('-'.($productId * 1800).' seconds'))->format('c'),
                'updatedAt' => (new \DateTimeImmutable('-'.($productId * 900).' seconds'))->format('c'),
            ];
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'products' => $products,
                'total' => 250,
                'page' => $page,
                'limit' => $limit,
                'filters' => [
                    'category' => $category,
                    'status' => $status,
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }

    /**
     * Get mock product by ID
     *
     * Request: GET /api/stage/products/{id}
     * Response: {"status":"success","data":{"product":{...}},"timestamp":"..."}
     */
    #[Route('/products/{id}', name: 'products_get', methods: ['GET'])]
    public function getProductById(string $id): JsonResponse
    {
        // Extract numeric part from product ID for consistent mock data
        $numericId = (int) preg_replace('/\D/', '', $id) ?: 1;

        $categories = ['electronics', 'clothing', 'books', 'home', 'sports'];
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'Microsoft', 'Dell', 'HP'];
        $category = $categories[$numericId % count($categories)];

        return $this->json([
            'status' => 'success',
            'data' => [
                'product' => [
                    'id' => $id,
                    'name' => sprintf('%s Product %d', ucfirst($category), $numericId),
                    'sku' => sprintf('SKU-%s-%04d', strtoupper(substr($category, 0, 3)), $numericId),
                    'category' => $category,
                    'brand' => $brands[$numericId % count($brands)],
                    'price' => round(($numericId * 123 + 999) / 100, 2),
                    'currency' => 'USD',
                    'status' => $numericId % 10 === 0 ? 'discontinued' : 'available',
                    'stock' => max(0, 100 - ($numericId % 25)),
                    'description' => sprintf('This is a detailed mock %s product for QA testing. ID: %d', $category, $numericId),
                    'specifications' => [
                        'weight' => sprintf('%.1f kg', ($numericId % 50) / 10 + 0.5),
                        'dimensions' => sprintf('%dx%dx%d cm',
                            10 + ($numericId % 20),
                            5 + ($numericId % 15),
                            2 + ($numericId % 8)
                        ),
                        'warranty' => $numericId % 3 === 0 ? '2 years' : '1 year',
                    ],
                    'images' => [
                        sprintf('https://picsum.photos/400/400?random=%d', $numericId),
                        sprintf('https://picsum.photos/400/400?random=%d', $numericId + 1000),
                    ],
                    'createdAt' => (new \DateTimeImmutable('-'.($numericId * 1800).' seconds'))->format('c'),
                    'updatedAt' => (new \DateTimeImmutable('-'.($numericId * 900).' seconds'))->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }

    /**
     * Create mock product endpoint
     *
     * Request: POST /api/stage/products
     * Body: {"name":"string","category":"string","brand":"string","price":99.99,"stock":50}
     * Response: {"status":"success","data":{"product":{...}},"timestamp":"..."}
     */
    #[Route('/products', name: 'products_create', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        // Validate required fields
        $requiredFields = ['name', 'category', 'price'];
        $missingFields = array_diff($requiredFields, array_keys($data));

        if (! empty($missingFields)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: '.implode(', ', $missingFields),
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable)->format('c'),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate price is numeric
        if (! is_numeric($data['price']) || $data['price'] < 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Price must be a positive number',
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable)->format('c'),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Generate mock product ID
        $productId = sprintf('prod-%08d', random_int(10000000, 99999999));
        $skuPrefix = strtoupper(substr($data['category'], 0, 3));

        return $this->json([
            'status' => 'success',
            'data' => [
                'product' => [
                    'id' => $productId,
                    'name' => $data['name'],
                    'sku' => sprintf('SKU-%s-%04d', $skuPrefix, random_int(1000, 9999)),
                    'category' => $data['category'],
                    'brand' => $data['brand'] ?? 'Generic',
                    'price' => round((float) $data['price'], 2),
                    'currency' => 'USD',
                    'status' => $data['status'] ?? 'available',
                    'stock' => $data['stock'] ?? 0,
                    'description' => $data['description'] ?? sprintf('Mock %s product for testing', $data['category']),
                    'createdAt' => (new \DateTimeImmutable)->format('c'),
                    'updatedAt' => (new \DateTimeImmutable)->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update mock product endpoint
     *
     * Request: PUT /api/stage/products/{id}
     * Body: {"name":"string","price":99.99,"stock":25,"status":"available|discontinued"}
     * Response: {"status":"success","data":{"product":{...}},"timestamp":"..."}
     */
    #[Route('/products/{id}', name: 'products_update', methods: ['PUT'])]
    public function updateProduct(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $numericId = (int) preg_replace('/\D/', '', $id) ?: 1;

        $categories = ['electronics', 'clothing', 'books', 'home', 'sports'];
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'Microsoft', 'Dell', 'HP'];
        $currentCategory = $categories[$numericId % count($categories)];

        // Validate price if provided
        if (isset($data['price']) && (! is_numeric($data['price']) || $data['price'] < 0)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Price must be a positive number',
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable)->format('c'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'product' => [
                    'id' => $id,
                    'name' => $data['name'] ?? sprintf('%s Product %d', ucfirst($currentCategory), $numericId),
                    'sku' => sprintf('SKU-%s-%04d', strtoupper(substr($currentCategory, 0, 3)), $numericId),
                    'category' => $data['category'] ?? $currentCategory,
                    'brand' => $data['brand'] ?? $brands[$numericId % count($brands)],
                    'price' => isset($data['price']) ? round((float) $data['price'], 2) : round(($numericId * 123 + 999) / 100, 2),
                    'currency' => 'USD',
                    'status' => $data['status'] ?? 'available',
                    'stock' => $data['stock'] ?? max(0, 100 - ($numericId % 25)),
                    'description' => $data['description'] ?? sprintf('Updated mock %s product for testing', $currentCategory),
                    'createdAt' => (new \DateTimeImmutable('-'.($numericId * 1800).' seconds'))->format('c'),
                    'updatedAt' => (new \DateTimeImmutable)->format('c'),
                ],
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }

    /**
     * Delete mock product endpoint
     *
     * Request: DELETE /api/stage/products/{id}
     * Response: {"status":"success","data":{"message":"..."},"timestamp":"..."}
     */
    #[Route('/products/{id}', name: 'products_delete', methods: ['DELETE'])]
    public function deleteProduct(string $id): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => [
                'message' => sprintf('Product %s has been deleted', $id),
                'deletedId' => $id,
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }

    /**
     * Bulk operations endpoint for products
     *
     * Request: POST /api/stage/products/bulk
     * Body: {"action":"update_stock","products":[{"id":"prod-123","stock":50}]}
     * Response: {"status":"success","data":{"updated":1,"failed":0},"timestamp":"..."}
     */
    #[Route('/products/bulk', name: 'products_bulk', methods: ['POST'])]
    public function bulkProductOperations(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $allowedActions = ['update_stock', 'update_status', 'delete'];
        $action = $data['action'] ?? '';

        if (! in_array($action, $allowedActions)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid action. Allowed: '.implode(', ', $allowedActions),
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable)->format('c'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $products = $data['products'] ?? [];
        if (empty($products) || ! is_array($products)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Products array is required and cannot be empty',
                'code' => 'VALIDATION_ERROR',
                'timestamp' => (new \DateTimeImmutable)->format('c'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $updated = 0;
        $failed = 0;
        $results = [];

        foreach ($products as $product) {
            if (! isset($product['id'])) {
                $failed++;
                $results[] = ['id' => 'unknown', 'status' => 'failed', 'reason' => 'Missing product ID'];

                continue;
            }

            // Simulate success/failure based on ID pattern
            $numericId = (int) preg_replace('/\D/', '', $product['id']);
            if ($numericId % 13 === 0) {
                // Simulate some failures for testing
                $failed++;
                $results[] = [
                    'id' => $product['id'],
                    'status' => 'failed',
                    'reason' => 'Product not found or locked',
                ];
            } else {
                $updated++;
                $results[] = [
                    'id' => $product['id'],
                    'status' => 'updated',
                    'action' => $action,
                ];
            }
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'action' => $action,
                'summary' => [
                    'total' => count($products),
                    'updated' => $updated,
                    'failed' => $failed,
                ],
                'results' => $results,
            ],
            'timestamp' => (new \DateTimeImmutable)->format('c'),
        ]);
    }
}
