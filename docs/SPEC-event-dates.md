# Event Dates Feature Specification

**Version:** 1.0
**Date:** 2026-01-23
**Status:** Approved for Implementation

---

## 1. Overview

### 1.1 Purpose
Add a dynamic "Event Dates" section to the Class Capture forms that allows users to record key class-related events (deliveries, exams, QA visits, etc.) with dates. This data does **not** affect training hours or class schedule calculations.

### 1.2 Business Value
- Centralized tracking of class milestones and events
- Data will be consumed by external plugins for reporting and dashboards
- Replaces manual tracking in spreadsheets/notes

### 1.3 Scope
- **In Scope:** Create Class form, Update Class form, database storage, CRUD operations
- **Out of Scope:** Calendar integration, notifications, external plugin consumption (separate spec)

---

## 2. Functional Requirements

### 2.1 Event Structure

Each event consists of 4 fields:

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `type` | Enum (dropdown) | Yes | N/A | One of 8 predefined event types |
| `description` | String | Yes | 255 chars | Free text description of the event |
| `date` | Date | Yes | N/A | Event date (YYYY-MM-DD format) |
| `notes` | String | No | 500 chars | Optional notes/comments |

### 2.2 Event Types (Dropdown Options)

| Value | Display Label |
|-------|---------------|
| `Deliveries` | Deliveries |
| `Collections` | Collections |
| `Exams` | Exams |
| `Mock Exams` | Mock Exams |
| `SBA Collection` | SBA Collection |
| `Learner Packs` | Learner Packs |
| `QA Visit` | QA Visit |
| `SETA Exit` | SETA Exit |

### 2.3 User Interactions

| Action | Behavior |
|--------|----------|
| Add Event | Click "+ Add Event Date" button to add a new row |
| Remove Event | Click "Remove" button on any row to delete it |
| Edit Event | All fields are editable inline |
| Save | Events saved when form is submitted |
| Max Events | Soft limit of ~10 events per class (no hard limit enforced) |

### 2.4 Validation Rules

| Rule | Enforcement |
|------|-------------|
| Event Type | Required when saving a row (client-side) |
| Date | Required when saving a row (client-side) |
| Description | Required when saving a row (client-side) |
| Notes | Optional |
| Date Range | No restrictions - any valid date allowed |
| Duplicates | Allowed - same event type can appear multiple times |

---

## 3. Data Model

### 3.1 Database Schema

```sql
-- Add to classes table
ALTER TABLE classes ADD COLUMN event_dates JSONB DEFAULT '[]'::jsonb;

-- Index for JSON queries (optional, for future reporting)
CREATE INDEX idx_classes_event_dates ON classes USING GIN (event_dates);
```

### 3.2 JSON Structure

```json
[
  {
    "type": "Deliveries",
    "description": "Deliver Initial Material",
    "date": "2026-02-15",
    "notes": ""
  },
  {
    "type": "QA Visit",
    "description": "First quality review",
    "date": "2026-03-20",
    "notes": "Scheduled with John Smith"
  },
  {
    "type": "Exams",
    "description": "Final Written Exam",
    "date": "2026-04-10",
    "notes": "Venue: Main Hall"
  }
]
```

### 3.3 PHP Model Properties

```php
// ClassModel.php
private $eventDates = [];

public function getEventDates() { return $this->eventDates; }
public function setEventDates($eventDates) {
    $this->eventDates = is_array($eventDates) ? $eventDates : [];
    return $this;
}
```

---

## 4. User Interface

### 4.1 Location
- **Create Class Form:** After "Class End Date" section, before "Schedule Statistics"
- **Update Class Form:** Same position, with pre-populated values

### 4.2 Visual Design

```
┌─────────────────────────────────────────────────────────────────────────┐
│ Event Dates                                                              │
│ Add key event dates (does not affect training hours or schedule).        │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│ ┌─────────┐ ┌──────────────────┐ ┌──────────┐ ┌────────────┐ ┌────────┐ │
│ │Deliveries│ │Deliver Initial   │ │2026-02-15│ │            │ │ Remove │ │
│ │    ▼    │ │Material          │ │          │ │            │ │        │ │
│ └─────────┘ └──────────────────┘ └──────────┘ └────────────┘ └────────┘ │
│                                                                          │
│ ┌─────────┐ ┌──────────────────┐ ┌──────────┐ ┌────────────┐ ┌────────┐ │
│ │QA Visit │ │First quality     │ │2026-03-20│ │With John   │ │ Remove │ │
│ │    ▼    │ │review            │ │          │ │            │ │        │ │
│ └─────────┘ └──────────────────┘ └──────────┘ └────────────┘ └────────┘ │
│                                                                          │
│ [+ Add Event Date]                                                       │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 4.3 Responsive Behavior
- Desktop: 5 columns (Type, Description, Date, Notes, Remove)
- Tablet: Stack to 2 rows per event
- Mobile: Single column stack

### 4.4 Bootstrap Classes
- Container: `mb-4`
- Row: `row event-date-row align-items-end`
- Columns: `col-md-2`, `col-md-3`, `col-md-2`, `col-md-3`, `col-md-2`
- Inputs: `form-control form-control-sm`, `form-select form-select-sm`
- Buttons: `btn btn-subtle-primary btn-sm`, `btn btn-subtle-danger btn-sm`

---

## 5. Technical Implementation

### 5.1 Files to Modify

| File | Changes |
|------|---------|
| `app/Models/ClassModel.php` | Add `$eventDates` property, getter/setter, hydration, save/update SQL |
| `app/Controllers/ClassController.php` | Add to `processFormData()`, `populateClassModel()` |
| `app/Views/.../create-class.php` | Add Event Dates section after Class End Date |
| `app/Views/.../update-class.php` | Add Event Dates section with pre-populated values |
| `assets/js/class-capture.js` | Add/remove event row handlers |

### 5.2 Form Field Names

```html
<select name="event_types[]">...</select>
<input name="event_descriptions[]" type="text">
<input name="event_dates_input[]" type="date">
<input name="event_notes[]" type="text">
```

Note: Using `event_dates_input[]` to avoid collision with the database column name in POST processing.

### 5.3 Controller Processing

```php
// processFormData() in ClassController.php
$eventDates = [];
if (isset($data['event_types']) && is_array($data['event_types'])) {
    $types = $data['event_types'];
    $descriptions = $data['event_descriptions'] ?? [];
    $dates = $data['event_dates_input'] ?? [];
    $notes = $data['event_notes'] ?? [];

    for ($i = 0; $i < count($types); $i++) {
        if (!empty($types[$i]) && !empty($dates[$i])) {
            $eventDates[] = [
                'type' => self::sanitizeText($types[$i]),
                'description' => self::sanitizeText($descriptions[$i] ?? ''),
                'date' => self::sanitizeText($dates[$i]),
                'notes' => self::sanitizeText($notes[$i] ?? '')
            ];
        }
    }
}
$processed['event_dates'] = $eventDates;
```

### 5.4 JavaScript Pattern

```javascript
// Add event row
$('#add-event-date-btn').on('click', function() {
    const template = $('#event-date-row-template').clone();
    template.removeClass('d-none').removeAttr('id');
    $('#event-dates-container').append(template);
});

// Remove event row
$(document).on('click', '.remove-event-btn', function() {
    $(this).closest('.event-date-row').remove();
});
```

---

## 6. API / Data Access

### 6.1 For External Plugin Consumption

```php
// Get event dates for a class
$class = ClassModel::findById($classId);
$eventDates = $class->getEventDates();

// Filter by event type
$qaVisits = array_filter($eventDates, fn($e) => $e['type'] === 'QA Visit');

// Get all events for a date range (raw SQL)
$sql = "SELECT class_id, event_dates
        FROM classes
        WHERE event_dates @> '[{\"type\": \"Exams\"}]'";
```

### 6.2 PostgreSQL JSON Queries

```sql
-- Find classes with specific event type
SELECT class_id, class_code
FROM classes
WHERE event_dates @> '[{"type": "QA Visit"}]';

-- Extract all exam dates
SELECT class_id,
       jsonb_array_elements(event_dates) ->> 'date' as event_date
FROM classes
WHERE jsonb_array_elements(event_dates) ->> 'type' = 'Exams';
```

---

## 7. Testing

### 7.1 Test Cases

| ID | Scenario | Expected Result |
|----|----------|-----------------|
| TC01 | Add single event and save | Event appears in database as JSON |
| TC02 | Add multiple events (3+) and save | All events saved in correct order |
| TC03 | Remove event before save | Event not included in saved data |
| TC04 | Edit existing class with events | Events pre-populate correctly |
| TC05 | Update event dates on existing class | Changes persisted to database |
| TC06 | Save with empty event section | Empty array `[]` saved (not null) |
| TC07 | Add event with only type and date | Saves with empty description and notes |

### 7.2 Verification Queries

```sql
-- Verify event_dates column exists
SELECT column_name, data_type
FROM information_schema.columns
WHERE table_name = 'classes' AND column_name = 'event_dates';

-- Check saved events for a class
SELECT class_id, class_code, event_dates
FROM classes
WHERE class_id = [ID];

-- Count events per class
SELECT class_id, jsonb_array_length(event_dates) as event_count
FROM classes
WHERE event_dates != '[]';
```

---

## 8. Migration

### 8.1 Database Migration Script

```sql
-- File: includes/migrations/add_event_dates_field.sql
-- Description: Add event_dates JSONB column to classes table
-- Date: 2026-01-23

-- Add column
ALTER TABLE classes ADD COLUMN IF NOT EXISTS event_dates JSONB DEFAULT '[]'::jsonb;

-- Add comment
COMMENT ON COLUMN classes.event_dates IS 'JSON array of class events (deliveries, exams, QA visits, etc.)';

-- Optional: Add GIN index for JSON queries
CREATE INDEX IF NOT EXISTS idx_classes_event_dates ON classes USING GIN (event_dates);
```

### 8.2 Rollback Script

```sql
-- Rollback: Remove event_dates column
DROP INDEX IF EXISTS idx_classes_event_dates;
ALTER TABLE classes DROP COLUMN IF EXISTS event_dates;
```

---

## 9. Future Considerations

- **Calendar Integration:** Display events on FullCalendar view
- **Notifications:** Email reminders before event dates
- **Bulk Operations:** Import/export events via CSV
- **Recurring Events:** Support for recurring event patterns
- **Event Status:** Add status field (Scheduled, Completed, Cancelled)

---

## 10. Approval

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Product Owner | | | |
| Developer | Claude/John | 2026-01-23 | Approved |
| QA | | | |
