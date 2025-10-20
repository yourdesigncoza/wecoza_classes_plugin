# Draft/Active Class Status Implementation Plan

## Overview
Update the "Create Class" functionality to save new classes as "Draft" status by default. Classes will only become "Active" when an order number is assigned from another plugin. The system will use the existing `order_nr` field to determine class status.

## Database Schema Changes
- **Leverage existing `order_nr` field** in the `classes` table (already exists as `character varying`)
- **Add status logic**: Classes with `order_nr = NULL` or `order_nr = ''` are considered "Draft"
- **Classes with `order_nr` assigned are considered "Active"**

## Implementation Steps

### 1. Update ClassModel.php
- Add `order_nr` property to the ClassModel with appropriate getter/setter methods
- Update the `hydrate()` method to handle the `order_nr` field
- Modify `save()` and `update()` methods to properly handle the order_nr field
- Add helper methods to determine class status based on order_nr:
  - `isDraft()` - returns true if order_nr is empty/null
  - `isActive()` - returns true if order_nr has a value

### 2. Update ClassController.php
- Modify `processFormData()` to include `order_nr` field processing (initially empty)
- Update `populateClassModel()` to handle the `order_nr` field
- Ensure `saveClassAjax()` passes order_nr to the model (will be empty initially)

### 3. Update Form Display
- Modify the create-class.php form to show "Draft" status for new classes
- Add visual indicators for draft vs active status in class listings
- Update the submit button text to reflect draft creation: "Create Draft Class"

### 4. Update Class Listing Views
- Modify the classes display to show status (Draft/Active) based on order_nr
- Add visual differentiation (badges/colors) for draft vs active classes
- Ensure filtering/sorting works with the new status logic

### 5. Order Number Integration
- The external plugin will be responsible for:
  - Setting the `order_nr` field when an order is created
  - Triggering the transition from Draft to Active status
- Our plugin will automatically detect status changes based on order_nr value

## Key Implementation Details

### Status Determination Logic
```php
public function isDraft() {
    return empty($this->order_nr);
}

public function isActive() {
    return !empty($this->order_nr);
}

public function getStatus() {
    return $this->isActive() ? 'Active' : 'Draft';
}
```

### Database Integration
- `order_nr` will be `NULL` initially for new classes (Draft status)
- When external plugin assigns an order number, the class becomes Active
- No separate status field needed - derived from order_nr presence

### User Experience
- Clear visual indicators in class listings (Draft badge vs Active badge)
- Form submission creates classes in Draft state
- Status automatically updates when order number is assigned externally
- No additional user steps required in our plugin

## Benefits
- Clean separation of concerns (our plugin handles class creation, external plugin handles order assignment)
- Automatic status transitions based on business logic
- Maintains existing database structure with minimal changes
- Clear workflow for users creating classes that become active through order processing