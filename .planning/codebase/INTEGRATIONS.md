# External Integrations

**Analysis Date:** 2026-01-22

## APIs & External Services

**FullCalendar:**
- Service - Interactive calendar UI component for class scheduling
- What it's used for - Display class schedule on calendar view with event management
- SDK/Client: CDN-based library - `https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js`
- Version: 6.1.15+
- Integration point: `app/Controllers/ClassController.php` (enqueueAssets method, line 150-156)
- Frontend: `assets/js/wecoza-calendar.js` - Initializes and manages calendar instance

**Chart.js:**
- Service - Data visualization library for analytics dashboards
- What it's used for - QA analytics dashboards with multiple chart types (bar, pie, line)
- SDK/Client: CDN-based library - `https://cdn.jsdelivr.net/npm/chart.js`
- Version: 4.4.0
- Integration point: `app/Controllers/QAController.php` (enqueueAssets method, line 52)
- Frontend: `assets/js/qa-dashboard.js` - Creates chart instances from server-provided data

**Public Holidays System:**
- Service - Static South African public holiday calendar (TODO: external API integration)
- What it's used for - Holiday conflict detection during class scheduling
- Current Implementation: Hard-coded South African holidays in `PublicHolidaysController::getHolidaysByYear()`
- Planned Enhancement: TODO comment indicates future integration with database or external holiday API
- Frontend Integration: Public holidays data injected into JavaScript via `wp_localize_script`

## Data Storage

**Databases:**

*Primary (External PostgreSQL):*
- Provider: DigitalOcean Managed PostgreSQL
- Connection: Via PDO with credentials stored in WordPress options
- Client: PHP PDO with PostgreSQL driver
- Connection Details:
  - Host: `db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com`
  - Port: 25060 (non-standard)
  - Credentials: Stored as WordPress options (wecoza_postgres_*)
- Connection Class: `WeCozaClasses\Services\Database\DatabaseService` (singleton pattern)
- Location: `app/Services/Database/DatabaseService.php`

*Secondary (WordPress MySQL):*
- Purpose: WordPress native data only (users, posts, pages, options)
- Not used for class/QA data
- Contains PostgreSQL credentials as serialized options

**Tables in PostgreSQL:**
- `classes` (primary entity with 20+ fields including JSONB)
- `qa_visits`, `qa_metrics`, `qa_findings` (QA reporting)
- `clients`, `agents`, `sites` (organizational structure)
- `learners`, `users` (participant management)
- 45+ total tables for comprehensive training management

**JSONB Fields (Flexible Data Storage):**
- `learner_ids` - Complex learner assignments with levels
- `schedule_data` - Per-day scheduling information
- `class_notes_data` - Structured annotations and QA reports
- `qa_reports` - Report metadata and file paths
- `exam_learners` - Exam-specific learner data
- `backup_agent_ids` - Agent backup assignments

**File Storage:**
- Local filesystem only - WordPress upload directory
- Upload path: `/wp-content/uploads/wecoza-classes/`
- Max file size: 10MB (configured in `config/app.php`)
- Allowed types: pdf, doc, docx, xls, xlsx, jpg, jpeg, png
- Secure uploads enforced via WordPress media library

**Caching:**
- WordPress transients (WordPress built-in cache API)
- Cache groups configured:
  - `classes` - 30 minutes
  - `class_types` - 2 hours
  - `public_holidays` - 24 hours
- Location: `config/app.php` (cache configuration)

## Authentication & Identity

**Auth Provider:**
- Custom/WordPress Native
- Implementation: WordPress user authentication with capability system
- Role-Based Access Control:
  - `manage_options` - Full admin access to QA analytics and class management
  - `edit_posts` - Create/edit classes
  - `delete_posts` - Delete classes
  - `edit_users` - Manage learners
  - `read` - View reports

**Access Control Pattern:**
```php
// Typical security check in ClassController
if (!\current_user_can('manage_options') || \get_transient('wecoza_pages_checked')) {
    wp_die('Insufficient permissions');
}
```

**AJAX Nonce Verification:**
- Nonce generation: `wp_create_nonce('wecoza_class_nonce')`
- Nonce verification: `wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')`
- Location: `app/Controllers/ClassController.php` (line 680)

**Security Functions Used:**
- `current_user_can()` - Permission verification
- `wp_verify_nonce()` - AJAX request validation
- `sanitize_text_field()` - Input sanitization
- `sanitize_textarea_field()` - Multi-line input sanitization
- `wp_kses_post()` - HTML content filtering (not extensively used)

## Monitoring & Observability

**Error Tracking:**
- WordPress native error logging via `error_log()`
- Conditional logging based on `WP_DEBUG` constant
- Errors logged to WordPress debug.log file
- Examples:
  - Database connection errors in `DatabaseService`
  - Query execution errors with SQL and parameters logged
  - Plugin initialization errors in bootstrap

**Logs:**
- File-based logging to WordPress `wp-content/debug.log`
- Log prefix: `"WeCoza Classes Plugin: [level]:"`
- No external log aggregation service (Sentry, LogRocket, etc.)
- Debug mode detection:
  ```php
  if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('Message here');
  }
  ```

**Performance Monitoring:**
- Database query logging in debug mode
- No APM (Application Performance Monitoring) integrated
- No error tracking beyond WordPress logs

## CI/CD & Deployment

**Hosting:**
- Shared hosting via XAMPP (local development)
- Production deployment: WordPress.com or similar managed hosting
- CDN: jsdelivr.net for library delivery (FullCalendar, Chart.js)

**CI Pipeline:**
- None detected - Manual deployment workflow
- No GitHub Actions, GitLab CI, or Jenkins configuration
- Plugin activation handled via WordPress admin interface

**Build Process:**
- No build step required
- Direct PHP file deployment
- Database connection configured via WordPress options (no migration files in standard format)

**Deployment Notes:**
- Requires PostgreSQL connection credentials configured in WordPress options
- CDN dependencies (FullCalendar, Chart.js) must be accessible from production server
- WordPress plugin activation runs hooks in `class-activator.php`:
  - Creates required WordPress pages
  - Sets default options
  - Creates upload directories
  - Flushes rewrite rules

## Environment Configuration

**Required Environment Variables:**
- `WECOZA_CLASSES_VERSION` - Dynamic cache-busting version (timestamp)
- `WP_DEBUG` - Enables detailed error logging

**WordPress Options (Stored in wp_options table):**
- `wecoza_postgres_host` - PostgreSQL server hostname
- `wecoza_postgres_port` - PostgreSQL server port
- `wecoza_postgres_dbname` - PostgreSQL database name
- `wecoza_postgres_user` - PostgreSQL username
- `wecoza_postgres_password` - PostgreSQL password (CRITICAL - must be set)
- `wecoza_classes_plugin_activated` - Activation flag
- `wecoza_classes_plugin_version` - Plugin version tracking
- `admin_email` - Used for notification sender email
- `bloginfo('name')` - Used for notification sender name

**Secrets Location:**
- PostgreSQL credentials stored as WordPress options in wp_options table
- Not recommended for production (should use WordPress secrets API or .env alternative)
- Exposed in WordPress admin if user has database access permissions

## Webhooks & Callbacks

**Incoming Webhooks:**
- None detected

**Outgoing Webhooks:**
- None detected

**Internal AJAX Endpoints (15+ handlers):**
```
// Class Management
wp.ajax.post('save_class', data)                    - Create new class
wp.ajax.post('update_class', data)                  - Update existing class
wp.ajax.post('delete_class', {class_id: id})        - Delete class

// Data Retrieval
wp.ajax.post('get_class_subjects', {class_type: type})     - Fetch subjects by type
wp.ajax.post('get_public_holidays', data)                  - Get holidays
wp.ajax.post('get_calendar_events', {start, end})          - Calendar event data
wp.ajax.post('get_class_notes', {class_id: id})            - Fetch class notes

// QA Management
wp.ajax.post('get_qa_analytics', {period: 'monthly'})      - QA analytics data
wp.ajax.post('get_qa_summary', data)                       - QA summary stats
wp.ajax.post('get_qa_visits', data)                        - QA visit history
wp.ajax.post('create_qa_visit', visitData)                 - Create QA visit
wp.ajax.post('export_qa_reports', {format: 'pdf'})         - Export reports

// Notes & Attachments
wp.ajax.post('save_class_note', noteData)                  - Save class note
wp.ajax.post('delete_class_note', {note_id: id})           - Delete note
wp.ajax.post('submit_qa_question', questionData)           - Submit QA question
wp.ajax.post('upload_attachment', formData)                - File upload
wp.ajax.post('delete_qa_report', {report_id: id})          - Delete QA report
```

**Endpoint Location:** `app/ajax-handlers.php` (registered via bootstrap)
**Nonce Protection:** All endpoints use `wp_verify_nonce` verification
**Access Control:** Many endpoints restricted to authenticated users (`public: false`)

## External API Usage

**jQuery (WordPress Bundled):**
- Used for DOM manipulation and AJAX calls
- AJAX calls use jQuery's AJAX wrapper around WordPress AJAX handler
- No direct external API calls via jQuery

**Fetch API (Modern JavaScript):**
- Used in `assets/js/class-types.js` for fetching class subjects
- Request URL: WordPress AJAX endpoint with action parameter
- No external API dependencies

**XMLHttpRequest:**
- Used in `assets/js/class-capture.js` for file uploads
- Direct implementation for legacy browser support
- Internal endpoints only (WordPress admin-ajax.php)

**No Third-Party Integrations Detected:**
- No Stripe, PayPal, or payment gateway integration
- No email service provider (SendGrid, Mailgun) integration
- No SMS provider integration
- No social media OAuth integration
- No Slack/Teams webhooks
- No external file storage (AWS S3, Google Drive)

---

*Integration audit: 2026-01-22*
