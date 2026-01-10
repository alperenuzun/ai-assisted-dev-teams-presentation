# Code Review Command

Performs comprehensive AI-powered code review on current changes or specific files.

## Usage

```bash
/review-code [files...] [options]
```

## Arguments

- `files`: Specific files to review (optional, defaults to staged files)

## Options

- `--full`: Review all changes, not just staged
- `--severity <level>`: Minimum severity to report (critical, major, minor, suggestion)
- `--focus <area>`: Focus on specific area (security, performance, quality, tests)
- `--format <format>`: Output format (markdown, json, html)

## Examples

```bash
# Review staged files
/review-code

# Review specific files
/review-code src/services/user.service.ts src/routes/user.routes.ts

# Full repository review
/review-code --full

# Security-focused review
/review-code --focus security

# Review with specific severity threshold
/review-code --severity major
```

## Review Areas

### Security
- SQL injection vulnerabilities
- XSS vulnerabilities
- Authentication/authorization issues
- Secret exposure
- Input validation

### Performance
- Inefficient algorithms
- Database query optimization
- Memory leaks
- Unnecessary re-renders (React)
- Bundle size concerns

### Code Quality
- Code complexity
- Code duplication
- Naming conventions
- SOLID principles
- Design patterns

### Testing
- Test coverage
- Test quality
- Missing edge cases
- Brittle tests

## Output

```markdown
# Code Review Report

## Summary
- Files reviewed: 5
- Issues found: 12
- Critical: 0
- Major: 2
- Minor: 7
- Suggestions: 3

## Critical Issues

None found

## Major Issues

### src/services/user.service.ts:45
**Issue**: SQL Injection vulnerability
**Severity**: Major
**Description**: Using string concatenation for SQL query
**Recommendation**: Use parameterized queries

### src/components/UserForm.tsx:123
**Issue**: Missing input validation
**Severity**: Major
**Description**: Email input not validated before submission
**Recommendation**: Add validation using Joi/Zod

## Recommendations
- Consider extracting duplicate logic in user service
- Add error boundaries for React components
- Improve test coverage (currently 65%, target 80%)
```

## AI Agent

Uses: `code-reviewer` agent
