<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles automatic recording of price changes.
 */
class WCPC_Price_Tracker {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wc_price_history';

        add_action( 'woocommerce_update_product', [ $this, 'track_price_change' ] );
        add_action( 'woocommerce_save_product_variation', [ $this, 'track_price_change' ], 10, 1 ); // Variation ID is passed
    }

    /**
     * Record price changes when a product is updated.
     */
    public function track_price_change( $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return;
        }

        global $wpdb;
        
        // For variations, use the variation ID. For simple products, use the product ID.
        $id_to_track = $product->is_type('variation') ? $product->get_id() : $product_id;

        // Use get_price() to get the currently active price (sale or regular)
        $current_price = $product->get_price();

        // Get the last recorded price
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $last_recorded_price = $wpdb->get_var( $wpdb->prepare(
            "SELECT price FROM {$this->table_name} WHERE product_id = %d ORDER BY date DESC LIMIT 1",
            $id_to_track
        ) );
        
        // Only record if the price has changed (using a small epsilon for float comparison)
        if ( is_null($last_recorded_price) || abs(floatval($current_price) - floatval($last_recorded_price)) > 0.0001 ) {
            $wpdb->insert(
                $this->table_name,
                [
                    'product_id' => $id_to_track,
                    'price'      => $current_price,
                    'date'       => current_time( 'mysql' ),
                ],
                [ '%d', '%f', '%s' ]
            );
        }
    }
}