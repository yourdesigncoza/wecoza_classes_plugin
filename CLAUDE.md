# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## WordPress Plugin Architecture

This is a **WeCoza Classes Plugin** - a comprehensive class management system for training programs with a clean MVC architecture and external PostgreSQL database integration.

### Core Architecture
- **MVC Structure**: Controllers, Models, Views with PSR-4 autoloading
- **External Database**: PostgreSQL (not WordPress MySQL) with 45+ tables
- **Namespace**: `WeCozaClasses\` for all plugin classes
- **Bootstrap**: `app/bootstrap.php` handles autoloading and initialization

### Key Files Structure
```
wecoza-classes-plugin.php     # Main plugin file with constants and activation hooks
app/bootstrap.php             # MVC application bootstrap with autoloader
config/app.php               # Comprehensive configuration (controllers, AJAX, shortcodes)
app/Controllers/             # Business logic (4 main controllers)
app/Models/                 # Data layer with PostgreSQL integration
app/Views/                  # Component-based presentation layer
app/Services/Database/      # Database abstraction layer
```

## Database Integration

### External PostgreSQL Database
- **Host**: DigitalOcean managed PostgreSQL cluster
- **Connection**: Via `DatabaseService` singleton in `app/Services/Database/DatabaseService.php`
- **Primary Table**: `classes` with 20+ fields including JSONB columns
- **Schema File**: `schema/classes_schema.sql` for table structure

### Key JSONB Fields
- `learner_ids`: Complex learner assignments with levels
- `schedule_data`: Per-day scheduling information  
- `class_notes_data`: Structured annotations and QA reports
- `qa_reports`: Report metadata and file paths
- `exam_learners`: Exam-specific learner data
- `backup_agent_ids`: Agent backup assignments

### Database Testing Commands
```bash
# Test PostgreSQL connection
wp eval "echo (new WeCozaClasses\Services\Database\DatabaseService())->testConnection();"

# Validate schema
psql -h db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com -p 25060 -U doadmin -d defaultdb -f schema/classes_schema.sql
```

## Controllers & Endpoints

### Main Controllers (`config/app.php`)
1. **ClassController** - Core class management, AJAX handlers, shortcode registration
2. **ClassTypesController** - Class types and subject management
3. **PublicHolidaysController** - Holiday detection and override system
4. **QAController** - Quality assurance analytics and dashboard

### AJAX Endpoints (15+ endpoints)
```javascript
// Class Operations
wp.ajax.post('save_class', data)
wp.ajax.post('update_class', data) 
wp.ajax.post('delete_class', {class_id: id})

// Data Retrieval
wp.ajax.post('get_class_subjects', {class_type: type})
wp.ajax.post('get_calendar_events', {start: date, end: date})
wp.ajax.post('get_class_notes', {class_id: id})

// QA System
wp.ajax.post('get_qa_analytics', {period: 'monthly'})
wp.ajax.post('create_qa_visit', visitData)
wp.ajax.post('export_qa_reports', {format: 'pdf'})
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

### Key JavaScript Files (`assets/js/`)
- `class-capture.js` - Form handling and validation
- `class-schedule-form.js` - Per-day scheduling interface  
- `classes-table-search.js` - Search and pagination
- `wecoza-calendar.js` - FullCalendar integration
- `qa-dashboard.js` - Chart.js analytics visualizations

### CSS Integration
**ALL CSS styles must be added to**: `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`

Never create separate CSS files in plugin directories.

## Development Workflows

### Plugin Activation Workflow
```bash
# Plugin creates required WordPress pages automatically
# Check page creation in includes/class-activator.php

# Test activation
wp plugin activate wecoza-classes-plugin
wp plugin list --status=active | grep wecoza
```

### Asset Development
```bash
# No build system - direct file editing
# JavaScript files load via WordPress wp_enqueue_scripts
# CSS modifications go to theme child CSS file only

# Test asset loading
wp eval "do_action('wp_enqueue_scripts');"
```

### Database Schema Updates
```bash
# Manual migration files in includes/migrations/
# Run via WordPress admin or wp-cli

wp eval "require_once 'includes/migrations/add_exam_learners_field.sql';"
```

## Testing Approach

### Manual Testing Framework
- **Admin Interface**: WordPress admin pages for functionality validation
- **Browser Testing**: JavaScript functionality through browser console
- **Interactive Demos**: Search/pagination testing via frontend shortcodes
- **No Automated Testing**: Relies on WordPress admin interface validation

### Testing Commands
```bash
# Test shortcode rendering
wp eval "echo do_shortcode('[wecoza_display_classes]');"

# Test AJAX endpoint
curl -X POST -d "action=get_class_subjects&class_type=skills" http://localhost/wp-admin/admin-ajax.php

# Test database connection
wp eval "echo (new WeCozaClasses\Services\Database\DatabaseService())->getConnection() ? 'Connected' : 'Failed';"
```

## Key Features Implementation

### Calendar Integration
- **FullCalendar**: Frontend calendar with class scheduling
- **Public Holidays**: API integration with override system
- **Per-day Scheduling**: JSONB field `schedule_data` stores daily time slots

### QA Analytics System  
- **Chart.js Integration**: Multi-chart dashboard with filtering
- **Visit Management**: QA visit scheduling and tracking
- **Report Generation**: PDF/Excel export functionality
- **Dashboard Widget**: Summary statistics for admin homepage

### Class Management Features
- **Learner Auto-population**: Database-driven learner assignment
- **Level Management**: 50+ level types with validation
- **Agent Assignment**: Primary and backup agent management  
- **SETA Integration**: Funding and compliance tracking
- **File Uploads**: Secure document management with validation

## WordPress Integration Patterns

### Capability-Based Security
```php
// Check user permissions before operations  
if (!current_user_can('edit_posts')) {
    wp_die('Insufficient permissions');
}

// Role-based feature access
$capabilities = get_user_class_capabilities();
if ($capabilities['can_delete_classes']) {
    // Show delete button
}
```

### WordPress Hook Usage
```php
// Plugin initialization
add_action('init', 'initialize_plugin_controllers');
add_action('wp_enqueue_scripts', 'load_conditional_assets'); 
add_action('wp_ajax_*', 'handle_ajax_requests');

// Shortcode registration
add_shortcode('wecoza_capture_class', 'render_class_capture_form');
```

### View Rendering System
```php
// Component-based views with data extraction
echo view('components/class-capture-form', [
    'class_types' => $classTypes,
    'subjects' => $subjects,
    'user_capabilities' => get_user_class_capabilities()
]);
```

## Common Development Tasks

### Adding New AJAX Endpoint
1. Add endpoint to `config/app.php` ajax_endpoints array
2. Implement method in appropriate controller  
3. Register WordPress AJAX hooks in controller constructor
4. Test via browser developer tools or curl

### Adding New Shortcode
1. Add shortcode to `config/app.php` shortcodes array
2. Implement method in controller
3. Create corresponding view file in `app/Views/`
4. Test rendering with `do_shortcode()` function

### Database Schema Changes
1. Create migration file in `includes/migrations/`
2. Update `schema/classes_schema.sql` 
3. Test locally before production deployment
4. Document JSONB field changes for complex data structures

### Asset Management
1. Add JavaScript files to `assets/js/`  
2. Register in controller's `enqueueAssets()` method
3. Use conditional loading based on shortcode presence
4. Add CSS to theme child CSS file only

## Important Development Notes

- **External Database**: All data operations use PostgreSQL, not WordPress MySQL
- **JSONB Fields**: Complex data structures stored as JSON for flexibility
- **No Build System**: Direct file editing with WordPress asset management
- **Component Views**: Reusable templates with data extraction pattern
- **Conditional Assets**: Scripts/styles load only when shortcodes are present
- **Role-Based Access**: WordPress capabilities system controls feature access
- **Manual Testing**: Validation through WordPress admin interface and browser testing