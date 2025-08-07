<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Evento_Checkin_Attendees_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Attendee', 'evento-check-in' ),
            'plural'   => __( 'Attendees', 'evento-check-in' ),
            'ajax'     => false,
        ] );
    }

    public function get_columns() {
        $columns = [
            'cb'                => '<input type="checkbox" />',
            'name'              => __( 'Name', 'evento-check-in' ),
            'email'             => __( 'Email', 'evento-check-in' ),
            'registration_date' => __( 'Registration Date', 'evento-check-in' ),
            'check_in_status'   => __( 'Checked In', 'evento-check-in' ),
        ];
        return $columns;
    }

    public function prepare_items() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'evento_attendees';
        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );

        $orderby = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( $_GET['orderby'] ) : 'registration_date';
        $order = isset( $_GET['order'] ) ? strtoupper( sanitize_key( $_GET['order'] ) ) : 'DESC';

        $offset = ( $current_page - 1 ) * $per_page;

        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
    }

    protected function get_sortable_columns() {
        return [
            'name'              => [ 'name', false ],
            'email'             => [ 'email', false ],
            'registration_date' => [ 'registration_date', true ],
            'check_in_status'   => [ 'check_in_status', false ],
        ];
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'registration_date':
                return date( 'Y-m-d H:i:s', strtotime( $item[ $column_name ] ) );
            case 'check_in_status':
                return $item[ $column_name ] ? __( 'Yes', 'evento-check-in' ) : __( 'No', 'evento-check-in' );
            default:
                return esc_html( $item[ $column_name ] );
        }
    }

    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="attendee[]" value="%d" />',
            $item['id']
        );
    }
}
