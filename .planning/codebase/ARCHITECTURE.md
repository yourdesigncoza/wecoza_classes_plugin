# Architecture

**Analysis Date:** 2026-01-22

## Pattern Overview

**Overall:** MVC with WordPress Integration

The WeCoza Classes Plugin implements a clean MVC architecture within a WordPress plugin container. Controllers register themselves on plugin init and manage both shortcodes and AJAX endpoints. Models handle data access via external PostgreSQL database (not WordPress MySQL). Views are component-based templates rendered via a helper function.

**Key Characteristics:**
- Namespaced MVC layer with PSR-4 autoloading
- External PostgreSQL database as primary data store (not WordPress MySQL)
- JSONB fields for complex data structures (learner assignments, scheduling, QA data)
- Singleton pattern for database connection management
- WordPress hooks for capability-based security and feature gating
- Direct AJAX handlers without request routing middleware
- Conditional asset loading based on shortcode presence

## Layers

**Presentation (Views):**
- Purpose: Render UI components and display data to users
- Location: `app/Views/`
- Contains: Component-based PHP templates (*.view.php files), partials for forms, dashboard widgets
- Depends on: ViewHelpers (via helper functions), ClassController data extraction
- Used by: Controllers via `view()` helper function which extracts and renders templates

**Business Logic (Controllers):**
- Purpose: Handle shortcode rendering, AJAX requests, asset management, WordPress integrations
- Location: `app/Controllers/`
- Contains: 4 main controllers - ClassController, ClassTypesController, PublicHolidaysController, QAController
- Depends on: Models for data access, WordPress hooks (add_action, add_shortcode, wp_enqueue_*)
- Used by: WordPress init hook and AJAX handlers

**Data Access (Models):**
- Purpose: Provide business objects and direct database queries via PostgreSQL
- Location: `app/Models/`
- Contains: ClassModel, QAModel, QAVisitModel - each managing one primary entity
- Depends on: DatabaseService singleton for query execution
- Used by: Controllers for data retrieval and entity hydration

**Infrastructure (Database Service):**
- Purpose: Abstract PostgreSQL connection management and query execution
- Location: `app/Services/Database/DatabaseService.php`
- Contains: Singleton PDO instance, parameterized query execution
- Depends on: PDO, WordPress options for credentials (get_option)
- Used by: All Models for database operations

**Helpers & Utilities:**
- Purpose: Reusable UI generation functions, view rendering
- Location: `app/Helpers/`
- Contains: ViewHelpers (form elements), view-helpers-loader
- Used by: Views and Controllers for common patterns

## Data Flow

**Class Creation Flow (Shortcode → AJAX → Database):**

1. User visits page with `[wecoza_capture_class]` shortcode
2. ClassController::captureClassShortcode() renders form view (`class-capture-form.view.php`)
3. JavaScript (class-capture.js) validates form and sends AJAX POST to `wp-admin/admin-ajax.php?action=save_class`
4. WordPress routes to ClassController::saveClassAjax() static method
5. Controller instantiates ClassModel, hydrates from form data
6. Model calls DatabaseService::query() with parameterized INSERT SQL
7. DatabaseService::getInstance() provides singleton PDO connection
8. Query executes against PostgreSQL with parameters bound securely
9. Response returned as JSON to JavaScript
10. JavaScript updates page with success/error message

**Class Display Flow (Shortcode → Database → View):**

1. User visits page with `[wecoza_display_classes]` shortcode
2. ClassController::displayClassesShortcode() queries ClassModel
3. ClassModel::getAllClasses() executes SELECT via DatabaseService
4. Model enriches results with agent names (lookup from learner_ids JSONB)
5. Controller extracts data array and renders `classes-display.view.php`
6. View iterates classes and renders Bootstrap table with search/pagination JS

**QA Analytics Flow (AJAX → Model → View → Chart.js):**

1. QA Dashboard shortcode renders JavaScript (qa-dashboard.js)
2. JavaScript calls AJAX action `get_qa_analytics`
3. QAController::getQAAnalytics() queries QAModel
4. Model aggregates data from PostgreSQL with GROUP BY, date filtering
5. Returns structured JSON with series for Chart.js
6. JavaScript renders chart using Chart.js library

**State Management:**

- **Transient Data**: Form submission state, loading indicators handled by JavaScript
- **Persistent Data**: All class/QA data stored in PostgreSQL, not WordPress postmeta
- **Session Data**: User capabilities evaluated per-request via WordPress functions (current_user_can)
- **Client-Side State**: Form state, UI interactions handled by JavaScript files (no Vue/React)

## Key Abstractions

**ClassModel:**
- Purpose: Represents a single class entity with full lifecycle (create, read, update, delete)
- Examples: `app/Models/ClassModel.php`
- Pattern:
  - Hydration from database rows or form data via `hydrate()` method
  - Property setters/getters for all database fields
  - JSONB field support for complex structures (learner_ids, schedule_data, backup_agent_ids)
  - Static query methods for batch operations (getAllClasses, getClassById, etc.)

**DatabaseService:**
- Purpose: Singleton wrapper around PDO for PostgreSQL connection management
- Examples: `app/Services/Database/DatabaseService.php`
- Pattern:
  - Single instance created on first getInstance() call
  - Parameterized queries to prevent SQL injection
  - PDO exception handling with error logging
  - Credentials loaded from WordPress options at runtime

**View Rendering Helper:**
- Purpose: Centralized template rendering with data extraction
- Examples: `view()` function in `app/bootstrap.php`
- Pattern:
  - Takes view name (without .view.php), data array, return flag
  - Extracts array keys to PHP variables via `extract()`
  - Includes template file with ob_start/ob_get_clean buffering
  - Returns HTML string or echoes based on return parameter

**WordPress Hook Integration:**
- Purpose: Register plugin features within WordPress lifecycle
- Pattern:
  - Controllers register themselves via __construct() hook actions
  - Shortcodes registered on `init` hook via `add_shortcode()`
  - AJAX handlers registered on `wp_ajax_*` and `wp_ajax_nopriv_*` hooks
  - Assets enqueued conditionally on `wp_enqueue_scripts` based on shortcode presence

## Entry Points

**Plugin Bootstrap:**
- Location: `wecoza-classes-plugin.php`
- Triggers: WordPress plugin initialization
- Responsibilities:
  - Define plugin constants (paths, URLs, versions)
  - Register activation/deactivation/uninstall hooks
  - Check minimum PHP/WordPress versions
  - Instantiate WeCoza_Classes_Plugin main class

**Application Bootstrap:**
- Location: `app/bootstrap.php`
- Triggers: Plugin file loads via require_once
- Responsibilities:
  - Register PSR-4 autoloader via spl_autoload_register
  - Define global helper functions (view, config, plugin_path, etc.)
  - Load configuration
  - Initialize controllers on 'init' hook
  - Load AJAX handlers

**Main Plugin Class:**
- Location: `includes/class-wecoza-classes-plugin.php`
- Triggers: Called from wecoza-classes-plugin.php::run_wecoza_classes_plugin()
- Responsibilities:
  - Load dependencies (bootstrap.php)
  - Define WordPress hooks
  - Execute plugin run action

**Controllers:**
- Location: `app/Controllers/ClassController.php`, `QAController.php`, etc.
- Triggers: Instantiated by bootstrap during 'init' hook via config
- Responsibilities:
  - Register shortcodes and AJAX handlers in __construct
  - Manage asset enqueueing (JavaScript, CSS via WordPress)
  - Implement individual action methods (captureClassShortcode, saveClassAjax, etc.)

## Error Handling

**Strategy:** Try-catch with error logging to WordPress error_log

**Patterns:**

```php
// Database errors caught and logged
try {
    $pdo = new \PDO(...);
} catch (\PDOException $e) {
    error_log('WeCoza Classes Plugin: Database connection error: ' . $e->getMessage());
    throw new \Exception('Database connection failed');
}

// AJAX responses with error status
if ($error) {
    wp_send_json_error(['message' => 'Error message'], 400);
}
wp_send_json_success(['data' => $result]);

// Missing views logged in debug mode
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log("WeCoza Classes Plugin: View file not found: {$view_file}");
}
```

## Cross-Cutting Concerns

**Logging:**
- Approach: WordPress error_log() function
- Only writes when WP_DEBUG is true
- Logs to wp-content/debug.log file
- Pattern: `error_log("WeCoza Classes Plugin: [message]")`

**Validation:**
- Form validation: JavaScript-side (class-capture.js, class-schedule-form.js)
- NONCE validation: Not consistently applied to AJAX endpoints
- Configuration validation: PostgreSQL credentials checked on first connection attempt
- Database constraints: PRIMARY KEY, UNIQUE constraints enforced at PostgreSQL level

**Authentication:**
- Approach: WordPress current_user_can() capability checks
- Pattern: Most AJAX endpoints register both wp_ajax and wp_ajax_nopriv handlers
- Capability function in bootstrap: `get_user_class_capabilities()` returns array of capabilities
- Admin menu: Requires 'manage_options' capability

**Security:**
- Parameterized queries: DatabaseService uses PDO prepared statements with bound parameters
- WordPress nonces: QA dashboard scripts use wp_create_nonce() and verify with check_ajax_referer
- Escaping: Output escaped with esc_html, esc_attr in views
- File uploads: Restricted to configured file types (pdf, doc, docx, xls, xlsx, jpg, jpeg, png)

---

*Architecture analysis: 2026-01-22*
