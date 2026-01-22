# WeCoza Classes Plugin

## Project Architecture Overview

### **Core Structure: WordPress Plugin with External Database**
The WeCoza Classes Plugin is a sophisticated **enterprise-level WordPress plugin** that follows a **clean MVC architecture** built for WordPress but uses **PostgreSQL** (not WordPress MySQL) as the primary database. This separation allows for complex training class management with JSONB data structures while maintaining WordPress integration.

### **Technology Stack Integration**
- **Backend**: PHP 7.4+ with PSR-4 autoloading, PostgreSQL via PDO
- **Frontend**: Bootstrap 5, jQuery, Chart.js for analytics
- **WordPress**: Hook system, capabilities, AJAX infrastructure
- **AI Development**: Claude Code with MCP servers, Task Master AI

## **Architectural Layers**

### **1. Database Layer (PostgreSQL)**
```
External Database (DigitalOcean)
├── classes (primary entity with JSONB fields)
├── qa_visits, qa_metrics, qa_findings
├── clients, agents, sites, learners, users
└── 45+ tables for comprehensive training management
```

### **2. Application Layer (MVC)**
```
app/
├── Controllers/     # Business logic (Class, QA, ClassTypes, PublicHolidays)
├── Models/         # Data models with JSONB handling
├── Views/          # Component-based templates
├── Services/       # DatabaseService (singleton pattern)
└── Helpers/        # ViewHelpers for consistent UI
```

### **3. Integration Layer**
```
WordPress Integration:
├── Plugin bootstrap (wecoza-classes-plugin.php)
├── Activation/Deactivation handlers
├── Shortcode system (5 public shortcodes)
├── AJAX endpoints (15+ handlers)
└── Asset management with conditional loading
```

### **4. Frontend Architecture**
```
assets/js/
├── class-capture.js (34K+ lines) - Main form handling
├── class-schedule-form.js - Scheduling interface
├── qa-dashboard.js - Chart.js analytics
└── Utility modules for search, calendar, learner management
```

## **Documentation Architecture (3-Tier System)**

### **AI-First Documentation Design**
The project implements a sophisticated **3-tier documentation architecture** optimized for AI development:

**Tier 1 (Foundation)**: `/docs/ai-context/` - Stable project-wide context
**Tier 2 (Component)**: Component-level CONTEXT.md files  
**Tier 3 (Feature)**: Implementation-specific docs co-located with code

### **Claude Code Integration**
```
.claude/
├── commands/ (8 specialized workflows)
├── hooks/ (security, context injection, notifications)
├── settings.json (model configuration)
└── sessions/ (historical transcripts)
```

### **MCP Server Integration**
- **task-master-ai**: Task management and planning
- **postgres-do**: Read-only database access

## **Key Architectural Patterns**

### **External Database Pattern**
Unlike typical WordPress plugins, you use PostgreSQL for complex data while WordPress handles authentication/authorization. This enables:
- JSONB fields for flexible data structures
- Complex analytics and reporting
- Enterprise-grade data management

### **Component-Based Frontend**
- Reusable view components
- Bootstrap 5 integration
- Conditional asset loading
- AJAX-driven interactions

### **AI-Enhanced Development**
- Multi-agent orchestration commands
- Automated documentation maintenance
- Context injection via hooks
- Task Master AI integration for project planning

## **Data Management Strategy**

### **JSONB Data Structures**
The plugin leverages PostgreSQL's JSONB for flexible data storage:
- `learner_ids`: Complex learner assignments
- `schedule_data`: Per-day scheduling
- `class_notes_data`: Structured annotations
- `qa_reports`: Report metadata and paths

### **Quality Assurance System**
Comprehensive QA tracking with:
- Visit analytics and reporting
- Chart.js dashboards
- Performance metrics
- Finding management

## **Development Workflow Architecture**

### **Task Master AI Integration**
- PRD parsing and task generation
- Complexity analysis and expansion
- Research-enhanced planning
- Dependency management

### **Automated Quality Processes**
- Documentation synchronization
- Security scanning via MCP
- Git safety workflows
- Centralized CSS management

## **Strengths and Architectural Insights**

1. **Clean Separation**: External database allows complex data management without WordPress constraints
2. **Scalable MVC**: Well-structured for maintenance and feature additions
3. **AI-First Development**: Comprehensive AI tooling for enhanced productivity
4. **Enterprise Features**: QA system, analytics, comprehensive reporting
5. **Modern Frontend**: Bootstrap 5, Chart.js, responsive design

---

A comprehensive class management system for WeCoza training programs. This WordPress plugin handles class creation, scheduling, learner management, and calendar integration with full MVC architecture.

## Features

- **Class Management**: Create, edit, and delete training classes with comprehensive form validation
- **Advanced Search & Filtering**: Real-time client-side search functionality with pagination
- **Scheduling System**: Advanced scheduling with calendar integration and per-day time management
- **Public Holidays Integration**: Smart holiday detection with individual and bulk override capabilities
- **Learner Management**: Assign and manage learners with status tracking and level assignment
- **Calendar Integration**: FullCalendar integration with public holidays detection
- **Agent Assignment**: Assign agents and supervisors with backup agent support
- **QA Management**: Comprehensive quality assurance system with analytics dashboard and visit tracking
- **QA Analytics Dashboard**: Real-time data visualization with Chart.js for performance metrics
- **QA Dashboard Widget**: Compact homepage widget with key metrics and recent activity
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
('wecoza_postgres_host', ''),
('wecoza_postgres_port', ''),
('wecoza_postgres_dbname', ''),
('wecoza_postgres_user', ''),
('wecoza_postgres_password', '');
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

## QA Features

The plugin includes a comprehensive Quality Assurance (QA) system with analytics dashboard and reporting capabilities.

### QA Shortcodes

#### [qa_dashboard_widget]
Displays a compact QA dashboard widget for administrator homepage with key metrics and recent activity.

**Features:**
- Key QA metrics summary (classes visited, visits this month, average rating)
- Recent QA visit highlights with activity feed
- Alert notifications and status indicators
- Mini chart showing 7-day visit trends
- Quick action buttons and navigation links
- Auto-refresh functionality (5-minute intervals)

**Parameters:**
- `show_charts`: Enable/disable mini chart display (default: true)
- `show_summary`: Enable/disable metrics summary (default: true)
- `limit`: Number of recent visits to show (default: 5)

**Example:**
```
[qa_dashboard_widget]
[qa_dashboard_widget show_charts="false" limit="10"]
```

#### [qa_analytics_dashboard]
Displays the full QA analytics dashboard with comprehensive data visualization and reporting.

**Features:**
- Monthly visit completion rates with interactive line charts
- Average ratings by department/subject with bar charts
- Officer performance metrics with doughnut charts
- Trending issues analysis with horizontal bar charts
- Date range filtering and department selection
- Export functionality for CSV reports
- Real-time AJAX data updates
- Responsive design for mobile and desktop

**Parameters:**
- None required - dashboard is fully interactive with built-in controls

**Example:**
```
[qa_analytics_dashboard]
```

### QA Analytics Dashboard

The QA Analytics Dashboard provides comprehensive quality assurance tracking and reporting capabilities:

#### Dashboard Components

1. **Key Metrics Summary**
   - Total QA visits for selected period
   - Number of classes visited
   - Active QA officers count
   - Overall average rating

2. **Data Visualizations**
   - **Monthly Completion Rates**: Line chart showing visit trends over time
   - **Ratings by Department**: Bar chart comparing average ratings across departments
   - **Officer Performance**: Doughnut chart showing visit distribution by officer
   - **Trending Issues**: Horizontal bar chart highlighting common problems

3. **Interactive Controls**
   - Date range picker (start/end dates)
   - Department filter dropdown
   - Refresh dashboard button
   - Export reports button (CSV format)

4. **Recent Activity Feed**
   - Latest QA visits with class details
   - Visit notes and observations
   - Sortable table with pagination

5. **Alerts & Notifications**
   - Follow-up visits required
   - Safety issues needing attention
   - Overdue action items

#### Database Schema

The QA system uses three main PostgreSQL tables:

```sql
-- Main QA visits table
CREATE TABLE qa_visits (
    visit_id SERIAL PRIMARY KEY,
    class_id INTEGER NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME,
    visit_type VARCHAR(50) DEFAULT 'routine',
    qa_officer_id INTEGER,
    overall_rating INTEGER CHECK (overall_rating >= 1 AND overall_rating <= 5),
    findings JSONB DEFAULT '[]'::jsonb,
    recommendations JSONB DEFAULT '[]'::jsonb,
    action_items JSONB DEFAULT '[]'::jsonb,
    -- ... additional fields
);

-- Analytics aggregation table
CREATE TABLE qa_metrics (
    metric_id SERIAL PRIMARY KEY,
    metric_period VARCHAR(20) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    class_id INTEGER,
    total_visits INTEGER DEFAULT 0,
    average_rating DECIMAL(3,2),
    -- ... additional metrics
);

-- Individual findings tracking
CREATE TABLE qa_findings (
    finding_id SERIAL PRIMARY KEY,
    visit_id INTEGER NOT NULL,
    finding_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'open'
    -- ... additional fields
);
```

#### API Endpoints

The QA system provides RESTful API endpoints for data management:

- `GET /wp-admin/admin-ajax.php?action=get_qa_analytics` - Dashboard analytics data
- `GET /wp-admin/admin-ajax.php?action=get_qa_summary` - Widget summary data
- `GET /wp-admin/admin-ajax.php?action=get_qa_visits` - Visit history by class
- `POST /wp-admin/admin-ajax.php?action=create_qa_visit` - Create new visit record
- `POST /wp-admin/admin-ajax.php?action=export_qa_reports` - Export data as CSV

#### WordPress Integration

- **Admin Menu**: QA Analytics menu item in WordPress admin (requires `manage_options` capability)
- **Asset Management**: Automatic enqueuing of Chart.js and custom JavaScript
- **Security**: Nonce verification and capability checks on all endpoints
- **Caching**: WordPress transients for performance optimization

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
- **QA tables**: Quality assurance system with visit tracking and analytics
  - **qa_visits**: Detailed QA visit records with ratings and findings
  - **qa_metrics**: Aggregated analytics data for dashboard reporting
  - **qa_findings**: Individual issue tracking and resolution status
  - **qa_reports**: Legacy QA report file management
  - **agent_qa_visits**: QA officer visit scheduling and tracking

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
│   ├── ClassController.php       # Main class management
│   ├── ClassTypesController.php  # Class types and subjects
│   ├── PublicHolidaysController.php  # Holiday management
│   └── QAController.php          # QA analytics and dashboard
├── Models/         # Data models and database interaction
│   ├── ClassModel.php            # Class data management
│   └── QAModel.php               # QA analytics data processing
├── Views/          # Presentation layer (templates)
│   ├── components/               # Reusable UI components
│   ├── qa-analytics-dashboard.php  # Full QA analytics dashboard
│   └── qa-dashboard-widget.php     # Compact QA widget
├── Services/       # Shared services (database, file upload, etc.)
│   └── Database/
│       └── DatabaseService.php  # PostgreSQL connection handling
└── Helpers/        # View helpers and utility functions
    ├── ViewHelpers.php           # General view utilities
    └── view-helpers-loader.php   # Helper loading system
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
- `qa-dashboard.js`: QA analytics dashboard and widget functionality with Chart.js integration

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

### QA Integration and Advanced Features Implementation (July 2025)
- **QA Analytics Dashboard**: Comprehensive dashboard with Chart.js data visualization
  - Monthly visit completion rates with interactive line charts
  - Average ratings by department/subject with bar charts
  - Officer performance metrics with doughnut charts
  - Trending issues analysis with horizontal bar charts
- **QA Dashboard Widget**: Compact homepage widget with key metrics and recent activity
  - Auto-refresh functionality (5-minute intervals)
  - Mini chart showing 7-day visit trends
  - Alert notifications and quick action buttons
- **Database Schema Enhancement**: Advanced QA schema with three main tables
  - `qa_visits`: Detailed visit records with ratings and findings
  - `qa_metrics`: Aggregated analytics data for dashboard reporting
  - `qa_findings`: Individual issue tracking and resolution status
- **API Endpoints**: RESTful AJAX endpoints for QA data management
  - Dashboard analytics data retrieval
  - Widget summary data for homepage
  - Visit history by class with export functionality
- **WordPress Integration**: Admin menu integration with proper security
  - `manage_options` capability requirements
  - Nonce verification and data sanitization
  - Chart.js library integration for visualizations
- **Technical Architecture**: MVC pattern with dedicated QA Controller and Model
  - PostgreSQL compatibility with existing database schema
  - Performance optimization with caching considerations
  - Responsive design for mobile and desktop platforms

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
