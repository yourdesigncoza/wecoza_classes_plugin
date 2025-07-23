<?php
/**
 * QAController.php
 *
 * Controller for handling QA analytics dashboard and widget operations
 */

namespace WeCozaClasses\Controllers;

use WeCozaClasses\Models\QAModel;
use WeCozaClasses\Services\Database\DatabaseService;

class QAController {

    /**
     * Constructor
     */
    public function __construct() {
        // Register WordPress hooks
        \add_action('init', [$this, 'registerShortcodes']);
        \add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        
        // Register AJAX handlers for QA analytics
        \add_action('wp_ajax_get_qa_analytics', [__CLASS__, 'getQAAnalytics']);
        \add_action('wp_ajax_nopriv_get_qa_analytics', [__CLASS__, 'getQAAnalytics']);
        \add_action('wp_ajax_get_qa_summary', [__CLASS__, 'getQASummary']);
        \add_action('wp_ajax_nopriv_get_qa_summary', [__CLASS__, 'getQASummary']);
        \add_action('wp_ajax_get_qa_visits', [__CLASS__, 'getQAVisits']);
        \add_action('wp_ajax_nopriv_get_qa_visits', [__CLASS__, 'getQAVisits']);
        \add_action('wp_ajax_create_qa_visit', [__CLASS__, 'createQAVisit']);
        \add_action('wp_ajax_nopriv_create_qa_visit', [__CLASS__, 'createQAVisit']);
        \add_action('wp_ajax_export_qa_reports', [__CLASS__, 'exportQAReports']);
        \add_action('wp_ajax_nopriv_export_qa_reports', [__CLASS__, 'exportQAReports']);
        
        // Register admin menu for QA dashboard
        \add_action('admin_menu', [$this, 'addQADashboardMenu']);
    }

    /**
     * Register shortcodes
     */
    public function registerShortcodes() {
        \add_shortcode('qa_dashboard_widget', [$this, 'renderQADashboardWidget']);
        \add_shortcode('qa_analytics_dashboard', [$this, 'renderQAAnalyticsDashboard']);
    }

    /**
     * Enqueue necessary assets for QA dashboard
     */
    public function enqueueAssets() {
        // Enqueue Chart.js for analytics
        \wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0', true);
        
        // Enqueue QA dashboard scripts (styles are in theme)
        \wp_enqueue_script('qa-dashboard-scripts', plugin_dir_url(__FILE__) . '../../assets/js/qa-dashboard.js', ['jquery', 'chartjs'], '1.0.0', true);
        
        // Localize script with AJAX URL and nonce
        \wp_localize_script('qa-dashboard-scripts', 'qaAjax', [
            'url' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('qa_dashboard_nonce')
        ]);
    }

    /**
     * Add QA dashboard menu to WordPress admin
     */
    public function addQADashboardMenu() {
        \add_menu_page(
            'QA Analytics Dashboard',
            'QA Analytics',
            'manage_options',
            'qa-analytics-dashboard',
            [$this, 'renderQAAnalyticsDashboard'],
            'dashicons-chart-area',
            6
        );
    }

    /**
     * Render QA analytics dashboard
     */
    public function renderQAAnalyticsDashboard() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        
        // Include the dashboard view
        include plugin_dir_path(__FILE__) . '../Views/qa-analytics-dashboard.php';
    }

    /**
     * Render QA dashboard widget shortcode
     */
    public function renderQADashboardWidget($atts) {
        $atts = shortcode_atts([
            'show_charts' => 'true',
            'show_summary' => 'true',
            'limit' => '5'
        ], $atts);
        
        ob_start();
        include plugin_dir_path(__FILE__) . '../Views/qa-dashboard-widget.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for getting QA analytics data
     */
    public static function getQAAnalytics() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'qa_dashboard_nonce')) {
            wp_die('Security check failed');
        }
        
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        $department = sanitize_text_field($_POST['department'] ?? '');
        
        $qa_model = new QAModel();
        $analytics_data = $qa_model->getAnalyticsData($start_date, $end_date, $department);
        
        wp_send_json_success($analytics_data);
    }

    /**
     * AJAX handler for getting QA summary data
     */
    public static function getQASummary() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'qa_dashboard_nonce')) {
            wp_die('Security check failed');
        }
        
        $qa_model = new QAModel();
        $summary_data = $qa_model->getSummaryData();
        
        wp_send_json_success($summary_data);
    }

    /**
     * AJAX handler for getting QA visits for a specific class
     */
    public static function getQAVisits() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'qa_dashboard_nonce')) {
            wp_die('Security check failed');
        }
        
        $class_id = intval($_POST['class_id'] ?? 0);
        
        if (!$class_id) {
            wp_send_json_error('Invalid class ID');
        }
        
        $qa_model = new QAModel();
        $visits = $qa_model->getVisitsByClass($class_id);
        
        wp_send_json_success($visits);
    }

    /**
     * AJAX handler for creating a new QA visit
     */
    public static function createQAVisit() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'qa_dashboard_nonce')) {
            wp_die('Security check failed');
        }
        
        $visit_data = [
            'class_id' => intval($_POST['class_id'] ?? 0),
            'visit_date' => sanitize_text_field($_POST['visit_date'] ?? ''),
            'visit_time' => sanitize_text_field($_POST['visit_time'] ?? ''),
            'visit_type' => sanitize_text_field($_POST['visit_type'] ?? 'routine'),
            'qa_officer_id' => intval($_POST['qa_officer_id'] ?? 0),
            'visit_duration' => intval($_POST['visit_duration'] ?? 0),
            'overall_rating' => intval($_POST['overall_rating'] ?? 0),
            'attendance_count' => intval($_POST['attendance_count'] ?? 0),
            'instructor_present' => filter_var($_POST['instructor_present'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'equipment_status' => sanitize_text_field($_POST['equipment_status'] ?? ''),
            'venue_condition' => sanitize_text_field($_POST['venue_condition'] ?? ''),
            'safety_compliance' => filter_var($_POST['safety_compliance'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'findings' => json_decode(stripslashes($_POST['findings'] ?? '[]'), true),
            'recommendations' => json_decode(stripslashes($_POST['recommendations'] ?? '[]'), true),
            'action_items' => json_decode(stripslashes($_POST['action_items'] ?? '[]'), true),
            'visit_notes' => sanitize_textarea_field($_POST['visit_notes'] ?? ''),
            'follow_up_required' => filter_var($_POST['follow_up_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'follow_up_date' => sanitize_text_field($_POST['follow_up_date'] ?? ''),
            'created_by' => get_current_user_id()
        ];
        
        $qa_model = new QAModel();
        $result = $qa_model->createVisit($visit_data);
        
        if ($result) {
            wp_send_json_success(['message' => 'QA visit created successfully', 'visit_id' => $result]);
        } else {
            wp_send_json_error('Failed to create QA visit');
        }
    }

    /**
     * AJAX handler for exporting QA reports
     */
    public static function exportQAReports() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'qa_dashboard_nonce')) {
            wp_die('Security check failed');
        }
        
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        
        $qa_model = new QAModel();
        $export_data = $qa_model->getExportData($start_date, $end_date);
        
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="qa-reports-' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Write CSV header
            fputcsv($output, ['Visit ID', 'Class ID', 'Visit Date', 'Officer', 'Rating', 'Duration', 'Notes']);
            
            // Write data rows
            foreach ($export_data as $row) {
                fputcsv($output, $row);
            }
            
            fclose($output);
        } else if ($format === 'pdf') {
            // PDF export would require additional library like TCPDF
            wp_send_json_error('PDF export not implemented yet');
        }
        
        exit;
    }
}