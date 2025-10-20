## Implementation Plan: Show Address Field on Site Selection

### Current State Analysis
- The address wrapper (`#address-wrapper`) is initially hidden with `style="display:none;"`
- JavaScript function `initializeSiteAddressLookup()` only shows the address field when there's address data available for the selected site
- The address field is populated from `wecozaClass.siteAddresses` object and is readonly

### Required Changes
**File: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/assets/js/class-capture.js`**

**Function: `initializeSiteAddressLookup()` (line ~300)**

**Changes needed:**
1. Modify the logic to show the address wrapper whenever a site is selected (regardless of address availability)
2. Only populate the address field if address data exists
3. Keep the field readonly as it currently is
4. Update the else condition to hide the wrapper when no site is selected

**Specific Logic Change:**
```javascript
// Current logic: show only if siteAddresses[selectedValue] exists
// New logic: show whenever selectedValue exists (site selected), but only populate if address data available
```

### Technical Implementation
- Keep the existing event handler on `#site_id` change
- Update the conditional logic to check for site selection first
- Maintain readonly behavior and address population from available data
- Ensure the address wrapper is hidden when site selection is cleared

### Result
After implementation:
- When any site is selected → address field becomes visible
- If address data exists → field gets populated with the address
- If no address data exists → field shows empty but remains visible
- When no site selected → address field remains hidden