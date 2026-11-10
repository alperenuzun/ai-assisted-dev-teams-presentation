# Parallel Features Development

Develop multiple independent features simultaneously using Git worktrees and parallel agents.


> **Changelog (architecture review)**: this command used to be prose-only — it described
> worktrees and "parallel agents" but nothing actually spawned them, waited for them, or
> gated a merge on each worktree's own test results. Phase 2 and Phase 4 below now point
> to a real executable supervisor: `.claude/scripts/run-parallel-agents.sh`. It creates
> the worktrees, launches one headless `claude -p` session per feature in the background,
> waits for all of them, runs the test suite per worktree **before** merging anything, and
> aborts (not silently continues) on merge conflicts or failing tests — leaving the failed
> worktree intact for manual debugging instead of deleting evidence.

## Usage

```
/parallel-features "Feature1,Feature2,Feature3" [--base-branch=main]
```

## Example

```
/parallel-features "Comment,Tag,Category"
```

This will:
1. Create 3 git worktrees
2. Launch 3 parallel agents
3. Each agent implements one feature
4. Merge all branches when done

## Prerequisites

- Clean git status (no uncommitted changes)
- Main branch up to date
- Docker containers running

---

## PHASE 1: Setup Worktrees

### Create Worktrees for Each Feature

```bash
# Get project root
PROJECT_ROOT=$(pwd)
PROJECT_NAME=$(basename $PROJECT_ROOT)

# Create worktrees directory (sibling to main project)
WORKTREES_DIR="$PROJECT_ROOT/../${PROJECT_NAME}-worktrees"
mkdir -p "$WORKTREES_DIR"

# For each feature, create a worktree
# Feature 1: Comment
git worktree add "$WORKTREES_DIR/feature-comment" -b feature/add-comment-entity

# Feature 2: Tag
git worktree add "$WORKTREES_DIR/feature-tag" -b feature/add-tag-entity

# Feature 3: Category
git worktree add "$WORKTREES_DIR/feature-category" -b feature/add-category-entity
```

### Verify Worktrees

```bash
git worktree list
```

Expected output:
```
/path/to/project                   abc1234 [main]
/path/to/project-worktrees/feature-comment   def5678 [feature/add-comment-entity]
/path/to/project-worktrees/feature-tag       ghi9012 [feature/add-tag-entity]
/path/to/project-worktrees/feature-category  jkl3456 [feature/add-category-entity]
```

---

## PHASE 2: Launch Parallel Agents

Run the real supervisor instead of hand-waving "launch agents conceptually":

```bash
.claude/scripts/run-parallel-agents.sh "Comment,Tag,Category" main
```

What it actually does (see the script for full detail):

1. For each feature, starts a **headless Claude Code session** (`claude -p "..." --output-format json`)
   in the background, `cd`'d into that feature's own worktree, with a prompt that loads the
   Backend Developer Agent persona and scopes it to that worktree only.
2. Waits for every background process (`wait "$pid"`) and captures exit codes + full logs
   under `.claude/state/parallel-logs/`.
3. Does **not** assume success — a non-zero exit code marks that feature as failed and
   excludes it from the merge step below.

### Parallel Execution Flow

```
Time ─────────────────────────────────────────────────────────────▶

Agent 1: [Setup] [Entity] [Endpoints] [Tests] [Commit] ✅
Agent 2: [Setup] [Entity] [Endpoints] [Tests] [Commit] ✅
Agent 3: [Setup] [Entity] [Endpoints] [Tests] [Commit] ✅

         └──────────── ALL RUNNING IN PARALLEL (real background processes) ────────────┘
```

## PHASE 3: Agent Task Details

### Each Agent Executes (in their worktree):

1. **Navigate to worktree**:
   ```bash
   cd $WORKTREE_PATH
   ```

2. **Generate Entity**:
   - Create entity class
   - Create value objects
   - Create repository interface + implementation
   - Create Doctrine mapping
   - Register Doctrine types
   - Create Command/Query handlers

3. **Generate Endpoints**:
   - Create controller methods
   - Add messenger routing
   - Clear cache

4. **Quality Checks**:
   ```bash
   docker exec blog-php vendor/bin/pint
   docker exec blog-php vendor/bin/pest
   docker exec blog-php php bin/console doctrine:schema:validate
   ```

5. **Commit Changes**:
   ```bash
   git add .
   git commit -m "feat(api): add {Feature} entity and endpoints

   - Add {Feature} entity with value objects
   - Add {Feature}RepositoryInterface and Doctrine implementation
   - Add Create/List/Get endpoints
   - Add CQRS commands and queries

   🤖 Generated with Claude Code (Parallel Agent)"
   ```

---

## PHASE 4: Merge & Cleanup

The supervisor script also owns this phase (it's one script, not a separate manual step):

1. **Quality gate per worktree, before any merge**: runs `vendor/bin/pest` against each
   worktree's changes. A worktree that fails its own suite is **excluded from the merge
   candidates** — it is never merged silently.
2. **Sequential merge of passing features only**: `git merge --no-ff` one branch at a time.
   On a merge conflict, the script runs `git merge --abort` immediately and marks that
   feature as failed rather than leaving the main branch in a conflicted state.
3. **Worktrees are only removed for features that failed AND whose branch was abandoned**
   intentionally by a human — the script leaves failed worktrees in place
   (`{project}-worktrees/feature-*`) so you can `cd` in and debug with full history, instead
   of deleting the evidence:
   ```bash
   # once a failed worktree has been fixed or abandoned:
   git worktree remove "$WORKTREES_DIR/feature-<slug>"
   ```
4. After merging, regenerate schema/run full suite once more against the merged result:
   ```bash
   docker exec blog-php php bin/console doctrine:schema:update --force
   docker exec blog-php vendor/bin/pest
   docker exec blog-php php bin/console doctrine:schema:validate
   ```

## PHASE 5: Create Combined PR

### PR Description

```markdown
## Summary
Parallel development of 3 independent features using Claude Code agents.

## Features Added

### 1. Comment Entity
- `POST /api/posts/{postId}/comments` - Add comment to post
- `GET /api/posts/{postId}/comments` - List post comments

### 2. Tag Entity
- `GET /api/tags` - List all tags
- `POST /api/tags` - Create new tag

### 3. Category Entity
- `GET /api/categories` - List all categories
- `POST /api/categories` - Create new category

## Development Method
- 3 parallel Claude Code agents
- Each in isolated git worktree
- Zero conflicts, maximum efficiency

## Test Results
All features tested independently and after merge.

🤖 Generated with Claude Code (Parallel Agents)
```

---

## Output Format

```markdown
## Parallel Features Development Complete

### Execution Summary
| Agent | Feature | Worktree | Status | Duration |
|-------|---------|----------|--------|----------|
| 1 | Comment | feature-comment | ✅ Complete | 2m 34s |
| 2 | Tag | feature-tag | ✅ Complete | 2m 12s |
| 3 | Category | feature-category | ✅ Complete | 2m 45s |

**Total Time**: 2m 45s (parallel) vs ~8m (sequential)
**Time Saved**: ~65%

### Files Created
- **Comment**: 12 files (entity, VOs, repository, handlers, controller, mapping)
- **Tag**: 11 files
- **Category**: 12 files
- **Total**: 35 new files

### Merge Status
- ✅ feature/add-comment-entity → main
- ✅ feature/add-tag-entity → main
- ✅ feature/add-category-entity → main

### Quality Checks
- ✅ All tests passing (45 tests)
- ✅ Code style OK
- ✅ Schema valid

### New Endpoints Available
| Method | Path | Feature |
|--------|------|---------|
| POST | /api/posts/{postId}/comments | Comment |
| GET | /api/posts/{postId}/comments | Comment |
| GET | /api/tags | Tag |
| POST | /api/tags | Tag |
| GET | /api/categories | Category |
| POST | /api/categories | Category |

### Cleanup
- ✅ Worktrees removed
- ✅ Feature branches merged
```

---

## Error Handling

### If one agent fails:
- Other agents continue
- Failed agent's worktree preserved for debugging
- Report which agent failed and why

### If merge conflicts:
- Rare with independent features
- If occurs, show conflict files
- Suggest resolution steps

### If tests fail after merge:
- Run schema update
- Re-run tests
- If still failing, report which feature caused the issue
