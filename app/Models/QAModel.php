<?php
/**
 * QAModel.php
 *
 * QA model for handling QA analytics and visit data
 */

namespace WeCozaClasses\Models;

use WeCozaClasses\Services\Database\DatabaseService;

class QAModel {

    private $db;

    public function __construct() {
        $this->db = new DatabaseService();
    }

    /**
     * Get analytics data for QA dashboard
     */
    public function getAnalyticsData($start_date = '', $end_date = '', $department = '') {
        $data = [];
        
        // Default date range if not provided
        if (empty($start_date)) {
            $start_date = date('Y-m-01', strtotime('-6 months'));
        }
        if (empty($end_date)) {
            $end_date = date('Y-m-t');
        }
        
        // Get monthly visit completion rates
        $data['monthly_rates'] = $this->getMonthlyCompletionRates($start_date, $end_date);
        
        // Get average ratings by class/department
        $data['average_ratings'] = $this->getAverageRatings($start_date, $end_date, $department);
        
        // Get officer performance metrics
        $data['officer_performance'] = $this->getOfficerPerformance($start_date, $end_date);
        
        // Get trending issues
        $data['trending_issues'] = $this->getTrendingIssues($start_date, $end_date);
        
        // Get overall statistics
        $data['overall_stats'] = $this->getOverallStats($start_date, $end_date);
        
        return $data;
    }

    /**
     * Get summary data for QA dashboard widget
     */
    public function getSummaryData() {
        $data = [];
        
        // Get recent visits
        $data['recent_visits'] = $this->getRecentVisits(5);
        
        // Get key metrics
        $data['key_metrics'] = $this->getKeyMetrics();
        
        // Get alerts/issues requiring attention
        $data['alerts'] = $this->getAlerts();
        
        return $data;
    }

    /**
     * Get visits for a specific class
     */
    public function getVisitsByClass($class_id) {
        // For now, use existing qa_reports table structure
        // Will be enhanced when advanced schema is implemented
        $query = "
            SELECT 
                qr.qa_report_id,
                qr.class_id,
                qr.report_date as visit_date,
                qr.notes,
                qr.created_at,
                c.class_code,
                c.class_subject
            FROM qa_reports qr
            JOIN classes c ON qr.class_id = c.class_id
            WHERE qr.class_id = $1
            ORDER BY qr.report_date DESC
        ";
        
        return $this->db->query($query, [$class_id]);
    }

    /**
     * Create a new QA visit
     */
    public function createVisit($visit_data) {
        // For now, create basic qa_reports entry
        // Will be enhanced when advanced schema is implemented
        $query = "
            INSERT INTO qa_reports (class_id, report_date, notes, created_at, updated_at)
            VALUES ($1, $2, $3, NOW(), NOW())
            RETURNING qa_report_id
        ";
        
        $result = $this->db->query($query, [
            $visit_data['class_id'],
            $visit_data['visit_date'],
            $visit_data['visit_notes'] ?? ''
        ]);
        
        return $result[0]['qa_report_id'] ?? null;
    }

    /**
     * Get monthly completion rates
     */
    private function getMonthlyCompletionRates($start_date, $end_date) {
        // Using existing qa_reports table for now
        $query = "
            SELECT 
                DATE_TRUNC('month', report_date) as month,
                COUNT(*) as total_visits,
                COUNT(DISTINCT class_id) as classes_visited
            FROM qa_reports
            WHERE report_date BETWEEN $1 AND $2
            GROUP BY DATE_TRUNC('month', report_date)
            ORDER BY month ASC
        ";
        
        return $this->db->query($query, [$start_date, $end_date]);
    }

    /**
     * Get average ratings by class/department
     */
    private function getAverageRatings($start_date, $end_date, $department = '') {
        // Placeholder implementation - will be enhanced with proper schema
        $query = "
            SELECT 
                c.class_subject,
                c.class_type,
                COUNT(qr.qa_report_id) as visit_count,
                4.2 as avg_rating -- Placeholder until rating system is implemented
            FROM classes c
            LEFT JOIN qa_reports qr ON c.class_id = qr.class_id
            WHERE qr.report_date BETWEEN $1 AND $2
        ";
        
        $params = [$start_date, $end_date];
        
        if (!empty($department)) {
            $query .= " AND c.class_subject = $3";
            $params[] = $department;
        }
        
        $query .= " GROUP BY c.class_subject, c.class_type ORDER BY avg_rating DESC";
        
        return $this->db->query($query, $params);
    }

    /**
     * Get officer performance metrics
     */
    private function getOfficerPerformance($start_date, $end_date) {
        // Placeholder implementation using agent data
        $query = "
            SELECT 
                aqv.agent_id,
                COUNT(aqv.visit_id) as total_visits,
                COUNT(DISTINCT aqv.class_id) as unique_classes,
                4.3 as avg_performance_score -- Placeholder
            FROM agent_qa_visits aqv
            WHERE aqv.visit_date BETWEEN $1 AND $2
            GROUP BY aqv.agent_id
            ORDER BY total_visits DESC
        ";
        
        return $this->db->query($query, [$start_date, $end_date]);
    }

    /**
     * Get trending issues
     */
    private function getTrendingIssues($start_date, $end_date) {
        // Placeholder implementation - will be enhanced with findings table
        return [
            ['issue' => 'Equipment maintenance', 'count' => 12, 'trend' => 'up'],
            ['issue' => 'Attendance tracking', 'count' => 8, 'trend' => 'down'],
            ['issue' => 'Venue cleanliness', 'count' => 6, 'trend' => 'stable'],
            ['issue' => 'Safety compliance', 'count' => 4, 'trend' => 'down']
        ];
    }

    /**
     * Get overall statistics
     */
    private function getOverallStats($start_date, $end_date) {
        $query = "
            SELECT 
                COUNT(DISTINCT qr.class_id) as classes_visited,
                COUNT(qr.qa_report_id) as total_visits,
                COUNT(DISTINCT aqv.agent_id) as active_officers,
                4.1 as overall_rating -- Placeholder
            FROM qa_reports qr
            LEFT JOIN agent_qa_visits aqv ON qr.qa_report_id = aqv.qa_report_id
            WHERE qr.report_date BETWEEN $1 AND $2
        ";
        
        $result = $this->db->query($query, [$start_date, $end_date]);
        return $result[0] ?? [];
    }

    /**
     * Get recent visits for widget
     */
    private function getRecentVisits($limit = 5) {
        $query = "
            SELECT 
                qr.qa_report_id,
                qr.class_id,
                qr.report_date as visit_date,
                qr.notes,
                c.class_code,
                c.class_subject
            FROM qa_reports qr
            JOIN classes c ON qr.class_id = c.class_id
            ORDER BY qr.report_date DESC
            LIMIT $1
        ";
        
        return $this->db->query($query, [$limit]);
    }

    /**
     * Get key metrics for widget
     */
    private function getKeyMetrics() {
        $query = "
            SELECT 
                COUNT(DISTINCT qr.class_id) as classes_this_month,
                COUNT(qr.qa_report_id) as visits_this_month,
                4.2 as avg_rating_this_month -- Placeholder
            FROM qa_reports qr
            WHERE qr.report_date >= DATE_TRUNC('month', CURRENT_DATE)
        ";
        
        $result = $this->db->query($query);
        return $result[0] ?? [];
    }

    /**
     * Get alerts requiring attention
     */
    private function getAlerts() {
        // Placeholder implementation - will be enhanced with proper findings/issues tracking
        return [
            ['type' => 'warning', 'message' => '3 classes require follow-up visits'],
            ['type' => 'info', 'message' => '2 safety issues need resolution'],
            ['type' => 'success', 'message' => 'All reports up to date']
        ];
    }

    /**
     * Get export data for reports
     */
    public function getExportData($start_date, $end_date) {
        $query = "
            SELECT 
                qr.qa_report_id,
                qr.class_id,
                qr.report_date,
                'N/A' as officer, -- Placeholder until officer data is linked
                'N/A' as rating,   -- Placeholder until rating system is implemented
                'N/A' as duration, -- Placeholder until duration tracking is implemented
                qr.notes
            FROM qa_reports qr
            WHERE qr.report_date BETWEEN $1 AND $2
            ORDER BY qr.report_date DESC
        ";
        
        return $this->db->query($query, [$start_date, $end_date]);
    }
}