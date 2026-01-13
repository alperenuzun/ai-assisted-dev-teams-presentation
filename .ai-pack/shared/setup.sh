#!/bin/bash

# AI Pack Setup Script
# This script sets up AI assistant integration for various IDEs and AI tools
# Usage: ./setup.sh [tool-name]
# Supported tools: vscode, cursor, windsurf, jetbrains, claude, github-copilot, all

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
AI_PACK_DIR="$PROJECT_ROOT/.ai-pack"
SHARED_DIR="$AI_PACK_DIR/shared"

# ============================================================================
# Helper Functions
# ============================================================================

print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${CYAN}ℹ${NC} $1"
}

# Check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Create directory if it doesn't exist
ensure_dir() {
    if [ ! -d "$1" ]; then
        mkdir -p "$1"
        print_success "Created directory: $1"
    fi
}

# Backup existing file
backup_file() {
    if [ -f "$1" ]; then
        cp "$1" "$1.backup.$(date +%Y%m%d_%H%M%S)"
        print_info "Backed up existing file: $1"
    fi
}

# Convert JSON agent to Markdown format
json_to_markdown() {
    local json_file="$1"
    local output_file="$2"
    
    if ! command_exists jq; then
        print_warning "jq not installed. Skipping JSON to Markdown conversion."
        return 1
    fi
    
    local name=$(jq -r '.name // "Agent"' "$json_file")
    local role=$(jq -r '.role // "specialist"' "$json_file")
    local description=$(jq -r '.description // ""' "$json_file")
    
    cat > "$output_file" << EOF
# $name

**Role**: $role

## Description

$description

## Expertise

$(jq -r '.expertise // [] | .[] | "- " + .' "$json_file")

## Responsibilities

$(jq -r '.responsibilities // [] | .[] | "- " + .' "$json_file")

## Rules

$(jq -r '.rules // [] | .[] | "- " + .' "$json_file")

## Best Practices

$(jq -r '.best_practices // [] | .[] | "- " + .' "$json_file")

---
*Generated from: $(basename "$json_file")*
EOF
    
    print_success "Converted $json_file to Markdown"
}

# ============================================================================
# VS Code Setup
# ============================================================================

setup_vscode() {
    print_header "Setting up VS Code Integration"
    
    local vscode_dir="$PROJECT_ROOT/.vscode"
    ensure_dir "$vscode_dir"
    
    # Create settings.json
    local settings_file="$vscode_dir/settings.json"
    backup_file "$settings_file"
    
    cat > "$settings_file" << 'EOF'
{
  "ai-pack.enabled": true,
  "ai-pack.agentsPath": ".ai-pack/shared/agents",
  "ai-pack.contextPath": ".ai-pack/shared/context",
  "ai-pack.templatesPath": ".ai-pack/shared/templates",
  "ai-pack.workflowsPath": ".ai-pack/shared/workflows",
  "ai-pack.instructionsFile": ".ai-pack/shared/instructions.md",
  "ai-pack.ignorePatterns": ".ai-pack/shared/ignore-patterns.txt",
  
  "ai-pack.agents.enabled": [
    "architect",
    "frontend-specialist",
    "backend-specialist",
    "code-reviewer",
    "qa-tester",
    "devops-engineer",
    "qa-staging-endpoint-specialist"
  ],
  
  "ai-pack.agents.defaultAgent": "code-reviewer",
  "ai-pack.agents.autoSuggest": true,
  
  "ai-pack.codeGeneration.includeTests": true,
  "ai-pack.codeGeneration.includeDocumentation": true,
  "ai-pack.codeGeneration.followStandards": true,
  
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll": "explicit",
    "source.organizeImports": "explicit"
  },
  
  "files.exclude": {
    "**/.git": true,
    "**/node_modules": true,
    "**/vendor": true,
    "**/var": true
  },
  
  "search.exclude": {
    "**/node_modules": true,
    "**/vendor": true,
    "**/var": true,
    "**/*.min.js": true
  }
}
EOF
    
    print_success "Created VS Code settings.json"
    
    # Create extensions.json
    local extensions_file="$vscode_dir/extensions.json"
    backup_file "$extensions_file"
    
    cat > "$extensions_file" << 'EOF'
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",
    "xdebug.php-debug",
    "github.copilot",
    "github.copilot-chat",
    "eamodio.gitlens",
    "usernamehw.errorlens",
    "streetsidesoftware.code-spell-checker"
  ]
}
EOF
    
    print_success "Created VS Code extensions.json"
    print_success "VS Code setup completed!"
}

# ============================================================================
# Cursor Setup
# ============================================================================

setup_cursor() {
    print_header "Setting up Cursor Integration"
    
    local cursor_dir="$PROJECT_ROOT/.cursor"
    ensure_dir "$cursor_dir"
    ensure_dir "$cursor_dir/agents"
    ensure_dir "$cursor_dir/commands"
    ensure_dir "$cursor_dir/skills"
    
    # Create .cursorrules file
    local cursorrules_file="$PROJECT_ROOT/.cursorrules"
    backup_file "$cursorrules_file"
    
    cat > "$cursorrules_file" << 'EOF'
# Cursor AI Rules - Symfony DDD Blog Application

## Project Context
- Read and follow instructions in `.ai-pack/shared/instructions.md`
- Use agents defined in `.ai-pack/shared/agents/`
- Follow coding standards in `.ai-pack/shared/context/coding-standards.md`
- Use commands from `.ai-pack/shared/commands/`
- Respect ignore patterns in `.ai-pack/shared/ignore-patterns.txt`

## Technology Stack
- PHP 8.3 with Symfony 7.3
- PostgreSQL 16
- Docker containers
- DDD (Domain-Driven Design) architecture

## Critical Rules
1. ALWAYS use `docker exec blog-php` for ALL PHP commands
2. Application runs on port 8081 (not 8080)
3. Follow DDD architecture patterns strictly
4. Use XML Doctrine mappings (not annotations)
5. No database migrations - use schema:create

## Code Generation
- Always include strict types declaration
- Write tests for new functionality using Pest PHP
- Add PHPDoc comments for public methods
- Follow existing code patterns in the codebase
- Use CQRS pattern with Symfony Messenger

## Architecture Layers
- Domain: Entities, Value Objects, Repository Interfaces
- Application: Commands, Queries, Handlers
- Infrastructure: Controllers, Doctrine Repositories, Types

## Testing
- Use Pest PHP syntax
- Follow AAA pattern (Arrange, Act, Assert)
- Include edge cases and error scenarios

## Available Commands
- /create-staging-endpoint - Create QA automation endpoint
- /create-tech-doc - Create API documentation
- /jira-to-pr - Complete Jira task and create PR
- /figma-to-code - Convert Figma design to code

## Security
- Never commit secrets or API keys
- Validate all user inputs
- Use parameterized queries via Doctrine
EOF
    
    print_success "Created .cursorrules file"
    
    # Copy agents from .ai-pack to .cursor/agents (convert JSON to MD)
    if [ -d "$SHARED_DIR/agents" ]; then
        for agent_file in "$SHARED_DIR/agents"/*.json; do
            if [ -f "$agent_file" ]; then
                local agent_name=$(basename "$agent_file" .json)
                local md_file="$cursor_dir/agents/$agent_name.md"
                json_to_markdown "$agent_file" "$md_file" || {
                    # Fallback: simple copy with description
                    cat > "$md_file" << EOF
# $(jq -r '.name // "Agent"' "$agent_file" 2>/dev/null || echo "$agent_name")

See full configuration at: .ai-pack/shared/agents/$agent_name.json

---
*Converted from JSON agent definition*
EOF
                }
            fi
        done
        print_success "Converted agents to Cursor format"
    fi
    
    # Copy commands from .ai-pack to .cursor/commands
    if [ -d "$SHARED_DIR/commands" ]; then
        for cmd_file in "$SHARED_DIR/commands"/*.md; do
            if [ -f "$cmd_file" ]; then
                cp "$cmd_file" "$cursor_dir/commands/"
            fi
        done
        print_success "Copied commands to Cursor"
    fi
    
    # Create Cursor settings
    local settings_file="$cursor_dir/settings.json"
    backup_file "$settings_file"
    
    cat > "$settings_file" << 'EOF'
{
  "aiPack": {
    "enabled": true,
    "agentsDirectory": ".ai-pack/shared/agents",
    "commandsDirectory": ".ai-pack/shared/commands",
    "contextDirectory": ".ai-pack/shared/context",
    "instructionsFile": ".ai-pack/shared/instructions.md",
    "ignorePatterns": ".ai-pack/shared/ignore-patterns.txt",
    "defaultAgent": "code-reviewer",
    "enabledAgents": ["*"]
  },
  "cursor.ai.useProjectContext": true,
  "cursor.ai.followProjectRules": true
}
EOF
    
    print_success "Created Cursor settings.json"
    print_success "Cursor setup completed!"
}

# ============================================================================
# GitHub Copilot Setup
# ============================================================================

setup_github_copilot() {
    print_header "Setting up GitHub Copilot Integration"
    
    local github_dir="$PROJECT_ROOT/.github"
    ensure_dir "$github_dir"
    ensure_dir "$github_dir/agents"
    ensure_dir "$github_dir/prompts"
    
    # Create copilot-instructions.md
    local instructions_file="$github_dir/copilot-instructions.md"
    backup_file "$instructions_file"
    
    cat > "$instructions_file" << 'EOF'
# GitHub Copilot Instructions

## Project Overview

This is a Symfony 7.3 DDD (Domain-Driven Design) blog application. It demonstrates modern PHP architecture patterns with PHP 8.3, PostgreSQL 16, and Docker containers.

## Technology Stack

- **Language**: PHP 8.3 with strict types
- **Framework**: Symfony 7.3
- **Database**: PostgreSQL 16
- **ORM**: Doctrine with XML mappings
- **Testing**: Pest PHP
- **Container**: Docker with docker-compose
- **Architecture**: DDD with CQRS pattern

## Project Structure

```
src/
├── Api/
│   ├── Domain/           # Entities, Value Objects, Repository Interfaces
│   ├── Application/      # Commands, Queries, Handlers (CQRS)
│   └── Infrastructure/   # Controllers, Doctrine Repositories
├── Admin/
│   └── Infrastructure/   # Admin controllers
├── Web/
│   └── Infrastructure/   # Public web controllers
└── SharedKernel/
    ├── Domain/           # Shared Value Objects, Exceptions
    └── Infrastructure/   # Shared Doctrine Types
```

## Critical Development Rules

### Docker Commands
Always use Docker for PHP commands:
```bash
docker exec blog-php composer install
docker exec blog-php php bin/console cache:clear
docker exec blog-php vendor/bin/pest
```

### Port Configuration
- Application: `http://localhost:8081`
- NOT port 8080 (LocalStack conflict)

### Doctrine Configuration
- Use XML mappings in `config/doctrine/`
- NO annotations in entity classes
- Custom types for Value Objects in `doctrine.yaml`

### Database Operations
```bash
# Reset database
docker exec blog-php php bin/console doctrine:database:drop --force --if-exists
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:schema:create
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
```

## Code Style Guidelines

### Entity Pattern
```php
<?php

declare(strict_types=1);

namespace App\Api\Domain\Entity;

use App\SharedKernel\Domain\ValueObject\Uuid;
use App\SharedKernel\Domain\ValueObject\CreatedAt;

class Entity
{
    private Uuid $id;
    private CreatedAt $createdAt;

    private function __construct(Uuid $id, CreatedAt $createdAt)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public static function create(): self
    {
        return new self(Uuid::generate(), CreatedAt::now());
    }
}
```

### Controller Pattern
```php
<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;

#[Route('/resource', name: 'resource_')]
class ResourceController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Use message bus for CQRS
    }
}
```

### Test Pattern (Pest PHP)
```php
<?php

declare(strict_types=1);

test('entity can be created', function () {
    $entity = Entity::create();
    
    expect($entity->getId())->toBeInstanceOf(Uuid::class);
});
```

## Available AI Agents

The project includes specialized AI agents in `.ai-pack/shared/agents/`:

- **qa-staging-endpoint-agent**: Creates QA automation endpoints in StageController
- **backend-feature-agent**: Implements backend features with DDD patterns
- **test-writer-agent**: Generates Pest PHP tests
- **security-auditor-agent**: Reviews code for security issues

## QA Staging Endpoints

For QA automation, use the StageController at `/api/stage/`:
- `GET /api/stage/health` - Health check
- `GET /api/stage/users` - Mock user list
- `POST /api/stage/echo` - Echo request parameters

Create new endpoints using the qa-staging-endpoint-agent.

## Authentication

- JWT-based authentication via LexikJWTAuthenticationBundle
- Default users:
  - admin@blog.com / password
  - user@blog.com / password

## Helpful Resources

- Agent definitions: `.ai-pack/shared/agents/`
- Commands: `.ai-pack/shared/commands/`
- Project documentation: `CLAUDE.md`
EOF

    print_success "Created copilot-instructions.md"
    
    # Create agent files for GitHub Copilot (workspace agents)
    if [ -d "$SHARED_DIR/agents" ]; then
        for agent_file in "$SHARED_DIR/agents"/*.json; do
            if [ -f "$agent_file" ]; then
                local agent_name=$(basename "$agent_file" .json)
                local copilot_agent_file="$github_dir/agents/$agent_name.md"
                
                # Create GitHub Copilot agent format
                cat > "$copilot_agent_file" << EOF
---
name: $(jq -r '.name // "Agent"' "$agent_file" 2>/dev/null || echo "$agent_name")
description: $(jq -r '.description // "AI Agent"' "$agent_file" 2>/dev/null || echo "Specialized agent")
---

# $(jq -r '.name // "Agent"' "$agent_file" 2>/dev/null || echo "$agent_name")

$(jq -r '.description // ""' "$agent_file" 2>/dev/null)

## Expertise

$(jq -r '.expertise // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Responsibilities

$(jq -r '.responsibilities // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Rules

$(jq -r '.rules // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## How to Use

Reference this agent in your prompt:
\`\`\`
@workspace /agent:$agent_name Help me with...
\`\`\`

## Source

Original configuration: \`.ai-pack/shared/agents/$agent_name.json\`
EOF
                print_success "Created GitHub Copilot agent: $agent_name"
            fi
        done
    fi
    
    # Create prompt templates
    ensure_dir "$github_dir/prompts"
    
    cat > "$github_dir/prompts/create-endpoint.md" << 'EOF'
---
name: Create API Endpoint
description: Create a new REST API endpoint following DDD patterns
---

Create a new API endpoint with the following specifications:

## Input
- Endpoint path: {{path}}
- HTTP method: {{method}}
- Request parameters: {{request_params}}
- Response format: {{response_format}}

## Requirements
- Follow DDD architecture (Domain → Application → Infrastructure)
- Use CQRS pattern with Symfony Messenger
- Include proper validation
- Add PHPDoc comments
- Create corresponding test with Pest PHP

## Output
Generate the following files:
1. Command/Query in Application layer
2. Handler with #[AsMessageHandler]
3. Controller method with Route attribute
4. Test file
EOF
    
    cat > "$github_dir/prompts/create-staging-endpoint.md" << 'EOF'
---
name: Create Staging Endpoint
description: Create a QA automation endpoint in StageController
---

Create a new staging endpoint for QA automation testing.

## Input
- Endpoint name: {{endpoint_name}}
- HTTP method: {{method}}
- Request parameters: {{request_params}}
- Response parameters: {{response_params}}

## Requirements
- Add to StageController only
- No authentication required
- No database access
- Return consistent JSON format: {status, data, timestamp}
- Include parameter validation

## Template
```php
#[Route('/{{path}}', name: 'stage_{{name}}', methods: ['{{method}}'])]
public function {{methodName}}(Request $request): JsonResponse
{
    // Extract parameters
    // Generate mock data
    // Return JSON response
}
```
EOF
    
    print_success "Created prompt templates"
    
    # Create AGENTS.md for GitHub Copilot reference
    cat > "$github_dir/AGENTS.md" << 'EOF'
# GitHub Copilot Agents

This directory contains AI agent definitions compatible with GitHub Copilot.

## Available Agents

| Agent | File | Purpose |
|-------|------|---------|
| QA Staging Endpoint | `agents/qa-staging-endpoint-agent.md` | Create QA automation endpoints |
| Backend Feature | `agents/backend-feature-agent.md` | Implement backend features |
| Test Writer | `agents/test-writer-agent.md` | Generate Pest PHP tests |
| Security Auditor | `agents/security-auditor-agent.md` | Security code review |
| Tech Document Writer | `agents/tech-document-writer-agent.md` | API documentation |

## Usage

### In GitHub Copilot Chat
```
@workspace /agent:qa-staging-endpoint-agent Create an endpoint for...
```

### In Copilot Edits
Reference agent instructions when requesting code changes.

## Adding New Agents

1. Create JSON definition in `.ai-pack/shared/agents/`
2. Run `./setup.sh github-copilot` to generate GitHub format
3. Agent will be available in `.github/agents/`

## Prompts

Pre-defined prompts are available in `.github/prompts/`:
- `create-endpoint.md` - Create REST API endpoint
- `create-staging-endpoint.md` - Create QA staging endpoint
EOF
    
    print_success "Created GitHub Copilot AGENTS.md"
    print_success "GitHub Copilot setup completed!"
}

# ============================================================================
# Claude Setup
# ============================================================================

setup_claude() {
    print_header "Setting up Claude Integration"
    
    local claude_dir="$PROJECT_ROOT/.claude"
    ensure_dir "$claude_dir"
    ensure_dir "$claude_dir/agents"
    ensure_dir "$claude_dir/commands"
    ensure_dir "$claude_dir/skills"
    
    # Copy agents from .ai-pack to .claude/agents (convert JSON to MD)
    if [ -d "$SHARED_DIR/agents" ]; then
        for agent_file in "$SHARED_DIR/agents"/*.json; do
            if [ -f "$agent_file" ]; then
                local agent_name=$(basename "$agent_file" .json)
                local md_file="$claude_dir/agents/$agent_name.md"
                
                # Create Claude-compatible agent format
                cat > "$md_file" << EOF
# $(jq -r '.name // "Agent"' "$agent_file" 2>/dev/null || echo "$agent_name")

## Role & Responsibilities

**Role**: $(jq -r '.role // "specialist"' "$agent_file" 2>/dev/null)

$(jq -r '.description // ""' "$agent_file" 2>/dev/null)

## Expertise

$(jq -r '.expertise // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Responsibilities

$(jq -r '.responsibilities // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Persona

- **Name**: $(jq -r '.name // "AI Agent"' "$agent_file" 2>/dev/null)
- **Expertise**: $(jq -r '.expertise // [] | .[0] // "General"' "$agent_file" 2>/dev/null)
- **Communication Style**: Clear, technical, focused on implementation

## Behavior Rules

$(jq -r '.rules // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Best Practices

$(jq -r '.best_practices // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Related Files

$(jq -r '.related_files // [] | .[] | "- `" + . + "`"' "$agent_file" 2>/dev/null)

---
*Source: .ai-pack/shared/agents/$agent_name.json*
EOF
                print_success "Created Claude agent: $agent_name"
            fi
        done
    fi
    
    # Copy commands from .ai-pack to .claude/commands
    if [ -d "$SHARED_DIR/commands" ]; then
        for cmd_file in "$SHARED_DIR/commands"/*.md; do
            if [ -f "$cmd_file" ]; then
                cp "$cmd_file" "$claude_dir/commands/"
            fi
        done
        print_success "Copied commands to Claude"
    fi
    
    # Update settings.json
    local settings_file="$claude_dir/settings.json"
    backup_file "$settings_file"
    
    cat > "$settings_file" << 'EOF'
{
  "permissions": {
    "allow": [
      "Bash(docker exec*)",
      "Bash(docker-compose*)",
      "Bash(docker cp*)",
      "Bash(make*)",
      "Bash(curl*)",
      "Bash(git*)",
      "Bash(gh*)",
      "Bash(mkdir*)",
      "Bash(ls*)",
      "Bash(cat*)",
      "Read",
      "Write",
      "Edit",
      "Glob",
      "Grep",
      "WebFetch",
      "Task"
    ],
    "deny": [
      "Bash(rm -rf /)",
      "Bash(docker rm -f*)",
      "Bash(docker-compose down -v)",
      "Bash(docker system prune*)"
    ]
  },
  "mcpServers": {
    "atlassian": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-atlassian"],
      "env": {
        "ATLASSIAN_SITE_URL": "https://your-company.atlassian.net",
        "ATLASSIAN_USER_EMAIL": "${ATLASSIAN_USER_EMAIL}",
        "ATLASSIAN_API_TOKEN": "${ATLASSIAN_API_TOKEN}"
      }
    },
    "github": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-github"],
      "env": {
        "GITHUB_PERSONAL_ACCESS_TOKEN": "${GITHUB_TOKEN}"
      }
    },
    "figma": {
      "command": "npx",
      "args": ["-y", "@anthropic/mcp-server-figma"],
      "env": {
        "FIGMA_ACCESS_TOKEN": "${FIGMA_ACCESS_TOKEN}"
      }
    }
  },
  "model": "claude-sonnet-4-20250514",
  "customInstructions": "This is a Symfony 7.3 DDD blog application running in Docker containers.\n\nCRITICAL RULES:\n1. ALWAYS use 'docker exec blog-php' for ALL PHP commands\n2. Application runs on port 8081 (not 8080)\n3. Follow DDD architecture patterns strictly\n4. Use XML Doctrine mappings (not annotations)\n5. No database migrations - use schema:create\n\nAVAILABLE SLASH COMMANDS:\n- /create-staging-endpoint - Create QA automation endpoint\n- /jira-to-pr {TASK-KEY} - Complete Jira task and create PR\n- /write-to-confluence {URL} {endpoint} - Write docs to Confluence\n- /figma-to-code {FIGMA-URL} - Convert Figma design to code\n- /add-localization {feature} - Add i18n support\n- /create-tech-doc {endpoint} - Create API documentation"
}
EOF
    
    print_success "Updated Claude settings.json"
    print_success "Claude setup completed!"
}

# ============================================================================
# Windsurf Setup
# ============================================================================

setup_windsurf() {
    print_header "Setting up Windsurf Integration"
    
    local windsurf_dir="$PROJECT_ROOT/.windsurf"
    ensure_dir "$windsurf_dir"
    ensure_dir "$windsurf_dir/agents"
    ensure_dir "$windsurf_dir/rules"
    
    # Create .windsurfrules file
    local windsurfrules_file="$PROJECT_ROOT/.windsurfrules"
    backup_file "$windsurfrules_file"
    
    cat > "$windsurfrules_file" << 'EOF'
# Windsurf AI Rules - Symfony DDD Blog Application

## AI Pack Integration
- Load context from `.ai-pack/shared/`
- Use specialized agents for different tasks
- Follow project instructions and standards
- Respect ignore patterns

## Technology Stack
- PHP 8.3 with Symfony 7.3
- PostgreSQL 16
- Docker containers
- DDD architecture with CQRS

## Critical Rules
1. ALWAYS use `docker exec blog-php` for PHP commands
2. Application runs on port 8081
3. Follow DDD architecture strictly
4. Use XML Doctrine mappings
5. Use Pest PHP for testing

## Development Workflow
1. Understand requirements thoroughly
2. Check existing patterns and implementations
3. Write tests first (TDD)
4. Implement minimal solution
5. Refactor for quality
6. Document changes

## Code Quality
- Follow SOLID principles
- Keep functions small and focused
- Use meaningful names
- Handle errors properly
- Include strict types

## Available Agents
- qa-staging-endpoint-agent: QA automation endpoints
- backend-feature-agent: Backend DDD features
- test-writer-agent: Pest PHP tests
- security-auditor-agent: Security review

## Collaboration
- Use appropriate specialist agents
- Review code with code-reviewer agent
- Generate tests with test-writer agent
- Consult backend-feature-agent for DDD patterns
EOF
    
    print_success "Created .windsurfrules file"
    
    # Copy agents from .ai-pack to .windsurf/agents
    if [ -d "$SHARED_DIR/agents" ]; then
        for agent_file in "$SHARED_DIR/agents"/*.json; do
            if [ -f "$agent_file" ]; then
                local agent_name=$(basename "$agent_file" .json)
                local ws_agent_file="$windsurf_dir/agents/$agent_name.md"
                
                cat > "$ws_agent_file" << EOF
# $(jq -r '.name // "Agent"' "$agent_file" 2>/dev/null || echo "$agent_name")

## Description
$(jq -r '.description // ""' "$agent_file" 2>/dev/null)

## Expertise
$(jq -r '.expertise // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

## Rules
$(jq -r '.rules // [] | .[] | "- " + .' "$agent_file" 2>/dev/null)

---
*Source: .ai-pack/shared/agents/$agent_name.json*
EOF
            fi
        done
        print_success "Converted agents to Windsurf format"
    fi
    
    # Create Windsurf settings
    local settings_file="$windsurf_dir/settings.json"
    backup_file "$settings_file"
    
    cat > "$settings_file" << 'EOF'
{
  "aiPack": {
    "enabled": true,
    "agentsDirectory": ".ai-pack/shared/agents",
    "contextDirectory": ".ai-pack/shared/context",
    "instructionsFile": ".ai-pack/shared/instructions.md",
    "ignorePatterns": ".ai-pack/shared/ignore-patterns.txt",
    "defaultAgent": "code-reviewer"
  },
  "windsurf.ai.useProjectContext": true,
  "windsurf.ai.followProjectRules": true,
  "windsurf.ai.multiAgentMode": true
}
EOF
    
    print_success "Created Windsurf settings.json"
    print_success "Windsurf setup completed!"
}

# ============================================================================
# JetBrains IDEs Setup
# ============================================================================

setup_jetbrains() {
    print_header "Setting up JetBrains IDEs Integration"
    
    local idea_dir="$PROJECT_ROOT/.idea"
    ensure_dir "$idea_dir"
    
    # Create ai-pack.xml
    local aipack_file="$idea_dir/ai-pack.xml"
    backup_file "$aipack_file"
    
    cat > "$aipack_file" << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  <component name="AiPackSettings">
    <option name="enabled" value="true" />
    <option name="agentsPath" value=".ai-pack/shared/agents" />
    <option name="contextPath" value=".ai-pack/shared/context" />
    <option name="instructionsFile" value=".ai-pack/shared/instructions.md" />
    <option name="ignorePatterns" value=".ai-pack/shared/ignore-patterns.txt" />
    <option name="defaultAgent" value="code-reviewer" />
    <option name="enabledAgents">
      <list>
        <option value="backend-feature-agent" />
        <option value="qa-staging-endpoint-agent" />
        <option value="test-writer-agent" />
        <option value="security-auditor-agent" />
        <option value="tech-document-writer-agent" />
      </list>
    </option>
  </component>
</project>
EOF
    
    print_success "Created JetBrains ai-pack.xml"
    
    # Create AI Assistant prompts
    local prompts_dir="$idea_dir/aiAssistant"
    ensure_dir "$prompts_dir"
    
    cat > "$prompts_dir/customPrompts.xml" << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<customPrompts>
  <prompt name="Create Staging Endpoint" group="AI Pack">
    <description>Create a QA automation endpoint in StageController</description>
    <text>
Create a new staging endpoint for QA automation in StageController:
- Endpoint: $SELECTION$
- Follow the pattern in existing StageController methods
- Return JSON with status, data, timestamp format
- No authentication required
- No database access
    </text>
  </prompt>
  <prompt name="Create DDD Entity" group="AI Pack">
    <description>Create a DDD entity with value objects</description>
    <text>
Create a new DDD entity following the project patterns:
- Entity name: $SELECTION$
- Include Value Objects for complex fields
- Create repository interface
- Add Doctrine XML mapping
    </text>
  </prompt>
</customPrompts>
EOF
    
    print_success "Created JetBrains AI prompts"
    print_success "JetBrains setup completed!"
}

# ============================================================================
# Git Hooks Setup
# ============================================================================

setup_git_hooks() {
    print_header "Setting up Git Hooks"
    
    local git_hooks_dir="$PROJECT_ROOT/.git/hooks"
    
    if [ ! -d "$PROJECT_ROOT/.git" ]; then
        print_warning "Not a git repository. Skipping git hooks setup."
        return
    fi
    
    ensure_dir "$git_hooks_dir"
    
    # Create pre-commit hook
    local pre_commit="$git_hooks_dir/pre-commit"
    backup_file "$pre_commit"
    
    cat > "$pre_commit" << 'EOF'
#!/bin/bash

# AI Pack Pre-commit Hook
# Runs code quality checks before commit

echo "Running pre-commit checks..."

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "Not in project root, skipping checks"
    exit 0
fi

# Run PHP-CS-Fixer check (if available)
if docker exec blog-php test -f vendor/bin/pint 2>/dev/null; then
    echo "Checking code style..."
    docker exec blog-php vendor/bin/pint --test
    if [ $? -ne 0 ]; then
        echo "Code style issues found. Run 'docker exec blog-php vendor/bin/pint' to fix."
        exit 1
    fi
fi

# Run tests
echo "Running tests..."
docker exec blog-php vendor/bin/pest --no-coverage 2>/dev/null
if [ $? -ne 0 ]; then
    echo "Tests failed. Please fix before committing."
    exit 1
fi

echo "All pre-commit checks passed!"
exit 0
EOF
    
    chmod +x "$pre_commit"
    print_success "Created pre-commit hook"
    
    # Create commit-msg hook
    local commit_msg="$git_hooks_dir/commit-msg"
    backup_file "$commit_msg"
    
    cat > "$commit_msg" << 'EOF'
#!/bin/bash

# AI Pack Commit Message Hook
# Validates commit message format

commit_msg=$(cat "$1")

# Check for conventional commit format
if ! echo "$commit_msg" | grep -qE "^(feat|fix|docs|style|refactor|test|chore)(\(.+\))?: .{1,}"; then
    echo "Error: Commit message does not follow conventional commit format."
    echo ""
    echo "Format: type(scope): description"
    echo "Types: feat, fix, docs, style, refactor, test, chore"
    echo ""
    echo "Examples:"
    echo "  feat(api): add user registration endpoint"
    echo "  fix(auth): resolve token refresh issue"
    echo "  docs: update API documentation"
    echo ""
    exit 1
fi

exit 0
EOF
    
    chmod +x "$commit_msg"
    print_success "Created commit-msg hook"
    print_success "Git hooks setup completed!"
}

# ============================================================================
# Verify Setup
# ============================================================================

verify_setup() {
    print_header "Verifying Setup"
    
    local errors=0
    
    # Check if .ai-pack directory exists
    if [ -d "$AI_PACK_DIR" ]; then
        print_success ".ai-pack directory exists"
    else
        print_error ".ai-pack directory not found"
        ((errors++))
    fi
    
    # Check required files
    local required_files=(
        "$AI_PACK_DIR/shared/instructions.md"
        "$AI_PACK_DIR/shared/AGENTS.md"
        "$AI_PACK_DIR/shared/ignore-patterns.txt"
    )
    
    for file in "${required_files[@]}"; do
        if [ -f "$file" ]; then
            print_success "Found: $(basename "$file")"
        else
            print_error "Missing: $(basename "$file")"
            ((errors++))
        fi
    done
    
    # Check agents directory
    if [ -d "$AI_PACK_DIR/shared/agents" ]; then
        local agent_count=$(find "$AI_PACK_DIR/shared/agents" -name "*.json" | wc -l | tr -d ' ')
        print_success "Found $agent_count agent(s)"
    else
        print_error "Agents directory not found"
        ((errors++))
    fi
    
    # Check commands directory
    if [ -d "$AI_PACK_DIR/shared/commands" ]; then
        local cmd_count=$(find "$AI_PACK_DIR/shared/commands" -name "*.md" | wc -l | tr -d ' ')
        print_success "Found $cmd_count command(s)"
    else
        print_warning "Commands directory not found"
    fi
    
    # Check generated tool configurations
    local tools=(
        ".github/copilot-instructions.md:GitHub Copilot"
        ".claude/settings.json:Claude"
        ".cursorrules:Cursor"
        ".windsurfrules:Windsurf"
    )
    
    echo ""
    print_info "Tool configurations:"
    for tool_check in "${tools[@]}"; do
        local file="${tool_check%%:*}"
        local name="${tool_check##*:}"
        if [ -f "$PROJECT_ROOT/$file" ]; then
            print_success "$name configured ($file)"
        else
            print_warning "$name not configured ($file)"
        fi
    done
    
    if [ $errors -eq 0 ]; then
        print_success "\nSetup verification passed! ✨"
    else
        print_error "\nSetup verification found $errors error(s)"
        return 1
    fi
}

# ============================================================================
# Main Setup Function
# ============================================================================

show_usage() {
    cat << EOF
AI Pack Setup Script

Usage: $0 [options] [tool-name]

Options:
  -h, --help          Show this help message
  -v, --verify        Verify setup only
  --git-hooks         Setup git hooks only

Tool Names:
  vscode              Setup VS Code
  cursor              Setup Cursor (includes .cursorrules)
  windsurf            Setup Windsurf
  jetbrains           Setup JetBrains IDEs (PhpStorm, etc.)
  claude              Setup Claude Code (.claude/)
  github-copilot      Setup GitHub Copilot (.github/)
  all                 Setup all supported tools

Examples:
  $0 all              # Setup all tools (recommended)
  $0 cursor           # Setup Cursor only
  $0 github-copilot   # Setup GitHub Copilot only
  $0 --verify         # Verify setup

Supported AI Tools:
  ┌─────────────────────────────────────────────────────────┐
  │ Tool              │ Config Location                     │
  ├─────────────────────────────────────────────────────────┤
  │ VS Code           │ .vscode/                            │
  │ Cursor            │ .cursor/, .cursorrules              │
  │ Windsurf          │ .windsurf/, .windsurfrules          │
  │ JetBrains         │ .idea/                              │
  │ Claude            │ .claude/                            │
  │ GitHub Copilot    │ .github/copilot-instructions.md     │
  │                   │ .github/agents/                     │
  └─────────────────────────────────────────────────────────┘

EOF
}

main() {
    print_header "AI Pack Setup"
    
    # Parse arguments
    case "${1:-}" in
        -h|--help)
            show_usage
            exit 0
            ;;
        -v|--verify)
            verify_setup
            exit $?
            ;;
        --git-hooks)
            setup_git_hooks
            exit 0
            ;;
        vscode)
            setup_vscode
            setup_git_hooks
            ;;
        cursor)
            setup_cursor
            setup_git_hooks
            ;;
        windsurf)
            setup_windsurf
            setup_git_hooks
            ;;
        jetbrains)
            setup_jetbrains
            setup_git_hooks
            ;;
        claude)
            setup_claude
            setup_git_hooks
            ;;
        github-copilot)
            setup_github_copilot
            setup_git_hooks
            ;;
        all)
            setup_vscode
            setup_cursor
            setup_windsurf
            setup_jetbrains
            setup_claude
            setup_github_copilot
            setup_git_hooks
            ;;
        "")
            print_error "No tool specified"
            show_usage
            exit 1
            ;;
        *)
            print_error "Unknown tool: $1"
            show_usage
            exit 1
            ;;
    esac
    
    # Verify setup
    verify_setup
    
    # Show next steps
    print_header "Next Steps"
    echo "1. Restart your IDE to load new settings"
    echo "2. Review the generated configuration files"
    echo "3. Customize settings as needed"
    echo "4. Start using AI agents in your workflow!"
    echo ""
    echo "Generated configurations:"
    echo "  - GitHub Copilot: .github/copilot-instructions.md, .github/agents/"
    echo "  - Claude: .claude/agents/, .claude/commands/"
    echo "  - Cursor: .cursorrules, .cursor/"
    echo "  - Windsurf: .windsurfrules, .windsurf/"
    echo ""
    print_success "Setup completed successfully! 🎉"
}

# Run main function
main "$@"
