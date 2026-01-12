# Figma Design to Code

Convert a Figma design into frontend code (Twig templates + CSS).

## Instructions

Figma design URL or node: `$ARGUMENTS`

### Phase 1: Design Analysis

1. **Fetch Figma Design**:
   - Use Figma MCP: `mcp__figma__get_file` or `mcp__figma__get_node`
   - Parse the Figma URL to extract file key and node ID
   - Download design specifications

2. **Extract Design Tokens**:
   - **Colors**: Primary, secondary, accent, text, background
   - **Typography**: Font families, sizes, weights, line heights
   - **Spacing**: Margins, paddings, gaps
   - **Border radius**: Corner radiuses
   - **Shadows**: Box shadows, drop shadows

3. **Analyze Component Structure**:
   - Identify layout (flexbox, grid)
   - List all UI components
   - Note responsive breakpoints
   - Document interactive states (hover, active, disabled)

### Phase 2: Code Generation

1. **Create/Update CSS Variables** (in `public/css/variables.css`):
   ```css
   :root {
     /* Colors */
     --color-primary: #...;
     --color-secondary: #...;

     /* Typography */
     --font-family: '...';
     --font-size-base: ...px;

     /* Spacing */
     --spacing-unit: ...px;

     /* Borders */
     --border-radius: ...px;
   }
   ```

2. **Create Twig Template** (in `templates/`):
   - Use semantic HTML5 elements
   - Apply BEM naming convention for classes
   - Include accessibility attributes (aria-*, role)
   - Add responsive meta viewport if needed

3. **Create Component Styles** (in `public/css/`):
   - Mobile-first approach
   - Use CSS custom properties
   - Include hover/focus states
   - Add print styles if needed

### Phase 3: Implementation

1. **File Structure**:
   ```
   templates/
   └── {component-name}/
       └── {component}.html.twig

   public/
   └── css/
       ├── variables.css
       └── components/
           └── {component}.css
   ```

2. **Integration**:
   - Link CSS in base template
   - Create Twig blocks for extensibility
   - Add any required JavaScript for interactions

### Phase 4: Verification

1. **Visual Comparison**:
   - Compare rendered output with Figma design
   - Check responsive behavior
   - Verify interactive states

2. **Code Quality**:
   - Validate HTML (no errors)
   - Check CSS for unused rules
   - Ensure accessibility compliance

## Design Token Mapping

| Figma Property | CSS Property |
|----------------|--------------|
| Fill | background-color / color |
| Stroke | border |
| Effects > Drop Shadow | box-shadow |
| Text > Font | font-family |
| Text > Size | font-size |
| Text > Weight | font-weight |
| Auto Layout > Gap | gap |
| Auto Layout > Padding | padding |
| Corner Radius | border-radius |

## Example Usage

With Figma URL:
```
/figma-to-code https://www.figma.com/file/ABC123/Design?node-id=1:234
```

With just node ID:
```
/figma-to-code ABC123/1:234
```

## Output

After completion, provide:
1. List of created/modified files
2. Design tokens extracted
3. Component structure overview
4. Any manual adjustments needed
5. Screenshot comparison (if possible)

## Notes

- This project uses Twig for templates (Symfony)
- CSS files go in `public/css/`
- Use existing design system if present
- Maintain consistency with existing components
