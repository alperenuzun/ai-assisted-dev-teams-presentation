---
description: Comprehensive security vulnerability scanning
---

# Security Scan

## Purpose

Performs comprehensive security scanning including dependency vulnerabilities, code security analysis, secrets detection, and OWASP Top 10 checks.

## Usage

```
/security-scan [--full] [--fix]
```

**Options:**

- `--full`: Run complete security audit (slower but thorough)
- `--fix`: Automatically fix vulnerabilities where possible

## Security Checks Performed

### 1. Dependency Vulnerability Scanning

#### npm audit

```bash
# Check for vulnerabilities
npm audit

# Show detailed report
npm audit --json

# Fix automatically (updates package.json)
npm audit fix

# Fix including breaking changes
npm audit fix --force
```

#### yarn audit

```bash
# Check for vulnerabilities
yarn audit

# Fix automatically
yarn audit fix
```

#### Snyk

```bash
# Install Snyk
npm install -g snyk

# Authenticate
snyk auth

# Test for vulnerabilities
snyk test

# Monitor project
snyk monitor

# Fix vulnerabilities
snyk fix
```

### 2. OWASP Dependency Check

```bash
# Using dependency-check
dependency-check --project "MyProject" --scan ./

# Generate HTML report
dependency-check --project "MyProject" --scan ./ --format HTML --out ./reports
```

### 3. Code Security Analysis

#### ESLint Security Plugin

```bash
# Install security plugin
npm install --save-dev eslint-plugin-security

# Run security linting
npx eslint . --ext .js,.ts --plugin security
```

#### Semgrep (Static Analysis)

```bash
# Install Semgrep
pip install semgrep

# Run security rules
semgrep --config=auto .

# Run OWASP Top 10 rules
semgrep --config "p/owasp-top-ten" .

# Run with specific rulesets
semgrep --config "p/security-audit" --config "p/secrets" .
```

### 4. Secrets Detection

#### GitGuardian

```bash
# Install ggshield
pip install ggshield

# Scan repository
ggshield secret scan repo .

# Scan commits
ggshield secret scan commit-range HEAD~10..HEAD
```

#### TruffleHog

```bash
# Install truffleHog
pip install truffleHog

# Scan repository
trufflehog git file://. --json

# Scan specific branch
trufflehog git file://. --branch main
```

#### git-secrets

```bash
# Install git-secrets
brew install git-secrets  # macOS
apt-get install git-secrets  # Linux

# Scan repository
git secrets --scan

# Scan history
git secrets --scan-history
```

### 5. Authentication & Authorization Review

Check for:

- ✅ Strong password requirements (min 8 chars, complexity)
- ✅ Password hashing with bcrypt/argon2 (not MD5/SHA1)
- ✅ Secure session management
- ✅ JWT token validation and expiration
- ✅ Multi-factor authentication support
- ✅ Account lockout after failed attempts
- ✅ Proper authorization checks on all endpoints
- ✅ Role-based access control (RBAC)

### 6. Input Validation & Injection Prevention

Check for:

- ✅ SQL injection prevention (parameterized queries)
- ✅ NoSQL injection prevention
- ✅ Command injection prevention
- ✅ XSS prevention (input sanitization, output encoding)
- ✅ CSRF protection
- ✅ Input validation on all user inputs
- ✅ Type checking and sanitization

### 7. API Security

Check for:

- ✅ Rate limiting implemented
- ✅ CORS properly configured
- ✅ API authentication required
- ✅ HTTPS enforced
- ✅ Security headers configured
- ✅ API versioning
- ✅ Proper error handling (no stack traces in production)

## Automated Security Scan Script

```bash
#!/bin/bash
# security-scan.sh - Comprehensive security scanning

echo "🔒 Starting Security Scan..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ISSUES_FOUND=0

# 1. Dependency Vulnerabilities
echo "📦 Scanning dependencies for vulnerabilities..."
if npm audit --audit-level=moderate; then
  echo -e "${GREEN}✅ No dependency vulnerabilities found${NC}"
else
  echo -e "${RED}❌ Dependency vulnerabilities detected${NC}"
  ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi
echo ""

# 2. Secrets Detection
echo "🔑 Scanning for secrets and credentials..."
if command -v ggshield &> /dev/null; then
  if ggshield secret scan repo .; then
    echo -e "${GREEN}✅ No secrets detected${NC}"
  else
    echo -e "${RED}❌ Secrets found in repository${NC}"
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
  fi
else
  echo -e "${YELLOW}⚠️  GitGuardian not installed, skipping secrets scan${NC}"
fi
echo ""

# 3. Code Security Analysis
echo "🔍 Running code security analysis..."
if command -v semgrep &> /dev/null; then
  if semgrep --config=auto --error .; then
    echo -e "${GREEN}✅ No code security issues found${NC}"
  else
    echo -e "${RED}❌ Code security issues detected${NC}"
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
  fi
else
  echo -e "${YELLOW}⚠️  Semgrep not installed, skipping code analysis${NC}"
fi
echo ""

# 4. ESLint Security
echo "🛡️  Running ESLint security checks..."
if npx eslint . --ext .js,.ts --plugin security --quiet; then
  echo -e "${GREEN}✅ No ESLint security issues${NC}"
else
  echo -e "${RED}❌ ESLint security issues found${NC}"
  ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi
echo ""

# 5. Check for common security misconfigurations
echo "⚙️  Checking for security misconfigurations..."

# Check for .env in git
if git ls-files | grep -q "^\.env$"; then
  echo -e "${RED}❌ .env file is tracked in git${NC}"
  ISSUES_FOUND=$((ISSUES_FOUND + 1))
else
  echo -e "${GREEN}✅ .env file not tracked${NC}"
fi

# Check for hardcoded secrets patterns
if grep -r -E "(password|secret|api_key|apikey)\s*=\s*['\"][^'\"]+['\"]" src/ --exclude-dir=node_modules 2>/dev/null; then
  echo -e "${RED}❌ Potential hardcoded secrets found${NC}"
  ISSUES_FOUND=$((ISSUES_FOUND + 1))
else
  echo -e "${GREEN}✅ No obvious hardcoded secrets${NC}"
fi

echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Security Scan Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ISSUES_FOUND -eq 0 ]; then
  echo -e "${GREEN}✅ All security checks passed!${NC}"
  exit 0
else
  echo -e "${RED}❌ Found $ISSUES_FOUND security issue(s)${NC}"
  echo ""
  echo "Please review and fix the issues above."
  exit 1
fi
```

## Security Headers Check

```javascript
// check-security-headers.js
const requiredHeaders = {
  "Strict-Transport-Security": "max-age=31536000; includeSubDomains",
  "X-Content-Type-Options": "nosniff",
  "X-Frame-Options": "DENY",
  "X-XSS-Protection": "1; mode=block",
  "Content-Security-Policy": "default-src 'self'",
  "Referrer-Policy": "strict-origin-when-cross-origin",
};

async function checkSecurityHeaders(url) {
  const response = await fetch(url);
  const headers = response.headers;

  console.log("🔒 Security Headers Check\n");

  let allPresent = true;

  for (const [header, expectedValue] of Object.entries(requiredHeaders)) {
    const actualValue = headers.get(header);

    if (actualValue) {
      console.log(`✅ ${header}: ${actualValue}`);
    } else {
      console.log(`❌ ${header}: MISSING`);
      allPresent = false;
    }
  }

  return allPresent;
}
```

## OWASP Top 10 Checklist

### A01: Broken Access Control

- [ ] All endpoints have authorization checks
- [ ] User roles and permissions properly enforced
- [ ] No direct object references without validation
- [ ] Horizontal privilege escalation prevented
- [ ] Vertical privilege escalation prevented

### A02: Cryptographic Failures

- [ ] Sensitive data encrypted at rest
- [ ] Sensitive data encrypted in transit (HTTPS)
- [ ] Strong encryption algorithms used (AES-256, RSA-2048+)
- [ ] No hardcoded encryption keys
- [ ] Proper key management

### A03: Injection

- [ ] Parameterized queries or ORM used
- [ ] No dynamic SQL with user input
- [ ] Input validation and sanitization
- [ ] NoSQL injection prevention
- [ ] Command injection prevention

### A04: Insecure Design

- [ ] Security requirements defined
- [ ] Threat modeling performed
- [ ] Secure design patterns applied
- [ ] Security controls in place

### A05: Security Misconfiguration

- [ ] Secure default configurations
- [ ] Unnecessary features disabled
- [ ] Security headers configured
- [ ] Error messages don't leak info
- [ ] Latest security patches applied

### A06: Vulnerable and Outdated Components

- [ ] All dependencies up-to-date
- [ ] No known vulnerable dependencies
- [ ] Dependency scanning in CI/CD
- [ ] Regular dependency updates

### A07: Identification and Authentication Failures

- [ ] Strong password policy
- [ ] Multi-factor authentication available
- [ ] Secure session management
- [ ] Brute force protection
- [ ] Account lockout mechanism

### A08: Software and Data Integrity Failures

- [ ] Digital signatures for critical data
- [ ] Secure CI/CD pipeline
- [ ] Dependency integrity verification
- [ ] Code signing

### A09: Security Logging and Monitoring Failures

- [ ] Security events logged
- [ ] Log integrity protected
- [ ] Real-time monitoring
- [ ] Alerting configured
- [ ] Logs don't contain sensitive data

### A10: Server-Side Request Forgery (SSRF)

- [ ] URL validation and sanitization
- [ ] Network segmentation
- [ ] Whitelist allowed destinations
- [ ] Disable unnecessary URL schemes

## CI/CD Integration

```yaml
# .github/workflows/security-scan.yml
name: Security Scan

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]
  schedule:
    - cron: "0 0 * * 0" # Weekly scan

jobs:
  security:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3
        with:
          fetch-depth: 0 # Full history for secret scanning

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: "18"

      - name: Install dependencies
        run: npm ci

      - name: Run npm audit
        run: npm audit --audit-level=moderate

      - name: Run Snyk
        uses: snyk/actions/node@master
        env:
          SNYK_TOKEN: ${{ secrets.SNYK_TOKEN }}

      - name: Run GitGuardian scan
        uses: GitGuardian/ggshield-action@v1
        env:
          GITHUB_PUSH_BEFORE_SHA: ${{ github.event.before }}
          GITHUB_PUSH_BASE_SHA: ${{ github.event.base }}
          GITHUB_DEFAULT_BRANCH: ${{ github.event.repository.default_branch }}
          GITGUARDIAN_API_KEY: ${{ secrets.GITGUARDIAN_API_KEY }}

      - name: Run Semgrep
        uses: returntocorp/semgrep-action@v1
        with:
          config: >-
            p/security-audit
            p/secrets
            p/owasp-top-ten
```

## Output Example

```
🔒 Starting Security Scan...

📦 Scanning dependencies for vulnerabilities...
✅ No dependency vulnerabilities found

🔑 Scanning for secrets and credentials...
✅ No secrets detected

🔍 Running code security analysis...
⚠️  2 potential issues found:
  - src/auth/login.ts:45 - Potential SQL injection
  - src/utils/exec.ts:12 - Command injection risk

🛡️  Running ESLint security checks...
✅ No ESLint security issues

⚙️  Checking for security misconfigurations...
✅ .env file not tracked
✅ No obvious hardcoded secrets

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Security Scan Summary
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠️  Found 1 security issue(s)

Please review and fix the issues above.
```

## Related Commands

- `/review-code` - Code quality review including security
- `/deployment-check` - Pre-deployment security validation
- `/apidoc-check` - API documentation and security review

## Tools and Resources

- [npm audit](https://docs.npmjs.com/cli/v8/commands/npm-audit)
- [Snyk](https://snyk.io/)
- [Semgrep](https://semgrep.dev/)
- [GitGuardian](https://www.gitguardian.com/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Dependency-Check](https://owasp.org/www-project-dependency-check/)
