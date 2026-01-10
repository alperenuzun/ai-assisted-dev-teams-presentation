# Create Feature Command

Creates a new feature branch and initializes the feature development workflow.

## Usage

```bash
/create-feature <feature-name> [options]
```

## Arguments

- `feature-name`: Name of the feature (kebab-case)

## Options

- `--type <type>`: Feature type (frontend, backend, fullstack)
- `--assign <agent>`: Assign specific AI agent
- `--description <text>`: Feature description

## Examples

```bash
# Create frontend feature
/create-feature user-authentication --type frontend

# Create fullstack feature with description
/create-feature payment-integration --type fullstack --description "Integrate Stripe payment gateway"

# Assign specific agent
/create-feature api-optimization --type backend --assign backend-specialist
```

## Workflow

1. Create feature branch `feature/<feature-name>`
2. Initialize project structure
3. Create task tracking
4. Assign appropriate AI agent
5. Start feature development workflow

## AI Agent Assignment

Based on feature type:
- `frontend`: frontend-specialist
- `backend`: backend-specialist
- `fullstack`: architect (coordinates both)

## Output

- New feature branch created
- Task list initialized
- Development workflow started
- Agent ready to assist
