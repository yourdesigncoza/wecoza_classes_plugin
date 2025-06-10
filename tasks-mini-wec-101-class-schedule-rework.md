# Task List: WEC-101 - Rework Class Schedule

## Relevant Files

- `assets/js/class-schedule-form.js` – Frontend schedule form handling and day/time controls (MODIFIED: Added per-day display logic + debugging)
- `app/Controllers/ClassController.php` – Backend schedule data processing and validation  
- `app/Views/components/class-capture-partials/create-class.php` – Schedule form UI template (MODIFIED: Added per-day time controls)
- `assets/js/wecoza-calendar.js` – Calendar event generation and display
- `assets/css/wecoza-classes-public.css` – Styling for new per-day time controls

### Notes
- Maintain backward compatibility with existing schedule data
- Follow WordPress coding standards and plugin architecture
- Test with both single-day and multi-day schedules

## Tasks

- [x] 1.0 Update Frontend Schedule Form UI
  - [x] 1.1 Analyze current schedule form structure and day selection mechanism
  - [x] 1.2 Design new UI layout for per-day time controls
  - [x] 1.3 Modify create-class.php template to include individual time inputs per day
  - [x] 1.4 Add conditional display logic to show/hide per-day controls based on selection

- [x] 2.0 Enhance JavaScript Form Handling
  - [x] 2.1 Update initSchedulePatternSelection() to handle per-day time controls
  - [x] 2.2 Modify time selection logic to work with individual day settings
  - [x] 2.3 Add validation for per-day time inputs (start < end, no overlaps)
  - [x] 2.4 Update form data collection to gather individual day times
  - [x] 2.5 Ensure backward compatibility with existing single-time format
  - [x] 2.6 🧹 Remove debugging console.log statements from development

- [x] 3.0 Modify Backend Data Processing
  - [x] 3.1 Analyze current schedule_data JSON structure
  - [x] 3.2 Design new data format to support per-day times
  - [x] 3.3 Update ClassController schedule data processing methods
  - [x] 3.4 Add validation for new schedule data format
  - [x] 3.5 Implement backward compatibility for existing schedule data

- [x] 4.0 Update Calendar Integration
  - [x] 4.1 Review generateCalendarEvents() method in ClassController
  - [x] 4.2 Update calendar event generation to handle per-day times
  - [x] 4.3 Modify wecoza-calendar.js to properly display per-day events
  - [x] 4.4 Test calendar display with new schedule format
  - [x] 4.5 Ensure existing calendar functionality remains intact

- [x] 5.0 Add Styling and UX Improvements
  - [x] 5.1 Create CSS styles for per-day time control layout
  - [x] 5.2 Add responsive design for mobile devices
  - [x] 5.3 Implement clear visual indicators for active/inactive days
  - [x] 5.4 Add helpful tooltips or instructions for new functionality
  - [x] 5.5 Test user experience across different screen sizes

- [ ] 6.0 Testing and Quality Assurance
  - [ ] 6.1 Test creating new classes with per-day schedules
  - [ ] 6.2 Test updating existing classes (backward compatibility)
  - [ ] 6.3 Verify calendar displays correct times for each day
  - [ ] 6.4 Test form validation with various time combinations
  - [ ] 6.5 Cross-browser testing for JavaScript functionality
