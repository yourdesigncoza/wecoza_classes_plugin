## Implementation Plan: Learner Selection Table for Update Class Form

### Overview
Replace the basic multi-select dropdown in the update class form with the same advanced searchable, sortable, paginated learner selection table that was implemented in the create class form.

### What Needs to Be Done

#### 1. Replace Dropdown Section
- **Location**: Lines ~975-990 in `app/Views/components/class-capture-partials/update-class.php`
- **Current**: Simple multi-select dropdown with basic learner selection
- **Replace with**: Full-featured learner selection table matching the create form implementation

#### 2. Table Structure Components
The replacement will include:
- **Search header** with real-time filtering input and learner count display
- **Sortable table** with 9 columns (Checkbox, First Name, Second Name, Initials, Surname, ID/Passport, City/Town, Province/Region, Postal Code)
- **Pagination controls** showing 10 learners per page
- **Add Selected Learners button** positioned below the table

#### 3. Key Features to Implement
- **Real-time search** across all learner fields
- **Column sorting** by clicking headers (ascending/descending)
- **Pagination** with navigation controls
- **Checkbox selection** with visual feedback
- **Assigned learner tracking** to prevent duplicate additions
- **Integration** with existing `#class-learners-container`

#### 4. JavaScript Integration
- **Reuse existing**: `learner-selection-table.js` handles all table interactions
- **Update form integration**: Ensure selected learners integrate with existing update form workflow
- **Data persistence**: Maintain existing learner data during form updates

#### 5. Styling Consistency
- **Phoenix/Bootstrap styling**: Match existing table designs exactly
- **Responsive design**: Mobile compatibility maintained
- **Visual feedback**: Selected states and hover effects

### Technical Requirements
- **No CSS changes needed** (styles already in theme child CSS)
- **JavaScript already implemented** (reuse existing learner-selection-table.js)
- **Database integration** already handled by existing `getLearners()` method
- **Form submission workflow** remains unchanged

### Expected Outcome
- Users will have the same enhanced learner selection experience in both create and update forms
- Improved usability with search, pagination, and sorting capabilities
- Consistent UI/UX across the application
- Better performance for classes with large numbers of learners