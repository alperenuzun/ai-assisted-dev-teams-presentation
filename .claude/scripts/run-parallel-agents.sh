#!/usr/bin/env bash
# Real supervisor for /parallel-features.
#
# Previously `/parallel-features` only *described* parallel agents in prose
# ("Launch Command (Conceptual)") with no process that actually started them,
# merged results, or handled a failing worktree. This script is the executable
# counterpart: it creates the worktrees, launches one headless `claude -p`
# session per feature in the background, waits for all of them, runs quality
# checks per worktree, and only then merges sequentially — aborting the merge
# step (not silently continuing) if a worktree's own test suite is red.
#
# Usage:
#   .claude/scripts/run-parallel-agents.sh "Comment,Tag,Category" [base_branch]
#
# Requires: git, claude CLI (headless/-p mode), docker compose stack running.

set -euo pipefail

FEATURES_CSV="${1:?usage: run-parallel-agents.sh \"Feature1,Feature2\" [base_branch]}"
BASE_BRANCH="${2:-main}"

PROJECT_ROOT="$(git rev-parse --show-toplevel)"
PROJECT_NAME="$(basename "$PROJECT_ROOT")"
WORKTREES_DIR="$PROJECT_ROOT/../${PROJECT_NAME}-worktrees"
LOG_DIR="$PROJECT_ROOT/.claude/state/parallel-logs"
RUN_ID="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$WORKTREES_DIR" "$LOG_DIR"

if [[ -n "$(git status --porcelain)" ]]; then
  echo "ERROR: working tree is not clean. Commit or stash changes first." >&2
  exit 1
fi

IFS=',' read -r -a FEATURES <<< "$FEATURES_CSV"
declare -A PIDS
declare -A WT_PATHS
declare -A BRANCHES

echo "== Phase 1: creating ${#FEATURES[@]} worktree(s) off ${BASE_BRANCH} =="
for feature in "${FEATURES[@]}"; do
  slug="$(echo "$feature" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '-')"
  branch="feature/add-${slug}-entity"
  wt_path="$WORKTREES_DIR/feature-${slug}"

  git worktree add "$wt_path" -b "$branch" "$BASE_BRANCH"
  WT_PATHS["$feature"]="$wt_path"
  BRANCHES["$feature"]="$branch"
done

git worktree list

echo "== Phase 2: launching one headless agent per worktree (background) =="
for feature in "${FEATURES[@]}"; do
  wt_path="${WT_PATHS[$feature]}"
  log_file="$LOG_DIR/${RUN_ID}-${feature}.log"

  prompt="You are the Backend Developer Agent (.claude/agents/backend-developer.md) for this Symfony DDD project. Working directory is this isolated git worktree only. Implement a complete ${feature} feature: entity + value objects + repository (interface+Doctrine impl) + CQRS command/query handlers + Doctrine XML mapping + controller endpoints. Then run 'docker exec blog-php vendor/bin/pint' and 'docker exec blog-php vendor/bin/pest' inside the container and fix failures. Finish with a single git commit describing the changes. Do not touch files outside this worktree."

  (
    cd "$wt_path"
    claude -p "$prompt" --output-format json > "$log_file" 2>&1
    echo $? > "${log_file}.exit"
  ) &
  PIDS["$feature"]=$!
  echo "  started feature='$feature' pid=${PIDS[$feature]} worktree=$wt_path log=$log_file"
done

echo "== Waiting for all background agents to finish =="
FAILED_FEATURES=()
for feature in "${FEATURES[@]}"; do
  pid="${PIDS[$feature]}"
  wait "$pid" || true
  log_file="$LOG_DIR/${RUN_ID}-${feature}.log"
  exit_code="$(cat "${log_file}.exit" 2>/dev/null || echo 1)"
  if [[ "$exit_code" != "0" ]]; then
    echo "  ✗ feature='$feature' agent exited non-zero ($exit_code) — see $log_file"
    FAILED_FEATURES+=("$feature")
  else
    echo "  ✓ feature='$feature' agent finished — see $log_file"
  fi
done

echo "== Phase 3: per-worktree quality gate (before any merge) =="
MERGE_CANDIDATES=()
for feature in "${FEATURES[@]}"; do
  if [[ " ${FAILED_FEATURES[*]} " == *" ${feature} "* ]]; then
    echo "  skipping quality gate for '$feature' (agent already failed)"
    continue
  fi
  wt_path="${WT_PATHS[$feature]}"
  if docker exec blog-php vendor/bin/pest --compact 2>&1 | tee -a "$LOG_DIR/${RUN_ID}-${feature}-pest.log"; then
    MERGE_CANDIDATES+=("$feature")
  else
    echo "  ✗ feature='$feature' failed its own test suite — will NOT be merged"
    FAILED_FEATURES+=("$feature")
  fi
done

echo "== Phase 4: sequential merge of passing features only =="
for feature in "${MERGE_CANDIDATES[@]}"; do
  branch="${BRANCHES[$feature]}"
  echo "  merging $branch into $(git branch --show-current)"
  if ! git merge --no-ff "$branch" -m "Merge $branch (parallel-features run $RUN_ID)"; then
    echo "  ✗ merge conflict on $branch — aborting merge, leaving worktree for manual resolution" >&2
    git merge --abort
    FAILED_FEATURES+=("$feature")
  fi
done

echo "== Summary =="
echo "Succeeded and merged: ${MERGE_CANDIDATES[*]:-none}"
echo "Failed (worktree preserved for debugging): ${FAILED_FEATURES[*]:-none}"
echo "Logs: $LOG_DIR/${RUN_ID}-*.log"

if [[ ${#FAILED_FEATURES[@]} -gt 0 ]]; then
  exit 1
fi
