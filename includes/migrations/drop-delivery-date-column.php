<?php
/**
 * Database migration to drop the delivery_date column
 * Delivery dates are now tracked via the event_dates JSONB field with type="Deliveries"
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Drop the delivery_date column from the classes table
 * This migration removes the deprecated delivery_date column
 * Deliveries are now tracked in the event_dates JSONB array
 */
function wecoza_classes_drop_delivery_date_column() {
    try {
        // Get database service
        $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();
        $pdo = $db->getPdo();

        // Check if column exists before attempting to drop
        $columnExists = $pdo->query("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
            AND table_name = 'classes'
            AND column_name = 'delivery_date'
        ")->fetchColumn();

        if (!$columnExists) {
            error_log('WeCoza Classes Plugin: delivery_date column already removed, skipping');
            return true;
        }

        // Drop the delivery_date column
        $sql = "ALTER TABLE public.classes DROP COLUMN IF EXISTS delivery_date";
        $pdo->exec($sql);

        error_log('WeCoza Classes Plugin: Successfully dropped delivery_date column');
        return true;

    } catch (\Exception $e) {
        error_log('WeCoza Classes Plugin: Failed to drop delivery_date column: ' . $e->getMessage());
        return false;
    }
}

// Run migration if called directly (for manual execution)
if (defined('WECOZA_RUN_MIGRATION') && WECOZA_RUN_MIGRATION === 'drop_delivery_date') {
    wecoza_classes_drop_delivery_date_column();
}
