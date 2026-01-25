<?php
/**
 * Application configuration for WeCoza Classes Plugin
 *
 * @package WeCozaClasses
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

return array(
    /**
     * Plugin Information
     */
    'name' => 'WeCoza Classes Plugin',
    'version' => WECOZA_CLASSES_VERSION,
    'description' => 'A comprehensive class management system for WeCoza training programs.',
    'author' => 'Your Design Co',
    'author_uri' => 'https://yourdesign.co.za',
    'text_domain' => 'wecoza-classes',

    /**
     * Plugin Settings
     */
    'settings' => array(
        'enable_debug' => defined('WP_DEBUG') && WP_DEBUG,
        'enable_logging' => true,
        'cache_duration' => 3600, // 1 hour
        'max_upload_size' => 10485760, // 10MB
        'allowed_file_types' => array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'),
    ),

    /**
     * Database Configuration
     */
    'database' => array(
        'use_postgresql' => true,
        // 'table_prefix' => 'wecoza_classes_',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci',
        // PostgreSQL connection settings (can be overridden via WordPress options)
        'postgresql' => array(
            'host' => get_option('wecoza_postgres_host', ''),
            'port' => get_option('wecoza_postgres_port', ''),
            'dbname' => get_option('wecoza_postgres_dbname', ''),
            'user' => get_option('wecoza_postgres_user', ''),
            'password' => get_option('wecoza_postgres_password', ''),
        ),
    ),

    /**
     * Controllers to initialize
     */
    'controllers' => array(
        'WeCozaClasses\\Controllers\\ClassController',
        'WeCozaClasses\\Controllers\\ClassAjaxController',
        'WeCozaClasses\\Controllers\\ClassTypesController',
        'WeCozaClasses\\Controllers\\PublicHolidaysController',
        'WeCozaClasses\\Controllers\\QAController',
    ),

    /**
     * Shortcodes configuration
     */
    'shortcodes' => array(
        'wecoza_capture_class' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassController',
            'method' => 'captureClassShortcode',
            'description' => 'Display class capture form',
        ),
        'wecoza_display_classes' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassController',
            'method' => 'displayClassesShortcode',
            'description' => 'Display all classes in a table',
        ),
        'wecoza_display_single_class' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassController',
            'method' => 'displaySingleClassShortcode',
            'description' => 'Display single class details',
        ),
        'qa_dashboard_widget' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'renderQADashboardWidget',
            'description' => 'Display QA dashboard widget for administrator homepage',
        ),
        'qa_analytics_dashboard' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'renderQAAnalyticsDashboard',
            'description' => 'Display full QA analytics dashboard',
        ),
    ),

    /**
     * AJAX endpoints configuration
     *
     * Note: AJAX handlers are now auto-registered in their respective controllers:
     * - ClassAjaxController: save_class, delete_class, get_calendar_events, get_class_subjects,
     *                        get_class_notes, save_class_note, delete_class_note, upload_attachment
     * - QAController: delete_qa_report, get_class_qa_data, submit_qa_question,
     *                 get_qa_analytics, get_qa_summary, get_qa_visits, create_qa_visit, export_qa_reports
     * - PublicHolidaysController: get_public_holidays
     *
     * This configuration is kept for reference and backward compatibility.
     */
    'ajax_endpoints' => array(
        'save_class' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'saveClassAjax',
            'public' => false,
        ),
        'update_class' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'saveClassAjax',
            'public' => false,
        ),
        'delete_class' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'deleteClassAjax',
            'public' => false,
        ),
        'get_class_subjects' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'getClassSubjectsAjax',
            'public' => true,
        ),
        'get_public_holidays' => array(
            'controller' => 'WeCozaClasses\\Controllers\\PublicHolidaysController',
            'method' => 'getPublicHolidays',
            'public' => true,
        ),
        'get_calendar_events' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'getCalendarEventsAjax',
            'public' => true,
        ),
        'get_class_notes' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'getClassNotes',
            'public' => false,
        ),
        'save_class_note' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'saveClassNote',
            'public' => false,
        ),
        'delete_qa_report' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'deleteQAReport',
            'public' => false,
        ),
        'get_class_qa_data' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'getClassQAData',
            'public' => false,
        ),
        'delete_class_note' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'deleteClassNote',
            'public' => false,
        ),
        'submit_qa_question' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'submitQAQuestion',
            'public' => false,
        ),
        'upload_attachment' => array(
            'controller' => 'WeCozaClasses\\Controllers\\ClassAjaxController',
            'method' => 'uploadAttachment',
            'public' => false,
        ),
        'get_qa_analytics' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'getQAAnalytics',
            'public' => false,
        ),
        'get_qa_summary' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'getQASummary',
            'public' => false,
        ),
        'get_qa_visits' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'getQAVisits',
            'public' => false,
        ),
        'create_qa_visit' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'createQAVisit',
            'public' => false,
        ),
        'export_qa_reports' => array(
            'controller' => 'WeCozaClasses\\Controllers\\QAController',
            'method' => 'exportQAReports',
            'public' => false,
        ),
    ),

    /**
     * Asset configuration
     */
    'assets' => array(
        'js' => array(
            'public' => array(
                'class-capture.js',
                'class-schedule-form.js',
                'learner-level-utils.js',
                'class-types.js',
                'wecoza-calendar.js',
                'classes-table-search.js',
            ),
            'admin' => array(
                'wecoza-classes-admin.js',
            ),
        ),
    ),

    /**
     * Capabilities configuration
     */
    'capabilities' => array(
        'manage_classes' => array(
            'administrator',
            'editor',
        ),
        'create_classes' => array(
            'administrator',
            'editor',
            'author',
        ),
        'edit_classes' => array(
            'administrator',
            'editor',
        ),
        'delete_classes' => array(
            'administrator',
        ),
        'view_reports' => array(
            'administrator',
            'editor',
            'author',
            'contributor',
        ),
    ),

    /**
     * Calendar configuration
     */
    'calendar' => array(
        'enable_fullcalendar' => true,
        'default_view' => 'dayGridMonth',
        'enable_public_holidays' => true,
        'holiday_api_enabled' => true,
        'time_format' => 'H:mm',
        'date_format' => 'Y-m-d',
    ),

    /**
     * Form validation rules
     */
    'validation' => array(
        'class_code' => array(
            'required' => true,
            'max_length' => 50,
            'pattern' => '/^[A-Z0-9\-]+$/',
        ),
        'class_duration' => array(
            'required' => true,
            'min' => 1,
            'max' => 365,
            'type' => 'integer',
        ),
        'original_start_date' => array(
            'required' => true,
            'type' => 'date',
            'min_date' => 'today',
        ),
    ),

    /**
     * Email configuration
     */
    'email' => array(
        'enable_notifications' => true,
        'from_email' => get_option('admin_email'),
        'from_name' => get_bloginfo('name'),
        'templates_path' => 'emails/',
    ),

    /**
     * File upload configuration
     */
    'uploads' => array(
        'max_file_size' => 10485760, // 10MB
        'allowed_types' => array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'),
        'upload_path' => 'wecoza-classes/',
        'secure_uploads' => true,
    ),

    /**
     * Cache configuration
     */
    'cache' => array(
        'enable_caching' => true,
        'default_expiration' => 3600, // 1 hour
        'cache_groups' => array(
            'classes' => 1800, // 30 minutes
            'class_types' => 7200, // 2 hours
            'public_holidays' => 86400, // 24 hours
        ),
    ),
);
