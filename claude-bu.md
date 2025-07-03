# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### WordPress Plugin Testing
```bash
# Test plugin functionality via WordPress admin
# Activate plugin: WordPress Admin → Plugins → Activate "WeCoza Classes Plugin"
# Manual testing only - no automated test suite
# Enable WordPress debug mode: WP_DEBUG=true in wp-config.php
# Monitor debug logs at wp-content/debug.log
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

# Test database connection via plugin functionality
# Use PostgreSQL client (pgAdmin, psql) for direct database access
# Never use WordPress $wpdb for classes data - use DatabaseService
```

### Asset Management
```bash
# Pure PHP approach - no build system (no package.json, composer.json, webpack, etc.)
# JavaScript files are automatically enqueued via config/app.php
# Files served directly without compilation
# Development versioning: date('YmdHis') for cache-busting
# Production versioning: Should use static version numbers
# CSS location: /opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css
```

### Plugin Testing Workflow
```bash
# Activate plugin: WordPress Admin → Plugins → Activate "WeCoza Classes Plugin"
# Test pages with shortcodes:
# [wecoza_capture_class] - Class creation form
# [wecoza_display_classes] - Classes table with search
# [wecoza_display_single_class] - Single class view

# Testing Strategy (Manual Testing Only)
# 1. Browser Testing: Test functionality through WordPress admin interface
# 2. Console Monitoring: Check browser console for JavaScript errors
# 3. Debug Log Monitoring: Check wp-content/debug.log for PHP errors
# 4. Database Testing: Verify PostgreSQL operations via database client
# 5. Form Testing: Test class creation, editing, and validation
# 6. AJAX Testing: Monitor network requests in browser DevTools
# 7. Shortcode Testing: Test all shortcodes on actual WordPress pages
```

## Development Setup Requirements

### Local Development Environment
- **XAMPP/WAMP/Local**: For local WordPress development
- **WordPress 5.0+**: Minimum WordPress version required
- **PHP 7.4+**: Minimum PHP version required
- **PostgreSQL**: External database for classes data (not WordPress database)
- **PostgreSQL Client**: pgAdmin, psql, or similar for database management
- **Text Editor**: VS Code, PHPStorm, or similar with PHP support

### Environment Configuration
- **No .env file**: Configuration via WordPress options and config/app.php
- **Database Credentials**: Stored in WordPress options table
- **Asset Versioning**: Uses datetime stamps for development cache-busting
- **Debug Mode**: Enable WP_DEBUG and WP_DEBUG_LOG for development

### Plugin Dependencies
- **WordPress Core**: Only external dependency
- **Bootstrap 5**: Required for styling (loaded via theme)
- **jQuery**: WordPress default jQuery
- **FullCalendar**: For calendar functionality
- **No Build Tools**: Pure PHP approach without package.json, composer.json, webpack, etc.

### Development Setup Steps
1. **Install Local WordPress Environment**: XAMPP, WAMP, or Local
2. **Enable Debug Mode**: Set WP_DEBUG=true in wp-config.php
3. **Configure PostgreSQL**: Set up external database connection
4. **Activate Plugin**: WordPress Admin → Plugins → Activate "WeCoza Classes Plugin"
5. **Test Functionality**: Create test pages with shortcodes

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
- **Schema**: Full schema in `schema/classes_schema.sql`
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

#### Code Development Process
1. **Edit Files**: Direct PHP/JavaScript file editing (no build step required)
2. **Test Changes**: Refresh WordPress pages to see changes immediately
3. **Debug Issues**: Monitor WordPress debug logs and browser console
4. **Validate**: Test all affected functionality manually
5. **Commit Changes**: Git commit with descriptive messages

#### Development Workflow Steps
1. **Local Development**: Make changes to plugin files directly
2. **Immediate Testing**: No compilation needed - changes visible immediately
3. **Error Checking**: Monitor wp-content/debug.log for PHP errors
4. **Browser Testing**: Use DevTools to check JavaScript functionality
5. **Database Validation**: Test PostgreSQL operations via DatabaseService
6. **Manual Testing**: Test all shortcodes and admin functionality

#### Debugging Process
- **WordPress Debug**: Enable WP_DEBUG and WP_DEBUG_LOG in wp-config.php
- **Error Logs**: Check wp-content/debug.log for PHP errors
- **Console Debugging**: Use browser DevTools console for JavaScript issues
- **Network Monitoring**: Monitor AJAX requests in browser DevTools
- **Database Debugging**: Use PostgreSQL client to verify database operations
- **PHP Debugging**: Add error_log() statements for debugging

#### Deployment Process
1. **Version Update**: Update version number in main plugin file
2. **Asset Versioning**: Change from datetime to static version numbers
3. **Database Backup**: Backup PostgreSQL database before deployment
4. **File Upload**: Upload changed files to production server
5. **Plugin Reactivation**: Reactivate plugin to run any new migrations
6. **Testing**: Verify all functionality works in production environment

#### Documentation Management
- **Daily Reports**: Development tracking in `daily-updates/`
  - `WEC-DAILY-WORK-REPORT-*.md`: Generated daily reports with commit analysis
  - `end-of-day-report.md`: Template for generating daily development reports
- **Reference Documentation**: Analysis reports in `reference/` for complex features
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

## Database Migration System

### Migration Files
- **Location**: `includes/migrations/`
- **Available Migrations**:
  - `create-classes-table.php` - Main classes table creation and setup
  - `add_exam_learners_field.sql` - Adds exam learners field to existing tables

### Migration Execution
- **Activation Hook**: Runs automatically during plugin activation
- **Manual Execution**: Via WordPress admin (plugin reactivation)
- **Rollback**: Available via plugin uninstall hook
- **Status Tracking**: Migration status tracked in WordPress options

### Schema Management
- **Primary Schema**: `schema/classes_schema.sql` - Complete database schema
- **Version Control**: Schema changes tracked via individual migration files
- **Database Service**: Use `WeCozaClasses\Services\Database\DatabaseService` for all database operations
- **Migration Format**: PHP files for complex migrations, SQL files for simple schema changes

### Running Migrations
```bash
# Migrations run automatically during plugin activation
# To manually run migrations:
# 1. Deactivate plugin in WordPress admin
# 2. Reactivate plugin to trigger migration hooks
# 3. Check WordPress debug log for migration status
# 4. Verify database changes via PostgreSQL client
```

### Reference Documentation System
The `reference/` directory contains detailed analysis reports for complex features:
- **Analysis Reports**: In-depth technical documentation for major features
- **Implementation Details**: Code structure, data flow, and integration patterns
- **Current Status**: Up-to-date assessment of feature states and recent changes
- **Development Context**: Essential information for understanding complex implementations

Use these reports when working on related features or debugging complex functionality.

### Task Management

#### Core Workflow
1. First think through the problem, read the codebase for relevant files, and write a plan to `/tasks/YYYY-MM-DD-task-name.md`.
2. The plan should have a list of todo items that you can check off as you complete them.
3. Before you begin working, check in with me and I will verify the plan (see Verification Guidelines below).
4. Then, begin working on the todo items, marking them as complete as you go.
5. Please every step of the way just give me a high level explanation of what changes you made.
6. Make every task and code change you do as simple as possible. We want to avoid making any massive or complex changes. Every change should impact as little code as possible. Everything is about simplicity.

#### Tool Integration
- **TodoWrite/Read**: Use for real-time tracking during task execution
- **Markdown files**: Serve as permanent project documentation
- **Workflow**: TodoWrite mirrors the plan from markdown file, then tracks live progress
- **Sequential Thinking**: Use for complex tasks with 5+ subtasks or architectural decisions

#### Task Categories
- **Research**: Codebase exploration, feasibility analysis (use Task/Agent tools)
- **Implementation**: Code writing and modifications
- **Testing**: Validation and verification
- **Refactoring**: Code improvement without changing functionality
- **Documentation**: README updates, inline comments (only when explicitly requested)

#### Task Phases
1. **Discovery Phase**: Use Task/Agent tools, no file modifications
2. **Planning Phase**: Create markdown plan with Sequential Thinking if complex
3. **Implementation Phase**: Execute with TodoWrite tracking
4. **Review Phase**: Update markdown with outcomes and lessons learned

### Task Creation

#### Format Guidelines
- Use checkbox format for all tasks and subtasks
- Auto-update task completion status as work progresses
- Even when user provides a task list, construct your own improved version with better structure and detail
- Mark dependencies: `- [ ] Task name [depends on: #1, #3]`
- Mark blocked tasks: `- [ ] Task name [BLOCKED: reason]`
- Mark failed attempts: `- [ ] Task name [FAILED: reason]`

#### Time Estimation
- **Simple (< 15 min)**: Single file, < 50 lines of code
- **Medium (15-45 min)**: 2-3 files, < 200 lines of code
- **Complex (45+ min)**: Multiple files, architectural changes
- Use these estimates to determine subtask breakdown

#### Subtask Criteria
- Create subtasks when a main task has 3+ distinct steps
- Create subtasks when a task involves multiple technologies/files
- Create subtasks when a task could take 15+ minutes to complete
- Keep simple, single-step tasks as main tasks only

#### Priority Levels
- **Critical**: Blocking issues, security fixes
- **High**: User-requested features, major bugs
- **Medium**: Enhancements, non-critical bugs
- **Low**: Refactoring, optimizations

### Task Execution

#### Verification Guidelines
- **Auto-proceed**: Simple refactoring, bug fixes < 3 files
- **Check-in required**: New features, database changes, 3+ files
- **Always verify**: Breaking changes, deletions, API modifications

#### Git Safety Checkpoints
- Before 3+ file changes: "Reminder: Commit current work?"
- After major feature: "Ready to commit these changes?"
- Add git status check to task completion review

#### Progress Communication Template
```
✓ Completed: [what was done]
→ Next: [what's coming]
⚠ Issue: [any blockers/concerns]
```

#### Error Recovery
- Document failed attempts in task with `[FAILED: reason]`
- Create rollback task if changes need reverting
- Add "Lessons Learned" to review section

#### Quick Actions
- **Quick fix**: For < 5 line changes, skip formal planning
- **Exploratory mode**: For research without formal planning
- **Emergency rollback**: Procedure for critical issues

### Task Completion
1. Mark all TodoWrite items as completed
2. Add a review section to the current `/tasks/YYYY-MM-DD-task-name.md` file with:
   - Summary of changes made
   - Any issues encountered and how they were resolved
   - Lessons learned
   - Git status if multiple files changed
   - Next steps or follow-up tasks if applicable