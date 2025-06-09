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

- [ ] 2.0 Enhance JavaScript Form Handling
  - [x] 2.1 Update initSchedulePatternSelection() to handle per-day time controls
  - [ ] 2.2 Modify time selection logic to work with individual day settings
  - [ ] 2.3 Add validation for per-day time inputs (start < end, no overlaps)
  - [ ] 2.4 Update form data collection to gather individual day times
  - [ ] 2.5 Ensure backward compatibility with existing single-time format
  - [ ] 2.6 🧹 Remove debugging console.log statements from development

- [ ] 3.0 Modify Backend Data Processing
  - [ ] 3.1 Analyze current schedule_data JSON structure
  - [ ] 3.2 Design new data format to support per-day times
  - [ ] 3.3 Update ClassController schedule data processing methods
  - [ ] 3.4 Add validation for new schedule data format
  - [ ] 3.5 Implement backward compatibility for existing schedule data

- [ ] 4.0 Update Calendar Integration
  - [ ] 4.1 Review generateCalendarEvents() method in ClassController
  - [ ] 4.2 Update calendar event generation to handle per-day times
  - [ ] 4.3 Modify wecoza-calendar.js to properly display per-day events
  - [ ] 4.4 Test calendar display with new schedule format
  - [ ] 4.5 Ensure existing calendar functionality remains intact

- [ ] 5.0 Add Styling and UX Improvements
  - [ ] 5.1 Create CSS styles for per-day time control layout
  - [ ] 5.2 Add responsive design for mobile devices
  - [ ] 5.3 Implement clear visual indicators for active/inactive days
  - [ ] 5.4 Add helpful tooltips or instructions for new functionality
  - [ ] 5.5 Test user experience across different screen sizes

- [ ] 6.0 Testing and Quality Assurance
  - [ ] 6.1 Test creating new classes with per-day schedules
  - [ ] 6.2 Test updating existing classes (backward compatibility)
  - [ ] 6.3 Verify calendar displays correct times for each day
  - [ ] 6.4 Test form validation with various time combinations
  - [ ] 6.5 Cross-browser testing for JavaScript functionality
