<?php
/**
 * Import view template
 *
 * @package WPProductVerification\Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use WPProductVerification\Admin\ProductManager;
use WPProductVerification\Admin\Settings;

$products = ProductManager::get_products();
$is_processing = isset($_GET['processing']) && isset($_GET['import_id']);
$import_id = isset($_GET['import_id']) ? sanitize_text_field($_GET['import_id']) : '';
?>

<div class="wrap">
    <h1><?php echo esc_html__('Import Serial Numbers', 'wp-product-verification'); ?></h1>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                switch ($_GET['error']) {
                    case 'missing_product':
                        echo esc_html__('Please select a product.', 'wp-product-verification');
                        break;
                    case 'upload_error':
                        echo esc_html__('File upload failed. Please try again.', 'wp-product-verification');
                        break;
                    case 'invalid_file':
                        echo esc_html__('Invalid file type. Please upload a CSV file.', 'wp-product-verification');
                        break;
                    case 'empty_file':
                        echo esc_html__('The CSV file is empty or invalid.', 'wp-product-verification');
                        break;
                    default:
                        echo esc_html__('An error occurred. Please try again.', 'wp-product-verification');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($is_processing): ?>
        <!-- Processing Status -->
        <div class="wpv-import-progress">
            <h2><?php echo esc_html__('Importing Serial Numbers...', 'wp-product-verification'); ?></h2>
            <div class="wpv-progress-bar">
                <div class="wpv-progress-fill" id="wpv-progress-fill" style="width: 0%;"></div>
            </div>
            <p class="wpv-progress-text" id="wpv-progress-text">
                <?php echo esc_html__('Starting import...', 'wp-product-verification'); ?>
            </p>
            <div id="wpv-import-complete" style="display: none;">
                <div class="notice notice-success">
                    <p><?php echo esc_html__('Import completed successfully!', 'wp-product-verification'); ?></p>
                </div>
                <p id="wpv-import-summary"></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-serials')); ?>" class="button button-primary">
                    <?php echo esc_html__('View Serial Numbers', 'wp-product-verification'); ?>
                </a>
            </div>
        </div>

        <script type="text/javascript">
        (function($) {
            var importId = '<?php echo esc_js($import_id); ?>';
            var maxVerifications = <?php echo absint($_GET['max_verifications'] ?? Settings::get_setting('max_verifications', 1)); ?>;

            function processNextBatch() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpv_process_import_batch',
                        nonce: '<?php echo wp_create_nonce('wpv_admin_nonce'); ?>',
                        import_id: importId,
                        max_verifications: maxVerifications
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            var percentage = data.percentage;

                            $('#wpv-progress-fill').css('width', percentage + '%');
                            $('#wpv-progress-text').text(
                                '<?php echo esc_js(__('Processing:', 'wp-product-verification')); ?> ' +
                                data.processed + ' / ' + data.total +
                                ' (' + percentage + '%)'
                            );

                            if (data.is_complete) {
                                $('#wpv-progress-text').hide();
                                $('#wpv-import-complete').show();
                                $('#wpv-import-summary').html(
                                    '<strong><?php echo esc_js(__('Import Summary:', 'wp-product-verification')); ?></strong><br>' +
                                    '<?php echo esc_js(__('Successfully imported:', 'wp-product-verification')); ?> ' + data.success + '<br>' +
                                    '<?php echo esc_js(__('Failed:', 'wp-product-verification')); ?> ' + data.failed + '<br>' +
                                    '<?php echo esc_js(__('Total processed:', 'wp-product-verification')); ?> ' + data.processed
                                );
                            } else {
                                setTimeout(processNextBatch, 100);
                            }
                        } else {
                            alert('<?php echo esc_js(__('Error:', 'wp-product-verification')); ?> ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('An error occurred during import.', 'wp-product-verification')); ?>');
                    }
                });
            }

            $(document).ready(function() {
                processNextBatch();
            });
        })(jQuery);
        </script>
    <?php else: ?>
        <!-- Import Form -->
        <div class="wpv-import-instructions">
            <h2><?php echo esc_html__('Instructions', 'wp-product-verification'); ?></h2>
            <ol>
                <li><?php echo esc_html__('Download the sample CSV file to see the required format.', 'wp-product-verification'); ?></li>
                <li><?php echo esc_html__('Prepare your CSV file with one serial number per row.', 'wp-product-verification'); ?></li>
                <li><?php echo esc_html__('Select the product to associate with these serial numbers.', 'wp-product-verification'); ?></li>
                <li><?php echo esc_html__('Upload the CSV file and click "Import".', 'wp-product-verification'); ?></li>
            </ol>

            <p>
                <a href="<?php echo esc_url(admin_url('admin-post.php?action=wpv_download_sample')); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                    <?php echo esc_html__('Download Sample CSV', 'wp-product-verification'); ?>
                </a>
            </p>
        </div>

        <?php if (empty($products)): ?>
            <div class="notice notice-warning">
                <p>
                    <?php echo esc_html__('You need to create at least one product before importing serial numbers.', 'wp-product-verification'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-products')); ?>">
                        <?php echo esc_html__('Create a product now', 'wp-product-verification'); ?>
                    </a>
                </p>
            </div>
        <?php else: ?>
            <div class="wpv-form-card">
                <h2><?php echo esc_html__('Upload CSV File', 'wp-product-verification'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="wpv_import_csv">
                    <?php wp_nonce_field('wpv_import_csv', 'wpv_import_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="product_id"><?php echo esc_html__('Product', 'wp-product-verification'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <select name="product_id" id="product_id" class="regular-text" required>
                                    <option value=""><?php echo esc_html__('Select Product', 'wp-product-verification'); ?></option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo esc_attr($product->id); ?>">
                                            <?php echo esc_html($product->name); ?> (<?php echo esc_html($product->prefix); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php echo esc_html__('All serial numbers in the CSV will be associated with this product.', 'wp-product-verification'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="csv_file"><?php echo esc_html__('CSV File', 'wp-product-verification'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                                <p class="description"><?php echo esc_html__('Maximum file size: 10MB. The CSV should have a header row and one serial number per row.', 'wp-product-verification'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="max_verifications"><?php echo esc_html__('Max Verifications', 'wp-product-verification'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="max_verifications" id="max_verifications" class="small-text" value="<?php echo esc_attr(Settings::get_setting('max_verifications', 1)); ?>" min="1">
                                <p class="description"><?php echo esc_html__('Maximum number of verifications allowed for each serial number.', 'wp-product-verification'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Import Serial Numbers', 'wp-product-verification'); ?>">
                    </p>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
