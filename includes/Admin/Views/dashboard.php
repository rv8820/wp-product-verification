<?php
/**
 * Dashboard view template
 *
 * @package WPProductVerification\Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use WPProductVerification\Admin\ProductManager;
use WPProductVerification\Admin\SerialManager;

global $wpdb;

// Get statistics
$total_products = count(ProductManager::get_products());
$total_serials = SerialManager::get_serial_count();

$logs_table = $wpdb->prefix . 'wpv_verification_logs';
$total_verifications = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table}");

$serials_table = $wpdb->prefix . 'wpv_serials';
$active_serials = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$serials_table} WHERE status = 'active'");

// Get recent verifications
$recent_verifications = $wpdb->get_results(
    "SELECT l.*, s.serial_number, p.name as product_name
    FROM {$logs_table} l
    LEFT JOIN {$serials_table} s ON l.serial_id = s.id
    LEFT JOIN {$wpdb->prefix}wpv_products p ON l.product_id = p.id
    ORDER BY l.verified_at DESC
    LIMIT 10"
);
?>

<div class="wrap">
    <h1><?php echo esc_html__('Product Verification Dashboard', 'wp-product-verification'); ?></h1>

    <div class="wpv-dashboard-stats">
        <div class="wpv-stat-card">
            <div class="wpv-stat-icon dashicons dashicons-products"></div>
            <div class="wpv-stat-content">
                <div class="wpv-stat-number"><?php echo esc_html($total_products); ?></div>
                <div class="wpv-stat-label"><?php echo esc_html__('Total Products', 'wp-product-verification'); ?></div>
            </div>
        </div>

        <div class="wpv-stat-card">
            <div class="wpv-stat-icon dashicons dashicons-tag"></div>
            <div class="wpv-stat-content">
                <div class="wpv-stat-number"><?php echo esc_html($total_serials); ?></div>
                <div class="wpv-stat-label"><?php echo esc_html__('Total Serial Numbers', 'wp-product-verification'); ?></div>
            </div>
        </div>

        <div class="wpv-stat-card">
            <div class="wpv-stat-icon dashicons dashicons-yes-alt"></div>
            <div class="wpv-stat-content">
                <div class="wpv-stat-number"><?php echo esc_html($total_verifications); ?></div>
                <div class="wpv-stat-label"><?php echo esc_html__('Total Verifications', 'wp-product-verification'); ?></div>
            </div>
        </div>

        <div class="wpv-stat-card">
            <div class="wpv-stat-icon dashicons dashicons-shield-alt"></div>
            <div class="wpv-stat-content">
                <div class="wpv-stat-number"><?php echo esc_html($active_serials); ?></div>
                <div class="wpv-stat-label"><?php echo esc_html__('Active Serials', 'wp-product-verification'); ?></div>
            </div>
        </div>
    </div>

    <div class="wpv-dashboard-section">
        <h2><?php echo esc_html__('Recent Verifications', 'wp-product-verification'); ?></h2>

        <?php if (!empty($recent_verifications)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Serial Number', 'wp-product-verification'); ?></th>
                        <th><?php echo esc_html__('Product', 'wp-product-verification'); ?></th>
                        <th><?php echo esc_html__('IP Address', 'wp-product-verification'); ?></th>
                        <th><?php echo esc_html__('Date', 'wp-product-verification'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_verifications as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->serial_number); ?></td>
                            <td><?php echo esc_html($log->product_name); ?></td>
                            <td><?php echo esc_html($log->ip_address); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->verified_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php echo esc_html__('No verifications yet.', 'wp-product-verification'); ?></p>
        <?php endif; ?>
    </div>

    <div class="wpv-dashboard-section">
        <h2><?php echo esc_html__('Quick Actions', 'wp-product-verification'); ?></h2>
        <div class="wpv-quick-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-products')); ?>" class="button button-primary">
                <?php echo esc_html__('Add New Product', 'wp-product-verification'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-serials')); ?>" class="button button-secondary">
                <?php echo esc_html__('Manage Serials', 'wp-product-verification'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-import')); ?>" class="button button-secondary">
                <?php echo esc_html__('Import Serials', 'wp-product-verification'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-settings')); ?>" class="button button-secondary">
                <?php echo esc_html__('Settings', 'wp-product-verification'); ?>
            </a>
        </div>
    </div>
</div>
