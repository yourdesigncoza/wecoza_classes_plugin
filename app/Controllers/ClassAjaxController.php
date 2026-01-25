<?php
/**
 * ClassAjaxController.php
 *
 * Controller for handling all AJAX requests related to class management.
 * Centralizes AJAX operations previously spread across ClassController.
 *
 * @package WeCozaClasses
 * @since 2.0.0
 */

namespace WeCozaClasses\Controllers;

use WeCozaClasses\Models\ClassModel;
use WeCozaClasses\Repositories\ClassRepository;
use WeCozaClasses\Services\FormDataProcessor;
use WeCozaClasses\Services\ScheduleService;
use WeCozaClasses\Services\Database\DatabaseService;

/**
 * AJAX Controller for class management operations
 *
 * Handles all AJAX endpoints for:
 * - Saving/updating classes
 * - Deleting classes
 * - Calendar events
 * - Class subjects lookup
 * - Class notes CRUD operations
 * - File uploads
 */
class ClassAjaxController
{
    /**
     * Constructor - Register AJAX hooks
     *
     * Security: Only read-only lookup endpoints have nopriv access.
     * All write operations require authentication.
     */
    public function __construct()
    {
        // Class CRUD operations (authenticated only)
        \add_action('wp_ajax_save_class', [__CLASS__, 'saveClassAjax']);
        \add_action('wp_ajax_delete_class', [__CLASS__, 'deleteClassAjax']);

        // Calendar events (authenticated only - contains class schedule data)
        \add_action('wp_ajax_get_calendar_events', [__CLASS__, 'getCalendarEventsAjax']);

        // Class subjects lookup (public - required for form dropdowns)
        \add_action('wp_ajax_get_class_subjects', [__CLASS__, 'getClassSubjectsAjax']);
        \add_action('wp_ajax_nopriv_get_class_subjects', [__CLASS__, 'getClassSubjectsAjax']);

        // Class notes operations (authenticated only)
        \add_action('wp_ajax_get_class_notes', [__CLASS__, 'getClassNotes']);
        \add_action('wp_ajax_save_class_note', [__CLASS__, 'saveClassNote']);
        \add_action('wp_ajax_delete_class_note', [__CLASS__, 'deleteClassNote']);

        // File uploads (authenticated only)
        \add_action('wp_ajax_upload_attachment', [__CLASS__, 'uploadAttachment']);
    }

    /**
     * Handle AJAX request to save class data
     *
     * @return void
     */
    public static function saveClassAjax(): void
    {
        // Start output buffering to capture any unexpected output
        ob_start();

        // Set error handler to capture warnings/notices
        $errorMessages = [];
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$errorMessages) {
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

            // Check nonce for security
            if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
                ob_clean();
                restore_error_handler();
                \wp_send_json_error('Security check failed. Please refresh the page and try again.');
                return;
            }

            // Process form data (including file uploads)
            $formData = FormDataProcessor::processFormData($_POST, $_FILES);

            // Determine if this is create or update operation
            $isUpdate = isset($formData['id']) && !empty($formData['id']);
            $classId = $isUpdate ? intval($formData['id']) : null;

            // Use direct model access for create or update
            try {
                // First check if database is properly configured
                try {
                    $db = DatabaseService::getInstance();
                } catch (\Exception $dbError) {
                    ob_clean();
                    restore_error_handler();
                    \wp_send_json_error('Database connection failed. Please ensure PostgreSQL credentials are configured in WordPress options (wecoza_postgres_password).');
                    return;
                }

                if ($isUpdate) {
                    // Load existing class and update it
                    $class = ClassModel::getById($classId);
                    if (!$class) {
                        ob_clean();
                        restore_error_handler();
                        \wp_send_json_error('Class not found for update.');
                        return;
                    }

                    // Update the class with new data
                    $class = FormDataProcessor::populateClassModel($class, $formData);
                    $result = $class->update();
                } else {
                    // Create new class instance and save it
                    $class = new ClassModel();
                    $class = FormDataProcessor::populateClassModel($class, $formData);
                    $result = $class->save();
                }

                if ($result) {
                    // Save QA visits to the new normalized structure
                    if (!empty($_POST) || !empty($_FILES)) {
                        QAController::saveQAVisits($class->getId(), $_POST, $_FILES);
                    }

                    // Generate redirect URL to single class display page
                    $redirect_url = '';
                    $display_page = \get_page_by_path('app/display-single-class');
                    if ($display_page) {
                        $redirect_url = \add_query_arg(
                            'class_id',
                            $class->getId(),
                            \get_permalink($display_page->ID)
                        );
                    }

                    // Clean buffer and restore error handler before sending response
                    ob_clean();
                    restore_error_handler();

                    \wp_send_json_success([
                        'message' => $isUpdate ? 'Class updated successfully.' : 'Draft class created successfully.',
                        'class_id' => $class->getId(),
                        'redirect_url' => $redirect_url
                    ]);
                } else {
                    ob_clean();
                    restore_error_handler();
                    \wp_send_json_error(
                        $isUpdate ? 'Failed to update class.' : 'Failed to create class.'
                    );
                }
            } catch (\Exception $e) {
                ob_clean();
                restore_error_handler();
                \wp_send_json_error('An error occurred while saving the class: ' . $e->getMessage());
            }
        } catch (\Error $e) {
            ob_clean();
            restore_error_handler();
            \wp_send_json_error('A server error occurred. Please check the error logs.');
        } catch (\Throwable $e) {
            ob_clean();
            restore_error_handler();
            \wp_send_json_error('A critical error occurred. Please check the error logs.');
        }
    }

    /**
     * Handle AJAX request to delete class
     *
     * @return void
     */
    public static function deleteClassAjax(): void
    {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            \wp_send_json_error('Security check failed.');
            return;
        }

        // Check user permissions - only administrators can delete classes
        if (!\current_user_can('manage_options')) {
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
            $db = DatabaseService::getInstance();
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
                \wp_send_json_error('Failed to delete class: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            \wp_send_json_error('Database error occurred while deleting class.');
        }
    }

    /**
     * Handle AJAX request to get calendar events
     *
     * @return void
     */
    public static function getCalendarEventsAjax(): void
    {
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
            $class = ClassRepository::getSingleClass($class_id);

            if (!$class) {
                \wp_send_json_error('Class not found.');
                return;
            }

            // Generate calendar events from schedule data
            $scheduleService = new ScheduleService();
            $events = $scheduleService->generateCalendarEvents($class);

            // FullCalendar expects a direct array of events, not wrapped in success response
            \wp_send_json($events);

        } catch (\Exception $e) {
            \wp_send_json_error('Error loading calendar events.');
        }
    }

    /**
     * Handle AJAX request to get class subjects
     *
     * @return void
     */
    public static function getClassSubjectsAjax(): void
    {
        // Check if class type is provided
        if (!isset($_GET['class_type']) || empty($_GET['class_type'])) {
            \wp_send_json_error('Class type is required.');
            return;
        }

        $classType = \sanitize_text_field($_GET['class_type']);

        try {
            // Get subjects for the selected class type
            $subjects = ClassTypesController::getClassSubjects($classType);

            if (empty($subjects)) {
                \wp_send_json_error('No subjects found for the selected class type.');
                return;
            }

            \wp_send_json_success($subjects);

        } catch (\Exception $e) {
            \wp_send_json_error('Error loading class subjects.');
        }
    }

    /**
     * Get class notes via AJAX
     *
     * @return void
     */
    public static function getClassNotes(): void
    {
        // Verify nonce
        if (!\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            \wp_send_json_error('Invalid nonce');
        }

        $class_id = intval($_POST['class_id']);
        if (!$class_id) {
            \wp_send_json_error('Invalid class ID');
        }

        // Use cached notes
        $notes = ClassRepository::getCachedClassNotes($class_id);

        if ($notes === false) {
            \wp_send_json_error('Class not found');
        }

        // Parse class notes
        if (!is_array($notes)) {
            $notes = [];
        }

        // Add author names and format dates
        foreach ($notes as &$note) {
            if (isset($note['author_id'])) {
                $user = \get_user_by('id', $note['author_id']);
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

        \wp_send_json_success(['notes' => $notes]);
    }

    /**
     * Save class note via AJAX
     *
     * @return void
     */
    public static function saveClassNote(): void
    {
        // Verify nonce
        if (!\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            \wp_send_json_error('Invalid nonce');
        }

        $class_id = intval($_POST['class_id']);
        if (!$class_id) {
            \wp_send_json_error('Invalid class ID');
        }

        $note_data = $_POST['note'];
        if (!$note_data) {
            \wp_send_json_error('No note data provided');
        }

        // Validate note data
        $note = [
            'id' => !empty($note_data['id']) ? \sanitize_text_field($note_data['id']) : uniqid('note_'),
            'content' => stripslashes(\sanitize_textarea_field($note_data['content'])),
            'category' => isset($note_data['category']) && is_array($note_data['category']) ?
                array_map('sanitize_text_field', $note_data['category']) :
                [\sanitize_text_field($note_data['category'] ?? '')],
            'priority' => \sanitize_text_field($note_data['priority'] ?? ''),
            'author_id' => \get_current_user_id(),
            'created_at' => isset($note_data['created_at']) ? $note_data['created_at'] : date('c'),
            'updated_at' => date('c'),
            'attachments' => isset($note_data['attachments']) ? $note_data['attachments'] : []
        ];

        // Basic validation
        if (empty($note['content'])) {
            \wp_send_json_error('Note content is required');
        }

        if (empty($note['category']) || (is_array($note['category']) && count($note['category']) === 0)) {
            \wp_send_json_error('At least one class note type is required');
        }

        if (empty($note['priority'])) {
            \wp_send_json_error('Priority is required');
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
                \wp_send_json_error('Class not found');
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
                ClassRepository::clearCachedClassNotes($class_id);

                // Add author name for response
                $user = \get_user_by('id', $note['author_id']);
                $note['author_name'] = $user ? $user->display_name : 'Unknown';

                \wp_send_json_success(['note' => $note]);
            } else {
                \wp_send_json_error('Failed to save note');
            }

        } catch (\PDOException $e) {
            \wp_send_json_error('Database error: Failed to save note');
        }
    }

    /**
     * Delete class note via AJAX
     *
     * @return void
     */
    public static function deleteClassNote(): void
    {
        // Verify nonce
        if (!\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            \wp_send_json_error('Invalid nonce');
        }

        $class_id = intval($_POST['class_id'] ?? 0);
        $note_id = \sanitize_text_field($_POST['note_id'] ?? '');

        if (!$class_id) {
            \wp_send_json_error('Invalid class ID');
        }

        if (!isset($_POST['note_id'])) {
            \wp_send_json_error('Note ID not provided');
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
                \wp_send_json_error('Class not found');
            }

            // Parse existing notes
            $notes = [];
            if (!empty($result['class_notes_data'])) {
                $notes_data = json_decode($result['class_notes_data'], true);
                if (is_array($notes_data)) {
                    $notes = $notes_data;
                }
            }

            // Find and remove the note
            $note_found = false;
            foreach ($notes as $index => $existing_note) {
                if ($existing_note['id'] === $note_id) {
                    unset($notes[$index]);
                    $note_found = true;
                    break;
                }
            }

            if (!$note_found) {
                \wp_send_json_error('Note not found');
            }

            // Re-index array after removal
            $notes = array_values($notes);

            // Update PostgreSQL database with JSONB
            $notes_json = json_encode($notes);
            $update_stmt = $pdo->prepare("UPDATE public.classes SET class_notes_data = :notes_data, updated_at = NOW() WHERE class_id = :class_id");
            $update_stmt->bindParam(':notes_data', $notes_json, \PDO::PARAM_STR);
            $update_stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $update_result = $update_stmt->execute();

            if ($update_result) {
                // Clear cache after successful delete
                ClassRepository::clearCachedClassNotes($class_id);

                \wp_send_json_success(['message' => 'Note deleted successfully']);
            } else {
                \wp_send_json_error('Failed to delete note');
            }

        } catch (\PDOException $e) {
            \wp_send_json_error('Database error: Failed to delete note');
        }
    }

    /**
     * AJAX: Upload attachment to WordPress media library
     *
     * @return void
     */
    public static function uploadAttachment(): void
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'wecoza_class_nonce')) {
            \wp_send_json_error('Invalid security token');
            return;
        }

        // Check if user can upload files
        if (!\current_user_can('upload_files')) {
            \wp_send_json_error('You do not have permission to upload files');
            return;
        }

        // Check if file was uploaded
        if (empty($_FILES['file'])) {
            \wp_send_json_error('No file uploaded');
            return;
        }

        $file = $_FILES['file'];
        $context = isset($_POST['context']) ? \sanitize_text_field($_POST['context']) : 'general';

        // Validate file type
        $allowed_types = [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg', 'image/png'
        ];

        $file_type = \wp_check_filetype($file['name']);
        if (!in_array($file['type'], $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
            \wp_send_json_error('Invalid file type');
            return;
        }

        // Validate file size (10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            \wp_send_json_error('File size must be less than 10MB');
            return;
        }

        // Handle the upload using WordPress functions
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Set custom upload directory
        \add_filter('upload_dir', [__CLASS__, 'customUploadDir']);

        // Move uploaded file to uploads directory
        $upload_overrides = array('test_form' => false);
        $movefile = \wp_handle_upload($file, $upload_overrides);

        // Remove custom upload directory filter
        \remove_filter('upload_dir', [__CLASS__, 'customUploadDir']);

        if ($movefile && !isset($movefile['error'])) {
            // File uploaded successfully, now create attachment
            $filename = $movefile['file'];
            $filetype = \wp_check_filetype(basename($filename), null);
            $wp_upload_dir = \wp_upload_dir();

            // Prepare attachment data
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            // Insert the attachment
            $attach_id = \wp_insert_attachment($attachment, $filename);

            if (!\is_wp_error($attach_id)) {
                // Generate metadata for the attachment
                $attach_data = \wp_generate_attachment_metadata($attach_id, $filename);
                \wp_update_attachment_metadata($attach_id, $attach_data);

                // Add custom meta for context
                \update_post_meta($attach_id, '_wecoza_context', $context);

                \wp_send_json_success([
                    'id' => $attach_id,
                    'url' => \wp_get_attachment_url($attach_id),
                    'title' => \get_the_title($attach_id),
                    'filename' => basename($filename),
                    'filesize' => filesize($filename),
                    'filetype' => $filetype['type']
                ]);
            } else {
                \wp_send_json_error('Failed to create attachment');
            }
        } else {
            // Handle error
            $error = isset($movefile['error']) ? $movefile['error'] : 'File upload failed';
            \wp_send_json_error($error);
        }
    }

    /**
     * Custom upload directory for class-related files
     *
     * @param array $upload Upload directory info
     * @return array Modified upload directory info
     */
    public static function customUploadDir(array $upload): array
    {
        $upload['subdir'] = '/wecoza-classes' . $upload['subdir'];
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];

        return $upload;
    }
}
