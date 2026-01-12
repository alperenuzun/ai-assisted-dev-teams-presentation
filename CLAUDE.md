# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7.3 DDD (Domain-Driven Design) blog application demonstrating modern PHP architecture patterns. The project uses PHP 8.3, PostgreSQL 16, and runs in Docker containers. It was created for the presentation "From Tools to Teammates: Build AI Assisted Teams".

**Port Information**: The application runs on **port 8081** (changed from 8080 due to LocalStack conflict).

## Essential Commands

### Docker & Development

```bash
# Start containers
make up                    # or: docker-compose up -d

# Stop containers
make down                  # or: docker-compose down

# Access PHP container
make bash                  # or: docker exec -it blog-php sh

# Install dependencies
docker exec blog-php composer install

# Clear Symfony cache (important after config changes)
docker exec blog-php php bin/console cache:clear
```

### Database Operations

```bash
# Create database
docker exec blog-php php bin/console doctrine:database:create

# Create schema from Doctrine mappings (NO migrations used)
docker exec blog-php php bin/console doctrine:schema:create

# Load test data (creates 2 users, 3 posts)
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction

# Reset database
docker exec blog-php php bin/console doctrine:database:drop --force --if-exists
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:schema:create
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction

# Validate Doctrine mappings and schema
docker exec blog-php php bin/console doctrine:schema:validate
```

### Testing

```bash
# Run all tests
docker exec blog-php vendor/bin/pest

# Run tests with verbose output
docker exec blog-php vendor/bin/pest -v

# Run specific test suite
docker exec blog-php vendor/bin/pest tests/Unit
```

### JWT Authentication

```bash
# Generate JWT keys (required for login)
docker exec blog-php php bin/console lexik:jwt:generate-keypair
```

### Code Style

```bash
# Check code style
docker exec blog-php vendor/bin/pint --test

# Fix code style
docker exec blog-php vendor/bin/pint
```

### API Testing

```bash
# Test endpoints (note: port 8081)
curl http://localhost:8081/
curl http://localhost:8081/admin/dashboard

# Login and get JWT token
curl -X POST http://localhost:8081/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}'

# Use token for authenticated requests
curl http://localhost:8081/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Architecture Deep Dive

### DDD Bounded Contexts

The codebase is organized into three bounded contexts, each with strict layering:

```
src/
├── SharedKernel/          # Shared across all contexts
│   ├── Domain/
│   │   ├── ValueObject/   # Uuid, Email, CreatedAt
│   │   └── Exception/     # DomainException, ValidationException
│   └── Infrastructure/
│       └── Persistence/
│           └── Doctrine/
│               └── Type/  # Custom Doctrine types for value objects
│
├── Api/                   # REST API context
│   ├── Domain/            # Pure business logic, no dependencies
│   │   ├── Post/
│   │   │   ├── Entity/           # Post aggregate root
│   │   │   ├── ValueObject/      # PostTitle, PostContent, PostStatus
│   │   │   └── Repository/       # Interfaces only
│   │   └── User/
│   │       ├── Entity/           # User entity (implements UserInterface)
│   │       ├── ValueObject/      # UserRole
│   │       └── Repository/       # Interfaces only
│   │
│   ├── Application/       # Use cases (CQRS)
│   │   ├── Post/
│   │   │   ├── Command/          # CreatePost, PublishPost + Handlers
│   │   │   └── Query/            # ListPosts, GetPost + Handlers
│   │   └── User/
│   │       └── Command/          # RegisterUser + Handler
│   │
│   └── Infrastructure/    # Framework-specific implementations
│       ├── Controller/           # REST endpoints
│       └── Persistence/
│           └── Doctrine/
│               └── Repository/   # DoctrinePostRepository, DoctrineUserRepository
│
├── Admin/                 # Admin panel context
│   └── Infrastructure/
│       └── Controller/    # DashboardController
│
└── Web/                   # Public web context
    └── Infrastructure/
        └── Controller/    # HomeController
```

### Critical Architecture Decisions

#### 1. Doctrine Value Object Hydration

**Problem**: Doctrine cannot automatically convert database primitives to value objects.

**Solution**: Custom Doctrine types are registered for all value objects:
- `UuidType` → `Uuid` value object
- `EmailType` → `Email` value object
- `CreatedAtType` → `CreatedAt` value object
- `PostTitleType` → `PostTitle` value object
- `PostContentType` → `PostContent` value object
- `PostStatusType` → `PostStatus` value object
- `UserRoleType` → `UserRole` value object

**Location**:
- Types: `src/SharedKernel/Infrastructure/Persistence/Doctrine/Type/` and `src/Api/Infrastructure/Persistence/Doctrine/Type/`
- Registration: `config/packages/doctrine.yaml` under `dbal.types`
- XML Mappings: `config/doctrine/*.orm.xml` use these types (e.g., `type="uuid_vo"`)

**When adding new value objects**:
1. Create custom Doctrine type in appropriate Infrastructure layer
2. Register in `doctrine.yaml`
3. Use custom type in XML mappings
4. Clear Symfony cache

#### 2. Doctrine XML Mappings (Not Annotations)

**Why XML**:
- Keeps domain layer pure (no framework dependencies)
- Better for DDD architecture
- Mappings in `config/doctrine/`

**Important**:
- File names: `Post.Entity.Post.orm.xml` (NOT `Api.Domain.Post.Entity.Post.orm.xml`)
- Entity name must be fully qualified: `name="App\Api\Domain\Post\Entity\Post"`
- Repository class must be fully qualified in XML

#### 3. CQRS with Symfony Messenger

Commands and Queries are handled through Symfony Messenger:
- **Commands** (writes): `CreatePostCommand`, `PublishPostCommand`, `RegisterUserCommand`
- **Queries** (reads): `ListPostsQuery`, `GetPostQuery`
- **Handlers**: Decorated with `#[AsMessageHandler]` attribute
- **Routing**: Explicit class routing in `config/packages/messenger.yaml` (no wildcards)

#### 4. No Database Migrations

This project uses **schema creation directly from mappings**:
```bash
doctrine:schema:create    # NOT doctrine:migrations:migrate
```

This is intentional for the demo/presentation nature of the project.

## Common Development Workflows

### Adding a New Endpoint

1. Create Command/Query in `Application/`
2. Create Handler with `#[AsMessageHandler]`
3. Add routing in `config/packages/messenger.yaml`
4. Create Controller method using `MessageBusInterface`
5. Add route attribute: `#[Route('/path', name: 'name', methods: ['GET'])]`
6. Test endpoint with curl

### Adding a New Value Object

1. Create value object in `Domain/ValueObject/`
2. Create custom Doctrine type in `Infrastructure/Persistence/Doctrine/Type/`
3. Register type in `config/packages/doctrine.yaml`
4. Update XML mapping to use custom type
5. Clear cache: `php bin/console cache:clear`
6. Recreate schema if needed

### Modifying Domain Entities

1. Update entity in `Domain/Entity/`
2. Update XML mapping in `config/doctrine/`
3. Drop and recreate schema (no migrations):
   ```bash
   docker exec blog-php php bin/console doctrine:database:drop --force
   docker exec blog-php php bin/console doctrine:database:create
   docker exec blog-php php bin/console doctrine:schema:create
   docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
   ```

### Authentication Flow

- Default users: `admin@blog.com` / `password` and `user@blog.com` / `password`
- JWT tokens valid for 1 hour (3600 seconds)
- Token in `Authorization: Bearer <token>` header
- User ID retrieved via `$this->getUser()->getId()->toString()` in controllers

## Important Constraints

### Value Objects Are Immutable and Self-Validating

All value objects validate in constructor:
```php
PostTitle::fromString('abc');  // Throws ValidationException (min 3 chars)
Email::fromString('invalid');  // Throws ValidationException
```

Handle validation exceptions at the controller/application layer.

### Aggregate Root Encapsulation

`Post` is an aggregate root - all modifications must go through its methods:
```php
// CORRECT
$post->publish();

// WRONG - don't modify internals directly
$post->status = PostStatus::published();  // Won't work (readonly)
```

### Repository Interfaces in Domain

Repository interfaces live in Domain layer, implementations in Infrastructure:
```php
// Domain
interface PostRepositoryInterface { }

// Infrastructure
class DoctrinePostRepository implements PostRepositoryInterface { }
```

Never import infrastructure classes into domain layer.

## Testing Notes

- **Pest PHP** is the testing framework (not PHPUnit syntax)
- Test structure: `test('description', function() { ... })`
- Expectations: `expect($value)->toBeTrue()`
- Only Unit tests are currently configured (no Integration/Feature test directories)
- Tests use `KernelTestCase` for Symfony integration

## Troubleshooting

### Doctrine Type Errors

If you see "Cannot assign X to property Y of type Z":
1. Check if custom Doctrine type exists for the value object
2. Verify type is registered in `doctrine.yaml`
3. Confirm XML mapping uses the custom type
4. Clear cache and try again

### JWT Authentication Errors

If login returns "Invalid credentials":
1. Check password hash in database matches expected format
2. Regenerate JWT keys if missing
3. Verify security.yaml configuration

### Port Conflicts

The app uses port **8081** (not 8080). If you see connection errors:
- Check `docker-compose.yml` for port mappings
- Verify no other service is using 8081
- Use `http://localhost:8081/` in all curl commands

### Permission Errors

```bash
docker exec blog-php chmod -R 777 var/
```

### Container Issues

```bash
# View logs
docker-compose logs -f php
docker-compose logs -f nginx

# Rebuild
docker-compose down
docker-compose up -d --build
```

---

## Demo Workflows (Presentation)

This section contains workflows specifically designed for the "From Tools to Teammates: Build AI Assisted Teams" presentation.

### Available Slash Commands

| Command | Description | Example |
|---------|-------------|---------|
| `/jira-to-pr` | Complete Jira task → Development → PR | `/jira-to-pr BLOG-123` |
| `/write-to-confluence` | Write documentation to Confluence | `/write-to-confluence https://... /api/posts` |
| `/update-confluence` | Update existing Confluence docs | `/update-confluence https://...` |
| `/figma-to-code` | Convert Figma design to code | `/figma-to-code https://figma.com/...` |
| `/add-localization` | Add i18n support for a feature | `/add-localization posts` |
| `/create-tech-doc` | Create local API documentation | `/create-tech-doc /api/posts` |
| `/update-tech-doc` | Update local API documentation | `/update-tech-doc /api/posts` |

---

## Complete Demo Scenario Flow

This is the recommended order for the presentation demo:

### DEMO 1: Feature Development (Localization)

**Purpose**: Show how Claude can implement a complete feature from scratch.

**Run Command**:
```
/add-localization posts
```

**Or ask directly**:
```
Implement localization support for the blog application:
1. Install and configure Symfony Translation component
2. Create translation files for Turkish (tr) and English (en)
3. Create a new endpoint GET /api/translations/{locale}
4. Add locale parameter support to existing endpoints
```

**What Claude will do**:
1. Install `symfony/translation` via composer
2. Create `translations/messages.en.yaml` and `translations/messages.tr.yaml`
3. Create Translation Query + Handler (CQRS pattern)
4. Create TranslationController with `/api/translations/{locale}` endpoint
5. Update messenger.yaml routing
6. Test the endpoint

**Verify**:
```bash
curl http://localhost:8081/api/translations/en
curl http://localhost:8081/api/translations/tr
```

---

### DEMO 2: Jira Task → Development → PR (Workflow + MCP)

**Purpose**: Show end-to-end workflow automation with multiple MCP integrations.

**Run Command**:
```
/jira-to-pr BLOG-123
```

**What Claude will do**:

**Phase 1 - Task Analysis (Sub-Agent: Analyzer)**:
- Fetch Jira task using Atlassian MCP
- Extract requirements and acceptance criteria
- Create implementation plan

**Phase 2 - Development (Sub-Agent: Developer)**:
- Create feature branch: `feature/BLOG-123-description`
- Implement required changes following DDD patterns
- Run tests with `docker exec blog-php vendor/bin/pest`
- Fix any issues

**Phase 3 - Pull Request (Sub-Agent: Reviewer)**:
- Commit with conventional commit message
- Push to remote
- Create PR using GitHub MCP
- Link PR to Jira task

**Phase 4 - Status Update**:
- Update Jira task status to "In Review"
- Add PR link to task comments

**MCP Tools Used**:
```
mcp__atlassian__get_issue          → Read Jira task
mcp__atlassian__update_issue       → Update status
mcp__atlassian__add_comment        → Add PR link
mcp__github__create_pull_request   → Create PR
mcp__github__create_branch         → Create feature branch
```

---

### DEMO 3: Write Documentation to Confluence (Command)

**Purpose**: Show how to automatically generate and publish documentation.

**Run Command**:
```
/write-to-confluence https://your-company.atlassian.net/wiki/spaces/DOCS/pages/123456 /api/posts
```

**What Claude will do**:
1. Read the endpoint implementation (Controller, Handler, Entity)
2. Generate comprehensive documentation
3. Publish to specified Confluence page using Atlassian MCP
4. Return Confluence page URL

**MCP Tools Used**:
```
mcp__atlassian__create_page   → Create new page
mcp__atlassian__update_page   → Update existing page
```

---

### DEMO 4: Figma to Code (Design Implementation)

**Purpose**: Show design-to-code workflow with Figma MCP.

**Run Command**:
```
/figma-to-code https://www.figma.com/file/ABC123/BlogDesign?node-id=1:234
```

**What Claude will do**:
1. Fetch design from Figma using MCP
2. Extract design tokens (colors, typography, spacing)
3. Create Twig templates in `templates/`
4. Create CSS files in `public/css/`
5. Ensure responsive design

**MCP Tools Used**:
```
mcp__figma__get_file    → Fetch design file
mcp__figma__get_node    → Get specific component
mcp__figma__get_styles  → Extract design tokens
```

---

### DEMO 5: Update Feature → Update Confluence (Full Cycle)

**Purpose**: Show how documentation stays in sync with code changes.

**Step 1 - Make a code change**:
```
Add a new field "summary" to the Post entity and update the create/list endpoints
```

**Step 2 - Update documentation**:
```
/update-confluence https://your-company.atlassian.net/wiki/spaces/DOCS/pages/123456
```

**What Claude will do**:
1. Fetch existing Confluence page
2. Read current implementation
3. Compare and identify changes
4. Update documentation with new field
5. Add changelog entry
6. Publish updated documentation

---

### DEMO 6: Local Documentation Commands

**Create documentation**:
```
/create-tech-doc /api/posts
```
Creates `docs/api/posts-*.md` files locally.

**Update documentation**:
```
/update-tech-doc /api/posts
```
Updates existing local documentation.

---

## MCP Server Configuration

### Required Environment Variables

```bash
# Atlassian (Jira + Confluence)
export ATLASSIAN_USER_EMAIL="your-email@company.com"
export ATLASSIAN_API_TOKEN="your-atlassian-api-token"

# GitHub
export GITHUB_TOKEN="your-github-personal-access-token"

# Figma
export FIGMA_ACCESS_TOKEN="your-figma-access-token"
```

### Getting API Tokens

**Atlassian API Token**:
1. Go to https://id.atlassian.com/manage-profile/security/api-tokens
2. Create API token
3. Use your email + token for authentication

**GitHub Personal Access Token**:
1. Go to GitHub Settings → Developer settings → Personal access tokens
2. Create token with `repo`, `workflow` scopes

**Figma Access Token**:
1. Go to Figma → Settings → Account → Personal access tokens
2. Create new token

---

## File Structure for Claude Code

```
.claude/
├── agents/                       # Agent persona definitions
│   ├── task-analyzer.md          # Analyzes Jira tasks, extracts requirements
│   ├── backend-developer.md      # Implements features following DDD
│   ├── code-reviewer.md          # Reviews code, runs tests
│   └── pr-creator.md             # Creates commits, PRs, updates Jira
├── commands/                     # Slash commands (workflows)
│   ├── jira-to-pr.md             # Orchestrator - coordinates all agents
│   ├── parallel-features.md      # Parallel agent orchestrator with worktrees
│   ├── write-to-confluence.md    # Write docs to Confluence
│   ├── update-confluence.md      # Update Confluence docs
│   ├── figma-to-code.md          # Figma design to code
│   ├── add-localization.md       # Add i18n support
│   ├── create-tech-doc.md        # Create local API docs
│   └── update-tech-doc.md        # Update local API docs
├── skills/                       # Reusable skills (code generation, testing)
│   ├── generate-entity.md        # Generate DDD entity with all components
│   ├── generate-endpoint.md      # Generate REST endpoint with CQRS
│   ├── run-quality-checks.md     # Run all quality checks
│   ├── reset-database.md         # Reset database to clean state
│   ├── test-endpoint.md          # Test API endpoint with auth
│   └── worktree-setup.md         # Setup git worktree for parallel dev
└── settings.json                 # MCP servers, permissions, custom instructions

docs/
└── api/
    └── README.md                 # API documentation index
```

---

## Agent System Architecture

This project demonstrates a **multi-agent workflow pattern** using Claude Code. While Claude Code doesn't have native multi-agent support, we simulate it using:

1. **Agent Persona Files** (`.claude/agents/`) - Define specialized roles
2. **Orchestrator Commands** (`.claude/commands/`) - Coordinate agents

### How It Works

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         ORCHESTRATOR COMMAND                                 │
│                         /jira-to-pr BLOG-123                                │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
            ┌───────────────────────┼───────────────────────┐
            ▼                       ▼                       ▼
    ┌───────────────┐      ┌───────────────┐      ┌───────────────┐
    │ Read Agent    │      │ Execute Phase │      │ Handoff Data  │
    │ Persona File  │ ──▶  │ Instructions  │ ──▶  │ to Next Agent │
    └───────────────┘      └───────────────┘      └───────────────┘
```

### Agent Workflow Phases

| Phase | Agent | Responsibility | Input | Output |
|-------|-------|----------------|-------|--------|
| 1 | Task Analyzer | Understand requirements | Jira Task | Analysis Report |
| 2 | Backend Developer | Implement code | Analysis | Code Changes |
| 3 | Code Reviewer | Verify quality | Changes | Review Report |
| 4 | PR Creator | Create PR, update Jira | Approval | PR URL |

### Agent Persona Structure

Each agent file (`.claude/agents/*.md`) contains:

```markdown
# {Agent Name}

## Role & Responsibilities
- What this agent does

## Persona
- Name, expertise, communication style

## Input
- What it receives from previous agent

## Output
- What it produces

## Behavior Rules
- Guidelines and constraints

## Handoff
- What to pass to next agent
```

### Why This Pattern?

1. **Separation of Concerns** - Each agent focuses on one task
2. **Reusability** - Agents can be used in different workflows
3. **Clarity** - Clear handoff points between phases
4. **Debugging** - Easy to identify which phase failed
5. **Demo Value** - Shows AI workflow orchestration concept

---

## Parallel Agents with Git Worktrees

The most powerful demo: **multiple agents working simultaneously** on independent features using Git worktrees for isolation.

### What is Git Worktree?

Git worktree allows multiple working directories from the same repository:

```
Normal:                          With Worktrees:

project/                         project/              (main)
├── .git/                        ├── .git/
├── src/                         └── src/
└── (single branch)
                                 project-worktrees/
                                 ├── feature-comment/  (feature/comment branch)
                                 │   └── src/
                                 ├── feature-tag/      (feature/tag branch)
                                 │   └── src/
                                 └── feature-category/ (feature/category branch)
                                     └── src/
```

**Key Benefit**: Each worktree is **completely isolated** - agents can work in parallel without conflicts!

### Parallel Agent Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    /parallel-features "Comment,Tag,Category"                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
        ┌───────────────────────────┼───────────────────────┐
        │                           │                       │
        ▼                           ▼                       ▼
┌───────────────┐           ┌───────────────┐       ┌───────────────┐
│  WORKTREE 1   │           │  WORKTREE 2   │       │  WORKTREE 3   │
│  feature/     │           │  feature/     │       │  feature/     │
│  comment      │           │  tag          │       │  category     │
└───────┬───────┘           └───────┬───────┘       └───────┬───────┘
        │                           │                       │
        ▼                           ▼                       ▼
┌───────────────┐           ┌───────────────┐       ┌───────────────┐
│   Agent 1     │           │   Agent 2     │       │   Agent 3     │
│   Comment     │           │   Tag         │       │   Category    │
│   Entity +    │           │   Entity +    │       │   Entity +    │
│   Endpoints   │           │   Endpoints   │       │   Endpoints   │
└───────┬───────┘           └───────┬───────┘       └───────┬───────┘
        │                           │                       │
        │         ┌─────────────────┼─────────────────┐     │
        │         │                 │                 │     │
        └─────────┴─────────────────┼─────────────────┴─────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │   MERGE ALL + COMBINED PR     │
                    └───────────────────────────────┘
```

### Time Comparison

```
Sequential (Traditional):
Agent 1: [====Comment====]
                          Agent 2: [====Tag====]
                                                Agent 3: [====Category====]
Total: ─────────────────────────────────────────────────────────────────▶ ~9 min

Parallel (With Worktrees):
Agent 1: [====Comment====]
Agent 2: [====Tag====]
Agent 3: [====Category====]
Total: ──────────────────▶ ~3 min

Time Saved: ~65%
```

### Demo Command

```
/parallel-features "Comment,Tag,Category"
```

**What happens**:
1. Creates 3 git worktrees
2. Launches 3 agents **in parallel**
3. Each generates: Entity + Value Objects + Repository + Endpoints
4. Each runs quality checks independently
5. Merges all branches
6. Creates combined PR
7. Cleans up worktrees

### Manual Setup (for understanding)

```bash
# 1. Create worktrees
git worktree add ../blog-worktrees/feature-comment -b feature/add-comment
git worktree add ../blog-worktrees/feature-tag -b feature/add-tag
git worktree add ../blog-worktrees/feature-category -b feature/add-category

# 2. List worktrees
git worktree list

# 3. Each agent works in their worktree
# Agent 1 in: ../blog-worktrees/feature-comment
# Agent 2 in: ../blog-worktrees/feature-tag
# Agent 3 in: ../blog-worktrees/feature-category

# 4. After all complete, merge
git merge feature/add-comment --no-ff
git merge feature/add-tag --no-ff
git merge feature/add-category --no-ff

# 5. Cleanup
git worktree remove ../blog-worktrees/feature-comment
git worktree remove ../blog-worktrees/feature-tag
git worktree remove ../blog-worktrees/feature-category
```

### Docker Consideration

For this dockerized project, add worktrees mount to `docker-compose.yml`:

```yaml
services:
  php:
    volumes:
      - .:/app
      - ../blog-worktrees:/app-worktrees  # Add for parallel agents
```

### When to Use Parallel Agents

| Scenario | Use Parallel? | Why |
|----------|---------------|-----|
| 3 independent entities | ✅ Yes | No file overlap |
| Feature + its tests | ❌ No | Same files |
| Bug fixes in different modules | ✅ Yes | Different file sets |
| Refactoring single module | ❌ No | Same files |
| Multiple microservices | ✅ Yes | Different codebases |

---

## Skills System

Skills are **reusable, focused capabilities** that automate repetitive development tasks while ensuring project standards are followed.

### Available Skills

| Skill | Command | Purpose |
|-------|---------|---------|
| Generate Entity | `/generate-entity` | Create DDD entity with all components |
| Generate Endpoint | `/generate-endpoint` | Create REST endpoint with CQRS |
| Quality Checks | `/run-quality-checks` | Run tests, linting, validation |
| Reset Database | `/reset-database` | Reset to clean state with fixtures |
| Test Endpoint | `/test-endpoint` | Test API with auto-auth |

### Skills vs Commands vs Agents

| Aspect | Skills | Commands | Agents |
|--------|--------|----------|--------|
| **Purpose** | Automate repetitive tasks | Orchestrate workflows | Define personas/roles |
| **Scope** | Single focused task | Multi-step process | Behavior guidelines |
| **Example** | Generate entity | Jira to PR | Backend Developer |
| **Reusability** | High | Medium | High |
| **Complexity** | Low-Medium | High | N/A (personas) |

### Skill Demo Scenarios

#### Scenario A: Rapid Entity Development

```
# 1. Generate entity with all DDD components
/generate-entity Comment Api --fields="content:text,postId:uuid"

# 2. Generate CRUD endpoints
/generate-endpoint POST /api/comments Api --entity=Comment
/generate-endpoint GET /api/comments Api --entity=Comment

# 3. Run quality checks
/run-quality-checks

# 4. Test the new endpoint
/test-endpoint POST /api/comments --auth --data='{"content":"Test","postId":"..."}'
```

**What audience sees**: Complete feature from zero to working API in minutes!

#### Scenario B: Development Workflow

```
# 1. Reset database for clean state
/reset-database

# 2. Implement new feature (manual or via /jira-to-pr)
...

# 3. Run all quality checks before PR
/run-quality-checks --fix

# 4. Test all affected endpoints
/test-endpoint GET /api/posts --auth
/test-endpoint POST /api/posts --auth --data='{"title":"Test","content":"..."}'
```

#### Scenario C: Quick Prototyping

```
# Generate entire Comment feature in one flow:
/generate-entity Comment Api --fields="content:text,postId:uuid,authorId:uuid"
/generate-endpoint POST /api/posts/{postId}/comments Api --entity=Comment
/generate-endpoint GET /api/posts/{postId}/comments Api --entity=Comment
/reset-database
/test-endpoint GET /api/posts/{postId}/comments --auth
```

### Skill Design Principles

1. **Single Responsibility** - One skill, one job
2. **Idempotent** - Safe to run multiple times
3. **Project-Aware** - Follow existing patterns automatically
4. **Self-Documenting** - Clear output explaining what was done
5. **Error Handling** - Graceful failure with actionable suggestions

---

## Best Practices for Demo

1. **Always use Docker**: All PHP commands must run via `docker exec blog-php`
2. **Port 8081**: Application runs on port 8081, not 8080
3. **Reset between demos**:
   ```bash
   docker exec blog-php php bin/console doctrine:database:drop --force
   docker exec blog-php php bin/console doctrine:database:create
   docker exec blog-php php bin/console doctrine:schema:create
   docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
   ```
4. **Verify MCP connections**: Ensure all environment variables are set before demo
5. **Show the process**: Let Claude explain what it's doing step by step
6. **Test endpoints**: Always verify with curl after implementation

---

## Demo Preparation Checklist

- [ ] Docker containers running (`make up`)
- [ ] Database initialized with fixtures
- [ ] JWT keys generated
- [ ] Environment variables set for MCP servers
- [ ] Jira task created for Demo 2
- [ ] Confluence page created for Demo 3
- [ ] Figma design ready for Demo 4
- [ ] Test all slash commands work
