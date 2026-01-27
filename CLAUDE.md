# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## WordPress Plugin Architecture

This is a **WeCoza Classes Plugin** - a comprehensive class management system for training programs with a clean MVC architecture and external PostgreSQL database integration.

### Core Architecture
- **MVC Structure**: Controllers, Models, Services, Repositories, Views with PSR-4 autoloading
- **External Database**: PostgreSQL (not WordPress MySQL) with 45+ tables
- **Namespace**: `WeCozaClasses\` for all plugin classes
- **Bootstrap**: `app/bootstrap.php` handles autoloading, view helpers, and component rendering

### Key Files Structure
```
wecoza-classes-plugin.php     # Main plugin file with constants and activation hooks
app/bootstrap.php             # MVC application bootstrap with autoloader + view/component helpers
config/app.php               # Comprehensive configuration (controllers, AJAX, shortcodes)
app/Controllers/             # HTTP handlers and shortcode registration (6 controllers)
app/Services/                # Business logic and data processing
app/Repositories/            # Data access layer with caching
app/Models/                  # Data entities with PostgreSQL integration
app/Helpers/                 # View helpers (select dropdowns, formatting)
app/Views/                   # Component-based presentation layer
app/Views/components/        # Reusable view components
includes/migrations/         # Database migration/seeder scripts
assets/js/                   # JavaScript files
assets/js/utils/             # Reusable JavaScript utilities
```

## Decomposed Controller Architecture

### Controllers (`app/Controllers/`)
| Controller | Lines | Responsibility |
|------------|-------|----------------|
| ClassController | ~608 | Shortcodes, page management, asset loading |
| ClassAjaxController | ~698 | All AJAX handlers (save, delete, calendar, notes) |
| QAController | ~794 | QA analytics, visits, reports |
| ClassTypesController | ~244 | Class types and subject management (DB-driven) |
| PublicHolidaysController | ~196 | Holiday detection and override system |

### Services (`app/Services/`)
| Service | Lines | Responsibility |
|---------|-------|----------------|
| FormDataProcessor | ~727 | Form validation, data processing, sanitization |
| ScheduleService | ~699 | Calendar generation, schedule patterns, date calculations |
| DatabaseService | ~281 | PDO connection management to PostgreSQL |

### Repositories (`app/Repositories/`)
| Repository | Lines | Responsibility |
|------------|-------|----------------|
| ClassRepository | ~685 | Data retrieval, caching, data enrichment |

## View Component System

### Component Helper Function
Views are rendered using the `view()` and `component()` functions from `bootstrap.php`:

```php
// Render a full view
echo \WeCozaClasses\view('components/single-class-display', $viewData);

// Render a component (partial)
\WeCozaClasses\component('single-class/summary-cards', $component_data);
```

### Component Data Pattern
Components receive data via array extraction with `EXTR_SKIP` for security:

```php
$component_data = [
    'class' => $class,
    'schedule_data' => $schedule_data,
    'learners' => $learners,
    // ... other data
];
\WeCozaClasses\component('single-class/details-general', $component_data);
```

### View Components (`app/Views/components/single-class/`)
| Component | Size | Purpose |
|-----------|------|---------|
| header.php | ~2.5KB | Loading indicator, error states |
| summary-cards.php | ~3.7KB | Top summary cards (client, type, subject) |
| details-general.php | ~10KB | Left column - Basic class information |
| details-logistics.php | ~14KB | Right column - Dates, agents, stop periods |
| details-staff.php | ~6KB | Learners preview, exam candidates |
| notes.php | ~12KB | Class notes with filtering |
| qa-reports.php | ~3KB | QA reports table |
| calendar.php | ~7KB | Calendar/list view tabs |
| modal-learners.php | ~7KB | Learners modal dialog |

## JavaScript Utilities

### Utility Files (`assets/js/utils/`)
| File | Purpose |
|------|---------|
| escape.js | XSS prevention with `escapeHtml()` function |
| date-utils.js | Consolidated date/time formatting utilities |
| table-manager.js | Reusable search/filter/pagination for tables |
| ajax-utils.js | Standardized AJAX request handling with WordPress nonce |

### Using TableManager
```javascript
// Initialize reusable table management
const manager = new WeCozaTableManager({
    tableId: '#my-table',
    searchInputId: '#my-search',
    searchColumns: [0, 1, 2],  // Column indices to search
    itemsPerPage: 20,
    onRender: (visibleRows, totalRows) => {
        console.log(`Showing ${visibleRows} of ${totalRows}`);
    }
});

// Available methods
manager.search('term');      // Trigger search
manager.goToPage(2);         // Navigate to page
manager.reset();             // Reset search and pagination
manager.refresh();           // Refresh after DOM changes
manager.getStats();          // Get current statistics
```

### Using AjaxUtils
```javascript
// Simple POST request
WeCozaAjax.post('save_class', formData)
    .then(data => console.log('Success:', data))
    .catch(error => console.error('Error:', error));

// With loading indicator
WeCozaAjax.post('get_calendar_events', { start: date, end: date }, {
    loadingTarget: '#calendar-container',
    loadingText: 'Loading events...'
});

// Form submission
WeCozaAjax.submitForm('save_class', '#class-form')
    .then(data => WeCozaAjax.showSuccess('Class saved!'))
    .catch(error => WeCozaAjax.showError(error.message));
```

### Using EscapeUtils
```javascript
// Always escape user-provided content before injecting into HTML
import { escapeHtml } from './utils/escape.js';

// Instead of:
container.innerHTML = `<td>${userData}</td>`;  // XSS vulnerable!

// Use:
container.innerHTML = `<td>${escapeHtml(userData)}</td>`;  // Safe
```

## Database Integration

### External PostgreSQL Database
- **Host**: DigitalOcean managed PostgreSQL cluster
- **Connection**: Via `DatabaseService` singleton in `app/Services/Database/DatabaseService.php`
- **Primary Table**: `classes` with 25+ fields including JSONB columns
- **Schema File**: `schema/wecoza_db_schema_bu_jan_27.sql` for table structure
- **Migrations**: `includes/migrations/` for schema changes and seeders (run on plugin activation via `class-activator.php`)

### Key JSONB Fields
- `learner_ids`: Complex learner assignments with levels
- `schedule_data`: Per-day scheduling information
- `class_notes_data`: Structured annotations and QA reports
- `qa_reports`: Report metadata and file paths
- `exam_learners`: Exam-specific learner data
- `backup_agent_ids`: Agent backup assignments
- `event_dates`: Class milestones (deliveries, exams, QA visits, etc.)

### Class Types & Subjects (Lookup Tables)
Two normalized tables store class type definitions and their subjects:

- **`class_types`**: `class_type_id`, `class_type_code` (AET/GETC/SOFT/etc.), `class_type_name`, `subject_selection_mode` ('own'/'all_subjects'/'progression'), `progression_total_hours`, `display_order`, `is_active`
- **`class_type_subjects`**: `class_type_subject_id`, `class_type_id` FK, `subject_code`, `subject_name`, `subject_duration`, `display_order`, `is_active`

**Business rules in DB via `subject_selection_mode`:**
- `own` → return only this type's subjects (AET, REALLL, SOFT)
- `all_subjects` → return ALL subjects flattened (package types: WALK, HEXA, RUN)
- `progression` → return single "Learner Progression" placeholder using `progression_total_hours` (GETC, BA2-4)

**Cross-plugin access via WordPress filters:**
```php
$types    = apply_filters('wecoza_classes_get_class_types', []);
$subjects = apply_filters('wecoza_classes_get_subjects', [], 'AET');
```

**Migration**: `includes/migrations/seed-class-types-subjects.php` (version 1.1.0)

### Database Testing Commands
```bash
# Test PostgreSQL connection
wp eval "echo (new WeCozaClasses\Services\Database\DatabaseService())->testConnection();"

# Validate schema
psql -h db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com -p 25060 -U doadmin -d defaultdb -f schema/classes_schema.sql
```

## AJAX Endpoints

### Endpoint Registration (Auto-registration pattern)
AJAX endpoints are configured in `config/app.php` and auto-registered:

```php
'ajax_endpoints' => [
    'save_class' => ['ClassAjaxController', 'saveClassAjax', true, false],
    'get_class_subjects' => ['ClassTypesController', 'getClassSubjectsAjax', true, true],
    // [action => [controller, method, logged_in_users, logged_out_users]]
]
```

### Available Endpoints
```javascript
// Class Operations
WeCozaAjax.post('save_class', formData)
WeCozaAjax.post('update_class', formData)
WeCozaAjax.post('delete_class', { class_id: id })

// Data Retrieval
WeCozaAjax.post('get_class_subjects', { class_type: type })
WeCozaAjax.post('get_calendar_events', { start: date, end: date })
WeCozaAjax.post('get_class_notes', { class_id: id })

// QA System
WeCozaAjax.post('get_qa_analytics', { period: 'monthly' })
WeCozaAjax.post('create_qa_visit', visitData)
WeCozaAjax.post('export_qa_reports', { format: 'pdf' })
```

### Shortcodes (5 available)
```php
[wecoza_capture_class]           # Class creation form
[wecoza_display_classes]         # All classes table with search/pagination
[wecoza_display_single_class id="123"] # Single class details
[qa_dashboard_widget]            # QA dashboard widget for admin
[qa_analytics_dashboard]         # Full QA analytics with Chart.js
```

## Asset Management

### Conditional Loading System
Assets load only on pages that need them via WordPress `wp_enqueue_scripts`:

```php
// Check if shortcode present before loading assets
if (has_shortcode($content, 'wecoza_capture_class')) {
    wp_enqueue_script('class-capture-js');
    wp_enqueue_style('bootstrap-css');
}
```

### JavaScript Files (`assets/js/`)
| File | Dependencies | Purpose |
|------|-------------|---------|
| class-capture.js | jquery, escape-utils, date-utils | Form handling and validation |
| class-schedule-form.js | jquery, learner-level-utils, date-utils | Per-day scheduling interface |
| classes-table-search.js | jquery | Legacy search (use TableManager for new code) |
| learner-selection-table.js | jquery, escape-utils | Learner assignment UI |
| single-class-display.js | jquery, calendar, escape-utils, date-utils | Single class view logic |
| wecoza-calendar.js | jquery | FullCalendar integration |
| qa-dashboard.js | jquery | Chart.js analytics visualizations |

### CSS Integration
**ALL CSS styles must be added to**: `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`

Never create separate CSS files in plugin directories.

## Development Workflows

### Adding New AJAX Endpoint
1. Add endpoint configuration to `config/app.php` ajax_endpoints array
2. Implement handler method in appropriate controller (ClassAjaxController for class operations)
3. Endpoints are auto-registered via the configuration
4. Test via browser developer tools or WeCozaAjax utility

### Adding New View Component
1. Create component file in `app/Views/components/` (e.g., `single-class/new-section.php`)
2. Use `$variable` syntax - data is extracted from the passed array
3. Include in parent view: `\WeCozaClasses\component('single-class/new-section', $component_data);`
4. Always escape output: `esc_html()`, `esc_attr()`, `esc_url()`

### Adding New Shortcode
1. Add shortcode to `config/app.php` shortcodes array
2. Implement method in controller
3. Create corresponding view file in `app/Views/components/`
4. Test rendering with `do_shortcode()` function

### Database Schema Changes
1. Create migration file in `includes/migrations/`
2. Update `schema/classes_schema.sql`
3. Test locally before production deployment
4. Document JSONB field changes for complex data structures

## Security Patterns

### PHP Security
- **SQL Injection**: All queries use PDO prepared statements
- **XSS Prevention**: 95%+ coverage with `esc_html()`, `esc_attr()`, `esc_url()`
- **Nonce Verification**: AJAX handlers verify nonces
- **Capability Checks**: `current_user_can()` used consistently
- **Variable Extraction**: `EXTR_SKIP` flag prevents variable collision

### JavaScript Security
- **XSS Prevention**: Use `escapeHtml()` from `utils/escape.js` for all user content
- **Nonce Handling**: Handled automatically by `WeCozaAjax` utility

```javascript
// NEVER do this:
container.innerHTML = `<td>${userInput}</td>`;

// ALWAYS do this:
container.innerHTML = `<td>${escapeHtml(userInput)}</td>`;
```

## Testing Approach

### Manual Testing Framework
- **Admin Interface**: WordPress admin pages for functionality validation
- **Browser Testing**: JavaScript functionality through browser console
- **Interactive Demos**: Search/pagination testing via frontend shortcodes
- **PHP Syntax Check**: `php -l` on all modified files

### Testing Commands
```bash
# Test shortcode rendering
wp eval "echo do_shortcode('[wecoza_display_classes]');"

# Test AJAX endpoint
curl -X POST -d "action=get_class_subjects&class_type=skills" http://localhost/wp-admin/admin-ajax.php

# Test database connection
wp eval "echo (new WeCozaClasses\Services\Database\DatabaseService())->getConnection() ? 'Connected' : 'Failed';"

# PHP syntax check
php -l app/Controllers/ClassController.php
```

## Specification Documents

Feature specifications are maintained in `docs/`:
- `SPEC-event-dates.md` - Event Dates tracking system (deliveries, exams, QA visits)
- `SPEC-event-dates-statistics.md` - Event Dates display in Schedule Statistics
- `WORDPRESS-SIMPLIFY-REPORT.md` - Refactoring progress and architecture overview

## Daily Reports

- **Location**: `daily-updates/WEC-DAILY-WORK-REPORT-YYYY-MM-DD.md`
- **Template**: `daily-updates/end-of-day-report.md`
- **Content**: Executive summary, commit table, detailed changes, QA notes, blockers

## Important Development Notes

- **External Database**: All data operations use PostgreSQL, not WordPress MySQL
- **JSONB Fields**: Complex data structures stored as JSON for flexibility
- **No Build System**: Direct file editing with WordPress asset management
- **Component Views**: Reusable templates with data extraction pattern
- **Conditional Assets**: Scripts/styles load only when shortcodes are present
- **Role-Based Access**: WordPress capabilities system controls feature access
- **Manual Testing**: Validation through WordPress admin interface and browser testing
- **XSS Prevention**: Always use `escapeHtml()` in JavaScript for user content
- **AJAX Utilities**: Use `WeCozaAjax` for standardized request handling
- **Table Management**: Use `WeCozaTableManager` for new search/pagination features
- **Plugin Activation**: Migrations run automatically via `includes/class-activator.php`
- **Transient Caching**: ClassTypesController caches DB lookups with 2-hour TTL via WordPress transients
- **Schema Backups**: `schema/wecoza_db_schema_bu_<mon>_<dd>.sql` — date-stamped, old ones get deleted
