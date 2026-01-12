# Reset Database Skill

Reset the database to a clean state with fresh schema and fixture data.

## Usage

```
/reset-database [--skip-fixtures] [--confirm]
```

## Options

- `--skip-fixtures`: Don't load fixture data
- `--confirm`: Skip confirmation prompt

## What This Skill Does

### Step 1: Drop Database
```bash
docker exec blog-php php bin/console doctrine:database:drop --force --if-exists
```

### Step 2: Create Database
```bash
docker exec blog-php php bin/console doctrine:database:create
```

### Step 3: Create Schema
```bash
docker exec blog-php php bin/console doctrine:schema:create
```

### Step 4: Load Fixtures (unless --skip-fixtures)
```bash
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
```

### Step 5: Verify
```bash
docker exec blog-php php bin/console doctrine:schema:validate
```

## Output Format

```markdown
## Database Reset Complete

### Actions Performed
1. ✅ Dropped existing database `blog`
2. ✅ Created new database `blog`
3. ✅ Created schema from Doctrine mappings
4. ✅ Loaded fixture data
5. ✅ Validated schema

### Database State
- **Database**: blog
- **Tables**: 2 (users, posts)
- **Fixture Data**:
  - Users: 2 (admin@blog.com, user@blog.com)
  - Posts: 3

### Test Credentials
| Email | Password | Role |
|-------|----------|------|
| admin@blog.com | password | ADMIN |
| user@blog.com | password | USER |

### Quick Test
```bash
# Login as admin
curl -X POST http://localhost:8081/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}'
```
```

## Safety Checks

Before execution:
1. Check if running in production environment → **ABORT**
2. Check for pending transactions → Warn
3. Verify Docker container is running → Error if not

## Error Handling

```markdown
## Database Reset Failed

### Error
Database container not running.

### Solution
```bash
docker-compose up -d
# Wait for PostgreSQL to be ready
docker-compose logs -f postgres
```

### Then retry
```
/reset-database
```
```

## Demo Scenario

**Presenter says**: "Let's reset the database to start fresh for the next demo..."

```
/reset-database
```

**What audience sees**:
1. Clean database state
2. Predictable fixture data
3. Ready for testing
4. Professional development workflow

**Use between demos**:
- After showing entity creation
- Before showing new feature
- When data gets messy
