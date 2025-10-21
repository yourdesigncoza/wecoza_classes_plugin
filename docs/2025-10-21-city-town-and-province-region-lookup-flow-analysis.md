# City/Town and Province/Region Lookup Flow Analysis

## Overview
The learners display shortcode uses a comprehensive database lookup system to resolve city/town and province/region names from location IDs.

## Database Schema
- **locations table**: `location_id`, `suburb`, `town`, `province`, `postal_code`
- **learners table**: stores `city_town_id` (foreign key to locations.location_id)

## Data Flow Architecture

### 1. Frontend Request (JavaScript)
- `learners-display-shortcode.js` calls AJAX action `'fetch_learners_data'`
- Data is parsed and displayed in table with `cityTown` field

### 2. AJAX Handler (PHP)
- `ajax/learners-ajax-handlers.php::fetch_learners_data()` 
- Calls `$db->get_learners_mappings()` method
- Generates HTML table rows via `generate_learner_table_rows()`

### 3. Database Query (Core Logic)
**File**: `database/learners-db.php::get_learners_mappings()`

**Key SQL JOIN**:
```sql
SELECT
    learners.*,
    locations.town AS city_town_name,
    locations.province AS province_region_name,
    -- other fields...
FROM learners
LEFT JOIN locations
    ON learners.city_town_id = locations.location_id
```

### 4. Caching Layer
- Uses WordPress transients: `learner_db_get_learners_mappings`
- Cache duration: 12 hours (`12 * HOUR_IN_SECONDS`)
- Automatic cache invalidation on data updates

### 5. Display Logic
- `generate_learner_table_rows()` accesses `$learner->city_town_name`
- Displays resolved city name instead of raw ID

## Supporting Functions
- `get_locations()`: Provides dropdown data with DISTINCT city/province lists
- `get_learner_by_id()`: Single record lookup with same JOIN pattern

## Implementation Pattern for Other Plugins
1. **Database Structure**: Create reference table with ID + descriptive fields
2. **Query Pattern**: Use LEFT JOIN to resolve IDs to names
3. **Caching Strategy**: Implement WordPress transients for performance
4. **AJAX Flow**: Frontend → AJAX Handler → Database Query → Response
5. **Data Structure**: Return objects with resolved field names (e.g., `city_town_name`)

This pattern provides efficient lookup, caching, and clean separation between data storage (IDs) and display (readable names).