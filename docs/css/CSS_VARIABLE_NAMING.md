# CSS Variable Naming Conventions - Renal Tales

## Overview
This document outlines the comprehensive naming conventions for CSS custom properties (variables) used throughout the Renal Tales project. Following these conventions ensures consistency, maintainability, and scalability across the codebase.

## Naming Structure

### Basic Pattern
```
--{category}-{property}-{modifier}
```

### Examples
- `--color-primary-500` 
- `--space-lg`
- `--font-family-sans`
- `--shadow-md`

## Color System

### Primary Color Scale
```css
/* Base color scale (50-950) */
--color-primary-50     /* Lightest tint */
--color-primary-100
--color-primary-200
--color-primary-300
--color-primary-400
--color-primary-500    /* Base/brand color */
--color-primary-600
--color-primary-700
--color-primary-800
--color-primary-900
--color-primary-950    /* Darkest shade */
```

### Semantic Color Tokens
```css
/* Semantic aliases for easier usage */
--color-primary         /* Maps to --color-primary-500 */
--color-primary-hover   /* Maps to --color-primary-600 */
--color-primary-active  /* Maps to --color-primary-700 */
--color-primary-light   /* Maps to --color-primary-100 */
--color-primary-lighter /* Maps to --color-primary-50 */
```

### Color Categories
- **Primary**: `--color-primary-{scale}`
- **Secondary**: `--color-secondary-{scale}`
- **Accent**: `--color-accent-{scale}`
- **Neutral**: `--color-neutral-{scale}`
- **Semantic**: `--color-{success|warning|danger|info}`

### Surface & Background Colors
```css
--color-background          /* Main page background */
--color-background-secondary /* Secondary backgrounds */
--color-surface            /* Card/modal backgrounds */
--color-surface-hover      /* Hover states */
--color-surface-active     /* Active states */
```

### Text Colors
```css
--color-text               /* Primary text */
--color-text-secondary     /* Secondary text */
--color-text-tertiary      /* Muted text */
--color-text-inverse       /* Text on dark backgrounds */
--color-text-disabled      /* Disabled text */
--color-text-link          /* Link text */
--color-text-link-hover    /* Link hover state */
```

### Border Colors
```css
--color-border             /* Default borders */
--color-border-secondary   /* Secondary borders */
--color-border-focus       /* Focus ring color */
--color-border-error       /* Error state borders */
```

## Typography System

### Font Families
```css
--font-family-sans         /* Primary sans-serif */
--font-family-serif        /* Serif fonts */
--font-family-mono         /* Monospace fonts */
--font-family-cursive      /* Cursive/script fonts */
--font-family-fantasy      /* Display/fantasy fonts */
--font-family-system       /* System fonts */
```

### Font Sizes (Modular Scale)
```css
--font-size-xs             /* Extra small */
--font-size-sm             /* Small */
--font-size-base           /* Base size (16px) */
--font-size-md             /* Medium */
--font-size-lg             /* Large */
--font-size-xl             /* Extra large */
--font-size-2xl            /* 2x large */
--font-size-3xl            /* 3x large */
--font-size-4xl            /* 4x large */
```

### Line Heights
```css
--line-height-none         /* 1 */
--line-height-tight        /* 1.25 */
--line-height-snug         /* 1.375 */
--line-height-normal       /* 1.5 */
--line-height-relaxed      /* 1.625 */
--line-height-loose        /* 2 */
```

## Spacing System

### Spacing Scale
```css
--space-xs                 /* 0.25rem / 4px */
--space-sm                 /* 0.5rem / 8px */
--space-md                 /* 1rem / 16px */
--space-lg                 /* 1.5rem / 24px */
--space-xl                 /* 2rem / 32px */
--space-2xl                /* 3rem / 48px */
--space-3xl                /* 4rem / 64px */
```

### Component-Specific Spacing
```css
--space-base               /* Base unit for modular scale */
--space-xxs                /* Extra small (2px) */
--space-4xl                /* 4x large */
--space-5xl                /* 5x large */
```

## Layout & Structure

### Border Radius
```css
--radius-sm                /* Small radius (4px) */
--radius-md                /* Medium radius (6px) */
--radius-lg                /* Large radius (8px) */
--radius-xl                /* Extra large radius (12px) */
--radius-2xl               /* 2x large radius (16px) */
--radius-full              /* Full/pill radius (9999px) */
```

### Shadows
```css
--shadow-sm                /* Small shadow */
--shadow-md                /* Medium shadow */
--shadow-lg                /* Large shadow */
--shadow-xl                /* Extra large shadow */
--shadow-color             /* Shadow color token */
--shadow-color-strong      /* Strong shadow color */
```

### Component-Specific Shadows
```css
--shadow-primary           /* Primary color shadow */
--shadow-success           /* Success color shadow */
--shadow-warning           /* Warning color shadow */
--shadow-error             /* Error color shadow */
--shadow-floating          /* Floating element shadow */
--shadow-inner             /* Inset shadow */
```

### Grid System
```css
--grid-columns             /* Grid column count */
--grid-gutter              /* Grid gutter spacing */
```

## Animation & Transitions

### Transition Durations
```css
--transition-fast          /* 150ms */
--transition-normal        /* 300ms */
--transition-slow          /* 500ms */
--transition-theme         /* Theme change transition */
```

### Border Widths
```css
--border-width-thin        /* 1px (default) */
--border-width-medium      /* 2px */
--border-width-thick       /* 4px */
```

## Component Tokens

### Buttons
```css
--button-primary-bg        /* Primary button background */
--button-primary-bg-hover  /* Primary button hover */
--button-primary-text      /* Primary button text */
--button-secondary-bg      /* Secondary button background */
```

### Cards
```css
--card-bg                  /* Card background */
--card-border              /* Card border */
--card-shadow              /* Card shadow */
```

### Forms
```css
--input-bg                 /* Input background */
--input-bg-focus           /* Input focus background */
--input-border             /* Input border */
--input-border-focus       /* Input focus border */
--input-text               /* Input text color */
--input-placeholder        /* Placeholder text color */
```

### Navigation
```css
--nav-bg                   /* Navigation background */
--nav-border               /* Navigation border */
--nav-text                 /* Navigation text */
--nav-text-hover           /* Navigation text hover */
--nav-text-active          /* Navigation text active */
```

## Legacy Compatibility

### Legacy Mappings
For backward compatibility, legacy variable names are mapped to new tokens:
```css
--primary-color            /* Maps to --color-primary */
--accent-color             /* Maps to --color-accent */
--background-color         /* Maps to --color-background */
--text-color               /* Maps to --color-text */
--border-color             /* Maps to --color-border */
```

## Usage Guidelines

### Do's
✅ Use semantic tokens when possible:
```css
/* Good */
color: var(--color-text-secondary);
background: var(--button-primary-bg);
```

✅ Use scale values for consistent spacing:
```css
/* Good */
margin: var(--space-lg) var(--space-md);
```

### Don'ts
❌ Don't hardcode colors:
```css
/* Bad */
color: #333333;
background: #3b82f6;
```

❌ Don't use scale values for semantic purposes:
```css
/* Bad - use semantic tokens instead */
color: var(--color-primary-500);
```

## File Organization

### Variables by File
- **themes.css**: All color tokens, font families, basic spacing
- **core/variables.css**: Component-specific tokens, extended spacing, shadows
- Component files: Only component-specific local variables

### Token Hierarchy
1. **Global tokens** (themes.css): Base scale and semantic colors
2. **Component tokens** (variables.css): Component-specific design tokens
3. **Local tokens** (component files): Component-specific overrides

## Naming Best Practices

### Consistency Rules
1. Always use kebab-case: `--font-size-lg`
2. Use consistent scale naming: `xs`, `sm`, `md`, `lg`, `xl`, `2xl`, etc.
3. Use semantic names for component tokens: `--button-primary-bg`
4. Group related tokens with prefixes: `--color-`, `--space-`, `--font-`

### Scale Conventions
- **Size scales**: `xs`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`, `4xl`
- **Color scales**: `50`, `100`, `200`, `300`, `400`, `500`, `600`, `700`, `800`, `900`, `950`
- **Semantic modifiers**: `hover`, `active`, `focus`, `disabled`

## Documentation Updates

When adding new variables:
1. Document the purpose and usage
2. Include in the appropriate category
3. Update any related semantic tokens
4. Add examples of proper usage
5. Update the legacy mapping if needed

---

**Last Updated**: December 2024  
**Maintainer**: Frontend Development Team  
**Version**: 1.0.0
