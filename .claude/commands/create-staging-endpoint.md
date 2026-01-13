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

## Example

### Creating a GET endpoint

**Input:**
```
/create-staging-endpoint /products GET 
  --request '[{"name":"category","type":"string","source":"query"},{"name":"limit","type":"int","source":"query"}]'
  --response '[{"name":"products","type":"array"},{"name":"total","type":"int"}]'
```

**Generated Code:**
```php
#[Route('/products', name: 'stage_products_list', methods: ['GET'])]
public function listProducts(Request $request): JsonResponse
{
    $category = $request->query->get('category', 'all');
    $limit = min(100, max(1, $request->query->getInt('limit', 10)));

    $products = [];
    for ($i = 1; $i <= $limit; $i++) {
        $products[] = [
            'id' => sprintf('product-%08d', $i),
            'name' => 'Test Product ' . $i,
            'category' => $category,
            'price' => round(rand(100, 10000) / 100, 2),
        ];
    }

    return $this->json([
        'status' => 'success',
        'data' => [
            'products' => $products,
            'total' => 100,
        ],
        'timestamp' => (new \DateTimeImmutable())->format('c'),
    ]);
}
```

## Post-Generation Steps

After generating the endpoint:

1. **Sync with Docker**:
   ```bash
   docker cp src/Api/Infrastructure/Controller/StageController.php blog-php:/var/www/html/src/Api/Infrastructure/Controller/
   docker exec blog-php php bin/console cache:clear
   ```

2. **Test the endpoint**:
   ```bash
   curl http://localhost:8081/api/stage/products?category=electronics&limit=5
   ```

## Rules

- ✅ All staging endpoints MUST be in StageController
- ✅ All endpoints MUST use /api/stage prefix
- ✅ All endpoints MUST return JSON with status, data, timestamp
- ✅ All endpoints MUST NOT require authentication
- ❌ NEVER include business logic
- ❌ NEVER access database or external services
