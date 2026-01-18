<?php
/**
 * AJAX verification handler
 *
 * @package WPProductVerification\Ajax
 */

namespace WPProductVerification\Ajax;

use WPProductVerification\Admin\SerialManager;
use WPProductVerification\Admin\Settings;
use WPProductVerification\Email\NotificationManager;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VerificationHandler class for handling verification requests
 */
class VerificationHandler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wpv_verify_serial', [$this, 'verify_serial']);
        add_action('wp_ajax_nopriv_wpv_verify_serial', [$this, 'verify_serial']);
    }

    /**
     * Verify serial number
     *
     * @return void
     */
    public function verify_serial(): void {
        check_ajax_referer('wpv_verification_nonce', 'nonce');

        $serial_number = strtoupper(sanitize_text_field($_POST['serial_number'] ?? ''));

        if (empty($serial_number)) {
            wp_send_json_error([
                'message' => __('Please enter a serial number.', 'wp-product-verification'),
            ]);
            return;
        }

        global $wpdb;
        $serials_table = $wpdb->prefix . 'wpv_serials';
        $products_table = $wpdb->prefix . 'wpv_products';
        $logs_table = $wpdb->prefix . 'wpv_verification_logs';

        // Get serial with product info
        $serial = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, p.name as product_name, p.prefix as product_prefix
            FROM {$serials_table} s
            LEFT JOIN {$products_table} p ON s.product_id = p.id
            WHERE s.serial_number = %s",
            $serial_number
        ));

        if (!$serial) {
            wp_send_json_error([
                'message' => __('Invalid serial number. Please check and try again.', 'wp-product-verification'),
            ]);
            return;
        }

        // Check if inactive
        if ($serial->status !== 'active') {
            wp_send_json_error([
                'message' => __('This serial number has been deactivated.', 'wp-product-verification'),
            ]);
            return;
        }

        // Check verification count
        if ($serial->verification_count >= $serial->max_verifications) {
            wp_send_json_error([
                'message' => sprintf(
                    __('This serial number has reached its maximum verification limit (%d/%d).', 'wp-product-verification'),
                    $serial->verification_count,
                    $serial->max_verifications
                ),
            ]);
            return;
        }

        // Update verification count
        $new_count = $serial->verification_count + 1;
        $wpdb->update(
            $serials_table,
            ['verification_count' => $new_count],
            ['id' => $serial->id],
            ['%d'],
            ['%d']
        );

        // Log verification
        $wpdb->insert(
            $logs_table,
            [
                'serial_id' => $serial->id,
                'product_id' => $serial->product_id,
                'ip_address' => $this->get_client_ip(),
                'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            ],
            ['%d', '%d', '%s', '%s']
        );

        // Send notification if max reached
        if ($new_count >= $serial->max_verifications) {
            NotificationManager::send_max_verification_notification(
                $serial->serial_number,
                $serial->product_name,
                $new_count
            );
        }

        wp_send_json_success([
            'message' => __('Serial number verified successfully!', 'wp-product-verification'),
            'product_name' => $serial->product_name,
            'verification_count' => $new_count,
            'max_verifications' => $serial->max_verifications,
            'remaining' => max(0, $serial->max_verifications - $new_count),
        ]);
    }

    /**
     * Get client IP address
     *
     * @return string IP address
     */
    private function get_client_ip(): string {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    }
}
