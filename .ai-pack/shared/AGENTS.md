# AI Agents Documentation

## Overview

This document describes the AI agents available in this project, their roles, capabilities, and how to effectively use them. Each agent is specialized for specific tasks and follows the project's coding standards and best practices.

---

## Quick Reference

| Agent | File | Purpose |
|-------|------|---------|
| QA Staging Endpoint | `qa-staging-endpoint-agent.json` | Create QA automation endpoints |
| Backend Feature | `backend-feature-agent.json` | Implement backend features with DDD |
| Test Writer | `test-writer-agent.json` | Generate Pest PHP tests |
| Security Auditor | `security-auditor-agent.json` | Security code review |
| Tech Document Writer | `tech-document-writer-agent.json` | API documentation |
| COR Refactor | `cor-refactor-agent.json` | Chain of Responsibility refactoring |

---

## Agent Architecture

### Agent Types

```
┌─────────────────────────────────────────────────────────┐
│                    Architect Agent                       │
│              (High-level design decisions)               │
└─────────────────────────────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   Frontend   │    │   Backend    │    │   DevOps     │
│  Specialist  │    │  Specialist  │    │  Engineer    │
└──────────────┘    └──────────────┘    └──────────────┘
        │                   │                   │
        └───────────────────┼───────────────────┘
                            ▼
                    ┌──────────────┐
                    │ Code Reviewer│
                    └──────────────┘
                            │
                            ▼
                    ┌──────────────┐
                    │  QA Tester   │
                    └──────────────┘
```

---

## Available Agents

### 0. QA Staging Endpoint Agent

**Role**: QA automation staging endpoint specialist

**Configuration**: `.ai-pack/shared/agents/qa-staging-endpoint-agent.json`

**Capabilities**:

- Create staging/mock endpoints for QA automation
- Map request parameters to response parameters
- Generate consistent JSON responses
- Handle HTTP methods (GET, POST, PUT, DELETE)
- Validate required parameters

**When to Use**:

- QA team needs test endpoints
- Frontend integration testing
- API contract verification
- Mock data generation
- Automated test scenarios

**Example Usage**:

```bash
# Create a staging endpoint
/create-staging-endpoint /orders GET --request '[{"name":"page","type":"int"}]' --response '[{"name":"orders","type":"array"}]'
```

**Target File**: `src/Api/Infrastructure/Controller/StageController.php`

**Rules**:

- Always add to StageController only
- Never include business logic
- Never access database
- Never require authentication
- Always return JSON with status, data, timestamp

---

### 1. Architect Agent

**Role**: System design and architectural decisions

**Configuration**: `.ai-pack/shared/agents/architect.json`

**Capabilities**:

- Design system architecture
- Make technology stack decisions
- Define project structure
- Create technical specifications
- Review architectural patterns
- Ensure scalability and maintainability

**When to Use**:

- Starting a new project
- Major feature additions
- System refactoring
- Performance optimization planning
- Technology migration decisions

**Example Usage**:

```bash
# Using the architect agent
ai-agent architect "Design a microservices architecture for user authentication"
```

**Best Practices**:

- Provide clear requirements and constraints
- Include performance and scale requirements
- Mention existing systems that need integration
- Specify non-functional requirements (security, compliance)

---

### 2. Frontend Specialist

**Role**: React/UI development expert

**Configuration**: `.ai-pack/shared/agents/frontend-specialist.json`

**Capabilities**:

- Build React components
- Implement responsive designs
- Create custom hooks
- Optimize frontend performance
- Implement state management
- Handle routing and navigation
- Accessibility (a11y) compliance

**When to Use**:

- Creating new UI components
- Implementing designs
- Frontend performance issues
- State management problems
- UI/UX improvements

**Example Usage**:

```bash
# Create a new component
ai-agent frontend "Create a reusable DataTable component with sorting and filtering"
```

**Specializations**:

- React 18+ features (Suspense, Concurrent Mode)
- TypeScript integration
- CSS-in-JS / Styled Components
- Performance optimization (React.memo, useMemo, useCallback)
- Form handling and validation

---

### 3. Backend Specialist

**Role**: Server-side development expert

**Configuration**: `.ai-pack/shared/agents/backend-specialist.json`

**Capabilities**:

- Design and implement APIs
- Database schema design
- Business logic implementation
- Authentication & authorization
- API documentation
- Performance optimization
- Security best practices

**When to Use**:

- Creating new API endpoints
- Database migrations
- Business logic implementation
- API performance issues
- Security vulnerabilities

**Example Usage**:

```bash
# Create a new API endpoint
ai-agent backend "Implement a REST API for user profile management with CRUD operations"
```

**Specializations**:

- RESTful API design
- GraphQL implementation
- Database optimization
- Caching strategies
- Message queues and async processing
- Microservices patterns

---

### 4. DevOps Engineer

**Role**: Infrastructure and deployment automation

**Configuration**: `.ai-pack/shared/agents/devops-engineer.json`

**Capabilities**:

- CI/CD pipeline setup
- Docker containerization
- Kubernetes orchestration
- Infrastructure as Code (IaC)
- Monitoring and logging
- Performance monitoring
- Security scanning

**When to Use**:

- Setting up deployment pipelines
- Infrastructure provisioning
- Monitoring setup
- Performance issues
- Security audits

**Example Usage**:

```bash
# Setup CI/CD pipeline
ai-agent devops "Create a GitHub Actions workflow for automated testing and deployment"
```

**Specializations**:

- Docker and Docker Compose
- Kubernetes manifests and Helm charts
- Terraform/CloudFormation
- GitHub Actions / GitLab CI
- AWS/GCP/Azure services
- Monitoring tools (Prometheus, Grafana)

---

### 5. Code Reviewer

**Role**: Code quality and best practices enforcement

**Configuration**: `.ai-pack/shared/agents/code-reviewer.json`

**Capabilities**:

- Review code for quality issues
- Enforce coding standards
- Identify security vulnerabilities
- Suggest performance improvements
- Check test coverage
- Verify documentation

**When to Use**:

- Before merging pull requests
- After implementing new features
- During refactoring
- Regular code audits

**Example Usage**:

```bash
# Review code changes
ai-agent review "Review the changes in src/services/user-service.ts"
```

**Review Checklist**:

- [ ] Code follows project standards
- [ ] Proper error handling
- [ ] Security best practices
- [ ] Performance considerations
- [ ] Test coverage adequate
- [ ] Documentation complete
- [ ] No code smells or anti-patterns

---

### 6. QA Tester

**Role**: Testing and quality assurance

**Configuration**: `.ai-pack/shared/agents/qa-tester.json`

**Capabilities**:

- Write unit tests
- Create integration tests
- Develop E2E test scenarios
- Test coverage analysis
- Bug reproduction
- Test automation
- Performance testing

**When to Use**:

- Writing tests for new features
- Improving test coverage
- Debugging test failures
- Creating test scenarios
- Performance testing

**Example Usage**:

```bash
# Generate tests
ai-agent qa "Generate comprehensive tests for the UserService class"
```

**Testing Strategies**:

- **Unit Tests**: Test individual functions/methods
- **Integration Tests**: Test component interactions
- **E2E Tests**: Test complete user flows
- **Performance Tests**: Load and stress testing
- **Security Tests**: Vulnerability scanning

---

## Agent Communication Protocol

### Request Format

```json
{
  "agent": "frontend-specialist",
  "task": "Create a login form component",
  "context": {
    "files": ["src/components/auth/"],
    "requirements": [
      "Email and password fields",
      "Form validation",
      "Error handling",
      "Accessibility support"
    ],
    "constraints": ["Use existing design system", "Follow project patterns"]
  },
  "output": {
    "format": "code",
    "includeTests": true,
    "includeDocumentation": true
  }
}
```

### Response Format

```json
{
  "status": "success",
  "agent": "frontend-specialist",
  "output": {
    "files": [
      {
        "path": "src/components/auth/LoginForm.tsx",
        "content": "...",
        "type": "implementation"
      },
      {
        "path": "src/components/auth/LoginForm.test.tsx",
        "content": "...",
        "type": "test"
      }
    ],
    "documentation": "...",
    "notes": [
      "Used Formik for form handling",
      "Implemented WCAG 2.1 AA accessibility"
    ]
  }
}
```

---

## Agent Collaboration

### Multi-Agent Workflows

#### Feature Development Flow

```
1. Architect → Design the feature architecture
2. Frontend Specialist → Implement UI components
3. Backend Specialist → Implement API endpoints
4. QA Tester → Write comprehensive tests
5. Code Reviewer → Review all changes
6. DevOps Engineer → Setup deployment
```

#### Bug Fix Flow

```
1. QA Tester → Reproduce and document the bug
2. Code Reviewer → Identify root cause
3. Frontend/Backend Specialist → Implement fix
4. QA Tester → Verify fix and add regression tests
5. Code Reviewer → Review the fix
```

#### Refactoring Flow

```
1. Code Reviewer → Identify areas for improvement
2. Architect → Design refactoring approach
3. Frontend/Backend Specialist → Implement changes
4. QA Tester → Ensure no regressions
5. Code Reviewer → Final review
```

---

## Configuration

### Agent Configuration Files

Each agent has a JSON configuration file in `.ai-pack/shared/agents/`:

```json
{
  "name": "frontend-specialist",
  "version": "1.0.0",
  "description": "React and frontend development expert",
  "capabilities": [
    "react-development",
    "typescript",
    "css-styling",
    "state-management",
    "performance-optimization"
  ],
  "context": {
    "instructions": "shared/instructions.md",
    "patterns": "shared/context/coding-standards.md",
    "templates": "shared/templates/"
  },
  "tools": ["code-generator", "test-generator", "documentation-generator"],
  "settings": {
    "maxFileSize": 500,
    "autoFormat": true,
    "includeLinting": true,
    "testCoverage": 80
  }
}
```

### Customizing Agents

To customize an agent:

1. Copy the agent configuration file
2. Modify capabilities and settings
3. Update context references
4. Save with a new name
5. Reference in your workflows

---

## Best Practices

### Effective Agent Usage

1. **Be Specific**: Provide clear, detailed requirements
2. **Provide Context**: Include relevant files and documentation
3. **Set Constraints**: Specify limitations and requirements
4. **Review Output**: Always review and test agent-generated code
5. **Iterate**: Refine requests based on initial output

### Common Pitfalls

❌ **Don't**:

- Give vague instructions
- Ignore agent suggestions
- Skip code review
- Assume agents understand implicit requirements
- Use agents for tasks outside their expertise

✅ **Do**:

- Provide clear acceptance criteria
- Include examples when possible
- Review and test all output
- Combine multiple agents for complex tasks
- Follow up with specific refinements

---

## Troubleshooting

### Agent Not Responding

- Check agent configuration file exists
- Verify agent name is correct
- Ensure all dependencies are installed
- Check logs for error messages

### Poor Quality Output

- Provide more context and examples
- Break down complex tasks into smaller ones
- Use the appropriate specialist agent
- Review and refine your request

### Inconsistent Results

- Ensure instructions.md is up to date
- Check agent configuration settings
- Verify project context is loaded
- Use code reviewer agent for validation

---

## Integration with IDEs

### VS Code

```json
// .vscode/settings.json
{
  "ai-pack.agents.path": ".ai-pack/shared/agents",
  "ai-pack.agents.enabled": [
    "frontend-specialist",
    "backend-specialist",
    "code-reviewer"
  ],
  "ai-pack.agents.autoSuggest": true
}
```

### JetBrains IDEs

```xml
<!-- .idea/ai-pack.xml -->
<component name="AiPackSettings">
  <option name="agentsPath" value=".ai-pack/shared/agents" />
  <option name="enabledAgents">
    <list>
      <option value="frontend-specialist" />
      <option value="backend-specialist" />
      <option value="code-reviewer" />
    </list>
  </option>
</component>
```

### Cursor / Windsurf

```json
// .cursor/settings.json or .windsurf/settings.json
{
  "aiPack": {
    "agentsDirectory": ".ai-pack/shared/agents",
    "defaultAgent": "code-reviewer",
    "enabledAgents": ["*"]
  }
}
```

---

## Metrics and Monitoring

### Agent Performance Metrics

- **Response Time**: Time to generate output
- **Code Quality**: Linting and test coverage scores
- **Accuracy**: Percentage of requirements met
- **Iteration Count**: Number of refinements needed

### Usage Analytics

- Most used agents
- Common task types
- Success rates
- Time saved vs manual coding

---

## Future Enhancements

### Planned Features

- [ ] Agent learning from feedback
- [ ] Custom agent creation wizard
- [ ] Agent performance analytics dashboard
- [ ] Multi-agent conversation support
- [ ] Integration with project management tools

---

## Resources

### Documentation

- [Instructions for AI](.ai-pack/shared/instructions.md)
- [Project Overview](.ai-pack/shared/context/project-overview.md)
- [Coding Standards](.ai-pack/shared/context/coding-standards.md)

### Agent Files

- [Architect](.ai-pack/shared/agents/architect.json)
- [Frontend Specialist](.ai-pack/shared/agents/frontend-specialist.json)
- [Backend Specialist](.ai-pack/shared/agents/backend-specialist.json)
- [DevOps Engineer](.ai-pack/shared/agents/devops-engineer.json)
- [Code Reviewer](.ai-pack/shared/agents/code-reviewer.json)
- [QA Tester](.ai-pack/shared/agents/qa-tester.json)

---

## Support

For issues or questions:

1. Check this documentation
2. Review agent configuration files
3. Consult project documentation
4. Open an issue in the project repository

---

**Version**: 1.0.0  
**Last Updated**: 2026-01-10  
**Maintained By**: Development Team
