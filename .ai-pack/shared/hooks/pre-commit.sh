#!/bin/bash
# Pre-commit hook for AI-assisted development
# This hook runs before each commit to ensure code quality

set -e

echo "🔍 Running pre-commit checks..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get list of staged files
STAGED_FILES=$(git diff --cached --name-only --diff-filter=ACM)

# Filter for relevant file types
JS_TS_FILES=$(echo "$STAGED_FILES" | grep -E '\.(js|jsx|ts|tsx)$' || true)
JSON_FILES=$(echo "$STAGED_FILES" | grep -E '\.json$' || true)

# Check if there are files to process
if [ -z "$STAGED_FILES" ]; then
  echo -e "${YELLOW}⚠️  No staged files to check${NC}"
  exit 0
fi

# Function to print success message
success() {
  echo -e "${GREEN}✓ $1${NC}"
}

# Function to print error message
error() {
  echo -e "${RED}✗ $1${NC}"
}

# Function to print warning message
warning() {
  echo -e "${YELLOW}⚠ $1${NC}"
}

# 1. Check for console.log statements
echo "Checking for console.log statements..."
if echo "$JS_TS_FILES" | xargs grep -n "console\.log" 2>/dev/null; then
  error "Found console.log statements. Please remove them before committing."
  echo "Tip: Use proper logging libraries (winston, pino) for production code."
  exit 1
fi
success "No console.log statements found"

# 2. Check for debugger statements
echo "Checking for debugger statements..."
if echo "$JS_TS_FILES" | xargs grep -n "debugger" 2>/dev/null; then
  error "Found debugger statements. Please remove them before committing."
  exit 1
fi
success "No debugger statements found"

# 3. Check for TODO/FIXME without ticket reference
echo "Checking for TODO/FIXME comments..."
if echo "$JS_TS_FILES" | xargs grep -nE "(TODO|FIXME)(?!.*#[0-9]+)" 2>/dev/null | grep -v "with ticket"; then
  warning "Found TODO/FIXME without ticket reference. Consider adding issue number."
  echo "Example: // TODO(#123): Fix this issue"
fi

# 4. Check for hardcoded secrets or API keys
echo "Checking for potential secrets..."
SECRET_PATTERNS=(
  "password\s*=\s*['\"]"
  "api[_-]?key\s*=\s*['\"]"
  "secret\s*=\s*['\"]"
  "token\s*=\s*['\"]"
  "bearer\s+[a-zA-Z0-9_-]{20,}"
  "mysql://.*:[^@]*@"
  "postgres://.*:[^@]*@"
)

for pattern in "${SECRET_PATTERNS[@]}"; do
  if echo "$STAGED_FILES" | xargs grep -nEi "$pattern" 2>/dev/null; then
    error "Potential secret or API key detected! Never commit secrets."
    echo "Use environment variables instead: process.env.API_KEY"
    exit 1
  fi
done
success "No secrets detected"

# 5. Check JSON files are valid
if [ -n "$JSON_FILES" ]; then
  echo "Validating JSON files..."
  for file in $JSON_FILES; do
    if ! jq empty "$file" 2>/dev/null; then
      error "Invalid JSON in $file"
      exit 1
    fi
  done
  success "All JSON files are valid"
fi

# 6. Run linter
if [ -n "$JS_TS_FILES" ]; then
  echo "Running ESLint..."
  if command -v npm &> /dev/null; then
    if npm run lint:staged --silent 2>/dev/null || npx eslint $JS_TS_FILES --max-warnings=0; then
      success "Linting passed"
    else
      error "Linting failed. Please fix the errors above."
      echo "Tip: Run 'npm run lint -- --fix' to auto-fix some issues"
      exit 1
    fi
  else
    warning "npm not found, skipping linting"
  fi
fi

# 7. Run Prettier
if [ -n "$JS_TS_FILES" ]; then
  echo "Running Prettier..."
  if command -v npm &> /dev/null; then
    if npm run format:staged --silent 2>/dev/null || npx prettier --check $JS_TS_FILES; then
      success "Code formatting is correct"
    else
      error "Code formatting issues found."
      echo "Tip: Run 'npm run format' to auto-format code"
      exit 1
    fi
  fi
fi

# 8. Run TypeScript type checking (if tsconfig exists)
if [ -f "tsconfig.json" ] && [ -n "$JS_TS_FILES" ]; then
  echo "Running TypeScript type check..."
  if command -v npm &> /dev/null; then
    if npm run type-check --silent 2>/dev/null || npx tsc --noEmit; then
      success "Type checking passed"
    else
      error "TypeScript type errors found"
      exit 1
    fi
  fi
fi

# 9. Check file sizes
echo "Checking file sizes..."
LARGE_FILES=$(echo "$STAGED_FILES" | xargs ls -l 2>/dev/null | awk '$5 > 1048576 {print $9, "(" $5 " bytes)"}')
if [ -n "$LARGE_FILES" ]; then
  warning "Large files detected (>1MB):"
  echo "$LARGE_FILES"
  echo "Consider using Git LFS for large files"
fi

# 10. AI-specific checks
echo "Running AI-specific checks..."

# Check if AI-generated code has been reviewed
if git diff --cached | grep -q "@ai-generated"; then
  warning "AI-generated code detected. Ensure it has been reviewed and tested."
fi

# Check for incomplete AI tasks
if echo "$JS_TS_FILES" | xargs grep -n "@ai-task:incomplete" 2>/dev/null; then
  error "Found incomplete AI tasks. Complete or remove @ai-task:incomplete markers."
  exit 1
fi

success "AI-specific checks passed"

echo ""
echo -e "${GREEN}✓ All pre-commit checks passed!${NC}"
echo ""

exit 0
