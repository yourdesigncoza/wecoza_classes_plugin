<?php
/**
 * Classes Display View
 *
 * This view displays all classes from the database in a Bootstrap 5 compatible format.
 * Used by the [wecoza_display_classes] shortcode.
 *
 * Available Variables:
 *   - $classes: Array of class data from the database
 *   - $show_loading: Boolean indicating whether to show loading indicator
 *   - $total_count: Total number of classes found
 *
 * @package WeCoza
 * @see \WeCoza\Controllers\ClassController::displayClassesShortcode() For the controller method that renders this view
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Ensure we have the classes data
$classes = $classes ?? [];
$show_loading = $show_loading ?? true;
$total_count = $total_count ?? 0;
?>

<div class="wecoza-classes-display">
    <!-- Loading Indicator -->
    <?php if ($show_loading): ?>
    <div id="classes-loading" class="d-flex justify-content-center align-items-center py-4">
        <div class="spinner-border text-primary me-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span class="text-muted">Loading classes...</span>
    </div>
    <?php endif; ?>

    <!-- Classes Content -->
    <div id="classes-content" class="<?php echo $show_loading ? 'd-none' : ''; ?>">
        <!-- Header Section -->

        <?php if (empty($classes)): ?>
            <!-- No Classes Found -->
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-1">No Classes Found</h6>
                    <p class="mb-0">There are currently no classes in the database. Create a new class to get started.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Classes Table -->
            <div class="card shadow-none border my-3" data-component-card="data-component-card">
                <div class="card-header p-3 border-bottom bg-body">
                    <div class="row g-3 justify-content-between align-items-center">
                        <div class="col-12 col-md">
                            <h4 class="text-body mb-0" data-anchor="data-anchor" id="classes-table-header">
                                <i class="bi bi-calendar-event me-2"></i>
                                All Classes
                            </h4>
                            <p class="text-muted fs-9 mb-0 mt-1">
                                Displaying <?php echo $total_count; ?> classes from the database
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshClasses()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Refresh
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportClasses()">
                                    <i class="bi bi-download me-1"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="classes-table" class="table table-hover table-sm fs-9 mb-0 overflow-hidden">
                            <thead class="border-bottom">
                                <tr>
                                    <th scope="col" class="border-0 ps-4">
                                        <i class="bi bi-hash me-1"></i>
                                        ID
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-building me-1"></i>
                                        Client ID & Name
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-tag me-1"></i>
                                        Type
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-book me-1"></i>
                                        Subject
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-calendar-date me-1"></i>
                                        Start Date
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-truck me-1"></i>
                                        Delivery Date
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-person me-1"></i>
                                        Agent ID & Name
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="bi bi-award me-1"></i>
                                        SETA
                                    </th>
                                    <th scope="col" class="border-0 pe-4">
                                        <i class="bi bi-gear me-1"></i>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td class="py-2 align-middle text-center fs-8 white-space-nowrap">
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                            #<?php echo esc_html($class['class_id']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-medium">
                                            <?php
                                            $client_id = $class['client_id'] ?? 'Unknown';
                                            $client_name = $class['client_name'] ?? 'Unknown Client';
                                            echo esc_html($client_id . ' : ' . $client_name);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($class['class_type'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <?php echo esc_html($class['class_type']); ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-medium">
                                            <?php echo esc_html($class['class_subject'] ?? 'No Subject'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($class['original_start_date'])): ?>
                                        <span class="text-nowrap">
                                            <?php echo esc_html(date('M j, Y', strtotime($class['original_start_date']))); ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($class['delivery_date'])): ?>
                                        <span class="text-nowrap">
                                            <?php echo esc_html(date('M j, Y', strtotime($class['delivery_date']))); ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($class['agent_name']) && !empty($class['class_agent'])): ?>
                                        <span class="text-nowrap">
                                            <i class="bi bi-person-circle me-1"></i>
                                            <?php
                                            $agent_id = $class['class_agent'] ?? 'Unknown';
                                            $agent_name = $class['agent_name'] ?? 'Unassigned';
                                            echo esc_html($agent_id . ' : ' . $agent_name);
                                            ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 fs-8 white-space-nowrap">
                                        <?php if ($class['seta_funded']): ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-success">
                                            <svg class="svg-inline--fa fa-check ms-1" data-fa-transform="shrink-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="" style="transform-origin: 0.4375em 0.5em;"><g transform="translate(224 256)"><g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)"><path fill="currentColor" d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z" transform="translate(-224 -256)"></path></g></g></svg>
                                            SETA Funded
                                        </span>
                                        <?php else: ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                            <svg class="svg-inline--fa fa-ban ms-1" data-fa-transform="shrink-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="ban" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" style="transform-origin: 0.5em 0.5em;"><g transform="translate(256 256)"><g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)"><path fill="currentColor" d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z" transform="translate(-256 -256)"></path></g></g></svg>
                                            Not SETA
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-link text-body btn-sm dropdown-toggle"
                                                    style="text-decoration: none;"
                                                    type="button"
                                                    id="dropdownMenuButton<?php echo $class['class_id']; ?>"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $class['class_id']; ?>">
                                                <?php if (current_user_can('edit_posts') || current_user_can('manage_options')): ?>
                                                <li>
<?php
// 1. Find the page object for “app/new-class” (or just “new-class”, depending on where it lives)
$page = get_page_by_path( 'app/new-class' ); 
// If your “new-class” page lives directly under /app/, use exactly that path.
// If it’s a top-level page called “new-class”, you can just do get_page_by_path('new-class').

// 2. Grab its permalink (so WP will automatically use the correct domain/child-theme slug, etc.)
if ( $page ) {
    $base_url = get_permalink( $page->ID ); 
} else {
    // Fallback if page not found:
    $base_url = home_url( '/app/new-class/' );
}

// 3. Append ?mode=update&class_id=… with add_query_arg()
$edit_url = add_query_arg(
    [
        'mode'     => 'update',
        'class_id' => $class['class_id'],
    ],
    $base_url
);
?>
                                                    <a class="dropdown-item" href="<?php echo esc_url( $edit_url ); ?>">
                                                        <i class="bi bi-pencil me-2"></i>
                                                        Edit Class
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <li>
<?php
// 1. Find the page object for "app/display-single-class"
$page = get_page_by_path( 'app/display-single-class' );

// 2. Grab its permalink (so WP will automatically use the correct domain/child-theme slug, etc.)
if ( $page ) {
    $base_url = get_permalink( $page->ID );
} else {
    // Fallback if page not found:
    $base_url = home_url( '/app/display-single-class/' );
}

// 3. Append ?class_id=… with add_query_arg()
$view_url = add_query_arg(
    [
        'class_id' => $class['class_id'],
    ],
    $base_url
);
?>
                                                    <a class="dropdown-item" href="<?php echo esc_url( $view_url ); ?>">
                                                        <i class="bi bi-eye me-2"></i>
                                                        View Details
                                                    </a>
                                                </li>
                                                <?php if (current_user_can('manage_options')): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deleteClass(<?php echo $class['class_id']; ?>)">
                                                        <i class="bi bi-trash me-2"></i>
                                                        Delete Class
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card border-0 bg-primary bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-event fs-1 text-primary mb-2"></i>
                            <h5 class="card-title text-primary"><?php echo $total_count; ?></h5>
                            <p class="card-text text-muted small mb-0">Total Classes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-success bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                            <h5 class="card-title text-success">
                                <?php echo count(array_filter($classes, function($c) { return $c['seta_funded']; })); ?>
                            </h5>
                            <p class="card-text text-muted small mb-0">SETA Funded</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-warning bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-award fs-1 text-warning mb-2"></i>
                            <h5 class="card-title text-warning">
                                <?php echo count(array_filter($classes, function($c) { return $c['exam_class']; })); ?>
                            </h5>
                            <p class="card-text text-muted small mb-0">Exam Classes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-info bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 text-info mb-2"></i>
                            <h5 class="card-title text-info">
                                <?php echo count(array_unique(array_column($classes, 'client_id'))); ?>
                            </h5>
                            <p class="card-text text-muted small mb-0">Unique Clients</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hide loading indicator and show content after a brief delay
    <?php if ($show_loading): ?>
    setTimeout(function() {
        const loading = document.getElementById('classes-loading');
        const content = document.getElementById('classes-content');

        if (loading) loading.classList.add('d-none');
        if (content) content.classList.remove('d-none');
    }, 500);
    <?php endif; ?>

    // Check for delete success message in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('deleted') === 'success') {
        const message = `Class Deleted.`;

        showSuccessBanner(message);

        // Clean up URL parameters
        const cleanUrl = new URL(window.location);
        cleanUrl.searchParams.delete('deleted');
        cleanUrl.searchParams.delete('class_subject');
        cleanUrl.searchParams.delete('class_code');
        window.history.replaceState({}, document.title, cleanUrl.toString());
    }
});

function refreshClasses() {
    location.reload();
}

function exportClasses() {
    // Placeholder for export functionality
    alert('Export functionality will be implemented soon.');
}

function viewClassDetails(classId) {
    // Placeholder for view details functionality
    alert('View details for class ID: ' + classId);
}

function deleteClass(classId) {
    // Check if user is administrator
    const isAdmin = <?php echo current_user_can('manage_options') ? 'true' : 'false'; ?>;
    if (!isAdmin) {
        alert('Only administrators can delete classes.');
        return;
    }

    if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
        // Show loading state
        const deleteButton = document.querySelector(`[onclick="deleteClass(${classId})"]`);
        const originalText = deleteButton.innerHTML;
        deleteButton.innerHTML = '<i class="bi bi-spinner-border me-2"></i>Deleting...';
        deleteButton.disabled = true;

        // Make AJAX request to delete class
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'delete_class',
                nonce: '<?php echo wp_create_nonce('wecoza_class_nonce'); ?>',
                class_id: classId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to current page with success message
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('deleted', 'success');
                window.location.href = currentUrl.toString();
            } else {
                // Show error message
                alert('Error: ' + (data.data || 'Failed to delete class.'));

                // Restore button state
                deleteButton.innerHTML = originalText;
                deleteButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('An error occurred while deleting the class. Please try again.');

            // Restore button state
            deleteButton.innerHTML = originalText;
            deleteButton.disabled = false;
        });
    }
}



function showSuccessBanner(message) {
    // Create success banner
    const banner = document.createElement('div');
    banner.className = 'alert alert-subtle-success alert-dismissible fade show position-fixed';
    banner.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
    banner.innerHTML = `
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Success!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Add to page
    document.body.appendChild(banner);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (banner.parentNode) {
            banner.remove();
        }
    }, 5000);
}
</script>