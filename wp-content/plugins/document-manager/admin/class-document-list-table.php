<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Document_List_Table
 *
 * Renders the list of documents in the admin area.
 */
class Document_List_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => 'document',
            'plural'   => 'documents',
            'ajax'     => false, // True if you want to load items via AJAX
        ] );
    }

    /**
     * Get a list of columns.
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'          => '<input type="checkbox" />', // Checkbox for bulk actions
            'title'       => __( 'Title', 'document-manager' ),
            'file_name'   => __( 'File Name', 'document-manager' ),
            'uploaded_by' => __( 'Uploaded By', 'document-manager' ),
            'uploaded_on' => __( 'Uploaded On', 'document-manager' ),
        ];
        return $columns;
    }

    /**
     * Default column rendering.
     *
     * @param array $item
     * @param string $column_name
     * @return mixed
     */
    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'file_name':
            case 'uploaded_on':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting
        }
    }

    /**
     * Render the checkbox column.
     *
     * @param array $item
     * @return string
     */
    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }

    /**
     * Render the title column.
     *
     * @param array $item
     * @return string
     */
    protected function column_title( $item ) {
        // Actions to be added later: Edit, Delete, View
        $actions = [];
        return sprintf( '%1$s %2$s',
            esc_html( $item['title'] ),
            $this->row_actions( $actions )
        );
    }

    /**
     * Render the uploaded_by column.
     *
     * @param array $item
     * @return string
     */
    protected function column_uploaded_by( $item ) {
        $user = get_userdata( $item['uploaded_by'] );
        return $user ? esc_html( $user->display_name ) : __( 'Unknown User', 'document-manager' );
    }

    /**
     * Prepare the items for the table to display.
     */
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'documents';

        $per_page = $this->get_items_per_page( 'documents_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name}" );

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );

        $offset = ( $current_page - 1 ) * $per_page;
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, title, file_name, uploaded_by, uploaded_on FROM {$table_name} ORDER BY uploaded_on DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ), ARRAY_A
        );

        $columns = $this->get_columns();
        $hidden = []; // Define hidden columns if any
        $sortable = $this->get_sortable_columns(); // Define sortable columns
        $this->_column_headers = [ $columns, $hidden, $sortable ];
    }

    /**
     * Get a list of sortable columns.
     *
     * @return array
     */
    protected function get_sortable_columns() {
        // For now, let's make title and uploaded_on sortable.
        // The actual sorting logic will need to be added to prepare_items if not handled by default.
        $sortable_columns = [
            'title'       => [ 'title', false ], // True for default sorting
            'uploaded_on' => [ 'uploaded_on', true ],
        ];
        return $sortable_columns;
    }
}
