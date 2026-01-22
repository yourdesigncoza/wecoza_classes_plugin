# Technology Stack

**Analysis Date:** 2026-01-22

## Languages

**Primary:**
- PHP 7.4+ (minimum requirement) - Server-side application logic, WordPress plugin infrastructure
- JavaScript (ES6+) - Client-side UI interactions and AJAX communication
- SQL (PostgreSQL dialect) - Data persistence and complex queries via PDO

**Secondary:**
- HTML5 - Template rendering via view files
- CSS3 - Styling (managed via external theme, not plugin)

## Runtime

**Environment:**
- WordPress 5.0+ (tested up to 6.4)
- Apache 2.4 (via XAMPP on local development)
- PostgreSQL 10+ (external DigitalOcean managed cluster)

**Package Manager:**
- Composer (no external PHP dependencies detected - native implementation)
- npm/yarn (not used - no package.json detected)

**Lockfile:** Not applicable - pure WordPress plugin with inline external CDN dependencies

## Frameworks

**Core:**
- WordPress 5.0+ - Plugin framework, authentication, capability system, AJAX infrastructure
- MVC Pattern - Custom implementation in `app/` directory for separation of concerns
- Bootstrap 5 (via theme) - Frontend component framework

**Testing:**
- None automated - Manual testing via WordPress admin interface and browser console

**Build/Dev:**
- No build system - Direct file editing with WordPress asset management
- webpack/gulp/vite - Not used

## Key Dependencies

**Critical:**
- PDO (PHP Data Objects) - Database abstraction layer for PostgreSQL connections
- WordPress Core Functions - `wp_enqueue_script`, `wp_ajax_*`, `add_action`, `add_shortcode`, etc.

**Infrastructure:**
- FullCalendar 6.1.15+ - Calendar UI component (CDN: jsdelivr.net)
- Chart.js 4.4.0 - Analytics visualization library (CDN: jsdelivr.net)
- jQuery (WordPress bundled) - DOM manipulation and AJAX handling

**External Libraries (CDN):**
- `https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js` (6.1.15)
- `https://cdn.jsdelivr.net/npm/chart.js` (4.4.0)

## Configuration

**Environment:**
- WordPress options store for PostgreSQL credentials:
  - `wecoza_postgres_host` - Database server hostname
  - `wecoza_postgres_port` - Database server port
  - `wecoza_postgres_dbname` - Database name
  - `wecoza_postgres_user` - Database username
  - `wecoza_postgres_password` - Database password (required)

**Configuration Files:**
- `config/app.php` - Central application configuration (controllers, shortcodes, AJAX endpoints, assets, validation rules)
- `wecoza-classes-plugin.php` - Plugin bootstrap with activation/deactivation hooks
- `app/bootstrap.php` - MVC initialization with autoloader and helper functions

**Configuration Values Defined:**
- `WECOZA_CLASSES_VERSION` - Dynamic version using timestamp (prevents caching)
- `WECOZA_CLASSES_PLUGIN_DIR` - Plugin directory path
- `WECOZA_CLASSES_ASSETS_DIR` - Assets directory path
- `WECOZA_CLASSES_JS_URL` - JavaScript assets URL
- `WECOZA_CLASSES_CSS_URL` - CSS assets URL (not used directly)

## Platform Requirements

**Development:**
- PHP 7.4 or higher
- WordPress 5.0 or higher
- PostgreSQL 10+ database access
- Apache web server with mod_rewrite
- Modern browser with ES6+ support

**Production:**
- PHP 7.4+ (tested up to 8.x)
- WordPress 5.0+ (tested to 6.4)
- PostgreSQL 10+ (DigitalOcean managed cluster recommended)
- CDN access for jsdelivr.net (FullCalendar, Chart.js)
- WordPress wp-admin AJAX endpoint accessibility

**System Dependencies:**
- PDO PostgreSQL driver extension (`pdo_pgsql`)
- WordPress database and filesystem write permissions for uploads
- 10MB file upload limit support in web server configuration

## Asset Management

**Conditional Loading System:**
Assets load only on pages with plugin shortcodes via `wp_enqueue_scripts` hook:

```php
// Check shortcode presence in page content
if (has_shortcode($content, 'wecoza_capture_class')) {
    wp_enqueue_script('class-capture-js');
}
```

**JavaScript Asset Files (`assets/js/`):**
- `class-capture.js` (138 KB) - Main class creation form with validation
- `class-schedule-form.js` (133 KB) - Per-day scheduling interface and holiday handling
- `wecoza-calendar.js` - FullCalendar integration and event management
- `classes-table-search.js` - Search and pagination for class listings
- `class-types.js` - Subject fetching and dropdown management
- `learner-level-utils.js` - Learner level calculations and utilities
- `learner-selection-table.js` - Learner selection interface
- `qa-dashboard.js` - Chart.js visualization for QA analytics
- `wecoza-classes-admin.js` - WordPress admin-specific functionality

**CSS:**
- All CSS managed via child theme at `/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`
- No plugin-specific CSS files (intentional separation)

## Plugin Architecture

**Bootstrap Flow:**
1. `wecoza-classes-plugin.php` - Plugin entry point with version/path constants
2. `app/bootstrap.php` - PSR-4 autoloader and helper function definitions
3. `config/app.php` - Application configuration loading
4. Controllers instantiation - MVC layer initialization

**PSR-4 Autoloading:**
```php
// Namespace: WeCozaClasses\*
// Maps to: app/* directory structure
WeCozaClasses\Controllers\ClassController → app/Controllers/ClassController.php
WeCozaClasses\Models\ClassModel → app/Models/ClassModel.php
```

**WordPress Hooks Used:**
- `init` - Application initialization and controller loading
- `wp_enqueue_scripts` - Conditional asset loading
- `wp_ajax_*` - AJAX endpoint handlers (15+ endpoints)
- `add_shortcode` - Shortcode registration (5 shortcodes)
- `add_action` - General WordPress action hooks
- `admin_menu` - QA dashboard admin menu

---

*Stack analysis: 2026-01-22*
