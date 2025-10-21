# Implementation Plan: Replace Learner Select Dropdown with Searchable Table

## Overview
Replace the `#add_learner` multi-select dropdown with a full-featured searchable, sortable, paginated table positioned above the existing learners container.

## Key Requirements

### 1. Database Integration & Data Retrieval
- **Update `getLearners()` method** to retrieve all learner fields including location data
- **Add location lookups** using LEFT JOIN with locations table (following existing pattern from docs)
- **Return enhanced learner objects** with resolved city_town_name and province_region_name
- **Implement caching** with WordPress transients for performance

### 2. Table Structure & UI Components
- **Position**: Directly above `<!-- Learners Table -->` comment
- **Layout**: Full-width with Phoenix/Bootstrap styling matching classes table
- **Pagination**: 10 results per page with navigation controls
- **Search**: Real-time filtering across all visible columns
- **Sorting**: Clickable column headers for ascending/descending sort

### 3. Table Columns (in order)
1. **Checkbox** - For selection, remains checked when added
2. **ID** - Hidden data attribute
3. **First Name** - `first_name` field
4. **Second Name** - `second_name` field  
5. **Initials** - `initials` field
6. **Surname** - `surname` field
7. **ID Number/Passport** - Badged display (SA ID = primary, Passport = warning)
8. **City/Town** - Resolved `city_town_name` from locations lookup
9. **Province/Region** - Resolved `province_region_name` from locations lookup
10. **Postal Code** - `postal_code` field

### 4. Visual Feedback & Interaction
- **Selected learners**: Checkboxes remain checked with visual distinction
- **Already assigned**: Different styling to prevent duplicate additions
- **Add button**: Replaces "Add Selected Learners" - processes checked items
- **Integration**: Seamlessly works with existing `#class-learners-container`

### 5. Technical Implementation Steps

#### Phase 1: Backend Enhancement
1. **Update `getLearners()` method** in ClassController.php
   - Add fields: `second_name`, `city_town_id`, `province_region_id`, `postal_code`
   - Implement LEFT JOIN with locations table
   - Add caching with transients
   - Return resolved city/province names

#### Phase 2: Frontend Table Component
2. **Create learner selection table HTML**
   - Follow exact Phoenix/Bootstrap pattern from classes table
   - Add search input and pagination controls
   - Implement sortable headers
   - Position above existing learners container

#### Phase 3: JavaScript Functionality  
3. **Implement table interactions**
   - Real-time search filtering
   - Pagination logic
   - Column sorting
   - Checkbox selection management
   - Integration with existing learner assignment workflow

#### Phase 4: Styling Integration
4. **CSS Updates**
   - Add all styles to theme child CSS file (per guidelines)
   - Match existing Phoenix table styling exactly
   - Responsive design for mobile compatibility

## Database Schema Alignment
Based on schema analysis:
- **Learners table**: Contains all required fields including `city_town_id`, `province_region_id`
- **Locations table**: Will provide city_town_name and province_region_name via LEFT JOIN
- **Location resolution**: Follow established pattern from learners-display-shortcode documentation

## Success Criteria
- Table displays 10 learners per page with search/sort/pagination
- Location names resolve correctly via database lookups
- Checkbox selection integrates seamlessly with existing learner assignment
- Visual feedback shows selected/assigned learners
- Performance maintained through caching
- UI matches existing Phoenix design patterns