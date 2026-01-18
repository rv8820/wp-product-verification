<?php
/**
 * Admin menu management
 *
 * @package WPProductVerification\Admin
 */

namespace WPProductVerification\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Menu class for admin menu registration
 */
class Menu {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menus']);
    }

    /**
     * Register admin menus
     *
     * @return void
     */
    public function register_menus(): void {
        add_menu_page(
            __('Product Verification', 'wp-product-verification'),
            __('Product Verification', 'wp-product-verification'),
            'manage_options',
            'wp-product-verification',
            [$this, 'render_dashboard'],
            'dashicons-shield-alt',
            30
        );

        add_submenu_page(
            'wp-product-verification',
            __('Dashboard', 'wp-product-verification'),
            __('Dashboard', 'wp-product-verification'),
            'manage_options',
            'wp-product-verification',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'wp-product-verification',
            __('Products', 'wp-product-verification'),
            __('Products', 'wp-product-verification'),
            'manage_options',
            'wp-product-verification-products',
            [$this, 'render_products']
        );

        add_submenu_page(
            'wp-product-verification',
            __('Serial Numbers', 'wp-product-verification'),
            __('Serial Numbers', 'wp-product-verification'),
            'manage_options',
            'wp-product-verification-serials',
            [$this, 'render_serials']
        );

        add_submenu_page(
            'wp-product-verification',
            __('Import Serials', 'wp-product-verification'),
            __('Import Serials', 'wp-product-verification'),
            'manage_options',
            'wp-product-verification-import',
            [$this, 'render_import']
        );

        add_submenu_page(
            'wp-product-verification',
            __('Settings', 'wp-product-verification'),
            __('Settings', 'wp-product-verification'),
            'manage_options',
            'wp-product-verification-settings',
            [$this, 'render_settings']
        );
    }

    /**
     * Render dashboard page
     *
     * @return void
     */
    public function render_dashboard(): void {
        require_once WPV_PLUGIN_DIR . 'includes/Admin/Views/dashboard.php';
    }

    /**
     * Render products page
     *
     * @return void
     */
    public function render_products(): void {
        require_once WPV_PLUGIN_DIR . 'includes/Admin/Views/products.php';
    }

    /**
     * Render serials page
     *
     * @return void
     */
    public function render_serials(): void {
        require_once WPV_PLUGIN_DIR . 'includes/Admin/Views/serials.php';
    }

    /**
     * Render import page
     *
     * @return void
     */
    public function render_import(): void {
        require_once WPV_PLUGIN_DIR . 'includes/Admin/Views/import.php';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings(): void {
        require_once WPV_PLUGIN_DIR . 'includes/Admin/Views/settings.php';
    }
}
