---
description: Validate and check API documentation completeness
---

# API Documentation Check

## Purpose

Validates API documentation completeness, accuracy, and consistency. Ensures all endpoints are properly documented with request/response schemas, examples, and error codes.

## Usage

```
/apidoc-check [path-to-api-docs]
```

## What It Checks

### 1. OpenAPI/Swagger Schema Validation

- ✅ Valid OpenAPI 3.0 or Swagger 2.0 format
- ✅ All required fields present (info, paths, components)
- ✅ Schema references are valid
- ✅ No circular references
- ✅ Data types are correct

### 2. Endpoint Documentation Completeness

For each endpoint, verify:

- ✅ **Description**: Clear explanation of what the endpoint does
- ✅ **Authentication**: Required auth method specified
- ✅ **Parameters**: All query, path, and header parameters documented
- ✅ **Request Body**: Schema defined with examples
- ✅ **Response Codes**: All possible status codes documented (200, 400, 401, 404, 500, etc.)
- ✅ **Response Schema**: Structure of successful responses
- ✅ **Error Responses**: Error format and examples
- ✅ **Examples**: Realistic request and response examples

### 3. Schema Accuracy

- ✅ Request schemas match actual API implementation
- ✅ Response schemas match actual API responses
- ✅ Data types are accurate (string, number, boolean, array, object)
- ✅ Required fields are marked correctly
- ✅ Enum values are up-to-date
- ✅ Format specifications are correct (email, date-time, uuid, etc.)

### 4. Consistency Checks

- ✅ Consistent naming conventions (camelCase, snake_case)
- ✅ Consistent error response format across all endpoints
- ✅ Consistent authentication requirements
- ✅ Consistent versioning (e.g., /api/v1/)
- ✅ Consistent HTTP methods usage (GET, POST, PUT, PATCH, DELETE)

### 5. Documentation Quality

- ✅ Clear and concise descriptions
- ✅ No placeholder text (e.g., "TODO", "TBD")
- ✅ Examples are realistic and helpful
- ✅ Error messages are descriptive
- ✅ Deprecation warnings where applicable

## Automated Checks

### Using Swagger CLI

```bash
# Validate OpenAPI schema
npx @apidevtools/swagger-cli validate ./docs/openapi.yaml

# Bundle and dereference
npx @apidevtools/swagger-cli bundle ./docs/openapi.yaml -o ./docs/openapi-bundled.yaml
```

### Using Spectral (OpenAPI Linter)

```bash
# Install Spectral
npm install -g @stoplight/spectral-cli

# Run linting
spectral lint ./docs/openapi.yaml

# With custom ruleset
spectral lint ./docs/openapi.yaml --ruleset .spectral.yaml
```

### Custom Validation Script

```javascript
// check-api-docs.js
const SwaggerParser = require("@apidevtools/swagger-parser");
const fs = require("fs");

async function validateApiDocs(filePath) {
  try {
    // Parse and validate
    const api = await SwaggerParser.validate(filePath);
    console.log("✅ API schema is valid");

    // Check completeness
    const issues = [];

    for (const [path, methods] of Object.entries(api.paths)) {
      for (const [method, operation] of Object.entries(methods)) {
        if (typeof operation !== "object") continue;

        // Check description
        if (!operation.description || operation.description.length < 10) {
          issues.push(
            `${method.toUpperCase()} ${path}: Missing or too short description`
          );
        }

        // Check responses
        if (
          !operation.responses ||
          Object.keys(operation.responses).length === 0
        ) {
          issues.push(
            `${method.toUpperCase()} ${path}: No responses documented`
          );
        }

        // Check for 400, 401, 500 responses
        const responses = operation.responses || {};
        if (!responses["400"]) {
          issues.push(
            `${method.toUpperCase()} ${path}: Missing 400 Bad Request response`
          );
        }
        if (!responses["500"]) {
          issues.push(
            `${method.toUpperCase()} ${path}: Missing 500 Internal Server Error response`
          );
        }

        // Check for examples
        if (!operation.requestBody?.content?.["application/json"]?.example) {
          if (method === "post" || method === "put" || method === "patch") {
            issues.push(
              `${method.toUpperCase()} ${path}: Missing request example`
            );
          }
        }
      }
    }

    if (issues.length > 0) {
      console.log("\n⚠️  Documentation Issues Found:");
      issues.forEach((issue) => console.log(`  - ${issue}`));
      process.exit(1);
    } else {
      console.log("✅ All documentation checks passed");
    }
  } catch (error) {
    console.error("❌ Validation failed:", error.message);
    process.exit(1);
  }
}

// Run validation
const apiDocPath = process.argv[2] || "./docs/openapi.yaml";
validateApiDocs(apiDocPath);
```

## Integration with CI/CD

Add to your CI/CD pipeline:

```yaml
# .github/workflows/api-docs-check.yml
name: API Documentation Check

on:
  pull_request:
    paths:
      - "docs/openapi.yaml"
      - "src/routes/**"

jobs:
  validate-docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: "18"

      - name: Install dependencies
        run: npm install -g @apidevtools/swagger-cli @stoplight/spectral-cli

      - name: Validate OpenAPI schema
        run: swagger-cli validate ./docs/openapi.yaml

      - name: Lint API documentation
        run: spectral lint ./docs/openapi.yaml

      - name: Check documentation completeness
        run: node ./scripts/check-api-docs.js
```

## Manual Review Checklist

When reviewing API documentation manually:

- [ ] All endpoints are documented
- [ ] Descriptions are clear and helpful
- [ ] All parameters are documented with types and descriptions
- [ ] Request examples are realistic and complete
- [ ] Response examples match actual API responses
- [ ] All error codes are documented with explanations
- [ ] Authentication requirements are clear
- [ ] Rate limiting information is included
- [ ] Deprecation warnings are present for deprecated endpoints
- [ ] Version information is accurate
- [ ] Base URL and server information is correct
- [ ] Contact and license information is present

## Common Issues and Fixes

### Issue: Missing Response Examples

**Fix**: Add realistic examples to each response:

```yaml
responses:
  "200":
    description: Successful response
    content:
      application/json:
        schema:
          $ref: "#/components/schemas/User"
        example:
          id: "123e4567-e89b-12d3-a456-426614174000"
          name: "John Doe"
          email: "john@example.com"
          createdAt: "2024-01-15T10:30:00Z"
```

### Issue: Inconsistent Error Format

**Fix**: Define a standard error schema:

```yaml
components:
  schemas:
    Error:
      type: object
      required:
        - error
        - message
      properties:
        error:
          type: string
          example: "ValidationError"
        message:
          type: string
          example: "Invalid email format"
        details:
          type: array
          items:
            type: object
```

### Issue: Missing Authentication Documentation

**Fix**: Add security schemes:

```yaml
components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

paths:
  /api/v1/users:
    get:
      security:
        - BearerAuth: []
```

## Output Example

```
🔍 Checking API Documentation...

✅ OpenAPI schema is valid
✅ All endpoints have descriptions
✅ Request/response schemas are defined
✅ Examples are present
⚠️  3 warnings found:

  - GET /api/v1/users: Missing 401 Unauthorized response
  - POST /api/v1/users: Request example could be more detailed
  - DELETE /api/v1/users/:id: Missing deprecation notice

📊 Summary:
  - Total endpoints: 15
  - Fully documented: 12
  - Warnings: 3
  - Errors: 0

✅ API documentation check passed with warnings
```

## Related Commands

- `/create-endpoint` - Create new endpoint with documentation
- `/review-code` - Review code quality including documentation
- `/generate-tests` - Generate tests based on API documentation

## Tools and Resources

- [Swagger Editor](https://editor.swagger.io/) - Online OpenAPI editor
- [Redoc](https://github.com/Redocly/redoc) - Beautiful API documentation
- [Spectral](https://stoplight.io/open-source/spectral) - OpenAPI linter
- [Swagger CLI](https://github.com/APIDevTools/swagger-cli) - Validation tool
- [OpenAPI Specification](https://swagger.io/specification/) - Official spec
