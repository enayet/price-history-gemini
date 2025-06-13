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
 * Handles activation and deactivation tasks.
 */
class WCPC_Install_Handler {

    /**
     * Activation hook.
     * Creates the custom database table.
     */
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_price_history';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            price decimal(19,4) NOT NULL,
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Deactivation hook.
     * Placeholder for future cleanup tasks.
     */
    public static function deactivate() {
        // Can add cleanup tasks here if needed in the future,
        // such as removing scheduled events or plugin options.
    }
}