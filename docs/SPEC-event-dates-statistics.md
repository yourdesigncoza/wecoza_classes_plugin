# Event Dates in Schedule Statistics - Specification

**Version:** 2.0
**Date:** 2026-01-23
**Status:** Implemented

---

## 1. Overview

### 1.1 Purpose
Display Event Dates (entered via the Event Dates form section) within the Schedule Statistics table with full details including Type, Description, Date, and Notes.

### 1.2 Relationship to Existing Features
- **Source Data**: Event Dates JSONB field (implemented per SPEC-event-dates.md)
- **Display Location**: Schedule Statistics section (existing component)
- **No Calculation Impact**: Event dates remain informational only - do not affect training hours/days

---

## 2. Functional Requirements

### 2.1 Display Rules

| Rule | Specification |
|------|---------------|
| Row per event | Each event = one row (no grouping) |
| Columns | Type, Description, Date, Notes (5 columns total) |
| Empty notes | Show '-' if empty |
| Date format | DD/MM/YYYY (e.g., 15/02/2026) |
| Date sorting | Chronological (earliest first) |
| Position | New category "Event Dates" after "Attendance Impact" |

### 2.2 Visual Layout

```
+-------------------------------------------------------------------------+
| Schedule Statistics                                                      |
+-------------------------------------------------------------------------+
| Category          | Metric                  | Value (colspan=3)         |
+-------------------+-------------------------+---------------------------+
| Training Duration |                         |                           |
|                   | Total Calendar Days     | 90                        |
| ...               |                         |                           |
+-------------------+-------------------------+---------------------------+
| Event Dates       | Type       | Description        | Date       | Notes |
+-------------------+------------+--------------------+------------+-------+
| Events            | Deliveries | Initial materials  | 28/01/2026 | -     |
|                   | Exams      | Final written exam | 06/02/2026 | Hall  |
|                   | QA Visit   | Quality review     | 15/03/2026 | Smith |
+-------------------------------------------------------------------------+
```

### 2.3 Edge Cases

| Scenario | Behavior |
|----------|----------|
| No events added | Show row: "No event dates added" (colspan=4) |
| Empty description | Show empty cell |
| Empty notes | Show '-' |
| Invalid/empty date | Skip that entry |

---

## 3. Technical Implementation

### 3.1 Files Modified

| File | Changes |
|------|---------|
| `app/Views/.../create-class.php` | 5-column table with colspan for existing rows |
| `app/Views/.../update-class.php` | Same as create-class.php |
| `assets/js/class-schedule-form.js` | Individual row rendering with all fields |

### 3.2 HTML Structure

```html
<!-- Table header -->
<thead>
  <tr>
    <th>Category</th>
    <th>Metric</th>
    <th colspan="3">Value</th>
  </tr>
</thead>

<!-- Existing metric rows use colspan="3" for value -->
<tr>
  <td>Total Calendar Days</td>
  <td colspan="3" id="stat-total-days">-</td>
</tr>

<!-- Event Dates section -->
<tr class="ydcoza-table-subheader">
  <th colspan="5">Event Dates</th>
</tr>
<tr class="ydcoza-table-subheader" style="font-size: 0.85em;">
  <th></th>
  <th>Type</th>
  <th>Description</th>
  <th>Date</th>
  <th>Notes</th>
</tr>
<tr id="event-dates-stats-empty-row">
  <td>Events</td>
  <td colspan="4" class="text-muted">No event dates added</td>
</tr>
```

### 3.3 JavaScript Functions

```javascript
// Collect event dates with all fields
function collectEventDatesForStats() {
    const events = [];
    $('.event-date-row:not(.d-none):not(#event-date-row-template)').each(function() {
        const $row = $(this);
        const type = $row.find('select[name="event_types[]"]').val();
        const description = $row.find('input[name="event_descriptions[]"]').val();
        const date = $row.find('input[name="event_dates_input[]"]').val();
        const notes = $row.find('input[name="event_notes[]"]').val();
        if (type && date) {
            events.push({
                type: type,
                description: description || '',
                date: date,
                notes: notes || ''
            });
        }
    });
    return events;
}

// Render individual rows (no grouping)
function updateEventDatesStatistics() {
    // Sort by date, render each event as individual row
    // Uses jQuery's .text() for XSS protection
    // First row gets rowspan for "Events" category label
}
```

### 3.4 Security Considerations

- **XSS Protection**: All dynamic content inserted via jQuery's `.text()` method
- **No HTML concatenation**: Avoids `$el.html('<td>' + value + '</td>')` pattern
- **Input validation**: Only processes rows with both type and date values

---

## 4. Testing

### 4.1 Test Cases

| ID | Scenario | Expected Result |
|----|----------|-----------------|
| TC01 | No events, view stats | "No event dates added" message |
| TC02 | Add 1 Exam event | Single row with Type, Description, Date, Notes |
| TC03 | Add 3 events | 3 individual rows sorted by date |
| TC04 | Event with empty notes | Notes column shows "-" |
| TC05 | Dates out of order | Displayed in chronological order |
| TC06 | Remove all events | Returns to "No event dates added" |

### 4.2 Manual Verification Steps
1. Navigate to Create Class form
2. Add event dates with descriptions and notes
3. Click "Show Schedule Statistics"
4. Verify 5-column Event Dates section displays correctly
5. Verify existing stats rows display correctly (colspan working)
6. Add/remove events, verify stats update dynamically

---

## 5. Acceptance Criteria

- [x] Event Dates category appears after Attendance Impact
- [x] 5-column layout: Type, Description, Date, Notes
- [x] Each event displays as individual row
- [x] Dates formatted as DD/MM/YYYY
- [x] Events sorted chronologically (earliest first)
- [x] Empty notes show "-"
- [x] Stats update dynamically when events added/removed
- [x] Works on both Create and Update forms
- [x] "No event dates added" shown when empty
- [x] XSS protection via jQuery .text()
