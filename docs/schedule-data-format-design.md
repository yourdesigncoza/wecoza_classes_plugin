# Schedule Data Format Design v2.0

## Overview

This document defines the new schedule data format that supports per-day time settings while maintaining backward compatibility with existing legacy data.

## Current Legacy Format (v1.0)

### Structure
```json
[
  {
    "date": "2024-01-15",
    "start_time": "09:00",
    "end_time": "17:00", 
    "notes": "Optional notes"
  },
  {
    "date": "2024-01-16",
    "start_time": "09:00", 
    "end_time": "17:00"
  }
]
```

### Characteristics
- Array of individual schedule entries
- Each entry represents a specific date with times
- Simple flat structure
- Used by existing calendar generation
- No pattern or recurrence information

## New Format (v2.0)

### Structure
```json
{
  "version": "2.0",
  "pattern": "weekly|biweekly|monthly|custom",
  "startDate": "2024-01-15",
  "endDate": "2024-03-15",
  "timeData": {
    "mode": "single|per-day",
    "single": {
      "startTime": "09:00",
      "endTime": "17:00", 
      "duration": 8.0
    },
    "perDay": {
      "Monday": {
        "startTime": "09:00",
        "endTime": "12:00",
        "duration": 3.0
      },
      "Wednesday": {
        "startTime": "13:00", 
        "endTime": "17:00",
        "duration": 4.0
      }
    }
  },
  "selectedDays": ["Monday", "Wednesday"],
  "dayOfMonth": 15,
  "exceptionDates": [
    {
      "date": "2024-02-15",
      "reason": "Public Holiday"
    }
  ],
  "holidayOverrides": {
    "2024-02-14": true
  },
  "metadata": {
    "lastUpdated": "2024-01-15T10:30:00Z",
    "createdBy": "frontend-v2.0"
  }
}
```

### Key Features
- **Version tracking**: Clear identification of data format
- **Pattern support**: Weekly, biweekly, monthly, custom schedules
- **Dual time modes**: Single time for all days OR per-day times
- **Rich metadata**: Exception dates, holiday overrides, audit trail
- **Extensible**: Easy to add new fields without breaking compatibility

## Hybrid Storage Strategy

### Database Storage
The `schedule_data` JSONB column will store the v2.0 format as the canonical representation, but the backend will:

1. **Detect format** on input (v1.0 array vs v2.0 object)
2. **Convert legacy data** to v2.0 format automatically
3. **Store in v2.0 format** for consistency
4. **Generate legacy format** when needed for calendar/compatibility

### Conversion Logic

#### Legacy to v2.0 Conversion
```php
function convertLegacyToV2($legacyData) {
    return [
        'version' => '2.0',
        'pattern' => 'custom',
        'timeData' => [
            'mode' => 'single',
            'single' => extractCommonTimes($legacyData)
        ],
        'generatedSchedule' => $legacyData, // Preserve original
        'metadata' => [
            'convertedFrom' => 'v1.0',
            'convertedAt' => date('c')
        ]
    ];
}
```

#### v2.0 to Legacy Conversion (for calendar)
```php
function convertV2ToLegacy($v2Data) {
    if (isset($v2Data['generatedSchedule'])) {
        return $v2Data['generatedSchedule']; // Use cached if available
    }
    
    return generateScheduleEntries($v2Data); // Generate from pattern
}
```

## Implementation Strategy

### Phase 1: Backend Compatibility Layer
1. Add format detection in `ClassController::processJsonField()`
2. Create conversion utilities
3. Update `generateCalendarEvents()` to handle both formats

### Phase 2: Enhanced Processing
1. Add validation for v2.0 format
2. Implement schedule generation from patterns
3. Add per-day time support in calendar

### Phase 3: Migration Support
1. Add migration utility for existing data
2. Provide admin tools for format conversion
3. Add data integrity checks

## Validation Rules

### v2.0 Format Validation
- `version` must be "2.0"
- `pattern` must be valid enum value
- `timeData.mode` must be "single" or "per-day"
- Time formats must be HH:MM
- Start times must be before end times
- Durations must be positive numbers
- Dates must be valid ISO format

### Backward Compatibility
- Legacy v1.0 arrays must be preserved during conversion
- Calendar generation must work with both formats
- Existing classes must continue to function
- No data loss during format conversion

## Benefits

1. **Flexibility**: Supports both simple and complex scheduling
2. **Extensibility**: Easy to add new features
3. **Compatibility**: Seamless transition from legacy format
4. **Performance**: Efficient storage and retrieval
5. **Maintainability**: Clear structure and versioning
