---
name: Create Staging Endpoint
description: Create a QA automation endpoint in StageController
---

# Create Staging Endpoint Prompt

Create a new staging endpoint for QA automation testing in `StageController`.

## Input Parameters

- **endpoint_name**: {{endpoint_name}}
- **method**: {{method}}
- **request_params**: {{request_params}}
- **response_params**: {{response_params}}

## Requirements

1. Add to `src/Api/Infrastructure/Controller/StageController.php`
2. Use Route attribute with `stage_` prefix
3. Return JsonResponse with format:
   ```json
   {
     "status": "success",
     "data": { ... },
     "timestamp": "ISO 8601"
   }
   ```
4. NO authentication required
5. NO database access
6. NO business logic

## Template

```php
#[Route('/{{path}}', name: 'stage_{{name}}', methods: ['{{method}}'])]
public function {{methodName}}(Request $request): JsonResponse
{
    // Extract parameters from request
    // Generate mock data based on response_params
    // Return JSON response
}
```

## Post-Generation

After creating:
1. Sync with Docker: `docker cp src/Api/Infrastructure/Controller/StageController.php blog-php:/var/www/html/src/Api/Infrastructure/Controller/`
2. Clear cache: `docker exec blog-php php bin/console cache:clear`
3. Test: `curl http://localhost:8081/api/stage/{{path}}`
