#!/bin/bash
# Commit message hook for AI-assisted development
# Validates commit message format and quality

COMMIT_MSG_FILE=$1
COMMIT_MSG=$(cat "$COMMIT_MSG_FILE")

# Colors
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

# Conventional Commits types
VALID_TYPES=("feat" "fix" "docs" "style" "refactor" "test" "chore" "perf" "ci" "build" "revert")

echo ""
echo "Validating commit message..."

# Skip validation for merge commits
if grep -qE "^Merge " "$COMMIT_MSG_FILE"; then
  echo -e "${GREEN}✓ Merge commit detected, skipping validation${NC}"
  exit 0
fi

# Skip validation for revert commits
if grep -qE "^Revert " "$COMMIT_MSG_FILE"; then
  echo -e "${GREEN}✓ Revert commit detected, skipping validation${NC}"
  exit 0
fi

# Extract commit type and message
TYPE=$(echo "$COMMIT_MSG" | grep -oP "^[a-z]+(?=(\(|\:))" || echo "")
SCOPE=$(echo "$COMMIT_MSG" | grep -oP "(?<=\()[^)]+(?=\):)" || echo "")
SUBJECT=$(echo "$COMMIT_MSG" | head -1 | sed -E 's/^[a-z]+(\([^)]+\))?:\s*//')

# Validation 1: Check format (type(scope): subject)
if ! echo "$COMMIT_MSG" | grep -qE "^[a-z]+(\([a-z0-9\-]+\))?:\s*.+"; then
  echo -e "${RED}✗ Invalid commit message format${NC}"
  echo ""
  echo "Commit messages should follow the Conventional Commits format:"
  echo ""
  echo "  <type>(<scope>): <subject>"
  echo ""
  echo "  <body>"
  echo ""
  echo "  <footer>"
  echo ""
  echo "Examples:"
  echo "  feat(auth): add JWT authentication"
  echo "  fix(api): handle null response in user endpoint"
  echo "  docs: update API documentation"
  echo ""
  exit 1
fi

# Validation 2: Check if type is valid
VALID=false
for valid_type in "${VALID_TYPES[@]}"; do
  if [ "$TYPE" = "$valid_type" ]; then
    VALID=true
    break
  fi
done

if [ "$VALID" = false ]; then
  echo -e "${RED}✗ Invalid commit type: $TYPE${NC}"
  echo ""
  echo "Valid types are:"
  printf '  %s\n' "${VALID_TYPES[@]}"
  echo ""
  echo "Type descriptions:"
  echo "  feat:     New feature"
  echo "  fix:      Bug fix"
  echo "  docs:     Documentation changes"
  echo "  style:    Code style changes (formatting, etc.)"
  echo "  refactor: Code refactoring"
  echo "  test:     Adding or updating tests"
  echo "  chore:    Maintenance tasks"
  echo "  perf:     Performance improvements"
  echo "  ci:       CI/CD changes"
  echo "  build:    Build system changes"
  echo "  revert:   Revert previous commit"
  echo ""
  exit 1
fi

# Validation 3: Check subject length
SUBJECT_LENGTH=${#SUBJECT}
if [ $SUBJECT_LENGTH -lt 10 ]; then
  echo -e "${RED}✗ Commit subject too short (${SUBJECT_LENGTH} chars, minimum 10)${NC}"
  echo "Provide a more descriptive commit message"
  exit 1
fi

if [ $SUBJECT_LENGTH -gt 72 ]; then
  echo -e "${RED}✗ Commit subject too long (${SUBJECT_LENGTH} chars, maximum 72)${NC}"
  echo "Keep the first line under 72 characters"
  echo "Use the body for additional details"
  exit 1
fi

# Validation 4: Check subject starts with lowercase
if echo "$SUBJECT" | grep -qE "^[A-Z]"; then
  echo -e "${YELLOW}⚠️  Warning: Subject should start with lowercase${NC}"
  echo "Example: 'add' instead of 'Add'"
fi

# Validation 5: Check subject doesn't end with period
if echo "$SUBJECT" | grep -qE "\.$"; then
  echo -e "${RED}✗ Subject should not end with a period${NC}"
  exit 1
fi

# Validation 6: Check for imperative mood
NON_IMPERATIVE_WORDS=("added" "fixed" "updated" "changed" "removed" "deleted")
FIRST_WORD=$(echo "$SUBJECT" | awk '{print $1}')

for word in "${NON_IMPERATIVE_WORDS[@]}"; do
  if [ "${FIRST_WORD,,}" = "$word" ]; then
    echo -e "${YELLOW}⚠️  Warning: Use imperative mood in subject${NC}"
    echo "Use '$( echo "$word" | sed 's/ed$//' )' instead of '$word'"
    echo "Example: 'add feature' not 'added feature'"
  fi
done

# Validation 7: Check for WIP commits
if echo "$COMMIT_MSG" | grep -qiE "(wip|work in progress|temp|temporary|fixup)"; then
  echo -e "${YELLOW}⚠️  Warning: This appears to be a temporary commit${NC}"
  echo "Remember to squash or reword before pushing"
fi

# Validation 8: Check body line length
BODY=$(echo "$COMMIT_MSG" | tail -n +3)
if [ -n "$BODY" ]; then
  while IFS= read -r line; do
    if [ ${#line} -gt 100 ]; then
      echo -e "${YELLOW}⚠️  Warning: Body line too long (${#line} chars, recommended max 100)${NC}"
      break
    fi
  done <<< "$BODY"
fi

# Validation 9: Check for issue references in footer (for feat/fix)
if [ "$TYPE" = "feat" ] || [ "$TYPE" = "fix" ]; then
  if ! echo "$COMMIT_MSG" | grep -qiE "(closes?|fixes?|resolves?)\s+#[0-9]+"; then
    echo -e "${YELLOW}⚠️  Recommendation: Reference related issue${NC}"
    echo "Add to commit footer:"
    echo "  Closes #123"
    echo "  Fixes #456"
  fi
fi

# Validation 10: Check for breaking changes marker
if echo "$COMMIT_MSG" | grep -qE "BREAKING CHANGE|!:"; then
  echo -e "${YELLOW}⚠️  BREAKING CHANGE detected${NC}"
  echo "Make sure to:"
  echo "  1. Document the breaking change in commit body"
  echo "  2. Provide migration instructions"
  echo "  3. Bump major version on release"
fi

# AI-specific validations
echo ""
echo "Running AI-specific validations..."

# Check for AI-generated marker
if echo "$COMMIT_MSG" | grep -q "@ai-generated"; then
  echo -e "${YELLOW}ℹ️  AI-generated code detected${NC}"

  # Check if reviewed marker exists
  if ! echo "$COMMIT_MSG" | grep -q "@ai-reviewed"; then
    echo -e "${YELLOW}⚠️  Warning: AI-generated code not marked as reviewed${NC}"
    echo "Add '@ai-reviewed by <name>' to commit message after review"
  fi
fi

# Success
echo ""
echo -e "${GREEN}✓ Commit message validation passed${NC}"
echo ""

exit 0
