# Parallel Features Development

Develop multiple independent features simultaneously using Git worktrees and parallel agents.

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

### Agent Configuration

Each agent receives:
- **Working Directory**: Its own worktree path
- **Task**: Generate entity + endpoints
- **Persona**: Backend Developer Agent
- **Independence**: No file conflicts possible

### Launch Command (Conceptual)

```
Launch 3 agents IN PARALLEL:

Agent 1:
  - Worktree: feature-comment
  - Task: /generate-entity Comment Api --fields="content:text,postId:uuid,authorId:uuid"
          /generate-endpoint POST /api/posts/{postId}/comments Api --entity=Comment
          /generate-endpoint GET /api/posts/{postId}/comments Api --entity=Comment
          /run-quality-checks

Agent 2:
  - Worktree: feature-tag
  - Task: /generate-entity Tag Api --fields="name:string,slug:string,color:string"
          /generate-endpoint GET /api/tags Api --entity=Tag
          /generate-endpoint POST /api/tags Api --entity=Tag
          /run-quality-checks

Agent 3:
  - Worktree: feature-category
  - Task: /generate-entity Category Api --fields="name:string,slug:string,description:text"
          /generate-endpoint GET /api/categories Api --entity=Category
          /generate-endpoint POST /api/categories Api --entity=Category
          /run-quality-checks
```

### Parallel Execution Flow

```
Time ─────────────────────────────────────────────────────────────▶

Agent 1: [Setup] [Entity] [Endpoints] [Tests] [Commit] ✅
Agent 2: [Setup] [Entity] [Endpoints] [Tests] [Commit] ✅
Agent 3: [Setup] [Entity] [Endpoints] [Tests] [Commit] ✅

         └──────────── ALL RUNNING IN PARALLEL ────────────┘
```

---

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

### After All Agents Complete

1. **Return to main project**:
   ```bash
   cd $PROJECT_ROOT
   ```

2. **Merge each feature branch**:
   ```bash
   # Merge Comment feature
   git merge feature/add-comment-entity --no-ff -m "Merge feature/add-comment-entity"

   # Merge Tag feature
   git merge feature/add-tag-entity --no-ff -m "Merge feature/add-tag-entity"

   # Merge Category feature
   git merge feature/add-category-entity --no-ff -m "Merge feature/add-category-entity"
   ```

3. **Handle any schema conflicts**:
   ```bash
   # Regenerate schema with all entities
   docker exec blog-php php bin/console doctrine:schema:update --force
   ```

4. **Run final quality checks**:
   ```bash
   docker exec blog-php vendor/bin/pest
   docker exec blog-php php bin/console doctrine:schema:validate
   ```

5. **Cleanup worktrees**:
   ```bash
   git worktree remove "$WORKTREES_DIR/feature-comment"
   git worktree remove "$WORKTREES_DIR/feature-tag"
   git worktree remove "$WORKTREES_DIR/feature-category"
   rmdir "$WORKTREES_DIR"
   ```

---

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
