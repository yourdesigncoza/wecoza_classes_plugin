# Testing Patterns

**Analysis Date:** 2026-01-22

## Test Framework

**Runner:**
- Not detected - No automated test runner installed
- PHPUnit: Not configured
- Jest/Vitest: Not configured

**Assertion Library:**
- None detected

**Run Commands:**
```bash
# No automated test commands available
# Testing relies on manual verification
```

## Test File Organization

**Location:**
- No dedicated test directory found
- No *.test.php or *.spec.php files in codebase

**Naming:**
- Not applicable - no test files present

**Structure:**
- Not applicable - no test files present

## Testing Approach

**Manual Testing Framework:**
- Testing conducted via WordPress admin interface
- Browser console testing for JavaScript functionality
- Interactive demos through frontend shortcodes

**Test Commands:**
```bash
# Test shortcode rendering
wp eval "echo do_shortcode('[wecoza_display_classes]');"

# Test AJAX endpoint via curl
curl -X POST -d "action=get_class_subjects&class_type=skills" http://localhost/wp-admin/admin-ajax.php

# Test database connection
wp eval "echo (new WeCozaClasses\Services\Database\DatabaseService())->getConnection() ? 'Connected' : 'Failed';"

# Plugin activation test
wp plugin activate wecoza-classes-plugin
wp plugin list --status=active | grep wecoza
```

## Test Structure

**Suite Organization:**
- No formal test suites defined
- Manual verification through browser and WordPress admin

**Patterns:**
- No setup/teardown pattern used
- No fixtures or test data factories
- No assertion patterns (manual verification)

## Debugging & Validation Approach

**Console Logging:**
- JavaScript debugging via console.log() statements throughout codebase
- Found in: `app/Views/components/single-class-display.view.php`
- Examples:
```javascript
console.log('Calendar container found, initializing...');
console.log('FullCalendar available:', typeof FullCalendar !== 'undefined');
console.log('Class data:', classData);
console.log('Switched to calendar view');
```
- Debug logs present in production code and should be removed

**PHP Debugging:**
- error_log() used for PHP debugging when WP_DEBUG enabled
- Commented-out error_log calls throughout codebase for performance
- Examples from DatabaseService.php:
```php
// error_log("WeCoza Classes Plugin: Attempting PostgreSQL connection...");
// error_log('WeCoza Classes Plugin: PostgreSQL connection successful');
```

**Browser Developer Tools:**
- Forms validated through browser console and network tab
- AJAX responses inspected for success/failure
- No formal browser testing framework used

## Mocking

**Framework:**
- No mocking framework detected (Mockery, PHPUnit mocks not used)
- No mock objects created

**Patterns:**
- Not applicable

**What to Mock:**
- Database calls (DatabaseService singleton)
- External API calls (PublicHolidaysController)
- WordPress functions (wrapped in global namespace or mocked via hooks)

**What NOT to Mock:**
- WordPress core functionality (posts, users, pages)
- Model objects (use real ClassModel, QAModel instances)
- View rendering (test via shortcode integration)

## Validation Approach

**Frontend Validation:**
- Bootstrap validation classes used for form feedback
- HTML5 form validation attributes: required, type constraints
- JavaScript client-side validation in class-capture.js and class-schedule-form.js
- Form submission prevented until validation passes

**Backend Validation:**
- Database service throws exceptions on connection failures
- Prepared statements used to prevent SQL injection
- WordPress functions like esc_html(), esc_attr(), esc_js() used for output escaping
- Current user capabilities checked before operations

## Data Testing

**Fixtures and Factories:**
- No factory classes for creating test data
- Manual data creation required for testing
- Static class types and subject mappings in ClassTypesController.php can serve as reference data

**Test Data Sources:**
- ClassTypesController::getClassTypes() returns hardcoded test data
- ClassTypesController::getClassSubjects() returns subject mappings by class type
- Example test data from ClassTypesController:
```php
public static function getClassTypes() {
    return [
        ['id' => 'AET', 'name' => 'AET Communication & Numeracy'],
        ['id' => 'GETC', 'name' => 'GETC AET'],
        ['id' => 'SKILL', 'name' => 'Skill Packages'],
    ];
}
```

## Coverage

**Requirements:**
- No coverage requirements enforced
- No coverage tools configured

**View Coverage:**
```bash
# No command available
```

## Test Types

**Unit Tests:**
- Not implemented
- Could test model methods, helper functions, controller logic independently
- Gap: No isolated testing of DatabaseService, ClassModel, ViewHelpers

**Integration Tests:**
- Not implemented
- Could verify shortcode rendering with database data
- Could test AJAX endpoints with real database
- Could verify model hydration from database rows

**E2E Tests:**
- Not implemented
- Could test full class creation workflow (form → AJAX → database)
- Could test class display and editing
- Could verify calendar integration

**Manual Testing:**
- Shortcode rendering: `[wecoza_display_classes]` displays all classes
- Form submission: Class capture form submission via AJAX
- Data persistence: Verify data saved to PostgreSQL
- Calendar display: FullCalendar rendering with class events
- QA dashboard: Analytics dashboard rendering with Chart.js

## Known Test Gaps

**Untested Areas:**
- DatabaseService connection and query execution
  - File: `app/Services/Database/DatabaseService.php`
  - Risk: Database connection errors may not be caught until production

- ClassModel hydration and validation
  - File: `app/Models/ClassModel.php`
  - Risk: Malformed database rows could cause silent failures

- JSONB field parsing
  - File: `app/Models/ClassModel.php` (parseJsonField method)
  - Risk: Invalid JSON could crash parsing

- Complex scheduling logic
  - File: `assets/js/class-schedule-form.js` (3322 lines)
  - Risk: Schedule calculation bugs undetected

- AJAX error handling
  - File: `app/Controllers/ClassController.php` (AJAX handlers)
  - Risk: Failed AJAX requests may not notify user

- View helper rendering
  - File: `app/Helpers/ViewHelpers.php`
  - Risk: Malformed HTML output not validated

- Public holidays integration
  - File: `app/Controllers/PublicHolidaysController.php`
  - Risk: API failures could break scheduling

- Permission-based feature access
  - File: `app/bootstrap.php` (get_user_class_capabilities)
  - Risk: Capability checks not verified for all operations

## Recommended Testing Additions

**Priority: High**
- Database connection tests (mock PDO failures)
- JSONB field parsing tests (invalid JSON handling)
- ClassModel hydration tests (field mapping validation)
- AJAX endpoint tests (success and error responses)

**Priority: Medium**
- Schedule calculation tests (date math validation)
- PublicHolidaysController tests (external API mocking)
- Permission checks (capability validation)
- Form validation tests (JavaScript validation rules)

**Priority: Low**
- View rendering integration tests
- Helper function tests
- UI interaction tests (calendar navigation)

## Testing Tools Recommendations

**For Backend:**
- PHPUnit for unit testing database layer and models
- Mockery for mocking external services
- WordPress Testing Library for WordPress-specific testing

**For Frontend:**
- Jest or Vitest for JavaScript testing
- Cypress or Playwright for E2E testing
- Codeception for acceptance testing

---

*Testing analysis: 2026-01-22*
