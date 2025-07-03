# Fix Per-Day Times Not Populating

## Task: Fix the per-day time sections not populating times in update-class.php

### Problem
The per-day time sections were opening for the correct days (e.g., Tuesday & Wednesday) but the time fields were not populating with the saved values. Console log showed:
```json
"perDayTimes": {}
```

### Root Cause
1. Schedule data might be stored as a JSON string but not properly decoded
2. The JavaScript expects camelCase properties (`startTime`, `endTime`) but the database stores snake_case (`start_time`, `end_time`)
3. Missing data migration for legacy formats

### Solution Implemented

#### 1. Added JSON Decoding Check (lines 410-413)
```php
// Handle both string and array formats for schedule data
if (is_string($scheduleData)) {
    $scheduleData = json_decode($scheduleData, true) ?? [];
}
```

#### 2. Enhanced Debug Logging (lines 1613-1635)
Added comprehensive debug logging to trace the data structure:
- Raw schedule data from PHP
- Schedule pattern and selected days
- Time data and perDayTimes structure
- Individual day times

#### 3. Normalized Data Format (lines 439-456)
Convert snake_case database format to camelCase JavaScript format:
```php
$normalizedPerDayTimes[$day] = [
    'startTime' => $times['start_time'] ?? $times['startTime'] ?? '',
    'endTime' => $times['end_time'] ?? $times['endTime'] ?? '',
    'duration' => $times['duration'] ?? ''
];
```

#### 4. Legacy Data Migration (lines 458-495)
Handle missing perDayTimes by:
- Checking for individual day times in schedule data
- Falling back to general start/end times
- Creating proper data structure

#### 5. Updated JavaScript Data Passing (lines 1625-1629)
Use the processed and normalized schedule data instead of raw data from database.

### Files Modified
- `/app/Views/components/class-capture-partials/update-class.php`

### Testing Steps
1. Navigate to the update form with `?debug=1` parameter
2. Check console for enhanced debug output
3. Verify that per-day time fields are populated correctly
4. Test saving and reloading to ensure data persistence

### Next Steps
- Monitor for any JavaScript errors related to holidays
- Verify that saving preserves the correct format
- Consider adding migration script for existing data with wrong format