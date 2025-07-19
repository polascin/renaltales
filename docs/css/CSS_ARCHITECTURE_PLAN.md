# Modern CSS Architecture Plan

## File Structure Overview

```
css/
├── core/
│   ├── variables.css      # Design tokens and CSS custom properties
│   ├── reset.css          # CSS reset and normalization
│   └── typography.css     # Typography styles and utility classes
├── layout/
│   ├── grid.css           # Grid system and layout utilities
│   ├── containers.css     # Container styles and max-widths
│   └── spacing.css        # Margin and padding utilities
├── components/
│   ├── buttons.css        # Button variants and states
│   ├── forms.css          # Form elements and validation styles
│   ├── cards.css          # Card component variants
│   └── navigation.css     # Navigation and menu components
├── utilities/
│   ├── helpers.css        # Helper classes and utilities
│   └── responsive.css     # Responsive utilities and visibility classes
├── themes/
│   ├── light.css          # Light theme variables
│   └── dark.css           # Dark theme variables
└── main.css               # Main import orchestrator
```

## Component Naming Convention (BEM)

### BEM Methodology
- **Block**: Independent component (e.g., `.card`, `.button`, `.navigation`)
- **Element**: Part of a block (e.g., `.card__header`, `.button__icon`)
- **Modifier**: Variation of block or element (e.g., `.card--featured`, `.button--primary`)

### Naming Rules
```css
/* Block */
.block-name { }

/* Element */
.block-name__element-name { }

/* Modifier */
.block-name--modifier-name { }
.block-name__element-name--modifier-name { }
```

### Examples
```css
/* Card Component */
.card { }
.card__header { }
.card__body { }
.card__footer { }
.card--featured { }
.card--compact { }
.card__header--dark { }

/* Button Component */
.button { }
.button__icon { }
.button__text { }
.button--primary { }
.button--secondary { }
.button--large { }
.button--disabled { }
```

## Modular Scale System

### Typography Scale (1.250 - Major Third)
```css
/* Base: 16px */
--font-size-xs: 0.64rem;   /* 10.24px */
--font-size-sm: 0.8rem;    /* 12.8px */
--font-size-base: 1rem;    /* 16px */
--font-size-md: 1.25rem;   /* 20px */
--font-size-lg: 1.563rem;  /* 25px */
--font-size-xl: 1.953rem;  /* 31.25px */
--font-size-2xl: 2.441rem; /* 39.06px */
--font-size-3xl: 3.052rem; /* 48.83px */
--font-size-4xl: 3.815rem; /* 61.04px */
```

### Spacing Scale (1.333 - Perfect Fourth)
```css
/* Base: 8px */
--space-xs: 0.375rem;      /* 6px */
--space-sm: 0.5rem;        /* 8px */
--space-base: 0.667rem;    /* 10.67px */
--space-md: 0.889rem;      /* 14.22px */
--space-lg: 1.185rem;      /* 18.96px */
--space-xl: 1.58rem;       /* 25.28px */
--space-2xl: 2.107rem;     /* 33.71px */
--space-3xl: 2.809rem;     /* 44.94px */
--space-4xl: 3.745rem;     /* 59.92px */
--space-5xl: 4.993rem;     /* 79.89px */
```

## Breakpoint Strategy

### Mobile-First Approach
```css
/* Breakpoint Variables */
--breakpoint-sm: 576px;    /* Small devices (landscape phones) */
--breakpoint-md: 768px;    /* Medium devices (tablets) */
--breakpoint-lg: 992px;    /* Large devices (desktops) */
--breakpoint-xl: 1200px;   /* Extra large devices (large desktops) */
--breakpoint-2xl: 1400px;  /* Extra extra large devices */
```

### Media Query Mixins (if using preprocessor)
```scss
@mixin sm-up {
  @media (min-width: 576px) { @content; }
}

@mixin md-up {
  @media (min-width: 768px) { @content; }
}

@mixin lg-up {
  @media (min-width: 992px) { @content; }
}

@mixin xl-up {
  @media (min-width: 1200px) { @content; }
}

@mixin 2xl-up {
  @media (min-width: 1400px) { @content; }
}
```

### CSS Custom Media Queries (Future Standard)
```css
@custom-media --sm-up (min-width: 576px);
@custom-media --md-up (min-width: 768px);
@custom-media --lg-up (min-width: 992px);
@custom-media --xl-up (min-width: 1200px);
@custom-media --2xl-up (min-width: 1400px);
```

## Design Token Categories

### Color Tokens
```css
/* Primary Colors */
--color-primary-50: #f0f9ff;
--color-primary-500: #3b82f6;
--color-primary-900: #1e3a8a;

/* Semantic Colors */
--color-success: #10b981;
--color-warning: #f59e0b;
--color-error: #ef4444;
--color-info: #06b6d4;

/* Neutral Colors */
--color-gray-50: #f9fafb;
--color-gray-500: #6b7280;
--color-gray-900: #111827;
```

### Component Architecture Guidelines

#### 1. Composition over Inheritance
- Use utility classes for spacing and layout
- Create specific component classes for styling
- Avoid deep nesting (max 3 levels)

#### 2. Naming Conventions
- Use lowercase with hyphens for CSS classes
- Be descriptive but concise
- Follow BEM methodology consistently

#### 3. File Organization
- One component per file in components/
- Group related utilities in utilities/
- Keep core styles minimal and focused

#### 4. Performance Considerations
- Load critical CSS inline
- Use CSS custom properties for theming
- Minimize specificity conflicts
- Optimize for CSS compression

## Implementation Order

1. **Phase 1**: Core Foundation
   - Set up variables.css with design tokens
   - Implement reset.css
   - Create basic typography.css

2. **Phase 2**: Layout System
   - Build grid.css system
   - Create containers.css
   - Implement spacing.css utilities

3. **Phase 3**: Components
   - Start with buttons.css (most reusable)
   - Add forms.css
   - Implement cards.css and navigation.css

4. **Phase 4**: Utilities & Themes
   - Add helpers.css and responsive.css
   - Create theme files
   - Set up main.css orchestrator

## Benefits of This Architecture

- **Scalable**: Easy to add new components without conflicts
- **Maintainable**: Clear organization and naming conventions
- **Performant**: Modular loading and minimal specificity
- **Flexible**: Easy theming and customization
- **Developer-Friendly**: Intuitive structure and documentation
