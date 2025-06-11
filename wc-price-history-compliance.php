<?php
/**
 * Plugin Name:       WooCommerce Price History & Sale Compliance
 * Plugin URI:        https://example.com/
 * Description:       Tracks product price history and displays the lowest price in the last 30 days during a sale to comply with EU law.
 * Version:           1.0.0
 * Author:            (Your Name)
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-price-history-compliance
 * Domain Path:       /languages
 * WC requires at least: 6.0
 * WC tested up to:      8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define constants
define( 'WCPC_VERSION', '1.0.1' );
define( 'WCPC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCPC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the install handler immediately (needed for activation hook)
require_once WCPC_PLUGIN_DIR . 'includes/class-install-handler.php';

// Register activation and deactivation hooks IMMEDIATELY (not in plugins_loaded)
register_activation_hook( __FILE__, [ 'WCPC_Install_Handler', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'WCPC_Install_Handler', 'deactivate' ] );

/**
 * The main plugin class.
 */
final class WC_Price_History_Compliance {

    /**
     * The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files.
     */
    private function includes() {
        require_once WCPC_PLUGIN_DIR . 'includes/class-price-tracker.php';
        require_once WCPC_PLUGIN_DIR . 'includes/class-display-handler.php';
        require_once WCPC_PLUGIN_DIR . 'includes/class-admin-reports.php';
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Initialize classes
        new WCPC_Price_Tracker();
        new WCPC_Display_Handler();
        new WCPC_Admin_Reports();
    }
}

/**
 * Declare HPOS compatibility.
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/**
 * Begins execution of the plugin.
 */
function wcpc_run_plugin() {
    // Ensure WooCommerce is active
    if ( class_exists( 'WooCommerce' ) ) {
         WC_Price_History_Compliance::instance();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>WooCommerce Price History & Sale Compliance</strong> requires WooCommerce to be installed and active.</p></div>';
        });
    }
}
add_action( 'plugins_loaded', 'wcpc_run_plugin' );