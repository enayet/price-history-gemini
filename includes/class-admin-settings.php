<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCPC_Admin_Settings extends WC_Settings_Page {
    public function __construct() {
        $this->id    = 'wcpc_settings';
        $this->label = esc_html__( 'Price History', 'wc-price-history-compliance' );
        parent::__construct();
    }

    public function get_settings( $current_section = '' ) {
        return apply_filters( 'wcpc_get_settings', [
            [
                'title' => esc_html__( 'Compliance Settings', 'wc-price-history-compliance' ),
                'type'  => 'title',
                'desc'  => esc_html__( 'Settings for displaying the lowest price as per EU Omnibus Directive.', 'wc-price-history-compliance' ),
                'id'    => 'wcpc_compliance_options',
            ],
            [
                'title'   => esc_html__( 'Enable 30-Day Lowest Price Message', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_show_lowest_price_message',
                'default' => 'yes',
                'type'    => 'checkbox',
            ],
            [
                'title'   => esc_html__( 'Message Text', 'wc-price-history-compliance' ),
                'desc'    => esc_html__( 'Customize the text. Use %s as a placeholder for the price.', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_lowest_price_text',
                'default' => 'Lowest price in the last 30 days: %s',
                'type'    => 'text',
                'css'     => 'width: 400px;',
            ],
            
            [
                'title'   => __( 'Custom Period (Days)', 'wc-price-history-compliance' ),
                'desc'    => __( 'Number of days to track for lowest price (default: 30)', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_custom_period_days',
                'default' => 30,
                'type'    => 'number',
                'custom_attributes' => [
                    'min' => 1,
                    'max' => 365,
                    'step' => 1
                ],
                'css'     => 'width: 100px;',
            ],
            [
                'title'   => __( 'Law/Compliance Tooltip', 'wc-price-history-compliance' ),
                'desc'    => __( 'Optional: Add legal/compliance information to show as tooltip next to sale prices. Leave empty to disable.', 'wc-price-history-compliance' ),
                'id'      => 'wcpc_law_tooltip',
                'default' => '',
                'type'    => 'textarea',
                'css'     => 'width: 500px; height: 80px;',
                'placeholder' => 'e.g., "Price complies with EU Omnibus Directive 2019/2161 requiring display of lowest price in 30 days prior to discount."'
            ],          
            
            
            [
                'type' => 'sectionend',
                'id'   => 'wcpc_compliance_options',
            ],
            [
                'title' => esc_html__( 'Price Chart Settings', 'wc-price-history-compliance' ),
                'type'  => 'title',
                'id'    => 'wcpc_chart_options',
            ],
            [
                'title'   => esc_html__( 'Enable Price History Chart', 'wc-price-history-compliance' ),
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