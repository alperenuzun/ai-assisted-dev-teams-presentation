# AI Agent Architecture — Improvements

This document summarizes a review of this repo's AI agent setup (`.claude/`, `.github/agents`,
`.ai-pack/`) and the concrete changes made in response. It's meant to be read alongside the
diff, not as a replacement for it.

## What was reviewed

- `.ai-pack/shared/` — the generic, multi-IDE "agent pack" template + `setup.sh` that
  renders it into `.claude/agents/*.md`, `.github/agents/*.md`, `.cursor/agents/*.md`
- `.claude/agents/*.md` — the actual Claude Code persona files
- `.claude/commands/jira-to-pr.md` and `.claude/commands/parallel-features.md` — the two
  "orchestrator" commands
- `.claude/settings.json` — MCP server config, permissions, model pin

## Findings and what changed

| # | Finding | Change |
|---|---|---|
| 1 | `jira-to-pr.md` ran every phase by "loading a persona" in the *same* conversation context and passed handoffs as free-text blocks — no real agent isolation, no schema, no resume. | Rewrote the command to launch each phase as a separate `Task` subagent, and to persist handoff/checkpoint data as typed JSON at `.claude/state/{TASK_KEY}.json` against `.claude/state/schema.json`. A run interrupted mid-pipeline now resumes from `currentPhase` instead of restarting. |
| 2 | `security-auditor-agent` and `test-writer-agent` were fully defined (`.ai-pack/shared/agents/*.json`, `.github/agents/*.md`) but never invoked by any workflow — PRs went out with no automated security pass or guaranteed test coverage. | Ported both into `.claude/agents/{security-auditor,test-writer}.md` and wired them into `jira-to-pr.md` as Phase 3 and Phase 4, between Implementation and Code Review. |
| 3 | Retry logic was an untracked rule in prose ("if tests fail after 3 attempts, draft PR") with nothing counting attempts. | `attempts.<phase>` counters now live in the state file; each phase increments its own counter and the orchestrator routes to `blocked` once a phase exceeds 3 attempts, instead of silently looping or guessing. |
| 4 | `/parallel-features` only *described* parallel agents and a merge step in prose — nothing actually spawned a session, waited for it, or gated merges on a worktree's own tests. | Added `.claude/scripts/run-parallel-agents.sh`, a real supervisor: creates worktrees, backgrounds one headless `claude -p` session per feature, waits on all PIDs, runs `pest` per worktree *before* merging, and `git merge --abort`s (rather than leaving a half-merged branch) on conflict or test failure. `parallel-features.md` now points at this script instead of a "conceptual" launch command. |
| 5 | `.claude/settings.json` referenced two MCP packages that don't exist on npm: `@modelcontextprotocol/server-atlassian` and `@anthropic/mcp-server-figma` (both verified 404 via `npm view`). Also, every MCP server ran via bare `npx -y <pkg>` with no version pin — floats to whatever is newest at run time. | Swapped to real, currently-published packages (`mcp-atlassian@2.1.0`, `figma-developer-mcp@0.13.2`) and pinned `@modelcontextprotocol/server-github@2025.4.8`. Left `_note` fields flagging that env var names/flags should still be double-checked against each server's own docs before production use. |
| 6 | Behavior rules in agent personas ("staging endpoints never require auth", "Doctrine XML mappings not annotations", "Domain/ stays framework-agnostic") were enforced only by LLM compliance with no automated check. | Added `scripts/ai-governance-check.sh` + `.github/workflows/agent-governance-check.yml`, a grep-based CI guardrail for a first batch of these rules. It's also called from `jira-to-pr.md`'s new Security Audit phase. Running it against the current codebase surfaced one real, pre-existing case worth a conscious decision: `src/Api/Domain/User/Entity/User.php` implements Symfony's `UserInterface`/`PasswordAuthenticatedUserInterface` inside the Domain layer, which is required for Symfony Security integration. The check now allow-lists exactly those two marker interfaces and nothing else from `Symfony\`/`Doctrine\`, so the exception is explicit and narrow instead of either being silently ignored or blocking CI on an unavoidable framework integration point. |
| 7 | `.ai-pack/shared/instructions.md` described a React/TypeScript/Node.js stack, Jest, JSDoc, `.tsx` components — none of which exist in this (PHP/Symfony) project. An agent reading this file instead of `CLAUDE.md` would generate code for the wrong stack entirely. | Corrected the Technology Stack, Naming Conventions, and Code Organization sections to match the real PHP 8.3/Symfony 7.3/DDD/Pest stack, and added a banner pointing at `CLAUDE.md` as the actual source of truth. |

## What was intentionally left out of this PR

- Making the model ID in `.claude/settings.json` configurable (e.g. per-agent model choice
  for cost/quality tradeoffs) — needs an actual config-loading mechanism, not just a JSON
  edit.
- Full observability/eval harness (token/time per agent, golden-set regression tests
  against real past Jira tasks) — worth its own PR with its own success metric.
- Extending `scripts/ai-governance-check.sh` beyond the 4 rules above — intentionally
  started narrow and CI-verified rather than guessing at a long list of untested rules.

## How to verify

```bash
# governance guardrail (also run in CI on every PR):
./scripts/ai-governance-check.sh

# state schema is valid JSON Schema:
python3 -c "import json; json.load(open('.claude/state/schema.json'))"

# settings.json is still valid JSON after the MCP server fixes:
python3 -c "import json; json.load(open('.claude/settings.json'))"

# parallel supervisor script has no syntax errors:
bash -n .claude/scripts/run-parallel-agents.sh
```
