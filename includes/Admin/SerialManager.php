<?php
/**
 * Serial number management
 *
 * @package WPProductVerification\Admin
 */

namespace WPProductVerification\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SerialManager class for handling serial number operations
 */
class SerialManager {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_post_wpv_add_serial', [$this, 'add_serial']);
        add_action('admin_post_wpv_delete_serial', [$this, 'delete_serial']);
        add_action('admin_post_wpv_generate_serials', [$this, 'generate_serials']);
        add_action('admin_post_wpv_bulk_delete_serials', [$this, 'bulk_delete_serials']);
    }

    /**
     * Add new serial number
     *
     * @return void
     */
    public function add_serial(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_add_serial', 'wpv_serial_nonce');

        $product_id = absint($_POST['product_id'] ?? 0);
        $serial_number = strtoupper(sanitize_text_field($_POST['serial_number'] ?? ''));
        $max_verifications = absint($_POST['max_verifications'] ?? Settings::get_setting('max_verifications', 1));

        if ($product_id === 0 || empty($serial_number)) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'error' => 'missing_fields',
            ], admin_url('admin.php')));
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wpv_serials';

        $result = $wpdb->insert(
            $table,
            [
                'product_id' => $product_id,
                'serial_number' => $serial_number,
                'max_verifications' => $max_verifications,
            ],
            ['%d', '%s', '%d']
        );

        if ($result === false) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'error' => 'db_error',
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'added' => 'true',
            ], admin_url('admin.php')));
        }
        exit;
    }

    /**
     * Generate multiple serial numbers
     *
     * @return void
     */
    public function generate_serials(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_generate_serials', 'wpv_serial_nonce');

        $product_id = absint($_POST['product_id'] ?? 0);
        $quantity = absint($_POST['quantity'] ?? 0);
        $max_verifications = absint($_POST['max_verifications'] ?? Settings::get_setting('max_verifications', 1));

        if ($product_id === 0 || $quantity === 0) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'error' => 'missing_fields',
            ], admin_url('admin.php')));
            exit;
        }

        $product = ProductManager::get_product($product_id);
        if (!$product) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'error' => 'invalid_product',
            ], admin_url('admin.php')));
            exit;
        }

        $generated = 0;
        for ($i = 0; $i < $quantity; $i++) {
            $serial = self::generate_serial_number($product->prefix);
            if (self::add_serial_to_db($product_id, $serial, $max_verifications)) {
                $generated++;
            }
        }

        wp_redirect(add_query_arg([
            'page' => 'wp-product-verification-serials',
            'generated' => $generated,
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Delete serial number
     *
     * @return void
     */
    public function delete_serial(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_delete_serial_' . $_GET['id']);

        $id = absint($_GET['id'] ?? 0);

        if ($id === 0) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'error' => 'invalid_id',
            ], admin_url('admin.php')));
            exit;
        }

        global $wpdb;
        $serials_table = $wpdb->prefix . 'wpv_serials';
        $logs_table = $wpdb->prefix . 'wpv_verification_logs';

        $wpdb->delete($logs_table, ['serial_id' => $id], ['%d']);
        $wpdb->delete($serials_table, ['id' => $id], ['%d']);

        wp_redirect(add_query_arg([
            'page' => 'wp-product-verification-serials',
            'deleted' => 'true',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Bulk delete serial numbers
     *
     * @return void
     */
    public function bulk_delete_serials(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_bulk_serials', 'wpv_bulk_nonce');

        $serial_ids = isset($_POST['serial_ids']) ? array_map('absint', (array) $_POST['serial_ids']) : [];

        if (empty($serial_ids)) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-serials',
                'error' => 'no_selection',
            ], admin_url('admin.php')));
            exit;
        }

        global $wpdb;
        $serials_table = $wpdb->prefix . 'wpv_serials';
        $logs_table = $wpdb->prefix . 'wpv_verification_logs';

        $deleted_count = 0;

        foreach ($serial_ids as $id) {
            if ($id > 0) {
                $wpdb->delete($logs_table, ['serial_id' => $id], ['%d']);
                $result = $wpdb->delete($serials_table, ['id' => $id], ['%d']);

                if ($result !== false) {
                    $deleted_count++;
                }
            }
        }

        wp_redirect(add_query_arg([
            'page' => 'wp-product-verification-serials',
            'bulk_deleted' => $deleted_count,
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Generate a unique serial number
     *
     * @param string $prefix Product prefix
     * @return string Generated serial number
     */
    public static function generate_serial_number(string $prefix): string {
        $settings = Settings::get_settings();
        $length = (int) $settings['serial_length'];
        $separator = (string) $settings['separator'];
        $interval = (int) $settings['separator_interval'];

        // Calculate remaining length after prefix
        $prefix_length = strlen($prefix);
        $remaining_length = max(0, $length - $prefix_length);

        // Generate random alphanumeric string
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_part = '';

        for ($i = 0; $i < $remaining_length; $i++) {
            $random_part .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Combine prefix and random part
        $full_serial = $prefix . $random_part;

        // Add separators
        if (!empty($separator) && $interval > 0) {
            $parts = str_split($full_serial, $interval);
            $full_serial = implode($separator, $parts);
        }

        return $full_serial;
    }

    /**
     * Add serial to database
     *
     * @param int $product_id Product ID
     * @param string $serial_number Serial number
     * @param int $max_verifications Max verifications
     * @return bool Success status
     */
    public static function add_serial_to_db(int $product_id, string $serial_number, int $max_verifications): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'wpv_serials';

        // Check if serial already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE serial_number = %s",
            $serial_number
        ));

        if ($exists > 0) {
            return false;
        }

        $result = $wpdb->insert(
            $table,
            [
                'product_id' => $product_id,
                'serial_number' => $serial_number,
                'max_verifications' => $max_verifications,
            ],
            ['%d', '%s', '%d']
        );

        return $result !== false;
    }

    /**
     * Get all serial numbers
     *
     * @param int|null $product_id Optional product ID filter
     * @param int $limit Number of results to return
     * @param int $offset Offset for pagination
     * @return array<int, object> Serial numbers array
     */
    public static function get_serials(?int $product_id = null, int $limit = 50, int $offset = 0): array {
        global $wpdb;
        $serials_table = $wpdb->prefix . 'wpv_serials';
        $products_table = $wpdb->prefix . 'wpv_products';

        $sql = "SELECT s.*, p.name as product_name, p.prefix as product_prefix
                FROM {$serials_table} s
                LEFT JOIN {$products_table} p ON s.product_id = p.id";

        if ($product_id !== null) {
            $sql .= $wpdb->prepare(" WHERE s.product_id = %d", $product_id);
        }

        $sql .= " ORDER BY s.created_at DESC LIMIT %d OFFSET %d";

        $results = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
        return $results ?: [];
    }

    /**
     * Get serial count
     *
     * @param int|null $product_id Optional product ID filter
     * @return int Serial count
     */
    public static function get_serial_count(?int $product_id = null): int {
        global $wpdb;
        $table = $wpdb->prefix . 'wpv_serials';

        if ($product_id !== null) {
            return (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE product_id = %d",
                $product_id
            ));
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * Get serial by number
     *
     * @param string $serial_number Serial number
     * @return object|null Serial object or null
     */
    public static function get_serial_by_number(string $serial_number): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'wpv_serials';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE serial_number = %s",
            $serial_number
        ));
    }
}
