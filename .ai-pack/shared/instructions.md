# AI Assistant Instructions

## Purpose

This document provides comprehensive instructions for AI assistants working on this project. These guidelines ensure consistency, quality, and alignment with project standards.

---

## Project Context

### Technology Stack

- **Frontend**: React 18+ with TypeScript
- **Backend**: Node.js with Express/NestJS
- **Database**: PostgreSQL/MongoDB
- **Testing**: Jest, React Testing Library
- **Build Tools**: Vite/Webpack
- **Package Manager**: npm/yarn

### Architecture Principles

1. **Clean Architecture**: Separation of concerns with clear boundaries
2. **Domain-Driven Design**: Business logic encapsulated in domain models
3. **SOLID Principles**: Maintainable and extensible code
4. **Test-Driven Development**: Write tests before implementation

---

## Code Standards

### General Guidelines

- Follow the coding standards defined in `.ai-pack/shared/context/coding-standards.md`
- Use TypeScript for type safety
- Write self-documenting code with clear naming
- Keep functions small and focused (max 20-30 lines)
- Avoid deep nesting (max 3 levels)

### Naming Conventions

- **Variables/Functions**: camelCase (`getUserData`, `isActive`)
- **Classes/Interfaces**: PascalCase (`UserService`, `IUserRepository`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_RETRY_COUNT`, `API_BASE_URL`)
- **Files**: kebab-case (`user-service.ts`, `api-client.ts`)
- **Components**: PascalCase (`UserProfile.tsx`, `LoginForm.tsx`)

### Code Organization

```
src/
├── components/     # React components
├── services/       # Business logic services
├── repositories/   # Data access layer
├── models/         # Domain models and types
├── utils/          # Utility functions
├── hooks/          # Custom React hooks
├── constants/      # Application constants
└── tests/          # Test files
```

---

## Development Workflow

### Feature Development

1. **Understand Requirements**: Review the feature specification
2. **Plan Architecture**: Design the solution following project patterns
3. **Write Tests**: Create test cases first (TDD approach)
4. **Implement**: Write the minimal code to pass tests
5. **Refactor**: Improve code quality while keeping tests green
6. **Document**: Add JSDoc comments and update documentation
7. **Review**: Self-review using the code review checklist

### Code Review Checklist

- [ ] Code follows project standards and patterns
- [ ] All tests pass and have good coverage (>80%)
- [ ] No console.log or debug code left
- [ ] Error handling is comprehensive
- [ ] Security best practices followed
- [ ] Performance considerations addressed
- [ ] Documentation is complete and accurate
- [ ] No hardcoded values (use constants/config)

---

## AI-Specific Guidelines

### When Writing Code

1. **Context Awareness**: Always check existing patterns before creating new ones
2. **Consistency**: Match the style of surrounding code
3. **Completeness**: Provide full implementations, not placeholders
4. **Testing**: Include tests for all new functionality
5. **Documentation**: Add JSDoc comments for public APIs

### When Refactoring

1. **Preserve Behavior**: Ensure functionality remains unchanged
2. **Incremental Changes**: Make small, focused refactorings
3. **Test Coverage**: Verify tests pass after each change
4. **Update Documentation**: Reflect changes in comments and docs

### When Debugging

1. **Reproduce First**: Understand the issue before fixing
2. **Root Cause**: Find the underlying problem, not just symptoms
3. **Minimal Fix**: Change only what's necessary
4. **Add Tests**: Prevent regression with new test cases

### When Reviewing Code

1. **Be Constructive**: Suggest improvements, don't just criticize
2. **Explain Why**: Provide reasoning for suggestions
3. **Prioritize**: Focus on critical issues first
4. **Learn Patterns**: Recognize and apply project patterns

---

## Common Patterns

### Error Handling

```typescript
try {
  const result = await riskyOperation();
  return { success: true, data: result };
} catch (error) {
  logger.error("Operation failed", { error, context });
  throw new ApplicationError("User-friendly message", error);
}
```

### API Response Format

```typescript
interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: {
    message: string;
    code: string;
    details?: unknown;
  };
  metadata?: {
    timestamp: string;
    requestId: string;
  };
}
```

### Component Structure

```typescript
interface Props {
  // Props definition
}

export const ComponentName: React.FC<Props> = ({ prop1, prop2 }) => {
  // Hooks
  const [state, setState] = useState();

  // Effects
  useEffect(() => {
    // Effect logic
  }, [dependencies]);

  // Handlers
  const handleAction = useCallback(() => {
    // Handler logic
  }, [dependencies]);

  // Render
  return (
    // JSX
  );
};
```

---

## Testing Guidelines

### Test Structure

```typescript
describe("FeatureName", () => {
  // Setup
  beforeEach(() => {
    // Common setup
  });

  // Teardown
  afterEach(() => {
    // Cleanup
  });

  describe("when condition", () => {
    it("should expected behavior", () => {
      // Arrange
      const input = setupTestData();

      // Act
      const result = functionUnderTest(input);

      // Assert
      expect(result).toBe(expected);
    });
  });
});
```

### Test Coverage Goals

- **Unit Tests**: >80% coverage
- **Integration Tests**: Critical paths covered
- **E2E Tests**: Main user flows covered

---

## Security Considerations

### Input Validation

- Validate all user inputs
- Sanitize data before database operations
- Use parameterized queries to prevent SQL injection
- Implement rate limiting on APIs

### Authentication & Authorization

- Never store passwords in plain text
- Use secure session management
- Implement proper RBAC (Role-Based Access Control)
- Validate permissions on every request

### Data Protection

- Encrypt sensitive data at rest and in transit
- Follow GDPR/privacy regulations
- Implement proper logging (no sensitive data in logs)
- Use environment variables for secrets

---

## Performance Best Practices

### Frontend

- Use React.memo for expensive components
- Implement lazy loading for routes and components
- Optimize bundle size (code splitting)
- Minimize re-renders with proper dependency arrays

### Backend

- Implement caching strategies (Redis)
- Use database indexes appropriately
- Optimize database queries (avoid N+1)
- Implement pagination for large datasets

### General

- Profile before optimizing
- Focus on bottlenecks first
- Monitor performance metrics
- Use CDN for static assets

---

## Documentation Requirements

### Code Comments

- Use JSDoc for all public APIs
- Explain "why" not "what" in comments
- Keep comments up-to-date with code changes
- Document complex algorithms

### README Files

- Clear project description
- Setup instructions
- Usage examples
- Contribution guidelines

### API Documentation

- Use OpenAPI/Swagger for REST APIs
- Document all endpoints, parameters, and responses
- Include example requests and responses
- Keep documentation in sync with implementation

---

## Resources

### Project Documentation

- [Project Overview](.ai-pack/shared/context/project-overview.md)
- [Coding Standards](.ai-pack/shared/context/coding-standards.md)
- [API Patterns](.ai-pack/shared/context/api-patterns.md)

### External Resources

- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [React Documentation](https://react.dev/)
- [Node.js Best Practices](https://github.com/goldbergyoni/nodebestpractices)
- [Clean Code Principles](https://github.com/ryanmcdermott/clean-code-javascript)

---

## Getting Help

### When Stuck

1. Review existing similar implementations
2. Check project documentation
3. Search for patterns in the codebase
4. Consult external documentation
5. Ask for clarification from the team

### Escalation Path

1. Try to solve independently first
2. Review with AI code reviewer agent
3. Consult with specialist agents (frontend/backend)
4. Escalate to architect agent for design decisions

---

## Version History

- **v1.0.0** (2026-01-10): Initial version
- Document updates tracked in git history
