<?php
/**
 * Plugin Name: WP Product Verification
 * Plugin URI: https://tmdigitalconsulting.com
 * Description: Advanced product serial number verification system with Elementor integration
 * Version: 1.0.0
 * Author: Ramises
 * Author URI: https://tmdigitalconsulting.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-product-verification
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

namespace WPProductVerification;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPV_VERSION', '1.0.0');
define('WPV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPV_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function (string $class): void {
    $prefix = 'WPProductVerification\\';
    $base_dir = WPV_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main plugin class
 */
final class Plugin {
    /**
     * @var Plugin|null Singleton instance
     */
    private static ?Plugin $instance = null;

    /**
     * Get singleton instance
     *
     * @return Plugin
     */
    public static function instance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     *
     * @return void
     */
    private function init_hooks(): void {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_enqueue_scripts']);
        add_action('elementor/widgets/register', [$this, 'register_elementor_widgets']);
    }

    /**
     * Plugin activation
     *
     * @return void
     */
    public function activate(): void {
        Database\Schema::create_tables();
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     *
     * @return void
     */
    public function deactivate(): void {
        flush_rewrite_rules();
    }

    /**
     * Initialize plugin
     *
     * @return void
     */
    public function init(): void {
        // Load text domain
        load_plugin_textdomain('wp-product-verification', false, dirname(WPV_PLUGIN_BASENAME) . '/languages');

        // Initialize components
        new Admin\Menu();
        new Admin\Settings();
        new Admin\ProductManager();
        new Admin\SerialManager();
        new Admin\CSVImporter();
        new Ajax\VerificationHandler();
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function admin_enqueue_scripts(string $hook): void {
        if (strpos($hook, 'wp-product-verification') === false) {
            return;
        }

        // Enqueue WordPress media uploader for products page
        if (strpos($hook, 'wp-product-verification-products') !== false) {
            wp_enqueue_media();
        }

        wp_enqueue_style(
            'wpv-admin-style',
            WPV_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WPV_VERSION
        );

        wp_enqueue_script(
            'wpv-admin-script',
            WPV_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WPV_VERSION,
            true
        );

        wp_localize_script('wpv-admin-script', 'wpvAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpv_admin_nonce'),
        ]);
    }

    /**
     * Enqueue frontend scripts and styles
     *
     * @return void
     */
    public function frontend_enqueue_scripts(): void {
        wp_enqueue_style(
            'wpv-frontend-style',
            WPV_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            WPV_VERSION
        );

        wp_enqueue_script(
            'wpv-frontend-script',
            WPV_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            WPV_VERSION,
            true
        );

        wp_localize_script('wpv-frontend-script', 'wpvFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpv_verification_nonce'),
        ]);
    }

    /**
     * Register Elementor widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager
     * @return void
     */
    public function register_elementor_widgets($widgets_manager): void {
        require_once WPV_PLUGIN_DIR . 'includes/Elementor/VerificationWidget.php';
        $widgets_manager->register(new Elementor\VerificationWidget());
    }
}

// Initialize plugin
Plugin::instance();
