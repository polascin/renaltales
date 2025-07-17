# CSS Architecture Documentation - Renal Tales

## Overview
This document describes the CSS architecture and organization for the Renal Tales application after the CSS migration and restructuring.

## File Structure

### Core Foundation Files
```
public/assets/css/
├── basic.css           # CSS reset, variables, root styles, typography
├── layout.css          # Grid system, containers, layout structures
├── components.css      # UI components (buttons, forms, cards, modals)
└── style.css          # Main application styles and header components
```

### Page-Specific Styles
```
public/assets/css/
├── home.css           # Homepage-specific styles
├── navigation.css     # Navigation component styles
├── error.css          # Error page styles
├── language-switcher.css  # Language switcher component
├── post-navigation.css    # Post navigation styles
└── footnotes.css      # Footnotes styling
```

### Responsive Styles
```
public/assets/css/
├── responsive.css            # Main responsive breakpoints
└── footnotes-responsive.css  # Responsive footnotes
```

### Production Files
```
public/assets/css/
├── *.min.css          # Minified versions for production
└── style.min.css      # Main minified stylesheet
```

## CSS Naming Conventions

### BEM (Block Element Modifier) Methodology
We follow BEM naming conventions for consistent, maintainable CSS:

```css
/* Block */
.language-selector { }

/* Element */
.language-selector__form { }
.language-selector__label { }
.language-selector__select { }

/* Modifier */
.language-selector--mobile { }
.language-selector__select--focused { }
```

### Component Naming Patterns
- **Main containers**: `.main-*` (e.g., `.main-header`, `.main-content`)
- **UI components**: `.btn-*`, `.form-*`, `.card-*`
- **Layout utilities**: `.d-*`, `.m-*`, `.p-*` (display, margin, padding)
- **State classes**: `.is-*`, `.has-*` (e.g., `.is-active`, `.has-error`)

## CSS Custom Properties (Variables)

### Color System
```css
:root {
  /* Primary Colors */
  --primary-color: #a0c4ff;        /* Pastel Steel Blue */
  --primary-dark: #7c9dd0;
  
  /* Accent Colors */
  --accent-color: #ff6f61;         /* Pastel Coral */
  --accent-dark: #d95f4f;
  --accent-light: #ff8f7b;
  
  /* Semantic Colors */
  --success-color: #b9fbc0;        /* Pastel Green */
  --danger-color: #ff6b6b;         /* Pastel Red */
  --warning-color: #ffe66d;        /* Pastel Yellow */
  --info-color: #85e3ff;           /* Pastel Cyan */
  
  /* Neutral Colors */
  --background-color: snow;
  --text-color: #333333;
  --border-color: #e0e0e0;
  --panel-bg: seashell;
  --panel-border: #dcdcdc;
}
```

### Typography System
```css
:root {
  --font-family-sans: 'Poppins', sans-serif;
  --font-family-serif: 'Playfair Display', serif;
  --font-family-mono: 'Source Code Pro', monospace;
  --font-family-cursive: 'Pacifico', cursive;
  --font-family-fantasy: 'Eagle Lake', 'Lobster', fantasy;
  --font-family-system: 'system-ui', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}
```

## Responsive Design Strategy

### Breakpoints
```css
/* Mobile First Approach */
@media (max-width: 320px)  { /* Very small screens */ }
@media (min-width: 321px) and (max-width: 480px) { /* Small screens */ }
@media (min-width: 481px) and (max-width: 768px) { /* Medium screens */ }
@media (min-width: 769px) and (max-width: 1024px) { /* Large screens */ }
@media (min-width: 1025px) { /* Extra large screens */ }
```

### Grid System
- **Main Layout**: CSS Grid with `grid-template-areas`
- **Component Grids**: Flexbox for component-level layouts
- **Utility Classes**: Spacing and layout utilities following Bootstrap conventions

## Complex Selectors and Hacks

### Language Switcher Focus States
```css
/* Custom focus ring for accessibility */
.language-select:focus {
  outline: none;
  border-color: var(--primary-color, #007cba);
  box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
}
```

### Grid Layout Fallbacks
```css
/* CSS Grid with flexbox fallback */
.main-container {
  display: grid;
  grid-template-columns: 1fr 3fr 1fr;
  grid-template-areas: "menu content notes";
  gap: 1rem;
}

/* Fallback for older browsers */
.main-container {
  display: flex;
  flex-direction: row;
}
```

### Mobile Navigation Stack
```css
/* Mobile-first responsive navigation */
@media (max-width: 768px) {
  .main-container {
    grid-template-columns: 1fr;
    grid-template-areas: 
      "content"
      "menu"
      "notes";
  }
}
```

## Performance Considerations

### CSS Loading Strategy
1. **Critical CSS**: Inline critical styles for above-the-fold content
2. **Deferred CSS**: Load non-critical styles asynchronously
3. **Minification**: Use minified versions in production
4. **Compression**: Enable gzip compression for CSS files

### File Organization Benefits
- **Modular Loading**: Load only necessary CSS per page
- **Maintainability**: Easier to locate and modify specific styles
- **Caching**: Better browser caching with separate files
- **Development**: Easier development with organized structure

## Browser Support

### Modern Browsers
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

### CSS Features Used
- CSS Custom Properties (CSS Variables)
- CSS Grid Layout
- Flexbox
- CSS Transitions and Animations
- Media Queries Level 4

### Fallbacks
- Flexbox fallbacks for CSS Grid
- Static color values for CSS custom properties
- Progressive enhancement approach

## Development Guidelines

### Adding New Styles
1. **Identify the correct file**: Choose between basic, layout, components, or page-specific
2. **Follow naming conventions**: Use BEM methodology
3. **Use CSS custom properties**: Leverage existing color and typography variables
4. **Consider responsive design**: Mobile-first approach
5. **Add comments**: Document complex selectors and hacks

### Code Quality
- **Linting**: Use stylelint or similar tools
- **Formatting**: Consistent indentation and spacing
- **Comments**: Document complex logic and browser-specific hacks
- **Testing**: Cross-browser testing for critical features

## Migration Notes

### Completed Tasks
- ✅ Extracted inline styles from PHP templates
- ✅ Organized CSS into logical file structure
- ✅ Implemented consistent naming conventions
- ✅ Added comprehensive documentation
- ✅ Created minified versions for production

### Future Enhancements
- [ ] Implement CSS-in-JS for dynamic components
- [ ] Add CSS custom properties for spacing system
- [ ] Create component library documentation
- [ ] Implement CSS modules for better encapsulation

## Team Guidelines

### Code Review Checklist
- [ ] Follows BEM naming conventions
- [ ] Uses existing CSS custom properties
- [ ] Includes responsive considerations
- [ ] Properly documented complex selectors
- [ ] Cross-browser tested

### Communication
- **Slack Channel**: #frontend-development
- **Documentation**: Update this document when adding new patterns
- **Training**: Regular CSS architecture workshops
- **Tools**: Use shared linting and formatting configurations

---

**Last Updated**: December 2024  
**Maintainer**: Frontend Development Team  
**Version**: 1.0.0
