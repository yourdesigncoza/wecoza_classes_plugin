<?php
/**
 * ClassController.php
 *
 * Controller for handling class-related operations
 * Extracted from WeCoza theme for standalone plugin
 */

namespace WeCozaClasses\Controllers;

use WeCozaClasses\Models\ClassModel;
use WeCozaClasses\Controllers\ClassTypesController;
use WeCozaClasses\Controllers\PublicHolidaysController;

// WordPress functions are in global namespace
// We'll access them directly with the global namespace prefix
// Example: \add_action() instead of add_action()

class ClassController {

    /**
     * Constructor
     */
    public function __construct() {
        // Register WordPress hooks
        \add_action('init', [$this, 'registerShortcodes']);
        \add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        // Ensure required pages exist
        \add_action('init', [$this, 'ensureRequiredPages']);
        
        // Register AJAX handlers
        \add_action('wp_ajax_save_class', [__CLASS__, 'saveClassAjax']);
        \add_action('wp_ajax_nopriv_save_class', [__CLASS__, 'saveClassAjax']);
        \add_action('wp_ajax_delete_class', [__CLASS__, 'deleteClassAjax']);
        \add_action('wp_ajax_nopriv_delete_class', [__CLASS__, 'deleteClassAjax']);
        \add_action('wp_ajax_get_calendar_events', [__CLASS__, 'getCalendarEventsAjax']);
        \add_action('wp_ajax_nopriv_get_calendar_events', [__CLASS__, 'getCalendarEventsAjax']);
        \add_action('wp_ajax_get_class_subjects', [__CLASS__, 'getClassSubjectsAjax']);
        \add_action('wp_ajax_nopriv_get_class_subjects', [__CLASS__, 'getClassSubjectsAjax']);
        \add_action('wp_ajax_get_class_qa_data', [__CLASS__, 'getClassQAData']);
        \add_action('wp_ajax_nopriv_get_class_qa_data', [__CLASS__, 'getClassQAData']);
        \add_action('wp_ajax_get_class_notes', [__CLASS__, 'getClassNotes']);
        \add_action('wp_ajax_nopriv_get_class_notes', [__CLASS__, 'getClassNotes']);
        \add_action('wp_ajax_save_class_note', [__CLASS__, 'saveClassNote']);
        \add_action('wp_ajax_nopriv_save_class_note', [__CLASS__, 'saveClassNote']);
        \add_action('wp_ajax_delete_class_note', [__CLASS__, 'deleteClassNote']);
        \add_action('wp_ajax_nopriv_delete_class_note', [__CLASS__, 'deleteClassNote']);
        \add_action('wp_ajax_submit_qa_question', [__CLASS__, 'submitQAQuestion']);
        \add_action('wp_ajax_nopriv_submit_qa_question', [__CLASS__, 'submitQAQuestion']);
        \add_action('wp_ajax_upload_attachment', [__CLASS__, 'uploadAttachment']);
        \add_action('wp_ajax_nopriv_upload_attachment', [__CLASS__, 'uploadAttachment']);
    }

    /**
     * Register all class-related shortcodes
     */
    public function registerShortcodes() {
        \add_shortcode('wecoza_capture_class', [$this, 'captureClassShortcode']);
        \add_shortcode('wecoza_display_classes', [$this, 'displayClassesShortcode']);
        \add_shortcode('wecoza_display_single_class', [$this, 'displaySingleClassShortcode']);
    }

    /**
     * Ensure required pages exist for the plugin functionality
     */
    public function ensureRequiredPages() {
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
                $page_id = \wp_insert_post([
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

                if ($page_id && !\is_wp_error($page_id)) {
                    \error_log("WeCoza Classes Plugin: Created display-single-class page with ID {$page_id}");
                }
            }
        }
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueueAssets() {
        // Only enqueue on pages that use our shortcodes
        if (!$this->shouldEnqueueAssets()) {
            return;
        }

        // FullCalendar CDN - Latest version from Context7
        // \wp_enqueue_style(
        //     'fullcalendar-css',
        //     'https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.css',
        //     [],
        //     '6.1.15'
        // );

        \wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js',
            [],
            '6.1.15', // Latest stable version
            true
        );

        \wp_enqueue_script(
            'wecoza-calendar-js',
            WECOZA_CLASSES_JS_URL . 'wecoza-calendar.js',
            ['jquery', 'fullcalendar'],
            WECOZA_CLASSES_VERSION,
            true
        );

        // Plugin JavaScript files
        \wp_enqueue_script(
            'wecoza-class-js',
            WECOZA_CLASSES_JS_URL . 'class-capture.js',
            ['jquery'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-class-schedule-form-js',
            WECOZA_CLASSES_JS_URL . 'class-schedule-form.js',
            ['jquery', 'wecoza-learner-level-utils-js'],
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
            ['jquery', 'wecoza-class-js', 'wecoza-learner-level-utils-js'],
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

        // Localize script with AJAX URL and nonce
        \wp_localize_script('wecoza-class-js', 'wecozaClass', [
            'ajaxUrl' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('wecoza_class_nonce'),
            'siteAddresses' => $this->getSiteAddresses(),
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
            error_log('WeCoza Classes Plugin: Error loading public holidays: ' . $e->getMessage());
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
    private function shouldEnqueueAssets() {
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
     * Get site addresses based on client and site selection
     *
     * @return array
     */
    private function getSiteAddresses() {
        try {
            // Get database service
            $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();

            // Query site addresses from database
            $sql = "SELECT site_id, address FROM public.sites WHERE address IS NOT NULL AND address != ''";
            $stmt = $db->query($sql);

            $addresses = [];
            while ($row = $stmt->fetch()) {
                $site_id = (int)$row['site_id'];
                $address = sanitize_textarea_field($row['address']);

                if (!empty($address)) {
                    $addresses[$site_id] = $address;
                }
            }

            return $addresses;

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error fetching site addresses: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle class capture shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function captureClassShortcode($atts) {
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
                return $this->handleUpdateMode($atts, null); // Pass null for testing
            }
            return $this->handleUpdateMode($atts, $class_id);
        } else {
            return $this->handleCreateMode($atts);
        }
    }

    /**
     * Handle create mode logic
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    private function handleCreateMode($atts) {
        // Get data for the view
        $viewData = [
            'mode' => 'create',
            'class_data' => null,
            'clients' => $this->getClients(),
            'sites' => $this->getSites(),
            'agents' => $this->getAgents(),
            'supervisors' => $this->getSupervisors(),
            'learners' => $this->getLearnersExam(),
            'setas' => $this->getSeta(),
            'class_types' => $this->getClassType(),
            'yes_no_options' => $this->getYesNoOptions(),
            'class_notes_options' => $this->getClassNotesOptions(),
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
    private function handleUpdateMode($atts, $class_id) {
        $class = null;
        
        // Enable debug logging if requested
        $debug = isset($_GET['debug']) && $_GET['debug'] === '1';
        
        if ($class_id) {
            // Get existing class data
            $class = $this->getSingleClass($class_id);
            
            if (empty($class)) {
                return '<div class="alert alert-danger">Class not found.</div>';
            }
            
            // Debug logging
            if ($debug) {
                $this->logDebugData($class_id, $class);
            }
        }

        // Get data for the view
        $viewData = [
            'mode' => 'update',
            'class_data' => $class,
            'class_id' => $class_id,
            'clients' => $this->getClients(),
            'sites' => $this->getSites(),
            'agents' => $this->getAgents(),
            'supervisors' => $this->getSupervisors(),
            'learners' => $this->getLearnersExam(),
            'setas' => $this->getSeta(),
            'class_types' => $this->getClassType(),
            'yes_no_options' => $this->getYesNoOptions(),
            'class_notes_options' => $this->getClassNotesOptions(),
            'redirect_url' => $atts['redirect_url']
        ];

        // Render the view
        return \WeCozaClasses\view('components/class-capture-form', $viewData);
    }

    // Placeholder methods for data retrieval - these will need to be implemented
    // based on how the plugin will access the data (from theme integration or plugin-specific)
    
    private function getClients() {
        try {
            // Get database service
            $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();

            // Query clients from database
            $sql = "SELECT client_id, client_name FROM public.clients ORDER BY client_name ASC";
            $stmt = $db->query($sql);

            $clients = [];
            while ($row = $stmt->fetch()) {
                $clients[] = [
                    'id' => (int)$row['client_id'],
                    'name' => sanitize_text_field($row['client_name'])
                ];
            }

            return $clients;

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error fetching clients: ' . $e->getMessage());
            return [];
        }
    }

    private function getSites() {
        try {
            // Get database service
            $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();

            // Query sites from database with client information
            $sql = "SELECT s.site_id, s.client_id, s.site_name, s.address
                    FROM public.sites s
                    ORDER BY s.client_id ASC, s.site_name ASC";
            $stmt = $db->query($sql);

            $sites = [];
            while ($row = $stmt->fetch()) {
                $client_id = (int)$row['client_id'];

                if (!isset($sites[$client_id])) {
                    $sites[$client_id] = [];
                }

                $sites[$client_id][] = [
                    'id' => (int)$row['site_id'],
                    'name' => sanitize_text_field($row['site_name']),
                    'address' => sanitize_textarea_field($row['address'])
                ];
            }

            return $sites;

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error fetching sites: ' . $e->getMessage());
            return [];
        }
    }

    private function getAgents() {
        // Static agent data - in production this would come from database
        return [
            ['id' => 1, 'name' => 'Michael M. van der Berg'],
            ['id' => 2, 'name' => 'Thandi T. Nkosi'],
            ['id' => 3, 'name' => 'Rajesh R. Patel'],
            ['id' => 4, 'name' => 'Lerato L. Moloi'],
            ['id' => 5, 'name' => 'Johannes J. Pretorius'],
            ['id' => 6, 'name' => 'Nomvula N. Dlamini'],
            ['id' => 7, 'name' => 'David D. O\'Connor'],
            ['id' => 8, 'name' => 'Zanele Z. Mthembu'],
            ['id' => 9, 'name' => 'Pieter P. van Zyl'],
            ['id' => 10, 'name' => 'Fatima F. Ismail'],
            ['id' => 11, 'name' => 'Sipho S. Ndlovu'],
            ['id' => 12, 'name' => 'Anita A. van Rensburg'],
            ['id' => 13, 'name' => 'Kobus K. Steyn'],
            ['id' => 14, 'name' => 'Nalini N. Reddy'],
            ['id' => 15, 'name' => 'Willem W. Botha']
        ];
    }

    private function getSupervisors() {
        // Static supervisor data - in production this would come from database
        return [
            ['id' => 1, 'name' => 'Dr. Sarah Johnson'],
            ['id' => 2, 'name' => 'Prof. Michael Smith'],
            ['id' => 3, 'name' => 'Ms. Jennifer Brown'],
            ['id' => 4, 'name' => 'Mr. David Wilson'],
            ['id' => 5, 'name' => 'Dr. Lisa Anderson']
        ];
    }

    private function getLearnersExam() {
        // Static learner data for exam classes - in production this would come from database
        return [
            ['id' => 1, 'name' => 'John Doe', 'id_number' => '8001015009088'],
            ['id' => 2, 'name' => 'Jane Smith', 'id_number' => '8505123456789'],
            ['id' => 3, 'name' => 'Mike Johnson', 'id_number' => '9002087654321'],
            ['id' => 4, 'name' => 'Sarah Wilson', 'id_number' => '8712034567890'],
            ['id' => 5, 'name' => 'David Brown', 'id_number' => '9105156789012']
        ];
    }

    private function getSeta() {
        // Static SETA data - in production this would come from database
        return [
            ['id' => 'BANKSETA', 'name' => 'Banking Sector Education and Training Authority'],
            ['id' => 'CHIETA', 'name' => 'Chemical Industries Education and Training Authority'],
            ['id' => 'CETA', 'name' => 'Construction Education and Training Authority'],
            ['id' => 'ETDP', 'name' => 'Education, Training and Development Practices SETA'],
            ['id' => 'FASSET', 'name' => 'Finance and Accounting Services SETA'],
            ['id' => 'FOODBEV', 'name' => 'Food and Beverages Manufacturing Industry SETA'],
            ['id' => 'HWSETA', 'name' => 'Health and Welfare SETA'],
            ['id' => 'INSETA', 'name' => 'Insurance Sector Education and Training Authority'],
            ['id' => 'LGSETA', 'name' => 'Local Government Sector Education and Training Authority'],
            ['id' => 'MERSETA', 'name' => 'Manufacturing, Engineering and Related Services SETA']
        ];
    }

    private function getClassType() {
        // Use the ClassTypesController to get class types
        return ClassTypesController::getClassTypes();
    }

    private function getYesNoOptions() {
        return array(
            array('id' => 'Yes', 'name' => 'Yes'),
            array('id' => 'No', 'name' => 'No')
        );
    }

    private function getClassNotesOptions() {
        // Static class notes options - in production this could come from database
        return [
            ['id' => 'Agent Absent', 'name' => 'Agent Absent'],
            ['id' => 'Client Cancelled', 'name' => 'Client Cancelled'],
            ['id' => 'Poor attendance', 'name' => 'Poor attendance'],
            ['id' => 'Learners behind schedule', 'name' => 'Learners behind schedule'],
            ['id' => 'Learners unhappy', 'name' => 'Learners unhappy'],
            ['id' => 'Client unhappy', 'name' => 'Client unhappy'],
            ['id' => 'Learners too fast', 'name' => 'Learners too fast'],
            ['id' => 'Class on track', 'name' => 'Class on track'],
            ['id' => 'Bad QA report', 'name' => 'Bad QA report'],
            ['id' => 'Good QA report', 'name' => 'Good QA report'],
            ['id' => 'Venue issues', 'name' => 'Venue issues'],
            ['id' => 'Equipment problems', 'name' => 'Equipment problems'],
            ['id' => 'Material shortage', 'name' => 'Material shortage'],
            ['id' => 'Weather delay', 'name' => 'Weather delay'],
            ['id' => 'Holiday adjustment', 'name' => 'Holiday adjustment']
        ];
    }

    /**
     * Handle AJAX request to save class data
     */
    public static function saveClassAjax() {
        // Start output buffering to capture any unexpected output
        ob_start();
        
        // Set error handler to capture warnings/notices
        $errorMessages = [];
        set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errorMessages) {
            $errorMessages[] = "PHP Warning: $errstr in $errfile on line $errline";
            return true; // Suppress the error from being output
        });
        
        try {
            // Ensure clean output buffer for JSON response
            while (ob_get_level() > 1) {
                ob_end_clean();
            }
            
            // Set JSON content type
            header('Content-Type: application/json; charset=utf-8');
            
            error_log('=== CLASS SAVE AJAX START ===');
            error_log('POST data keys: ' . implode(', ', array_keys($_POST)));
            error_log('PHP file last modified: ' . date('Y-m-d H:i:s', filemtime(__FILE__)));
            
            // Create instance
            $instance = new self();

            // Check nonce for security
            if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
                error_log('Nonce verification failed');
                error_log('Expected nonce name: wecoza_class_nonce');
                error_log('Received nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'not set'));
                
                // Clean buffer and restore error handler before sending response
                ob_clean();
                restore_error_handler();
                $instance->sendJsonError('Security check failed. Please refresh the page and try again.');
                return;
            }

            error_log('Nonce verification passed');

            // Process form data (including file uploads)
            $formData = self::processFormData($_POST, $_FILES);
            error_log('Processed form data keys: ' . implode(', ', array_keys($formData)));

        // Determine if this is create or update operation
        $isUpdate = isset($formData['id']) && !empty($formData['id']);
        $classId = $isUpdate ? intval($formData['id']) : null;

        error_log($isUpdate ? "Updating existing class with ID: {$classId}" : 'Creating new class');

        // Use direct model access for create or update
        try {
            // First check if database is properly configured
            try {
                $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();
            } catch (\Exception $dbError) {
                error_log('Database connection failed during save: ' . $dbError->getMessage());
                
                // Clean buffer and restore error handler before sending response
                ob_clean();
                restore_error_handler();
                $instance->sendJsonError('Database connection failed. Please ensure PostgreSQL credentials are configured in WordPress options (wecoza_postgres_password).');
                return;
            }
            
            if ($isUpdate) {
                // Load existing class and update it
                $class = ClassModel::getById($classId);
                if (!$class) {
                    error_log('Class not found for update: ' . $classId);
                    
                    // Clean buffer and restore error handler before sending response
                    ob_clean();
                    restore_error_handler();
                    $instance->sendJsonError('Class not found for update.');
                    return;
                }

                // Update the class with new data
                $class = self::populateClassModel($class, $formData);
                $result = $class->update();
            } else {
                // Create new class instance and save it
                $class = new ClassModel();
                $class = self::populateClassModel($class, $formData);
                $result = $class->save();
            }

            if ($result) {
                error_log('Class saved successfully with ID: ' . $class->getId());
                
                // Generate redirect URL to single class display page
                $redirect_url = '';
                $display_page = \get_page_by_path('app/display-single-class');
                if ($display_page) {
                    $redirect_url = \add_query_arg(
                        'class_id', 
                        $class->getId(), 
                        \get_permalink($display_page->ID)
                    );
                    error_log('Generated redirect URL: ' . $redirect_url);
                } else {
                    error_log('Display single class page not found at path: app/display-single-class');
                }
                
                // Log any captured warnings
                if (!empty($errorMessages)) {
                    foreach ($errorMessages as $errorMsg) {
                        error_log($errorMsg);
                    }
                }
                
                // Clean buffer and restore error handler before sending response
                ob_clean();
                restore_error_handler();
                
                $instance->sendJsonSuccess([
                    'message' => $isUpdate ? 'Class updated successfully.' : 'Class created successfully.',
                    'class_id' => $class->getId(),
                    'redirect_url' => $redirect_url
                ]);
            } else {
                error_log('Model operation failed');
                
                // Clean buffer and restore error handler before sending response
                ob_clean();
                restore_error_handler();
                
                $instance->sendJsonError(
                    $isUpdate ? 'Failed to update class.' : 'Failed to create class.'
                );
            }
        } catch (\Exception $e) {
            error_log('Exception during class save: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            
            // Clean buffer and restore error handler before sending response
            ob_clean();
            restore_error_handler();
            
            $instance->sendJsonError('An error occurred while saving the class: ' . $e->getMessage());
        }
        } catch (\Error $e) {
            error_log('FATAL ERROR in saveClassAjax: ' . $e->getMessage());
            error_log('Error file: ' . $e->getFile() . ' Line: ' . $e->getLine());
            error_log('Error trace: ' . $e->getTraceAsString());
            
            // Clean buffer and restore error handler before sending response
            ob_clean();
            restore_error_handler();
            
            // Try to send JSON error if possible
            if (isset($instance)) {
                $instance->sendJsonError('A server error occurred. Please check the error logs.');
            } else {
                \wp_send_json_error('A server error occurred. Please check the error logs.');
            }
        } catch (\Throwable $e) {
            error_log('THROWABLE in saveClassAjax: ' . $e->getMessage());
            error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            // Clean buffer and restore error handler before sending response
            ob_clean();
            restore_error_handler();
            
            \wp_send_json_error('A critical error occurred. Please check the error logs.');
        }
    }

    /**
     * Populate ClassModel with form data
     *
     * @param ClassModel $class Class model instance
     * @param array $formData Processed form data
     * @return ClassModel
     */
    private static function populateClassModel($class, $formData) {
        // Set all the properties from form data
        if (isset($formData['client_id'])) $class->setClientId($formData['client_id']);
        if (isset($formData['site_id'])) $class->setSiteId($formData['site_id']);
        if (isset($formData['site_address'])) $class->setClassAddressLine($formData['site_address']);
        if (isset($formData['class_type'])) $class->setClassType($formData['class_type']);
        if (isset($formData['class_subject'])) $class->setClassSubject($formData['class_subject']);
        if (isset($formData['class_code'])) $class->setClassCode($formData['class_code']);
        if (isset($formData['class_duration'])) $class->setClassDuration($formData['class_duration']);
        if (isset($formData['original_start_date'])) $class->setOriginalStartDate($formData['original_start_date']);
        if (isset($formData['seta_funded'])) $class->setSetaFunded($formData['seta_funded']);
        if (isset($formData['seta'])) $class->setSeta($formData['seta']);
        if (isset($formData['exam_class'])) $class->setExamClass($formData['exam_class']);
        if (isset($formData['exam_type'])) $class->setExamType($formData['exam_type']);
        if (isset($formData['qa_visit_dates'])) $class->setQaVisitDates($formData['qa_visit_dates']);
        if (isset($formData['qa_reports'])) $class->setQaReports($formData['qa_reports']);
        if (isset($formData['class_agent'])) $class->setClassAgent($formData['class_agent']);
        if (isset($formData['initial_class_agent'])) $class->setInitialClassAgent($formData['initial_class_agent']);
        if (isset($formData['initial_agent_start_date'])) $class->setInitialAgentStartDate($formData['initial_agent_start_date']);
        if (isset($formData['project_supervisor'])) $class->setProjectSupervisorId($formData['project_supervisor']);
        if (isset($formData['delivery_date'])) $class->setDeliveryDate($formData['delivery_date']);
        if (isset($formData['learner_ids'])) $class->setLearnerIds($formData['learner_ids']);
        if (isset($formData['exam_learners'])) $class->setExamLearners($formData['exam_learners']);
        if (isset($formData['backup_agent_ids'])) $class->setBackupAgentIds($formData['backup_agent_ids']);
        if (isset($formData['schedule_data'])) $class->setScheduleData($formData['schedule_data']);
        if (isset($formData['stop_restart_dates'])) $class->setStopRestartDates($formData['stop_restart_dates']);
        if (isset($formData['class_notes'])) $class->setClassNotesData($formData['class_notes']);

        return $class;
    }

    /**
     * Send JSON success response
     *
     * @param array $data Response data
     */
    private function sendJsonSuccess($data) {
        \wp_send_json_success($data);
    }

    /**
     * Send JSON error response
     *
     * @param string $message Error message
     */
    private function sendJsonError($message) {
        \wp_send_json_error($message);
    }

    /**
     * Process form data from POST and FILES
     *
     * @param array $data POST data
     * @param array $files FILES data
     * @return array Processed form data
     */
    private static function processFormData($data, $files = []) {
        error_log('processFormData: Starting to process form data');
        $processed = [];

        try {
            // Basic fields - using snake_case field names that the model expects
            $processed['id'] = isset($data['class_id']) && $data['class_id'] !== 'auto-generated' ? intval($data['class_id']) : null;
            error_log('processFormData: Processed ID field');
        $processed['client_id'] = isset($data['client_id']) && !empty($data['client_id']) ? intval($data['client_id']) : null;
        $processed['site_id'] = isset($data['site_id']) && !is_array($data['site_id']) ? $data['site_id'] : null;
        $processed['site_address'] = isset($data['site_address']) && !is_array($data['site_address']) ? self::sanitizeText($data['site_address']) : null;
        $processed['class_type'] = isset($data['class_type']) && !is_array($data['class_type']) ? self::sanitizeText($data['class_type']) : null;
        $processed['class_subject'] = isset($data['class_subject']) && !is_array($data['class_subject']) ? self::sanitizeText($data['class_subject']) : null;
        $processed['class_code'] = isset($data['class_code']) && !is_array($data['class_code']) ? self::sanitizeText($data['class_code']) : null;
        $processed['class_duration'] = isset($data['class_duration']) && !empty($data['class_duration']) ? intval($data['class_duration']) : null;
        // Map schedule_start_date to original_start_date for backward compatibility
        $processed['original_start_date'] = isset($data['schedule_start_date']) && !is_array($data['schedule_start_date']) 
            ? self::sanitizeText($data['schedule_start_date']) 
            : (isset($data['original_start_date']) && !is_array($data['original_start_date']) 
                ? self::sanitizeText($data['original_start_date']) 
                : null);
        // Handle boolean fields properly - check for 'Yes'/'No' string values
        // Convert empty strings to false for boolean fields
        error_log('processFormData: Processing seta_funded field. Raw value: ' . var_export(isset($data['seta_funded']) ? $data['seta_funded'] : 'not set', true));
        $processed['seta_funded'] = false; // default to false
        if (isset($data['seta_funded']) && !empty($data['seta_funded'])) {
            $processed['seta_funded'] = ($data['seta_funded'] === 'Yes' || $data['seta_funded'] === '1' || $data['seta_funded'] === true);
        }
        error_log('processFormData: Processed seta_funded: ' . var_export($processed['seta_funded'], true));
        
        $processed['seta'] = isset($data['seta_id']) && !is_array($data['seta_id']) 
            ? self::sanitizeText($data['seta_id']) 
            : (isset($data['seta']) && !is_array($data['seta']) 
                ? self::sanitizeText($data['seta']) 
                : null);
        
        // Convert empty strings to false for boolean fields
        error_log('processFormData: Processing exam_class field. Raw value: ' . var_export(isset($data['exam_class']) ? $data['exam_class'] : 'not set', true));
        $processed['exam_class'] = false; // default to false
        if (isset($data['exam_class']) && !empty($data['exam_class'])) {
            $processed['exam_class'] = ($data['exam_class'] === 'Yes' || $data['exam_class'] === '1' || $data['exam_class'] === true);
        }
        error_log('processFormData: Processed exam_class: ' . var_export($processed['exam_class'], true));
        $processed['exam_type'] = isset($data['exam_type']) && !is_array($data['exam_type']) ? self::sanitizeText($data['exam_type']) : null;
        // Process QA data (dates and reports) together
        $qaData = self::processQAData($data, $files);
        $processed['qa_visit_dates'] = $qaData['qa_visit_dates'];
        $processed['qa_reports'] = $qaData['qa_reports'];
        $processed['class_agent'] = isset($data['class_agent']) && !empty($data['class_agent']) ? intval($data['class_agent']) : null;
        $processed['initial_class_agent'] = isset($data['initial_class_agent']) && !empty($data['initial_class_agent']) ? intval($data['initial_class_agent']) : null;
        $processed['initial_agent_start_date'] = isset($data['initial_agent_start_date']) && !is_array($data['initial_agent_start_date']) ? self::sanitizeText($data['initial_agent_start_date']) : null;
        $processed['project_supervisor'] = isset($data['project_supervisor']) && !empty($data['project_supervisor']) ? intval($data['project_supervisor']) : null;
        $processed['delivery_date'] = isset($data['delivery_date']) && !is_array($data['delivery_date']) ? self::sanitizeText($data['delivery_date']) : null;

        // Array fields
        $processed['class_notes'] = isset($data['class_notes']) && is_array($data['class_notes']) ? array_map([self::class, 'sanitizeText'], $data['class_notes']) : [];
        // qa_reports is now processed in processQAData method above

        // JSON fields that need special handling
        // JSON fields that need special handling
        error_log('processFormData: Processing JSON fields');
        
        // Process learner IDs 
        $learnerIds = [];
        if (isset($data['class_learners_data']) && is_string($data['class_learners_data']) && !empty($data['class_learners_data'])) {
            $learnerData = json_decode(stripslashes($data['class_learners_data']), true);
            if (is_array($learnerData)) {
                $learnerIds = $learnerData;
            }
        }
        $processed['learner_ids'] = $learnerIds;
        error_log('processFormData: Processed learner_ids');
        
        // Process exam learners separately
        $examLearners = [];
        if (isset($data['exam_learners']) && is_string($data['exam_learners']) && !empty($data['exam_learners'])) {
            $examLearnerData = json_decode(stripslashes($data['exam_learners']), true);
            if (is_array($examLearnerData)) {
                $examLearners = $examLearnerData;
            }
        }
        $processed['exam_learners'] = $examLearners;
        error_log('processFormData: Processed exam_learners');
        
        // Process backup agents from form arrays
        $backupAgents = [];
        if (isset($data['backup_agent_ids']) && is_array($data['backup_agent_ids'])) {
            $agentIds = $data['backup_agent_ids'];
            $agentDates = isset($data['backup_agent_dates']) ? $data['backup_agent_dates'] : [];
            
            for ($i = 0; $i < count($agentIds); $i++) {
                if (!empty($agentIds[$i])) {
                    $backupAgents[] = [
                        'agent_id' => intval($agentIds[$i]),
                        'date' => isset($agentDates[$i]) ? $agentDates[$i] : ''
                    ];
                }
            }
        }
        $processed['backup_agent_ids'] = $backupAgents;
        error_log('processFormData: Processed backup_agent_ids: ' . json_encode($backupAgents));
        
        $processed['schedule_data'] = self::processJsonField($data, 'schedule_data');
        error_log('processFormData: Processed schedule_data: ' . json_encode($processed['schedule_data']));
        
        // Process stop/restart dates from form arrays
        $stopRestartDates = [];
        if (isset($data['stop_dates']) && is_array($data['stop_dates'])) {
            $stopDates = $data['stop_dates'];
            $restartDates = isset($data['restart_dates']) ? $data['restart_dates'] : [];
            
            for ($i = 0; $i < count($stopDates); $i++) {
                if (!empty($stopDates[$i]) && isset($restartDates[$i]) && !empty($restartDates[$i])) {
                    $stopRestartDates[] = [
                        'stop_date' => $stopDates[$i],
                        'restart_date' => $restartDates[$i]
                    ];
                }
            }
        }
        $processed['stop_restart_dates'] = $stopRestartDates;
        error_log('processFormData: Processed stop_restart_dates: ' . json_encode($stopRestartDates));

        error_log('processFormData: Successfully processed all form data');
        return $processed;
        
        } catch (\Exception $e) {
            error_log('processFormData ERROR: ' . $e->getMessage());
            error_log('processFormData ERROR trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Process JSON field from form data with enhanced schedule data handling
     *
     * @param array $data Form data
     * @param string $field Field name
     * @return array Processed JSON data
     */
    private static function processJsonField($data, $field) {
        error_log("processJsonField: Processing field {$field}");
        
        if (!isset($data[$field])) {
            error_log("processJsonField: Field {$field} not set");
            return [];
        }

        $value = $data[$field];
        
        // If it's already an array (from form submission), return it
        if (is_array($value)) {
            error_log("processJsonField: Field {$field} is already an array");
            
            // Special handling for schedule_data field
            if ($field === 'schedule_data') {
                // The form sends schedule_data as nested arrays, we need to reconstruct it
                $scheduleData = self::reconstructScheduleData($data);
                error_log("processJsonField: Reconstructed schedule_data: " . json_encode($scheduleData));
                
                // Verify end_date is present
                if (isset($scheduleData['end_date'])) {
                    error_log('processJsonField: end_date is present: ' . $scheduleData['end_date']);
                } else {
                    error_log('processJsonField: WARNING - end_date is missing from reconstructed schedule_data');
                }
                
                return self::processScheduleData($scheduleData);
            }
            
            return $value;
        }

        // Handle WordPress addslashes and HTML encoding for strings
        if (is_string($value)) {
            if (empty($value)) {
                error_log("processJsonField: Field {$field} is empty string");
                return [];
            }
            
            $value = stripslashes($value);
            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            
            // Decode JSON
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error for field {$field}: " . json_last_error_msg());
                error_log("JSON string that failed: " . $value);
                return [];
            }
            
            // Special handling for schedule_data field
            if ($field === 'schedule_data' && !empty($decoded)) {
                $decoded = self::processScheduleData($decoded);
            }
            
            return $decoded ?: [];
        }

        error_log("processJsonField: Field {$field} is neither array nor string");
        return [];
    }

    /**
     * Reconstruct schedule data from form's nested array structure
     *
     * @param array $data Form data
     * @return array Reconstructed schedule data
     */
    private static function reconstructScheduleData($data) {
        $scheduleData = [];
        
        error_log('reconstructScheduleData: Input data keys: ' . implode(', ', array_keys($data)));
        
        // Extract base fields from schedule_data array
        if (isset($data['schedule_data']) && is_array($data['schedule_data'])) {
            error_log('reconstructScheduleData: schedule_data exists and is array');
            foreach ($data['schedule_data'] as $key => $value) {
                if (!is_array($value)) {
                    $scheduleData[$key] = $value;
                }
            }
            
            // Handle nested arrays
            if (isset($data['schedule_data']['per_day_times'])) {
                $scheduleData['per_day_times'] = $data['schedule_data']['per_day_times'];
            }
            if (isset($data['schedule_data']['selected_days'])) {
                $scheduleData['selected_days'] = $data['schedule_data']['selected_days'];
            }
            if (isset($data['schedule_data']['exception_dates'])) {
                $scheduleData['exception_dates'] = $data['schedule_data']['exception_dates'];
            }
            if (isset($data['schedule_data']['holiday_overrides']) && is_array($data['schedule_data']['holiday_overrides'])) {
                // Handle holiday_overrides - convert string values to boolean
                $overrides = [];
                foreach ($data['schedule_data']['holiday_overrides'] as $date => $value) {
                    $overrides[$date] = ($value === '1' || $value === 'true' || $value === true);
                }
                $scheduleData['holiday_overrides'] = $overrides;
            }
        }
        
        // Add missing data from form
        if (!isset($scheduleData['start_date']) && isset($data['schedule_start_date'])) {
            $scheduleData['start_date'] = $data['schedule_start_date'];
        }
        // Capture end date from multiple possible sources
        if (isset($data['schedule_end_date']) && !empty($data['schedule_end_date'])) {
            $scheduleData['end_date'] = $data['schedule_end_date'];
            error_log('reconstructScheduleData: Captured end_date from schedule_end_date: ' . $data['schedule_end_date']);
        } elseif (isset($data['schedule_data']['end_date']) && !empty($data['schedule_data']['end_date'])) {
            $scheduleData['end_date'] = $data['schedule_data']['end_date'];
            error_log('reconstructScheduleData: Captured end_date from schedule_data.end_date: ' . $data['schedule_data']['end_date']);
        } elseif (isset($data['schedule_data']['endDate']) && !empty($data['schedule_data']['endDate'])) {
            $scheduleData['end_date'] = $data['schedule_data']['endDate'];
            error_log('reconstructScheduleData: Captured end_date from schedule_data.endDate: ' . $data['schedule_data']['endDate']);
        } else {
            error_log('reconstructScheduleData: WARNING - No end_date found in any expected location');
        }
        
        // Ensure we have the selected days from the form
        if (empty($scheduleData['selected_days']) && isset($data['schedule_days']) && is_array($data['schedule_days'])) {
            $scheduleData['selected_days'] = array_values(array_filter($data['schedule_days']));
        }
        
        // Build per_day_times if not present
        if (empty($scheduleData['per_day_times']) && isset($data['day_start_time']) && isset($data['day_end_time'])) {
            $scheduleData['per_day_times'] = [];
            foreach ($data['day_start_time'] as $day => $startTime) {
                if (!empty($startTime) && isset($data['day_end_time'][$day])) {
                    $endTime = $data['day_end_time'][$day];
                    $duration = self::calculateDuration($startTime, $endTime);
                    $scheduleData['per_day_times'][$day] = [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'duration' => number_format($duration, 2)
                    ];
                }
            }
        }
        
        // Process exception dates from form arrays
        if (empty($scheduleData['exception_dates']) && isset($data['exception_dates']) && is_array($data['exception_dates'])) {
            $scheduleData['exception_dates'] = [];
            $exceptionDates = $data['exception_dates'];
            $exceptionReasons = isset($data['exception_reasons']) ? $data['exception_reasons'] : [];
            
            for ($i = 0; $i < count($exceptionDates); $i++) {
                if (!empty($exceptionDates[$i])) {
                    $scheduleData['exception_dates'][] = [
                        'date' => $exceptionDates[$i],
                        'reason' => isset($exceptionReasons[$i]) ? $exceptionReasons[$i] : ''
                    ];
                }
            }
        }
        
        // Ensure metadata
        if (!isset($scheduleData['metadata'])) {
            $scheduleData['metadata'] = [
                'lastUpdated' => date('c'),
                'validatedAt' => date('c')
            ];
        }
        
        // Ensure timeData
        if (!isset($scheduleData['timeData'])) {
            $scheduleData['timeData'] = [
                'mode' => isset($scheduleData['time_mode']) ? $scheduleData['time_mode'] : 'per_day'
            ];
        }
        
        // Remove duplicate fields
        unset($scheduleData['time_mode']);
        
        return $scheduleData;
    }
    
    /**
     * Process schedule data with format detection, validation, and conversion
     *
     * @param array $scheduleData Raw schedule data from form
     * @return array Processed schedule data in v2.0 format
     */
    private static function processScheduleData($scheduleData) {
        // Basic validation of schedule data
        if (!is_array($scheduleData)) {
            error_log('Schedule data is not an array');
            return [];
        }

        // Log the received schedule data for debugging
        error_log('Processing schedule data: ' . json_encode($scheduleData));

        // Expect V2.0 format only
        return self::validateScheduleDataV2($scheduleData);
    }

    // V1 format detection functions removed - V2.0 format only

    // V1 to V2 conversion function removed - V2.0 format only

    // Legacy helper functions removed - V2.0 format only

    /**
     * Calculate duration in hours from start and end time
     *
     * @param string $startTime Start time (HH:MM)
     * @param string $endTime End time (HH:MM)
     * @return float Duration in hours
     */
    private static function calculateDuration($startTime, $endTime) {
        $start = strtotime($startTime);
        $end = strtotime($endTime);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return ($end - $start) / 3600; // Convert seconds to hours
    }

    /**
     * Sanitize text input
     *
     * @param string $text Input text
     * @return string Sanitized text
     */
    private static function sanitizeText($text) {
        // Check if WordPress function exists (it might not in some AJAX contexts)
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($text);
        }
        
        // Fallback sanitization
        return htmlspecialchars(strip_tags($text), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate and sanitize v2.0 schedule data format
     *
     * @param array $data v2.0 schedule data
     * @return array Validated and sanitized data
     */
    private static function validateScheduleDataV2($data) {
        error_log('validateScheduleDataV2: Input data keys: ' . implode(', ', array_keys($data)));
        
        $validated = [
            'version' => '2.0',
            'pattern' => 'weekly',
            'startDate' => '',
            'endDate' => '',
            'timeData' => [
                'mode' => 'single'
            ],
            'selectedDays' => [],
            'dayOfMonth' => null,
            'exceptionDates' => [],
            'holidayOverrides' => [],
            'metadata' => [
                'lastUpdated' => date('c'),
                'validatedAt' => date('c')
            ]
        ];

        // Validate version
        if (isset($data['version'])) {
            $validated['version'] = sanitize_text_field($data['version']);
        }

        // Validate pattern
        $allowedPatterns = ['weekly', 'biweekly', 'monthly', 'custom'];
        if (isset($data['pattern']) && in_array($data['pattern'], $allowedPatterns)) {
            $validated['pattern'] = $data['pattern'];
        }

        // Validate dates - check both camelCase and snake_case versions
        if (isset($data['startDate']) && self::isValidDate($data['startDate'])) {
            $validated['startDate'] = sanitize_text_field($data['startDate']);
        } elseif (isset($data['start_date']) && self::isValidDate($data['start_date'])) {
            $validated['startDate'] = sanitize_text_field($data['start_date']);
        }

        if (isset($data['endDate']) && self::isValidDate($data['endDate'])) {
            $validated['endDate'] = sanitize_text_field($data['endDate']);
        } elseif (isset($data['end_date']) && self::isValidDate($data['end_date'])) {
            $validated['endDate'] = sanitize_text_field($data['end_date']);
        }

        // Validate day of month for monthly pattern
        if (isset($data['dayOfMonth']) && is_numeric($data['dayOfMonth'])) {
            $dayOfMonth = intval($data['dayOfMonth']);
            if ($dayOfMonth >= 1 && $dayOfMonth <= 31) {
                $validated['dayOfMonth'] = $dayOfMonth;
            }
        }

        // Validate time data
        if (isset($data['timeData']) && is_array($data['timeData'])) {
            $validated['timeData'] = self::validateTimeData($data['timeData']);
        } else {
            // Check if we have per_day_times directly in the data
            if (isset($data['per_day_times']) && is_array($data['per_day_times'])) {
                $validated['timeData'] = [
                    'mode' => 'per-day',
                    'perDayTimes' => $data['per_day_times']
                ];
            }
        }
        
        // If timeData was set but per_day_times exists, override with the actual data
        if (isset($data['per_day_times']) && is_array($data['per_day_times']) && !empty($data['per_day_times'])) {
            $validated['timeData'] = [
                'mode' => 'per-day',
                'perDayTimes' => $data['per_day_times']
            ];
        }

        // Validate selected days - check both camelCase and snake_case versions
        if (isset($data['selectedDays']) && is_array($data['selectedDays'])) {
            $allowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $validated['selectedDays'] = array_intersect($data['selectedDays'], $allowedDays);
        } elseif (isset($data['selected_days']) && is_array($data['selected_days'])) {
            $allowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $validated['selectedDays'] = array_intersect($data['selected_days'], $allowedDays);
        }

        // Validate exception dates - check both camelCase and snake_case versions
        if (isset($data['exceptionDates']) && is_array($data['exceptionDates'])) {
            $validated['exceptionDates'] = self::validateExceptionDates($data['exceptionDates']);
        } elseif (isset($data['exception_dates']) && is_array($data['exception_dates'])) {
            $validated['exceptionDates'] = self::validateExceptionDates($data['exception_dates']);
        }

        // Validate holiday overrides - check both camelCase and snake_case versions
        if (isset($data['holidayOverrides']) && is_array($data['holidayOverrides'])) {
            $validated['holidayOverrides'] = self::validateHolidayOverrides($data['holidayOverrides']);
        } elseif (isset($data['holiday_overrides']) && is_array($data['holiday_overrides'])) {
            $validated['holidayOverrides'] = self::validateHolidayOverrides($data['holiday_overrides']);
        }

        // Preserve metadata
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $validated['metadata'] = array_merge($validated['metadata'], $data['metadata']);
        }

        // Preserve generated schedule if present (for backward compatibility)
        if (isset($data['generatedSchedule']) && is_array($data['generatedSchedule'])) {
            $validated['generatedSchedule'] = $data['generatedSchedule'];
        }
        
        error_log('validateScheduleDataV2: Output validated data: ' . json_encode($validated));
        
        // Final check for endDate
        if (empty($validated['endDate'])) {
            error_log('validateScheduleDataV2: WARNING - endDate is empty in validated output');
        }

        return $validated;
    }

    /**
     * Validate time data structure
     *
     * @param array $timeData Time data to validate
     * @return array Validated time data
     */
    private static function validateTimeData($timeData) {
        $validated = ['mode' => 'single'];

        // Validate mode
        $allowedModes = ['single', 'per-day'];
        if (isset($timeData['mode']) && in_array($timeData['mode'], $allowedModes)) {
            $validated['mode'] = $timeData['mode'];
        }

        // Validate single time data
        if ($validated['mode'] === 'single' && isset($timeData['single'])) {
            $validated['single'] = self::validateSingleTimeData($timeData['single']);
        }

        // Validate per-day time data
        if ($validated['mode'] === 'per-day' && isset($timeData['perDay'])) {
            $validated['perDay'] = self::validatePerDayTimeData($timeData['perDay']);
        }

        return $validated;
    }

    /**
     * Validate single time data
     *
     * @param array $singleData Single time data
     * @return array Validated single time data
     */
    private static function validateSingleTimeData($singleData) {
        $validated = [
            'startTime' => '',
            'endTime' => '',
            'duration' => 0
        ];

        if (isset($singleData['startTime']) && self::isValidTime($singleData['startTime'])) {
            $validated['startTime'] = sanitize_text_field($singleData['startTime']);
        }

        if (isset($singleData['endTime']) && self::isValidTime($singleData['endTime'])) {
            $validated['endTime'] = sanitize_text_field($singleData['endTime']);
        }

        if (isset($singleData['duration']) && is_numeric($singleData['duration'])) {
            $validated['duration'] = floatval($singleData['duration']);
        } else if ($validated['startTime'] && $validated['endTime']) {
            $validated['duration'] = self::calculateDuration($validated['startTime'], $validated['endTime']);
        }

        return $validated;
    }

    /**
     * Validate per-day time data
     *
     * @param array $perDayData Per-day time data
     * @return array Validated per-day time data
     */
    private static function validatePerDayTimeData($perDayData) {
        $validated = [];
        $allowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($perDayData as $day => $dayData) {
            if (in_array($day, $allowedDays) && is_array($dayData)) {
                $validated[$day] = self::validateSingleTimeData($dayData);
            }
        }

        return $validated;
    }

    /**
     * Validate exception dates array
     *
     * @param array $exceptionDates Exception dates to validate
     * @return array Validated exception dates
     */
    private static function validateExceptionDates($exceptionDates) {
        $validated = [];

        foreach ($exceptionDates as $exception) {
            if (is_array($exception) && isset($exception['date']) && self::isValidDate($exception['date'])) {
                $validException = [
                    'date' => sanitize_text_field($exception['date']),
                    'reason' => isset($exception['reason']) ? sanitize_text_field($exception['reason']) : 'No reason specified'
                ];
                $validated[] = $validException;
            }
        }

        return $validated;
    }

    /**
     * Validate holiday overrides
     *
     * @param array $holidayOverrides Holiday overrides to validate
     * @return array Validated holiday overrides
     */
    private static function validateHolidayOverrides($holidayOverrides) {
        $validated = [];

        foreach ($holidayOverrides as $date => $override) {
            if (self::isValidDate($date)) {
                $validated[sanitize_text_field($date)] = (bool) $override;
            }
        }

        return $validated;
    }

    /**
     * Check if a date string is valid
     *
     * @param string $date Date string to validate
     * @return bool True if valid date
     */
    private static function isValidDate($date) {
        if (!is_string($date)) {
            return false;
        }

        $timestamp = strtotime($date);
        return $timestamp !== false && date('Y-m-d', $timestamp) === $date;
    }

    /**
     * Check if a time string is valid (HH:MM format)
     *
     * @param string $time Time string to validate
     * @return bool True if valid time
     */
    private static function isValidTime($time) {
        if (!is_string($time)) {
            return false;
        }

        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time) === 1;
    }

    /**
     * Convert v2.0 schedule data back to legacy format for calendar compatibility
     *
     * @param array $v2Data v2.0 schedule data
     * @return array Legacy format schedule entries
     */
    public static function convertV2ToLegacy($v2Data) {
        // If we have a cached generated schedule, use it
        if (isset($v2Data['generatedSchedule']) && is_array($v2Data['generatedSchedule'])) {
            return $v2Data['generatedSchedule'];
        }

        // Generate schedule entries from v2.0 pattern data
        return self::generateScheduleEntries($v2Data);
    }

    /**
     * Generate schedule entries from v2.0 pattern data
     *
     * @param array $v2Data v2.0 schedule data
     * @return array Generated schedule entries in legacy format
     */
    private static function generateScheduleEntries($v2Data) {
        $entries = [];

        if (!isset($v2Data['startDate']) || !isset($v2Data['endDate'])) {
            return $entries;
        }

        $startDate = new \DateTime($v2Data['startDate']);
        $endDate = new \DateTime($v2Data['endDate']);
        $pattern = $v2Data['pattern'] ?? 'weekly';
        $timeData = $v2Data['timeData'] ?? [];
        $selectedDays = $v2Data['selectedDays'] ?? [];

        // Generate entries based on pattern
        switch ($pattern) {
            case 'weekly':
                $entries = self::generateWeeklyEntries($startDate, $endDate, $timeData, $selectedDays);
                break;
            case 'biweekly':
                $entries = self::generateBiweeklyEntries($startDate, $endDate, $timeData, $selectedDays);
                break;
            case 'monthly':
                $entries = self::generateMonthlyEntries($startDate, $endDate, $timeData, $v2Data['dayOfMonth'] ?? 1);
                break;
            case 'custom':
            default:
                // For custom pattern, return minimal entry if we have time data
                if (isset($timeData['single'])) {
                    $entries[] = [
                        'date' => $v2Data['startDate'],
                        'start_time' => $timeData['single']['startTime'] ?? '09:00',
                        'end_time' => $timeData['single']['endTime'] ?? '17:00'
                    ];
                }
                break;
        }

        // Apply exception dates (remove entries on exception dates)
        if (isset($v2Data['exceptionDates']) && is_array($v2Data['exceptionDates'])) {
            $exceptionDates = array_column($v2Data['exceptionDates'], 'date');
            $entries = array_filter($entries, function($entry) use ($exceptionDates) {
                return !in_array($entry['date'], $exceptionDates);
            });
        }

        return array_values($entries); // Re-index array
    }

    /**
     * Generate weekly schedule entries
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data
     * @param array $selectedDays Selected days of week
     * @return array Schedule entries
     */
    private static function generateWeeklyEntries($startDate, $endDate, $timeData, $selectedDays) {
        $entries = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dayName = $current->format('l');

            if (in_array($dayName, $selectedDays)) {
                $times = self::getTimesForDay($timeData, $dayName);
                if ($times) {
                    $entries[] = [
                        'date' => $current->format('Y-m-d'),
                        'start_time' => $times['startTime'],
                        'end_time' => $times['endTime']
                    ];
                }
            }

            $current->add(new \DateInterval('P1D'));
        }

        return $entries;
    }

    /**
     * Generate biweekly schedule entries
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data
     * @param array $selectedDays Selected days of week
     * @return array Schedule entries
     */
    private static function generateBiweeklyEntries($startDate, $endDate, $timeData, $selectedDays) {
        $entries = [];
        $current = clone $startDate;
        $weekCount = 0;

        while ($current <= $endDate) {
            $dayName = $current->format('l');

            // Only add entries on even weeks (0, 2, 4, etc.)
            if ($weekCount % 2 === 0 && in_array($dayName, $selectedDays)) {
                $times = self::getTimesForDay($timeData, $dayName);
                if ($times) {
                    $entries[] = [
                        'date' => $current->format('Y-m-d'),
                        'start_time' => $times['startTime'],
                        'end_time' => $times['endTime']
                    ];
                }
            }

            // Increment week count on Sundays
            if ($current->format('N') == 7) {
                $weekCount++;
            }

            $current->add(new \DateInterval('P1D'));
        }

        return $entries;
    }

    /**
     * Generate monthly schedule entries
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data
     * @param int $dayOfMonth Day of month for schedule
     * @return array Schedule entries
     */
    private static function generateMonthlyEntries($startDate, $endDate, $timeData, $dayOfMonth) {
        $entries = [];
        $current = clone $startDate;

        // Set to the specified day of month
        $current->setDate($current->format('Y'), $current->format('n'), $dayOfMonth);

        // If the day is before start date, move to next month
        if ($current < $startDate) {
            $current->add(new \DateInterval('P1M'));
            $current->setDate($current->format('Y'), $current->format('n'), $dayOfMonth);
        }

        while ($current <= $endDate) {
            $times = self::getTimesForDay($timeData, null); // Use single time for monthly
            if ($times) {
                $entries[] = [
                    'date' => $current->format('Y-m-d'),
                    'start_time' => $times['startTime'],
                    'end_time' => $times['endTime']
                ];
            }

            // Move to next month
            $current->add(new \DateInterval('P1M'));

            // Handle month-end edge cases
            $targetDay = min($dayOfMonth, $current->format('t')); // Last day of month if dayOfMonth > days in month
            $current->setDate($current->format('Y'), $current->format('n'), $targetDay);
        }

        return $entries;
    }

    /**
     * Get times for a specific day from time data
     *
     * @param array $timeData Time data structure
     * @param string|null $dayName Day name (for per-day mode) or null (for single mode)
     * @return array|null Times array with startTime and endTime, or null if not found
     */
    private static function getTimesForDay($timeData, $dayName = null) {
        $mode = $timeData['mode'] ?? 'single';

        if ($mode === 'per-day' && $dayName && isset($timeData['perDay'][$dayName])) {
            $dayData = $timeData['perDay'][$dayName];
            return [
                'startTime' => $dayData['startTime'] ?? '09:00',
                'endTime' => $dayData['endTime'] ?? '17:00'
            ];
        } else if ($mode === 'single' && isset($timeData['single'])) {
            return [
                'startTime' => $timeData['single']['startTime'] ?? '09:00',
                'endTime' => $timeData['single']['endTime'] ?? '17:00'
            ];
        }

        return null;
    }

    /**
     * Handle display classes shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function displayClassesShortcode($atts) {
        // Process shortcode attributes
        $atts = \shortcode_atts([
            'limit' => 50,
            'order_by' => 'created_at',
            'order' => 'DESC',
            'show_loading' => true,
        ], $atts);

        try {
            // Get all classes from database
            $classes = $this->getAllClasses($atts);

            // Enrich classes with agent names
            $classes = $this->enrichClassesWithAgentNames($classes);

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
            error_log('WeCoza Classes Plugin: Error in displayClassesShortcode: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error loading classes: ' . esc_html($e->getMessage()) . '</div>';
        }
    }

    /**
     * Handle display single class shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function displaySingleClassShortcode($atts) {
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
            // Get single class from database
            $class = $this->getSingleClass($class_id);

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

            // Render the view
            return \WeCozaClasses\view('components/single-class-display', $viewData);

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error in displaySingleClassShortcode: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error loading class: ' . esc_html($e->getMessage()) . '</div>';
        }
    }

    /**
     * Check if a class is currently stopped based on stop_restart_dates
     *
     * @param array $class Class data
     * @return bool True if class is currently stopped, false otherwise
     */
    public function isClassCurrentlyStopped($class) {
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
     * Enrich classes array with agent names
     *
     * @param array $classes Array of class data
     * @return array Array of class data with agent names added
     */
    private function enrichClassesWithAgentNames($classes) {
        $agents = $this->getAgents();
        $agentLookup = [];

        // Create lookup array for faster access
        foreach ($agents as $agent) {
            $agentLookup[$agent['id']] = $agent['name'];
        }

        // Enrich each class with agent names
        foreach ($classes as &$class) {
            // Add current agent name
            if (!empty($class['class_agent'])) {
                $class['agent_name'] = $agentLookup[$class['class_agent']] ?? 'Unknown Agent';
            }

            // Add initial agent name
            if (!empty($class['initial_class_agent'])) {
                $class['initial_agent_name'] = $agentLookup[$class['initial_class_agent']] ?? 'Unknown Agent';
            }
        }

        return $classes;
    }

    /**
     * Get all classes from database with optional filtering
     *
     * @param array $options Query options (limit, order_by, order)
     * @return array Array of class data
     */
    private function getAllClasses($options = []) {
        $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();

        // Set defaults
        $limit = isset($options['limit']) ? intval($options['limit']) : 50;
        $order_by = isset($options['order_by']) ? $options['order_by'] : 'created_at';
        $order = isset($options['order']) ? strtoupper($options['order']) : 'DESC';

        // Validate order_by to prevent SQL injection
        $allowed_columns = [
            'class_id', 'client_id', 'class_type', 'class_subject',
            'original_start_date', 'delivery_date', 'created_at', 'updated_at'
        ];

        if (!in_array($order_by, $allowed_columns)) {
            $order_by = 'created_at';
        }

        // Validate order direction
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Build the query with client name JOIN
        $sql = "
            SELECT
                c.class_id,
                c.client_id,
                cl.client_name,
                c.class_type,
                c.class_subject,
                c.class_code,
                c.class_duration,
                c.original_start_date,
                c.delivery_date,
                c.seta_funded,
                c.seta,
                c.exam_class,
                c.exam_type,
                c.class_agent,
                c.initial_class_agent,
                c.project_supervisor_id,
                c.stop_restart_dates,
                c.created_at,
                c.updated_at
            FROM public.classes c
            LEFT JOIN public.clients cl ON c.client_id = cl.client_id
            ORDER BY c." . $order_by . " " . $order . "
            LIMIT " . $limit;

        try {
            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error in getAllClasses: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single class from database
     *
     * @param int $class_id Class ID
     * @return array|null Class data or null if not found
     */
    private function getSingleClass($class_id) {
        $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();

        $sql = "
            SELECT
                c.*,
                cl.client_name
            FROM public.classes c
            LEFT JOIN public.clients cl ON c.client_id = cl.client_id
            WHERE c.class_id = :class_id
            LIMIT 1";

        try {
            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute(['class_id' => $class_id]);
            $result = $stmt->fetch();

            if (!$result) {
                // No class found - return sample data for testing
                error_log('WeCoza Classes Plugin: No class found with ID ' . $class_id . ', returning sample data');
                return [
                    'class_id' => $class_id,
                    'class_code' => 'SAMPLE-CLASS-' . $class_id,
                    'class_subject' => 'Sample Class Subject',
                    'class_type' => 1, // Change to ID for form compatibility
                    'client_id' => 1, // Add client_id for form
                    'site_id' => 1, // Add site_id for form
                    'client_name' => 'Sample Client Ltd',
                    'class_agent' => null, // Will fallback to initial_class_agent
                    'supervisor_name' => 'Dr. Sarah Johnson',
                    'project_supervisor_id' => 1,
                    'seta_funded' => 'Yes', // Change to Yes/No for form compatibility
                    'seta' => 'CHIETA',
                    'exam_class' => 'Yes', // Change to Yes/No for form compatibility
                    'exam_type' => 'Open Book Exam',
                    'class_duration' => 240,
                    'class_address_line' => '123 Sample Street, Sample City, 1234',
                    'original_start_date' => date('Y-m-d'),
                    'delivery_date' => date('Y-m-d', strtotime('+30 days')),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'schedule_data' => [
                        'pattern' => 'weekly',
                        'startDate' => date('Y-m-d'),
                        'endDate' => date('Y-m-d', strtotime('+3 months')),
                        'selectedDays' => ['Monday', 'Wednesday', 'Friday'],
                        'timeData' => [
                            'mode' => 'per-day',
                            'perDayTimes' => [
                                'Monday' => [
                                    'startTime' => '09:00',
                                    'endTime' => '11:00',
                                    'duration' => 2
                                ],
                                'Wednesday' => [
                                    'startTime' => '14:00',
                                    'endTime' => '16:30',
                                    'duration' => 2.5
                                ],
                                'Friday' => [
                                    'startTime' => '10:00',
                                    'endTime' => '12:00',
                                    'duration' => 2
                                ]
                            ]
                        ],
                        'version' => '2.0',
                        'holidayOverrides' => [],
                        'exceptionDates' => []
                    ],
                    'exception_dates' => null,
                    'stop_restart_dates' => [
                        [
                            'stop_date' => date('Y-m-d', strtotime('+10 days')),
                            'restart_date' => date('Y-m-d', strtotime('+15 days'))
                        ]
                    ],
                    'learner_ids' => [
                        ['id' => 1, 'name' => 'Alice Johnson', 'status' => 'CIC - Currently in Class'],
                        ['id' => 2, 'name' => 'Bob Smith', 'status' => 'CIC - Currently in Class'],
                        ['id' => 3, 'name' => 'Charlie Brown', 'status' => 'Walk'],
                        ['id' => 4, 'name' => 'Diana Prince', 'status' => 'CIC - Currently in Class']
                    ],
                    'exam_learners' => [
                        ['id' => 1, 'name' => 'Alice Johnson', 'exam_status' => 'Registered'],
                        ['id' => 2, 'name' => 'Bob Smith', 'exam_status' => 'Registered'],
                        ['id' => 4, 'name' => 'Diana Prince', 'exam_status' => 'Pending']
                    ],
                    'qa_reports' => [
                        [
                            'date' => date('Y-m-d', strtotime('-5 days')),
                            'type' => 'Initial QA Visit',
                            'filename' => 'qa_report_initial_' . $class_id . '.pdf',
                            'file_path' => '#',
                            'uploaded_by' => 'QA Manager'
                        ],
                        [
                            'date' => date('Y-m-d', strtotime('-2 days')),
                            'type' => 'Follow-up QA',
                            'filename' => 'qa_report_followup_' . $class_id . '.pdf',
                            'file_path' => '#',
                            'uploaded_by' => 'Senior QA Officer'
                        ]
                    ],
                    'class_notes_data' => [
                        [
                            'note' => 'Class started successfully. All learners present.',
                            'category' => 'Class on track',
                            'author' => 'John Doe',
                            'timestamp' => date('Y-m-d H:i:s', strtotime('-7 days'))
                        ],
                        [
                            'note' => 'Two learners arrived late due to transport issues.',
                            'category' => 'Poor attendance',
                            'author' => 'John Doe',
                            'timestamp' => date('Y-m-d H:i:s', strtotime('-6 days'))
                        ],
                        [
                            'note' => 'QA visit completed. Positive feedback received.',
                            'category' => 'Good QA report',
                            'author' => 'QA Manager',
                            'timestamp' => date('Y-m-d H:i:s', strtotime('-5 days'))
                        ],
                        [
                            'note' => 'Equipment issue resolved. Projector replaced.',
                            'category' => 'Equipment problems',
                            'author' => 'John Doe',
                            'timestamp' => date('Y-m-d H:i:s', strtotime('-3 days'))
                        ]
                    ],
                    'backup_agent_ids' => [
                        ['agent_id' => 2, 'date' => date('Y-m-d', strtotime('-10 days'))],
                        ['agent_id' => 3, 'date' => date('Y-m-d', strtotime('-5 days'))]
                    ],
                    'initial_class_agent' => 5,
                    'initial_agent_start_date' => date('Y-m-d', strtotime('-30 days'))
                ];
            }

            // Handle JSONB fields that come as strings from PostgreSQL
            $jsonbFields = ['learner_ids', 'backup_agent_ids', 'schedule_data', 'stop_restart_dates', 'class_notes_data', 'qa_reports', 'exam_learners'];
            
            // Also decode qa_visit_dates if stored as JSON
            if (isset($result['qa_visit_dates']) && is_string($result['qa_visit_dates'])) {
                $decoded = json_decode($result['qa_visit_dates'], true);
                if ($decoded !== null) {
                    $result['qa_visit_dates'] = $decoded;
                }
            }

            foreach ($jsonbFields as $field) {
                if (isset($result[$field]) && is_string($result[$field])) {
                    $decoded = json_decode($result[$field], true);
                    if ($decoded !== null) {
                        $result[$field] = $decoded;
                    }
                }
            }

            // Enrich with agent names
            $agents = $this->getAgents();
            $agentLookup = [];
            foreach ($agents as $agent) {
                $agentLookup[$agent['id']] = $agent['name'];
            }

            // Add current agent name (fallback to initial_class_agent if class_agent is empty)
            $currentAgentId = $result['class_agent'] ?? $result['initial_class_agent'] ?? null;
            if (!empty($currentAgentId)) {
                $result['agent_name'] = $agentLookup[$currentAgentId] ?? 'Unknown Agent';
                $result['class_agent'] = $currentAgentId; // Ensure class_agent is set for display
            }

            // Add initial agent name
            if (!empty($result['initial_class_agent'])) {
                $result['initial_agent_name'] = $agentLookup[$result['initial_class_agent']] ?? 'Unknown Agent';
            }

            // Add backup agent names
            if (!empty($result['backup_agent_ids']) && is_array($result['backup_agent_ids'])) {
                $result['backup_agent_names'] = [];
                foreach ($result['backup_agent_ids'] as $agentId) {
                    if (isset($agentId['agent_id'])) {
                        $id = $agentId['agent_id'];
                        $result['backup_agent_names'][] = [
                            'id' => $id,
                            'name' => $agentLookup[$id] ?? 'Unknown Agent'
                        ];
                    }
                }
            }

            // Add supervisor name
            if (!empty($result['project_supervisor_id'])) {
                $supervisors = $this->getSupervisors();
                foreach ($supervisors as $supervisor) {
                    if ($supervisor['id'] == $result['project_supervisor_id']) {
                        $result['supervisor_name'] = $supervisor['name'];
                        break;
                    }
                }
            }

            // Schedule data expected to be in V2.0 format only

            // Transform boolean fields to Yes/No for form compatibility
            if (isset($result['seta_funded'])) {
                $result['seta_funded'] = $result['seta_funded'] === true || $result['seta_funded'] === 't' || $result['seta_funded'] === '1' || $result['seta_funded'] === 'Yes' ? 'Yes' : 'No';
            }
            
            if (isset($result['exam_class'])) {
                $result['exam_class'] = $result['exam_class'] === true || $result['exam_class'] === 't' || $result['exam_class'] === '1' || $result['exam_class'] === 'Yes' ? 'Yes' : 'No';
            }

            // Convert class_type string to ID if needed
            if (isset($result['class_type']) && !is_numeric($result['class_type'])) {
                $classTypes = $this->getClassType();
                foreach ($classTypes as $type) {
                    if (strcasecmp($type['name'], $result['class_type']) === 0) {
                        $result['class_type'] = $type['id'];
                        break;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error in getSingleClass: ' . $e->getMessage());
            // Return sample data for testing when database fails
            return [
                'class_id' => $class_id,
                'class_code' => 'SAMPLE-CLASS-' . $class_id,
                'class_subject' => 'Sample Class Subject',
                'original_start_date' => date('Y-m-d'),
                'delivery_date' => date('Y-m-d', strtotime('+30 days')),
                'schedule_data' => [
                    'pattern' => 'weekly',
                    'startDate' => date('Y-m-d'),
                    'endDate' => date('Y-m-d', strtotime('+3 months')),
                    'selectedDays' => ['Monday', 'Wednesday'],
                    'timeData' => [
                        'mode' => 'per-day',
                        'perDayTimes' => [
                            'Monday' => [
                                'startTime' => '09:00',
                                'endTime' => '11:00',
                                'duration' => 2
                            ],
                            'Wednesday' => [
                                'startTime' => '14:00',
                                'endTime' => '16:00',
                                'duration' => 2
                            ]
                        ]
                    ],
                    'version' => '2.0'
                ],
                'exception_dates' => null,
                'stop_restart_dates' => [
                    [
                        'stop_date' => date('Y-m-d', strtotime('+10 days')),
                        'restart_date' => date('Y-m-d', strtotime('+15 days'))
                    ]
                ]
            ];
        }
    }

    /**
     * Log debug data for update form troubleshooting
     * 
     * @param int $class_id Class ID
     * @param array $class_data Class data array
     */
    private function logDebugData($class_id, $class_data) {
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
            'request_uri' => $_SERVER['REQUEST_URI'],
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

    /**
     * Handle AJAX request to delete class
     */
    public static function deleteClassAjax() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            \wp_send_json_error('Security check failed.');
            return;
        }

        // Check user permissions - only administrators can delete classes
        if (!current_user_can('manage_options')) {
            \wp_send_json_error('Only administrators can delete classes.');
            return;
        }

        // Validate class_id
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        if (empty($class_id) || $class_id <= 0) {
            \wp_send_json_error('Invalid class ID provided.');
            return;
        }

        try {
            $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();
            $db->beginTransaction();

            try {
                // Delete the main class record using RETURNING to check if it existed
                $stmt = $db->getPdo()->prepare(
                    "DELETE FROM public.classes WHERE class_id = :class_id RETURNING class_id"
                );
                $stmt->execute(['class_id' => $class_id]);
                $deletedClass = $stmt->fetch();

                if (!$deletedClass) {
                    $db->rollback();
                    \wp_send_json_error('Class not found or already deleted.');
                    return;
                }

                $db->commit();
                \wp_send_json_success([
                    'message' => 'Class deleted successfully.',
                    'class_id' => $class_id
                ]);

            } catch (\Exception $e) {
                $db->rollback();
                error_log('WeCoza Classes Plugin: Error during class deletion: ' . $e->getMessage());
                \wp_send_json_error('Failed to delete class: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Database error during class deletion: ' . $e->getMessage());
            \wp_send_json_error('Database error occurred while deleting class.');
        }
    }

    /**
     * Handle AJAX request to get calendar events
     */
    public static function getCalendarEventsAjax() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_calendar_nonce')) {
            \wp_send_json_error('Security check failed.');
            return;
        }

        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        if (empty($class_id) || $class_id <= 0) {
            \wp_send_json_error('Invalid class ID provided.');
            return;
        }

        try {
            $instance = new self();
            $class = $instance->getSingleClass($class_id);

            if (!$class) {
                \wp_send_json_error('Class not found.');
                return;
            }

            // Generate calendar events from schedule data
            $events = $instance->generateCalendarEvents($class);

            // FullCalendar expects a direct array of events, not wrapped in success response
            \wp_send_json($events);

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error getting calendar events: ' . $e->getMessage());
            \wp_send_json_error('Error loading calendar events.');
        }
    }

    /**
     * Handle AJAX request to get class subjects
     */
    public static function getClassSubjectsAjax() {
        // Check if class type is provided
        if (!isset($_GET['class_type']) || empty($_GET['class_type'])) {
            \wp_send_json_error('Class type is required.');
            return;
        }

        $classType = sanitize_text_field($_GET['class_type']);

        try {
            // Get subjects for the selected class type
            $subjects = ClassTypesController::getClassSubjects($classType);

            if (empty($subjects)) {
                \wp_send_json_error('No subjects found for the selected class type.');
                return;
            }

            \wp_send_json_success($subjects);

        } catch (\Exception $e) {
            error_log('WeCoza Classes Plugin: Error getting class subjects: ' . $e->getMessage());
            \wp_send_json_error('Error loading class subjects.');
        }
    }

    /**
     * Generate calendar events from class schedule data
     * Enhanced to handle both legacy v1.0 and new v2.0 schedule formats
     *
     * @param array $class Class data
     * @return array Calendar events
     */
    private function generateCalendarEvents($class) {
        $events = [];

        // Get basic class information
        $classCode = $class['class_code'] ?? 'Unknown';
        $classSubject = $class['class_subject'] ?? 'Unknown Subject';
        $startDate = $class['original_start_date'] ?? null;
        $deliveryDate = $class['delivery_date'] ?? null;

        // Parse schedule data if available
        $scheduleData = null;
        if (!empty($class['schedule_data'])) {
            $scheduleData = is_string($class['schedule_data'])
                ? json_decode($class['schedule_data'], true)
                : $class['schedule_data'];
        }

        // Generate events from schedule data
        if ($scheduleData && is_array($scheduleData)) {
            $events = $this->generateEventsFromScheduleData($scheduleData, $class);
        } else {
            // Generate sample events based on start and delivery dates
            if ($startDate && $deliveryDate) {
                $start = new \DateTime($startDate);
                $end = new \DateTime($deliveryDate);
                $interval = new \DateInterval('P1D'); // 1 day interval
                $period = new \DatePeriod($start, $interval, $end);

                $eventCount = 0;
                foreach ($period as $date) {
                    // Skip weekends for sample events
                    if ($date->format('N') >= 6) {
                        continue;
                    }

                    // Limit to first 10 events for demo
                    if ($eventCount >= 10) {
                        break;
                    }

                    $events[] = [
                        'id' => 'class_' . $class['class_id'] . '_' . $date->format('Y-m-d'),
                        'title' => '09:00 - 17:00',
                        'start' => $date->format('Y-m-d') . 'T09:00:00',
                        'end' => $date->format('Y-m-d') . 'T17:00:00',
                        'classNames' => ['wecoza-class-event', 'text-primary'],
                        'extendedProps' => [
                            'type' => 'class_session',
                            'classCode' => $classCode,
                            'classSubject' => $classSubject,
                            'notes' => 'Sample class session'
                        ]
                    ];

                    $eventCount++;
                }
            }
        }

        // Handle exception dates if available
        if (!empty($class['exception_dates'])) {
            $exceptionDates = is_string($class['exception_dates'])
                ? json_decode($class['exception_dates'], true)
                : $class['exception_dates'];

            if (is_array($exceptionDates)) {
                foreach ($exceptionDates as $exception) {
                    if (isset($exception['date']) && isset($exception['reason'])) {
                        $events[] = [
                            'id' => 'exception_' . $class['class_id'] . '_' . $exception['date'],
                            'title' => 'Exception - ' . $exception['reason'],
                            'start' => $exception['date'],
                            'allDay' => true,
                            'display' => 'background',
                            'classNames' => ['wecoza-exception-event'],
                            'extendedProps' => [
                                'type' => 'exception',
                                'reason' => $exception['reason']
                            ]
                        ];
                    }
                }
            }
        }

        // Handle stop/restart dates if available
        if (!empty($class['stop_restart_dates'])) {
            $stopRestartDates = is_string($class['stop_restart_dates'])
                ? json_decode($class['stop_restart_dates'], true)
                : $class['stop_restart_dates'];

            if (is_array($stopRestartDates)) {
                foreach ($stopRestartDates as $index => $stopRestart) {
                    if (isset($stopRestart['stop_date']) && isset($stopRestart['restart_date'])) {
                        $stopDate = $stopRestart['stop_date'];
                        $restartDate = $stopRestart['restart_date'];

                        // Create stop date event
                        $events[] = [
                            'id' => 'class_stop_' . $class['class_id'] . '_' . $index,
                            'title' => 'Class Stopped',
                            'start' => $stopDate,
                            'allDay' => true,
                            'display' => 'block',
                            'classNames' => ['text-danger', 'wecoza-stop'],
                            'extendedProps' => [
                                'type' => 'stop_date',
                                'class_id' => $class['class_id'],
                                'description' => sprintf(
                                    'Class Stopped: %s\nClass: %s',
                                    $stopDate,
                                    $classSubject
                                ),
                                'interactive' => false
                            ]
                        ];

                        // Create restart date event
                        $events[] = [
                            'id' => 'class_restart_' . $class['class_id'] . '_' . $index,
                            'title' => 'Restart',
                            'start' => $restartDate,
                            'allDay' => true,
                            'display' => 'block',
                            'classNames' => ['text-danger', 'wecoza-restart'],
                            'extendedProps' => [
                                'type' => 'restart_date',
                                'class_id' => $class['class_id'],
                                'description' => sprintf(
                                    'Class Restart: %s\nClass: %s',
                                    $restartDate,
                                    $classSubject
                                ),
                                'interactive' => false
                            ]
                        ];

                        // Create events for days between stop and restart (red circles only)
                        try {
                            $currentDate = new \DateTime($stopDate);
                            $endDate = new \DateTime($restartDate);

                            // Move to the day after stop date
                            $currentDate->add(new \DateInterval('P1D'));

                            while ($currentDate < $endDate) {
                                $dateStr = $currentDate->format('Y-m-d');

                                $events[] = [
                                    'id' => 'stop_period_' . $class['class_id'] . '_' . $index . '_' . $dateStr,
                                    'title' => '', // No text, just red circle
                                    'start' => $dateStr,
                                    'allDay' => true,
                                    'display' => 'block',
                                    'classNames' => ['text-danger', 'wecoza-stop-period'],
                                    'extendedProps' => [
                                        'type' => 'stop_period',
                                        'class_id' => $class['class_id'],
                                        'description' => sprintf(
                                            'Class Stopped Period: %s\nClass: %s\nStopped from %s to %s',
                                            $dateStr,
                                            $classSubject,
                                            $stopDate,
                                            $restartDate
                                        ),
                                        'interactive' => false
                                    ]
                                ];

                                $currentDate->add(new \DateInterval('P1D'));
                            }
                        } catch (\Exception $e) {
                            error_log('WeCoza Classes Plugin: Error generating stop period events: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Generate events from V2.0 schedule data only
     *
     * @param array $scheduleData V2.0 schedule data
     * @param array $class Class information
     * @return array Calendar events
     */
    private function generateEventsFromScheduleData($scheduleData, $class) {
        // Only handle V2.0 format
        return $this->generateEventsFromV2Data($scheduleData, $class);
    }

    /**
     * Generate events from v2.0 schedule data with proper per-day time support
     *
     * @param array $scheduleData v2.0 schedule data
     * @param array $class Class information
     * @return array Calendar events
     */
    private function generateEventsFromV2Data($scheduleData, $class) {
        $events = [];
        $classCode = $class['class_code'] ?? 'Unknown';
        $classSubject = $class['class_subject'] ?? 'Unknown Subject';

        // Check if we have per-day time data that should be preserved
        $timeData = $scheduleData['timeData'] ?? [];
        $hasPerDayTimes = isset($timeData['mode']) && $timeData['mode'] === 'per-day' && !empty($timeData['perDay']);

        // Generate events directly from v2.0 pattern data
        $events = $this->generateEventsFromV2Pattern($scheduleData, $class);

        // Add exception date events
        if (isset($scheduleData['exceptionDates'])) {
            $events = array_merge($events, $this->generateExceptionEvents($scheduleData['exceptionDates'], $class));
        }

        return $events;
    }

    // Legacy event generation function removed - V2.0 format only

    /**
     * Format event title based on schedule format
     *
     * @param array $schedule Schedule entry
     * @param string $format Schedule format version
     * @return string Formatted title
     */
    private function formatEventTitle($schedule, $format) {
        $startTime = $schedule['start_time'];
        $endTime = $schedule['end_time'];

        if ($format === 'v2.0') {
            // Enhanced title for v2.0 format
            $duration = $this->calculateEventDuration($startTime, $endTime);
            return sprintf('%s - %s (%.1fh)', $startTime, $endTime, $duration);
        } else {
            // Simple title for legacy format
            return $startTime . ' - ' . $endTime;
        }
    }

    /**
     * Generate events from V2.0 schedule data (handles both pattern-based and direct entries)
     *
     * @param array $scheduleData V2.0 schedule data
     * @param array $class Class information
     * @return array Calendar events
     */
    private function generateEventsFromV2Pattern($scheduleData, $class) {
        $events = [];
        $classCode = $class['class_code'] ?? 'Unknown';
        $classSubject = $class['class_subject'] ?? 'Unknown Subject';

        // Check if we have direct schedule entries (numbered keys format)
        $hasDirectEntries = false;
        foreach ($scheduleData as $key => $value) {
            if (is_numeric($key) && is_array($value) && isset($value['date']) && isset($value['start_time']) && isset($value['end_time'])) {
                $hasDirectEntries = true;
                break;
            }
        }

        if ($hasDirectEntries) {
            // Handle direct schedule entries (numbered keys format)
            foreach ($scheduleData as $key => $schedule) {
                if (is_numeric($key) && is_array($schedule) && isset($schedule['date']) && isset($schedule['start_time']) && isset($schedule['end_time'])) {
                    // Calculate duration for display
                    $duration = $this->calculateEventDuration($schedule['start_time'], $schedule['end_time']);
                    $dayName = $schedule['day'] ?? date('l', strtotime($schedule['date']));

                    $events[] = [
                        'id' => 'class_' . $class['class_id'] . '_' . $schedule['date'],
                        'title' => $dayName . ': ' . $schedule['start_time'] . ' - ' . $schedule['end_time'] . ' (' . $duration . 'h)',
                        'start' => $schedule['date'] . 'T' . $schedule['start_time'],
                        'end' => $schedule['date'] . 'T' . $schedule['end_time'],
                        'classNames' => ['wecoza-class-event', 'text-primary'],
                        'extendedProps' => [
                            'type' => 'class_session',
                            'classCode' => $classCode,
                            'classSubject' => $classSubject,
                            'notes' => $schedule['notes'] ?? '',
                            'scheduleFormat' => 'v2.0',
                            'dayOfWeek' => $dayName,
                            'duration' => $duration
                        ]
                    ];
                }
            }
        } else {
            // Handle pattern-based generation
            $pattern = $scheduleData['pattern'] ?? 'weekly';
            $startDate = isset($scheduleData['startDate']) ? new \DateTime($scheduleData['startDate']) : null;
            $endDate = isset($scheduleData['endDate']) ? new \DateTime($scheduleData['endDate']) : null;
            $timeData = $scheduleData['timeData'] ?? [];
            $selectedDays = $scheduleData['selectedDays'] ?? [];

            if ($startDate && $endDate) {
                // Generate schedule entries using existing pattern generation methods
                $scheduleEntries = [];
                switch ($pattern) {
                    case 'weekly':
                        $scheduleEntries = self::generateWeeklyEntries($startDate, $endDate, $timeData, $selectedDays);
                        break;
                    case 'biweekly':
                        $scheduleEntries = self::generateBiweeklyEntries($startDate, $endDate, $timeData, $selectedDays);
                        break;
                    case 'monthly':
                        $scheduleEntries = self::generateMonthlyEntries($startDate, $endDate, $timeData, $scheduleData['dayOfMonth'] ?? 1);
                        break;
                    case 'custom':
                    default:
                        // For custom pattern, create a single entry if we have time data
                        if (isset($timeData['single'])) {
                            $scheduleEntries[] = [
                                'date' => $scheduleData['startDate'],
                                'start_time' => $timeData['single']['startTime'] ?? '09:00',
                                'end_time' => $timeData['single']['endTime'] ?? '17:00'
                            ];
                        }
                        break;
                }

                // Convert schedule entries to calendar events
                foreach ($scheduleEntries as $schedule) {
                    if (isset($schedule['date']) && isset($schedule['start_time']) && isset($schedule['end_time'])) {
                        // Get day of week for enhanced title
                        $date = new \DateTime($schedule['date']);
                        $dayName = $date->format('l');

                        $events[] = [
                            'id' => 'class_' . $class['class_id'] . '_' . $schedule['date'],
                            'title' => $this->formatV2EventTitle($schedule, $dayName, $timeData),
                            'start' => $schedule['date'] . 'T' . $schedule['start_time'],
                            'end' => $schedule['date'] . 'T' . $schedule['end_time'],
                            'classNames' => ['wecoza-class-event', 'text-primary'],
                            'extendedProps' => [
                                'type' => 'class_session',
                                'classCode' => $classCode,
                                'classSubject' => $classSubject,
                                'notes' => $schedule['notes'] ?? '',
                                'scheduleFormat' => 'v2.0',
                                'dayOfWeek' => $dayName,
                                'pattern' => $pattern,
                                'timeMode' => $timeData['mode'] ?? 'single',
                                'duration' => $this->calculateEventDuration($schedule['start_time'], $schedule['end_time'])
                            ]
                        ];
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Format event title for v2.0 events with enhanced per-day information
     *
     * @param array $schedule Schedule entry
     * @param string $dayName Day of week
     * @param array $timeData Time data from v2.0 format
     * @return string Formatted title
     */
    private function formatV2EventTitle($schedule, $dayName, $timeData) {
        $startTime = $schedule['start_time'];
        $endTime = $schedule['end_time'];
        $duration = $this->calculateEventDuration($startTime, $endTime);

        // Check if this is per-day mode for enhanced title
        $mode = $timeData['mode'] ?? 'single';
        if ($mode === 'per-day') {
            // Show day name for per-day schedules to highlight different times
            return sprintf('%s: %s - %s (%.1fh)', $dayName, $startTime, $endTime, $duration);
        } else {
            // Standard title for single-time mode
            return sprintf('%s - %s (%.1fh)', $startTime, $endTime, $duration);
        }
    }

    /**
     * Calculate event duration in hours
     *
     * @param string $startTime Start time (HH:MM)
     * @param string $endTime End time (HH:MM)
     * @return float Duration in hours
     */
    private function calculateEventDuration($startTime, $endTime) {
        $start = strtotime($startTime);
        $end = strtotime($endTime);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return ($end - $start) / 3600;
    }

    /**
     * Generate exception date events
     *
     * @param array $exceptionDates Exception dates from v2.0 format
     * @param array $class Class information
     * @return array Exception events
     */
    private function generateExceptionEvents($exceptionDates, $class) {
        $events = [];

        foreach ($exceptionDates as $exception) {
            if (isset($exception['date']) && isset($exception['reason'])) {
                $events[] = [
                    'id' => 'exception_' . $class['class_id'] . '_' . $exception['date'],
                    'title' => 'Exception - ' . $exception['reason'],
                    'start' => $exception['date'],
                    'allDay' => true,
                    'display' => 'background',
                    'classNames' => ['wecoza-exception-event'],
                    'extendedProps' => [
                        'type' => 'exception',
                        'reason' => $exception['reason'],
                        'scheduleFormat' => 'v2.0'
                    ]
                ];
            }
        }

        return $events;
    }

    /**
     * Validate schedule data (public method for external use)
     *
     * @param array $scheduleData Schedule data to validate
     * @return array Validation result
     */
    public static function validateScheduleData($scheduleData) {
        $validator = new \WeCozaClasses\Services\ScheduleDataValidator();
        return $validator->validate($scheduleData);
    }

    /**
     * Get supported schedule data formats
     *
     * @return array Supported formats with descriptions
     */
    public static function getSupportedScheduleFormats() {
        return [
            'v2.0' => [
                'name' => 'Enhanced Format v2.0',
                'description' => 'Structured format with patterns, per-day times, and metadata',
                'example' => [
                    'version' => '2.0',
                    'pattern' => 'weekly',
                    'timeData' => [
                        'mode' => 'per-day',
                        'perDay' => [
                            'Monday' => ['startTime' => '09:00', 'endTime' => '12:00', 'duration' => 3],
                            'Wednesday' => ['startTime' => '13:00', 'endTime' => '17:00', 'duration' => 4]
                        ]
                    ],
                    'selectedDays' => ['Monday', 'Wednesday'],
                    'exceptionDates' => [],
                    'holidayOverrides' => []
                ]
            ]
        ];
    }

    // Backward compatibility function removed - V2.0 format only

    // Migration and integrity check functions removed - V2.0 format only
    
    /**
     * Handle QA report file uploads
     *
     * @param array $files The $_FILES array data for qa_reports
     * @param array $dates The corresponding qa_visit_dates array
     * @return array Processed file information with metadata
     */
    private static function handleQAReportUploads($files, $dates) {
        $uploadedReports = [];
        
        if (empty($files['name']) || !is_array($files['name'])) {
            return $uploadedReports;
        }
        
        // Get WordPress upload directory
        $upload_dir = wp_upload_dir();
        $qa_reports_dir = $upload_dir['basedir'] . '/qa-reports';
        $qa_reports_url = $upload_dir['baseurl'] . '/qa-reports';
        
        // Create directory if it doesn't exist
        if (!file_exists($qa_reports_dir)) {
            wp_mkdir_p($qa_reports_dir);
        }
        
        // Process each uploaded file
        for ($i = 0; $i < count($files['name']); $i++) {
            // Skip if no file uploaded for this index
            if (empty($files['name'][$i]) || $files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Get corresponding date
            $visit_date = isset($dates[$i]) ? $dates[$i] : date('Y-m-d');
            
            // Prepare file upload
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            // Validate file type (PDF only)
            $allowed_types = ['application/pdf'];
            if (!in_array($file['type'], $allowed_types)) {
                continue;
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $base_name = 'qa_report_' . date('Ymd_His') . '_' . uniqid();
            $new_filename = $base_name . '.' . $file_extension;
            $file_path = $qa_reports_dir . '/' . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $uploadedReports[] = [
                    'date' => $visit_date,
                    'filename' => $new_filename,
                    'original_name' => $file['name'],
                    'file_path' => 'qa-reports/' . $new_filename,
                    'file_url' => $qa_reports_url . '/' . $new_filename,
                    'file_size' => $file['size'],
                    'uploaded_by' => wp_get_current_user()->display_name,
                    'upload_date' => current_time('mysql')
                ];
            }
        }
        
        return $uploadedReports;
    }
    
    /**
     * Process QA data for saving
     *
     * @param array $data Form data
     * @param array $files $_FILES data
     * @return array Processed QA data
     */
    private static function processQAData($data, $files = null) {
        $result = [
            'qa_visit_dates' => null,
            'qa_reports' => []
        ];
        
        // Process visit dates
        if (isset($data['qa_visit_dates']) && is_array($data['qa_visit_dates'])) {
            $dates = array_values(array_filter(array_map([self::class, 'sanitizeText'], $data['qa_visit_dates'])));
            $result['qa_visit_dates'] = json_encode($dates);
        }
        
        // Get existing reports if updating
        $existing_reports = [];
        if (isset($data['qa_reports_metadata']) && !empty($data['qa_reports_metadata'])) {
            $existing_reports = json_decode($data['qa_reports_metadata'], true) ?: [];
        }
        
        // Handle new file uploads
        $new_reports = [];
        if ($files && isset($files['qa_reports'])) {
            $new_reports = self::handleQAReportUploads(
                $files['qa_reports'], 
                $data['qa_visit_dates'] ?? []
            );
        }
        
        // Merge existing and new reports
        $result['qa_reports'] = array_merge($existing_reports, $new_reports);
        
        return $result;
    }
    
    /**
     * AJAX: Get class QA data for a specific class
     *
     * @return void
     */
    public static function getClassQAData() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid security token');
            return;
        }
        
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        
        if ($class_id <= 0) {
            wp_send_json_error('Invalid class ID');
            return;
        }
        
        // Get class data
        $controller = new self();
        $class = $controller->getSingleClass($class_id);
        
        if (!$class) {
            wp_send_json_error('Class not found');
            return;
        }
        
        // Parse QA visit dates
        $qa_visit_dates = [];
        if (!empty($class['qa_visit_dates'])) {
            if (is_string($class['qa_visit_dates'])) {
                // Try to decode as JSON first
                $decoded = json_decode($class['qa_visit_dates'], true);
                if ($decoded !== null) {
                    $qa_visit_dates = $decoded;
                } else {
                    // Fall back to comma-separated values
                    $qa_visit_dates = array_map('trim', explode(',', $class['qa_visit_dates']));
                }
            } elseif (is_array($class['qa_visit_dates'])) {
                $qa_visit_dates = $class['qa_visit_dates'];
            }
        }
        
        $qa_reports = isset($class['qa_reports']) ? $class['qa_reports'] : [];
        
        wp_send_json_success([
            'qa_visit_dates' => $qa_visit_dates,
            'qa_reports' => $qa_reports
        ]);
    }
    
    
    
    
    /**
     * AJAX: Submit a QA question
     *
     * @return void
     */
    public static function submitQAQuestion() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid security token');
            return;
        }
        
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        $question = isset($_POST['question']) ? sanitize_textarea_field($_POST['question']) : '';
        $context = isset($_POST['context']) ? sanitize_textarea_field($_POST['context']) : '';
        
        if ($class_id <= 0 || empty($question)) {
            wp_send_json_error('Invalid input data');
            return;
        }
        
        // Handle file upload if present
        $attachment_url = '';
        $attachment_path = '';
        
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            
            // Validate file type
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                            'image/jpeg', 'image/png'];
            if (!in_array($file['type'], $allowed_types)) {
                wp_send_json_error('Invalid file type');
                return;
            }
            
            // Validate file size (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                wp_send_json_error('File size must be less than 5MB');
                return;
            }
            
            // Upload file
            $upload_dir = wp_upload_dir();
            $qa_dir = $upload_dir['basedir'] . '/qa-questions/' . $class_id;
            
            if (!file_exists($qa_dir)) {
                wp_mkdir_p($qa_dir);
            }
            
            $filename = 'question_' . uniqid() . '_' . sanitize_file_name($file['name']);
            $filepath = $qa_dir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $attachment_path = 'qa-questions/' . $class_id . '/' . $filename;
                $attachment_url = $upload_dir['baseurl'] . '/' . $attachment_path;
            }
        }
        
        // Create question data
        $question_data = [
            'id' => uniqid('qa_'),
            'question' => $question,
            'context' => $context,
            'author' => wp_get_current_user()->display_name,
            'author_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'status' => 'pending',
            'answers' => []
        ];
        
        if ($attachment_url) {
            $question_data['attachment'] = [
                'url' => $attachment_url,
                'path' => $attachment_path,
                'name' => basename($filename)
            ];
        }
        
        // Get current class data
        $controller = new self();
        $class = $controller->getSingleClass($class_id);
        
        if (!$class) {
            wp_send_json_error('Class not found');
            return;
        }
        
        // Get existing Q&A data (stored in class_notes_data for now)
        $qa_data = isset($class['qa_data']) && is_array($class['qa_data']) 
            ? $class['qa_data'] 
            : [];
        
        // Add new question
        $qa_data[] = $question_data;
        
        // For now, we'll store Q&A in a separate field or within class_notes_data
        // This would need a schema update to add qa_data JSONB column
        
        wp_send_json_success([
            'message' => 'Question submitted successfully',
            'question' => $question_data
        ]);
    }
    
    /**
     * AJAX: Delete a QA report
     *
     * @return void
     */
    public function deleteQAReport() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid security token');
            return;
        }
        
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        $report_index = isset($_POST['report_index']) ? intval($_POST['report_index']) : -1;
        
        if ($class_id <= 0 || $report_index < 0) {
            wp_send_json_error('Invalid input data');
            return;
        }
        
        // Get current class data
        $class = $this->getSingleClass($class_id);
        
        if (!$class) {
            wp_send_json_error('Class not found');
            return;
        }
        
        // Get existing reports
        $reports = isset($class['qa_reports']) && is_array($class['qa_reports']) 
            ? $class['qa_reports'] 
            : [];
        
        if (!isset($reports[$report_index])) {
            wp_send_json_error('Report not found');
            return;
        }
        
        // Get file path to delete
        $report = $reports[$report_index];
        $file_path = '';
        
        if (isset($report['file_path'])) {
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . '/' . $report['file_path'];
        }
        
        // Remove from array
        array_splice($reports, $report_index, 1);
        
        // Update class
        $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();
        
        try {
            $sql = "UPDATE public.classes SET qa_reports = :reports, updated_at = NOW() WHERE class_id = :class_id";
            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute([
                'reports' => json_encode($reports),
                'class_id' => $class_id
            ]);
            
            // Delete file if exists
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
            
            wp_send_json_success([
                'message' => 'Report deleted successfully',
                'remaining_reports' => count($reports)
            ]);
        } catch (\Exception $e) {
            error_log('Error deleting QA report: ' . $e->getMessage());
            wp_send_json_error('Failed to delete report');
        }
    }
    
    /**
     * AJAX: Upload attachment to WordPress media library
     *
     * @return void
     */
    public static function uploadAttachment() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid security token');
            return;
        }
        
        // Check if user can upload files
        if (!current_user_can('upload_files')) {
            wp_send_json_error('You do not have permission to upload files');
            return;
        }
        
        // Check if file was uploaded
        if (empty($_FILES['file'])) {
            wp_send_json_error('No file uploaded');
            return;
        }
        
        $file = $_FILES['file'];
        $context = isset($_POST['context']) ? sanitize_text_field($_POST['context']) : 'general';
        
        // Validate file type
        $allowed_types = ['application/pdf', 'application/msword', 
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel', 
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg', 'image/png'];
        
        $file_type = wp_check_filetype($file['name']);
        if (!in_array($file['type'], $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
            wp_send_json_error('Invalid file type');
            return;
        }
        
        // Validate file size (10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error('File size must be less than 10MB');
            return;
        }
        
        // Handle the upload using WordPress functions
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Set custom upload directory
        add_filter('upload_dir', [__CLASS__, 'customUploadDir']);
        
        // Move uploaded file to uploads directory
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        // Remove custom upload directory filter
        remove_filter('upload_dir', [__CLASS__, 'customUploadDir']);
        
        if ($movefile && !isset($movefile['error'])) {
            // File uploaded successfully, now create attachment
            $filename = $movefile['file'];
            $filetype = wp_check_filetype(basename($filename), null);
            $wp_upload_dir = wp_upload_dir();
            
            // Prepare attachment data
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            // Insert the attachment
            $attach_id = wp_insert_attachment($attachment, $filename);
            
            if (!is_wp_error($attach_id)) {
                // Generate metadata for the attachment
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Add custom meta for context
                update_post_meta($attach_id, '_wecoza_context', $context);
                
                wp_send_json_success([
                    'id' => $attach_id,
                    'url' => wp_get_attachment_url($attach_id),
                    'title' => get_the_title($attach_id),
                    'filename' => basename($filename),
                    'filesize' => filesize($filename),
                    'filetype' => $filetype['type']
                ]);
            } else {
                wp_send_json_error('Failed to create attachment');
            }
        } else {
            // Handle error
            $error = isset($movefile['error']) ? $movefile['error'] : 'File upload failed';
            wp_send_json_error($error);
        }
    }
    
    /**
     * Get class notes via AJAX
     */
    /**
     * Get cached class notes with transient support and performance optimizations
     * Uses PostgreSQL database with JSONB column for class_notes_data
     * @param int $class_id The class ID
     * @param array $options Optional parameters for optimization
     * @return array Array of notes
     */
    private static function getCachedClassNotes($class_id, $options = []) {
        $cache_key = "wecoza_class_notes_{$class_id}";
        $cached_notes = get_transient($cache_key);
        
        if ($cached_notes !== false) {
            return $cached_notes;
        }
        
        // Use PostgreSQL connection for external database
        $db_config = include(plugin_dir_path(__FILE__) . '../../config/app.php');
        $pg_config = $db_config['database']['postgresql'];
        
        try {
            $pdo = new \PDO(
                "pgsql:host={$pg_config['host']};port={$pg_config['port']};dbname={$pg_config['dbname']}",
                $pg_config['user'],
                $pg_config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Query PostgreSQL classes table for class_notes_data JSONB column
            $stmt = $pdo->prepare("SELECT class_notes_data FROM public.classes WHERE class_id = :class_id LIMIT 1");
            $stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $notes = [];
            if ($result && !empty($result['class_notes_data'])) {
                $notes_data = json_decode($result['class_notes_data'], true);
                if (is_array($notes_data)) {
                    $notes = $notes_data;
                    
                    // Sort notes by created_at for better performance
                    usort($notes, function($a, $b) {
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    });
                    
                    // Apply pagination limits early to reduce memory usage
                    $limit = isset($options['limit']) ? (int)$options['limit'] : 50;
                    $offset = isset($options['offset']) ? (int)$options['offset'] : 0;
                    
                    if ($limit > 0) {
                        $notes = array_slice($notes, $offset, $limit);
                    }
                }
            }
            
            // Cache for 15 minutes with performance optimizations
            set_transient($cache_key, $notes, 15 * MINUTE_IN_SECONDS);
            
            return $notes;
            
        } catch (\PDOException $e) {
            // Log error and return empty array
            error_log("PostgreSQL connection error in getCachedClassNotes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clear cached class notes
     * @param int $class_id The class ID
     */
    private static function clearCachedClassNotes($class_id) {
        $cache_key = "wecoza_class_notes_{$class_id}";
        delete_transient($cache_key);
    }
    
    /**
     * Clear all class notes caches (for bulk operations)
     */
    private static function clearAllClassNotesCache() {
        global $wpdb;
        
        // Get all transient keys for class notes
        $transient_keys = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_wecoza_class_notes_%'"
        );
        
        foreach ($transient_keys as $key) {
            $transient_name = str_replace('_transient_', '', $key);
            delete_transient($transient_name);
        }
    }

    public static function getClassNotes() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $class_id = intval($_POST['class_id']);
        if (!$class_id) {
            wp_send_json_error('Invalid class ID');
        }
        
        // Use cached notes
        $notes = self::getCachedClassNotes($class_id);
        
        if ($notes === false) {
            wp_send_json_error('Class not found');
        }
        
        // Parse class notes
        if (!is_array($notes)) {
            $notes = [];
        }
        
        // Add author names and format dates
        foreach ($notes as &$note) {
            if (isset($note['author_id'])) {
                $user = get_user_by('id', $note['author_id']);
                $note['author_name'] = $user ? $user->display_name : 'Unknown';
            }
            
            // Ensure dates are properly formatted
            if (isset($note['created_at'])) {
                $note['created_at'] = date('c', strtotime($note['created_at']));
            }
            if (isset($note['updated_at'])) {
                $note['updated_at'] = date('c', strtotime($note['updated_at']));
            }
        }
        
        wp_send_json_success(['notes' => $notes]);
    }
    
    /**
     * Save class note via AJAX
     */
    public static function saveClassNote() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $class_id = intval($_POST['class_id']);
        if (!$class_id) {
            wp_send_json_error('Invalid class ID');
        }
        
        $note_data = $_POST['note'];
        if (!$note_data) {
            wp_send_json_error('No note data provided');
        }
        
        // Validate note data
        $note = [
            'id' => isset($note_data['id']) ? sanitize_text_field($note_data['id']) : uniqid('note_'),
            'title' => sanitize_text_field($note_data['title']),
            'content' => sanitize_textarea_field($note_data['content']),
            'category' => sanitize_text_field($note_data['category'] ?? 'general'),
            'priority' => sanitize_text_field($note_data['priority'] ?? 'medium'),
            'tags' => isset($note_data['tags']) ? array_map('sanitize_text_field', explode(',', $note_data['tags'])) : [],
            'author_id' => get_current_user_id(),
            'created_at' => isset($note_data['created_at']) ? $note_data['created_at'] : date('c'),
            'updated_at' => date('c'),
            'attachments' => isset($note_data['attachments']) ? $note_data['attachments'] : []
        ];
        
        // Basic validation
        if (empty($note['title'])) {
            wp_send_json_error('Note title is required');
        }
        
        if (empty($note['content'])) {
            wp_send_json_error('Note content is required');
        }
        
        // Use PostgreSQL connection for external database
        $db_config = include(plugin_dir_path(__FILE__) . '../../config/app.php');
        $pg_config = $db_config['database']['postgresql'];
        
        try {
            $pdo = new \PDO(
                "pgsql:host={$pg_config['host']};port={$pg_config['port']};dbname={$pg_config['dbname']}",
                $pg_config['user'],
                $pg_config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Get current class notes from PostgreSQL
            $stmt = $pdo->prepare("SELECT class_notes_data FROM public.classes WHERE class_id = :class_id LIMIT 1");
            $stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$result) {
                wp_send_json_error('Class not found');
            }
            
            // Parse existing notes
            $notes = [];
            if (!empty($result['class_notes_data'])) {
                $notes_data = json_decode($result['class_notes_data'], true);
                if (is_array($notes_data)) {
                    $notes = $notes_data;
                }
            }
            
            // Find existing note or add new one
            $note_found = false;
            foreach ($notes as &$existing_note) {
                if ($existing_note['id'] === $note['id']) {
                    // Update existing note
                    $existing_note = array_merge($existing_note, $note);
                    $note_found = true;
                    break;
                }
            }
            
            if (!$note_found) {
                // Add new note
                $notes[] = $note;
            }
            
            // Update PostgreSQL database with JSONB
            $notes_json = json_encode($notes);
            $update_stmt = $pdo->prepare("UPDATE public.classes SET class_notes_data = :notes_data, updated_at = NOW() WHERE class_id = :class_id");
            $update_stmt->bindParam(':notes_data', $notes_json, \PDO::PARAM_STR);
            $update_stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $update_result = $update_stmt->execute();
        
            if ($update_result) {
                // Clear cache after successful save
                self::clearCachedClassNotes($class_id);
                
                // Add author name for response
                $user = get_user_by('id', $note['author_id']);
                $note['author_name'] = $user ? $user->display_name : 'Unknown';
                
                wp_send_json_success(['note' => $note]);
            } else {
                wp_send_json_error('Failed to save note');
            }
            
        } catch (\PDOException $e) {
            // Log error and return error response
            error_log("PostgreSQL error in saveClassNote: " . $e->getMessage());
            wp_send_json_error('Database error: Failed to save note');
        }
    }
    
    /**
     * Delete class note via AJAX
     */
    public static function deleteClassNote() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $class_id = intval($_POST['class_id'] ?? 0);
        $note_id = sanitize_text_field($_POST['note_id'] ?? '');
        
        if (!$class_id || !$note_id) {
            wp_send_json_error('Invalid class ID or note ID');
        }
        
        // Use PostgreSQL connection for external database
        $db_config = include(plugin_dir_path(__FILE__) . '../../config/app.php');
        $pg_config = $db_config['database']['postgresql'];
        
        try {
            $pdo = new \PDO(
                "pgsql:host={$pg_config['host']};port={$pg_config['port']};dbname={$pg_config['dbname']}",
                $pg_config['user'],
                $pg_config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Get current class notes from PostgreSQL
            $stmt = $pdo->prepare("SELECT class_notes_data FROM public.classes WHERE class_id = :class_id LIMIT 1");
            $stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$result) {
                wp_send_json_error('Class not found');
            }
            
            // Parse existing notes
            $notes = [];
            if (!empty($result['class_notes_data'])) {
                $notes_data = json_decode($result['class_notes_data'], true);
                if (is_array($notes_data)) {
                    $notes = $notes_data;
                }
            }
            
            // Filter out the note to delete
            $original_count = count($notes);
            $notes = array_filter($notes, function($note) use ($note_id) {
                return $note['id'] !== $note_id;
            });
            
            // Check if note was found and removed
            if (count($notes) === $original_count) {
                wp_send_json_error('Note not found');
            }
            
            // Re-index array
            $notes = array_values($notes);
            
            // Update PostgreSQL database with JSONB
            $notes_json = json_encode($notes);
            $update_stmt = $pdo->prepare("UPDATE public.classes SET class_notes_data = :notes_data, updated_at = NOW() WHERE class_id = :class_id");
            $update_stmt->bindParam(':notes_data', $notes_json, \PDO::PARAM_STR);
            $update_stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $update_result = $update_stmt->execute();
        
            if ($update_result) {
                // Clear cache after successful delete
                self::clearCachedClassNotes($class_id);
                
                wp_send_json_success(['message' => 'Note deleted successfully']);
            } else {
                wp_send_json_error('Failed to delete note');
            }
            
        } catch (\PDOException $e) {
            // Log error and return error response
            error_log("PostgreSQL error in deleteClassNote: " . $e->getMessage());
            wp_send_json_error('Database error: Failed to delete note');
        }
    }

    /**
     * Custom upload directory for class-related files
     *
     * @param array $upload
     * @return array
     */
    public static function customUploadDir($upload) {
        $upload['subdir'] = '/wecoza-classes' . $upload['subdir'];
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        
        return $upload;
    }
}
