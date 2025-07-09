<?php
/**
 * QA Dashboard Widget View
 * 
 * Compact widget for displaying QA metrics on administrator homepage
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get widget attributes
$show_charts = filter_var($atts['show_charts'], FILTER_VALIDATE_BOOLEAN);
$show_summary = filter_var($atts['show_summary'], FILTER_VALIDATE_BOOLEAN);
$limit = intval($atts['limit']);
?>

<div class="qa-dashboard-widget">
    <div class="widget-header">
        <h3>QA Dashboard</h3>
        <div class="widget-controls">
            <button id="refresh-qa-widget" class="button button-small" title="Refresh Data">
                <span class="dashicons dashicons-update"></span>
            </button>
            <a href="<?php echo admin_url('admin.php?page=qa-analytics-dashboard'); ?>" class="button button-small">
                View Full Dashboard
            </a>
        </div>
    </div>

    <?php if ($show_summary): ?>
    <!-- Key Metrics Summary -->
    <div class="widget-metrics">
        <div class="widget-metric">
            <div class="metric-label">Classes This Month</div>
            <div class="metric-value" id="widget-classes-count">Loading...</div>
        </div>
        <div class="widget-metric">
            <div class="metric-label">Visits This Month</div>
            <div class="metric-value" id="widget-visits-count">Loading...</div>
        </div>
        <div class="widget-metric">
            <div class="metric-label">Average Rating</div>
            <div class="metric-value" id="widget-avg-rating">Loading...</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($show_charts): ?>
    <!-- Mini Chart -->
    <div class="widget-chart">
        <h4>Recent Activity</h4>
        <canvas id="widget-mini-chart"></canvas>
    </div>
    <?php endif; ?>

    <!-- Recent Visits -->
    <div class="widget-recent-visits">
        <h4>Recent QA Visits</h4>
        <div id="widget-visits-list">
            Loading recent visits...
        </div>
    </div>

    <!-- Alerts -->
    <div class="widget-alerts">
        <h4>Alerts & Notifications</h4>
        <div id="widget-alerts-list">
            Loading alerts...
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="widget-actions">
        <a href="#" class="button button-primary" id="new-qa-visit">Add QA Visit</a>
        <a href="<?php echo admin_url('admin.php?page=qa-analytics-dashboard'); ?>" class="button">View Reports</a>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize QA widget
    var qaWidget = new QAWidget();
    qaWidget.init();

    // Refresh widget on button click
    $('#refresh-qa-widget').on('click', function() {
        qaWidget.refresh();
    });

    // New QA visit modal/form
    $('#new-qa-visit').on('click', function(e) {
        e.preventDefault();
        qaWidget.showNewVisitForm();
    });
});

// QA Widget JavaScript Class
class QAWidget {
    constructor() {
        this.miniChart = null;
        this.refreshInterval = null;
    }

    init() {
        this.loadWidgetData();
        this.setupAutoRefresh();
    }

    loadWidgetData() {
        jQuery.ajax({
            url: qaAjax.url,
            type: 'POST',
            data: {
                action: 'get_qa_summary',
                nonce: qaAjax.nonce
            },
            success: (response) => {
                if (response.success) {
                    this.updateMetrics(response.data.key_metrics);
                    this.updateRecentVisits(response.data.recent_visits);
                    this.updateAlerts(response.data.alerts);
                    this.updateMiniChart(response.data.recent_visits);
                }
            },
            error: (xhr, status, error) => {
                console.error('Error loading widget data:', error);
            }
        });
    }

    updateMetrics(metrics) {
        if (!metrics) return;

        document.getElementById('widget-classes-count').textContent = metrics.classes_this_month || '0';
        document.getElementById('widget-visits-count').textContent = metrics.visits_this_month || '0';
        document.getElementById('widget-avg-rating').textContent = metrics.avg_rating_this_month || '0';
    }

    updateRecentVisits(visits) {
        if (!visits || visits.length === 0) {
            document.getElementById('widget-visits-list').innerHTML = '<p>No recent visits found.</p>';
            return;
        }

        let html = '<ul class="widget-visits-list">';
        visits.forEach(visit => {
            html += `<li class="visit-item">
                <div class="visit-date">${visit.visit_date}</div>
                <div class="visit-details">
                    <strong>${visit.class_code}</strong> - ${visit.class_subject}
                    <div class="visit-notes">${visit.notes.substring(0, 80)}...</div>
                </div>
            </li>`;
        });
        html += '</ul>';

        document.getElementById('widget-visits-list').innerHTML = html;
    }

    updateAlerts(alerts) {
        if (!alerts || alerts.length === 0) {
            document.getElementById('widget-alerts-list').innerHTML = '<p>No alerts at this time.</p>';
            return;
        }

        let html = '<ul class="widget-alerts-list">';
        alerts.forEach(alert => {
            html += `<li class="alert-item alert-${alert.type}">
                <span class="alert-icon dashicons dashicons-${this.getAlertIcon(alert.type)}"></span>
                <span class="alert-message">${alert.message}</span>
            </li>`;
        });
        html += '</ul>';

        document.getElementById('widget-alerts-list').innerHTML = html;
    }

    updateMiniChart(visits) {
        if (!visits || visits.length === 0) return;

        const ctx = document.getElementById('widget-mini-chart');
        if (!ctx) return;

        // Create simple line chart showing visit trend
        const last7Days = this.getLast7Days();
        const visitCounts = this.getVisitCountsForDays(visits, last7Days);

        if (this.miniChart) {
            this.miniChart.destroy();
        }

        this.miniChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: last7Days.map(date => date.getDate()),
                datasets: [{
                    label: 'Daily Visits',
                    data: visitCounts,
                    borderColor: '#0073aa',
                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    getLast7Days() {
        const days = [];
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            days.push(date);
        }
        return days;
    }

    getVisitCountsForDays(visits, days) {
        return days.map(day => {
            const dayStr = day.toISOString().split('T')[0];
            return visits.filter(visit => visit.visit_date === dayStr).length;
        });
    }

    getAlertIcon(type) {
        switch (type) {
            case 'warning': return 'warning';
            case 'error': return 'dismiss';
            case 'success': return 'yes';
            default: return 'info';
        }
    }

    showNewVisitForm() {
        // This would typically open a modal or navigate to a form
        // For now, just show an alert
        alert('New QA Visit form would open here. This will be implemented with a modal or separate page.');
    }

    refresh() {
        this.loadWidgetData();
    }

    setupAutoRefresh() {
        // Auto-refresh every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.loadWidgetData();
        }, 300000);
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        if (this.miniChart) {
            this.miniChart.destroy();
        }
    }
}
</script>

<style>
.qa-dashboard-widget {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    background: #f9f9f9;
}

.widget-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.widget-controls {
    display: flex;
    gap: 10px;
}

.widget-controls .button {
    padding: 4px 8px;
    font-size: 12px;
    height: auto;
    line-height: 1.4;
}

.widget-metrics {
    display: flex;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    gap: 20px;
}

.widget-metric {
    flex: 1;
    text-align: center;
}

.metric-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.metric-value {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.widget-chart {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.widget-chart h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #333;
}

.widget-chart canvas {
    height: 120px !important;
}

.widget-recent-visits,
.widget-alerts {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.widget-recent-visits h4,
.widget-alerts h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #333;
}

.widget-visits-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.visit-item {
    display: flex;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.visit-item:last-child {
    border-bottom: none;
}

.visit-date {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
    min-width: 80px;
}

.visit-details {
    flex: 1;
}

.visit-details strong {
    color: #0073aa;
}

.visit-notes {
    font-size: 12px;
    color: #666;
    margin-top: 3px;
}

.widget-alerts-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    font-size: 13px;
}

.alert-icon {
    width: 16px;
    height: 16px;
    font-size: 16px;
}

.alert-warning .alert-icon {
    color: #f56e28;
}

.alert-error .alert-icon {
    color: #dc3232;
}

.alert-success .alert-icon {
    color: #46b450;
}

.alert-info .alert-icon {
    color: #0073aa;
}

.widget-actions {
    padding: 15px 20px;
    display: flex;
    gap: 10px;
}

.widget-actions .button {
    flex: 1;
    text-align: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .widget-metrics {
        flex-direction: column;
        gap: 10px;
    }
    
    .widget-actions {
        flex-direction: column;
    }
    
    .visit-item {
        flex-direction: column;
        gap: 5px;
    }
}
</style>