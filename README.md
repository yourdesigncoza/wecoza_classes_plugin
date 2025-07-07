# WeCoza Classes Plugin

A comprehensive class management system for WeCoza training programs. This WordPress plugin handles class creation, scheduling, learner management, and calendar integration with full MVC architecture.

## Features

- **Class Management**: Create, edit, and delete training classes with comprehensive form validation
- **Advanced Search & Filtering**: Real-time client-side search functionality with pagination
- **Scheduling System**: Advanced scheduling with calendar integration and per-day time management
- **Public Holidays Integration**: Smart holiday detection with individual and bulk override capabilities
- **Learner Management**: Assign and manage learners with status tracking and level assignment
- **Calendar Integration**: FullCalendar integration with public holidays detection
- **Agent Assignment**: Assign agents and supervisors with backup agent support
- **QA Management**: Quality assurance visit tracking and reporting
- **SETA Integration**: SETA funding and compliance tracking
- **Exam Management**: Exam class designation and learner selection
- **Database Integration**: PostgreSQL integration with comprehensive schema support
- **Responsive Design**: Bootstrap 5 compatible interface with mobile optimization
- **Auto-Population**: Intelligent learner level auto-population based on class subjects
- **Documentation System**: Comprehensive development guides and feature analysis reports

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- PostgreSQL database (for class data)
- Bootstrap 5 (for styling)

## Installation

### Prerequisites
- WordPress 5.0 or higher
- PHP 7.4 or higher
- PostgreSQL database access
- Bootstrap 5 (optional - plugin includes basic styling)

### Step 1: Upload Plugin Files
1. Upload the `wecoza-classes-plugin` folder to `/wp-content/plugins/`
2. Ensure all files are properly uploaded and accessible

### Step 2: Configure Database Connection
Before activating the plugin, configure the PostgreSQL database connection by adding these options to your WordPress database:

```sql
INSERT INTO wp_options (option_name, option_value) VALUES
('wecoza_postgres_host', 'your-database-host'),
('wecoza_postgres_port', '5432'),
('wecoza_postgres_dbname', 'your-database-name'),
('wecoza_postgres_user', 'your-database-user'),
('wecoza_postgres_password', 'your-database-password');
```

Or use WordPress admin to set these options:
- Go to WordPress Admin → Tools → WeCoza Classes Test
- Check database connection status
- Update options if needed

### Step 3: Activate Plugin
1. Go to WordPress Admin → Plugins
2. Find "WeCoza Classes Plugin"
3. Click "Activate"
4. The plugin will automatically create the necessary database tables

### Step 4: Verify Installation
1. Go to WordPress Admin → Tools → WeCoza Classes Test
2. Run the plugin test to verify all components are working
3. Check for any errors in the test results

### Step 5: Create Test Pages
Create WordPress pages with the shortcodes to test functionality:

1. **Class Creation Page**: Add `[wecoza_capture_class]`
2. **All Classes Page**: Add `[wecoza_display_classes]`
3. **Single Class Page**: Add `[wecoza_display_single_class]`

## Shortcodes

### [wecoza_capture_class]
Displays the class capture form for creating and editing classes.

**Parameters:**
- `mode`: 'create' or 'update' (default: 'create')
- `class_id`: Class ID for update mode

**Example:**
```
[wecoza_capture_class]
[wecoza_capture_class mode="update" class_id="123"]
```

### [wecoza_display_classes]
Displays all classes in a responsive table format with search and pagination functionality.

**Features:**
- Real-time client-side search (searches Client ID & Name)
- Pagination with 5 items per page default
- Responsive Bootstrap table design
- Loading indicators and status messages

**Parameters:**
- `limit`: Number of classes to display (default: 50)
- `order_by`: Field to order by (default: 'created_at')
- `order`: Sort order 'ASC' or 'DESC' (default: 'DESC')
- `show_loading`: Show loading indicator (default: true)

**Example:**
```
[wecoza_display_classes]
[wecoza_display_classes limit="25" order_by="class_subject" order="ASC"]
```

### [wecoza_display_single_class]
Displays detailed information for a single class.

**Parameters:**
- `class_id`: Class ID to display (can be passed via URL parameter)

**Example:**
```
[wecoza_display_single_class]
[wecoza_display_single_class class_id="123"]
```

## Configuration

The plugin uses a configuration file at `config/app.php` for various settings:

- Database configuration
- Asset management
- Validation rules
- Calendar settings
- File upload settings

## Database Integration

The plugin integrates with an existing PostgreSQL database containing:
- **Classes table**: Main class data with JSONB fields for flexible data storage
- **Clients table**: Client information and contact details
- **Agents table**: Agent assignments and backup agent management
- **Sites table**: Training site locations and details
- **Users table**: User management and permissions
- **Learners table**: Learner information with status and level tracking

**Database Schema**: Complete schema documentation available in `classes_schema_3.sql`

**Key Features:**
- JSONB support for flexible data structures
- Backward compatibility with legacy data formats
- Comprehensive table relationships and constraints
- Support for both single-time and per-day scheduling formats

## MVC Architecture

The plugin follows a strict MVC (Model-View-Controller) architecture:

```
app/
├── Controllers/     # Business logic and request handling
├── Models/         # Data models and database interaction
├── Views/          # Presentation layer (templates)
├── Services/       # Shared services (database, file upload, etc.)
└── Helpers/        # View helpers and utility functions
```

## Assets

### JavaScript Files
- `class-capture.js`: Form functionality and validation
- `class-schedule-form.js`: Scheduling interface with per-day time management
- `class-types.js`: Class type and subject selection with auto-population
- `classes-table-search.js`: Real-time search and pagination functionality
- `learner-level-utils.js`: Learner level management and auto-population utilities
- `wecoza-calendar.js`: Calendar integration with public holidays
- `wecoza-classes-admin.js`: Admin interface enhancements

### CSS Files
- Styles are managed through the main theme's stylesheet
- Bootstrap 5 compatible responsive design
- Custom styling for search, pagination, and form components

## Development

### File Structure
```
wecoza-classes-plugin/
├── wecoza-classes-plugin.php    # Main plugin file
├── CLAUDE.md                    # Development guide for Claude Code
├── includes/                    # Core plugin classes and migrations
├── app/                        # MVC application structure
│   ├── Controllers/            # Business logic and request handling
│   ├── Models/                # Data models and database interaction
│   ├── Views/                 # Presentation layer (templates)
│   ├── Services/              # Shared services (database, file upload, etc.)
│   └── Helpers/               # View helpers and utility functions
├── assets/                     # CSS, JS, and images
│   └── js/                    # JavaScript files for functionality
├── config/                     # Configuration files
├── reference/                  # Feature analysis and technical documentation
├── daily-updates/              # Development reports and documentation
├── schema/                     # Database schema files
│   └── classes_schema_3.sql    # Database schema reference
└── README.md                   # This file
```

### Hooks and Filters

The plugin provides several hooks for customization:

**Actions:**
- `wecoza_classes_plugin_loaded`: Fired when plugin is loaded
- `wecoza_classes_before_save`: Before saving a class
- `wecoza_classes_after_save`: After saving a class

**Filters:**
- `wecoza_classes_user_capabilities`: Modify user capabilities
- `wecoza_classes_form_data`: Filter form data before processing
- `wecoza_classes_calendar_events`: Filter calendar events

## Testing

The plugin includes a comprehensive testing framework located in the `tests/` directory:

### Available Tests
- **ClassesTableSearchTest.php**: Tests for search functionality
- **ActiveClassesCalculationTest.php**: Tests for class status calculations
- **AgentDataEnrichmentTest.php**: Tests for agent data processing
- **search-demo.html**: Interactive demo for search functionality

### Running Tests
Tests are designed for manual frontend testing rather than automated command-line execution. To test functionality:

1. Use the WordPress admin interface to test plugin features
2. Open test HTML files in browser for JavaScript functionality
3. Monitor browser console for debugging information
4. Verify database operations through the admin interface

## Documentation System

### Development Documentation
- **CLAUDE.md**: Comprehensive development guide for Claude Code with architecture overview, commands, and implementation details
- **README.md**: User-facing documentation with installation, features, and usage instructions

### Reference Documentation (`reference/`)
- **Feature Analysis Reports**: In-depth technical analysis of complex features
- **Implementation Documentation**: Code structure, data flow, and integration patterns
- **Current Status Reports**: Up-to-date assessments of feature states and recent changes

### Daily Development Reports (`daily-updates/`)
- **end-of-day-report.md**: Template for generating daily development reports
- **WEC-DAILY-WORK-REPORT-*.md**: Generated daily reports with commit analysis and progress tracking

## Development Workflow

### Code Standards
- **Function Prefixing**: All functions prefixed with `classes_` or `wecoza_classes_`
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **MVC Architecture**: Strict separation of concerns with MVC pattern
- **Backward Compatibility**: Maintains compatibility with existing data structures

## Support

For support and documentation, contact:
- Email: support@yourdesign.co.za
- Website: https://yourdesign.co.za

## License

This plugin is licensed under the GPL v2 or later.

## Recent Updates

### Class Creation Workflow Enhancement (June 2025)
- **Automatic Redirect**: After successful class creation, users are automatically redirected to the single class display page
- **URL Construction**: Uses WordPress best practices with `get_page_by_path()`, `get_permalink()`, and `add_query_arg()`
- **Error Resolution**: Fixed AJAX form submission errors related to JSON field processing
- **Exam Learners Field**: Added dedicated `exam_learners` database field for better data separation
- **Response Enhancement**: Server returns redirect URL in AJAX success response for seamless UX

### AJAX & Form Processing (June 2025)
- **JSON Field Processing**: Fixed `processJsonField()` method to handle both array and string inputs
- **Schedule Data Reconstruction**: Added `reconstructScheduleData()` method for complex form array structures
- **Error Handling**: Enhanced error logging and debugging capabilities
- **Field Mapping**: Corrected field name mappings (schedule_start_date → original_start_date, seta_id → seta)
- **Nonce Security**: Improved AJAX security with proper nonce verification

### Documentation & Analysis (June 2025)
- **CLAUDE.md**: Comprehensive development guide with architecture overview and commands
- **Reference Documentation**: Detailed analysis reports for complex features including public holidays integration
- **Development Workflow**: Enhanced documentation for future development work

### Public Holidays System (June 2025)
- **Restored Functionality**: Public holidays section fully active in class creation forms
- **Smart Detection**: Only shows holidays that conflict with scheduled class days
- **Override System**: Individual and bulk holiday override capabilities
- **Form Reorganization**: Improved logical flow of form sections

### Search & Pagination System (June 2025)
- **Real-time Search**: Client-side search functionality for classes table
- **Pagination**: 5 items per page with Bootstrap navigation controls
- **Performance**: Debounced search with optimized filtering
- **User Experience**: Search status indicators and responsive design

### Learner Management Enhancement (June 2025)
- **Auto-Population**: Intelligent learner level assignment based on class subjects
- **Level Management**: Comprehensive learner level utilities (50+ level types)
- **Status Tracking**: Enhanced learner status options (CIC, RBE, DRO)
- **Backward Compatibility**: Support for legacy data formats

### Database & Architecture (June 2025)
- **Schema Documentation**: Complete database schema reference
- **JSONB Integration**: Flexible data storage for complex structures
- **Migration System**: Database migration support for updates
- **Reference System**: Comprehensive analysis documentation in `reference/` directory

## Changelog

### Version 1.0.0
- Initial release
- Class management functionality
- Calendar integration
- Shortcode system
- MVC architecture implementation
- PostgreSQL database integration
- Search and pagination functionality
- Learner management system
- Agent assignment features
