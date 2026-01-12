# Jira Task to Pull Request - Orchestrator Workflow

This is an **orchestrator command** that coordinates multiple specialized agents to complete a Jira task from start to finish.

## Workflow Overview

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Task Analyzer  │ ──▶ │ Backend Developer│ ──▶ │  Code Reviewer  │ ──▶ │   PR Creator    │
│     Agent       │     │      Agent       │     │      Agent      │     │      Agent      │
└─────────────────┘     └─────────────────┘     └─────────────────┘     └─────────────────┘
        │                       │                       │                       │
   Analyze Task            Implement              Review Code             Create PR
   Requirements            Feature                Run Tests             Update Jira
```

## Target Jira Task: `$ARGUMENTS`

---

## PHASE 1: Task Analysis

**Active Agent**: Task Analyzer (`.claude/agents/task-analyzer.md`)

### Instructions

1. **Fetch Jira Task**:
   - Use Atlassian MCP: `mcp__atlassian__get_issue`
   - Get task: `$ARGUMENTS`

2. **Load Agent Persona**:
   - Read `.claude/agents/task-analyzer.md`
   - Adopt the Task Analyzer role

3. **Perform Analysis**:
   - Extract title, description, acceptance criteria
   - Identify task type (feature/bug/refactor)
   - Break down into technical requirements
   - Identify affected bounded contexts and layers
   - List files to create/modify
   - Assess complexity and risks

4. **Output**: Task Analysis Report (as defined in agent file)

5. **Handoff Data**:
   ```
   ANALYSIS_COMPLETE: true
   TASK_KEY: {JIRA-KEY}
   TASK_TYPE: {type}
   IMPLEMENTATION_STEPS: [...]
   AFFECTED_FILES: [...]
   ```

---

## PHASE 2: Implementation

**Active Agent**: Backend Developer (`.claude/agents/backend-developer.md`)

### Instructions

1. **Load Agent Persona**:
   - Read `.claude/agents/backend-developer.md`
   - Adopt the Backend Developer role

2. **Create Feature Branch**:
   ```bash
   git checkout -b feature/{TASK_KEY}-{short-description}
   ```

3. **Implement Changes**:
   Follow the implementation steps from Phase 1:

   For each step:
   - Create/modify required files
   - Follow DDD patterns strictly
   - Use value objects for domain concepts
   - Create Commands/Queries with Handlers
   - Update Doctrine XML mappings
   - Register in messenger.yaml

4. **Run Initial Validation**:
   ```bash
   docker exec blog-php php bin/console cache:clear
   docker exec blog-php vendor/bin/pint
   ```

5. **Output**: List of changes made

6. **Handoff Data**:
   ```
   IMPLEMENTATION_COMPLETE: true
   FILES_CREATED: [...]
   FILES_MODIFIED: [...]
   DECISIONS_MADE: [...]
   KNOWN_ISSUES: [...]
   ```

---

## PHASE 3: Code Review

**Active Agent**: Code Reviewer (`.claude/agents/code-reviewer.md`)

### Instructions

1. **Load Agent Persona**:
   - Read `.claude/agents/code-reviewer.md`
   - Adopt the Code Reviewer role

2. **Review All Changes**:
   - Check architecture compliance
   - Verify DDD patterns
   - Look for security issues
   - Ensure code quality

3. **Run Tests**:
   ```bash
   docker exec blog-php vendor/bin/pest
   ```

4. **Run Code Style Check**:
   ```bash
   docker exec blog-php vendor/bin/pint --test
   ```

5. **Fix Issues** (if any):
   - If tests fail → fix and re-run
   - If pint fails → `docker exec blog-php vendor/bin/pint`
   - If critical issues → go back to Phase 2

6. **Output**: Code Review Report

7. **Handoff Data**:
   ```
   REVIEW_COMPLETE: true
   APPROVAL_STATUS: [Approved | Changes Requested]
   TEST_RESULTS: [pass/fail]
   STYLE_CHECK: [pass/fail]
   REVIEW_SUMMARY: "..."
   ```

---

## PHASE 4: Pull Request Creation

**Active Agent**: PR Creator (`.claude/agents/pr-creator.md`)

### Instructions

1. **Load Agent Persona**:
   - Read `.claude/agents/pr-creator.md`
   - Adopt the PR Creator role

2. **Stage and Commit**:
   ```bash
   git add .
   git commit -m "$(cat <<'EOF'
   {type}({scope}): {description from task}

   - {change 1}
   - {change 2}
   - {change 3}

   Closes {TASK_KEY}

   🤖 Generated with Claude Code
   EOF
   )"
   ```

3. **Push Branch**:
   ```bash
   git push -u origin feature/{TASK_KEY}-{short-description}
   ```

4. **Create Pull Request**:
   - Use GitHub MCP: `mcp__github__create_pull_request`
   - Or use GitHub CLI:
   ```bash
   gh pr create --title "[{TASK_KEY}] {Task Title}" --body "$(cat <<'EOF'
   ## Summary
   {Summary from analysis}

   ## Jira Task
   [{TASK_KEY}]({JIRA_URL})

   ## Changes Made
   {List from implementation}

   ## Test Plan
   - [ ] Unit tests pass
   - [ ] Manual endpoint testing

   🤖 Generated with Claude Code
   EOF
   )"
   ```

5. **Update Jira Task**:
   - Use Atlassian MCP: `mcp__atlassian__update_issue`
   - Set status to "In Review"
   - Add PR link as comment

6. **Output**: Final workflow summary

---

## Final Report

After all phases complete, provide:

```markdown
## Workflow Complete ✅

### Jira Task
- **Key**: {TASK_KEY}
- **Title**: {Title}
- **Status**: In Review

### Implementation Summary
- **Branch**: feature/{TASK_KEY}-{description}
- **Files Created**: {N}
- **Files Modified**: {N}
- **Total Changes**: +{additions} -{deletions}

### Quality Checks
- **Tests**: ✅ Passed
- **Code Style**: ✅ Passed
- **Review**: ✅ Approved

### Pull Request
- **URL**: {PR_URL}
- **Title**: [{TASK_KEY}] {Title}

### Agent Workflow
1. ✅ Task Analyzer - Requirements extracted
2. ✅ Backend Developer - Feature implemented
3. ✅ Code Reviewer - Code approved
4. ✅ PR Creator - PR created and Jira updated

### Next Steps
- Await human code review
- Address any feedback
- Merge when approved
```

---

## Error Handling

### If Jira task not found:
- Report error and stop workflow

### If tests fail after 3 attempts:
- Create PR as draft
- Note failing tests in PR description
- Set Jira status to "Blocked"

### If MCP tools unavailable:
- Fall back to manual git commands
- Fall back to GitHub CLI (`gh`) for PR
- Note in final report
