# Write Documentation to Confluence

Create technical documentation for a feature/endpoint and publish it to Confluence.

## Instructions

Target Confluence page: `$ARGUMENTS`

### Step 1: Gather Information

1. **Identify the subject**:
   - Parse the argument to understand what needs to be documented
   - If it's an endpoint (e.g., `/api/posts`), read the controller and handlers
   - If it's a feature, gather all related components

2. **Read the implementation**:
   - Controller files in `src/*/Infrastructure/Controller/`
   - Command/Query handlers in `src/*/Application/`
   - Domain entities and value objects in `src/*/Domain/`
   - Doctrine mappings in `config/doctrine/`

### Step 2: Generate Documentation

Create comprehensive documentation with these sections:

```markdown
# {Feature/Endpoint Name}

## Overview
Brief description of the functionality.

## Architecture

### Components
- **Controller**: `{path}`
- **Command/Query**: `{path}`
- **Handler**: `{path}`
- **Entity**: `{path}`

### Data Flow
```
Request → Controller → Command/Query → Handler → Repository → Database
```

## API Reference

### Endpoint
`{METHOD} {PATH}`

### Authentication
{Required/Optional} - Bearer Token (JWT)

### Request
{Request schema with examples}

### Response
{Response schema with examples}

### Error Codes
| Code | Description |
|------|-------------|
| 400  | Bad Request |
| 401  | Unauthorized |
| 404  | Not Found |

## Domain Model

### Entity: {Name}
{Properties and their types}

### Value Objects
{List of value objects with validation rules}

## Usage Examples

### cURL
```bash
curl -X {METHOD} http://localhost:8081{PATH} \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{request body}'
```

## Related Documentation
- {Links to related pages}
```

### Step 3: Publish to Confluence

1. **Parse Confluence URL**:
   - Extract space key and parent page ID from the URL
   - Or use the page ID directly if provided

2. **Create/Update Page**:
   - Use Atlassian MCP: `mcp__atlassian__create_page` or `mcp__atlassian__update_page`
   - Set proper page title
   - Apply documentation template
   - Add labels: `api-docs`, `technical`, `{feature-name}`

3. **Verify Publication**:
   - Confirm page was created/updated successfully
   - Return the Confluence page URL

## Example Usage

```
/write-to-confluence https://company.atlassian.net/wiki/spaces/DOCS/pages/123456 /api/posts
```

Or with just endpoint:
```
/write-to-confluence /api/posts
```
(Will create in default documentation space)

## Output

After completion, provide:
1. Confluence page URL
2. Summary of documented content
3. Any warnings or missing information
