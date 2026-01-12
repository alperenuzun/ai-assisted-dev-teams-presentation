# Update Confluence Documentation

Update existing Confluence documentation to reflect code changes.

## Instructions

Confluence page to update: `$ARGUMENTS`

### Step 1: Fetch Existing Documentation

1. **Get Current Page**:
   - Use Atlassian MCP: `mcp__atlassian__get_page`
   - Parse the Confluence URL or page ID from arguments
   - Retrieve current page content and version

2. **Identify Documented Subject**:
   - Determine which endpoint/feature is documented
   - Find the corresponding code files

### Step 2: Analyze Changes

1. **Read Current Implementation**:
   - Controller, handlers, entities, value objects
   - Compare with documented information

2. **Identify Discrepancies**:
   - New parameters or fields
   - Changed validation rules
   - Modified response structure
   - Updated error codes
   - Deprecated functionality

3. **List Required Updates**:
   - Create a checklist of changes needed
   - Prioritize critical updates (breaking changes)

### Step 3: Update Documentation

1. **Preserve Structure**:
   - Keep existing section hierarchy
   - Maintain formatting consistency
   - Don't remove historical information without confirmation

2. **Apply Updates**:
   - Update API schemas
   - Refresh code examples
   - Update cURL commands
   - Add new sections if needed
   - Mark deprecated items with warnings

3. **Add Changelog Entry**:
   ```markdown
   ## Changelog

   | Date | Version | Changes |
   |------|---------|---------|
   | {today} | {version} | {description of updates} |
   ```

### Step 4: Publish Updates

1. **Update Confluence Page**:
   - Use Atlassian MCP: `mcp__atlassian__update_page`
   - Increment page version
   - Add update comment

2. **Notify Stakeholders** (optional):
   - Add page comment mentioning updates
   - Tag relevant team members if breaking changes

## Validation Checklist

Before publishing, verify:
- [ ] All endpoints match current implementation
- [ ] Request/response schemas are accurate
- [ ] cURL examples work correctly
- [ ] Error codes are complete
- [ ] Authentication requirements are correct
- [ ] Links to related pages are valid

## Example Usage

```
/update-confluence https://company.atlassian.net/wiki/spaces/DOCS/pages/123456
```

Or with page ID:
```
/update-confluence 123456
```

## Output

After completion, provide:
1. Updated Confluence page URL
2. Summary of changes made
3. List of any items that need manual review
4. Diff summary (what was added/modified/removed)
