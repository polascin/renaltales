# CSS Architecture Documentation - Renal Tales

## Overview
This document describes the modern CSS architecture and organization for the Renal Tales application. The architecture follows industry best practices with a modular, scalable, and maintainable approach using BEM methodology and CSS custom properties.

## File Structure

### Main Orchestrator
```
main.css                   # Main CSS orchestrator with import order and comments
```

### Core Foundation Files
```
core/
├── variables.css          # Component-specific design tokens and variables
├── reset.css              # CSS normalization and reset
├── typography.css         # Typography styles and utility classes
└── responsive-mixins.css  # Responsive design mixins and utilities
```

### Layout System
```
layout/
└── spacing.css           # Margin, padding, and spacing utilities
```

### Component Library
```
components/
├── buttons.css           # Button variants and interactive states
├── forms.css            # Form elements and validation styles
├── cards.css            # Card component variants and layouts
├── navigation.css       # Navigation and menu components
└── modern-enhancements.css # Modern component enhancements
```

### Theme System
```
public/assets/css/
└── themes.css           # Unified theme system with light/dark modes
```

### Legacy and Application Styles
```
public/assets/css/
├── basic.css            # Base styles and responsive typography
├── layout.css           # Main layout containers and structures
├── components.css       # Legacy component compatibility layer
├── style.css           # General application and header styles
├── modern-home.css     # Modern home page enhancements
├── navigation.css      # Navigation-specific styles
├── error.css           # Error page styles
├── responsive.css      # Responsive utilities and breakpoints
└── performance.css     # Performance-related optimizations
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

Our CSS architecture uses a comprehensive design token system with CSS custom properties. For complete documentation of our variable naming conventions, see [CSS Variable Naming Conventions](CSS_VARIABLE_NAMING.md).

### Color System Overview
The color system uses a modern approach with semantic tokens:

```css
/* Modern color scale (50-950) */
--color-primary-500        /* Base brand color */
--color-primary-hover      /* Interactive hover state */
--color-primary-active     /* Interactive active state */

/* Semantic color tokens */
--color-background         /* Main page background */
--color-text               /* Primary text color */
--color-border             /* Default border color */
--color-surface            /* Card and modal backgrounds */
```

### Typography System Overview
Typography tokens following modular scale principles:

```css
/* Font families */
--font-family-sans         /* Primary interface font */
--font-family-serif        /* Content and display font */
--font-family-mono         /* Code and technical content */

/* Modular font scale */
--font-size-base           /* 16px base */
--font-size-lg             /* 20px */
--font-size-xl             /* 25px */
--font-size-2xl            /* 31px */
```

### Design Token Documentation
For comprehensive documentation of all CSS variables and naming conventions, see:
- **[CSS Variable Naming Conventions](CSS_VARIABLE_NAMING.md)** - Complete variable naming system
- **[CSS Architecture Guide](CSS_ARCHITECTURE_GUIDE.md)** - Development methodology and best practices

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

## Related Documentation

### CSS Architecture Documentation Suite
This document is part of a comprehensive CSS architecture documentation suite:

1. **[CSS Architecture Documentation](CSS_ARCHITECTURE.md)** (This file)
   - Overview of file structure and organization
   - BEM naming conventions
   - Responsive design strategy
   - Development guidelines

2. **[CSS Architecture Guide](CSS_ARCHITECTURE_GUIDE.md)**
   - Detailed development methodology
   - Component architecture patterns
   - Performance optimization strategies
   - Theme system implementation
   - Browser support and accessibility guidelines

3. **[CSS Variable Naming Conventions](CSS_VARIABLE_NAMING.md)**
   - Complete CSS custom property naming system
   - Design token categories and hierarchies
   - Usage guidelines and best practices
   - Legacy compatibility mappings

### Quick Reference Links
- **Main CSS File**: [`main.css`](../main.css) - Main orchestrator with import order
- **Theme System**: [`public/assets/css/themes.css`](../public/assets/css/themes.css) - Color and theme tokens
- **Core Variables**: [`core/variables.css`](../core/variables.css) - Component design tokens
- **Component Library**: [`components/`](../components/) - Modular UI components

### External Resources
- [BEM Methodology](https://getbem.com/) - Official BEM documentation
- [ITCSS Architecture](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/) - Scalable CSS architecture
- [CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/--*) - MDN documentation
- [CSS Grid Layout](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Grid_Layout) - Grid system reference

---

**Last Updated**: December 2024  
**Maintainer**: Frontend Development Team  
**Version**: 2.0.0
