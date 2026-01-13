# GitHub Copilot Agents

This directory contains AI agent definitions compatible with GitHub Copilot.

## Available Agents

| Agent | File | Purpose |
|-------|------|---------|
| QA Staging Endpoint | `agents/qa-staging-endpoint-agent.md` | Create QA automation endpoints |
| Backend Feature | `agents/backend-feature-agent.md` | Implement backend features with DDD |
| Test Writer | `agents/test-writer-agent.md` | Generate Pest PHP tests |
| Security Auditor | `agents/security-auditor-agent.md` | Security code review |
| Tech Document Writer | `agents/tech-document-writer-agent.md` | API documentation |

## Usage

### In GitHub Copilot Chat

Reference agent files when asking for help:

```
@workspace Looking at the qa-staging-endpoint-agent, create a new endpoint for products
```

```
@workspace Using backend-feature-agent patterns, implement a Comment entity
```

### In Copilot Edits

When requesting code changes, reference the agent guidelines:

```
Following the test-writer-agent pattern, create tests for the Post entity
```

### With @workspace

For codebase-wide operations:

```
@workspace Using security-auditor-agent, review the authentication implementation
```

## Agent Details

### QA Staging Endpoint Agent

Creates mock endpoints in `StageController` for QA automation testing.

**Use for:**
- Creating test endpoints
- Mock data endpoints
- QA automation support

**Target file:** `src/Api/Infrastructure/Controller/StageController.php`

### Backend Feature Agent

Implements features following DDD architecture patterns.

**Use for:**
- New entities and value objects
- CQRS commands and queries
- Repository implementations
- Controller endpoints

### Test Writer Agent

Generates comprehensive Pest PHP tests.

**Use for:**
- Unit tests for entities
- Value object tests
- Integration tests
- Feature tests

### Security Auditor Agent

Reviews code for security vulnerabilities.

**Use for:**
- Security reviews
- Vulnerability assessment
- Best practice enforcement

### Tech Document Writer Agent

Creates and updates technical documentation.

**Use for:**
- API documentation
- Architecture docs
- Developer guides

## Adding New Agents

1. Create JSON definition in `.ai-pack/shared/agents/`
2. Run the setup script:
   ```bash
   .ai-pack/shared/setup.sh github-copilot
   ```
3. Agent will be generated in `.github/agents/`

## Prompts Directory

Pre-defined prompts are available in `.github/prompts/`:

- `create-endpoint.md` - Create REST API endpoint
- `create-staging-endpoint.md` - Create QA staging endpoint

## Integration with .ai-pack

All agent definitions originate from `.ai-pack/shared/agents/`. The setup script converts JSON definitions to GitHub Copilot compatible markdown format.

## Related Files

- `.github/copilot-instructions.md` - Global Copilot instructions
- `.ai-pack/shared/agents/` - Source agent definitions (JSON)
- `.ai-pack/shared/commands/` - Available commands

---

**Version**: 1.0.0  
**Last Updated**: 2026-01-13  
**Source**: AI Pack Setup Script
