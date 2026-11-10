# AI Assistant Instructions

> **Changelog (architecture review)**: this file is the generic, multi-IDE "AI Pack"
> template and had drifted out of sync with the real project — it described a
> React/TypeScript/Node.js stack while the actual codebase is PHP 8.3 + Symfony 7.3 DDD
> (see `CLAUDE.md`, the actual single source of truth for Claude Code). An AI agent reading
> this file instead of `CLAUDE.md` would confidently generate `.tsx` components and Jest
> tests for a project that has neither. The stack/structure/naming sections below are now
> corrected to match reality; please keep this file and `CLAUDE.md` in sync going forward,
> or better, generate this one from `CLAUDE.md` instead of hand-maintaining both.

## Purpose

This document provides comprehensive instructions for AI assistants working on this project. These guidelines ensure consistency, quality, and alignment with project standards.

---

## Project Context

### Technology Stack

- **Backend**: PHP 8.3 (strict types) with Symfony 7.3
- **Database**: PostgreSQL 16, Doctrine ORM (XML mappings, not annotations)
- **Testing**: Pest PHP
- **Package Manager**: Composer
- **Runtime**: Docker Compose (nginx + php-fpm + postgres), all PHP commands run via
  `docker exec blog-php ...`

### Architecture Principles

1. **Clean Architecture**: Separation of concerns with clear boundaries
2. **Domain-Driven Design**: Business logic encapsulated in domain models
3. **SOLID Principles**: Maintainable and extensible code
4. **Test-Driven Development**: Write tests before implementation

---

## Code Standards

### General Guidelines

- Follow the coding standards defined in `.ai-pack/shared/context/coding-standards.md`
- Use PHP 8.3 strict types (`declare(strict_types=1)`) for type safety
- Write self-documenting code with clear naming
- Keep functions small and focused (max 20-30 lines)
- Avoid deep nesting (max 3 levels)

### Naming Conventions

- **Methods/Variables**: camelCase (`getUserData`, `isActive`)
- **Classes/Interfaces**: PascalCase, interfaces suffixed `Interface` (`PostRepositoryInterface`)
- **Constants**: UPPER_SNAKE_CASE
- **Files**: match the class name exactly (PSR-4), one class per file (`PostTitle.php`)
- **Value Objects**: `final readonly class`, named after the domain concept, not the type
  (`PostTitle`, not `StringValue`)

### Code Organization

```
src/{Context}/              # Api | Admin | Web | SharedKernel (bounded contexts)
├── Domain/
│   ├── {Aggregate}/
│   │   ├── Entity/
│   │   ├── ValueObject/
│   │   └── Repository/     # interfaces only, no framework/Doctrine code here
├── Application/
│   └── {Aggregate}/
│       ├── Command/        # CQRS writes + handlers
│       └── Query/          # CQRS reads + handlers
└── Infrastructure/
    ├── Controller/
    └── Persistence/Doctrine/Repository/   # repository implementations

tests/Unit/{Context}/...    # Pest tests mirroring src/ namespaces
```

See `CLAUDE.md` for the authoritative, up-to-date version of this structure.

---

## Development Workflow

### Feature Development

1. **Understand Requirements**: Review the feature specification
2. **Plan Architecture**: Design the solution following project patterns
3. **Write Tests**: Create test cases first (TDD approach)
4. **Implement**: Write the minimal code to pass tests
5. **Refactor**: Improve code quality while keeping tests green
6. **Document**: Add PHPDoc comments where types aren't self-evident and update documentation
7. **Review**: Self-review using the code review checklist

### Code Review Checklist

- [ ] Code follows project standards and patterns
- [ ] All tests pass and have good coverage (>80%)
- [ ] No `dump()`/`var_dump()`/debug code left
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
5. **Documentation**: Add PHPDoc comments for public APIs where return/param types need clarification

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

- Use PHPDoc for public APIs where native PHP types aren't sufficient
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

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
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
