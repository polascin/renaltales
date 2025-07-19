# RenalTales New Template System Documentation

## Overview

The RenalTales project now features an advanced, maintainable HTML template system with clear separation of presentation and business logic. This improves maintainability and enhances performance.

## Template System Features

- **Components and Partials**: Reusable components like header, footer, and navigation.
- **Template Syntax**:
  - Variable substitution using `{{variable}}`
  - Looping constructs using `{{#array}}...{{/array}}`
  - Conditional rendering with `{{#condition}}...{{/condition}}`
- **HTML Escaping**: Automatic HTML escaping for better security.
- **Template Caching**: Improves performance by reducing redundant I/O operations.

## Code Structure

- **Template Files**: Located in `resources/templates`.
- **Template Renderer**: Defined in `src/Services/TemplateRenderer.php`.
- **Interface**: `src/Contracts/TemplateRendererInterface.php`.

## Getting Started

### Setup

1. Ensure the template directory is properly set up in the configuration.
2. Use the `TemplateRenderer` class to render templates.

### Example Usage

```php
$template = new TemplateRenderer('path/to/templates');
$html = $template->render('home', ['name' => 'RenalTales']);
echo $html;
```

## Advantages

- **Separation of Concerns**: Logic and presentation layers are distinct.
- **Reusability**: Modular components enhance code reuse.
- **Performance**: Template caching and optimized file loading.

## Next Steps

- Explore partials for more complex page building.
- Use loop and conditional syntaxes to manage dynamic data.

For more detailed usage, explore the [TemplateRenderer] class and its methods.
