<?php
/**
 * Settings management
 *
 * @package WPProductVerification\Admin
 */

namespace WPProductVerification\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class for plugin configuration
 */
class Settings {
    /**
     * @var string Option name
     */
    private string $option_name = 'wpv_settings';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_wpv_save_settings', [$this, 'save_settings']);
    }

    /**
     * Register plugin settings
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting('wpv_settings_group', $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
        ]);
    }

    /**
     * Save settings via admin_post
     *
     * @return void
     */
    public function save_settings(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_save_settings', 'wpv_settings_nonce');

        $settings = $this->sanitize_settings($_POST['wpv_settings'] ?? []);
        update_option($this->option_name, $settings);

        wp_redirect(add_query_arg([
            'page' => 'wp-product-verification-settings',
            'updated' => 'true',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Sanitize settings
     *
     * @param array<string, mixed> $settings Raw settings
     * @return array<string, mixed> Sanitized settings
     */
    public function sanitize_settings(array $settings): array {
        return [
            'max_verifications' => absint($settings['max_verifications'] ?? 1),
            'serial_length' => absint($settings['serial_length'] ?? 16),
            'separator' => sanitize_text_field($settings['separator'] ?? '-'),
            'separator_interval' => absint($settings['separator_interval'] ?? 4),
            'notification_emails' => sanitize_textarea_field($settings['notification_emails'] ?? get_option('admin_email')),
            'success_message' => sanitize_text_field($settings['success_message'] ?? __('Serial number verified successfully!', 'wp-product-verification')),
            'error_invalid_message' => sanitize_text_field($settings['error_invalid_message'] ?? __('Invalid serial number. Please check and try again.', 'wp-product-verification')),
            'error_max_reached_message' => sanitize_text_field($settings['error_max_reached_message'] ?? __('This serial number has reached its maximum verification limit.', 'wp-product-verification')),
            'error_inactive_message' => sanitize_text_field($settings['error_inactive_message'] ?? __('This serial number has been deactivated.', 'wp-product-verification')),
        ];
    }

    /**
     * Get settings
     *
     * @return array<string, mixed> Settings array
     */
    public static function get_settings(): array {
        $defaults = [
            'max_verifications' => 1,
            'serial_length' => 16,
            'separator' => '-',
            'separator_interval' => 4,
            'notification_emails' => get_option('admin_email'),
            'success_message' => __('Serial number verified successfully!', 'wp-product-verification'),
            'error_invalid_message' => __('Invalid serial number. Please check and try again.', 'wp-product-verification'),
            'error_max_reached_message' => __('This serial number has reached its maximum verification limit.', 'wp-product-verification'),
            'error_inactive_message' => __('This serial number has been deactivated.', 'wp-product-verification'),
        ];

        $settings = get_option('wpv_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get specific setting
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public static function get_setting(string $key, $default = null) {
        $settings = self::get_settings();
        return $settings[$key] ?? $default;
    }
}
