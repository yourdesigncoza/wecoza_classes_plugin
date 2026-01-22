# Coding Conventions

**Analysis Date:** 2026-01-22

## Naming Patterns

**Files:**
- Classes: PascalCase with `.php` extension, e.g., `ClassModel.php`, `ClassController.php`
- Views: kebab-case with `.view.php` suffix for primary views, e.g., `class-capture-form.view.php`
- Partials: kebab-case with `.php` suffix, e.g., `create-class.php`, `update-class.php`
- JavaScript: kebab-case, e.g., `class-capture.js`, `class-schedule-form.js`
- Configuration: kebab-case or single word, e.g., `app.php`
- Controllers use PascalCase suffixed with `Controller`, e.g., `ClassController`, `QAController`

**Functions:**
- Public functions: camelCase, e.g., `captureClassShortcode()`, `registerShortcodes()`
- Private functions: camelCase prefixed with underscore (older style) or just camelCase with `private` visibility keyword, e.g., `_handleCreateMode()` or `handleCreateMode()`
- Utility functions in bootstrap: camelCase with descriptive names, e.g., `config()`, `view()`, `asset_url()`, `plugin_path()`
- WordPress hooks use snake_case with underscores: `add_shortcode()`, `wp_ajax_*`, `wp_enqueue_scripts`

**Variables:**
- PHP properties: camelCase for private/public properties, e.g., `$clientId`, `$classType`, `$learnerIds`
- JSONB field mapping: snake_case in database, converted to camelCase in PHP models
- JavaScript variables: camelCase, e.g., `formData`, `classData`, `scheduleData`
- Static properties: camelCase, e.g., `self::$instance`

**Types:**
- Models are appended with `Model`, e.g., `ClassModel`, `QAModel`
- Controllers are appended with `Controller`, e.g., `ClassController`
- Services are placed in `Services/` directory with domain-specific naming, e.g., `DatabaseService`
- Database classes: Services/Database/DatabaseService.php

## Code Style

**Formatting:**
- PSR-12 PHP standards applied
- 4-space indentation (not tabs)
- Maximum line length: Not enforced strictly, but readability preferred
- No automatic formatter configured (manual formatting followed)

**Linting:**
- No ESLint or Prettier configuration detected
- Code follows manual conventions without automated linting
- JavaScript uses informal conventions

## Import Organization

**Order:**

1. PHP Namespace declaration (`namespace WeCozaClasses\...`)
2. Use statements for other namespaces (`use WeCozaClasses\Models\...`)
3. WordPress global function calls prefixed with backslash when in namespaced context
4. Class definition

**Example from ClassController.php:**
```php
namespace WeCozaClasses\Controllers;

use WeCozaClasses\Models\ClassModel;
use WeCozaClasses\Models\QAVisitModel;
use WeCozaClasses\Controllers\ClassTypesController;
use WeCozaClasses\Controllers\PublicHolidaysController;

class ClassController {
    // Call WordPress functions with global namespace prefix
    \add_action('init', [$this, 'registerShortcodes']);
    \wp_ajax_save_class();
}
```

**Path Aliases:**
- Not used; full namespace paths always explicit
- Constants for base paths used: `WECOZA_CLASSES_PATH`, `WECOZA_CLASSES_APP_PATH`, `WECOZA_CLASSES_VIEWS_PATH`

## Error Handling

**Patterns:**
- Try-catch blocks used for database operations and external service calls
- Errors logged via WordPress `error_log()` when `WP_DEBUG` is enabled
- Silent failures with fallback empty arrays or null returns common pattern
- Example from ClassController.php:
```php
try {
    $db = DatabaseService::getInstance();
    $sql = "SELECT client_id, client_name FROM public.clients ORDER BY client_name ASC";
    $stmt = $db->query($sql);
    return $stmt->fetchAll();
} catch (\Exception $e) {
    // error_log('WeCoza Classes Plugin: Error fetching clients: ' . $e->getMessage());
    return [];
}
```
- No exceptions thrown to user; errors caught and logged
- Graceful degradation in UI when data unavailable

## Logging

**Framework:** WordPress native `error_log()`

**Patterns:**
- All logs prefixed with "WeCoza Classes Plugin: " for easy identification
- Conditional logging: only logs when `WP_DEBUG` defined and true
- Log levels included in message: `[info]`, `[warning]`, `[error]`
- Sensitive data (passwords) explicitly NOT logged
- Example from bootstrap.php:
```php
function plugin_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("WeCoza Classes Plugin [{$level}]: {$message}");
    }
}
```
- Many error_log calls commented out in production code for performance

## Comments

**When to Comment:**
- File headers: Required with docblock describing purpose, location, and extraction details
- Class headers: Required with description and responsibility
- Function/method docblocks: Always included with @param, @return, @throws
- Complex logic blocks: Inline comments for non-obvious operations
- Section dividers: Comments mark major sections within controllers

**JSDoc/TSDoc:**
- PHP docblocks follow PHPDoc conventions
- @param, @return, @throws tags used consistently
- Type hints included in comments for clarity
- Examples from ClassModel.php:
```php
/**
 * Hydrate model from database row or form data
 */
private function hydrate($data) {

/**
 * Get all class types (main categories)
 *
 * @return array List of class types
 */
public static function getClassTypes() {
```

## Function Design

**Size:**
- Methods typically range from 20-150 lines
- Long methods (200+ lines) exist for complex operations like saveClassAjax()
- Private helper methods extract single responsibilities

**Parameters:**
- Maximum 3-4 parameters typical; complex data passed as arrays
- Default parameters used for optional values
- Array unpacking with null coalescing: `$data['field'] ?? default`

**Return Values:**
- Methods return arrays for data, booleans for success/failure, objects for models
- Silent failures return empty arrays [] or null
- Static methods used for AJAX handlers and utility functions
- No explicit return type declarations (PHP 7.4 compatible)

## Module Design

**Exports:**
- Classes exported via PSR-4 autoloading registered in bootstrap.php
- Public methods define public interface; private methods for internal use
- Singletons used for services (DatabaseService)

**Barrel Files:**
- Not used; individual imports explicit
- view-helpers-loader.php loads helper functions as global scope

## Database Patterns

**Query Methods:**
- Prepared statements with parameter binding: `$db->query($sql, [$param])`
- Snake_case SQL identifiers for columns and tables
- PDOStatement::fetchAll() returns associative arrays by default (FETCH_ASSOC)
- JSONB fields parsed in application layer with parseJsonField()

**Model Hydration:**
- Hydration method parses database row into object properties
- Supports both snake_case (DB) and camelCase (form) field names
- Flexible field mapping with null coalescing for alternate names

## WordPress Integration

**Capability Checks:**
- `current_user_can()` used before sensitive operations
- Capability names follow WordPress conventions: 'edit_posts', 'delete_posts', 'manage_options'
- AJAX handlers check both logged-in and non-privileged versions

**Hook Usage:**
- Actions: `add_action('hook_name', [class, 'method'])`
- Shortcodes: `add_shortcode('shortcode_name', [instance, 'method'])`
- AJAX: `add_action('wp_ajax_action_name', [Class, 'staticMethod'])`
- All hooks registered in controller constructors

## Asset Enqueue Patterns

**Script Registration:**
- `wp_enqueue_script()` with jQuery dependency where needed
- Localization with `wp_localize_script()` for AJAX URLs and nonces
- Conditional loading based on shortcode presence checked with `has_shortcode()`
- Scripts loaded in footer with `true` parameter for performance

**Styles:**
- No CSS registered from plugin (delegated to theme child CSS file)
- All styles added to `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`

---

*Convention analysis: 2026-01-22*
