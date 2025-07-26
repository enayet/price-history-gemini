<?php
/**
 * This file is part of WooCommerce Price History & Sale Compliance plugin.
 *
 * @package WCPC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Handles frontend display logic for compliance message and chart.
 */
class WCPC_Display_Handler {

    public function __construct() {
        // Display hooks
        add_filter( 'woocommerce_get_price_html', [ $this, 'display_lowest_price_message' ], 20, 2 );
        add_action( 'woocommerce_single_product_summary', [ $this, 'add_chart_canvas' ], 35 );

        // Asset enqueuing
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        
        // Add law tooltip icon to sale prices - only when compliance message is enabled
        add_filter( 'woocommerce_format_sale_price', [ $this, 'add_law_tooltip_to_sale_price' ], 10, 3 );

        
        // AJAX handlers for variation chart data
        add_action( 'wp_ajax_wcpc_get_variation_chart_data', [ $this, 'ajax_get_variation_chart_data' ] );
        add_action( 'wp_ajax_nopriv_wcpc_get_variation_chart_data', [ $this, 'ajax_get_variation_chart_data' ] );
        
        add_action( 'wp_ajax_wcpc_get_variation_compliance_data', [ $this, 'ajax_get_variation_compliance_data' ] );
        add_action( 'wp_ajax_nopriv_wcpc_get_variation_compliance_data', [ $this, 'ajax_get_variation_compliance_data' ] );        
        
        
        add_action( 'woocommerce_single_product_summary', [ $this, 'force_display_variable_compliance_info' ], 10 );
        
        // Settings page
        add_filter( 'woocommerce_get_settings_pages', [ $this, 'add_settings_page' ] );
    }
    
    /**
     * Display the lowest price in the last 30 days.
     */
    public function display_lowest_price_message( $price_html, $product ) {
        if ( get_option('wcpc_show_lowest_price_message', 'yes') !== 'yes' ) {
            return $price_html;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_price_history';
        $product_id = $product->get_id();

        // Get custom period (default 30 days)
        $period_days = get_option( 'wcpc_custom_period_days', 30 );
        $period_start = gmdate( 'Y-m-d H:i:s', strtotime( "-{$period_days} days" ) );

        // Necessary for custom table operations - no WP API available
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $lowest_price = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(price) FROM $table_name WHERE product_id = %d AND date >= %s", $product_id, $period_start ) );

        if ( $lowest_price && floatval($lowest_price) < floatval($product->get_regular_price()) ) {
            // Get custom message template
            $message_template = get_option( 'wcpc_lowest_price_text', 'Lowest price in the last 30 days: %s' );
            $message = sprintf( $message_template, wc_price($lowest_price) );

            $price_html .= '<p class="wcpc-lowest-price-message">' . $message . '</p>';
        }

        return $price_html;
    }

    
    /**
     * Add law tooltip icon to sale prices - only when compliance message is enabled
     */
    public function add_law_tooltip_to_sale_price( $price, $regular_price, $sale_price ) {
        // Check if compliance message is enabled first
        if ( get_option('wcpc_show_lowest_price_message', 'yes') !== 'yes' ) {
            return $price;
        }
        
        // Only add if tooltip is configured and we're on frontend
        $law_tooltip = get_option( 'wcpc_law_tooltip', '' );
        if ( empty( $law_tooltip ) || is_admin() ) {
            return $price;
        }

        // Add the helper icon with tooltip
        $tooltip_icon = '<span class="wcpc-law-tooltip" title="' . esc_attr( $law_tooltip ) . '">ℹ️</span>';

        // Add icon after the sale price
        return $price . ' ' . $tooltip_icon;
    }    
    
    /**
     * Add the canvas element for the chart.
     */
    public function add_chart_canvas() {
        if ( get_option('wcpc_show_chart', 'yes') === 'yes' ) {
            echo '<div class="wcpc-chart-container" style="display:none;"><canvas id="wcpcPriceChart"></canvas></div>';
        }
    }
    
    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts() {
        if ( is_product() ) {
            // Enqueue frontend CSS
            wp_enqueue_style( 'wcpc-frontend-css', WCPC_PLUGIN_URL . 'assets/css/frontend.css', [], WCPC_VERSION );

            if ( get_option('wcpc_show_chart', 'yes') === 'yes' ) {
                // Get the global product object safely
                global $woocommerce, $product;
                
                // Make sure we have a valid product object
                if ( ! is_object( $product ) ) {
                    $product = wc_get_product( get_the_ID() );
                }
                
                // If we still don't have a product, try to get it from the global post
                if ( ! is_object( $product ) ) {
                    global $post;
                    if ( isset( $post->ID ) ) {
                        $product = wc_get_product( $post->ID );
                    }
                }
                
                // Only proceed if we have a valid product object
                if ( ! is_object( $product ) || ! method_exists( $product, 'is_type' ) ) {
                    return;
                }
                
                // Enqueue Chart.js from CDN
                wp_enqueue_script( 'wcpc-chartjs-org', WCPC_PLUGIN_URL . 'assets/js/chart.min.js', [], '3.9.1', true );
                
                // Enqueue our chart script
                wp_enqueue_script( 'wcpc-chart-js', WCPC_PLUGIN_URL . 'assets/js/chart.js', ['wcpc-chartjs-org', 'jquery'], WCPC_VERSION, true );
                
                // For variable products, don't load initial data - let JS handle it
                if ( $product->is_type('variable') ) {
                    wp_localize_script( 'wcpc-chart-js', 'wcpc_chart_data', [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('wcpc_chart_nonce'),
                        'is_variable' => true,
                        'product_id' => $product->get_id()
                    ]);
                } else {
                    // For simple products, load data as before
                    $price_history = $this->get_price_history_for_chart($product->get_id());
                    wp_localize_script( 'wcpc-chart-js', 'wcpc_chart_data', [
                        'labels' => wp_list_pluck($price_history, 'date'),
                        'data'   => wp_list_pluck($price_history, 'price'),
                        'label'  => esc_html__('Price History', 'wc-price-history-compliance'),
                        'has_data' => count($price_history) >= 1,
                        'is_variable' => false
                    ]);
                }
            }
        }
    }
    
    /**
     * AJAX handler to get variation chart data.
     */
    public function ajax_get_variation_chart_data() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'wcpc_chart_nonce' ) ) {
            wp_die( 'Security check failed' );
        }       
        
        $variation_id = intval( $_POST['variation_id'] ?? 0 );
        
        if ( ! $variation_id ) {
            wp_send_json_error( 'Invalid variation ID' );
        }
        
        $price_history = $this->get_price_history_for_chart( $variation_id );
        
        wp_send_json_success( [
            'labels' => wp_list_pluck($price_history, 'date'),
            'data'   => wp_list_pluck($price_history, 'price'),
            'label'  => esc_html__('Price History', 'wc-price-history-compliance'),
            'has_data' => count($price_history) >= 1
        ] );
    }
    
    /**
     * Get price history data formatted for the chart.
     */
    private function get_price_history_for_chart( $product_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_price_history';
        //$sixty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-60 days' ) );
        
        $period_days = get_option( 'wcpc_custom_period_days', 30 );
        $chart_period = max( $period_days * 2, 60 ); // Show 2x the period for better chart, minimum 60 days
        $period_start = gmdate( 'Y-m-d H:i:s', strtotime( "-{$chart_period} days" ) );        
        
        
        // Necessary for custom table operations - no WP API available
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT price, DATE_FORMAT(date, '%%b %%d') as date FROM $table_name WHERE product_id = %d AND date >= %s ORDER BY date ASC", $product_id, $period_start ) );
        
        // Add the regular price as the first entry if we have price change history
        if ( !empty($results) ) {
            $product = wc_get_product( $product_id );
            if ( $product && $product->get_regular_price() ) {
                $regular_price = $product->get_regular_price();
                $first_recorded_price = $results[0]->price;
                
                // Only add regular price if it's different from the first recorded price
                if ( floatval($regular_price) != floatval($first_recorded_price) ) {
                    $initial_entry = (object) [
                        'price' => $regular_price,
                        'date' => 'Initial'
                    ];
                    array_unshift( $results, $initial_entry );
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Add settings page to WooCommerce.
     */
    public function add_settings_page( $settings ) {
        $settings[] = include( WCPC_PLUGIN_DIR . 'includes/class-admin-settings.php' );
        return $settings;
    }
    
    
    /**
     * Force display compliance info for variable products when all variations have same price
     */
    public function force_display_variable_compliance_info() {
        if ( get_option('wcpc_show_lowest_price_message', 'yes') !== 'yes' ) {
            return;
        }

        global $product;

        if ( ! $product || ! $product->is_type('variable') ) {
            return;
        }

        // Get all variation prices
        $available_variations = $product->get_available_variations();
        $prices = wp_list_pluck($available_variations, 'display_price');

        // If all variations have same price, WooCommerce may hide the price block
        if ( count(array_unique($prices)) === 1 ) {
            // Output a placeholder div that JavaScript will populate
            echo '<div id="wcpc-forced-compliance-container" style="display:none;"></div>';

            // Add JavaScript to handle variation selection
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                var $container = $('#wcpc-forced-compliance-container');
                var $form = $('form.variations_form');

                // Function to get compliance data for a variation
                function getComplianceData(variationId) {
                    return $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'wcpc_get_variation_compliance_data',
                            variation_id: variationId,
                            nonce: '<?php echo wp_create_nonce('wcpc_compliance_nonce'); ?>'
                        }
                    });
                }

                // Handle variation selection
                $form.on('found_variation', function(event, variation) {
                    if (variation && variation.variation_id) {
                        getComplianceData(variation.variation_id).done(function(response) {
                            if (response.success && response.data.has_compliance) {
                                $container.html(response.data.html).show();
                            } else {
                                $container.hide().empty();
                            }
                        });
                    }
                });

                // Handle variation reset/clear
                $form.on('reset_data', function() {
                    $container.hide().empty();
                });
            });
            </script>
            <?php
        }
    }

    // ADD this new AJAX handler method to the class:
    /**
     * AJAX handler to get variation compliance data
     */
    public function ajax_get_variation_compliance_data() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'wcpc_compliance_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        $variation_id = intval( $_POST['variation_id'] ?? 0 );

        if ( ! $variation_id ) {
            wp_send_json_error( 'Invalid variation ID' );
        }

        $variation = wc_get_product( $variation_id );
        if ( ! $variation ) {
            wp_send_json_error( 'Variation not found' );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_price_history';

        // Get custom period (default 30 days)
        $period_days = get_option( 'wcpc_custom_period_days', 30 );
        $period_start = gmdate( 'Y-m-d H:i:s', strtotime( "-{$period_days} days" ) );

        // Check for compliance data
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $lowest_price = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(price) FROM $table_name WHERE product_id = %d AND date >= %s", $variation_id, $period_start ) );

        $has_compliance = false;
        $html = '';

        if ( $lowest_price && floatval($lowest_price) < floatval($variation->get_regular_price()) ) {
            $has_compliance = true;

            // Get custom message template
            $message_template = get_option( 'wcpc_lowest_price_text', 'Lowest price in the last 30 days: %s' );
            $message = sprintf( $message_template, wc_price($lowest_price) );

            $html = '<div class="wcpc-forced-compliance-info">';
            $html .= '<p class="wcpc-lowest-price-message">' . $message . '</p>';

            // Add tooltip if configured
//            $law_tooltip = get_option( 'wcpc_law_tooltip', '' );
//            if ( ! empty( $law_tooltip ) ) {
//                $html .= ' <span class="wcpc-law-tooltip" title="' . esc_attr( $law_tooltip ) . '">ℹ️</span>';
//            }

            $html .= '</div>';
        }

        wp_send_json_success( [
            'has_compliance' => $has_compliance,
            'html' => $html
        ] );
    }    
    
    
}