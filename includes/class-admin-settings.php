<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCPC_Admin_Settings extends WC_Settings_Page {
    public function __construct() {
        $this->id    = 'wcpc_settings';
        $this->label = __( 'Price History', 'wc-price-history-compliance' );
        parent::__construct();
    }

    public function get_settings( $current_section = '' ) {
        return apply_filters( 'wcpc_get_settings', [
            [
                'title' => __( 'Compliance Settings', 'wc-price-history-compliance' ),
                'type'  => 'title',
                'desc'  => __( 'Settings for displaying the lowest price as per EU Omnibus Directive.', 'wc-price-history-compliance' ),
                'id'    => 'wcpc_compliance_options',
            ],
            [
                'title'   => __( 'Enable 30-Day Lowest Price Message', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_show_lowest_price_message',
                'default' => 'yes',
                'type'    => 'checkbox',
            ],
            [
                'title'   => __( 'Message Text', 'wc-price-history-compliance' ),
                'desc'    => __( 'Customize the text. Use %s as a placeholder for the price.', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_lowest_price_text',
                'default' => 'Lowest price in the last 30 days: %s',
                'type'    => 'text',
                'css'     => 'width: 400px;',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'wcpc_compliance_options',
            ],
            [
                'title' => __( 'Price Chart Settings', 'wc-price-history-compliance' ),
                'type'  => 'title',
                'id'    => 'wcpc_chart_options',
            ],
            [
                'title'   => __( 'Enable Price History Chart', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_show_chart',
                'default' => 'yes',
                'type'    => 'checkbox',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'wcpc_chart_options',
            ],
        ]);
    }
}

return new WCPC_Admin_Settings();