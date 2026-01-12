# Run Quality Checks Skill

Run all quality checks (tests, code style, static analysis, schema validation) and provide a comprehensive report.

## Usage

```
/run-quality-checks [--fix] [--verbose]
```

## Options

- `--fix`: Automatically fix code style issues
- `--verbose`: Show detailed output

## What This Skill Runs

### 1. Code Style Check (Pint)
```bash
docker exec blog-php vendor/bin/pint --test
```

### 2. Unit Tests (Pest)
```bash
docker exec blog-php vendor/bin/pest
```

### 3. Doctrine Schema Validation
```bash
docker exec blog-php php bin/console doctrine:schema:validate
```

### 4. Symfony Container Check
```bash
docker exec blog-php php bin/console lint:container
```

### 5. Twig Syntax Check (if templates exist)
```bash
docker exec blog-php php bin/console lint:twig templates/
```

### 6. YAML Syntax Check
```bash
docker exec blog-php php bin/console lint:yaml config/
```

## Output Format

```markdown
## Quality Check Report

### Summary
| Check | Status | Details |
|-------|--------|---------|
| Code Style | ✅ PASS | No issues found |
| Unit Tests | ✅ PASS | 15 tests, 45 assertions |
| Doctrine Schema | ✅ PASS | Mapping and schema in sync |
| Container | ✅ PASS | No errors |
| Twig | ✅ PASS | All templates valid |
| YAML | ✅ PASS | All config files valid |

### Overall Status: ✅ ALL CHECKS PASSED

---

### Detailed Results

#### Code Style (Pint)
```
✓ No code style issues found
```

#### Unit Tests (Pest)
```
   PASS  Tests\Unit\Domain\Post\ValueObject\PostTitleTest
   ✓ post title can be created from valid string
   ✓ post title throws exception for empty string
   ✓ post title throws exception for too short string

   Tests:    15 passed (45 assertions)
   Duration: 0.52s
```

#### Doctrine Schema
```
[OK] The mapping files are correct.
[OK] The database schema is in sync with the mapping files.
```
```

## Fix Mode Output

When `--fix` is used:

```markdown
## Quality Check Report (Fix Mode)

### Fixes Applied
- ✅ Fixed 3 code style issues in `src/Api/Domain/Post/Entity/Post.php`
- ✅ Fixed 1 code style issue in `src/Api/Infrastructure/Controller/PostController.php`

### Remaining Issues
- ❌ 2 unit tests failing (cannot auto-fix)
- ❌ Doctrine schema out of sync (run schema:update)

### Commands to Run Manually
```bash
# Fix schema issues
docker exec blog-php php bin/console doctrine:schema:update --force

# Re-run tests after fixing code
docker exec blog-php vendor/bin/pest
```
```

## Integration with CI/CD

This skill output can be used for:
- Pre-commit hooks
- PR checks
- Deployment gates

## Demo Scenario

**Presenter says**: "Before creating a PR, let's verify everything is in order..."

```
/run-quality-checks
```

**If issues found**:
```
/run-quality-checks --fix
```

**What audience sees**:
1. Automated quality gates
2. Clear pass/fail status
3. Actionable fix suggestions
4. Professional development workflow
