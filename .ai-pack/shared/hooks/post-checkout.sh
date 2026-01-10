#!/bin/bash
# Post-checkout hook for AI-assisted development
# Runs after git checkout to update dependencies and notify about changes

set -e

# Get branch info
PREV_HEAD=$1
NEW_HEAD=$2
BRANCH_CHECKOUT=$3

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo -e "${BLUE}📦 Post-checkout tasks...${NC}"
echo ""

# Only run on branch checkout (not file checkout)
if [ "$BRANCH_CHECKOUT" = "1" ]; then

  # Get branch name
  BRANCH_NAME=$(git symbolic-ref --short HEAD 2>/dev/null || echo "detached")

  echo -e "${GREEN}Switched to branch: $BRANCH_NAME${NC}"
  echo ""

  # Check if package.json changed
  PACKAGE_JSON_CHANGED=$(git diff --name-only $PREV_HEAD $NEW_HEAD | grep "package.json" || true)

  if [ -n "$PACKAGE_JSON_CHANGED" ]; then
    echo -e "${YELLOW}⚠️  package.json has changed${NC}"

    # Auto-install dependencies if configured
    if [ -f ".ai-pack/config/auto-install.enabled" ]; then
      echo "Installing dependencies..."
      npm install
      echo -e "${GREEN}✓ Dependencies installed${NC}"
    else
      echo "Run 'npm install' to update dependencies"
    fi
    echo ""
  fi

  # Check if database migrations need to run
  MIGRATION_CHANGED=$(git diff --name-only $PREV_HEAD $NEW_HEAD | grep "migrations/" || true)

  if [ -n "$MIGRATION_CHANGED" ]; then
    echo -e "${YELLOW}⚠️  Database migrations have changed${NC}"
    echo "Run 'npm run migrate' to update your database"
    echo ""
  fi

  # Check if .env.example changed
  ENV_EXAMPLE_CHANGED=$(git diff --name-only $PREV_HEAD $NEW_HEAD | grep ".env.example" || true)

  if [ -n "$ENV_EXAMPLE_CHANGED" ]; then
    echo -e "${YELLOW}⚠️  .env.example has changed${NC}"
    echo "New environment variables may be required. Compare with your .env file:"
    echo ""

    if [ -f ".env" ]; then
      # Show new variables not in .env
      echo "New variables in .env.example:"
      comm -13 <(grep -o '^[A-Z_]*' .env 2>/dev/null | sort) <(grep -o '^[A-Z_]*' .env.example 2>/dev/null | sort)
      echo ""
    else
      echo "No .env file found. Copy .env.example to .env and configure it."
      echo ""
    fi
  fi

  # AI-specific branch notifications
  if [[ "$BRANCH_NAME" == feature/* ]]; then
    echo -e "${BLUE}💡 Feature branch detected${NC}"
    echo "You can use these workflows:"
    echo "  - /develop-feature: Complete feature development workflow"
    echo "  - /test-feature: Run comprehensive tests"
    echo ""
  elif [[ "$BRANCH_NAME" == bugfix/* ]] || [[ "$BRANCH_NAME" == fix/* ]]; then
    echo -e "${BLUE}🐛 Bugfix branch detected${NC}"
    echo "You can use the bug fix workflow:"
    echo "  - /fix-bug: Systematic bug fixing workflow"
    echo ""
  elif [[ "$BRANCH_NAME" == refactor/* ]]; then
    echo -e "${BLUE}♻️  Refactor branch detected${NC}"
    echo "You can use the refactoring workflow:"
    echo "  - /refactor: Safe refactoring workflow"
    echo ""
  fi

  # Show recent commits on this branch
  echo -e "${BLUE}Recent commits on $BRANCH_NAME:${NC}"
  git log --oneline --color -5
  echo ""

  # Show branch status
  echo -e "${BLUE}Branch status:${NC}"
  git status -sb
  echo ""

  # Check for AI assistant recommendations
  if [ -f ".ai-pack/shared/context/branch-context.json" ]; then
    # Read branch-specific context if exists
    CONTEXT=$(cat .ai-pack/shared/context/branch-context.json 2>/dev/null || echo "{}")

    # Parse and show recommendations (requires jq)
    if command -v jq &> /dev/null; then
      RECOMMENDATIONS=$(echo "$CONTEXT" | jq -r ".branches[\"$BRANCH_NAME\"].recommendations[]" 2>/dev/null || true)

      if [ -n "$RECOMMENDATIONS" ]; then
        echo -e "${YELLOW}AI Recommendations for this branch:${NC}"
        echo "$RECOMMENDATIONS"
        echo ""
      fi
    fi
  fi

fi

echo -e "${GREEN}✓ Post-checkout tasks completed${NC}"
echo ""

exit 0
