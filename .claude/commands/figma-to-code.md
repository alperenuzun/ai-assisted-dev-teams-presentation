# Figma Design to Code

Convert a Figma design into frontend code (Twig templates + CSS).

## Instructions

Figma design URL or node: `$ARGUMENTS`

### Phase 1: Design Analysis

1. **Fetch Figma Design**:
   - Use Figma MCP: `mcp_Figma_MCP_Server_get_design_context` for full design context
   - Use `mcp_Figma_MCP_Server_get_screenshot` for visual reference
   - Use `mcp_Figma_MCP_Server_get_variable_defs` for design tokens
   - Parse the Figma URL to extract file key and node ID (format: `2-5856` → `2:5856`)
   - If section node is selected, fetch individual child nodes for detailed implementation

2. **Extract Design Tokens**:
   - **Colors**: 
     - Extract ALL background colors for different sections (header, hero, content sections)
     - Primary, secondary, accent, text colors
     - Border colors
     - Pay special attention to section-specific backgrounds (e.g., welcome section vs header)
   - **Typography**: 
     - Font families (if Inter or custom font, add Google Fonts link)
     - Font sizes (extract exact px values)
     - Font weights (400, 500, 600, 700)
     - Line heights
   - **Spacing**: 
     - Margins, paddings, gaps
     - Section-specific padding (hero sections often have more padding)
   - **Border radius**: Corner radiuses
   - **Shadows**: Box shadows, drop shadows
   - **Layout**: Container max-width, alignment (center, left, right)

3. **Analyze Component Structure**:
   - Identify layout (flexbox, grid)
   - **Text Alignment**: Note which sections have centered text (hero/welcome sections)
   - **Section Backgrounds**: Identify which sections have different background colors
   - List all UI components
   - Note responsive breakpoints
   - Document interactive states (hover, active, disabled)
   - **Container Structure**: Identify if hero sections need special container styling (flexbox, center alignment)

### Phase 2: Code Generation

1. **Create/Update CSS Variables** (in `public/css/variables.css`):
   ```css
   :root {
     /* Colors - Extract ALL section-specific colors */
     --color-text-default: #...;
     --color-background-surface: #ffffff;
     --color-header-background: #ffffff; /* Usually white, verify from design */
     --color-welcome-background: #e8eaf6; /* Hero/welcome section background */
     --color-card-background: #...;
     /* ... other colors ... */

     /* Typography */
     --font-family-default: 'Inter', ...; /* If Inter, add Google Fonts */
     --font-size-6xl: 60px;
     --font-size-base: 16px;
     /* ... all sizes ... */

     /* Spacing */
     --spacing-1: 4px;
     --spacing-6: 24px;
     /* ... all spacing values ... */
   }
   ```
   **IMPORTANT**: 
   - Separate header background from welcome/hero section background
   - Extract exact color values from Figma variables or screenshot
   - Include all font weights used in design

2. **Update Base Template** (`templates/base.html.twig`):
   - **Add Google Fonts** if design uses Inter or other Google Fonts:
     ```html
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
     ```
   - Link all CSS files in correct order (variables.css first, then main.css, then components)

3. **Create Twig Template** (in `templates/`):
   - Use semantic HTML5 elements
   - Apply BEM naming convention for classes
   - **Verify text content matches Figma exactly** (especially subtitles, descriptions)
   - Include accessibility attributes (aria-*, role)
   - **Add appropriate classes for section-specific styling**:
     - Hero sections: `class="section section--hero hero"`
     - Regular sections: `class="section"`
   - Ensure container structure supports centering if needed

4. **Create Main CSS** (`public/css/main.css`):
   - **Header Styling**:
     - Background color (usually white, verify from design)
     - Border if needed (subtle bottom border common)
   - **Hero/Welcome Section Styling**:
     - Background color (often different from header)
     - **Text alignment**: Center for hero sections
     - **Container**: Use flexbox for centering content
     - Padding: Usually more padding (spacing-24 top, spacing-16 bottom)
   - **Section Styling**:
     - Standard padding for regular sections
     - Left-aligned text for content sections
   - Mobile-first approach
   - Use CSS custom properties
   - Include hover/focus states

5. **Create Component Styles** (in `public/css/components/`):
   - Mobile-first approach
   - Use CSS custom properties
   - Include hover/focus states
   - Responsive breakpoints

### Phase 3: Implementation

1. **File Structure**:
   ```
   templates/
   ├── base.html.twig (update with Google Fonts if needed)
   └── {component-name}/
       └── {component}.html.twig

   public/
   └── css/
       ├── variables.css (all design tokens)
       ├── main.css (base styles, header, hero, sections)
       └── components/
           ├── principle-card.css
           ├── structure-item.css
           ├── community-card.css
           └── {other-components}.css
   ```

2. **Integration**:
   - **Update base template** with Google Fonts link if design uses Inter/custom fonts
   - Link CSS in base template (variables.css → main.css → components)
   - Create Twig blocks for extensibility
   - Add any required JavaScript for interactions

3. **Docker Container Sync** (CRITICAL):
   - **ALWAYS copy files to container** after creation:
     ```bash
     docker cp public/css/variables.css blog-php:/var/www/html/public/css/
     docker cp public/css/main.css blog-php:/var/www/html/public/css/
     docker cp public/css/components/. blog-php:/var/www/html/public/css/components/
     docker cp templates/base.html.twig blog-php:/var/www/html/templates/
     docker cp templates/{component}/. blog-php:/var/www/html/templates/{component}/
     ```
   - **Create directories if needed**:
     ```bash
     docker exec blog-php mkdir -p /var/www/html/public/css/components
     ```
   - **Clear Symfony cache**:
     ```bash
     docker exec blog-php php bin/console cache:clear
     ```

4. **Verify Text Content**:
   - **Compare subtitle/description text with Figma design EXACTLY**
   - Common issues: Slight wording differences, punctuation differences
   - Extract text directly from Figma design context or screenshot description

### Phase 4: Verification & Testing

1. **Visual Comparison Checklist**:
   - ✅ Header background color matches (usually white)
   - ✅ Welcome/Hero section background color matches (often light blue/lavender)
   - ✅ Text alignment: Hero sections centered, content sections left-aligned
   - ✅ Font family matches (Inter should be loaded via Google Fonts)
   - ✅ Subtitle text matches Figma EXACTLY (word-for-word)
   - ✅ Spacing and padding match design
   - ✅ Colors match design tokens
   - ✅ Responsive behavior works
   - ✅ Interactive states (hover, focus) work

2. **Code Quality**:
   - Validate HTML (no errors)
   - Check CSS for unused rules
   - Ensure accessibility compliance
   - Verify all CSS files are linked in base template

3. **Test in Browser**:
   - Visit `http://localhost:8081/` (or configured port)
   - Compare with Figma design side-by-side
   - Check browser console for errors
   - Verify fonts load correctly
   - Test responsive breakpoints

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

## Critical Checklist (Must Follow)

Before completing, verify:

1. **Section Background Colors**:
   - [ ] Header has correct background (usually white)
   - [ ] Welcome/Hero section has correct background (often different color)
   - [ ] Other sections have correct backgrounds

2. **Text Alignment**:
   - [ ] Hero/Welcome sections have centered text
   - [ ] Content sections have left-aligned text
   - [ ] Container uses flexbox for centering where needed

3. **Typography**:
   - [ ] Google Fonts link added to base template if using Inter/custom fonts
   - [ ] Font family matches design exactly
   - [ ] Font sizes match design tokens
   - [ ] Font weights match design

4. **Text Content**:
   - [ ] Subtitle text matches Figma EXACTLY (word-for-word)
   - [ ] All descriptions match design
   - [ ] No placeholder text left

5. **Docker Integration**:
   - [ ] All CSS files copied to container
   - [ ] All template files copied to container
   - [ ] Directories created in container if needed
   - [ ] Symfony cache cleared

6. **CSS Structure**:
   - [ ] Variables.css has all design tokens
   - [ ] Main.css has header, hero, section styles
   - [ ] Component CSS files created for each component
   - [ ] All files linked in base template

## Common Mistakes to Avoid

1. **❌ Using same background color for header and welcome section**
   - ✅ Header usually white, welcome section often has colored background

2. **❌ Forgetting to center hero section text**
   - ✅ Hero sections need `text-align: center` and flexbox container

3. **❌ Not adding Google Fonts for Inter**
   - ✅ Always add Google Fonts link if design uses Inter

4. **❌ Text content doesn't match Figma exactly**
   - ✅ Extract text directly from Figma, don't paraphrase

5. **❌ Files not copied to Docker container**
   - ✅ Always copy files to container and clear cache

6. **❌ Missing section-specific CSS classes**
   - ✅ Use `section--hero` modifier class for hero sections

## Notes

- This project uses Twig for templates (Symfony)
- CSS files go in `public/css/`
- Use existing design system if present
- Maintain consistency with existing components
- **Docker environment**: Always sync files to container after creation
- **Port**: Application runs on port 8081
