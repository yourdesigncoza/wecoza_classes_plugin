## Implementation Plan: Show Address Field on Site Selection

### Analysis Summary
- ✅ **SQL queries already fixed**: Both ClassController.php and SiteModel.php use proper JOINs with locations table
- ❌ **JavaScript logic needs update**: Currently only shows address field when address data exists

### Required Changes

#### **File: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/assets/js/class-capture.js`**

**Function**: `initializeSiteAddressLookup()` (lines ~305-325)

**Current Logic**: 
```javascript
if (siteAddresses[selectedValue]) {
    $addressInput.val(siteAddresses[selectedValue]);
    $addressWrapper.show();
} else {
    $addressInput.val("");
    $addressWrapper.hide();
}
```

**New Logic**:
```javascript
if (selectedValue) {
    // Site selected - always show address field
    $addressWrapper.show();
    
    // Populate if address data exists, otherwise show empty
    if (siteAddresses[selectedValue]) {
        $addressInput.val(siteAddresses[selectedValue]);
    } else {
        $addressInput.val("");
    }
} else {
    // No site selected - hide address field
    $addressInput.val("");
    $addressWrapper.hide();
}
```

### Technical Implementation Steps

1. **Modify JavaScript Logic**: Update the conditional logic in `initializeSiteAddressLookup()` to separate site selection visibility from address data population

2. **Test the Changes**: 
   - Select site with address → field shows + populated
   - Select site without address → field shows + empty  
   - Clear site selection → field hides

### Expected Result
- Address field becomes visible whenever ANY site is selected
- Field gets populated only when address data exists
- Maintains readonly behavior and existing functionality
- Improves user experience by showing consistent UI behavior