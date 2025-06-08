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
        \wp_enqueue_style(
            'fullcalendar-css',
            'https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.css',
            [],
            '6.1.15'
        );

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
            ['jquery'],
            WECOZA_CLASSES_VERSION,
            true
        );

        \wp_enqueue_script(
            'wecoza-class-types-js',
            WECOZA_CLASSES_JS_URL . 'class-types.js',
            ['jquery', 'wecoza-class-js'],
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
        // Static site address data - in production this would come from database
        return [
            '11_1' => 'Aspen Pharmacare Head Office, 1 Sandton Drive, Sandton, 2196',
            '11_2' => 'Aspen Pharmacare Production Unit, 15 Industrial Road, Germiston, 1401',
            '11_3' => 'Aspen Pharmacare Research Centre, 25 Science Park, Johannesburg, 2000',
            '14_1' => 'Barloworld Northern Branch, 45 North Street, Pretoria, 0001',
            '14_2' => 'Barloworld Southern Branch, 78 South Avenue, Cape Town, 8001',
            '14_3' => 'Barloworld Central Branch, 12 Central Road, Bloemfontein, 9300',
            '9_1' => 'Bidvest Group Main Office, 18 Bidvest Boulevard, Johannesburg, 2000',
            '9_2' => 'Bidvest Group Distribution Center, 55 Logistics Lane, Durban, 4000',
            '8_1' => 'FirstRand Corporate Office, 4 Merchant Place, Sandton, 2196',
            '8_2' => 'FirstRand Training Center, 88 Learning Street, Johannesburg, 2000',
            '4_1' => 'MTN Group Headquarters, 216 14th Avenue, Fairland, 2195',
            '4_2' => 'MTN Group Technical Center, 99 Technology Drive, Midrand, 1685',
        ];
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
        
        if ($class_id) {
            // Get existing class data
            $class = $this->getSingleClass($class_id);
            
            if (empty($class)) {
                return '<div class="alert alert-danger">Class not found.</div>';
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
        // Static client data - in production this would come from database
        return [
            ['id' => 11, 'name' => 'Aspen Pharmacare'],
            ['id' => 14, 'name' => 'Barloworld'],
            ['id' => 9, 'name' => 'Bidvest Group'],
            ['id' => 8, 'name' => 'FirstRand'],
            ['id' => 4, 'name' => 'MTN Group'],
            ['id' => 15, 'name' => 'Multichoice Group'],
            ['id' => 5, 'name' => 'Naspers'],
            ['id' => 12, 'name' => 'Nedbank Group'],
            ['id' => 10, 'name' => 'Sanlam'],
            ['id' => 1, 'name' => 'Sasol Limited'],
            ['id' => 3, 'name' => 'Shoprite Holdings'],
            ['id' => 2, 'name' => 'Standard Bank Group'],
            ['id' => 13, 'name' => 'Tiger Brands'],
            ['id' => 6, 'name' => 'Vodacom Group'],
            ['id' => 7, 'name' => 'Woolworths Holdings']
        ];
    }

    private function getSites() {
        // Static site data grouped by client - in production this would come from database
        return [
            11 => [ // Aspen Pharmacare
                ['id' => '11_1', 'name' => 'Aspen Pharmacare - Head Office'],
                ['id' => '11_2', 'name' => 'Aspen Pharmacare - Production Unit'],
                ['id' => '11_3', 'name' => 'Aspen Pharmacare - Research Centre']
            ],
            14 => [ // Barloworld
                ['id' => '14_1', 'name' => 'Barloworld - Northern Branch'],
                ['id' => '14_2', 'name' => 'Barloworld - Southern Branch'],
                ['id' => '14_3', 'name' => 'Barloworld - Central Branch']
            ],
            9 => [ // Bidvest Group
                ['id' => '9_1', 'name' => 'Bidvest Group - Main Office'],
                ['id' => '9_2', 'name' => 'Bidvest Group - Distribution Center']
            ],
            8 => [ // FirstRand
                ['id' => '8_1', 'name' => 'FirstRand - Corporate Office'],
                ['id' => '8_2', 'name' => 'FirstRand - Training Center']
            ],
            4 => [ // MTN Group
                ['id' => '4_1', 'name' => 'MTN Group - Headquarters'],
                ['id' => '4_2', 'name' => 'MTN Group - Technical Center']
            ],
            // Add more clients as needed
        ];
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
            array('id' => 1, 'name' => 'Yes'),
            array('id' => 0, 'name' => 'No')
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
        error_log('=== CLASS SAVE AJAX START ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('FILES data: ' . print_r($_FILES, true));

        // Create instance
        $instance = new self();

        // Check nonce for security
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            error_log('Nonce verification failed');
            $instance->sendJsonError('Security check failed.');
            return;
        }

        error_log('Nonce verification passed');

        // Process form data (including file uploads)
        $formData = self::processFormData($_POST, $_FILES);
        error_log('Processed form data: ' . print_r($formData, true));

        // Determine if this is create or update operation
        $isUpdate = isset($formData['id']) && !empty($formData['id']);
        $classId = $isUpdate ? intval($formData['id']) : null;

        error_log($isUpdate ? "Updating existing class with ID: {$classId}" : 'Creating new class');

        // Use direct model access for create or update
        try {
            if ($isUpdate) {
                // Load existing class and update it
                $class = ClassModel::getById($classId);
                if (!$class) {
                    error_log('Class not found for update: ' . $classId);
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
                $instance->sendJsonSuccess([
                    'message' => $isUpdate ? 'Class updated successfully.' : 'Class created successfully.',
                    'class_id' => $class->getId()
                ]);
            } else {
                error_log('Model operation failed');
                $instance->sendJsonError(
                    $isUpdate ? 'Failed to update class.' : 'Failed to create class.'
                );
            }
        } catch (\Exception $e) {
            error_log('Exception during class save: ' . $e->getMessage());
            $instance->sendJsonError('An error occurred while saving the class: ' . $e->getMessage());
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
        $processed = [];

        // Basic fields - using snake_case field names that the model expects
        $processed['id'] = isset($data['class_id']) && $data['class_id'] !== 'auto-generated' ? intval($data['class_id']) : null;
        $processed['client_id'] = isset($data['client_id']) && !empty($data['client_id']) ? intval($data['client_id']) : null;
        $processed['site_id'] = isset($data['site_id']) && !is_array($data['site_id']) ? $data['site_id'] : null;
        $processed['site_address'] = isset($data['site_address']) && !is_array($data['site_address']) ? self::sanitizeText($data['site_address']) : null;
        $processed['class_type'] = isset($data['class_type']) && !is_array($data['class_type']) ? self::sanitizeText($data['class_type']) : null;
        $processed['class_subject'] = isset($data['class_subject']) && !is_array($data['class_subject']) ? self::sanitizeText($data['class_subject']) : null;
        $processed['class_code'] = isset($data['class_code']) && !is_array($data['class_code']) ? self::sanitizeText($data['class_code']) : null;
        $processed['class_duration'] = isset($data['class_duration']) && !empty($data['class_duration']) ? intval($data['class_duration']) : null;
        $processed['original_start_date'] = isset($data['original_start_date']) && !is_array($data['original_start_date']) ? self::sanitizeText($data['original_start_date']) : null;
        $processed['seta_funded'] = isset($data['seta_funded']) ? (bool)$data['seta_funded'] : false;
        $processed['seta'] = isset($data['seta']) && !is_array($data['seta']) ? self::sanitizeText($data['seta']) : null;
        $processed['exam_class'] = isset($data['exam_class']) ? (bool)$data['exam_class'] : false;
        $processed['exam_type'] = isset($data['exam_type']) && !is_array($data['exam_type']) ? self::sanitizeText($data['exam_type']) : null;
        $processed['qa_visit_dates'] = isset($data['qa_visit_dates']) && !is_array($data['qa_visit_dates']) ? self::sanitizeText($data['qa_visit_dates']) : null;
        $processed['class_agent'] = isset($data['class_agent']) && !empty($data['class_agent']) ? intval($data['class_agent']) : null;
        $processed['initial_class_agent'] = isset($data['initial_class_agent']) && !empty($data['initial_class_agent']) ? intval($data['initial_class_agent']) : null;
        $processed['initial_agent_start_date'] = isset($data['initial_agent_start_date']) && !is_array($data['initial_agent_start_date']) ? self::sanitizeText($data['initial_agent_start_date']) : null;
        $processed['project_supervisor'] = isset($data['project_supervisor']) && !empty($data['project_supervisor']) ? intval($data['project_supervisor']) : null;
        $processed['delivery_date'] = isset($data['delivery_date']) && !is_array($data['delivery_date']) ? self::sanitizeText($data['delivery_date']) : null;

        // Array fields
        $processed['class_notes'] = isset($data['class_notes']) && is_array($data['class_notes']) ? array_map([self::class, 'sanitizeText'], $data['class_notes']) : [];
        $processed['qa_reports'] = isset($data['qa_reports']) && is_array($data['qa_reports']) ? array_map([self::class, 'sanitizeText'], $data['qa_reports']) : [];

        // JSON fields that need special handling
        $processed['learner_ids'] = self::processJsonField($data, 'class_learners_data');
        $processed['backup_agent_ids'] = self::processJsonField($data, 'backup_agents_data');
        $processed['schedule_data'] = self::processJsonField($data, 'schedule_data');
        $processed['stop_restart_dates'] = self::processJsonField($data, 'stop_restart_dates');

        return $processed;
    }

    /**
     * Process JSON field from form data
     *
     * @param array $data Form data
     * @param string $field Field name
     * @return array Processed JSON data
     */
    private static function processJsonField($data, $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return [];
        }

        $value = $data[$field];

        // Handle WordPress addslashes and HTML encoding
        if (is_string($value)) {
            $value = stripslashes($value);
            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        }

        // Decode JSON
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for field {$field}: " . json_last_error_msg());
            return [];
        }

        return $decoded ?: [];
    }

    /**
     * Sanitize text input
     *
     * @param string $text Input text
     * @return string Sanitized text
     */
    private static function sanitizeText($text) {
        return sanitize_text_field($text);
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

            // Prepare view data
            $viewData = [
                'classes' => $classes,
                'show_loading' => $atts['show_loading'],
                'total_count' => count($classes)
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

        // Build the query
        $sql = "
            SELECT
                c.class_id,
                c.client_id,
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
                c.project_supervisor_id,
                c.created_at,
                c.updated_at
            FROM public.classes c
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
                c.*
            FROM public.classes c
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
                    'original_start_date' => date('Y-m-d'),
                    'delivery_date' => date('Y-m-d', strtotime('+30 days')),
                    'schedule_data' => null,
                    'exception_dates' => null,
                    'stop_restart_dates' => [
                        [
                            'stop_date' => date('Y-m-d', strtotime('+10 days')),
                            'restart_date' => date('Y-m-d', strtotime('+15 days'))
                        ]
                    ]
                ];
            }

            // Handle JSONB fields that come as strings from PostgreSQL
            $jsonbFields = ['learner_ids', 'backup_agent_ids', 'schedule_data', 'stop_restart_dates', 'class_notes_data', 'qa_reports'];

            foreach ($jsonbFields as $field) {
                if (isset($result[$field]) && is_string($result[$field])) {
                    $decoded = json_decode($result[$field], true);
                    if ($decoded !== null) {
                        $result[$field] = $decoded;
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
                'schedule_data' => null,
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

        // If we have schedule data, generate events from it
        if ($scheduleData && is_array($scheduleData)) {
            foreach ($scheduleData as $schedule) {
                if (isset($schedule['date']) && isset($schedule['start_time']) && isset($schedule['end_time'])) {
                    $events[] = [
                        'id' => 'class_' . $class['class_id'] . '_' . $schedule['date'],
                        'title' => $schedule['start_time'] . ' - ' . $schedule['end_time'],
                        'start' => $schedule['date'] . 'T' . $schedule['start_time'],
                        'end' => $schedule['date'] . 'T' . $schedule['end_time'],
                        'classNames' => ['wecoza-class-event', 'text-primary'],
                        'extendedProps' => [
                            'type' => 'class_session',
                            'classCode' => $classCode,
                            'classSubject' => $classSubject,
                            'notes' => $schedule['notes'] ?? ''
                        ]
                    ];
                }
            }
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
                            'classNames' => ['text-danger', 'wecoza-stop-restart'],
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
                            'classNames' => ['text-danger', 'wecoza-stop-restart'],
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
}
