#!/bin/bash

# AI Pack Setup Script
# This script sets up AI assistant integration for various IDEs and tools
# Usage: ./setup.sh [ide-name]
# Supported IDEs: vscode, cursor, windsurf, jetbrains, all

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
AI_PACK_DIR="$PROJECT_ROOT/.ai-pack"

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
    echo -e "${BLUE}ℹ${NC} $1"
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
    "devops-engineer"
  ],
  
  "ai-pack.agents.defaultAgent": "code-reviewer",
  "ai-pack.agents.autoSuggest": true,
  
  "ai-pack.codeGeneration.includeTests": true,
  "ai-pack.codeGeneration.includeDocumentation": true,
  "ai-pack.codeGeneration.followStandards": true,
  
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll": true,
    "source.organizeImports": true
  },
  
  "files.exclude": {
    "**/.git": true,
    "**/node_modules": true,
    "**/dist": true,
    "**/build": true,
    "**/.next": true,
    "**/coverage": true
  },
  
  "search.exclude": {
    "**/node_modules": true,
    "**/dist": true,
    "**/build": true,
    "**/.next": true,
    "**/coverage": true,
    "**/*.min.js": true,
    "**/*.map": true
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
    "dbaeumer.vscode-eslint",
    "esbenp.prettier-vscode",
    "ms-vscode.vscode-typescript-next",
    "bradlc.vscode-tailwindcss",
    "christian-kohler.path-intellisense",
    "eamodio.gitlens",
    "github.copilot",
    "github.copilot-chat",
    "usernamehw.errorlens",
    "streetsidesoftware.code-spell-checker"
  ]
}
EOF
    
    print_success "Created VS Code extensions.json"
    
    # Create tasks.json
    local tasks_file="$vscode_dir/tasks.json"
    backup_file "$tasks_file"
    
    cat > "$tasks_file" << 'EOF'
{
  "version": "2.0.0",
  "tasks": [
    {
      "label": "AI: Review Code",
      "type": "shell",
      "command": "ai-agent review ${file}",
      "problemMatcher": []
    },
    {
      "label": "AI: Generate Tests",
      "type": "shell",
      "command": "ai-agent qa 'Generate tests for ${file}'",
      "problemMatcher": []
    },
    {
      "label": "AI: Optimize Code",
      "type": "shell",
      "command": "ai-agent review 'Optimize ${file}'",
      "problemMatcher": []
    }
  ]
}
EOF
    
    print_success "Created VS Code tasks.json"
    print_success "VS Code setup completed!"
}

# ============================================================================
# Cursor Setup
# ============================================================================

setup_cursor() {
    print_header "Setting up Cursor Integration"
    
    local cursor_dir="$PROJECT_ROOT/.cursor"
    ensure_dir "$cursor_dir"
    
    # Create .cursorrules file
    local cursorrules_file="$PROJECT_ROOT/.cursorrules"
    backup_file "$cursorrules_file"
    
    cat > "$cursorrules_file" << 'EOF'
# Cursor AI Rules

## Project Context
- Read and follow instructions in `.ai-pack/shared/instructions.md`
- Use agents defined in `.ai-pack/shared/agents/`
- Follow coding standards in `.ai-pack/shared/context/coding-standards.md`
- Use templates from `.ai-pack/shared/templates/`
- Respect ignore patterns in `.ai-pack/shared/ignore-patterns.txt`

## Code Generation
- Always include TypeScript types
- Write tests for new functionality
- Add JSDoc comments for public APIs
- Follow existing code patterns
- Use project's design system and components

## Testing
- Minimum 80% code coverage
- Use Jest and React Testing Library
- Follow AAA pattern (Arrange, Act, Assert)
- Include edge cases and error scenarios

## Documentation
- Update README when adding features
- Add inline comments for complex logic
- Keep API documentation in sync
- Document breaking changes

## Security
- Never commit secrets or API keys
- Validate all user inputs
- Use parameterized queries
- Implement proper authentication

## Performance
- Optimize bundle size
- Use lazy loading where appropriate
- Implement proper caching
- Profile before optimizing
EOF
    
    print_success "Created .cursorrules file"
    
    # Create Cursor settings
    local settings_file="$cursor_dir/settings.json"
    backup_file "$settings_file"
    
    cat > "$settings_file" << 'EOF'
{
  "aiPack": {
    "enabled": true,
    "agentsDirectory": ".ai-pack/shared/agents",
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
# Windsurf Setup
# ============================================================================

setup_windsurf() {
    print_header "Setting up Windsurf Integration"
    
    local windsurf_dir="$PROJECT_ROOT/.windsurf"
    ensure_dir "$windsurf_dir"
    
    # Create .windsurfrules file
    local windsurfrules_file="$PROJECT_ROOT/.windsurfrules"
    backup_file "$windsurfrules_file"
    
    cat > "$windsurfrules_file" << 'EOF'
# Windsurf AI Rules

## AI Pack Integration
- Load context from `.ai-pack/shared/`
- Use specialized agents for different tasks
- Follow project instructions and standards
- Respect ignore patterns

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
- Avoid deep nesting
- Handle errors properly

## Collaboration
- Use appropriate specialist agents
- Review code with code-reviewer agent
- Generate tests with qa-tester agent
- Consult architect for design decisions
EOF
    
    print_success "Created .windsurfrules file"
    
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
        <option value="architect" />
        <option value="frontend-specialist" />
        <option value="backend-specialist" />
        <option value="code-reviewer" />
        <option value="qa-tester" />
        <option value="devops-engineer" />
      </list>
    </option>
  </component>
</project>
EOF
    
    print_success "Created JetBrains ai-pack.xml"
    
    # Create inspectionProfiles
    local inspections_dir="$idea_dir/inspectionProfiles"
    ensure_dir "$inspections_dir"
    
    local profile_file="$inspections_dir/Project_Default.xml"
    backup_file "$profile_file"
    
    cat > "$profile_file" << 'EOF'
<component name="InspectionProjectProfileManager">
  <profile version="1.0">
    <option name="myName" value="Project Default" />
    <inspection_tool class="Eslint" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="TsLint" enabled="true" level="WARNING" enabled_by_default="true" />
  </profile>
</component>
EOF
    
    print_success "Created JetBrains inspection profile"
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
    
    # Copy hooks from .ai-pack/shared/hooks/
    local source_hooks_dir="$AI_PACK_DIR/shared/hooks"
    
    if [ -d "$source_hooks_dir" ]; then
        for hook in "$source_hooks_dir"/*.sh; do
            if [ -f "$hook" ]; then
                local hook_name=$(basename "$hook" .sh)
                local dest_hook="$git_hooks_dir/$hook_name"
                
                backup_file "$dest_hook"
                cp "$hook" "$dest_hook"
                chmod +x "$dest_hook"
                
                print_success "Installed git hook: $hook_name"
            fi
        done
    else
        print_warning "Hooks directory not found: $source_hooks_dir"
    fi
    
    print_success "Git hooks setup completed!"
}

# ============================================================================
# NPM Scripts Setup
# ============================================================================

setup_npm_scripts() {
    print_header "Setting up NPM Scripts"
    
    local package_json="$PROJECT_ROOT/package.json"
    
    if [ ! -f "$package_json" ]; then
        print_warning "package.json not found. Skipping NPM scripts setup."
        return
    fi
    
    print_info "Add these scripts to your package.json:"
    
    cat << 'EOF'

"scripts": {
  "ai:review": "ai-agent review",
  "ai:test": "ai-agent qa 'Generate tests'",
  "ai:optimize": "ai-agent review 'Optimize code'",
  "ai:docs": "ai-agent 'Generate documentation'",
  "ai:refactor": "ai-agent architect 'Suggest refactoring'"
}
EOF
    
    print_info "You can manually add these to your package.json"
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
        local agent_count=$(find "$AI_PACK_DIR/shared/agents" -name "*.json" | wc -l)
        print_success "Found $agent_count agent(s)"
    else
        print_error "Agents directory not found"
        ((errors++))
    fi
    
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

Usage: $0 [options] [ide-name]

Options:
  -h, --help          Show this help message
  -v, --verify        Verify setup only
  --git-hooks         Setup git hooks only
  --npm-scripts       Show NPM scripts only

IDE Names:
  vscode              Setup VS Code
  cursor              Setup Cursor
  windsurf            Setup Windsurf
  jetbrains           Setup JetBrains IDEs
  all                 Setup all supported IDEs

Examples:
  $0 vscode           # Setup VS Code only
  $0 all              # Setup all IDEs
  $0 --verify         # Verify setup
  $0 --git-hooks      # Setup git hooks only

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
        --npm-scripts)
            setup_npm_scripts
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
        all)
            setup_vscode
            setup_cursor
            setup_windsurf
            setup_jetbrains
            setup_git_hooks
            ;;
        "")
            print_error "No IDE specified"
            show_usage
            exit 1
            ;;
        *)
            print_error "Unknown IDE: $1"
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
    print_success "Setup completed successfully! 🎉"
}

# Run main function
main "$@"
