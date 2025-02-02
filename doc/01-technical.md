# Pablo Technical Documentation

## Overview

Pablo is a modular PHP web application framework that uses a plugin-based architecture. It requires PHP 8.4 or higher and follows modern PHP practices including strict types and namespacing.

## Core Architecture

### Entry Point (public/index.php)

The application bootstraps through `public/index.php`, which:

1. Defines the ROOT constant
2. Loads the Composer autoloader
3. Initializes configuration
4. Creates and runs the main application instance

### Initialization Process

The `Init` class (`src/Core/Init.php`) handles the application bootstrap sequence:

1. Environment validation
2. Session initialization
3. Configuration setup
4. Plugin navigation initialization
5. Theme initialization
6. Plugin execution

## Plugin System

### Plugin Structure

Each plugin resides in its own directory under `src/Plugins/` and consists of:

- `Plugin.php`: The main plugin class
- `meta.json`: Plugin configuration and metadata

### Plugin Base Class

All plugins extend the abstract `Plugin` class (`src/Core/Plugin.php`) which:

- Implements the `PluginInterface`
- Provides access to the theme instance
- Requires implementation of the `execute()` method
- Provides string conversion through `__toString()`

### Plugin Interface

The `PluginInterface` (`src/Interfaces/PluginInterface.php`) defines two methods:

- `execute(): mixed` - Main plugin logic
- `__toString(): string` - String representation

### Plugin Discovery

The `PluginScanner` class handles plugin discovery:

1. Scans the plugins directory
2. Reads each plugin's meta.json
3. Creates a navigation structure with:
   - Plugin name
   - URL (based on plugin directory name)
   - Icon
   - Display order

### Plugin Metadata (meta.json)

Each plugin includes a meta.json file with:

```json
{
  "name": "Plugin Name",
  "description": "Plugin description",
  "icon": "Bootstrap icon class",
  "order": "Navigation display order"
}
```

### Plugin Invocation Process

1. **URL Routing**

   - Plugins are invoked via the `plugin` URL parameter
   - Default plugin is "Home" if no plugin parameter is specified

2. **Plugin Loading**

   ```php
   $pluginName = ucfirst(strtolower($this->config->in['plugin'] ?? 'Home'));
   $pluginClass = "Markc\\Pablo\\Plugins\\{$pluginName}\\Plugin";
   ```

3. **Instantiation**

   - Plugin class is instantiated with Theme instance
   - Validates plugin implements PluginInterface

   ```php
   $plugin = new $pluginClass($this->theme);
   ```

4. **Execution**
   - Plugin's execute() method is called via \_\_toString()
   - Output is stored in config->out['main']
   ```php
   $this->config->out['main'] = (string)$plugin;
   ```

### Creating a New Plugin

1. Create directory: `src/Plugins/YourPlugin/`

2. Create meta.json:

```json
{
  "name": "Your Plugin",
  "description": "Plugin description",
  "icon": "bi bi-your-icon fw",
  "order": 1
}
```

3. Create Plugin.php:

```php
<?php
declare(strict_types=1);

namespace Markc\Pablo\Plugins\YourPlugin;

use Markc\Pablo\Core\Plugin as BasePlugin;

class Plugin extends BasePlugin
{
    public function execute(): mixed
    {
        // Your plugin logic here
        return "Plugin output";
    }
}
```

### Plugin Features

1. **Theme Access**

   - Plugins have access to the theme instance via `$this->theme`
   - Enables consistent styling and layout

2. **Output Formats**

   - HTML (default)
   - Text (strips HTML tags)
   - JSON
   - Partial (specific sections)

3. **API Support**

   - Plugins can handle API requests
   - CSRF protection for API endpoints
   - JSON response formatting

4. **Debug Mode**
   - Detailed error reporting when APP_DEBUG is enabled
   - Request logging
   - Performance metrics

## Security Features

1. **Session Security**

   - HTTP-only cookies
   - Strict same-site policy
   - CSRF token generation and validation

2. **Input Sanitization**

   - Configuration sanitizes input
   - Type declarations enforce data types

3. **Error Handling**
   - Production-safe error messages
   - Detailed debugging in development

## Best Practices

1. **Plugin Development**

   - Use strict types
   - Implement proper error handling
   - Follow PSR standards
   - Document your code
   - Include meaningful meta.json

2. **Security**

   - Validate all input
   - Use CSRF tokens for forms/API
   - Sanitize output
   - Follow least privilege principle

3. **Performance**
   - Keep plugins focused and lightweight
   - Cache when appropriate
   - Optimize database queries
   - Minimize dependencies
