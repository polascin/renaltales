# RenalTales Template Style Guide

## Overview

This style guide aims to set standard conventions and best practices for developing and maintaining templates across the RenalTales application.

---

## Naming Conventions

### Template Files

- Use `kebab-case` for file names, e.g., `user-profile.html`, `contact-form.html`.
- Be descriptive: Use meaningful names that reflect the content or purpose, e.g., `error-404.html`.

### Variables

- Use `snake_case` for variable names, e.g., `user_name`, `page_title`.
- Be consistent: Use the same naming convention throughout the application.

### Components

- Name components descriptively, using `kebab-case`, e.g., `user-avatar`, `form-error-message`.
- Indicate purpose clearly: Use names that convey the function or usage of the component.

---

## File Organization

### Directories

- Group related components together.
- Use directories to separate different concerns, such as `components/`, `layouts/`, `partials/`.

### Template Structure

- Templates should be stored under `resources/templates/`.
- Use a clear and consistent folder structure, e.g.,:
  ```
  resources/templates/
  ├── layouts/           # Base layouts
  ├── pages/             # Complete pages
  ├── components/        # Reusable components
  └── partials/          # Small, specific parts
  ```

---

## Code Quality

### Readability

- Write clean, readable code.
- Use comments to explain complex logic.
- Keep consistent indentation and spacing.

### Maintainability

- Keep the code DRY (Don't Repeat Yourself).
- Break down complex templates into reusable components or partials.
- Keep changes minimal and specific to each template.

---

## HTML & Templating

### Best Practices

- Follow semantic HTML standards.
- Use template syntax consistently across files.
- Utilize components for reusable patterns and avoid duplication.

### Security Concerns

- Ensure data passed to templates is properly sanitized and escaped.
- Use built-in security features to prevent XSS and other vulnerabilities.

---

## Performance Optimization

### Caching

- Utilize built-in caching mechanisms for templates and partials.
- Avoid complex logic inside templates; pre-process data in controllers.

### Minimization

- Minimize dependency on external resources by inlining critical CSS.
- Use async/defer for resource-heavy script loading.

---

## Testing & Debugging

### Testing Templates

- Write tests for essential templates/components to ensure correct rendering.
- Use automated tools and scripts to validate HTML/CSS.

---

**End of Style Guide**

This guide will assist developers in maintaining consistency and quality across all templates in the RenalTales project, ensuring maintainable and scalable code.
