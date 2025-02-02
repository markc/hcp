# Pablo Technical Summary

## Core Concepts

Pablo is a PHP 8.4+ web framework using a plugin-based architecture. The system consists of:

1. **Core Framework**

   - Entry point (`public/index.php`)
   - Configuration management
   - Plugin system
   - Theme handling

2. **Plugin System**

   - Plugins live in `src/Plugins/{PluginName}/`
   - Each plugin needs:
     - `Plugin.php`: Main class extending `Core\Plugin`
     - `meta.json`: Configuration (name, icon, order)
   - Plugins are invoked via `?plugin=name` URL parameter

3. **Plugin Lifecycle**

   - Discovery: `PluginScanner` finds and loads plugins
   - Initialization: Plugin instantiated with theme
   - Execution: `execute()` method runs
   - Output: Result rendered via theme

4. **Security**
   - HTTP-only cookies
   - CSRF protection
   - Input sanitization
   - Type safety
