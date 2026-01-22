# Codebase Structure

**Analysis Date:** 2026-01-22

## Directory Layout

```
wecoza-classes-plugin/
├── wecoza-classes-plugin.php           # Main plugin file - entry point, constants, hooks
├── app/                                 # MVC application layer
│   ├── bootstrap.php                    # Autoloader, config loader, view helper, initialization
│   ├── Controllers/                     # Business logic handlers for shortcodes/AJAX
│   │   ├── ClassController.php          # Class management, shortcodes, AJAX handlers
│   │   ├── ClassTypesController.php     # Class types/subjects reference data
│   │   ├── PublicHolidaysController.php # Holiday detection and calendar integration
│   │   └── QAController.php             # QA analytics, dashboards, visits
│   ├── Models/                          # Data access layer with PostgreSQL
│   │   ├── ClassModel.php               # Class entity with database queries
│   │   ├── QAModel.php                  # QA analytics and data aggregation
│   │   └── QAVisitModel.php             # QA visit scheduling and tracking
│   ├── Views/                           # Component-based presentation templates
│   │   ├── components/                  # Reusable view components
│   │   │   ├── class-capture-form.view.php      # Class creation/edit form
│   │   │   ├── class-capture-partials/          # Form sub-components
│   │   │   │   ├── create-class.php
│   │   │   │   └── update-class.php
│   │   │   ├── classes-display.view.php         # All classes table with search
│   │   │   └── single-class-display.view.php    # Single class details view
│   │   ├── qa-dashboard-widget.php     # QA summary widget for admin homepage
│   │   └── qa-analytics-dashboard.php  # Full QA analytics with Chart.js
│   ├── Services/                        # Service layer
│   │   └── Database/
│   │       └── DatabaseService.php      # PostgreSQL PDO singleton wrapper
│   ├── Helpers/                         # Utility functions and view helpers
│   │   ├── ViewHelpers.php              # Form rendering helpers (select, dropdowns)
│   │   └── view-helpers-loader.php      # Registers helper functions globally
│   └── Admin/                           # Admin-specific code
│       └── ScheduleDataAdmin.php        # Schedule field management in admin
├── config/                              # Configuration files
│   └── app.php                          # Controllers, shortcodes, AJAX endpoints, capabilities
├── includes/                            # WordPress integration and lifecycle
│   ├── class-wecoza-classes-plugin.php  # Main plugin class with hook definitions
│   ├── class-activator.php              # Plugin activation logic
│   ├── class-deactivator.php            # Plugin deactivation logic
│   ├── class-uninstaller.php            # Plugin uninstall logic
│   └── migrations/                      # Database schema migrations
│       ├── create-classes-table.php     # Initial PostgreSQL table creation
│       └── add_exam_learners_field.sql  # Schema migration example
├── assets/                              # Frontend JavaScript and CSS assets
│   └── js/
│       ├── class-capture.js             # Form handling, validation, submission
│       ├── class-schedule-form.js       # Per-day scheduling interface
│       ├── classes-table-search.js      # Table search and pagination
│       ├── class-types.js               # Class type/subject filtering
│       ├── wecoza-calendar.js           # FullCalendar integration
│       ├── qa-dashboard.js              # QA analytics Chart.js visualizations
│       ├── learner-level-utils.js       # Learner assignment utilities
│       ├── learner-selection-table.js   # Learner picker table interface
│       └── wecoza-classes-admin.js      # Admin-specific scripts
├── schema/                              # Database schema documentation
│   ├── wecoza_db_schema_bu_oct_22.sql  # PostgreSQL schema backup/reference
│   └── classes_schema.sql               # (Referenced in migrations, not present)
├── daily-updates/                       # Maintenance/update scripts
└── .planning/                           # Documentation and planning
    └── codebase/                        # Generated analysis documents
```

## Directory Purposes

**`wecoza-classes-plugin.php`:**
- Purpose: WordPress plugin header and bootstrap
- Contains: Plugin metadata, version constants, activation/deactivation hooks
- Key files: Define WECOZA_CLASSES_* constants used throughout

**`app/`:**
- Purpose: Core MVC application
- Contains: Controllers, Models, Views, Services, Helpers
- Key files: bootstrap.php (initialization), Controllers/* (business logic)

**`app/Controllers/`:**
- Purpose: Handle shortcodes, AJAX requests, asset management
- Contains: 4 main controller classes, each managing specific features
- Key files:
  - `ClassController.php` - 60% of AJAX endpoints (save_class, get_calendar_events, etc.)
  - `QAController.php` - Analytics and dashboard rendering

**`app/Models/`:**
- Purpose: Data access and business object representation
- Contains: Entity classes that hydrate from database rows or form data
- Key files:
  - `ClassModel.php` - Main entity representing a training class
  - `QAModel.php` - Aggregates QA analytics and reporting data
  - `QAVisitModel.php` - Manages QA visit scheduling

**`app/Views/`:**
- Purpose: Presentation templates with Bootstrap 5
- Contains: Component-based .view.php templates with embedded PHP
- Key files:
  - `components/class-capture-form.view.php` - Main form template
  - `components/classes-display.view.php` - Classes table (used by [wecoza_display_classes])
  - `qa-analytics-dashboard.php` - Chart.js dashboard

**`app/Services/Database/`:**
- Purpose: Database abstraction layer
- Contains: DatabaseService singleton managing PostgreSQL PDO connection
- Key files: `DatabaseService.php` - Singleton pattern, parameterized query execution

**`app/Helpers/`:**
- Purpose: Reusable utility functions for views
- Contains: Form element generation, view rendering helpers
- Key files: `ViewHelpers.php` - select dropdowns, optgroups, HTML helpers

**`config/`:**
- Purpose: Centralized configuration management
- Contains: Controllers list, shortcodes, AJAX endpoints, validation rules
- Key files: `app.php` - Single source of truth for feature registration

**`includes/`:**
- Purpose: WordPress plugin lifecycle management
- Contains: Activator, deactivator, uninstaller, main plugin class
- Key files:
  - `class-wecoza-classes-plugin.php` - Main plugin class
  - `class-activator.php` - Runs on plugin activation
  - `migrations/` - Database schema changes

**`assets/js/`:**
- Purpose: Client-side interactivity
- Contains: Form handling, search, calendar, charts
- Key files:
  - `class-capture.js` - Form validation and submission (138KB)
  - `class-schedule-form.js` - Scheduling interface (133KB)
  - `wecoza-calendar.js` - FullCalendar integration

**`schema/`:**
- Purpose: Database schema documentation
- Contains: PostgreSQL table definitions and JSONB field specifications
- Key files: `wecoza_db_schema_bu_oct_22.sql` - Backup/reference of production schema

## Key File Locations

**Entry Points:**
- `wecoza-classes-plugin.php` - Plugin header and initialization trigger
- `app/bootstrap.php` - Application bootstrap, autoloader, helpers
- `includes/class-wecoza-classes-plugin.php` - Main plugin class instantiation

**Configuration:**
- `config/app.php` - Controllers, shortcodes, AJAX endpoints, validation rules, capabilities
- `wecoza-classes-plugin.php` - Constants (paths, URLs, versions)
- WordPress options (via get_option): PostgreSQL credentials stored as wp_options

**Core Logic:**
- `app/Controllers/ClassController.php` - Primary business logic (class CRUD, AJAX handlers)
- `app/Controllers/QAController.php` - QA analytics and dashboard
- `app/Models/ClassModel.php` - Class data access and entity representation

**Presentation:**
- `app/Views/components/class-capture-form.view.php` - Class form template
- `app/Views/components/classes-display.view.php` - Classes list display
- `app/Views/qa-analytics-dashboard.php` - QA dashboard with charts

**Testing/Data:**
- `schema/wecoza_db_schema_bu_oct_22.sql` - PostgreSQL schema reference

## Naming Conventions

**Files:**
- Controllers: `[Feature]Controller.php` (example: `ClassController.php`)
- Models: `[Entity]Model.php` (example: `ClassModel.php`)
- Views: `[component-name].view.php` (example: `class-capture-form.view.php`)
- Migrations: `[action-name].php` or `.sql` (example: `create-classes-table.php`)
- JavaScript: `kebab-case.js` (example: `class-capture.js`)
- Services: `[ServiceName].php` placed in `Services/[Category]/` (example: `DatabaseService.php`)

**Classes:**
- Namespace: `WeCozaClasses\[Category]\[ClassName]` (example: `WeCozaClasses\Controllers\ClassController`)
- Non-namespaced legacy: `WeCoza_[ClassName]` (example: `WeCoza_Classes_Plugin`)

**Functions:**
- Global helpers: Snake_case prefixed with context (example: `wecoza_classes_init()`)
- View helpers: Snake_case in ViewHelpers namespace (example: `select_dropdown_with_optgroups()`)
- Shortcode callbacks: camelCase methods on controllers (example: `captureClassShortcode()`)
- AJAX callbacks: camelCase static methods on controllers (example: `saveClassAjax()`)

**Database:**
- Tables: Snake_case (example: `classes`, `agent_replacements`)
- Columns: Snake_case (example: `class_id`, `original_start_date`)
- JSONB fields: Snake_case arrays (example: `learner_ids`, `schedule_data`)
- Indexes: `idx_[table]_[columns]` (example: `idx_classes_client_id`)

## Where to Add New Code

**New Feature (e.g., new class module):**
- Controller: `app/Controllers/[Feature]Controller.php`
  - Register shortcodes in __construct via add_shortcode()
  - Register AJAX endpoints in __construct via add_action('wp_ajax_*')
  - Add entry to `config/app.php` in controllers array
- Model: `app/Models/[Entity]Model.php`
  - Implement data access methods (get, save, delete, query)
  - Use DatabaseService::getInstance()->query() for execution
- Views: `app/Views/components/[feature-name].view.php`
  - Render via $output = view('components/[feature-name]', $data);
- Tests: No automated test framework - test manually via browser/WordPress admin

**New Component/Module:**
- If it's a reusable UI element: Create view in `app/Views/components/`
- If it's a business service: Create class in `app/Services/[Category]/`
- If it's a data access layer: Extend appropriate Model in `app/Models/`

**Utilities:**
- Shared helpers: `app/Helpers/` directory
- Global functions: Add to bootstrap.php or view-helpers-loader.php
- Reusable logic: Extract to Models or Services, not Controllers

**JavaScript:**
- New UI interactions: `assets/js/[feature-name].js`
- Enqueue in controller's enqueueAssets() method via wp_enqueue_script()
- Load conditionally: Check for shortcode via has_shortcode() before enqueueing

**Assets (CSS):**
- DO NOT add CSS files to plugin directory
- Add ALL CSS to theme: `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`
- Reference theme CSS in controller via wp_enqueue_style() pointing to theme URL

**Database Migrations:**
- Create PHP file in `includes/migrations/[action-name].php`
- Or create SQL file in `includes/migrations/[action-name].sql`
- Called manually via WordPress admin or wp-cli during plugin lifecycle

## Special Directories

**`app/Views/components/class-capture-partials/`:**
- Purpose: Sub-components of the class capture form
- Generated: No (manually maintained)
- Committed: Yes
- Contains: create-class.php and update-class.php for form mode switching

**`daily-updates/`:**
- Purpose: Maintenance and periodic update scripts
- Generated: No (manual scripts)
- Committed: Yes

**`.planning/`:**
- Purpose: Documentation and planning artifacts
- Generated: Yes (via `/gsd:map-codebase` command)
- Committed: Yes (documents checked in for team reference)

**`schema/`:**
- Purpose: PostgreSQL database schema documentation
- Generated: No (manual backups and references)
- Committed: Yes
- Note: wecoza_db_schema_bu_oct_22.sql is a production schema backup

---

*Structure analysis: 2026-01-22*
