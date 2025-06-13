<?php
/**
 * Classes Display View
 *
 * This view displays all classes from the database in a Bootstrap 5 compatible format.
 * Used by the [wecoza_display_classes] shortcode.
 *
 * Available Variables:
 *   - $classes: Array of class data from the database (enriched with agent names)
 *   - $show_loading: Boolean indicating whether to show loading indicator
 *   - $total_count: Total number of classes found
 *   - $active_count: Number of classes that are currently active (not stopped)
 *   - $controller: ClassController instance for accessing helper methods
 *
 * Class Data Fields (enriched):
 *   - agent_name: Current agent name (from class_agent ID lookup)
 *   - initial_agent_name: Initial agent name (from initial_class_agent ID lookup)
 *
 * Agent Display Logic:
 *   - Same agent (current == initial): Shows "[ID] : [Name]" with person-circle icon
 *   - Different agents: Shows both "Current:" and "Initial:" with respective icons
 *   - Single agent: Shows the available agent without comparison labels
 *   - No agents: Shows "No Agent Assigned" warning badge
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
$active_count = $active_count ?? 0;
$controller = $controller ?? null;
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
                <div class="card-header p-3 border-bottom">
                    <div class="row g-3 justify-content-between align-items-center mb-3">
                        <div class="col-12 col-md">
                            <h4 class="text-body mb-0" data-anchor="data-anchor" id="classes-table-header">
                                All Classes
                                <i class="bi bi-calendar-event ms-2"></i>
                            </h4>
                        </div>
                        <div class="search-box col-auto">
                          <form class="position-relative"><input class="form-control search-input search form-control-sm" type="search" placeholder="Search" aria-label="Search">
                            <svg class="svg-inline--fa fa-magnifying-glass search-box-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="magnifying-glass" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"></path></svg><!-- <span class="fas fa-search search-box-icon"></span> Font Awesome fontawesome.com -->
                          </form>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshClasses()">
                                    Refresh
                                    <i class="bi bi-arrow-clockwise ms-1"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportClasses()">
                                    Export
                                    <i class="bi bi-download ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
<!-- Summary strip -->
                  <div class="col-12">
                    <div class="scrollbar">
                      <div class="row g-0 flex-nowrap">
                        <div class="col-auto border-end pe-4">
                          <h6 class="text-body-tertiary">Total Classes : <?php echo $total_count; ?> <div class="badge badge-phoenix fs-10 badge-phoenix-success">+ 11</div></h6>
                        </div>
                        <div class="col-auto px-4 border-end">
                          <h6 class="text-body-tertiary">Active Classes : <?php echo $active_count; ?></h6>
                        </div>
                        <div class="col-auto px-4 border-end">
                          <h6 class="text-body-tertiary">SETA Funded : <?php echo count(array_filter($classes, function($c) { return $c['seta_funded']; })); ?> <div class="badge badge-phoenix fs-10 badge-phoenix-success">+ 5</div></h6>
                        </div>
                        <div class="col-auto px-4 border-end">
                          <h6 class="text-body-tertiary">Exam Classes : <?php echo count(array_filter($classes, function($c) { return $c['exam_class']; })); ?> <div class="badge badge-phoenix fs-10 badge-phoenix-danger">+ 8</div></h6>
                        </div>
                        <div class="col-auto px-4">
                          <h6 class="text-body-tertiary">Unique Clients : <?php echo count(array_unique(array_column($classes, 'client_id'))); ?> <div class="badge badge-phoenix fs-10 badge-phoenix-success">- 2</div></h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="card-body p-4 py-2">
                    <div class="table-responsive">
                        <table id="classes-table" class="table table-hover table-sm fs-9 mb-0 overflow-hidden">
                            <thead class="border-bottom">
                                <tr>
                                    <th scope="col" class="border-0 ps-4">
                                        ID
                                        <i class="bi bi-hash ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Client ID & Name
                                        <i class="bi bi-building ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Type
                                        <i class="bi bi-tag ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Subject
                                        <i class="bi bi-book ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Start Date
                                        <i class="bi bi-calendar-date ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Agent ID & Name
                                        <i class="bi bi-person ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Exam Class
                                        <i class="bi bi-mortarboard ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Status
                                        <i class="bi bi-activity ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        SETA
                                        <i class="bi bi-award ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0 pe-4">
                                        Actions
                                        <i class="bi bi-gear ms-1"></i>
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
                                        <?php
                                        $hasCurrentAgent = !empty($class['agent_name']) && !empty($class['class_agent']);
                                        $hasInitialAgent = !empty($class['initial_agent_name']) && !empty($class['initial_class_agent']);
                                        $sameAgent = $hasCurrentAgent && $hasInitialAgent && ($class['class_agent'] == $class['initial_class_agent']);
                                        ?>

                                        <?php if ($hasCurrentAgent || $hasInitialAgent): ?>
                                        <div class="text-nowrap">
                                            <?php if ($sameAgent): ?>
                                            <!-- Same agent for both current and initial - show simplified format -->
                                            <div>
                                                <?php
                                                $agent_id = $class['class_agent'];
                                                $agent_name = $class['agent_name'];
                                                echo esc_html($agent_id . ' : ' . $agent_name);
                                                ?>
                                                <i class="bi bi-person-circle ms-1"></i>
                                            </div>
                                            <?php else: ?>
                                            <!-- Different agents or only one agent - show detailed format -->
                                            <?php if ($hasCurrentAgent): ?>
                                            <div class="mb-1">
                                                <strong>Current:</strong>
                                                <?php
                                                $agent_id = $class['class_agent'];
                                                $agent_name = $class['agent_name'];
                                                echo esc_html($agent_id . ' : ' . $agent_name);
                                                ?>
                                                <i class="bi bi-person-circle ms-1"></i>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($hasInitialAgent): ?>
                                            <div class="text-muted small">
                                                <strong>Initial:</strong>
                                                <?php
                                                $initial_agent_id = $class['initial_class_agent'];
                                                $initial_agent_name = $class['initial_agent_name'];
                                                echo esc_html($initial_agent_id . ' : ' . $initial_agent_name);
                                                ?>
                                                <i class="bi bi-person-badge ms-1"></i>
                                            </div>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-warning">
                                            No Agent Assigned
                                            <i class="bi bi-exclamation-triangle ms-1"></i>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 fs-8 white-space-nowrap">
                                        <?php if ($class['exam_class']): ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-success">
                                            Exam Class
                                            <svg class="svg-inline--fa fa-check ms-1" data-fa-transform="shrink-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="" style="transform-origin: 0.4375em 0.5em;"><g transform="translate(224 256)"><g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)"><path fill="currentColor" d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z" transform="translate(-224 -256)"></path></g></g></svg>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                            Not Exam
                                            <svg class="svg-inline--fa fa-ban ms-1" data-fa-transform="shrink-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="ban" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" style="transform-origin: 0.5em 0.5em;"><g transform="translate(256 256)"><g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)"><path fill="currentColor" d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z" transform="translate(-256 -256)"></path></g></g></svg>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 fs-8 white-space-nowrap">
                                        <?php if ($controller && $controller->isClassCurrentlyStopped($class)): ?>
                                        <span class="badge badge-phoenix fs-10 badge-phoenix-warning">
                                            <span class="badge-label">Stopped</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon ms-1" style="height:12.8px;width:12.8px;">
                                                <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge badge-phoenix fs-10 badge-phoenix-success">
                                            <span class="badge-label">Active</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check ms-1" style="height:12.8px;width:12.8px;">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 fs-8 white-space-nowrap">
                                        <?php if ($class['seta_funded']): ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-success">
                                            SETA Funded
                                            <svg class="svg-inline--fa fa-check ms-1" data-fa-transform="shrink-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="" style="transform-origin: 0.4375em 0.5em;"><g transform="translate(224 256)"><g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)"><path fill="currentColor" d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z" transform="translate(-224 -256)"></path></g></g></svg>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                            Not SETA
                                            <svg class="svg-inline--fa fa-ban ms-1" data-fa-transform="shrink-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="ban" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" style="transform-origin: 0.5em 0.5em;"><g transform="translate(256 256)"><g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)"><path fill="currentColor" d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2-35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z" transform="translate(-256 -256)"></path></g></g></svg>
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
                                                        Edit Class
                                                        <i class="bi bi-pencil ms-2"></i>
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
                                                        View Details
                                                        <i class="bi bi-eye ms-2"></i>
                                                    </a>
                                                </li>
                                                <?php if (current_user_can('manage_options')): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deleteClass(<?php echo $class['class_id']; ?>)">
                                                        Delete Class
                                                        <i class="bi bi-trash ms-2"></i>
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
        deleteButton.innerHTML = 'Deleting...<i class="bi bi-spinner-border ms-2"></i>';
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
        <strong>Success!</strong> ${message}
        <i class="bi bi-check-circle-fill ms-2"></i>
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