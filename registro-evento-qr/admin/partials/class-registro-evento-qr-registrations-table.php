<?php
/**
 * WP_List_Table para mostrar los registros del evento.
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/admin/partials
 * @author     Jules
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Registro_Evento_QR_Registrations_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Registro', 'registro-evento-qr' ), // Singular label
            'plural'   => __( 'Registros', 'registro-evento-qr' ),   // Plural label
            'ajax'     => false, // We won't support Ajax for now
        ) );
    }

    /**
     * Prepara los items para la tabla.
     */
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'registros_evento';

        $per_page = $this->get_items_per_page( 'registrations_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ) );

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // Procesar acciones masivas
        $this->process_bulk_action();

        // Obtener datos
        $orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST['orderby'] : 'fecha_registro';
        $order = ( isset( $_REQUEST['order'] ) && in_array( strtoupper( $_REQUEST['order'] ), array( 'ASC', 'DESC' ) ) ) ? strtoupper( $_REQUEST['order'] ) : 'DESC';

        $offset = ( $current_page - 1 ) * $per_page;
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ), ARRAY_A
        );
    }

    /**
     * Define las columnas de la tabla.
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'             => '<input type="checkbox" />', // Checkbox para acciones masivas
            'nombre'         => __( 'Nombre', 'registro-evento-qr' ),
            'email'          => __( 'Email', 'registro-evento-qr' ),
            'empresa'        => __( 'Empresa', 'registro-evento-qr' ),
            'puesto'         => __( 'Puesto', 'registro-evento-qr' ),
            'telefono'       => __( 'Teléfono', 'registro-evento-qr' ),
            'qr_code_hash'   => __( 'Clave QR', 'registro-evento-qr' ),
            'fecha_registro' => __( 'Fecha Registro', 'registro-evento-qr' ),
            'confirmado'     => __( 'Confirmado', 'registro-evento-qr' ),
            'acciones'       => __( 'Acciones', 'registro-evento-qr' ),
        );
        return $columns;
    }

    /**
     * Define qué columnas son ocultables.
     * @return array
     */
    public function get_hidden_columns() {
        return array('qr_code_hash'); // Ocultar la clave QR por defecto, es larga.
    }

    /**
     * Define qué columnas son ordenables.
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'nombre'         => array( 'nombre', false ),
            'email'          => array( 'email', false ),
            'empresa'        => array( 'empresa', false ),
            'fecha_registro' => array( 'fecha_registro', true ), //true মানে ডিফল্ট সর্টিং কলাম
            'confirmado'     => array( 'confirmado', false ),
        );
        return $sortable_columns;
    }

    /**
     * Define el contenido por defecto de una celda si no hay un método específico.
     * @param  array $item
     * @param  string $column_name
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'puesto':
            case 'telefono':
                return $item[ $column_name ] ? esc_html( $item[ $column_name ] ) : '<em>' . __( 'N/A', 'registro-evento-qr' ) . '</em>';
            case 'qr_code_hash':
                return '<code>' . esc_html( substr($item[ $column_name ], 0, 15) ) . '...</code>'; // Acortar para visualización
            case 'fecha_registro':
                return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item[ $column_name ] ) );
            default:
                return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : print_r( $item, true ); //Muestra el array completo si no se encuentra la columna
        }
    }

    /**
     * Renderiza la celda 'cb' (checkbox).
     * @param  array $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="registrations[]" value="%s" />', $item['id']
        );
    }

    /**
     * Renderiza la columna 'nombre'.
     * @param  array $item
     * @return string
     */
    function column_nombre( $item ) {
        $delete_nonce = wp_create_nonce( 'reqr_delete_registration_admin_nonce' );
        $resend_nonce = wp_create_nonce( 'reqr_resend_email_admin_nonce' );

        $page_slug = 'registro-evento-qr'; // El slug base del menú del plugin

        $actions = array(
            'resend_email' => sprintf( '<a href="#" class="reqr-resend-email" data-id="%d" data-nonce="%s">%s</a>', $item['id'], $resend_nonce, __( 'Reenviar Email', 'registro-evento-qr' ) ),
            'delete' => sprintf( '<a href="#" class="reqr-delete-registro" data-id="%d" data-nonce="%s" style="color:#a00;">%s</a>', $item['id'], $delete_nonce, __( 'Eliminar', 'registro-evento-qr' ) ),
        );

        // Podríamos añadir una acción para editar si fuera necesario
        // 'edit'   => sprintf( '<a href="?page=%s&action=%s&registration=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', $item['id'] ),

        return sprintf( '<strong>%1$s</strong>%2$s', esc_html( $item['nombre'] ), $this->row_actions( $actions ) );
    }

    /**
     * Renderiza la columna 'confirmado'.
     * @param  array $item
     * @return string
     */
    function column_confirmado( $item ) {
        if ( $item['confirmado'] ) {
            return '<span class="status-confirmado" style="color:green;font-weight:bold;">&#10004; ' . __( 'Sí', 'registro-evento-qr' ) . '</span>';
        } else {
            // Podríamos añadir una acción para marcar como confirmado manualmente aquí
            // $mark_confirmed_nonce = wp_create_nonce('reqr_mark_confirmed_nonce_' . $item['id']);
            // $mark_link = sprintf('<a href="?page=%s&action=mark_confirmed&id=%s&_wpnonce=%s" class="button button-small">Marcar</a>', $_REQUEST['page'], $item['id'], $mark_confirmed_nonce);
            // return '<span class="status-no-confirmado" style="color:red;">' . __( 'No', 'registro-evento-qr' ) . '</span> ' . $mark_link;
            return '<span class="status-no-confirmado" style="color:red;">&ndash; ' . __( 'No', 'registro-evento-qr' ) . '</span>';
        }
    }

    /**
     * Renderiza la columna 'acciones' (acciones directas en la fila).
     * @param  array $item
     * @return string
     */
    function column_acciones( $item ) {
        $resend_nonce = wp_create_nonce( 'reqr_resend_email_admin_nonce' );
        $delete_nonce = wp_create_nonce( 'reqr_delete_registration_admin_nonce' );
        $page_slug = 'registro-evento-qr';

        $actions_html = sprintf(
            '<button type="button" class="button button-secondary reqr-resend-email" data-id="%1$d" data-nonce="%2$s" title="%3$s"><span class="dashicons dashicons-email-alt"></span></button>',
            $item['id'],
            $resend_nonce,
            __( 'Reenviar Email de Confirmación', 'registro-evento-qr' )
        );
        $actions_html .= ' ';
        $actions_html .= sprintf(
            '<button type="button" class="button button-secondary reqr-delete-registro" data-id="%1$d" data-nonce="%2$s" title="%3$s" style="color:#a00;"><span class="dashicons dashicons-trash"></span></button>',
            $item['id'],
            $delete_nonce,
            __( 'Eliminar Registro', 'registro-evento-qr' )
        );
        return $actions_html;
    }


    /**
     * Define las acciones masivas.
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'bulk-delete'    => __( 'Eliminar Seleccionados', 'registro-evento-qr' ),
            'bulk-resend-email' => __( 'Reenviar Email a Seleccionados', 'registro-evento-qr' ),
        );
        return $actions;
    }

    /**
     * Procesa las acciones masivas.
     */
    public function process_bulk_action() {
        // Detecta cuándo se ha enviado una acción masiva
        if ( 'delete' === $this->current_action() ) { // Acción individual de la fila 'nombre'
            // check_admin_referer('reqr_delete_registration_admin_nonce'); // No, el nonce está en el data-attribute
            // La eliminación individual se maneja por AJAX en registro-evento-qr-admin.js
        }

        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {
            $delete_ids = esc_sql( $_POST['registrations'] );

            if ( empty($delete_ids) ) {
                $this->admin_redirect_with_message(__( 'No se seleccionaron registros para eliminar.', 'registro-evento-qr' ), 'error');
                return;
            }

            check_admin_referer( 'reqr_bulk_action_registrations', 'reqr_bulk_nonce' ); // Verificar nonce del formulario

            if ( !current_user_can('manage_options') ) { // Solo admins pueden borrar en masa
                $this->admin_redirect_with_message(__( 'No tienes permisos para eliminar registros.', 'registro-evento-qr' ), 'error');
                return;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'registros_evento';
            $ids_string = implode( ',', array_map( 'absint', $delete_ids ) );

            // Opcional: eliminar archivos QR asociados
            $registros_a_borrar = $wpdb->get_results( "SELECT id, qr_code_hash FROM $table_name WHERE id IN ($ids_string)" );
            foreach ($registros_a_borrar as $reg) {
                $qr_filename_pattern = 'qr_registro_' . $reg->id . '_' . md5($reg->qr_code_hash) . '.png';
                $upload_dir = wp_upload_dir();
                $qr_filepath = $upload_dir['basedir'] . '/registro-evento-qr/' . $qr_filename_pattern;
                if (file_exists($qr_filepath)) {
                    wp_delete_file($qr_filepath);
                }
            }

            $deleted_count = $wpdb->query( "DELETE FROM $table_name WHERE id IN ($ids_string)" );

            if (false === $deleted_count) {
                 $this->admin_redirect_with_message(__( 'Error al eliminar los registros.', 'registro-evento-qr' ), 'error');
            } else {
                 $this->admin_redirect_with_message(sprintf(__( '%d registro(s) eliminado(s) exitosamente.', 'registro-evento-qr' ), $deleted_count ));
            }
            return;
        }


        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-resend-email' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-resend-email' )
        ) {
            $resend_ids = esc_sql( $_POST['registrations'] );

            if ( empty($resend_ids) ) {
                $this->admin_redirect_with_message(__( 'No se seleccionaron registros para reenviar email.', 'registro-evento-qr' ), 'error');
                return;
            }

            check_admin_referer( 'reqr_bulk_action_registrations', 'reqr_bulk_nonce' );

            if ( !current_user_can('manage_options') ) {
                $this->admin_redirect_with_message(__( 'No tienes permisos para esta acción.', 'registro-evento-qr' ), 'error');
                return;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'registros_evento';
            $ids_string = implode( ',', array_map( 'absint', $resend_ids ) );
            $registros = $wpdb->get_results( "SELECT * FROM $table_name WHERE id IN ($ids_string)" );

            if (empty($registros)) {
                $this->admin_redirect_with_message(__( 'No se encontraron los registros seleccionados.', 'registro-evento-qr' ), 'error');
                return;
            }

            $public_class_instance = new Registro_Evento_QR_Public( 'registro-evento-qr', REG_EVENTO_QR_VERSION ); // Necesitamos una instancia
            $sent_count = 0;
            $error_count = 0;

            foreach ( $registros as $registro ) {
                $qr_image_url = $public_class_instance->generate_qr_code_image( $registro->qr_code_hash, $registro->id );
                if ($qr_image_url) {
                    $public_class_instance->send_confirmation_email( (array) $registro, $registro->qr_code_hash, $qr_image_url );
                    $sent_count++;
                } else {
                    $error_count++;
                }
            }

            $message = sprintf(__( '%d correo(s) reenviado(s) exitosamente.', 'registro-evento-qr' ), $sent_count);
            if ($error_count > 0) {
                $message .= ' ' . sprintf(__( '%d correo(s) no pudieron ser enviados debido a un error generando el QR.', 'registro-evento-qr' ), $error_count);
                $this->admin_redirect_with_message($message, ($sent_count > 0 ? 'updated' : 'error')); // updated si algunos se enviaron
            } else {
                $this->admin_redirect_with_message($message);
            }
            return;
        }
    }

    /**
     * Helper para redirigir con mensaje.
     * @param string $message
     * @param string $type 'updated' o 'error'
     */
    private function admin_redirect_with_message( $message, $type = 'updated' ) {
        $redirect_url = add_query_arg(
            array(
                'page' => $_REQUEST['page'], // Mantener la página actual
                'message' => urlencode($message),
                'type' => $type,
            ),
            admin_url( 'admin.php' ) // URL base para páginas de admin
        );
        wp_redirect( esc_url_raw( $redirect_url ) );
        exit;
    }


    /**
     * Mensaje a mostrar si no hay items.
     */
    public function no_items() {
        esc_html_e( 'No se encontraron registros.', 'registro-evento-qr' );
    }
}
