<?php
/**
 * Serials view template
 *
 * @package WPProductVerification\Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use WPProductVerification\Admin\ProductManager;
use WPProductVerification\Admin\SerialManager;
use WPProductVerification\Admin\Settings;

$products = ProductManager::get_products();
$filter_product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : null;
$page_num = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$per_page = 50;
$offset = ($page_num - 1) * $per_page;

$serials = SerialManager::get_serials($filter_product_id, $per_page, $offset);
$total_serials = SerialManager::get_serial_count($filter_product_id);
$total_pages = ceil($total_serials / $per_page);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Serial Numbers', 'wp-product-verification'); ?></h1>

    <a href="#" class="page-title-action wpv-add-serial-btn">
        <?php echo esc_html__('Add New', 'wp-product-verification'); ?>
    </a>

    <a href="#" class="page-title-action wpv-generate-serials-btn">
        <?php echo esc_html__('Generate Serials', 'wp-product-verification'); ?>
    </a>

    <hr class="wp-header-end">

    <?php if (isset($_GET['added'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Serial number added successfully!', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['generated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html(sprintf(__('%d serial numbers generated successfully!', 'wp-product-verification'), absint($_GET['generated']))); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Serial number deleted successfully!', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['bulk_deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html(sprintf(__('%d serial numbers deleted successfully!', 'wp-product-verification'), absint($_GET['bulk_deleted']))); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                switch ($_GET['error']) {
                    case 'no_selection':
                        echo esc_html__('Please select serial numbers to delete.', 'wp-product-verification');
                        break;
                    default:
                        echo esc_html__('An error occurred. Please try again.', 'wp-product-verification');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Add Serial Form (Hidden by default) -->
    <div id="wpv-add-serial-form" class="wpv-form-card" style="display: none;">
        <h2><?php echo esc_html__('Add New Serial Number', 'wp-product-verification'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="wpv_add_serial">
            <?php wp_nonce_field('wpv_add_serial', 'wpv_serial_nonce'); ?>

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
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="serial_number"><?php echo esc_html__('Serial Number', 'wp-product-verification'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" name="serial_number" id="serial_number" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="max_verifications"><?php echo esc_html__('Max Verifications', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="max_verifications" id="max_verifications" class="small-text" value="<?php echo esc_attr(Settings::get_setting('max_verifications', 1)); ?>" min="1">
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Add Serial', 'wp-product-verification'); ?>">
                <button type="button" class="button button-secondary wpv-cancel-add-serial">
                    <?php echo esc_html__('Cancel', 'wp-product-verification'); ?>
                </button>
            </p>
        </form>
    </div>

    <!-- Generate Serials Form (Hidden by default) -->
    <div id="wpv-generate-serials-form" class="wpv-form-card" style="display: none;">
        <h2><?php echo esc_html__('Generate Serial Numbers', 'wp-product-verification'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="wpv_generate_serials">
            <?php wp_nonce_field('wpv_generate_serials', 'wpv_serial_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="gen_product_id"><?php echo esc_html__('Product', 'wp-product-verification'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <select name="product_id" id="gen_product_id" class="regular-text" required>
                            <option value=""><?php echo esc_html__('Select Product', 'wp-product-verification'); ?></option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo esc_attr($product->id); ?>">
                                    <?php echo esc_html($product->name); ?> (<?php echo esc_html($product->prefix); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="quantity"><?php echo esc_html__('Quantity', 'wp-product-verification'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="number" name="quantity" id="quantity" class="small-text" min="1" max="1000" required>
                        <p class="description"><?php echo esc_html__('Number of serial numbers to generate (max 1000 per batch)', 'wp-product-verification'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="gen_max_verifications"><?php echo esc_html__('Max Verifications', 'wp-product-verification'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="max_verifications" id="gen_max_verifications" class="small-text" value="<?php echo esc_attr(Settings::get_setting('max_verifications', 1)); ?>" min="1">
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Generate Serials', 'wp-product-verification'); ?>">
                <button type="button" class="button button-secondary wpv-cancel-generate-serials">
                    <?php echo esc_html__('Cancel', 'wp-product-verification'); ?>
                </button>
            </p>
        </form>
    </div>

    <!-- Filter and List -->
    <div class="wpv-table-container">
        <div class="tablenav top">
            <form method="get" action="">
                <input type="hidden" name="page" value="wp-product-verification-serials">
                <label for="filter_product_id"><?php echo esc_html__('Filter by Product:', 'wp-product-verification'); ?></label>
                <select name="product_id" id="filter_product_id" onchange="this.form.submit();">
                    <option value=""><?php echo esc_html__('All Products', 'wp-product-verification'); ?></option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo esc_attr($product->id); ?>" <?php selected($filter_product_id, $product->id); ?>>
                            <?php echo esc_html($product->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (!empty($serials)): ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="wpv-bulk-serials-form">
                <input type="hidden" name="action" value="wpv_bulk_delete_serials">
                <?php wp_nonce_field('wpv_bulk_serials', 'wpv_bulk_nonce'); ?>

                <div class="wpv-bulk-actions">
                    <label for="wpv-bulk-action" class="screen-reader-text"><?php echo esc_html__('Bulk Actions', 'wp-product-verification'); ?></label>
                    <select name="bulk_action" id="wpv-bulk-action">
                        <option value=""><?php echo esc_html__('Bulk Actions', 'wp-product-verification'); ?></option>
                        <option value="delete"><?php echo esc_html__('Delete', 'wp-product-verification'); ?></option>
                    </select>
                    <button type="submit" class="button"><?php echo esc_html__('Apply', 'wp-product-verification'); ?></button>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="wpv-select-all">
                            </td>
                            <th><?php echo esc_html__('Serial Number', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Product', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Verifications', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Status', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Created', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Actions', 'wp-product-verification'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serials as $serial): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="serial_ids[]" value="<?php echo esc_attr($serial->id); ?>" class="wpv-serial-checkbox">
                                </th>
                                <td><code><?php echo esc_html($serial->serial_number); ?></code></td>
                                <td><?php echo esc_html($serial->product_name); ?></td>
                                <td>
                                    <?php echo esc_html($serial->verification_count); ?> / <?php echo esc_html($serial->max_verifications); ?>
                                    <?php if ($serial->verification_count >= $serial->max_verifications): ?>
                                        <span class="wpv-badge wpv-badge-danger"><?php echo esc_html__('Max Reached', 'wp-product-verification'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($serial->status === 'active'): ?>
                                        <span class="wpv-badge wpv-badge-success"><?php echo esc_html__('Active', 'wp-product-verification'); ?></span>
                                    <?php else: ?>
                                        <span class="wpv-badge wpv-badge-warning"><?php echo esc_html(ucfirst($serial->status)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($serial->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wpv_delete_serial&id=' . $serial->id), 'wpv_delete_serial_' . $serial->id)); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this serial number?', 'wp-product-verification')); ?>');">
                                        <?php echo esc_html__('Delete', 'wp-product-verification'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links([
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $page_num,
                        ]);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p><?php echo esc_html__('No serial numbers found. Click "Add New" or "Generate Serials" to create serial numbers.', 'wp-product-verification'); ?></p>
        <?php endif; ?>
    </div>
</div>
