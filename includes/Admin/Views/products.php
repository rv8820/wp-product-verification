<?php
/**
 * Products view template
 *
 * @package WPProductVerification\Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use WPProductVerification\Admin\ProductManager;

$products = ProductManager::get_products();
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$edit_product = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $edit_product = ProductManager::get_product(absint($_GET['id']));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Products', 'wp-product-verification'); ?></h1>

    <?php if ($action !== 'edit'): ?>
        <a href="#" class="page-title-action wpv-add-product-btn">
            <?php echo esc_html__('Add New', 'wp-product-verification'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php if (isset($_GET['added'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Product added successfully!', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Product updated successfully!', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Product deleted successfully!', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__('An error occurred. Please try again.', 'wp-product-verification'); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($action === 'edit' && $edit_product): ?>
        <!-- Edit Product Form -->
        <div class="wpv-form-card">
            <h2><?php echo esc_html__('Edit Product', 'wp-product-verification'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="wpv_edit_product">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($edit_product->id); ?>">
                <?php wp_nonce_field('wpv_edit_product', 'wpv_product_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="product_name"><?php echo esc_html__('Product Name', 'wp-product-verification'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="product_name" id="product_name" class="regular-text" value="<?php echo esc_attr($edit_product->name); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="product_prefix"><?php echo esc_html__('Prefix', 'wp-product-verification'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="product_prefix" id="product_prefix" class="regular-text" value="<?php echo esc_attr($edit_product->prefix); ?>" required>
                            <p class="description"><?php echo esc_html__('First characters of serial numbers for this product (e.g., ABCD)', 'wp-product-verification'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="product_description"><?php echo esc_html__('Description', 'wp-product-verification'); ?></label>
                        </th>
                        <td>
                            <textarea name="product_description" id="product_description" class="large-text" rows="5"><?php echo esc_textarea($edit_product->description); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="product_logo"><?php echo esc_html__('Logo', 'wp-product-verification'); ?></label>
                        </th>
                        <td>
                            <div class="wpv-logo-upload-container">
                                <input type="hidden" name="product_logo_id" id="product_logo_id" value="<?php echo esc_attr($edit_product->logo_id ?? ''); ?>">
                                <div class="wpv-logo-preview">
                                    <?php if (!empty($edit_product->logo_id)): ?>
                                        <?php echo wp_get_attachment_image(absint($edit_product->logo_id), 'thumbnail'); ?>
                                    <?php endif; ?>
                                </div>
                                <p>
                                    <button type="button" class="button wpv-upload-logo-btn"><?php echo esc_html__('Upload Logo', 'wp-product-verification'); ?></button>
                                    <button type="button" class="button wpv-remove-logo-btn" <?php echo empty($edit_product->logo_id) ? 'style="display:none;"' : ''; ?>><?php echo esc_html__('Remove Logo', 'wp-product-verification'); ?></button>
                                </p>
                                <p class="description"><?php echo esc_html__('Upload a logo image for this product. Recommended size: 300x300px', 'wp-product-verification'); ?></p>
                            </div>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Update Product', 'wp-product-verification'); ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-products')); ?>" class="button button-secondary">
                        <?php echo esc_html__('Cancel', 'wp-product-verification'); ?>
                    </a>
                </p>
            </form>
        </div>
    <?php else: ?>
        <!-- Add Product Form (Hidden by default) -->
        <div id="wpv-add-product-form" class="wpv-form-card" style="display: none;">
            <h2><?php echo esc_html__('Add New Product', 'wp-product-verification'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="wpv_add_product">
                <?php wp_nonce_field('wpv_add_product', 'wpv_product_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="product_name"><?php echo esc_html__('Product Name', 'wp-product-verification'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="product_name" id="product_name" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="product_prefix"><?php echo esc_html__('Prefix', 'wp-product-verification'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="product_prefix" id="product_prefix" class="regular-text" required>
                            <p class="description"><?php echo esc_html__('First characters of serial numbers for this product (e.g., ABCD)', 'wp-product-verification'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="product_description"><?php echo esc_html__('Description', 'wp-product-verification'); ?></label>
                        </th>
                        <td>
                            <textarea name="product_description" id="product_description" class="large-text" rows="5"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="product_logo"><?php echo esc_html__('Logo', 'wp-product-verification'); ?></label>
                        </th>
                        <td>
                            <div class="wpv-logo-upload-container">
                                <input type="hidden" name="product_logo_id" id="product_logo_id_new" value="">
                                <div class="wpv-logo-preview"></div>
                                <p>
                                    <button type="button" class="button wpv-upload-logo-btn"><?php echo esc_html__('Upload Logo', 'wp-product-verification'); ?></button>
                                    <button type="button" class="button wpv-remove-logo-btn" style="display:none;"><?php echo esc_html__('Remove Logo', 'wp-product-verification'); ?></button>
                                </p>
                                <p class="description"><?php echo esc_html__('Upload a logo image for this product. Recommended size: 300x300px', 'wp-product-verification'); ?></p>
                            </div>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Add Product', 'wp-product-verification'); ?>">
                    <button type="button" class="button button-secondary wpv-cancel-add-product">
                        <?php echo esc_html__('Cancel', 'wp-product-verification'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Products List -->
        <div class="wpv-table-container">
            <?php if (!empty($products)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php echo esc_html__('Logo', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Name', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Prefix', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Description', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Created', 'wp-product-verification'); ?></th>
                            <th><?php echo esc_html__('Actions', 'wp-product-verification'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product->logo_id)): ?>
                                        <?php echo wp_get_attachment_image(absint($product->logo_id), 'thumbnail', false, ['style' => 'max-width:50px;height:auto;']); ?>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-format-image" style="font-size: 40px; color: #ddd;"></span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc_html($product->name); ?></strong></td>
                                <td><code><?php echo esc_html($product->prefix); ?></code></td>
                                <td><?php echo esc_html(wp_trim_words($product->description, 10)); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($product->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-product-verification-products&action=edit&id=' . $product->id)); ?>" class="button button-small">
                                        <?php echo esc_html__('Edit', 'wp-product-verification'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wpv_delete_product&id=' . $product->id), 'wpv_delete_product_' . $product->id)); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this product? All associated serial numbers will be deleted.', 'wp-product-verification')); ?>');">
                                        <?php echo esc_html__('Delete', 'wp-product-verification'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php echo esc_html__('No products found. Click "Add New" to create your first product.', 'wp-product-verification'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
