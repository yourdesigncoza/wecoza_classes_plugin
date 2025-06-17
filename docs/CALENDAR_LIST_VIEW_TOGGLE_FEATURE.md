# Calendar/List View Toggle Feature

## Overview

The WeCoza Classes plugin now includes a **Calendar/List View Toggle Feature** that allows users to switch between two different ways of viewing class schedule data:

1. **Calendar View** (Default) - Traditional calendar interface with events displayed on their respective dates
2. **List View** (New) - Chronological list format showing all class events in a structured table layout

## Features

### View Toggle Interface
- **Bootstrap Navigation Tabs** - Clean, professional toggle interface above the calendar
- **Persistent Preferences** - Your last selected view is remembered across page reloads
- **Smooth Transitions** - Animated switching between views for better user experience

### Calendar View (Existing)
- Full FullCalendar integration with month and week views
- Interactive calendar with event details
- Public holiday integration
- Visual event indicators and legends

### List View (New)
- **Chronological Event List** - All events sorted by date and time
- **Event Grouping** - Events organized by type (Class Sessions, Public Holidays, Exceptions, etc.)
- **Detailed Information** - Shows date, time, duration, subject, and notes for each event
- **Responsive Design** - Optimized for both desktop and mobile viewing
- **Event Type Indicators** - Color-coded badges and icons for different event types

### Filtering Capabilities
- **Event Type Filter** - Filter by Class Sessions, Public Holidays, Exception Dates, etc.
- **Date Range Filter** - Filter events by start and end dates
- **Clear Filters** - Quick reset to show all events
- **Real-time Filtering** - Instant results as you adjust filter criteria

## How to Use

### Switching Views

1. **Navigate to any single class display page** using the `[wecoza_display_single_class]` shortcode
2. **Locate the view toggle tabs** above the calendar section
3. **Click "Calendar View"** to see the traditional calendar interface
4. **Click "List View"** to see the chronological list of events

### Using List View

1. **Switch to List View** using the toggle tabs
2. **Browse Events** - Events are automatically grouped by type and sorted chronologically
3. **View Details** - Each event shows:
   - Date and time
   - Event title and description
   - Class subject (if applicable)
   - Duration
   - Event type indicator

### Using Filters (List View Only)

1. **Event Type Filter**:
   - Select from dropdown: All Event Types, Class Sessions, Public Holidays, Exception Dates, Stop/Restart Dates
   - Filter is applied immediately

2. **Date Range Filter**:
   - Set "From Date" to filter events starting from a specific date
   - Set "To Date" to filter events ending by a specific date
   - Both filters can be used together

3. **Clear Filters**:
   - Click the "Clear" button to reset all filters and show all events

## Benefits

### For Students and Learners
- **List View** provides a clear, chronological overview of all class sessions
- **Mobile-friendly** table layout works better on small screens
- **Filtering** helps focus on specific types of events or date ranges
- **Detailed Information** shows duration and notes for better planning

### For Administrators and Instructors
- **Dual View Options** accommodate different user preferences
- **Comprehensive Event Display** shows all event types in one place
- **Print-friendly** list view for documentation and reports
- **Accessibility** improved screen reader compatibility with structured list format

### Technical Benefits
- **No Additional Database Queries** - Uses existing calendar data
- **Responsive Design** - Works seamlessly across all devices
- **State Persistence** - Remembers user preferences
- **Performance Optimized** - Efficient data processing and rendering

## Technical Implementation

### Data Source
- Uses the same AJAX endpoints as the calendar view (`get_calendar_events`, `get_public_holidays`)
- No additional server-side changes required
- Maintains data consistency between views

### Browser Compatibility
- Modern browsers with JavaScript enabled
- Bootstrap 5 compatible
- Mobile responsive design
- Progressive enhancement approach

### Integration
- Seamlessly integrates with existing WeCoza Classes plugin functionality
- Maintains all existing calendar features
- Compatible with existing shortcodes and display options

## Troubleshooting

### List View Not Loading
1. Check browser console for JavaScript errors
2. Ensure FullCalendar library is loaded
3. Verify AJAX endpoints are accessible
4. Check network connectivity

### Filters Not Working
1. Ensure events are loaded in list view
2. Check filter dropdown selections
3. Verify date format in date inputs
4. Clear browser cache if needed

### View Toggle Not Appearing
1. Verify you're on a single class display page
2. Check that the calendar section is present
3. Ensure Bootstrap JavaScript is loaded
4. Check for CSS conflicts

## Support

For technical support or feature requests related to the Calendar/List View Toggle Feature, please contact the WeCoza development team.

## Version Information

- **Feature Version**: 1.0
- **Implementation Date**: June 17, 2025
- **Plugin Compatibility**: WeCoza Classes Plugin v4.0+
- **WordPress Compatibility**: 5.0+
- **Bootstrap Version**: 5.x
