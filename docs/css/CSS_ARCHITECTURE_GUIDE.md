# CSS Architecture Guide - Renal Tales

## Overview
This guide explains the CSS architecture philosophy, file organization, and development methodology for the Renal Tales project. Our architecture is designed for scalability, maintainability, and performance while following modern best practices.

## Architecture Philosophy

### Core Principles
1. **Modular Design**: Each component is self-contained and reusable
2. **Progressive Enhancement**: Mobile-first, performance-focused approach
3. **Semantic Naming**: BEM methodology for clear, consistent naming
4. **Design Tokens**: CSS custom properties for consistent theming
5. **Maintainability**: Clear separation of concerns and documentation

### ITCSS Influence
Our architecture is inspired by Inverted Triangle CSS (ITCSS) methodology:
- **Generic**: Reset and normalize styles
- **Base**: Typography and basic elements
- **Components**: UI components and patterns
- **Utilities**: Helper classes and overrides

## File Organization Structure

### Directory Layout
```
renaltales/
├── main.css                 # Main orchestrator file
├── core/                    # Core foundation files
│   ├── variables.css        # Component-specific design tokens
│   ├── reset.css           # CSS normalization and reset
│   ├── typography.css      # Typography system
│   └── responsive-mixins.css # Responsive utilities
├── layout/                  # Layout system
│   └── spacing.css         # Spacing utilities
├── components/             # Component library
│   ├── buttons.css         # Button components
│   ├── forms.css           # Form components
│   ├── cards.css           # Card components
│   ├── navigation.css      # Navigation components
│   └── modern-enhancements.css # Modern component features
├── public/assets/css/      # Application and legacy styles
│   ├── themes.css          # Theme system (colors, fonts)
│   ├── basic.css           # Base application styles
│   ├── layout.css          # Main layout containers
│   ├── components.css      # Legacy component compatibility
│   ├── style.css           # General application styles
│   ├── navigation.css      # Navigation-specific styles
│   ├── modern-home.css     # Modern home page styles
│   ├── error.css           # Error page styles
│   ├── responsive.css      # Responsive breakpoints
│   └── performance.css     # Performance optimizations
└── docs/                   # Documentation
    ├── CSS_ARCHITECTURE.md
    ├── CSS_VARIABLE_NAMING.md
    └── CSS_ARCHITECTURE_GUIDE.md
```

## Import Strategy

### Cascade Order (main.css)
The import order in `main.css` follows the cascade hierarchy:

1. **Theme Foundation**: Color tokens and global variables
2. **Core Variables**: Component-specific design tokens
3. **Reset & Normalize**: CSS reset for consistent baseline
4. **Typography**: Font definitions and text styles
5. **Layout System**: Grid, containers, and spacing
6. **Base Styles**: Basic element styles
7. **Components**: Modular UI components
8. **Page-Specific**: Application-specific styles
9. **Utilities**: Helper classes and responsive utilities
10. **Overrides**: Component-specific adjustments

### Critical CSS Strategy
- **Above-the-fold**: Inline critical styles in HTML head
- **Deferred loading**: Load non-critical CSS asynchronously
- **Progressive enhancement**: Layer additional styles progressively

## Component Architecture

### BEM Methodology
We use Block, Element, Modifier (BEM) naming convention:

```css
/* Block: Independent component */
.card { }

/* Element: Child component */
.card__header { }
.card__body { }
.card__footer { }

/* Modifier: Variation or state */
.card--featured { }
.card--compact { }
.card__header--dark { }
```

### Component Structure
Each component file follows this pattern:
```css
/* Component Name: Button
 * Purpose: Interactive buttons with variants
 * Dependencies: themes.css, variables.css
 */

/* ==========================================================================
   BASE COMPONENT
   ========================================================================== */

.button {
  /* Base styles */
}

/* ==========================================================================
   ELEMENTS
   ========================================================================== */

.button__icon { }
.button__text { }

/* ==========================================================================
   MODIFIERS
   ========================================================================== */

.button--primary { }
.button--secondary { }
.button--large { }
.button--disabled { }

/* ==========================================================================
   STATES
   ========================================================================== */

.button:hover { }
.button:focus { }
.button:active { }
.button[disabled] { }

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 768px) {
  .button--large {
    /* Mobile adjustments */
  }
}
```

## Design Token System

### Token Hierarchy
1. **Global Tokens** (themes.css): Base colors, typography, spacing
2. **Semantic Tokens** (themes.css): Component-specific aliases
3. **Component Tokens** (variables.css): Extended design tokens
4. **Local Tokens** (component files): Component-specific variables

### Token Categories
- **Colors**: `--color-{category}-{scale|semantic}`
- **Typography**: `--font-{property}-{value}`
- **Spacing**: `--space-{scale}`
- **Layout**: `--radius-{scale}`, `--shadow-{scale}`
- **Animation**: `--transition-{speed}`

### Usage Examples
```css
/* Good: Using semantic tokens */
.card {
  background-color: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: var(--radius-md);
  box-shadow: var(--card-shadow);
  color: var(--color-text);
}

/* Good: Using scale tokens for spacing */
.section {
  padding: var(--space-lg) var(--space-md);
  margin-bottom: var(--space-2xl);
}
```

## Responsive Design Strategy

### Mobile-First Approach
All styles are written mobile-first with progressive enhancement:

```css
/* Mobile styles (base) */
.navigation {
  display: block;
}

/* Tablet and up */
@media (min-width: 768px) {
  .navigation {
    display: flex;
  }
}

/* Desktop and up */
@media (min-width: 1024px) {
  .navigation {
    justify-content: space-between;
  }
}
```

### Breakpoint Strategy
```css
/* Standard breakpoints */
@media (max-width: 320px)  { /* Very small screens */ }
@media (min-width: 321px) and (max-width: 480px) { /* Small screens */ }
@media (min-width: 481px) and (max-width: 768px) { /* Medium screens */ }
@media (min-width: 769px) and (max-width: 1024px) { /* Large screens */ }
@media (min-width: 1025px) { /* Extra large screens */ }
```

## Performance Optimization

### Loading Strategy
1. **Critical CSS**: Inline essential styles (≤14KB)
2. **Component CSS**: Load on-demand per page
3. **Utility CSS**: Load for interactive components
4. **Theme CSS**: Preload for theme switching

### File Size Management
- **Minimize imports**: Only load needed components
- **Tree shaking**: Remove unused styles in build process
- **Compression**: Use gzip/brotli compression
- **Caching**: Implement cache-busting for updates

### CSS Optimization Techniques
```css
/* Use efficient selectors */
.button { } /* Good: Single class */
.card .button { } /* Good: Limited nesting */
.page .section .content .card .button { } /* Bad: Too specific */

/* Minimize repaints and reflows */
.smooth-animation {
  transform: translateX(100px); /* Good: Composite layer */
  left: 100px; /* Bad: Causes reflow */
}
```

## Theme System

### Light/Dark Mode Support
```css
/* Light theme (default) */
:root,
[data-theme="light"] {
  --color-background: #ffffff;
  --color-text: #1f2937;
}

/* Dark theme */
[data-theme="dark"] {
  --color-background: #1f2937;
  --color-text: #f9fafb;
}

/* System preference support */
@media (prefers-color-scheme: dark) {
  :root:not([data-theme]) {
    --color-background: #1f2937;
    --color-text: #f9fafb;
  }
}
```

### Theme Implementation
- **CSS Custom Properties**: All theme values use CSS variables
- **JavaScript Toggle**: Theme switching via data attributes
- **System Preference**: Respects user's OS theme preference
- **Persistence**: Theme choice saved in localStorage

## Accessibility Considerations

### Color Contrast
```css
/* Ensure proper contrast ratios */
:root {
  --color-text: #1f2937;        /* 4.5:1 ratio minimum */
  --color-text-muted: #6b7280;  /* 3:1 ratio for large text */
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  :root {
    --color-border: #000000;
    --color-text: #000000;
  }
}
```

### Focus Management
```css
/* Visible focus indicators */
.button:focus {
  outline: 2px solid var(--color-border-focus);
  outline-offset: 2px;
}

/* Focus-visible for keyboard navigation */
.button:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}
```

### Reduced Motion Support
```css
/* Respect user motion preferences */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

## Development Workflow

### Adding New Components
1. **Create component file** in `components/` directory
2. **Follow BEM naming** convention consistently
3. **Use design tokens** from existing variables
4. **Add responsive styles** with mobile-first approach
5. **Document component** with comments and examples
6. **Import in main.css** at appropriate cascade level
7. **Test accessibility** and browser compatibility

### CSS Authoring Guidelines

#### Do's ✅
- Use semantic class names that describe purpose
- Leverage existing design tokens and variables
- Write mobile-first responsive styles
- Document complex selectors and browser-specific code
- Follow consistent indentation and formatting
- Use logical property names (margin-inline vs margin-left)

#### Don'ts ❌
- Don't use overly specific selectors
- Don't hardcode values that should use design tokens
- Don't nest selectors more than 3 levels deep
- Don't use `!important` unless absolutely necessary
- Don't write desktop-first responsive styles

### Code Quality Tools
- **Stylelint**: CSS linting for consistency and best practices
- **Prettier**: Code formatting for consistent style
- **PostCSS**: Modern CSS features and optimizations
- **Browser testing**: Cross-browser compatibility verification

## Maintenance and Updates

### Updating Design Tokens
1. **Update base tokens** in `themes.css` or `variables.css`
2. **Check component usage** for breaking changes
3. **Update documentation** with new token information
4. **Test theme variations** (light/dark mode)
5. **Verify responsive behavior** across breakpoints

### Adding New Themes
1. **Create new theme block** in `themes.css`
2. **Define all required color tokens**
3. **Test component compatibility**
4. **Update theme toggle functionality**
5. **Document new theme usage**

### Performance Monitoring
- **Monitor CSS bundle size** and loading performance
- **Audit unused CSS** and remove dead code
- **Optimize critical CSS** for faster initial renders
- **Test on slower devices** and connections

## Browser Support Strategy

### Target Browsers
- **Modern browsers**: Chrome 88+, Firefox 85+, Safari 14+, Edge 88+
- **CSS features**: Custom properties, Grid, Flexbox, modern selectors
- **Fallback strategy**: Progressive enhancement for older browsers

### Feature Detection
```css
/* CSS Grid with Flexbox fallback */
.layout {
  display: flex; /* Fallback */
  display: grid; /* Modern */
}

/* CSS custom properties with fallbacks */
.component {
  color: #3b82f6; /* Fallback */
  color: var(--color-primary); /* Modern */
}
```

## Documentation Standards

### Component Documentation
Each component should include:
- **Purpose and use cases**
- **Available modifiers and states**
- **Dependencies and requirements**
- **Usage examples**
- **Accessibility considerations**
- **Browser support notes**

### Code Comments
```css
/* ==========================================================================
   COMPONENT NAME: Button
   ========================================================================== 
   
   Purpose: Interactive button component with multiple variants
   Dependencies: themes.css, variables.css
   
   Modifiers:
   - .button--primary: Primary action button
   - .button--secondary: Secondary action button
   - .button--large: Larger button size
   
   States:
   - :hover: Hover interaction
   - :focus: Keyboard focus
   - :active: Click/press state
   - [disabled]: Disabled state
   ========================================================================== */

.button {
  /* Base button styles */
}

/* Complex selector explanation */
.button:focus:not(:focus-visible) {
  /* Remove focus ring for mouse users while maintaining accessibility */
  outline: none;
}
```

## Future Considerations

### Potential Enhancements
- **CSS Modules**: For component-scoped styles
- **CSS-in-JS**: For dynamic component styling
- **Container Queries**: For component-based responsive design
- **CSS Layers**: For better cascade management
- **View Transitions API**: For smooth page transitions

### Migration Strategies
- **Gradual adoption**: Implement new patterns incrementally
- **Backward compatibility**: Maintain support during transitions
- **Documentation updates**: Keep guides current with changes
- **Team training**: Ensure consistent adoption of new patterns

---

**Last Updated**: December 2024  
**Maintainer**: Frontend Development Team  
**Version**: 1.0.0
