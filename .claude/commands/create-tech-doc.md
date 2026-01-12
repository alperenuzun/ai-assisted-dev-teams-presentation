# Create Technical Documentation

Create comprehensive technical documentation for the specified API endpoint.

## Instructions

1. **Analyze the endpoint**: Read the controller, command/query handler, and related domain entities for the endpoint: `$ARGUMENTS`

2. **Document the following**:
   - Endpoint URL and HTTP method
   - Request parameters (path, query, body)
   - Request body schema with examples
   - Response schema with examples
   - Authentication requirements
   - Error responses and status codes
   - Rate limiting (if applicable)

3. **Create documentation file**: Save the documentation to `docs/api/{endpoint-name}.md`

4. **Follow this template structure**:
```markdown
# {Endpoint Name}

## Overview
Brief description of what this endpoint does.

## Endpoint
`{METHOD} /api/{path}`

## Authentication
- Required: Yes/No
- Type: Bearer Token (JWT)

## Request

### Headers
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes/No |
| Content-Type | application/json | Yes/No |

### Path Parameters
| Parameter | Type | Description |
|-----------|------|-------------|

### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|

### Request Body
```json
{
  "example": "value"
}
```

## Response

### Success Response (200 OK)
```json
{
  "example": "response"
}
```

### Error Responses
| Status | Description |
|--------|-------------|
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |

## Example

### cURL
```bash
curl -X {METHOD} http://localhost:8081/api/{path} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```
```

5. **Important Notes**:
   - This project runs on Docker (port 8081)
   - Use `docker exec blog-php` for any PHP commands
   - Read the actual implementation to document accurately
   - Include all value object validations as constraints
