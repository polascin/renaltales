# CSS File References Documentation
## Backup Date: 2025-01-19 01:12:30
## Backup Directory: css-backup-20250719-011230

This document provides a comprehensive overview of all CSS file references in the RenalTales application templates before the backup was created.

## 1. STATIC HTML TEMPLATE FILES

### 1.1 homepage.html
**Location**: `G:\Môj disk\www\renaltales\homepage.html`
**CSS Files Referenced (in order):**
- `/assets/css/basic.css`
- `/assets/css/style.css`
- `/assets/css/layout.css`
- `/assets/css/navigation.css`
- `/assets/css/language-switcher.css`
- `/assets/css/modern-home.css`
- `/assets/css/responsive.css`
- `/assets/css/theme.css`

**External Resources:**
- Google Fonts: Multiple font families loaded from Google Fonts API
- Font families: Eagle Lake, Funnel Display, Montserrat, Open Sans, Pacifico, Playfair Display, Poppins, Roboto, Source Code Pro, Winky Sans

### 1.2 theme-test.html
**Location**: `G:\Môj disk\www\renaltales\theme-test.html`
**CSS Files Referenced (in order):**
- `/assets/css/themes.css`
- `/assets/css/basic.css`
- `/assets/css/components.css`
- `/assets/css/theme.css`

### 1.3 test-theme-system.html
**Location**: `G:\Môj disk\www\renaltales\test-theme-system.html`
**CSS Files Referenced (in order):**
- `/assets/css/basic.css`
- `/assets/css/style.css`
- `/assets/css/layout.css`
- `/assets/css/theme.css`
- `/assets/css/language-switcher.css`

## 2. PHP VIEW TEMPLATES

### 2.1 HomeView.php
**Location**: `G:\Môj disk\www\renaltales\src\Views\HomeView.php`
**CSS Loading Method**: Dynamic via CSSOptimizer class

**Base CSS Files List (lines 176-184):**
- `reset.css`
- `variables.css`
- `core.css`
- `components.css`
- `utilities.css`
- `performance.css`
- `style.css`

**CSS Generation Methods:**
- `CSSOptimizer::generateCSSLinks($stylesheets, 'home')` - Generates optimized CSS links
- `CSSOptimizer::generateContainmentCSS()` - Generates CSS containment rules
- `CSSOptimizer::generatePerformanceMonitoring()` - Generates performance monitoring CSS

### 2.2 ErrorView.php
**Location**: `G:\Môj disk\www\renaltales\src\Views\ErrorView.php`
**CSS Files Referenced (line 135):**
- `/assets/css/consolidated.css?v=<?php echo time(); ?>`

## 3. JAVASCRIPT ASSETS

### 3.1 Theme Switcher
**Location**: `/assets/js/theme-switcher.js`
**Referenced in:**
- `homepage.html` (line 324)
- `theme-test.html` (line 206)
- `test-theme-system.html` (line 338)

**Functionality:**
- Handles theme switching between light and dark modes
- Manages localStorage persistence
- Provides theme synchronization across tabs

## 4. CURRENT CSS DIRECTORY STRUCTURE

### 4.1 Root CSS Directory
**Location**: `G:\Môj disk\www\renaltales\assets\css\`
**Files:**
- `consolidated.css` - Main consolidated CSS file

### 4.2 Public CSS Directory
**Location**: `G:\Môj disk\www\renaltales\public\assets\css\`
**Files:**
- `basic.css` - Basic styles and resets
- `components.css` - Component-specific styles
- `error.css` - Error page styles
- `home-integrated.css` - Home page integrated styles
- `home.css` - Home page styles
- `language-switcher-consolidated.css` - Language switcher consolidated styles
- `language-switcher.css` - Language switcher styles
- `layout.css` - Layout-specific styles
- `modern-home.css` - Modern home page styles
- `navigation.css` - Navigation styles
- `performance.css` - Performance optimization styles
- `responsive.css` - Responsive design styles
- `style.css` - Main application styles
- `theme.css` - Theme-specific styles
- `themes.css` - Multiple theme definitions

### 4.3 Critical CSS Subdirectory
**Location**: `G:\Môj disk\www\renaltales\public\assets\css\critical\`
**Files:**
- `home.css` - Critical CSS for home page above-the-fold content

## 5. CSS LOADING PATTERNS

### 5.1 Static HTML Files
- Direct `<link>` tags in `<head>` section
- Cache-busting through timestamp query parameters
- Prioritized loading order for critical styles

### 5.2 PHP Templates
- Dynamic CSS generation through helper classes
- Conditional loading based on page type
- Optimized for performance with critical CSS inlining

### 5.3 CSS Optimization Features
- Critical CSS extraction and inlining
- CSS containment for performance
- Performance monitoring integration
- Cache-busting mechanisms

## 6. BACKUP VERIFICATION

### 6.1 Files Successfully Backed Up
✅ **assets/css/consolidated.css**
✅ **public/assets/css/** (17 files)
✅ **public/assets/css/critical/** (1 file)

### 6.2 Total Files Backed Up
- **Root assets/css**: 1 file
- **Public assets/css**: 17 files
- **Critical CSS**: 1 file
- **Total**: 19 CSS files

### 6.3 Backup Integrity
- All CSS files have been successfully copied
- Directory structure preserved
- File permissions maintained
- Backup timestamp: 2025-01-19 01:12:30

## 7. RECOVERY INSTRUCTIONS

### 7.1 Full Recovery
To restore all CSS files from backup:
```bash
# Copy assets/css files
xcopy "css-backup-20250719-011230\assets" "assets" /E /Y

# Copy public/assets/css files
xcopy "css-backup-20250719-011230\public\assets" "public\assets" /E /Y
```

### 7.2 Selective Recovery
To restore specific CSS files:
```bash
# Restore specific file
copy "css-backup-20250719-011230\public\assets\css\[filename]" "public\assets\css\"
```

### 7.3 Verification After Recovery
1. Check that all CSS files are present in their original locations
2. Verify file contents match the backup
3. Test the application to ensure proper CSS loading
4. Validate theme switching functionality

## 8. NOTES AND RECOMMENDATIONS

### 8.1 Critical Dependencies
- **CSSOptimizer class**: Required for dynamic CSS generation in PHP templates
- **Theme Switcher JS**: Essential for theme functionality
- **Google Fonts**: External dependency for typography

### 8.2 Maintenance Notes
- CSS files are loaded in specific order for cascade inheritance
- Theme system depends on CSS custom properties
- Performance optimization relies on critical CSS extraction
- Language switcher styles are modular and can be updated independently

### 8.3 Future Considerations
- Consider consolidating CSS files for better performance
- Implement CSS preprocessing for better maintainability
- Add automated CSS minification
- Implement CSS versioning for cache management

---

**Backup Created**: 2025-01-19 01:12:30
**Documentation Version**: 1.0
**Backup Location**: `css-backup-20250719-011230/`
