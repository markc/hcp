# Theme Selection and Session Variable Handling in HCP Framework

## Overview
This document explains the interaction between initialization variables, session handling, and theme selection in the HCP Framework, specifically focusing on how theme persistence is managed across page requests.

## Variable Flow and Initialization

### Initial State
The global configuration object `$g` starts with default values:
```php
public $in = [
    't' => 'Basic',    // Default theme
    'o' => 'Home',     // Default content object
    'm' => 'list',     // Default method
    // ... other defaults
];
```

### Request Processing
1. In `Init::__construct()`, the following sequence occurs:
   ```php
   // Sanitize input values
   $g->in = Util::esc($g->in);
   
   // This processes each input var against $_REQUEST
   // If a matching $_REQUEST key exists, it overrides the default
   // If not, the default value remains
   ```

### Session Handling
The `Util::ses()` method manages session variable persistence:
```php
public static function ses(string $k, string $v = '', ?string $x = null): string
{
    // k: session key
    // v: default value
    // x: override value (if provided)
    
    return $_SESSION[$k] = 
        (!is_null($x) && (!isset($_SESSION[$k]) || ($_SESSION[$k] != $x))) 
            ? $x 
            : (((isset($_REQUEST[$k]) && !isset($_SESSION[$k]))
                || (isset($_REQUEST[$k], $_SESSION[$k])
                    && ($_REQUEST[$k] != $_SESSION[$k])))
                ? self::enc($_REQUEST[$k])
                : ($_SESSION[$k] ?? $v));
}
```

## Theme Selection Process

### Priority Order
Theme selection follows this priority sequence:
1. URL Parameter (`?t=ThemeName`)
2. Existing Session Value
3. Default Value (`Basic`)

### Selection Flow
```php
// In Init.php
$t = Util::ses('t', $g->in['t']);  // Correct usage for theme persistence

// This evaluates as:
// 1. If t exists in $_REQUEST, use that value
// 2. If t exists in $_SESSION, use that value
// 3. Otherwise, fall back to $g->in['t'] (usually 'Basic')
```

### Theme Class Resolution
After theme selection:
```php
$t2 = "HCP\\Themes\\$t";  // Constructs theme class name
if (class_exists($t2)) {
    $thm->themeImpl = new $t2($g);  // Instantiates theme
}
```

## Example Scenarios

### First Visit (No Session)
- Default: `$g->in['t'] = 'Basic'`
- No session or URL parameters
- Result: Basic theme used and stored in session

### Theme Change via URL
- URL: `?t=TopNav`
- Session updated to store 'TopNav'
- Theme persists on subsequent pages

### Subsequent Visits
- No URL parameter
- Existing session value used
- Default 'Basic' ignored if session exists

## Important Notes
1. Session persistence relies on proper parameter ordering in `Util::ses()`
2. URL parameters always override existing session values
3. Default values only apply when no session or URL parameter exists
4. Theme class must exist in `HCP\Themes` namespace
5. Invalid theme names fall back to base Theme class

This implementation allows for dynamic theme switching while maintaining user preference across page loads through session persistence.

