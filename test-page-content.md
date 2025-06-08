# WeCoza Classes Plugin Test Page Content

This file contains sample content that can be used to test the WeCoza Classes Plugin shortcodes in WordPress.

## Test Page 1: Class Capture Form

**Page Title:** Create New Class
**Page Content:**
```
<h2>Create New Class</h2>
<p>Use the form below to create a new training class.</p>

[wecoza_capture_class]

<hr>

<h3>Instructions:</h3>
<ul>
<li>Fill in all required fields marked with *</li>
<li>Select the appropriate client and site</li>
<li>Choose the class type and subject</li>
<li>Set up the class schedule using the calendar</li>
<li>Add learners and assign agents</li>
</ul>
```

## Test Page 2: Update Class Form

**Page Title:** Update Class
**Page URL:** `/update-class/?mode=update&class_id=1`
**Page Content:**
```
<h2>Update Class</h2>
<p>Use the form below to update an existing training class.</p>

[wecoza_capture_class]

<p><a href="/all-classes/" class="btn btn-secondary">← Back to All Classes</a></p>
```

## Test Page 3: All Classes Display

**Page Title:** All Classes
**Page Content:**
```
<h2>All Training Classes</h2>
<p>View and manage all training classes in the system.</p>

[wecoza_display_classes limit="20" order_by="created_at" order="DESC"]

<hr>

<h3>Actions Available:</h3>
<ul>
<li><strong>View:</strong> Click on any class to view details</li>
<li><strong>Edit:</strong> Use the edit button to modify class information</li>
<li><strong>Delete:</strong> Remove classes that are no longer needed</li>
</ul>

<p><a href="/create-class/" class="btn btn-primary">+ Create New Class</a></p>
```

## Test Page 4: Single Class Display

**Page Title:** Class Details
**Page URL:** `/class-details/?class_id=1`
**Page Content:**
```
<h2>Class Details</h2>
<p>View detailed information about this training class.</p>

[wecoza_display_single_class]

<hr>

<div class="row mt-4">
    <div class="col-md-6">
        <a href="/all-classes/" class="btn btn-secondary">← Back to All Classes</a>
    </div>
    <div class="col-md-6 text-end">
        <a href="/update-class/?mode=update&class_id=1" class="btn btn-primary">Edit Class</a>
    </div>
</div>
```

## Test Page 5: Combined Test Page

**Page Title:** WeCoza Classes Plugin Test
**Page Content:**
```
<h2>WeCoza Classes Plugin Test Page</h2>
<p>This page tests all the shortcodes provided by the WeCoza Classes Plugin.</p>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Test 1: Class Capture Form</h3>
            </div>
            <div class="card-body">
                [wecoza_capture_class]
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Test 2: All Classes Display</h3>
            </div>
            <div class="card-body">
                [wecoza_display_classes limit="10"]
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Test 3: Single Class Display</h3>
            </div>
            <div class="card-body">
                <p><em>Note: This will show an error if no class_id is provided in the URL.</em></p>
                [wecoza_display_single_class]
            </div>
        </div>
    </div>
</div>
```

## Testing Instructions

1. **Install the Plugin:**
   - Upload the `wecoza-classes-plugin` folder to `/wp-content/plugins/`
   - Activate the plugin in WordPress admin

2. **Create Test Pages:**
   - Create new pages in WordPress admin
   - Copy the content from above into each page
   - Publish the pages

3. **Test Database Connection:**
   - Ensure PostgreSQL credentials are set in WordPress options:
     - `wecoza_postgres_host`
     - `wecoza_postgres_port`
     - `wecoza_postgres_dbname`
     - `wecoza_postgres_user`
     - `wecoza_postgres_password`

4. **Test Functionality:**
   - Visit each test page
   - Check that shortcodes render properly
   - Test form submissions
   - Test AJAX operations
   - Test calendar functionality

5. **Check for Errors:**
   - Monitor WordPress debug log for any errors
   - Check browser console for JavaScript errors
   - Verify database operations work correctly

## Expected Results

- **Class Capture Form:** Should display a comprehensive form with all fields populated from the static data methods
- **All Classes Display:** Should show a table of classes (may be empty initially)
- **Single Class Display:** Should show class details or appropriate error message
- **AJAX Operations:** Should work for form submissions, calendar events, etc.
- **Calendar Integration:** Should display FullCalendar with public holidays

## Troubleshooting

If you encounter issues:

1. Check WordPress debug log for errors
2. Verify database connection settings
3. Ensure all plugin files are uploaded correctly
4. Check that Bootstrap 5 is available (plugin includes its own CSS)
5. Verify user permissions for class management operations
