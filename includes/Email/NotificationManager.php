<?php
/**
 * Email notification management
 *
 * @package WPProductVerification\Email
 */

namespace WPProductVerification\Email;

use WPProductVerification\Admin\Settings;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * NotificationManager class for handling email notifications
 */
class NotificationManager {
    /**
     * Send max verification notification
     *
     * @param string $serial_number Serial number
     * @param string $product_name Product name
     * @param int $verification_count Verification count
     * @return bool Success status
     */
    public static function send_max_verification_notification(
        string $serial_number,
        string $product_name,
        int $verification_count
    ): bool {
        $notification_emails = Settings::get_setting('notification_emails', get_option('admin_email'));

        // Parse email addresses (can be comma or newline separated)
        $emails = preg_split('/[\s,]+/', $notification_emails, -1, PREG_SPLIT_NO_EMPTY);
        $emails = array_filter(array_map('trim', $emails));

        if (empty($emails)) {
            return false;
        }

        $subject = sprintf(
            __('[%s] Serial Number Reached Maximum Verification', 'wp-product-verification'),
            get_bloginfo('name')
        );

        $message = self::get_notification_template(
            $serial_number,
            $product_name,
            $verification_count
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        $sent = false;
        foreach ($emails as $email) {
            if (is_email($email)) {
                $result = wp_mail($email, $subject, $message, $headers);
                if ($result) {
                    $sent = true;
                }
            }
        }

        return $sent;
    }

    /**
     * Get notification email template
     *
     * @param string $serial_number Serial number
     * @param string $product_name Product name
     * @param int $verification_count Verification count
     * @return string HTML email template
     */
    private static function get_notification_template(
        string $serial_number,
        string $product_name,
        int $verification_count
    ): string {
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #0073aa;
                    color: #fff;
                    padding: 20px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    background-color: #f9f9f9;
                    padding: 30px;
                    border: 1px solid #ddd;
                    border-top: none;
                    border-radius: 0 0 5px 5px;
                }
                .info-box {
                    background-color: #fff;
                    border-left: 4px solid #0073aa;
                    padding: 15px;
                    margin: 20px 0;
                }
                .info-box p {
                    margin: 5px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    color: #666;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?php echo esc_html__('Maximum Verification Alert', 'wp-product-verification'); ?></h1>
            </div>
            <div class="content">
                <p><?php echo esc_html__('Hello,', 'wp-product-verification'); ?></p>

                <p><?php echo esc_html__('A serial number has reached its maximum verification limit:', 'wp-product-verification'); ?></p>

                <div class="info-box">
                    <p><strong><?php echo esc_html__('Serial Number:', 'wp-product-verification'); ?></strong> <?php echo esc_html($serial_number); ?></p>
                    <p><strong><?php echo esc_html__('Product:', 'wp-product-verification'); ?></strong> <?php echo esc_html($product_name); ?></p>
                    <p><strong><?php echo esc_html__('Total Verifications:', 'wp-product-verification'); ?></strong> <?php echo esc_html($verification_count); ?></p>
                    <p><strong><?php echo esc_html__('Date:', 'wp-product-verification'); ?></strong> <?php echo esc_html(current_time('Y-m-d H:i:s')); ?></p>
                </div>

                <p><?php echo esc_html__('This serial number will no longer accept verification attempts.', 'wp-product-verification'); ?></p>

                <p><?php echo esc_html__('You can view more details in your WordPress admin panel.', 'wp-product-verification'); ?></p>
            </div>
            <div class="footer">
                <p><?php echo esc_html__('This email was sent from', 'wp-product-verification'); ?> <a href="<?php echo esc_url($site_url); ?>"><?php echo esc_html($site_name); ?></a></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
