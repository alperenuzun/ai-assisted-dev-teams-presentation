---
description: Create a QA automation staging endpoint in StageController
agent: qa-staging-endpoint-specialist
---

# Create Staging Endpoint

Creates a new staging/mock endpoint in StageController for QA automation testing purposes.

## Purpose

This command generates simple, direct API endpoints without business logic that QA teams can use for:
- Frontend integration testing
- API contract verification
- Automated test scenarios
- Mock data generation

## Usage

```bash
/create-staging-endpoint <endpoint-name> <http-method> --request "<params>" --response "<params>"
```

### Parameters

| Parameter | Required | Description | Example |
|-----------|----------|-------------|---------|
| `endpoint-name` | Yes | The endpoint path (without /api/stage prefix) | `/users`, `/orders/{id}` |
| `http-method` | Yes | HTTP method | `GET`, `POST`, `PUT`, `DELETE` |
| `--request` | Yes | Request parameters JSON | `'[{"name":"page","type":"int","source":"query"}]'` |
| `--response` | Yes | Response parameters JSON | `'[{"name":"users","type":"array"}]'` |
| `--description` | No | Endpoint description | `"List mock users"` |
| `--status` | No | HTTP status code | `200`, `201`, `204` |

### Request Parameter Format

```json
{
  "name": "parameterName",
  "type": "string|int|bool|array|object",
  "required": true|false,
  "source": "query|body|path",
  "example": "example value"
}
```

### Response Parameter Format

```json
{
  "name": "parameterName",
  "type": "string|int|bool|array|object",
  "description": "What this field represents",
  "example": "example value"
}
```

## Examples

### Example 1: GET endpoint with query parameters

```bash
/create-staging-endpoint /users GET \
  --request '[
    {"name": "page", "type": "int", "required": false, "source": "query", "example": 1},
    {"name": "limit", "type": "int", "required": false, "source": "query", "example": 10},
    {"name": "status", "type": "string", "required": false, "source": "query", "example": "active"}
  ]' \
  --response '[
    {"name": "users", "type": "array", "description": "List of user objects"},
    {"name": "total", "type": "int", "description": "Total number of users"},
    {"name": "page", "type": "int", "description": "Current page number"},
    {"name": "limit", "type": "int", "description": "Items per page"}
  ]' \
  --description "List mock users for QA testing"
```

### Example 2: POST endpoint with body parameters

```bash
/create-staging-endpoint /orders POST \
  --request '[
    {"name": "customerId", "type": "string", "required": true, "source": "body"},
    {"name": "items", "type": "array", "required": true, "source": "body"},
    {"name": "shippingAddress", "type": "object", "required": true, "source": "body"}
  ]' \
  --response '[
    {"name": "orderId", "type": "string", "description": "Generated order ID"},
    {"name": "status", "type": "string", "description": "Order status"},
    {"name": "createdAt", "type": "string", "description": "Creation timestamp"}
  ]' \
  --status 201
```

### Example 3: GET endpoint with path parameter

```bash
/create-staging-endpoint /orders/{id} GET \
  --request '[
    {"name": "id", "type": "string", "required": true, "source": "path"}
  ]' \
  --response '[
    {"name": "id", "type": "string", "description": "Order ID"},
    {"name": "customerId", "type": "string", "description": "Customer ID"},
    {"name": "items", "type": "array", "description": "Order items"},
    {"name": "total", "type": "float", "description": "Order total"},
    {"name": "status", "type": "string", "description": "Order status"}
  ]'
```

### Example 4: DELETE endpoint

```bash
/create-staging-endpoint /orders/{id} DELETE \
  --request '[
    {"name": "id", "type": "string", "required": true, "source": "path"}
  ]' \
  --response '[
    {"name": "message", "type": "string", "description": "Deletion confirmation"}
  ]' \
  --status 200
```

## Generated Code

The command generates code in `src/Api/Infrastructure/Controller/StageController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stage', name: 'stage_')]
class StageController extends AbstractController
{
    /**
     * List mock users for QA testing
     * 
     * Request: GET /api/stage/users?page=1&limit=10&status=active
     * Response: {"status":"success","data":{...},"timestamp":"..."}
     */
    #[Route('/users', name: 'users_list', methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $status = $request->query->get('status', 'active');

        // Generate mock data
        $users = [];
        for ($i = 1; $i <= $limit; $i++) {
            $userId = ($page - 1) * $limit + $i;
            $users[] = [
                'id' => 'user-' . $userId,
                'name' => 'Test User ' . $userId,
                'email' => 'user' . $userId . '@test.staging.com',
                'status' => $status,
                'createdAt' => (new \DateTimeImmutable())->format('c')
            ];
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'users' => $users,
                'total' => 100,
                'page' => $page,
                'limit' => $limit
            ],
            'timestamp' => (new \DateTimeImmutable())->format('c')
        ]);
    }
}
```

## Workflow

1. **Parse Input**: Extract endpoint name, method, and parameters
2. **Validate Parameters**: Ensure all required parameters are provided
3. **Generate Method Name**: Convert endpoint path to camelCase method name
4. **Generate Route Attribute**: Create unique route name with `stage_` prefix
5. **Generate Parameter Extraction**: Create code to extract request parameters
6. **Generate Mock Data**: Create realistic mock data based on response parameters
7. **Generate Response**: Create JSON response with status, data, and timestamp
8. **Add to Controller**: Insert method into StageController
9. **Verify**: Check that the endpoint is accessible via curl

## Post-Generation Steps

After generating the endpoint:

1. **Sync with Docker** (if applicable):
   ```bash
   docker cp src/Api/Infrastructure/Controller/StageController.php blog-php:/var/www/html/src/Api/Infrastructure/Controller/
   docker exec blog-php php bin/console cache:clear
   ```

2. **Test the endpoint**:
   ```bash
   curl http://localhost:8081/api/stage/users
   curl -X POST http://localhost:8081/api/stage/orders -H "Content-Type: application/json" -d '{"customerId":"123"}'
   ```

3. **Document**: Update API documentation if needed

## Rules

- ✅ All staging endpoints MUST be in StageController
- ✅ All endpoints MUST use /api/stage prefix
- ✅ All endpoints MUST return JSON with status, data, timestamp
- ✅ All endpoints MUST NOT require authentication
- ✅ All endpoints MUST NOT access database
- ✅ All endpoints MUST validate required parameters
- ❌ NEVER include business logic
- ❌ NEVER access external services
- ❌ NEVER store data persistently

## Related Commands

- `/list-staging-endpoints` - List all staging endpoints
- `/update-staging-endpoint` - Modify an existing endpoint
- `/delete-staging-endpoint` - Remove a staging endpoint
- `/export-staging-endpoints` - Export endpoint specifications as OpenAPI

## Notes

- Staging endpoints are designed for QA automation testing
- Mock data is generated dynamically but consistently
- Endpoints do not require JWT authentication
- Response format follows the project's API standards
