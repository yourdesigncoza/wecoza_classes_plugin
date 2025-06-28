# Schedule End Date Calculation Analysis

## Overview
Analysis of how the `#schedule_end_date` field is calculated in the WeCoza Classes Plugin.

## File Locations
- **Trigger Button**: `/app/Views/components/class-capture-partials/create-class.php:394`
- **JavaScript Handler**: `/assets/js/class-schedule-form.js:91-94`
- **Calculation Function**: `/assets/js/class-schedule-form.js:2435`

## Trigger Mechanism
```html
<button type="button" class="btn btn-subtle-warning mb-2 mt-2" id="calculate_schedule_end_date-btn">
   Calculate Estimated End Date
</button>
```

The button triggers the `recalculateEndDate()` function when clicked.

## Calculation Process

### 1. Input Parameters
The calculation requires the following inputs:
- `#schedule_start_date` - Starting date for the class schedule
- `#class_type` - Type of class (determines total hours)
- `#schedule_pattern` - Schedule frequency (weekly, biweekly, monthly, custom)
- Session duration - Calculated from per-day time data or fallback values
- Exception dates - Dates when classes won't occur
- Stop/restart periods - Class suspension periods

### 2. Core Algorithm

```javascript
function recalculateEndDate() {
    // Get inputs
    const startDate = $('#schedule_start_date').val();
    const classType = $('#class_type').val();
    const pattern = $('#schedule_pattern').val();
    
    // Calculate session duration
    let sessionDuration = getSessionDuration();
    
    // Get total class hours from class type
    const classHours = getClassTypeHours(classType);
    
    // Calculate sessions needed
    const sessionsNeeded = Math.ceil(classHours / sessionDuration);
    
    // Iterate through dates to find end date
    // ... (detailed logic below)
}
```

### 3. Date Iteration Logic

#### For Weekly Pattern:
1. **Day Selection**: Uses selected days from checkboxes (`schedule_days[]`)
2. **Starting Point**: Adjusts start date to first occurrence of selected day if needed
3. **Iteration**: 
   - Checks each day if it matches selected days
   - Skips exception dates, stop periods, and public holidays (unless overridden)
   - Counts valid sessions until reaching `sessionsNeeded`

#### Holiday Handling:
- Checks against `window.wecozaPublicHolidays.events`
- Respects holiday overrides in `window.holidayOverrides`
- Public holidays are skipped unless explicitly overridden

#### Exception Date Handling:
- Reads from exception date rows: `#exception-dates-container .exception-date-row`
- Skips any dates marked as exceptions

#### Stop/Restart Periods:
- Reads from date history rows: `#date-history-container .date-history-row`
- Excludes dates within stop periods (between stop and restart dates)

### 4. Output
- Sets the calculated end date in `#schedule_end_date` field
- Updates `#schedule_total_hours` with total class hours
- Provides console logging for debugging

## Key Functions Referenced

### Helper Functions:
- `getSelectedDays()` - Gets checked days from schedule_days checkboxes
- `getDayIndex(dayName)` - Converts day name to index (0-6)
- `getDayName(index)` - Converts day index to name
- `getClassTypeHours(classType)` - Gets total hours for class type
- `getAllTimeData()` - Gets session duration data
- `isDateInStopPeriod()` - Checks if date falls in stop period

### Data Sources:
- Class types and hours from backend data
- Public holidays from `window.wecozaPublicHolidays`
- Holiday overrides from `window.holidayOverrides`
- Exception dates from form inputs
- Stop/restart dates from form inputs

## Dependencies
- jQuery for DOM manipulation
- Bootstrap for UI components
- Class type data from PHP backend
- Public holiday calendar integration
- Form validation system

## Notes
- Calculation is manual (button-triggered), not automatic
- Supports multiple schedule patterns (weekly, biweekly, monthly)
- Handles complex scenarios with holidays and exceptions
- Provides extensive console logging for debugging
- Uses ceiling function for session calculation (rounds up)

## Example Flow
1. User selects class type, start date, schedule pattern, and days
2. User adds any exception dates or stop/restart periods
3. User clicks "Calculate Estimated End Date" button
4. System calculates total sessions needed based on class hours
5. System iterates through calendar, counting valid session days
6. System sets the final calculated date in the end date field

---
*Generated on: 2025-06-28*
*Plugin: WeCoza Classes Plugin*