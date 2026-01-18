<?php
/**
 * Settings view template
 *
 * @package WPProductVerification\Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use WPProductVerification\Admin\Settings;

$settings = Settings::get_settings();
?>

<div class="wrap">
    <h1><?php echo esc_html__('Product Verification Settings', 'wp-product-verification'); ?></h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Settings saved successfully!', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wpv_save_settings">
        <?php wp_nonce_field('wpv_save_settings', 'wpv_settings_nonce'); ?>

        <div class="wpv-settings-section">
            <h2><?php echo esc_html__('Verification Settings', 'wp-product-verification'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="max_verifications"><?php echo esc_html__('Maximum Verifications', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="wpv_settings[max_verifications]" id="max_verifications" class="small-text" value="<?php echo esc_attr($settings['max_verifications']); ?>" min="1">
                        <p class="description"><?php echo esc_html__('Maximum number of times a serial number can be verified (default for new serials).', 'wp-product-verification'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wpv-settings-section">
            <h2><?php echo esc_html__('Serial Number Format', 'wp-product-verification'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="serial_length"><?php echo esc_html__('Serial Length', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="wpv_settings[serial_length]" id="serial_length" class="small-text" value="<?php echo esc_attr($settings['serial_length']); ?>" min="4" max="50">
                        <p class="description"><?php echo esc_html__('Total number of characters in the serial number (including prefix and separators).', 'wp-product-verification'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="separator"><?php echo esc_html__('Separator Character', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="wpv_settings[separator]" id="separator" class="small-text" value="<?php echo esc_attr($settings['separator']); ?>" maxlength="3">
                        <p class="description"><?php echo esc_html__('Character(s) to use as separator (e.g., "-" for XXXX-XXXX). Leave empty for no separator.', 'wp-product-verification'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="separator_interval"><?php echo esc_html__('Separator Interval', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="wpv_settings[separator_interval]" id="separator_interval" class="small-text" value="<?php echo esc_attr($settings['separator_interval']); ?>" min="1" max="10">
                        <p class="description"><?php echo esc_html__('Number of characters between each separator (e.g., 4 for XXXX-XXXX-XXXX).', 'wp-product-verification'); ?></p>
                    </td>
                </tr>
            </table>

            <div class="wpv-format-preview">
                <h3><?php echo esc_html__('Preview', 'wp-product-verification'); ?></h3>
                <p>
                    <?php echo esc_html__('Example format:', 'wp-product-verification'); ?>
                    <code id="wpv-format-example">
                        <?php
                        $prefix = 'ABCD';
                        $length = (int) $settings['serial_length'];
                        $separator = (string) $settings['separator'];
                        $interval = (int) $settings['separator_interval'];
                        $remaining = max(0, $length - strlen($prefix));
                        $sample = $prefix . str_repeat('X', $remaining);
                        if (!empty($separator) && $interval > 0) {
                            $parts = str_split($sample, $interval);
                            $sample = implode($separator, $parts);
                        }
                        echo esc_html($sample);
                        ?>
                    </code>
                </p>
            </div>
        </div>

        <div class="wpv-settings-section">
            <h2><?php echo esc_html__('Email Notifications', 'wp-product-verification'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="notification_emails"><?php echo esc_html__('Notification Email Addresses', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <textarea name="wpv_settings[notification_emails]" id="notification_emails" class="large-text" rows="5"><?php echo esc_textarea($settings['notification_emails']); ?></textarea>
                        <p class="description"><?php echo esc_html__('Email addresses to notify when a serial reaches max verifications. Enter one per line or comma-separated.', 'wp-product-verification'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Save Settings', 'wp-product-verification'); ?>">
        </p>
    </form>

    <script type="text/javascript">
    (function($) {
        function updatePreview() {
            var length = parseInt($('#serial_length').val()) || 16;
            var separator = $('#separator').val() || '-';
            var interval = parseInt($('#separator_interval').val()) || 4;
            var prefix = 'ABCD';

            var remaining = Math.max(0, length - prefix.length);
            var sample = prefix + 'X'.repeat(remaining);

            if (separator && interval > 0) {
                var parts = sample.match(new RegExp('.{1,' + interval + '}', 'g'));
                sample = parts.join(separator);
            }

            $('#wpv-format-example').text(sample);
        }

        $('#serial_length, #separator, #separator_interval').on('input change', updatePreview);
    })(jQuery);
    </script>
</div>
