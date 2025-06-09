# Mini-Spec: WEC-101 - Rework Class Schedule

## Issue Summary

The current class schedule system forces all selected days of the week to share the same time settings. When creating or updating a class and selecting multiple days (e.g., Monday, Wednesday, Friday), all selected days must use identical start and end times. This limitation prevents flexible scheduling where different days might need different time slots.

## Reproduction Steps

1. Navigate to the class creation or update form
2. In the schedule section, select multiple days of the week (e.g., Monday, Wednesday, Friday)
3. Set start and end times
4. Observe that all selected days are forced to use the same time settings
5. There is no way to set different times for different days

## Expected vs Actual

**Expected Behavior:**
- Each selected day of the week should allow individual time configuration
- User can set different start/end times for Monday vs Wednesday vs Friday
- Schedule data should store separate time settings per day
- Calendar should display the correct individual times for each day

**Actual Behavior:**
- All selected days share the same time settings
- No UI mechanism to set different times per day
- Schedule data structure may not support individual day times
- Calendar shows same times for all selected days

## Acceptance Criteria

1. **UI Enhancement:**
   - When multiple days are selected, show individual time controls for each day
   - Each day should have its own start time and end time inputs
   - UI should be intuitive and not overwhelming

2. **Data Structure:**
   - Schedule data should support storing different times per day
   - Maintain backward compatibility with existing schedule data
   - Proper validation of time inputs per day

3. **Backend Processing:**
   - Update schedule data processing to handle individual day times
   - Ensure calendar event generation works with new data structure
   - Maintain existing functionality for single-day schedules

4. **Calendar Display:**
   - Calendar should correctly display different times for different days
   - Events should show accurate start/end times per day
   - No visual conflicts or overlapping issues

## Environment / Dependencies

- **Platform:** WordPress Plugin (WeCoza Classes)
- **Frontend:** JavaScript (class-schedule-form.js)
- **Backend:** PHP (ClassController.php, schedule data processing)
- **UI Framework:** Bootstrap (existing form styling)
- **Calendar:** FullCalendar integration (wecoza-calendar.js)
- **Data Storage:** JSON format in database (schedule_data column)

## Technical Context

- Current schedule data structure may need modification
- JavaScript form handling requires updates for individual day controls
- PHP backend processing needs to handle new data format
- Calendar event generation must work with per-day time settings
- Existing classes should continue to work without migration issues
