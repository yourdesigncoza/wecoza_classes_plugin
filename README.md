# WeCoza Classes Plugin

A comprehensive class management system for WeCoza training programs. This WordPress plugin handles class creation, scheduling, learner management, and calendar integration with full MVC architecture.

## Features

- **Class Management**: Create, edit, and delete training classes
- **Scheduling System**: Advanced scheduling with calendar integration
- **Learner Management**: Assign and manage learners for classes
- **Calendar Integration**: FullCalendar integration with public holidays
- **Agent Assignment**: Assign agents and supervisors to classes
- **QA Management**: Quality assurance visit tracking and reporting
- **SETA Integration**: SETA funding and compliance tracking
- **Exam Management**: Exam class designation and learner selection
- **Responsive Design**: Bootstrap 5 compatible interface

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
Displays all classes in a responsive table format.

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
- Classes table
- Clients table
- Agents table
- Sites table
- Users table

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
- `class-schedule-form.js`: Scheduling interface
- `class-types.js`: Class type and subject selection
- `wecoza-calendar.js`: Calendar integration

### CSS Files
- `wecoza-classes-public.css`: Public-facing styles
- `wecoza-classes-admin.css`: Admin interface styles
- `wecoza-classes-calendar.css`: Calendar-specific styles

## Development

### File Structure
```
wecoza-classes-plugin/
├── wecoza-classes-plugin.php    # Main plugin file
├── includes/                    # Core plugin classes
├── app/                        # MVC application structure
├── assets/                     # CSS, JS, and images
├── config/                     # Configuration files
├── docs/                       # Documentation
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

## Support

For support and documentation, contact:
- Email: support@yourdesign.co.za
- Website: https://yourdesign.co.za

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Class management functionality
- Calendar integration
- Shortcode system
- MVC architecture implementation
