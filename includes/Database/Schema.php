<?php
/**
 * Database schema management
 *
 * @package WPProductVerification\Database
 */

namespace WPProductVerification\Database;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schema class for database table creation and management
 */
class Schema {
    /**
     * Create all plugin database tables
     *
     * @return void
     */
    public static function create_tables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Products table
        $products_table = $wpdb->prefix . 'wpv_products';
        $products_sql = "CREATE TABLE IF NOT EXISTS {$products_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            prefix varchar(50) NOT NULL,
            description text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY prefix (prefix)
        ) {$charset_collate};";

        // Serial numbers table
        $serials_table = $wpdb->prefix . 'wpv_serials';
        $serials_sql = "CREATE TABLE IF NOT EXISTS {$serials_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            serial_number varchar(255) NOT NULL,
            verification_count int(11) NOT NULL DEFAULT 0,
            max_verifications int(11) NOT NULL DEFAULT 1,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY serial_number (serial_number),
            KEY product_id (product_id),
            KEY status (status)
        ) {$charset_collate};";

        // Verification logs table
        $logs_table = $wpdb->prefix . 'wpv_verification_logs';
        $logs_sql = "CREATE TABLE IF NOT EXISTS {$logs_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            serial_id bigint(20) unsigned NOT NULL,
            product_id bigint(20) unsigned NOT NULL,
            ip_address varchar(45),
            user_agent text,
            verified_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY serial_id (serial_id),
            KEY product_id (product_id),
            KEY verified_at (verified_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($products_sql);
        dbDelta($serials_sql);
        dbDelta($logs_sql);

        // Set default options if not exist
        if (!get_option('wpv_settings')) {
            add_option('wpv_settings', [
                'max_verifications' => 1,
                'serial_length' => 16,
                'separator' => '-',
                'separator_interval' => 4,
                'notification_emails' => get_option('admin_email'),
            ]);
        }
    }

    /**
     * Drop all plugin database tables
     *
     * @return void
     */
    public static function drop_tables(): void {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'wpv_verification_logs',
            $wpdb->prefix . 'wpv_serials',
            $wpdb->prefix . 'wpv_products',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
