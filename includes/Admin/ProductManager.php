<?php
/**
 * Product management
 *
 * @package WPProductVerification\Admin
 */

namespace WPProductVerification\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ProductManager class for handling product operations
 */
class ProductManager {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_post_wpv_add_product', [$this, 'add_product']);
        add_action('admin_post_wpv_edit_product', [$this, 'edit_product']);
        add_action('admin_post_wpv_delete_product', [$this, 'delete_product']);
    }

    /**
     * Add new product
     *
     * @return void
     */
    public function add_product(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_add_product', 'wpv_product_nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'wpv_products';

        $name = sanitize_text_field($_POST['product_name'] ?? '');
        $prefix = strtoupper(sanitize_text_field($_POST['product_prefix'] ?? ''));
        $description = sanitize_textarea_field($_POST['product_description'] ?? '');

        if (empty($name) || empty($prefix)) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'error' => 'missing_fields',
            ], admin_url('admin.php')));
            exit;
        }

        $result = $wpdb->insert(
            $table,
            [
                'name' => $name,
                'prefix' => $prefix,
                'description' => $description,
            ],
            ['%s', '%s', '%s']
        );

        if ($result === false) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'error' => 'db_error',
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'added' => 'true',
            ], admin_url('admin.php')));
        }
        exit;
    }

    /**
     * Edit existing product
     *
     * @return void
     */
    public function edit_product(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_edit_product', 'wpv_product_nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'wpv_products';

        $id = absint($_POST['product_id'] ?? 0);
        $name = sanitize_text_field($_POST['product_name'] ?? '');
        $prefix = strtoupper(sanitize_text_field($_POST['product_prefix'] ?? ''));
        $description = sanitize_textarea_field($_POST['product_description'] ?? '');

        if ($id === 0 || empty($name) || empty($prefix)) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'error' => 'missing_fields',
            ], admin_url('admin.php')));
            exit;
        }

        $result = $wpdb->update(
            $table,
            [
                'name' => $name,
                'prefix' => $prefix,
                'description' => $description,
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'error' => 'db_error',
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'updated' => 'true',
            ], admin_url('admin.php')));
        }
        exit;
    }

    /**
     * Delete product
     *
     * @return void
     */
    public function delete_product(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_delete_product_' . $_GET['id']);

        $id = absint($_GET['id'] ?? 0);

        if ($id === 0) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-products',
                'error' => 'invalid_id',
            ], admin_url('admin.php')));
            exit;
        }

        global $wpdb;

        // Delete associated serials and logs
        $serials_table = $wpdb->prefix . 'wpv_serials';
        $logs_table = $wpdb->prefix . 'wpv_verification_logs';
        $products_table = $wpdb->prefix . 'wpv_products';

        $wpdb->delete($logs_table, ['product_id' => $id], ['%d']);
        $wpdb->delete($serials_table, ['product_id' => $id], ['%d']);
        $wpdb->delete($products_table, ['id' => $id], ['%d']);

        wp_redirect(add_query_arg([
            'page' => 'wp-product-verification-products',
            'deleted' => 'true',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Get all products
     *
     * @return array<int, object> Products array
     */
    public static function get_products(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'wpv_products';

        $results = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");
        return $results ?: [];
    }

    /**
     * Get product by ID
     *
     * @param int $id Product ID
     * @return object|null Product object or null
     */
    public static function get_product(int $id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'wpv_products';

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
    }

    /**
     * Get product by prefix
     *
     * @param string $prefix Product prefix
     * @return object|null Product object or null
     */
    public static function get_product_by_prefix(string $prefix): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'wpv_products';

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE prefix = %s", $prefix));
    }
}
