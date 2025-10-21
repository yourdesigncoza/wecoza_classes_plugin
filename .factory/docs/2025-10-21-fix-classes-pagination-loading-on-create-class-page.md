## Fix Classes Pagination Loading on Create Class Page

**Problem**: The `classes-table-search.js` pagination system (`#classes-pagination`) is incorrectly initializing on the create class page where `#learner-selection-table` exists, causing duplicate pagination displays.

**Root Cause**: The `classes-table-search.js` is too broad in its element detection:
1. It looks for any `.search-input.search.form-control-sm` element
2. It finds any `.table-responsive table` element 
3. It adds pagination after any `.table-responsive` element
4. The create class form has these same elements, so it incorrectly initializes

**Solution**: Make the classes table search more specific to only work with the actual classes display table.

**Implementation Steps**:

1. **Update classes-table-search.js selectors**:
   - Change search input selector to be more specific to classes table context
   - Add validation to ensure we're on the right page/table
   - Check for specific classes table identifiers before initializing

2. **Add context validation**:
   - Check for `#classes-table` element before initializing
   - Verify the table contains class data (not learner data)
   - Only initialize pagination when on classes display page

3. **Improve scoping**:
   - Make selectors more specific to avoid conflicts
   - Add parent container validation
   - Prevent initialization when learner selection table is present

**Expected Outcome**: 
- Classes pagination only loads on the main classes display page
- Learner selection table pagination works independently on create class page
- No more duplicate pagination displays
- Both systems remain functional on their respective pages