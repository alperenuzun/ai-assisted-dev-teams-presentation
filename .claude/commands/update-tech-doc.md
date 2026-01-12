# Update Technical Documentation

Update existing technical documentation for the specified API endpoint.

## Instructions

1. **Find the existing documentation**: Look for the documentation file in `docs/api/` for endpoint: `$ARGUMENTS`

2. **Analyze current implementation**:
   - Read the controller, command/query handler, and related domain entities
   - Compare with existing documentation
   - Identify any changes or discrepancies

3. **Update the documentation**:
   - Keep the existing structure
   - Update any changed parameters, schemas, or responses
   - Add new fields or remove deprecated ones
   - Update examples if they're outdated
   - Add a "Last Updated" date at the bottom

4. **Validation checklist**:
   - [ ] Request parameters match implementation
   - [ ] Response schema is accurate
   - [ ] Error codes are documented
   - [ ] Authentication requirements are correct
   - [ ] Examples work with current implementation

5. **Test the examples**:
   - Run the cURL examples to verify they work
   - Remember: Use port 8081 for this project
   - Use `docker exec blog-php` for PHP commands

6. **Add changelog entry** (if significant changes):
```markdown
## Changelog
- **{date}**: {description of changes}
```

7. **Important Notes**:
   - This project runs on Docker (port 8081)
   - Never remove documentation for deprecated endpoints without explicit confirmation
   - If endpoint no longer exists, mark it as deprecated instead of deleting
