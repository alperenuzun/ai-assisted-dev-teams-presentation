# Test Endpoint Skill

Test an API endpoint with automatic authentication and detailed response analysis.

## Usage

```
/test-endpoint {METHOD} {path} [--data='{}'] [--auth] [--user=email]
```

## Options

- `--data`: JSON body for POST/PUT requests
- `--auth`: Use JWT authentication (auto-login)
- `--user`: Specify user email (default: admin@blog.com)

## Examples

```
/test-endpoint GET /api/posts --auth
/test-endpoint POST /api/posts --auth --data='{"title":"Test","content":"Content"}'
/test-endpoint GET /api/posts/uuid-here --auth
/test-endpoint GET /api/translations/en
```

## What This Skill Does

### Step 1: Get JWT Token (if --auth)
```bash
TOKEN=$(curl -s -X POST http://localhost:8081/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}' | jq -r '.token')
```

### Step 2: Execute Request
```bash
curl -s -X {METHOD} http://localhost:8081{path} \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{data}'
```

### Step 3: Analyze Response
- Parse JSON response
- Check status code
- Validate response structure
- Measure response time

## Output Format

```markdown
## Endpoint Test Results

### Request
- **Method**: POST
- **URL**: http://localhost:8081/api/posts
- **Auth**: Bearer Token (admin@blog.com)
- **Body**:
```json
{
  "title": "Test Post",
  "content": "This is test content"
}
```

### Response
- **Status**: 201 Created
- **Time**: 45ms
- **Headers**:
  - Content-Type: application/json
  - X-Debug-Token: abc123

### Response Body
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "title": "Test Post",
  "content": "This is test content",
  "status": "draft",
  "createdAt": "2024-01-15T10:30:00+00:00"
}
```

### Validation
- ✅ Status code is 2xx
- ✅ Response is valid JSON
- ✅ Required fields present (id, title, content)
- ✅ UUID format valid
- ✅ Timestamp format valid

### cURL Command (for manual testing)
```bash
curl -X POST http://localhost:8081/api/posts \
  -H "Authorization: Bearer eyJ..." \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Post","content":"This is test content"}'
```
```

## Error Response Handling

```markdown
## Endpoint Test Results

### Request
- **Method**: POST
- **URL**: http://localhost:8081/api/posts
- **Auth**: Bearer Token

### Response
- **Status**: 400 Bad Request
- **Time**: 12ms

### Response Body
```json
{
  "error": "Validation failed",
  "details": {
    "title": "Title must be at least 3 characters"
  }
}
```

### Analysis
- ❌ Request failed with 400
- ℹ️ Validation error on field: title
- 💡 Suggestion: Title "Te" is too short, minimum 3 characters required
```

## Batch Testing

Test multiple endpoints at once:

```
/test-endpoint GET /api/posts --auth
/test-endpoint GET /api/posts/{id} --auth
/test-endpoint POST /api/posts --auth --data='{"title":"Test","content":"Content"}'
```

Output:
```markdown
## Batch Test Results

| Endpoint | Method | Status | Time |
|----------|--------|--------|------|
| /api/posts | GET | ✅ 200 | 32ms |
| /api/posts/{id} | GET | ✅ 200 | 18ms |
| /api/posts | POST | ✅ 201 | 45ms |

**Summary**: 3/3 tests passed
```

## Demo Scenario

**Presenter says**: "Let's verify our new endpoint works correctly..."

```
/test-endpoint POST /api/comments --auth --data='{"content":"Great post!","postId":"..."}'
```

**What audience sees**:
1. Automatic authentication handling
2. Detailed request/response inspection
3. Validation of response structure
4. Copy-paste ready cURL command
5. Professional API testing workflow

**Chain with other skills**:
```
/generate-endpoint POST /api/comments Api --entity=Comment
/test-endpoint POST /api/comments --auth --data='{"content":"Test","postId":"..."}'
```
