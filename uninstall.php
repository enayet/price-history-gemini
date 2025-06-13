<?php
/**
 * Uninstall WooCommerce Price History & Sale Compliance
 *
 * @package WCPC
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
delete_option( 'wcpc_show_lowest_price_message' );
delete_option( 'wcpc_lowest_price_text' );
delete_option( 'wcpc_custom_period_days' );
delete_option( 'wcpc_law_tooltip' );
delete_option( 'wcpc_show_chart' );

// Optionally drop the custom table (be careful with this)
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wc_price_history" );