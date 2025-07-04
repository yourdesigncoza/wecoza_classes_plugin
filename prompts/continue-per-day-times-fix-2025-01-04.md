# Continue Fixing Per-Day Times Not Populating

## Current Issue Status
The per-day time fields in the update form (`update-class.php`) are still not populating with saved values. The fields show "Select" instead of the actual times (e.g., 6:00-6:30 for Tuesday, 6:00-7:00 for Wednesday).

## What We've Done So Far
1. Added data normalization to filter out corrupt numeric keys (0, 1, 2) and convert snake_case to camelCase
2. Changed debug output from HTML comments to console.log 
3. Fixed the holiday JavaScript error by ensuring holiday data is available

## Current Problems
1. **Debug Output Not Showing**: The PHP debug console.log statements around line 428-437 are not appearing in the browser console even with `?debug=1`
2. **perDayTimes Still Empty**: The JavaScript is still receiving empty perDayTimes object

## Key Files
- `/app/Views/components/class-capture-partials/update-class.php` - The update form
- `/assets/js/class-schedule-form.js` - JavaScript that handles schedule form
- `/captured.json` - Shows the database has correct data with perDayTimes
- `/console.txt` - Shows the JavaScript is receiving empty perDayTimes

## Database Structure (from captured.json)
```json
"perDayTimes": {
    "0": {"start_time": "06:00"},
    "1": {"end_time": "06:30"},
    "2": {"duration": "0.50"},
    "Tuesday": {"duration": "0.50", "end_time": "06:30", "start_time": "06:00"},
    "Wednesday": {"duration": "1.00", "end_time": "07:00", "start_time": "06:00"}
}
```

## Next Steps to Investigate
1. Check why the PHP debug output isn't showing in console
2. Trace where $scheduleData is being set (around line 408)
3. Verify the data flow from database -> PHP -> JavaScript
4. Check if $perDayTimes is actually being populated from $timeData['perDayTimes']
5. Consider that the issue might be in how the class data is loaded initially

## Testing URL
Add `?debug=1` to the update form URL to enable debug mode

## Expected Result
The time dropdowns should show the saved times instead of "Select"

## Context
This is part of the WeCoza Classes Plugin for WordPress that manages class schedules. The schedule data is stored in PostgreSQL as JSONB.