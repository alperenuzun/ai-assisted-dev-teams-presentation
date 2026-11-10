# Jira Task to Pull Request - Orchestrator Workflow

This is an **orchestrator command** that coordinates multiple specialized agents, run as
isolated **subagents via the `Task` tool**, to complete a Jira task from start to finish.

> **Changelog (architecture review)**: this command used to run every phase by "loading a
> persona" in the *same* conversation context and passing free-text "Handoff Data" blocks
> between phases. That meant no real isolation between agents, no schema on the handoff
> data, and no way to resume a run that was interrupted mid-pipeline. This version:
> - launches each phase as a **separate `Task` subagent** (fresh context per phase)
> - persists handoff data as **typed JSON state** at `.claude/state/{TASK_KEY}.json`
>   (schema: `.claude/state/schema.json`) instead of free-text blocks
> - adds two previously-orphaned agents (**Security Auditor**, **Test Writer**) into the
>   pipeline — they existed under `.ai-pack/shared/agents/` and `.github/agents/` but were
>   never actually invoked by any workflow
> - makes retries explicit and bounded via `state.attempts.<phase>` instead of an untracked
>   "if tests fail after 3 attempts" rule

## Workflow Overview

```
┌──────────┐   ┌─────────────┐   ┌──────────┐   ┌──────────┐   ┌────────┐   ┌────────────┐
│   Task   │──▶│  Backend    │──▶│ Security │──▶│   Test   │──▶│  Code  │──▶│     PR     │
│ Analyzer │   │ Developer   │   │ Auditor  │   │  Writer  │   │Reviewer│   │  Creator   │
└──────────┘   └─────────────┘   └──────────┘   └──────────┘   └────────┘   └────────────┘
   analysis     implementation   security_audit  test_writing   code_review   pr_creation
```

Each stage name above matches a `currentPhase` value in `.claude/state/schema.json`.

## Target Jira Task: `$ARGUMENTS`

---

## PHASE 0: Resume Check (always run first)

1. Compute `TASK_KEY` from `$ARGUMENTS`.
2. Check if `.claude/state/{TASK_KEY}.json` exists.
   - If it **does not exist**, create it with:
     ```json
     {
       "taskKey": "{TASK_KEY}",
       "branch": "feature/{TASK_KEY}-tbd",
       "createdAt": "{iso-now}",
       "updatedAt": "{iso-now}",
       "currentPhase": "analysis",
       "attempts": {},
       "phases": {}
     }
     ```
   - If it **exists** and `currentPhase` is not `complete` or `blocked`, **skip directly**
     to the phase named by `currentPhase` instead of starting over. Report to the user:
     `Resuming {TASK_KEY} from phase: {currentPhase}`.
   - If `currentPhase` is `blocked`, stop and report the blocking reason from the state
     file before doing anything else.

Every phase below, after completing its work, MUST:
1. Update `.claude/state/{TASK_KEY}.json` (`phases.<name>`, `currentPhase` → next phase,
   `updatedAt`), validating against `.claude/state/schema.json` shape.
2. Only then hand off — the next phase reads its input **from the state file**, not from
   conversation memory.

---

## PHASE 1: Task Analysis

**Subagent**: launch via `Task` tool using persona `.claude/agents/task-analyzer.md`.

### Instructions

1. Fetch the Jira task (Atlassian MCP `mcp__atlassian__get_issue`, fallback: ask the user
   to paste the task body if MCP is unavailable).
2. Extract title, description, acceptance criteria; identify task type; break down into
   technical requirements; identify affected bounded contexts/layers; list files to
   create/modify; assess complexity/risks (full report format defined in the agent file).
3. Write results into `phases.analysis` in the state file, set `branch` to
   `feature/{TASK_KEY}-{short-description}`, set `currentPhase` to `implementation`.

---

## PHASE 2: Implementation

**Subagent**: launch via `Task` tool using persona `.claude/agents/backend-developer.md`.
**Input**: `phases.analysis` from the state file (do not re-derive it from scratch).

### Instructions

1. `git checkout -b {branch}` (branch name comes from the state file).
2. Implement each step from `phases.analysis.implementationSteps`, following DDD patterns
   strictly (value objects, CQRS commands/queries, Doctrine XML mappings, messenger.yaml).
3. `docker exec blog-php php bin/console cache:clear && docker exec blog-php vendor/bin/pint`
4. Write `phases.implementation` (filesCreated/filesModified/decisionsMade/knownIssues),
   set `currentPhase` to `security_audit`.

---

## PHASE 3: Security Audit *(new)*

**Subagent**: launch via `Task` tool using persona `.claude/agents/security-auditor.md`.
**Input**: `phases.implementation` (diff/file list) from the state file.

### Instructions

1. Run the automated guardrail check first: `scripts/ai-governance-check.sh` (catches
   known rule violations, e.g. auth on staging endpoints, Doctrine annotations instead of
   XML mappings — see that script for the full list). Treat any failure as **blocking**.
2. Perform the manual review checklist defined in the agent file.
3. Write `phases.security_audit` with `status`.
   - `blocking_issues` → increment `attempts.security_audit`; if `attempts.security_audit <
     3`, set `currentPhase` back to `implementation` and pass `blockingFindings` so the
     Backend Developer subagent knows what to fix. If `attempts.security_audit >= 3`, set
     `currentPhase` to `blocked` and stop the whole workflow.
   - `pass` / `pass_with_notes` → set `currentPhase` to `test_writing`.

---

## PHASE 4: Test Writing *(new)*

**Subagent**: launch via `Task` tool using persona `.claude/agents/test-writer.md`.
**Input**: `phases.implementation` + `phases.security_audit` from the state file.

### Instructions

1. Write/extend Pest tests for all touched entities, value objects, handlers, endpoints.
2. Run `docker exec blog-php vendor/bin/pest`.
3. Write `phases.test_writing` (testFiles, suiteResult, coverageGaps).
   - `suiteResult: fail` → increment `attempts.test_writing`; if `< 3`, stay in this phase
     and retry; if `>= 3`, set `currentPhase` to `blocked`.
   - `suiteResult: pass` → set `currentPhase` to `code_review`.

---

## PHASE 5: Code Review

**Subagent**: launch via `Task` tool using persona `.claude/agents/code-reviewer.md`.
**Input**: full `phases` object so far (implementation + security_audit + test_writing).

### Instructions

1. Review architecture compliance, code quality, Doctrine mappings, style
   (`vendor/bin/pint --test`), and re-confirm tests still pass.
2. Write `phases.code_review`.
   - `changes_requested` → increment `attempts.code_review`; if `< 3`, set `currentPhase`
     back to `implementation` with the fix list; if `>= 3`, set `currentPhase` to
     `blocked`.
   - `approved` → set `currentPhase` to `pr_creation`.

---

## PHASE 6: Pull Request Creation

**Subagent**: launch via `Task` tool using persona `.claude/agents/pr-creator.md`.
**Input**: full state file (used to build the PR description from real data, not
re-summarized from memory).

### Instructions

1. Commit (HEREDOC commit message), push branch.
2. Create PR: GitHub MCP `mcp__github__create_pull_request`, fallback `gh pr create`
   (both call out explicitly in the agent file — MCP first, CLI fallback).
3. Update Jira via Atlassian MCP (`mcp__atlassian__update_issue` + comment with PR link).
   If MCP tools are unavailable, note this explicitly in the final report instead of
   silently skipping the Jira update.
4. Write `phases.pr_creation` (prUrl, commitSha, jiraUpdated), set `currentPhase` to
   `complete`.

---

## Final Report

After `currentPhase` becomes `complete`, read the full state file and render:

```markdown
## Workflow Complete ✅

### Jira Task
- **Key**: {taskKey}
- **Status**: In Review

### Pipeline
1. ✅ Task Analyzer — {phases.analysis.taskType}
2. ✅ Backend Developer — {len(filesCreated)} created / {len(filesModified)} modified
3. ✅ Security Auditor — {phases.security_audit.status}
4. ✅ Test Writer — {len(testFiles)} test files, suite: {suiteResult}
5. ✅ Code Reviewer — {approvalStatus}
6. ✅ PR Creator — {prUrl}

### Retries Used
{attempts object, phase: count — 0 means first-try success}

### Next Steps
- Await human code review
- Address any feedback
- Merge when approved
```

---

## Error Handling

- **Jira task not found** → report and stop, do not create a state file.
- **A phase fails `attempts.<phase> >= 3` times** → set `currentPhase: blocked`, write the
  reason into the state file, and stop. Re-running `/jira-to-pr {TASK_KEY}` later resumes
  from `blocked` and reports the reason instead of silently retrying forever.
- **MCP tools unavailable** → fall back to `git`/`gh` CLI commands; note the fallback in
  the final report so it's visible this wasn't the primary path.
