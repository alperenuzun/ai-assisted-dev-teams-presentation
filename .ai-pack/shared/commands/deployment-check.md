---
description: Pre-deployment validation and readiness check
---

# Deployment Check

## Purpose

Comprehensive pre-deployment validation to ensure the application is ready for production deployment. Verifies tests, builds, security, performance, and configuration.

## Usage

```
/deployment-check [--environment production|staging]
```

## Deployment Readiness Checklist

### 1. ✅ All Tests Passing

#### Unit Tests

```bash
# Run unit tests
npm run test:unit

# With coverage
npm run test:unit -- --coverage

# Minimum coverage requirement: 80%
```

#### Integration Tests

```bash
# Run integration tests
npm run test:integration

# Verify database connectivity
npm run test:integration -- --testNamePattern="database"
```

#### E2E Tests

```bash
# Run E2E tests
npm run test:e2e

# Run critical user flows only
npm run test:e2e -- --testNamePattern="critical"
```

**Requirements:**

- ✅ All unit tests pass (100%)
- ✅ All integration tests pass (100%)
- ✅ Critical E2E tests pass (100%)
- ✅ Test coverage ≥ 80%
- ✅ No skipped or disabled tests in critical paths

### 2. ✅ Build Successful

```bash
# Clean build
npm run clean
npm run build

# Verify build output
ls -la dist/

# Check build size
du -sh dist/

# Verify no build warnings
npm run build 2>&1 | grep -i warning
```

**Requirements:**

- ✅ Build completes without errors
- ✅ No critical warnings
- ✅ Build artifacts generated correctly
- ✅ Bundle size within acceptable limits
- ✅ Source maps generated (if configured)

### 3. ✅ Environment Variables Validated

```bash
# Check required environment variables
node scripts/check-env.js production
```

```javascript
// scripts/check-env.js
const requiredEnvVars = {
  production: [
    "NODE_ENV",
    "DATABASE_URL",
    "REDIS_URL",
    "JWT_SECRET",
    "API_BASE_URL",
    "CORS_ORIGIN",
    "LOG_LEVEL",
    "SENTRY_DSN",
  ],
  staging: [
    "NODE_ENV",
    "DATABASE_URL",
    "REDIS_URL",
    "JWT_SECRET",
    "API_BASE_URL",
  ],
};

function checkEnvironment(env) {
  const required = requiredEnvVars[env] || requiredEnvVars.production;
  const missing = [];

  required.forEach((varName) => {
    if (!process.env[varName]) {
      missing.push(varName);
    }
  });

  if (missing.length > 0) {
    console.error("❌ Missing environment variables:");
    missing.forEach((v) => console.error(`  - ${v}`));
    process.exit(1);
  }

  console.log("✅ All required environment variables are set");
}

const environment = process.argv[2] || "production";
checkEnvironment(environment);
```

**Requirements:**

- ✅ All required environment variables set
- ✅ No default/placeholder values in production
- ✅ Secrets properly configured
- ✅ Database connection string valid
- ✅ External service URLs correct

### 4. ✅ Database Migrations Ready

```bash
# Check pending migrations
npm run migration:status

# Dry run migrations
npm run migration:dry-run

# Verify rollback capability
npm run migration:test-rollback
```

**Requirements:**

- ✅ All migrations tested
- ✅ Rollback scripts available
- ✅ No pending schema changes
- ✅ Migrations are idempotent
- ✅ Data migrations tested with production-like data

### 5. ✅ Security Checks Passed

```bash
# Run security scan
npm run security:scan

# Check dependencies
npm audit --audit-level=high

# Scan for secrets
npm run security:secrets
```

**Requirements:**

- ✅ No high/critical security vulnerabilities
- ✅ All dependencies up-to-date
- ✅ No hardcoded secrets
- ✅ Security headers configured
- ✅ HTTPS enforced
- ✅ Authentication/authorization tested
- ✅ Rate limiting configured

### 6. ✅ Performance Benchmarks Met

```bash
# Run performance tests
npm run test:performance

# Check API response times
npm run benchmark:api

# Memory leak detection
npm run test:memory
```

**Performance Targets:**

- ✅ API response time < 200ms (p95)
- ✅ Database query time < 100ms (p95)
- ✅ Memory usage stable (no leaks)
- ✅ CPU usage < 70% under load
- ✅ Concurrent users supported ≥ target

### 7. ✅ Code Quality Checks

```bash
# Linting
npm run lint

# Type checking
npm run type-check

# Code complexity
npm run complexity-check
```

**Requirements:**

- ✅ No linting errors
- ✅ No TypeScript errors
- ✅ Cyclomatic complexity < 10
- ✅ Code duplication < 3%
- ✅ Code review approved

### 8. ✅ Documentation Updated

**Requirements:**

- ✅ README updated
- ✅ API documentation current
- ✅ Changelog updated
- ✅ Deployment guide current
- ✅ Environment variables documented
- ✅ Architecture diagrams updated

### 9. ✅ Monitoring and Logging

**Requirements:**

- ✅ Error tracking configured (Sentry, etc.)
- ✅ Application logging configured
- ✅ Metrics collection enabled
- ✅ Health check endpoint working
- ✅ Alerts configured
- ✅ Dashboard set up

### 10. ✅ Backup and Rollback Plan

**Requirements:**

- ✅ Database backup verified
- ✅ Rollback procedure documented
- ✅ Previous version tagged
- ✅ Rollback tested
- ✅ Data migration rollback plan

## Automated Deployment Check Script

```bash
#!/bin/bash
# deployment-check.sh - Comprehensive pre-deployment validation

set -e  # Exit on any error

ENVIRONMENT=${1:-production}
FAILED_CHECKS=0

echo "🚀 Deployment Readiness Check for $ENVIRONMENT"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

check_step() {
  local step_name=$1
  local command=$2

  echo -n "Checking: $step_name... "

  if eval "$command" > /dev/null 2>&1; then
    echo -e "${GREEN}✅ PASS${NC}"
    return 0
  else
    echo -e "${RED}❌ FAIL${NC}"
    FAILED_CHECKS=$((FAILED_CHECKS + 1))
    return 1
  fi
}

# 1. Tests
echo "📝 Running Tests..."
check_step "Unit Tests" "npm run test:unit -- --passWithNoTests"
check_step "Integration Tests" "npm run test:integration -- --passWithNoTests"
check_step "E2E Tests (Critical)" "npm run test:e2e:critical -- --passWithNoTests"
echo ""

# 2. Build
echo "🔨 Build Verification..."
check_step "Clean Build" "npm run build"
check_step "Build Output Exists" "test -d dist"
echo ""

# 3. Environment Variables
echo "⚙️  Environment Configuration..."
check_step "Environment Variables" "node scripts/check-env.js $ENVIRONMENT"
echo ""

# 4. Database
echo "🗄️  Database Checks..."
check_step "Database Connection" "npm run db:ping"
check_step "Migrations Status" "npm run migration:status"
echo ""

# 5. Security
echo "🔒 Security Checks..."
check_step "Dependency Audit" "npm audit --audit-level=high"
check_step "Secrets Scan" "npm run security:secrets"
check_step "Security Headers" "npm run security:headers"
echo ""

# 6. Code Quality
echo "✨ Code Quality..."
check_step "Linting" "npm run lint"
check_step "Type Checking" "npm run type-check"
echo ""

# 7. Performance
echo "⚡ Performance Checks..."
check_step "API Benchmarks" "npm run benchmark:api"
echo ""

# 8. Documentation
echo "📚 Documentation..."
check_step "API Docs Valid" "npm run apidoc:validate"
check_step "Changelog Updated" "test -f CHANGELOG.md"
echo ""

# 9. Monitoring
echo "📊 Monitoring Setup..."
check_step "Health Endpoint" "curl -f http://localhost:3000/health || true"
check_step "Sentry Configured" "test -n \"\$SENTRY_DSN\""
echo ""

# Summary
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Deployment Check Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $FAILED_CHECKS -eq 0 ]; then
  echo -e "${GREEN}✅ All checks passed! Ready for deployment.${NC}"
  echo ""
  echo "Next steps:"
  echo "  1. Create deployment tag: git tag v\$(date +%Y%m%d-%H%M%S)"
  echo "  2. Push to deployment branch: git push origin main"
  echo "  3. Monitor deployment: npm run deploy:monitor"
  exit 0
else
  echo -e "${RED}❌ $FAILED_CHECKS check(s) failed!${NC}"
  echo ""
  echo "Please fix the issues above before deploying."
  exit 1
fi
```

## Health Check Endpoint

Ensure your application has a health check endpoint:

```typescript
// src/routes/health.ts
import { Router } from "express";

const router = Router();

router.get("/health", async (req, res) => {
  const health = {
    status: "ok",
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    environment: process.env.NODE_ENV,
    version: process.env.APP_VERSION || "unknown",
    checks: {
      database: await checkDatabase(),
      redis: await checkRedis(),
      externalApi: await checkExternalApi(),
    },
  };

  const allHealthy = Object.values(health.checks).every(
    (check) => check.status === "ok"
  );

  res.status(allHealthy ? 200 : 503).json(health);
});

async function checkDatabase() {
  try {
    await db.raw("SELECT 1");
    return { status: "ok", responseTime: "5ms" };
  } catch (error) {
    return { status: "error", message: error.message };
  }
}

async function checkRedis() {
  try {
    await redis.ping();
    return { status: "ok" };
  } catch (error) {
    return { status: "error", message: error.message };
  }
}

async function checkExternalApi() {
  try {
    const response = await fetch("https://api.example.com/health");
    return { status: response.ok ? "ok" : "degraded" };
  } catch (error) {
    return { status: "error", message: error.message };
  }
}

export default router;
```

## CI/CD Integration

```yaml
# .github/workflows/deployment-check.yml
name: Deployment Check

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  deployment-check:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:14
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

      redis:
        image: redis:7
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: "18"
          cache: "npm"

      - name: Install dependencies
        run: npm ci

      - name: Run deployment check
        env:
          NODE_ENV: production
          DATABASE_URL: postgresql://postgres:postgres@localhost:5432/test
          REDIS_URL: redis://localhost:6379
          JWT_SECRET: test-secret-key
        run: ./scripts/deployment-check.sh production

      - name: Upload test results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: test-results
          path: |
            coverage/
            test-results/

      - name: Notify on failure
        if: failure()
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: "Deployment check failed!"
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

## Rollback Procedure

Document your rollback procedure:

````markdown
## Rollback Procedure

### Quick Rollback (< 5 minutes)

1. Revert to previous deployment:
   ```bash
   kubectl rollout undo deployment/api-server
   ```
````

2. Verify rollback:

   ```bash
   kubectl rollout status deployment/api-server
   ```

3. Check health:
   ```bash
   curl https://api.example.com/health
   ```

### Database Rollback

1. Stop application
2. Run rollback migration:
   ```bash
   npm run migration:rollback
   ```
3. Verify data integrity
4. Restart application

### Full Rollback (> 5 minutes)

1. Tag current state
2. Checkout previous version
3. Run deployment check
4. Deploy previous version
5. Verify functionality
6. Monitor for 30 minutes

```

## Output Example

```

🚀 Deployment Readiness Check for production
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📝 Running Tests...
Checking: Unit Tests... ✅ PASS
Checking: Integration Tests... ✅ PASS
Checking: E2E Tests (Critical)... ✅ PASS

🔨 Build Verification...
Checking: Clean Build... ✅ PASS
Checking: Build Output Exists... ✅ PASS

⚙️ Environment Configuration...
Checking: Environment Variables... ✅ PASS

🗄️ Database Checks...
Checking: Database Connection... ✅ PASS
Checking: Migrations Status... ✅ PASS

🔒 Security Checks...
Checking: Dependency Audit... ✅ PASS
Checking: Secrets Scan... ✅ PASS
Checking: Security Headers... ✅ PASS

✨ Code Quality...
Checking: Linting... ✅ PASS
Checking: Type Checking... ✅ PASS

⚡ Performance Checks...
Checking: API Benchmarks... ✅ PASS

📚 Documentation...
Checking: API Docs Valid... ✅ PASS
Checking: Changelog Updated... ✅ PASS

📊 Monitoring Setup...
Checking: Health Endpoint... ✅ PASS
Checking: Sentry Configured... ✅ PASS

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Deployment Check Summary
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ All checks passed! Ready for deployment.

Next steps:

1. Create deployment tag: git tag v20240115-143000
2. Push to deployment branch: git push origin main
3. Monitor deployment: npm run deploy:monitor

```

## Related Commands
- `/security-scan` - Comprehensive security check
- `/apidoc-check` - API documentation validation
- `/generate-tests` - Generate missing tests
- `/review-code` - Final code review

## Tools and Resources
- [Kubernetes Deployment](https://kubernetes.io/docs/concepts/workloads/controllers/deployment/)
- [Docker Health Checks](https://docs.docker.com/engine/reference/builder/#healthcheck)
- [GitHub Actions](https://docs.github.com/en/actions)
- [Sentry Monitoring](https://sentry.io/)
```
