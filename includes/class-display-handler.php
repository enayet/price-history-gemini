<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles frontend display logic for compliance message and chart.
 */
class WCPC_Display_Handler {

    public function __construct() {
        // Display hooks
        add_filter( 'woocommerce_get_price_html', [ $this, 'display_lowest_price_message' ], 20, 2 );
        // Remove the automatic chart canvas - we'll add it conditionally with the message
        // add_action( 'woocommerce_single_product_summary', [ $this, 'add_chart_canvas' ], 35 );

        // Asset enqueuing
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        
        // Settings page
        add_filter( 'woocommerce_get_settings_pages', [ $this, 'add_settings_page' ] );
    }
    
    /**
     * Display the lowest price in the last 30 days.
     */
    public function display_lowest_price_message( $price_html, $product ) {
        if ( $product->is_on_sale() && get_option('wcpc_show_lowest_price_message', 'yes') === 'yes' ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wc_price_history';
            $product_id = $product->get_id();
            $thirty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

            $lowest_price = $wpdb->get_var( $wpdb->prepare(
                "SELECT MIN(price) FROM $table_name WHERE product_id = %d AND date >= %s",
                $product_id,
                $thirty_days_ago
            ) );

            if ( $lowest_price && floatval($lowest_price) < floatval($product->get_regular_price()) ) {
                $message_format = get_option('wcpc_lowest_price_text', 'Lowest price in the last 30 days: %s');
                $lowest_price_message = sprintf( $message_format, wc_price($lowest_price) );
                
                // Add the message
                $price_html .= '<p class="wcpc-lowest-price-message">' . $lowest_price_message . '</p>';
                
                // Check if we should show chart and if there's actual price history data
                if ( get_option('wcpc_show_chart', 'yes') === 'yes' ) {
                    $price_history = $this->get_price_history_for_chart($product_id);
                    // Only add chart if there's actual data (more than 1 data point)
                    if ( count($price_history) > 1 ) {
                        $price_html .= '<div class="wcpc-chart-container"><canvas id="wcpcPriceChart-' . $product_id . '"></canvas></div>';
                    }
                }
            }
        }
        return $price_html;
    }

    /**
     * Add the canvas element for the chart - REMOVED since we're adding it conditionally with the message
     */
    /*
    public function add_chart_canvas() {
        if ( get_option('wcpc_show_chart', 'yes') === 'yes' ) {
            echo '<div class="wcpc-chart-container"><canvas id="wcpcPriceChart"></canvas></div>';
        }
    }
    */
    
    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts() {
        if ( is_product() ) {
            // Enqueue frontend CSS
            wp_enqueue_style( 'wcpc-frontend-css', WCPC_PLUGIN_URL . 'assets/css/frontend.css', [], WCPC_VERSION );

            if ( get_option('wcpc_show_chart', 'yes') === 'yes' ) {
                global $product;
                
                // Only enqueue chart scripts if there's actual price history data
                $price_history = $this->get_price_history_for_chart($product->get_id());
                if ( count($price_history) > 1 ) {
                    // Enqueue Chart.js from CDN
                    wp_enqueue_script( 'chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [], '3.9.1', true );
                    
                    // Enqueue our chart script
                    wp_enqueue_script( 'wcpc-chart-js', WCPC_PLUGIN_URL . 'assets/js/chart.js', ['chart-js'], WCPC_VERSION, true );
                    
                    // Pass data to the script
                    wp_localize_script( 'wcpc-chart-js', 'wcpc_chart_data', [
                        'labels' => wp_list_pluck($price_history, 'date'),
                        'data'   => wp_list_pluck($price_history, 'price'),
                        'label'  => __('Price History', 'wc-price-history-compliance'),
                        'product_id' => $product->get_id()
                    ]);
                }
            }
        }
    }
    
    /**
     * Get price history data formatted for the chart.
     */
    private function get_price_history_for_chart( $product_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_price_history';
        $sixty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-60 days' ) );
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT price, DATE_FORMAT(date, '%%b %%d') as date FROM $table_name WHERE product_id = %d AND date >= %s ORDER BY date ASC",
            $product_id,
            $sixty_days_ago
        ) );
        
        return $results;
    }
    
    /**
     * Add settings page to WooCommerce.
     */
    public function add_settings_page( $settings ) {
        $settings[] = include( WCPC_PLUGIN_DIR . 'includes/class-admin-settings.php' );
        return $settings;
    }
}