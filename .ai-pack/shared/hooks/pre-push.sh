#!/bin/bash
# Pre-push hook for AI-assisted development
# Runs comprehensive checks before pushing code to remote

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo -e "${BLUE}🚀 Running pre-push checks...${NC}"
echo ""

# Get current branch
CURRENT_BRANCH=$(git symbolic-ref --short HEAD 2>/dev/null || echo "detached")

# Check if pushing to protected branches
PROTECTED_BRANCHES=("main" "master" "production" "staging")

for branch in "${PROTECTED_BRANCHES[@]}"; do
  if [ "$CURRENT_BRANCH" = "$branch" ]; then
    echo -e "${RED}⚠️  WARNING: You are about to push to $branch branch!${NC}"
    echo "This is a protected branch. Are you sure? (y/N)"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
      echo -e "${YELLOW}Push cancelled${NC}"
      exit 1
    fi
  fi
done

# 1. Check for uncommitted changes
echo "Checking for uncommitted changes..."
if ! git diff-index --quiet HEAD --; then
  echo -e "${RED}✗ You have uncommitted changes${NC}"
  echo "Commit or stash your changes before pushing"
  exit 1
fi
echo -e "${GREEN}✓ No uncommitted changes${NC}"

# 2. Run tests
if [ -f "package.json" ] && grep -q "\"test\"" package.json; then
  echo ""
  echo "Running tests..."

  if npm test 2>&1 | tee /tmp/test-output.log; then
    echo -e "${GREEN}✓ All tests passed${NC}"
  else
    echo -e "${RED}✗ Tests failed${NC}"
    echo "Fix the failing tests before pushing"
    exit 1
  fi
fi

# 3. Check test coverage
if [ -f "package.json" ] && grep -q "test:coverage" package.json; then
  echo ""
  echo "Checking test coverage..."

  npm run test:coverage --silent 2>&1 | tee /tmp/coverage-output.log || true

  # Parse coverage (if using Jest)
  COVERAGE=$(grep -oP "All files\s+\|\s+\K[0-9.]+" /tmp/coverage-output.log | head -1 || echo "0")
  THRESHOLD=80

  if (( $(echo "$COVERAGE < $THRESHOLD" | bc -l) )); then
    echo -e "${YELLOW}⚠️  Warning: Code coverage is ${COVERAGE}% (threshold: ${THRESHOLD}%)${NC}"
    echo "Consider adding more tests"
  else
    echo -e "${GREEN}✓ Code coverage: ${COVERAGE}%${NC}"
  fi
fi

# 4. Run linter
if [ -f "package.json" ] && grep -q "\"lint\"" package.json; then
  echo ""
  echo "Running linter..."

  if npm run lint --silent; then
    echo -e "${GREEN}✓ Linting passed${NC}"
  else
    echo -e "${RED}✗ Linting failed${NC}"
    echo "Fix linting errors before pushing"
    echo "Tip: Run 'npm run lint -- --fix' to auto-fix some issues"
    exit 1
  fi
fi

# 5. TypeScript type checking
if [ -f "tsconfig.json" ]; then
  echo ""
  echo "Running TypeScript type check..."

  if npm run type-check --silent 2>/dev/null || npx tsc --noEmit; then
    echo -e "${GREEN}✓ Type checking passed${NC}"
  else
    echo -e "${RED}✗ TypeScript errors found${NC}"
    exit 1
  fi
fi

# 6. Check build
if [ -f "package.json" ] && grep -q "\"build\"" package.json; then
  echo ""
  echo "Testing production build..."

  if npm run build --silent; then
    echo -e "${GREEN}✓ Build successful${NC}"
    # Clean up build artifacts
    rm -rf dist/ build/ 2>/dev/null || true
  else
    echo -e "${RED}✗ Build failed${NC}"
    exit 1
  fi
fi

# 7. Security audit
if [ -f "package.json" ]; then
  echo ""
  echo "Running security audit..."

  # Run audit and check for high/critical vulnerabilities
  AUDIT_OUTPUT=$(npm audit --json 2>/dev/null || echo '{"error": true}')

  if command -v jq &> /dev/null; then
    HIGH_VULNS=$(echo "$AUDIT_OUTPUT" | jq '.metadata.vulnerabilities.high // 0')
    CRITICAL_VULNS=$(echo "$AUDIT_OUTPUT" | jq '.metadata.vulnerabilities.critical // 0')

    if [ "$HIGH_VULNS" -gt 0 ] || [ "$CRITICAL_VULNS" -gt 0 ]; then
      echo -e "${RED}✗ Found $HIGH_VULNS high and $CRITICAL_VULNS critical vulnerabilities${NC}"
      echo "Run 'npm audit fix' to resolve"
      exit 1
    else
      echo -e "${GREEN}✓ No high or critical vulnerabilities found${NC}"
    fi
  else
    echo -e "${YELLOW}⚠️  jq not installed, skipping detailed vulnerability check${NC}"
  fi
fi

# 8. Check for large files
echo ""
echo "Checking for large files..."
LARGE_FILES=$(git diff --cached --name-only | xargs ls -l 2>/dev/null | awk '$5 > 5242880 {print $9, "(" $5/1048576 " MB)"}' || true)

if [ -n "$LARGE_FILES" ]; then
  echo -e "${RED}✗ Large files detected (>5MB):${NC}"
  echo "$LARGE_FILES"
  echo "Consider using Git LFS for large files"
  exit 1
fi
echo -e "${GREEN}✓ No large files${NC}"

# 9. Check commit messages
echo ""
echo "Checking commit messages..."
BAD_COMMITS=$(git log @{u}.. --pretty=format:"%s" 2>/dev/null | grep -E "^(wip|WIP|temp|TEMP|fixup|squash)" || true)

if [ -n "$BAD_COMMITS" ]; then
  echo -e "${YELLOW}⚠️  Warning: Found temporary commit messages:${NC}"
  echo "$BAD_COMMITS"
  echo ""
  echo "Consider squashing or rewording these commits"
  echo "Continue anyway? (y/N)"
  read -r response
  if [[ ! "$response" =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Push cancelled${NC}"
    exit 1
  fi
fi

# 10. AI-specific checks
echo ""
echo "Running AI-specific checks..."

# Check for AI review requirements
if [ -f ".ai-pack/config/review-policy.json" ]; then
  if command -v jq &> /dev/null; then
    REQUIRES_AI_REVIEW=$(jq -r '.requireAIReview' .ai-pack/config/review-policy.json 2>/dev/null || echo "false")

    if [ "$REQUIRES_AI_REVIEW" = "true" ]; then
      # Check if commits have AI review marker
      UNREVIEWED=$(git log @{u}.. --pretty=format:"%s %b" 2>/dev/null | grep -L "@ai-reviewed" || true)

      if [ -n "$UNREVIEWED" ]; then
        echo -e "${YELLOW}⚠️  Warning: Some commits haven't been AI-reviewed${NC}"
        echo "Run '/review-code' to get AI review"
      fi
    fi
  fi
fi

# Check for incomplete AI tasks
INCOMPLETE_TASKS=$(git diff @{u}.. | grep -c "@ai-task:incomplete" || true)
if [ "$INCOMPLETE_TASKS" -gt 0 ]; then
  echo -e "${RED}✗ Found $INCOMPLETE_TASKS incomplete AI tasks${NC}"
  echo "Complete or remove @ai-task:incomplete markers before pushing"
  exit 1
fi

echo -e "${GREEN}✓ AI-specific checks passed${NC}"

# 11. Final confirmation for important branches
if [[ " ${PROTECTED_BRANCHES[@]} " =~ " ${CURRENT_BRANCH} " ]]; then
  echo ""
  echo -e "${YELLOW}═══════════════════════════════════════${NC}"
  echo -e "${YELLOW}FINAL CONFIRMATION${NC}"
  echo -e "${YELLOW}═══════════════════════════════════════${NC}"
  echo -e "${RED}You are about to push to: $CURRENT_BRANCH${NC}"
  echo ""
  echo "Summary of changes:"
  git log @{u}.. --oneline --color
  echo ""
  echo "Are you absolutely sure? Type 'yes' to continue:"
  read -r final_confirm

  if [ "$final_confirm" != "yes" ]; then
    echo -e "${YELLOW}Push cancelled${NC}"
    exit 1
  fi
fi

echo ""
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}✓ All pre-push checks passed!${NC}"
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo ""

# Clean up temp files
rm -f /tmp/test-output.log /tmp/coverage-output.log 2>/dev/null || true

exit 0
