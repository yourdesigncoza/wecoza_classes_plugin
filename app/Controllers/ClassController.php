<?php
/**
 * ClassController.php
 *
 * Core controller for handling class-related operations.
 * Handles shortcode rendering and WordPress page management.
 *
 * AJAX operations have been extracted to ClassAjaxController.
 * Data access has been extracted to ClassRepository.
 * Form processing has been extracted to FormDataProcessor.
 * Schedule generation has been extracted to ScheduleService.
 * QA operations have been moved to QAController.
 *
 * @package WeCozaClasses
 * @since 2.0.0 Refactored for single responsibility
 */

namespace WeCozaClasses\Controllers;

use WeCozaClasses\Models\ClassModel;
use WeCozaClasses\Repositories\ClassRepository;
use WeCozaClasses\Controllers\PublicHolidaysController;

/**
 * Core Class Controller
 *
 * Responsibilities:
 * - Registering and rendering shortcodes
 * - Ensuring required WordPress pages exist
 * - Enqueuing assets conditionally
 *
 * @package WeCozaClasses
 */
class ClassController
{
    /**
     * Constructor - Register WordPress hooks
     */
    public function __construct()
    {
        // Register WordPress hooks
        \add_action('init', [$this, 'registerShortcodes']);
        \add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        // Ensure required pages exist
        \add_action('init', [$this, 'ensureRequiredPages']);

        // Note: AJAX endpoints are registered by ClassAjaxController and QAController
    }

    /**
     * Register all class-related shortcodes
     */
    public function registerShortcodes(): void
    {
        \add_shortcode('wecoza_capture_class', [$this, 'captureClassShortcode']);
        \add_shortcode('wecoza_display_classes', [$this, 'displayClassesShortcode']);
        \add_shortcode('wecoza_display_single_class', [$this, 'displaySingleClassShortcode']);
    }

    /**
     * Ensure required pages exist for the plugin functionality
     */
    public function ensureRequiredPages(): void
    {
        // Only run this once per request and only for admin users
        if (!\current_user_can('manage_options') || \get_transient('wecoza_pages_checked')) {
            return;
        }

        // Set transient to prevent multiple checks
        \set_transient('wecoza_pages_checked', true, HOUR_IN_SECONDS);

        // Check if display-single-class page exists
        $class_details_page = \get_page_by_path('app/display-single-class');

        if (!$class_details_page) {
            // Get or create app parent page
            $app_page = \get_page_by_path('app');
            $app_page_id = 0;

            if (!$app_page) {
                $app_page_id = \wp_insert_post([
                    'post_title' => 'App',
                    'post_content' => '<h2>WeCoza Application</h2><p>Welcome to the WeCoza training management system.</p>',
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => 'app',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ]);
            } else {
                $app_page_id = $app_page->ID;
            }

            // Create display-single-class page
            if ($app_page_id && !\is_wp_error($app_page_id)) {
                \wp_insert_post([
                    'post_title' => 'Display Single Class',
                    'post_content' => '<h2>Class Details</h2>
<p>View detailed information about this training class.</p>

[wecoza_display_single_class]

<hr>

<div class="row mt-4">
    <div class="col-md-6">
        <a href="/app/all-classes/" class="btn btn-secondary">← Back to All Classes</a>
    </div>
    <div class="col-md-6 text-end">
        <a href="/app/update-class/?mode=update" class="btn btn-primary">Edit Class</a>
    </div>
</div>',
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => 'display-single-class',
                    'post_parent' => $app_page_id,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ]);
            }
        }
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueueAssets(): void
    {
        // Only enqueue on pages that use our shortcodes
        if (!$this->shouldEnqueueAssets()) {
            return;
        }

        // FullCalendar CDN
        \wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js',
            [],
            '6.1.15',
            true
        );

        \wp_enqueue_script(
            'wecoza-calendar-js',
            WECOZA_CLASSES_JS_URL . 'wecoza-calendar.js',
            ['jquery', 'fullcalendar'],
            WECOZA_CLASSES_VERSION,
            true
        );

        // Utility scripts (must load first)
        \wp_enqueue_script(
            'wecoza-escape-utils-js',
            WECOZA_CLASSES_JS_URL . 'utils/escape.js',
            [],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-date-utils-js',
            WECOZA_CLASSES_JS_URL . 'utils/date-utils.js',
            [],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-table-manager-js',
            WECOZA_CLASSES_JS_URL . 'utils/table-manager.js',
            [],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-ajax-utils-js',
            WECOZA_CLASSES_JS_URL . 'utils/ajax-utils.js',
            ['jquery'],
            WECOZA_CLASSES_VERSION,
            true
        );

        // Plugin JavaScript files
        \wp_enqueue_script(
            'wecoza-class-js',
            WECOZA_CLASSES_JS_URL . 'class-capture.js',
            ['jquery', 'wecoza-escape-utils-js', 'wecoza-date-utils-js'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-class-schedule-form-js',
            WECOZA_CLASSES_JS_URL . 'class-schedule-form.js',
            ['jquery', 'wecoza-learner-level-utils-js', 'wecoza-date-utils-js'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-learner-level-utils-js',
            WECOZA_CLASSES_JS_URL . 'learner-level-utils.js',
            ['jquery'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-class-types-js',
            WECOZA_CLASSES_JS_URL . 'class-types.js',
            ['jquery', 'wecoza-class-js', 'wecoza-learner-level-utils-js', 'wecoza-escape-utils-js'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-classes-table-search-js',
            WECOZA_CLASSES_JS_URL . 'classes-table-search.js',
            ['jquery'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-learner-selection-table-js',
            WECOZA_CLASSES_JS_URL . 'learner-selection-table.js',
            ['jquery', 'wecoza-escape-utils-js'],
            WECOZA_CLASSES_VERSION,
            true
        );

        // Register single-class-display.js (will be enqueued with localized data by shortcode)
        \wp_register_script(
            'wecoza-single-class-display-js',
            WECOZA_CLASSES_JS_URL . 'single-class-display.js',
            ['jquery', 'wecoza-calendar-js', 'wecoza-escape-utils-js', 'wecoza-date-utils-js'],
            WECOZA_CLASSES_VERSION,
            true
        );

        // Localize script with AJAX URL and nonce
        \wp_localize_script('wecoza-class-js', 'wecozaClass', [
            'ajaxUrl' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('wecoza_class_nonce'),
            'siteAddresses' => ClassRepository::getSiteAddresses(),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'conflictCheckEnabled' => true
        ]);

        // Get public holidays data for the class schedule form
        try {
            $publicHolidaysController = PublicHolidaysController::getInstance();
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;

            // Get holidays for current and next year to cover class schedules
            $currentYearHolidays = $publicHolidaysController->getHolidaysForCalendar($currentYear);
            $nextYearHolidays = $publicHolidaysController->getHolidaysForCalendar($nextYear);
            $allHolidays = array_merge($currentYearHolidays, $nextYearHolidays);

            // Localize public holidays data for class-schedule-form.js
            \wp_localize_script('wecoza-class-schedule-form-js', 'wecozaPublicHolidays', [
                'events' => $allHolidays
            ]);
        } catch (\Exception $e) {
            // Silently fail - holidays are optional
        }

        // Localize calendar data for wecoza-calendar.js
        \wp_localize_script('wecoza-calendar-js', 'wecozaCalendar', [
            'ajaxUrl' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('wecoza_calendar_nonce'),
            'fallbackCdn' => 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js',
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        ]);
    }

    /**
     * Check if we should enqueue assets on current page
     *
     * @return bool
     */
    private function shouldEnqueueAssets(): bool
    {
        global $post;

        if (!$post) {
            return false;
        }

        // Check if any of our shortcodes are present in the content
        $shortcodes = ['wecoza_capture_class', 'wecoza_display_classes', 'wecoza_display_single_class'];

        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle class capture shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function captureClassShortcode($atts): string
    {
        // Process shortcode attributes
        $atts = \shortcode_atts([
            'redirect_url' => '',
        ], $atts);

        // Check for URL parameters to determine mode
        $mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'create';
        $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

        // Handle different modes
        if ($mode === 'update') {
            // For testing: allow update mode without class_id to see all fields
            if ($class_id <= 0) {
                return $this->handleUpdateMode($atts, null);
            }
            return $this->handleUpdateMode($atts, $class_id);
        }

        return $this->handleCreateMode($atts);
    }

    /**
     * Handle create mode logic
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    private function handleCreateMode(array $atts): string
    {
        // Get data for the view from repository
        $viewData = [
            'mode' => 'create',
            'class_data' => null,
            'clients' => ClassRepository::getClients(),
            'sites' => ClassRepository::getSites(),
            'agents' => ClassRepository::getAgents(),
            'supervisors' => ClassRepository::getSupervisors(),
            'learners' => ClassRepository::getLearners(),
            'setas' => ClassRepository::getSeta(),
            'class_types' => ClassRepository::getClassTypes(),
            'yes_no_options' => ClassRepository::getYesNoOptions(),
            'class_notes_options' => ClassRepository::getClassNotesOptions(),
            'redirect_url' => $atts['redirect_url']
        ];

        // Render the view
        return \WeCozaClasses\view('components/class-capture-form', $viewData);
    }

    /**
     * Handle update mode logic
     *
     * @param array $atts Shortcode attributes
     * @param int|null $class_id Class ID to update
     * @return string HTML output
     */
    private function handleUpdateMode(array $atts, ?int $class_id): string
    {
        $class = null;

        // Enable debug logging if requested
        $debug = isset($_GET['debug']) && $_GET['debug'] === '1';

        if ($class_id) {
            // Get existing class data from repository
            $class = ClassRepository::getSingleClass($class_id);

            if (empty($class)) {
                return '<div class="alert alert-subtle-danger">Class not found.</div>';
            }

            // Debug logging
            if ($debug) {
                $this->logDebugData($class_id, $class);
            }
        }

        // Get data for the view from repository
        $viewData = [
            'mode' => 'update',
            'class_data' => $class,
            'class_id' => $class_id,
            'clients' => ClassRepository::getClients(),
            'sites' => ClassRepository::getSites(),
            'agents' => ClassRepository::getAgents(),
            'supervisors' => ClassRepository::getSupervisors(),
            'learners' => ClassRepository::getLearners(),
            'setas' => ClassRepository::getSeta(),
            'class_types' => ClassRepository::getClassTypes(),
            'yes_no_options' => ClassRepository::getYesNoOptions(),
            'class_notes_options' => ClassRepository::getClassNotesOptions(),
            'redirect_url' => $atts['redirect_url']
        ];

        // Render the view
        return \WeCozaClasses\view('components/class-capture-form', $viewData);
    }

    /**
     * Handle display classes shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function displayClassesShortcode($atts): string
    {
        // Process shortcode attributes
        $atts = \shortcode_atts([
            'limit' => 50,
            'order_by' => 'created_at',
            'order' => 'DESC',
            'show_loading' => true,
        ], $atts);

        try {
            // Get all classes from database using ClassRepository
            $classes = ClassRepository::getAllClasses($atts);

            // Enrich classes with agent names using ClassRepository
            $classes = ClassRepository::enrichClassesWithAgentNames($classes);

            // Calculate active classes count (excluding currently stopped classes)
            $activeClassesCount = 0;
            foreach ($classes as $class) {
                if (!$this->isClassCurrentlyStopped($class)) {
                    $activeClassesCount++;
                }
            }

            // Prepare view data
            $viewData = [
                'classes' => $classes,
                'show_loading' => $atts['show_loading'],
                'total_count' => count($classes),
                'active_count' => $activeClassesCount,
                'controller' => $this
            ];

            // Render the view
            return \WeCozaClasses\view('components/classes-display', $viewData);

        } catch (\Exception $e) {
            return '<div class="alert alert-subtle-danger">Error loading classes: ' . esc_html($e->getMessage()) . '</div>';
        }
    }

    /**
     * Handle display single class shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function displaySingleClassShortcode($atts): string
    {
        // Process shortcode attributes
        $atts = \shortcode_atts([
            'class_id' => 0,
            'show_loading' => true,
        ], $atts);

        // Get class_id from URL parameter if not provided in shortcode
        $class_id = $atts['class_id'] ?: (isset($_GET['class_id']) ? intval($_GET['class_id']) : 0);

        if (empty($class_id) || $class_id <= 0) {
            return '<div class="alert alert-warning">No valid class ID provided.</div>';
        }

        try {
            // Get single class from database using ClassRepository
            $class = ClassRepository::getSingleClass($class_id);

            // Prepare view data
            $viewData = [
                'class' => $class,
                'show_loading' => $atts['show_loading'],
                'error_message' => ''
            ];

            // If class not found, set error message
            if (empty($class)) {
                $viewData['error_message'] = "Class with ID {$class_id} was not found in the database.";
            }

            // Enqueue single-class-display.js with localized data
            if (!empty($class)) {
                $this->enqueueAndLocalizeSingleClassScript($class, $atts['show_loading']);
            }

            // Render the view
            return \WeCozaClasses\view('components/single-class-display', $viewData);

        } catch (\Exception $e) {
            return '<div class="alert alert-subtle-danger">Error loading class: ' . esc_html($e->getMessage()) . '</div>';
        }
    }

    /**
     * Enqueue and localize the single-class-display.js script with class data
     *
     * @param array $class Class data array
     * @param bool $showLoading Whether to show loading indicator
     */
    private function enqueueAndLocalizeSingleClassScript(array $class, bool $showLoading): void
    {
        // Enqueue the registered script
        \wp_enqueue_script('wecoza-single-class-display-js');

        // Build the edit URL
        $newClassPage = \get_page_by_path('app/new-class');
        $editUrl = $newClassPage
            ? \add_query_arg(['mode' => 'update', 'class_id' => $class['class_id']], \get_permalink($newClassPage->ID))
            : \add_query_arg(['mode' => 'update', 'class_id' => $class['class_id']], \home_url('/app/new-class/'));

        // Build the classes list URL
        $classesUrl = \esc_url(\home_url('/app/all-classes'));

        // Extract notes data for filtering
        $notesData = $class['class_notes_data'] ?? [];
        if (is_string($notesData)) {
            $notesData = json_decode($notesData, true) ?: [];
        }

        // Localize script with all required data
        \wp_localize_script('wecoza-single-class-display-js', 'WeCozaSingleClass', [
            // Class data
            'classId' => $class['class_id'] ?? null,
            'classCode' => $class['class_code'] ?? '',
            'classSubject' => $class['class_subject'] ?? '',
            'startDate' => $class['original_start_date'] ?? '',
            'deliveryDate' => $class['delivery_date'] ?? '',
            'duration' => $class['class_duration'] ?? '',
            'scheduleData' => $class['schedule_data'] ?? null,

            // URLs
            'ajaxUrl' => \admin_url('admin-ajax.php'),
            'classesUrl' => $classesUrl,
            'editUrl' => \esc_url_raw($editUrl),

            // Security
            'calendarNonce' => \wp_create_nonce('wecoza_calendar_nonce'),
            'classNonce' => \wp_create_nonce('wecoza_class_nonce'),

            // Permissions
            'canEdit' => \current_user_can('edit_posts') || \current_user_can('manage_options'),
            'isAdmin' => \current_user_can('manage_options'),

            // UI state
            'showLoading' => $showLoading,

            // Notes data for filtering
            'notesData' => $notesData,

            // Debug mode
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        ]);
    }

    /**
     * Check if a class is currently stopped based on stop_restart_dates
     *
     * @param array $class Class data
     * @return bool True if class is currently stopped, false otherwise
     */
    public function isClassCurrentlyStopped(array $class): bool
    {
        // Check if stop_restart_dates field exists and has data
        if (empty($class['stop_restart_dates'])) {
            return false;
        }

        // Parse JSON if it's a string
        $stopRestartDates = is_string($class['stop_restart_dates'])
            ? json_decode($class['stop_restart_dates'], true)
            : $class['stop_restart_dates'];

        // If parsing failed or no data, class is not stopped
        if (!is_array($stopRestartDates) || empty($stopRestartDates)) {
            return false;
        }

        $currentDate = date('Y-m-d');

        // Check each stop/restart period
        foreach ($stopRestartDates as $period) {
            if (!isset($period['stop_date']) || !isset($period['restart_date'])) {
                continue;
            }

            $stopDate = $period['stop_date'];
            $restartDate = $period['restart_date'];

            // Check if current date falls between stop and restart dates (inclusive)
            if ($currentDate >= $stopDate && $currentDate <= $restartDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log debug data for class updates (development only)
     *
     * @param int $class_id Class ID
     * @param array $class_data Class data array
     */
    private function logDebugData(int $class_id, array $class_data): void
    {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/wecoza-logs/update-form/' . date('Y-m-d');

        // Create directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $timestamp = date('H-i-s');
        $log_file = $log_dir . '/' . $timestamp . '-class-' . $class_id . '-data.json';

        // Prepare debug data
        $debug_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'class_id' => $class_id,
            'user_id' => get_current_user_id(),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'class_data' => $class_data,
            'field_analysis' => [
                'client_id' => [
                    'value' => $class_data['client_id'] ?? null,
                    'type' => gettype($class_data['client_id'] ?? null),
                    'exists' => isset($class_data['client_id'])
                ],
                'site_id' => [
                    'value' => $class_data['site_id'] ?? null,
                    'type' => gettype($class_data['site_id'] ?? null),
                    'exists' => isset($class_data['site_id'])
                ],
                'class_type' => [
                    'value' => $class_data['class_type'] ?? null,
                    'type' => gettype($class_data['class_type'] ?? null),
                    'exists' => isset($class_data['class_type'])
                ],
                'seta_funded' => [
                    'value' => $class_data['seta_funded'] ?? null,
                    'type' => gettype($class_data['seta_funded'] ?? null),
                    'exists' => isset($class_data['seta_funded'])
                ],
                'exam_class' => [
                    'value' => $class_data['exam_class'] ?? null,
                    'type' => gettype($class_data['exam_class'] ?? null),
                    'exists' => isset($class_data['exam_class'])
                ],
                'schedule_data' => [
                    'exists' => isset($class_data['schedule_data']),
                    'is_array' => is_array($class_data['schedule_data'] ?? null),
                    'keys' => array_keys($class_data['schedule_data'] ?? [])
                ]
            ]
        ];

        // Write debug data
        file_put_contents($log_file, json_encode($debug_data, JSON_PRETTY_PRINT));

        // Also create a summary log
        $summary_file = $log_dir . '/' . $timestamp . '-class-' . $class_id . '-summary.log';
        $summary = "Update Form Debug Log\n";
        $summary .= "=====================\n";
        $summary .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $summary .= "Class ID: $class_id\n";
        $summary .= "User ID: " . get_current_user_id() . "\n\n";
        $summary .= "Field Population Status:\n";

        foreach ($debug_data['field_analysis'] as $field => $info) {
            $status = $info['exists'] ? '✓' : '✗';
            $summary .= "$status $field: ";
            if ($field === 'schedule_data') {
                $summary .= $info['exists'] ? 'Present' : 'Missing';
            } else {
                $summary .= $info['exists'] ? $info['value'] . ' (' . $info['type'] . ')' : 'NOT SET';
            }
            $summary .= "\n";
        }

        file_put_contents($summary_file, $summary);
    }
}
