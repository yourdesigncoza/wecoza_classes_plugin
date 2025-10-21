## Fix JSON Parsing Error in Learner Selection Table

**Problem**: The `data-learner-data` HTML attribute contains malformed JSON due to unescaped single quotes in learner data (names, addresses, etc.), causing `JSON.parse()` to fail with "Unterminated string in JSON" error.

**Root Cause**: Line 633 in `create-class.php` directly outputs JSON into HTML attribute:
```php
data-learner-data='<?php echo json_encode($learner); ?>'
```

**Solution**: Replace direct JSON output with proper HTML escaping:

1. **Fix PHP Template** (`create-class.php` line 633):
   - Use `htmlspecialchars()` with `ENT_QUOTES` to properly escape the JSON string
   - Change from single quotes to double quotes for consistency
   - Ensure JSON is safely embedded as HTML attribute

2. **Alternative approaches considered**:
   - Use `data-*` attributes with multiple smaller attributes (more complex)
   - Store data in JavaScript global variable (less clean)
   - Use base64 encoding (unnecessary overhead)

**Implementation**:
```php
data-learner-data="<?php echo htmlspecialchars(json_encode($learner), ENT_QUOTES, 'UTF-8'); ?>"
```

**Testing**: Verify that learners with names containing apostrophes (like "O'Connor") parse correctly in the JavaScript table.

This is a single-line fix that resolves the JSON parsing error while maintaining all existing functionality.