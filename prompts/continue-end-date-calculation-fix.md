# Continue End Date Calculation Fix - WeCoza Classes Plugin

## Context
Working on fixing the end date calculation for the class update form in the WeCoza Classes Plugin. The calculation should produce the same end date (2027-04-13) when recalculating without any changes.

## Current Status

### What's Been Done:
1. **Added IDs to hidden fields** - Added `id="class_type"` and `id="class_duration"` to hidden inputs in update-class.php
2. **Loaded holiday overrides** - Added JavaScript to load holiday overrides from hidden field into window.holidayOverrides
3. **Implemented weekly calculation** - Changed from average session duration (0.75h) to weekly totals (1.5h/week)
4. **Fixed week counting** - Now correctly calculates 80 weeks needed and stops at week 80
5. **Added comprehensive debugging** - Detailed console output for troubleshooting

### Current Issues:
1. **Extra Exception Date** - Form shows TWO exception dates ['2025-07-22', '2025-07-23'] but original data only has ONE ['2025-07-22']
2. **Wrong End Date** - Calculation produces 2027-01-20 instead of expected 2027-04-13 (about 3 months early)
3. **Last scheduled date issue** - Console shows "Last scheduled date: 2027-01-20" but the last Tuesday scheduled was 2027-01-19

## Files Modified:
- `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/app/Views/components/class-capture-partials/update-class.php`
- `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/assets/js/class-schedule-form.js`

## Key Data Points:
- Class Duration: 120 hours
- Weekly Schedule: Tuesday (0.5h) + Wednesday (1.0h) = 1.5h/week
- Weeks Needed: 80 weeks (120 ÷ 1.5)
- Start Date: 2025-07-07
- Expected End Date: 2027-04-13
- Current Calculated End Date: 2027-01-20
- Exception Dates: Should be ['2025-07-22'] but showing ['2025-07-22', '2025-07-23']
- Stop/Restart: 2025-07-14 to 2025-07-16
- Holiday Override: 2025-09-24 (Heritage Day) is included

## Next Steps to Investigate:

### 1. Find Source of Extra Exception Date
- The form is showing an extra exception date (2025-07-23) that's not in the database
- Check if it's being added during form population in update-class.php
- Look for any code that might be adding adjacent dates automatically

### 2. Debug the 3-Month Date Discrepancy
- The calculation is stopping at the right number of weeks (80)
- But the date is ~3 months earlier than expected
- Possible causes:
  - More holidays than expected are being skipped
  - The week counting might be off due to partial weeks
  - Stop/restart periods might be calculated differently

### 3. Fix Last Scheduled Date Logic
- The code shows it scheduled 2027-01-19 (Tuesday) but reports last date as 2027-01-20 (Wednesday)
- The Wednesday wasn't actually scheduled, so the date is off by one day

## Debug Information from Latest Run:
```
- Exception date rows found: 2 (should be 1)
- Row 0: 2025-07-22 (correct)
- Row 1: 2025-07-23 (unexpected)
- Weeks calculated: 80 (correct)
- Final date: 2027-01-20 (wrong - should be ~2027-04-13)
```

## Reference Files:
- Original class data: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/captured.json`
- Database schema: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/schema/classes_schema.sql`

## Testing:
To test, go to the update class page for class ID 47 and click "Calculate Estimated End Date" button without making any changes. The console will show detailed debug output.