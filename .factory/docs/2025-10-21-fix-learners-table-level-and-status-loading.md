## Fix Level/Module and Status Loading in Learners Table

**Problem**: The `createLearnerRow` function in `learner-selection-table.js` creates incorrect controls:
1. **Level/Module**: Simple text input instead of proper select dropdown with level options
2. **Status**: Only "Active/Inactive" options instead of proper status options

**Root Cause**: The `createLearnerRow` function doesn't use the existing utility functions and standard options.

**Required Fixes**:

1. **Fix Level/Module Field** (`learner-selection-table.js` line ~412):
   - Replace text input with `classes_generate_learner_level_select_html(learner.id)` 
   - This provides the proper select dropdown with 50+ level options from `learner-level-utils.js`

2. **Fix Status Field** (`learner-selection-table.js` line ~415):
   - Replace basic "Active/Inactive" options with proper status options:
     ```javascript
     <option value="CIC - Currently in Class">CIC - Currently in Class</option>
     <option value="RBE - Removed by Employer">RBE - Removed by Employer</option>  
     <option value="DRO - Drop Out">DRO - Drop Out</option>
     ```

3. **Update Event Handlers** (`learner-selection-table.js` line ~435):
   - Change `.learner-level` to `.learner-level-select` to match new class name
   - Ensure compatibility with existing `classes_populate_learner_levels()` function

**Verification**: 
- Level dropdown will auto-populate when class subject changes (existing functionality)
- Status will have proper options matching other parts of the system
- Learner Selection Table continues working normally
- JSON parsing fix from previous change remains intact

This maintains compatibility with existing infrastructure while fixing the broken functionality.