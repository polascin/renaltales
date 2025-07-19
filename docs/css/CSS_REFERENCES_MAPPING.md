# CSS Dependencies and References Mapping

This document analyzes all CSS dependencies and references in the RenalTales project, identifying which files are being referenced, which have hardcoded paths that need updating, any dynamic CSS loading mechanisms, and provides a comprehensive mapping of all CSS references.

## Analysis Summary

**Analysis Date:** 2025-01-22  
**Project Path:** G:\Môj disk\www\renaltales  
**Analysis Scope:** All HTML, PHP, and template files  

---

## 1. CSS Files Currently Available

### Active CSS Files (public/assets/css/)
```
public/assets/css/
├── basic.css
├── components.css  
├── error.css
├── home.css
├── home-integrated.css
├── language-switcher.css
├── language-switcher-consolidated.css
├── layout.css
├── modern-home.css
├── navigation.css
├── performance.css
├── responsive.css
├── style.css
├── theme.css
├── themes.css
└── critical/
    └── home.css
```

### Additional CSS Files (assets/css/)
```
assets/css/
└── consolidated.css
```

### Backup CSS Files (css-backup-20250719-011230/)
- Complete mirror of the above structure (backup from July 19, 2025)

---

## 2. Files with CSS References

### 2.1 Static HTML Files

#### `homepage.html`
**CSS References:**
```html
<!-- External Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Eagle+Lake&family=Funnel+Display&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&family=Winky+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

<!-- Local CSS Files -->
<link rel="stylesheet" href="/assets/css/basic.css">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/layout.css">
<link rel="stylesheet" href="/assets/css/navigation.css">
<link rel="stylesheet" href="/assets/css/language-switcher.css">
<link rel="stylesheet" href="/assets/css/modern-home.css">
<link rel="stylesheet" href="/assets/css/responsive.css">
<link rel="stylesheet" href="/assets/css/theme.css">
```

**Status:** ✅ All paths are correct and files exist
**Issues:** None - paths are properly structured

#### `test-theme-system.html`
**CSS References:**
```html
<link rel="stylesheet" href="/assets/css/basic.css">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/layout.css">
<link rel="stylesheet" href="/assets/css/theme.css">
<link rel="stylesheet" href="/assets/css/language-switcher.css">
```

**Status:** ✅ All paths are correct and files exist
**Issues:** None - paths are properly structured

#### `theme-test.html`
**CSS References:**
```html
<link rel="stylesheet" href="/assets/css/themes.css">
<link rel="stylesheet" href="/assets/css/basic.css">
<link rel="stylesheet" href="/assets/css/components.css">
<link rel="stylesheet" href="/assets/css/theme.css">
```

**Status:** ✅ All paths are correct and files exist
**Issues:** None - paths are properly structured

### 2.2 PHP View Files

#### `src/Views/HomeView.php`
**CSS Loading Mechanism:** Dynamic via CSSOptimizer helper
```php
// Dynamically generated CSS loading
$stylesheets = [
    'reset.css',      // ❌ FILE DOES NOT EXIST
    'variables.css',  // ❌ FILE DOES NOT EXIST
    'core.css',       // ❌ FILE DOES NOT EXIST
    'components.css', // ✅ EXISTS
    'utilities.css',  // ❌ FILE DOES NOT EXIST
    'performance.css',// ✅ EXISTS
    'style.css'       // ✅ EXISTS
];

$optimizedCSS = CSSOptimizer::generateCSSLinks($stylesheets, 'home');
```

**Status:** ⚠️ **CRITICAL ISSUES** - Multiple referenced CSS files don't exist
**Issues:** 
- `reset.css` - Missing
- `variables.css` - Missing  
- `core.css` - Missing
- `utilities.css` - Missing

#### `src/Views/ErrorView.php`
**CSS References:**
```html
<link rel="stylesheet" href="/assets/css/consolidated.css?v=<?php echo time(); ?>">
```

**Status:** ⚠️ **PATH ISSUE** - File exists in `assets/css/` not `public/assets/css/`
**Issues:** Path refers to `consolidated.css` which is in `assets/css/` directory, not `public/assets/css/`

### 2.3 Component Files

#### `resources/views/components/language-switcher.php`
**CSS References:** 
```html
<!-- Language Selector Styles are loaded via CSS files -->
```

**Status:** ✅ Styles loaded via separate CSS files
**Issues:** None - properly structured

#### `resources/views/components/language-switcher-enhanced.php`
**CSS References:** References external CSS files
**Status:** ✅ Styles loaded via separate CSS files  
**Issues:** None - properly structured

---

## 3. Dynamic CSS Loading Mechanisms

### 3.1 CSSOptimizer Helper Class (`src/Helpers/CSSOptimizer.php`)
**Purpose:** Dynamic CSS optimization and loading
**Methods:**
- `generateCSSLinks()` - Generates optimized CSS link tags
- `generateContainmentCSS()` - Generates containment CSS
- `generatePerformanceMonitoring()` - Performance monitoring CSS

**Issues:** Referenced CSS files don't exist in file system

### 3.2 CSSLoader Helper Class (`src/Helpers/CSSLoader.php`)  
**Purpose:** CSS loading with cache busting and asset management
**Features:**
- Cache busting with timestamps
- Consolidated CSS URL generation
- Critical CSS generation
- Font preload generation
- CSS preload functionality

**Configuration:**
```php
private const ASSETS_BASE_PATH = '/assets/css/';
private const ASSETS_PHYSICAL_PATH = 'G:\Môj disk\www\renaltales\assets\css\';
```

**Status:** ✅ Properly configured paths

---

## 4. Hardcoded Paths That Need Updating

### 4.1 ErrorView.php
**Current:** `/assets/css/consolidated.css`
**Issue:** File is located in `/assets/css/consolidated.css` 
**Fix Required:** Update path or move file to correct location

### 4.2 HomeView.php CSS Array
**Missing Files:** Need to be created or references removed:
- `reset.css`
- `variables.css` 
- `core.css`
- `utilities.css`

---

## 5. External Dependencies

### 5.1 Google Fonts
**References:** Used in `homepage.html`
```
https://fonts.googleapis.com/css2?family=Eagle+Lake&family=Funnel+Display&family=Montserrat&family=Open+Sans&family=Pacifico&family=Playfair+Display&family=Poppins&family=Roboto&family=Source+Code+Pro&family=Winky+Sans&display=swap
```

**Status:** ✅ External dependency - no local path issues

### 5.2 Font Preloads  
**Location:** `CSSLoader.php`
**Status:** ✅ Properly handled via helper class

---

## 6. Theme System Integration

### 6.1 Theme CSS Files
- `theme.css` - Base theme functionality
- `themes.css` - Theme variants and utilities  

### 6.2 Theme Switching Mechanism
**JavaScript:** `/assets/js/theme-switcher.js`
**Referenced in:** All HTML files and PHP views
**Status:** ✅ Properly integrated

---

## 7. Critical Issues Requiring Immediate Action

### 7.1 Missing CSS Files (HIGH PRIORITY)
```
❌ public/assets/css/reset.css
❌ public/assets/css/variables.css  
❌ public/assets/css/core.css
❌ public/assets/css/utilities.css
```

### 7.2 Path Mismatches (MEDIUM PRIORITY)
```
⚠️ ErrorView.php references /assets/css/consolidated.css 
   but file is in /assets/css/consolidated.css
```

### 7.3 Potential Performance Issues (LOW PRIORITY)
- Multiple CSS files loaded in HTML files could be consolidated
- Dynamic CSS generation may impact performance

---

## 8. Recommendations

### 8.1 Immediate Actions
1. **Create missing CSS files** or update HomeView.php to remove references
2. **Fix path mismatch** in ErrorView.php
3. **Test all CSS loading** on both static HTML and PHP-generated pages

### 8.2 Long-term Improvements  
1. **Implement CSS bundling** for production
2. **Add CSS minification** to build process
3. **Optimize critical CSS** loading
4. **Implement CSS versioning** strategy

### 8.3 Build Process Integration
1. **Add CSS validation** to build pipeline
2. **Implement automatic CSS optimization**
3. **Add missing file detection** to CI/CD

---

## 9. File Reference Matrix

| File | CSS References | Status | Issues |
|------|---------------|--------|--------|
| `homepage.html` | 8 local + 1 external | ✅ Good | None |
| `test-theme-system.html` | 5 local | ✅ Good | None |  
| `theme-test.html` | 4 local | ✅ Good | None |
| `HomeView.php` | 7 dynamic | ❌ Critical | 4 missing files |
| `ErrorView.php` | 1 local | ⚠️ Warning | Path mismatch |
| `language-switcher.php` | External refs | ✅ Good | None |
| `language-switcher-enhanced.php` | External refs | ✅ Good | None |

---

## 10. CSS Architecture Overview

```
📁 public/assets/css/
├── 📄 Core Styles
│   ├── basic.css           ✅ EXISTS
│   ├── style.css           ✅ EXISTS  
│   ├── components.css      ✅ EXISTS
│   └── layout.css          ✅ EXISTS
├── 📄 Theme System
│   ├── theme.css           ✅ EXISTS
│   └── themes.css          ✅ EXISTS
├── 📄 Feature Specific
│   ├── navigation.css      ✅ EXISTS
│   ├── language-switcher.css ✅ EXISTS
│   ├── home.css           ✅ EXISTS
│   ├── modern-home.css    ✅ EXISTS
│   └── responsive.css     ✅ EXISTS
├── 📄 Utilities
│   ├── error.css          ✅ EXISTS
│   └── performance.css    ✅ EXISTS
├── 📄 Consolidated
│   ├── home-integrated.css ✅ EXISTS
│   └── language-switcher-consolidated.css ✅ EXISTS
└── 📁 critical/
    └── home.css           ✅ EXISTS

📁 assets/css/ 
└── consolidated.css       ✅ EXISTS (Alternative location)
```

---

*Analysis completed on 2025-01-22*  
*Next review recommended: After fixing critical issues*
