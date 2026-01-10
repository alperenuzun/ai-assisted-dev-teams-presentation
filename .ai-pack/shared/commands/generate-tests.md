# Generate Tests Command

Automatically generates comprehensive test suites for specified files or modules.

## Usage

```bash
/generate-tests <files...> [options]
```

## Arguments

- `files`: Files to generate tests for (required)

## Options

- `--type <type>`: Test type (unit, integration, e2e, all)
- `--framework <framework>`: Testing framework (jest, vitest, playwright, cypress)
- `--coverage-target <percentage>`: Target coverage percentage (default: 80)
- `--include-edge-cases`: Generate tests for edge cases
- `--mocks`: Generate mock data and fixtures

## Examples

```bash
# Generate unit tests for a service
/generate-tests src/services/user.service.ts

# Generate all test types for a component
/generate-tests src/components/UserProfile.tsx --type all

# Generate E2E tests with Playwright
/generate-tests src/pages/Login.tsx --type e2e --framework playwright

# Generate tests with high coverage target
/generate-tests src/utils/validation.ts --coverage-target 95 --include-edge-cases
```

## Test Types

### Unit Tests
- Individual function/method tests
- Component tests (React)
- Isolated from dependencies
- Fast execution

### Integration Tests
- Module interaction tests
- API endpoint tests
- Database integration tests
- Service layer tests

### E2E Tests
- Complete user workflow tests
- Browser automation
- Full stack testing
- Slower execution

## Generated Test Structure

```typescript
// user.service.test.ts

describe('UserService', () => {
  describe('Initialization', () => {
    // Constructor and setup tests
  });

  describe('Core Functionality', () => {
    // Main business logic tests
  });

  describe('Error Handling', () => {
    // Error scenarios
  });

  describe('Edge Cases', () => {
    // Edge case coverage
  });

  describe('Integration Tests', () => {
    // Integration with dependencies
  });
});
```

## Features

- **Comprehensive Coverage**: Tests for happy path, errors, and edge cases
- **AAA Pattern**: Arrange-Act-Assert structure
- **Mocking**: Automatic mock generation for dependencies
- **Assertions**: Meaningful, specific assertions
- **Documentation**: Clear test descriptions
- **Performance**: Performance validation tests
- **Accessibility**: Accessibility tests for components

## Output

```
✓ Generated tests for 3 files

src/services/user.service.test.ts
  - 25 test cases generated
  - Estimated coverage: 87%

src/components/UserProfile.test.tsx
  - 18 test cases generated
  - Includes accessibility tests
  - Estimated coverage: 92%

src/utils/validation.test.ts
  - 32 test cases generated
  - Includes edge cases
  - Estimated coverage: 95%

Total: 75 test cases
Run tests: npm test
```

## AI Agent

Uses: `qa-tester` agent
