# Git Worktree Setup Skill

Set up isolated git worktrees for parallel development.

## Usage

```
/worktree-setup {branch-name} [--base=main]
```

## Examples

```
/worktree-setup feature/add-comments
/worktree-setup feature/add-tags --base=develop
/worktree-setup bugfix/fix-login
```

## What This Skill Does

### 1. Validate Prerequisites

```bash
# Check for clean working directory
git status --porcelain
# Should be empty

# Check current branch
git branch --show-current
```

### 2. Create Worktree

```bash
# Project structure
PROJECT_ROOT=$(pwd)
PROJECT_NAME=$(basename "$PROJECT_ROOT")
WORKTREES_DIR="$PROJECT_ROOT/../${PROJECT_NAME}-worktrees"
BRANCH_NAME="$1"
WORKTREE_NAME=$(echo "$BRANCH_NAME" | sed 's/\//-/g')

# Create worktrees directory
mkdir -p "$WORKTREES_DIR"

# Create worktree with new branch
git worktree add "$WORKTREES_DIR/$WORKTREE_NAME" -b "$BRANCH_NAME"
```

### 3. Setup Worktree Environment

```bash
cd "$WORKTREES_DIR/$WORKTREE_NAME"

# Copy environment files (not tracked by git)
cp "$PROJECT_ROOT/.env" .env 2>/dev/null || true
cp "$PROJECT_ROOT/.env.local" .env.local 2>/dev/null || true

# Install dependencies if needed
# (vendor is typically gitignored, so need to install)
docker exec blog-php composer install --working-dir=/app-worktrees/$WORKTREE_NAME
```

## Output Format

```markdown
## Worktree Created

### Details
- **Branch**: feature/add-comments
- **Path**: /path/to/project-worktrees/feature-add-comments
- **Base**: main (abc1234)

### Quick Navigation
```bash
cd /path/to/project-worktrees/feature-add-comments
```

### Docker Note
If using Docker, mount the worktrees directory in docker-compose.yml:
```yaml
volumes:
  - ../project-worktrees:/app-worktrees
```

### Cleanup Command
```bash
git worktree remove /path/to/project-worktrees/feature-add-comments
git branch -d feature/add-comments  # if not merged
```
```

## List Worktrees

```bash
git worktree list
```

## Remove Worktree

```bash
# Remove worktree
git worktree remove /path/to/worktree

# Prune stale worktrees
git worktree prune
```

## Docker Considerations

For this project (Dockerized Symfony), worktrees need special handling:

### Option 1: Mount Worktrees Directory

```yaml
# docker-compose.yml
services:
  php:
    volumes:
      - .:/app
      - ../blog-worktrees:/app-worktrees  # Add this line
```

### Option 2: Separate Docker Compose per Worktree

Each worktree can have its own docker-compose with different ports:
- Main: 8081
- Worktree 1: 8082
- Worktree 2: 8083

### Option 3: Shared Containers (Recommended for Demo)

Use the same containers, just switch directories:
```bash
docker exec blog-php bash -c "cd /app-worktrees/feature-comment && vendor/bin/pest"
```

## Demo Scenario

**Step 1**: Setup worktrees
```
/worktree-setup feature/add-comments
/worktree-setup feature/add-tags
/worktree-setup feature/add-categories
```

**Step 2**: Show isolation
```bash
# In main
git branch --show-current  # main

# In worktree
cd ../project-worktrees/feature-add-comments
git branch --show-current  # feature/add-comments
```

**Step 3**: Parallel development ready!
