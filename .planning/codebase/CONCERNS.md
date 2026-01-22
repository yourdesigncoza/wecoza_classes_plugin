# Codebase Concerns

**Analysis Date:** 2026-01-22

## Tech Debt

**Extensive Commented Debug Code:**
- Issue: Over 91 error_log statements commented out throughout codebase, creating dead code clutter
- Files: `app/Controllers/ClassController.php`, `app/Services/Database/DatabaseService.php`, `app/Views/components/class-capture-partials/update-class.php`
- Impact: Makes code harder to read, increases maintenance burden, masks actual logging capability
- Fix approach: Either remove all commented error_log calls or implement proper debug logging abstraction. Create a DebugLogger class that respects WP_DEBUG constant

**Monolithic Controller File:**
- Issue: ClassController.php is 3,955 lines, far exceeding single-responsibility principle
- Files: `app/Controllers/ClassController.php`
- Impact: Difficult to test, navigate, and maintain. Multiple concerns mixed: AJAX handlers, shortcode processing, data processing, file uploads
- Fix approach: Extract into smaller classes: ClassAjaxHandler, ClassFormProcessor, ClassUploadHandler, keeping public method stubs that delegate

**Malformed Error Log Comments:**
- Issue: Error_log lines with syntax error: `/ error_log(...)` instead of `// error_log(...)`
- Files: `app/Controllers/ClassController.php` (lines 808, 823, 1078, 1081, etc.)
- Impact: These are attempting to be comments but are invalid PHP syntax preceded by division operator. When uncommented, would create syntax errors
- Fix approach: Globally replace `/ error_log` with `// error_log` or remove entirely

**Placeholder SQL Comments:**
- Issue: Placeholder values in SQL queries where actual implementation deferred
- Files: `app/Models/QAModel.php` (lines 145, 275: "4.2 as avg_rating -- Placeholder until rating system is implemented")
- Impact: Reports may show hardcoded/false data that doesn't reflect real QA ratings
- Fix approach: Implement actual rating calculation logic or document that these fields are not yet functional

**Static Method Instantiation Pattern:**
- Issue: AJAX handlers register as static methods but instantiate non-static within: `$instance = new self();`
- Files: `app/Controllers/ClassController.php` (saveClassAjax, deleteClassAjax)
- Impact: Inconsistent pattern, harder to test, unclear design intent
- Fix approach: Either use true static methods throughout or convert to instance-based pattern with proper initialization

## Security Concerns

**AJAX Handlers Without Consistent Nonce Verification:**
- Issue: `saveClassAjax()` verifies nonce (line 680), but `getCalendarEventsAjax()`, `getClassSubjectsAjax()`, and read-only endpoints registered with `wp_ajax_nopriv` allowing unauthenticated access
- Files: `app/Controllers/ClassController.php` (lines 34-53)
- Current mitigation: Read-only operations only expose data, nonce verified on write operations
- Recommendations: Add explicit comments documenting why `nopriv` endpoints exist. Consider adding capability checks even for read operations if data is sensitive

**Missing Permission Checks on Some AJAX Endpoints:**
- Issue: `getClassNotesAjax()`, `getCalendarEventsAjax()` don't verify user capabilities
- Files: `app/Controllers/ClassController.php`
- Risk: Unauthenticated users can retrieve class data they may not have permission to access
- Recommendations: Add `current_user_can('read_posts')` or equivalent check before returning data

**File Upload MIME Type Validation Bypass:**
- Issue: File upload validation checks both `$_FILES['file']['type']` (client-provided, spoofable) AND `wp_check_filetype()`, but accepts either
- Files: `app/Controllers/ClassController.php` (lines 3513-3514)
- Risk: Client can set arbitrary MIME type; WordPress check alone might be insufficient
- Fix approach: Only trust `wp_check_filetype()` result, reject if it doesn't match allowed list

**SQL Injection Protection via Prepared Statements:**
- Issue: While using PDO prepared statements (good), some model methods use string interpolation before sanitization
- Files: `app/Models/ClassModel.php` (query methods)
- Current mitigation: DatabaseService uses parameterized queries via PDO
- Recommendations: Audit all SQL building; ensure no string concatenation of user input

**Credentials in WordPress Options:**
- Issue: PostgreSQL credentials stored in wp_options table as plaintext
- Files: `app/Services/Database/DatabaseService.php` (lines 25-29)
- Risk: Anyone with database access or WordPress admin panel access can read database credentials
- Current mitigation: get_option() access controlled by WordPress; password field check (line 32)
- Recommendations: Consider environment variables or encrypted options plugin for production

## Performance Bottlenecks

**N+1 Query Pattern in Data Retrieval:**
- Issue: Enqueued asset method `getSiteAddresses()` queries all sites then iterates to populate JavaScript
- Files: `app/Controllers/ClassController.php` (lines 281-308)
- Problem: Called on every page with shortcode; could be cached
- Improvement path: Cache site addresses in transient for 1 hour, invalidate on client/site updates

**Large JavaScript Bundle for Class Capture:**
- Issue: `class-capture.js` is 3,589 lines; `class-schedule-form.js` is 3,322 lines
- Files: `assets/js/class-capture.js`, `assets/js/class-schedule-form.js`
- Impact: Slow initial page load on class capture form
- Improvement path: Code-split into modules; lazy-load schedule form logic; minify

**View Rendering Complex Data Structures:**
- Issue: View files process JSON, decode arrays, loop through data inline
- Files: `app/Views/components/single-class-display.view.php` (2,564 lines)
- Problem: All data processing happens at view render time; no caching
- Improvement path: Prepare formatted data in controller before passing to view

**Singleton Database Connection Never Reconnected:**
- Issue: DatabaseService uses singleton pattern, connection never tested for staleness
- Files: `app/Services/Database/DatabaseService.php`
- Risk: Long-running processes (cron jobs, webhooks) might use stale connection
- Fix approach: Add connection validation in getInstance(); reconnect if stale

## Fragile Areas

**Schedule Data Reconstruction Logic:**
- Files: `app/Controllers/ClassController.php` (reconstructScheduleData method around line 1094)
- Why fragile: Complex transformation between form arrays and schedule_data JSON structure. Multiple commented-out warning logs suggest previous issues
- Multiple helper methods (validateScheduleDataV2, reconstructScheduleData) with similar logic
- Safe modification: Write comprehensive unit tests for schedule data transformations before any changes

**JSONB Field Parsing Edge Cases:**
- Files: `app/Models/ClassModel.php` (parseJsonField method), `app/Views/components/class-capture-partials/update-class.php` (lines 59-61)
- Why fragile: Code checks `is_string()` to conditionally json_decode; if wrong type assumption, silently uses wrong data
- No validation that decoded JSON matches expected schema
- Safe modification: Create JsonField validation class; validate against schema after decode

**Agent System with Dual Storage:**
- Files: `app/Controllers/ClassController.php` (backup_agent_ids vs agent_replacements confusion)
- Comments at lines 1010-1021 show this is complex design decision
- Why fragile: Two separate systems (backup agents, agent replacements) could be confused; stored in different places (JSON vs separate table)
- Safe modification: Create dedicated AgentManager class encapsulating both systems

**Multiple Date Field Mappings:**
- Files: `app/Controllers/ClassController.php`, `app/Models/ClassModel.php`
- Why fragile: original_start_date, schedule_start_date, class_start_date field name aliases
- Lines 920-924 show conditional fallback logic that could fail if assumptions change
- Safe modification: Establish single canonical field name; migrate all usage before changes

## Test Coverage Gaps

**No Automated Testing:**
- What's not tested: All business logic relies on manual testing through WordPress admin/frontend
- Files: Entire codebase
- Risk: Regressions on schedule data transformations, JSONB manipulations, agent system logic undetected until production
- Priority: HIGH - Critical functionality (class scheduling, agent management) untested
- Recommended approach: Add PHPUnit tests for ClassModel, schedule data processing, QA logic

**AJAX Handler Error Paths Not Tested:**
- What's not tested: ob_start/restore_error_handler flow, JSON response formatting, exception handling
- Files: `app/Controllers/ClassController.php` (saveClassAjax method, lines 652-831)
- Risk: Output buffering cleanup issues could corrupt JSON responses
- Priority: MEDIUM

**Database Connection Failures Not Covered:**
- What's not tested: What happens if PostgreSQL becomes unavailable mid-request
- Files: `app/Services/Database/DatabaseService.php`, `app/Models/ClassModel.php`
- Risk: Silent failures or cryptic error messages
- Priority: MEDIUM

**File Upload Validation Edge Cases:**
- What's not tested: Boundary conditions (exactly 10MB file), double extension attacks, MIME type spoofing
- Files: `app/Controllers/ClassController.php` (uploadAttachment method)
- Priority: MEDIUM

## Known Issues

**Public Holiday Year Range Hardcoded:**
- Symptoms: Public holidays only generated for current and next year (line 54 in PublicHolidaysController shows TODO)
- Files: `app/Controllers/PublicHolidaysController.php` (line 49)
- Trigger: Viewing class schedule form in December or January with multi-year classes
- Workaround: Manually extend holiday year range in controller for longer class schedules
- Fix: Implement configurable year range or query from database

**Missing Database Integration for Holidays:**
- Symptoms: Public holidays are hardcoded South African holidays only; no way to override or add custom holidays
- Files: `app/Controllers/PublicHolidaysController.php` (lines 48-68)
- Trigger: Any organization outside SA or with custom holiday requirements
- Workaround: No workaround available
- Fix approach: Create public_holidays database table, load from there instead of hardcoded array

**Schedule Data Validation Warnings in Commented Code:**
- Symptoms: Multiple error_log statements suggest schedule data validation was problematic
- Files: `app/Controllers/ClassController.php` (lines 1101, 1195, 1433)
- Trigger: Classes with certain schedule data patterns might not validate correctly
- Workaround: Watch error logs when saving classes with complex schedules
- Fix: Uncomment warnings, capture logs, create test cases reproducing issues

## Missing Critical Features

**No Soft Delete or Audit Trail:**
- Problem: deleteClassAjax() performs hard delete; no history of what changed or who deleted
- Blocks: Compliance reporting, accidental deletion recovery, audit trails for regulated industries
- Risk: Cannot determine why historical class data is missing
- Implementation: Add soft_delete flag to classes table, create audit_log table tracking all changes with user/timestamp

**No Transaction Management for Complex Operations:**
- Problem: Class save with multiple related inserts (learners, agents, schedule) not atomic
- Blocks: Data consistency if mid-save failure occurs
- Risk: Orphaned records, incomplete data in database
- Implementation: Wrap entire class save in DatabaseService transaction; rollback on any error

**No Validation Rules Engine:**
- Problem: Validation logic scattered across processFormData, individual model setters
- Blocks: Reusable validation, consistent error messages, easy rule updates
- Risk: Validation bypasses if logic not duplicated correctly across endpoints
- Implementation: Create ValidationRules class defining all business rules in single place

**No Database Migration System:**
- Problem: Schema changes in migrations/ folder but no tracking of which migrations applied
- Blocks: Safe schema evolution, multi-environment deployment, rollback capability
- Risk: Inconsistent database state across environments
- Implementation: Create migration table tracking applied migrations with timestamps

**No Caching Strategy:**
- Problem: Every page load queries same reference data (clients, sites, agents, supervisors)
- Blocks: Performance optimization, offline-first features
- Risk: Unnecessary database load during high-traffic periods
- Implementation: Cache reference data in transients with invalidation on CRUD

## Scaling Limits

**PostgreSQL Connection Pool Limited to 1:**
- Current capacity: Single PDO connection singleton
- Limit: Concurrent requests queued behind single connection
- Scaling path: Implement connection pooling or read replicas; switch to connection pool manager

**Form Data Size No Limit:**
- Current: `class-learners-data` JSON field sent via POST with no size check
- Limit: Large classes (1000+ learners) could hit POST size limits
- Scaling path: Implement AJAX pagination for learner selection; batch inserts via separate requests

**Schedule Data Complexity Unbounded:**
- Current: schedule_data JSON field stores per-day data structure
- Limit: Class with 500 days of schedule data could create very large JSON
- Scaling path: Archive old schedule data to separate table; query only active period

## Dependencies at Risk

**No Version Pinning for FullCalendar:**
- Risk: CDN fallback URL has full version (6.1.17) but no pin in composer/package.json
- Impact: Breaking changes in major version undetected
- Migration plan: Pin FullCalendar in package.json; test before updating

**WordPress Dependency on Dynamic Hook System:**
- Risk: Plugin depends on WordPress add_action/add_shortcode working correctly
- Impact: Changes to WordPress core hooks could break plugin
- Migration plan: Only mitigated by comprehensive testing; no way to pin WordPress

**PostgreSQL Hard Dependency:**
- Risk: Plugin cannot function without external PostgreSQL; no MySQL fallback
- Impact: If cluster goes down, all functionality unavailable
- Migration plan: Implement read-only failover mode using WordPress MySQL cache table

---

*Concerns audit: 2026-01-22*
