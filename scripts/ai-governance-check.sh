#!/usr/bin/env bash
# Automated guardrail for rules that, until now, only existed as prose inside agent
# persona files (.claude/agents/*.md). Those files say things like "NEVER require
# authentication on staging endpoints" or "use XML Doctrine mappings, not annotations" —
# but nothing actually checked an agent complied. This script turns a handful of those
# rules into a CI-enforceable check. It intentionally uses plain grep (no PHP toolchain
# required) so it can run fast on every PR, including from an agent's own review phase
# (see Phase 3 of .claude/commands/jira-to-pr.md).
#
# Exit code 0 = all checks passed. Non-zero = at least one rule violated.

set -uo pipefail

ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

FAILED=0

fail() {
  echo "✗ $1"
  FAILED=1
}

pass() {
  echo "✓ $1"
}

echo "== AI governance check =="

# Rule 1 (qa-staging-endpoint-agent.md: "NEVER require authentication"):
# StageController must stay auth-free; it exists purely for QA mocks.
STAGE_CONTROLLER="src/Api/Infrastructure/Controller/StageController.php"
if [[ -f "$STAGE_CONTROLLER" ]]; then
  hits="$(grep -nE "#\[IsGranted|#\[Security\(|AuthorizationCheckerInterface" "$STAGE_CONTROLLER" 2>/dev/null || true)"
  if [[ -n "$hits" ]]; then
    fail "Rule 1: $STAGE_CONTROLLER appears to require authentication (staging endpoints must stay open for QA automation):"
    echo "$hits" | sed 's/^/    /'
  else
    pass "Rule 1: $STAGE_CONTROLLER has no auth checks"
  fi
else
  pass "Rule 1: $STAGE_CONTROLLER does not exist yet, nothing to check"
fi

# Rule 2 (backend-developer.md / CLAUDE.md: "Doctrine ORM with XML mappings, not
# annotations"): no #[ORM\...] attribute mappings should appear under src/.
hits="$(grep -rlnE '#\[ORM\\(Entity|Column|Id|ManyToOne|OneToMany|ManyToMany|GeneratedValue)' src/ 2>/dev/null || true)"
if [[ -n "$hits" ]]; then
  fail "Rule 2: found Doctrine attribute mappings under src/ — this project uses XML mappings (config/doctrine/*.orm.xml) instead:"
  echo "$hits" | sed 's/^/    /'
else
  pass "Rule 2: no Doctrine attribute mappings found under src/"
fi

# Rule 3 (backend-developer.md: "Keep domain pure — no framework dependencies in Domain
# layer"): Domain/ files must not import Symfony/Doctrine namespaces directly.
#
# Known, intentional exception: Symfony's own Security component requires the User entity
# to implement its marker interfaces (UserInterface, PasswordAuthenticatedUserInterface) --
# see src/Api/Domain/User/Entity/User.php's own docblock. Those are zero-logic marker
# interfaces, not a framework dependency in the harmful sense (no Symfony code executes
# inside Domain/ because of them), so they're allow-listed below. Anything else from
# Symfony\ or Doctrine\ is still flagged -- this is a deliberate, narrow exception, not a
# blanket pass for the whole Security namespace.
ALLOWED_DOMAIN_FRAMEWORK_IMPORTS='use Symfony\\Component\\Security\\Core\\User\\UserInterface;|use Symfony\\Component\\Security\\Core\\User\\PasswordAuthenticatedUserInterface;'
hits="$(grep -rnE "^use (Symfony\\\\|Doctrine\\\\)" src/*/Domain/ 2>/dev/null | grep -vE "$ALLOWED_DOMAIN_FRAMEWORK_IMPORTS" || true)"
if [[ -n "$hits" ]]; then
  fail "Rule 3: found Symfony/Doctrine imports inside a Domain/ layer (must stay framework-agnostic):"
  echo "$hits" | sed 's/^/    /'
else
  pass "Rule 3: Domain/ layers are framework-agnostic (modulo the allow-listed Security marker interfaces)"
fi

# Rule 4 (basic secret-leak guard): no obviously-committed private keys or tokens.
hits="$(grep -rlnE "BEGIN (RSA|EC|OPENSSH) PRIVATE KEY" --include="*.php" --include="*.yaml" --include="*.yml" . 2>/dev/null \
    | grep -v -E "^\./(vendor|config/jwt)/" || true)"
if [[ -n "$hits" ]]; then
  fail "Rule 4: possible committed private key material outside config/jwt/:"
  echo "$hits" | sed 's/^/    /'
else
  pass "Rule 4: no stray private key material detected"
fi

echo "=========================="
if [[ "$FAILED" -eq 0 ]]; then
  echo "All governance checks passed."
  exit 0
else
  echo "One or more governance checks FAILED. See ✗ lines above."
  exit 1
fi
