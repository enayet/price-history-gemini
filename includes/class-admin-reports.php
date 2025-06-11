<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Creates the admin reports page with a searchable list of price changes.
 */
class WCPC_Price_History_List_Table extends WP_List_Table {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wc_price_history';
        parent::__construct([
            'singular' => 'Price Record',
            'plural'   => 'Price Records',
            'ajax'     => false
        ]);
    }
    
    public function get_columns() {
        return [
            'product_name' => 'Product',
            'price'        => 'Price',
            'date'         => 'Date Recorded'
        ];
    }
    
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }
    
    public function column_price($item) {
        return wc_price($item['price']);
    }

    public function column_product_name($item) {
        $product = wc_get_product($item['product_id']);
        if ($product) {
            $edit_link = get_edit_post_link($item['product_id']);
            return sprintf('<a href="%s">%s</a>', esc_url($edit_link), $product->get_formatted_name());
        }
        return 'Product not found (ID: ' . $item['product_id'] . ')';
    }

    public function prepare_items() {
        global $wpdb;
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];
        
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_REQUEST['s']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'search_price_history')) {
            $search_term = sanitize_text_field(wp_unslash($_REQUEST['s']));
        }        
        
        
        $query = "SELECT * FROM {$this->table_name}";
        $params = [];

        if ($search_term) {
            // This is a simple search. A real plugin might search product names in the posts table.
             $query .= " WHERE product_id = %d";
             $params[] = $search_term;
        }

        $query .= " ORDER BY date DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $this->items = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);
        
        $total_items_query = "SELECT COUNT(id) FROM {$this->table_name}";
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching 
        $total_items = $wpdb->get_var($total_items_query);
        
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);
    }
}

class WCPC_Admin_Reports {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Price History Report',
            'Price History',
            'manage_woocommerce',
            'wcpc-price-history',
            [$this, 'render_reports_page']
        );
    }
    
    public function render_reports_page() {
        $list_table = new WCPC_Price_History_List_Table();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Price History Report</h1>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr(wp_unslash($_REQUEST['page'])); ?>" />
                <?php $list_table->search_box('Search by Product ID', 'product_id'); ?>
            </form>
            <?php $list_table->display(); ?>
        </div>
        <?php
    }

    public function enqueue_styles($hook) {
        if ('woocommerce_page_wcpc-price-history' !== $hook) {
            return;
        }
        wp_enqueue_style( 'wcpc-admin-css', WCPC_PLUGIN_URL . 'assets/css/admin.css', [], WCPC_VERSION );
    }
}