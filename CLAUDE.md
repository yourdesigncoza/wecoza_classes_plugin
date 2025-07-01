# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### WordPress Plugin Testing
```bash
# Test plugin functionality via WordPress admin
# Go to WordPress Admin → Tools → WeCoza Classes Test
# Verify database connection and run functionality tests
```

### Database Operations
```bash
# Check PostgreSQL connection status
# Database configuration in config/app.php
# Connection details stored in WordPress options:
# - wecoza_postgres_host
# - wecoza_postgres_port  
# - wecoza_postgres_dbname
# - wecoza_postgres_user
# - wecoza_postgres_password
```

### Asset Management
```bash
# JavaScript files are automatically enqueued via config/app.php
# No build process required - files served directly
# Version control uses datetime stamps to prevent caching during development
```

### Plugin Activation/Testing
```bash
# Activate plugin: WordPress Admin → Plugins → Activate "WeCoza Classes Plugin"
# Test pages with shortcodes:
# [wecoza_capture_class] - Class creation form
# [wecoza_display_classes] - Classes table with search
# [wecoza_display_single_class] - Single class view
```

## Architecture Overview

### MVC Structure
The plugin follows strict MVC architecture with namespace `WeCozaClasses\`:

- **Controllers** (`app/Controllers/`): Handle business logic and request routing
  - `ClassController.php`: Main class management, CRUD operations, calendar integration
  - `ClassTypesController.php`: Class types and subjects via AJAX
  - `PublicHolidaysController.php`: Static South African holidays, calendar events

- **Models** (`app/Models/`): Database interaction and data structures
  - `ClassModel.php`: Class data handling with PostgreSQL integration

- **Views** (`app/Views/`): Presentation layer with component-based architecture
  - `components/`: Reusable form components and display templates
  - `class-capture-partials/`: Create/update form partials

- **Services** (`app/Services/`): Shared functionality
  - `Database/DatabaseService.php`: PostgreSQL connection management

### Bootstrap System
- `app/bootstrap.php`: Core application initialization with autoloader
- `includes/class-wecoza-classes-plugin.php`: Main plugin orchestration
- `config/app.php`: Centralized configuration for all plugin aspects

### Database Integration
- **Primary Database**: PostgreSQL (not WordPress database)
- **External Database**: Classes data is stored in DigitalOcean PostgreSQL at `db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com:25060/defaultdb`
- **Connection**: Configured via WordPress options, fallback to config defaults
- **IMPORTANT**: WordPress's `$wpdb` does NOT contain classes data - use `DatabaseService::getInstance()` for PostgreSQL access
- **Schema**: Full schema in `schema/classes_schema_3.sql` and `schema/classes_schema.sql`
- **Data Format**: JSONB fields for flexible schedule data storage
- **Tables**: Classes, clients, agents, sites, learners with full relationship mapping

### Asset Architecture
- **JavaScript**: Component-based with specific responsibilities
  - `class-schedule-form.js`: Advanced scheduling with holiday detection
  - `class-capture.js`: Form validation and submission
  - `learner-level-utils.js`: Auto-population logic for learner levels
  - `classes-table-search.js`: Real-time search with pagination
  - `wecoza-calendar.js`: FullCalendar integration
- **CSS**: Bootstrap 5 compatible, integrated with theme styles
  - **Stylesheet Location**: All stylesheets are in `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css`
  - **Primary Stylesheet**: `ydcoza-styles.css` is the ONLY stylesheet that should be updated
- **Versioning**: Dynamic timestamps prevent caching issues during development

### Configuration System
All configuration centralized in `config/app.php`:
- Database connections and settings
- Shortcode registration and routing
- AJAX endpoint definitions with public/private access
- Asset management and loading
- Validation rules and constraints
- User capabilities and permissions

### Shortcode System
Three main shortcodes with controller routing:
- `[wecoza_capture_class]`: Form for creating/editing classes
- `[wecoza_display_classes]`: Searchable table of all classes
- `[wecoza_display_single_class]`: Detailed single class view

Each shortcode maps to controller methods via configuration.

### Public Holidays Integration
- **Status**: Fully active and restored in create-class.php (lines 300-354)
- **Source**: Static South African holidays in `PublicHolidaysController.php`
- **Detection**: Smart conflict detection only shows holidays that fall on scheduled class days
- **Override System**: Individual and bulk override capabilities with hidden form input
- **Form Integration**: Template-based dynamic holiday row generation
- **UI Features**: Skip All/Override All bulk actions, real-time status badges
- **Integration**: Holidays factor into end date calculations and schedule generation

### Schedule Data Format
Classes support complex scheduling with JSONB storage:
- **V2.0 Format**: Per-day time configuration with individual day settings
- **Patterns**: Weekly, bi-weekly, monthly with flexible day selection
- **Holiday Handling**: Override system for including holidays in schedules
- **Exception Dates**: Client cancellations, agent absences with reason tracking

### AJAX Architecture
- **Endpoints**: Defined in `config/app.php` with public/private access control
- **Nonce Security**: WordPress nonce verification for all authenticated endpoints
- **Error Handling**: Structured JSON responses with error logging
- **Data Flow**: Form submission → validation → database storage → response

### Development Workflow
- **Daily Reports**: Automated development tracking in `daily-updates/`
  - `WEC-DAILY-WORK-REPORT-*.md`: Generated daily reports with commit analysis
  - `end-of-day-report.md`: Template for generating daily development reports
- **Reference Documentation**: Analysis reports in `reference/` for complex features
  - `public-holidays-integration-analysis.md`: Comprehensive holiday system analysis
  - `schedule-end-date-calculation-analysis.md`: End date calculation documentation
- **Migration System**: Database migrations in `includes/migrations/`
- **Version Control**: Git-based with descriptive commit messages
- **Documentation**: CLAUDE.md for development guidance and README.md for user documentation

### WordPress Integration
- **Hooks**: Minimal WordPress dependency with clean separation
- **Capabilities**: Custom capability checking for class management
- **Options**: Database settings stored in WordPress options table
- **Internationalization**: Text domain support with translation ready strings

### Data Validation
- **Frontend**: JavaScript validation with real-time feedback
- **Backend**: Server-side validation in controllers with sanitization
- **Rules**: Centralized validation configuration in `config/app.php`
- **Security**: Input sanitization and nonce verification for all operations

## Important Development Notes

### PostgreSQL Dependency
This plugin requires PostgreSQL database connection. WordPress database is NOT used for class data. Database connection must be configured before plugin activation.

**Critical**: All classes, learners, agents, sites, and related data are stored in an external PostgreSQL database on DigitalOcean, NOT in the WordPress database. Never use `global $wpdb` to query classes data - always use the DatabaseService class.

### Calendar Integration
The Public Holidays Section is **FULLY ACTIVE** in the create-class.php form. Recent commits restored this functionality after it was temporarily disabled. Holiday detection requires `window.wecozaPublicHolidays` to be available. Data is localized via `ClassController.php` and consumed by scheduling JavaScript.

### Form Validation
Forms use Bootstrap validation classes with custom JavaScript. Server-side validation mirrors client-side rules defined in configuration.

### Search and Pagination
Classes table implements client-side search with debounced input and Bootstrap pagination. No AJAX required for search functionality.

### Autoloader
Custom PSR-4 compatible autoloader in `app/bootstrap.php` handles namespace resolution for all plugin classes.

### Asset Versioning
Development uses datetime stamps for cache busting. Production should use static version numbers.

### Testing Strategy
No automated test suite. Testing performed via WordPress admin interface and browser-based JavaScript functionality verification.

### Reference Documentation System
The `reference/` directory contains detailed analysis reports for complex features:
- **Analysis Reports**: In-depth technical documentation for major features
- **Implementation Details**: Code structure, data flow, and integration patterns
- **Current Status**: Up-to-date assessment of feature states and recent changes
- **Development Context**: Essential information for understanding complex implementations

Use these reports when working on related features or debugging complex functionality.