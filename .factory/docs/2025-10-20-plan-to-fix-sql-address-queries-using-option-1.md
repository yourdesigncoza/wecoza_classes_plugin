## Plan to Fix SQL Address Queries Using Option 1

### **Problem Analysis**
The debug.log shows SQL errors because code expects an `address` column in the `public.sites` table, but the actual database design uses:
- `sites.place_id` → `locations.location_id` (foreign key relationship)
- `locations.street_address` (contains the actual address data)

### **Files Requiring Fixes**

#### **1. WeCoza Classes Plugin**
**File**: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/app/Controllers/ClassController.php`

**Problematic Queries:**
```sql
-- Query 1 (Line ~?): Site addresses
SELECT site_id, address FROM public.sites WHERE address IS NOT NULL AND address != ''

-- Query 2 (Line ~?): Sites list  
SELECT s.site_id, s.client_id, s.site_name, s.address
FROM public.sites s
ORDER BY s.client_id ASC, s.site_name ASC
```

#### **2. WeCoza Classes Site Management Plugin**
**File**: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-site-management/app/Models/SiteModel.php`

**Problematic Query:**
```sql
-- Query 3 (Line ~?): Site data with CTE
SELECT s.site_id, s.client_id, s.site_name, s.address, s.created_at, s.updated_at, c.client_name
FROM sites s
LEFT JOIN clients c ON s.client_id = c.client_id
```

### **Solution: Implement Proper JOIN with Locations Table**

#### **Fixed Query 1 - Site Addresses**
```sql
SELECT s.site_id, l.street_address as address 
FROM public.sites s 
LEFT JOIN public.locations l ON s.place_id = l.location_id 
WHERE l.street_address IS NOT NULL AND l.street_address != ''
```

#### **Fixed Query 2 - Sites List**
```sql
SELECT s.site_id, s.client_id, s.site_name, l.street_address as address
FROM public.sites s
LEFT JOIN public.locations l ON s.place_id = l.location_id
ORDER BY s.client_id ASC, s.site_name ASC
```

#### **Fixed Query 3 - Site Data with CTE**
```sql
WITH site_data AS (
    SELECT
        s.site_id,
        s.client_id,
        s.site_name,
        l.street_address as address,
        s.created_at,
        s.updated_at,
        c.client_name
    FROM public.sites s
    LEFT JOIN public.locations l ON s.place_id = l.location_id
    LEFT JOIN public.clients c ON s.client_id = c.client_id
)
```

### **Implementation Steps**

#### **Step 1: Fix ClassController.php**
1. Locate the first SQL query around site address fetching
2. Replace with proper JOIN to locations table
3. Update the second SQL query for sites listing
4. Test that the queries return expected data

#### **Step 2: Fix SiteModel.php**
1. Locate the CTE query with address reference
2. Replace `s.address` with `l.street_address as address`
3. Add the LEFT JOIN to locations table
4. Ensure the CTE structure is maintained

#### **Step 3: Handle Null Cases**
- Use `COALESCE(l.street_address, '')` if needed to handle missing addresses
- Ensure empty strings are properly filtered
- Maintain backward compatibility with existing code expectations

#### **Step 4: Testing & Verification**
1. Test the modified queries with actual database data
2. Verify that sites without addresses (NULL place_id) are handled gracefully
3. Check that the debug.log no longer shows SQL errors
4. Ensure the UI displays addresses correctly

### **Expected Results**
- ✅ SQL errors eliminated from debug.log
- ✅ Proper address data retrieved from normalized locations table
- ✅ Existing functionality preserved with correct address display
- ✅ Database design integrity maintained (no denormalization)

### **Alternative Consideration**
If some sites legitimately have no address (NULL place_id), the LEFT JOIN ensures they still appear in results with empty address values, maintaining existing behavior.