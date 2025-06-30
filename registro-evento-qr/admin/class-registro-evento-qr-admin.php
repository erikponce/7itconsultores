<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/admin
 * @author     Jules <jules@example.com>
 */
class Registro_Evento_QR_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook The current admin page.
	 */
	public function enqueue_styles( $hook ) {
		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Registro_Evento_QR_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Registro_Evento_QR_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        // Solo cargar en las páginas del plugin
        // Las páginas del plugin tendrán un $hook como:
        // toplevel_page_registro-evento-qr (página principal)
        // registro-evento_page_registro-evento-qr-config (subpágina de config)
        // registro-evento_page_registro-evento-qr-validation (subpágina de validación)
        $plugin_pages_hooks = array(
            'toplevel_page_' . $this->plugin_name,
            $this->plugin_name . '_page_' . $this->plugin_name . '-config',
            $this->plugin_name . '_page_' . $this->plugin_name . '-validation',
        );

        if ( in_array( $hook, $plugin_pages_hooks ) ) {
		    wp_enqueue_style( $this->plugin_name, REG_EVENTO_QR_PLUGIN_URL . 'admin/css/registro-evento-qr-admin.css', array(), $this->version, 'all' );
        }

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
     * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Registro_Evento_QR_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Registro_Evento_QR_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        // Solo cargar en las páginas del plugin
        $plugin_pages_hooks = array(
            'toplevel_page_' . $this->plugin_name,
            $this->plugin_name . '_page_' . $this->plugin_name . '-config',
            $this->plugin_name . '_page_' . $this->plugin_name . '-validation',
        );

        if ( in_array( $hook, $plugin_pages_hooks ) ) {
		    wp_enqueue_script( $this->plugin_name, REG_EVENTO_QR_PLUGIN_URL . 'admin/js/registro-evento-qr-admin.js', array( 'jquery' ), $this->version, true ); // true for footer

            // Si estamos en la página de validación del admin, encolar la librería de escaneo QR
            if ( $hook === $this->plugin_name . '_page_' . $this->plugin_name . '-validation' ) {
                // Asumimos que la librería html5-qrcode.min.js está en admin/js/libs/
                // Deberás crear esta carpeta y añadir la librería allí.
                $qr_scanner_lib_path = REG_EVENTO_QR_PLUGIN_DIR . 'admin/js/libs/html5-qrcode.min.js';
                $qr_scanner_lib_url = REG_EVENTO_QR_PLUGIN_URL . 'admin/js/libs/html5-qrcode.min.js';
                if (file_exists($qr_scanner_lib_path)) {
                    wp_enqueue_script( $this->plugin_name . '-qr-scanner-lib', $qr_scanner_lib_url, array(), $this->version, true );
                } else {
                     // Notificar al admin que la librería falta
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-error"><p>' .
                             sprintf(__( 'La librería de escaneo QR no se encontró en %s. Por favor, descárgala (html5-qrcode.min.js) y colócala en esa ruta para activar el escaneo por cámara en la página de validación del admin.', 'registro-evento-qr' ), '<code>registro-evento-qr/admin/js/libs/</code>') .
                             '</p></div>';
                    });
                }

                // Nonce para la validación AJAX en admin
                wp_localize_script( $this->plugin_name, 'reqr_admin_ajax', array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'validate_nonce' => wp_create_nonce( 'reqr_validate_qr_admin_nonce' ),
                    'resend_nonce' => wp_create_nonce( 'reqr_resend_email_admin_nonce'),
                    'delete_nonce' => wp_create_nonce( 'reqr_delete_registration_admin_nonce'),
                    'text_confirm_delete' => __('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.', 'registro-evento-qr'),
                    'text_confirm_resend' => __('¿Estás seguro de que quieres reenviar el correo de confirmación a este usuario?', 'registro-evento-qr'),
                ) );
            }
        }
	}

    /**
     * Enqueue WordPress media uploader scripts for logo upload.
     *
     * @since    1.0.0
     * @param string $hook The current admin page.
     */
    public function enqueue_media_uploader( $hook ) {
        // Solo en la página de configuración del plugin
        if ( $hook === $this->plugin_name . '_page_' . $this->plugin_name . '-config' ) {
            wp_enqueue_media();
        }
    }

    /**
     * Add the admin menu pages for the plugin.
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Registro Evento', 'registro-evento-qr' ),
            __( 'Registro Evento', 'registro-evento-qr' ),
            'manage_options', // Capacidad requerida para ver la lista de registros
            $this->plugin_name,
            array( $this, 'display_registrations_page' ),
            'dashicons-groups', // Icono del menú (mejor que universal-access para "registros")
            25 // Posición
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Configuración del Evento', 'registro-evento-qr' ), // Título de la página
            __( 'Configuración', 'registro-evento-qr' ),    // Título del Menú
            'manage_options', // Capacidad para configurar
            $this->plugin_name . '-config',
            array( $this, 'display_settings_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Validar Registros QR', 'registro-evento-qr' ), // Título de la página
            __( 'Validar QR', 'registro-evento-qr' ),       // Título del Menú
            'edit_others_posts', // Capacidad para organizadores (edit_posts es muy común, edit_others_posts es un poco más restrictiva)
                                 // Considerar un rol personalizado "Organizador de Evento" para más granularidad.
            $this->plugin_name . '-validation',
            array( $this, 'display_validation_page' )
        );
    }

    /**
     * Display the registrations page.
     * This will now use a WP_List_Table.
     * @since 1.0.0
     */
    public function display_registrations_page() {
        // La lógica de WP_List_Table se manejará en un archivo separado.
        require_once REG_EVENTO_QR_PLUGIN_DIR . 'admin/partials/class-registro-evento-qr-registrations-table.php';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Registros del Evento', 'registro-evento-qr' ) . '</h1>';

        // Mensajes de feedback (ej. después de reenviar email o eliminar)
        if (isset($_GET['message'])) {
            $message_type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'updated';
            echo '<div id="message" class="' . esc_attr($message_type) . ' notice is-dismissible"><p>' . esc_html(urldecode($_GET['message'])) . '</p></div>';
        }

        // Formulario para búsqueda y filtros (si se implementan en WP_List_Table)
        echo '<form method="post">'; // Necesario para bulk actions si se usan
        wp_nonce_field( 'reqr_bulk_action_registrations', 'reqr_bulk_nonce' ); // Nonce para bulk actions

        $registrations_table = new Registro_Evento_QR_Registrations_Table();
        $registrations_table->prepare_items();
        // $registrations_table->search_box( __( 'Buscar Registros', 'registro-evento-qr' ), 'search_id' ); // Si se añade búsqueda
        $registrations_table->display();

        echo '</form>';
        echo '</div>';
    }

    /**
     * Display the settings page.
     * @since 1.0.0
     */
    public function display_settings_page() {
        // Verificar permisos
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos suficientes para acceder a esta página.', 'registro-evento-qr' ) );
        }
        require_once REG_EVENTO_QR_PLUGIN_DIR . 'admin/partials/registro-evento-qr-admin-display-settings.php';
    }

    /**
     * Display the QR validation page.
     * @since 1.0.0
     */
    public function display_validation_page() {
         // Verificar permisos (ej. 'edit_others_posts' o un rol personalizado)
        if ( ! current_user_can( 'edit_others_posts' ) ) {
            wp_die( __( 'No tienes permisos suficientes para acceder a esta página.', 'registro-evento-qr' ) );
        }
        require_once REG_EVENTO_QR_PLUGIN_DIR . 'admin/partials/registro-evento-qr-admin-display-validation.php';
    }


    /**
     * Register the settings for the plugin.
     * @since 1.0.0
     */
    public function register_settings() {
        $settings_group_name = $this->plugin_name . '-settings-group';

        register_setting( $settings_group_name, 'reqr_event_logo_url', array(
            'sanitize_callback' => 'esc_url_raw', // Mejor para URLs
            'default' => ''
        ) );
        register_setting( $settings_group_name, 'reqr_email_body', array(
            'sanitize_callback' => 'wp_kses_post', // Permite HTML seguro
            'default' => $this->get_public_class_instance()->get_default_email_body() // Usar el método de la clase pública
        ) );

        add_settings_section(
            'reqr_general_settings_section',
            __( 'Configuración General del Evento y Correo', 'registro-evento-qr' ),
            array( $this, 'general_settings_section_callback' ),
            $settings_group_name // Page slug
        );

        add_settings_field(
            'reqr_event_logo_url_field', // ID único para el campo
            __( 'Logo del Evento', 'registro-evento-qr' ),
            array( $this, 'event_logo_url_render' ),
            $settings_group_name, // Page slug
            'reqr_general_settings_section' // ID de la sección
        );

        add_settings_field(
            'reqr_email_body_field', // ID único para el campo
            __( 'Cuerpo del Correo de Confirmación', 'registro-evento-qr' ),
            array( $this, 'email_body_render' ),
            $settings_group_name, // Page slug
            'reqr_general_settings_section' // ID de la sección
        );
    }

    public function general_settings_section_callback() {
        echo '<p>' . esc_html__( 'Personaliza el logo que se mostrará en los correos de confirmación y el contenido de dichos correos.', 'registro-evento-qr' ) . '</p>';
    }

    public function event_logo_url_render() {
        $option_name = 'reqr_event_logo_url';
        $option_value = get_option( $option_name );
        ?>
        <input type='text' name='<?php echo esc_attr( $option_name ); ?>' id='<?php echo esc_attr( $option_name ); ?>' value='<?php echo esc_attr( $option_value ); ?>' class='regular-text'>
        <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="<?php esc_attr_e( 'Subir/Seleccionar Logo', 'registro-evento-qr' ); ?>">
        <p class="description"><?php esc_html_e( 'Ingresa la URL del logo o súbelo/selecciónalo de la biblioteca de medios. El logo se mostrará en el correo de confirmación.', 'registro-evento-qr' ); ?></p>
        <div id="logo-preview-container" style="margin-top:10px;">
            <?php if ( ! empty( $option_value ) ) : ?>
                <img src="<?php echo esc_url( $option_value ); ?>" style="max-width:200px; height:auto; border:1px solid #ddd; padding:5px; background:#fff;" />
            <?php endif; ?>
        </div>
        <?php
    }

    public function email_body_render() {
        $public_class = $this->get_public_class_instance();
        $option_name = 'reqr_email_body';
        $option_value = get_option( $option_name, $public_class->get_default_email_body() );

        wp_editor( $option_value, $option_name, array( // Usar $option_name como ID del editor
            'textarea_name' => $option_name, // Asegurar que el nombre del textarea sea el correcto
            'media_buttons' => false,
            'textarea_rows' => 15,
            'quicktags' => true,
            'tinymce' => array(
                'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo,wp_help',
            ),
        ) );
        echo '<p class="description">' .
             wp_kses_post( __( 'Personaliza el contenido del correo. Puedes usar los siguientes placeholders: ', 'registro-evento-qr' ) .
             '<code>{nombre_usuario}</code>, <code>{email_usuario}</code>, <code>{empresa_usuario}</code>, <code>{puesto_usuario}</code>, <code>{telefono_usuario}</code>, <code>{qr_code_image_tag}</code> (para la imagen QR), <code>{qr_code_url}</code> (URL de validación), <code>{event_logo_tag}</code> (para el logo del evento), <code>{detalles_evento}</code>.' ) .
             '</p>';
    }

    /**
     * Helper para obtener instancia de la clase pública (para acceder a get_default_email_body).
     */
    private function get_public_class_instance() {
        // Esto es un poco hacky, idealmente las plantillas de email por defecto estarían en un lugar más central.
        // O la clase Public podría ser inyectada o accesible de otra forma.
        if (!class_exists('Registro_Evento_QR_Public')) {
            require_once REG_EVENTO_QR_PLUGIN_DIR . 'public/class-registro-evento-qr-public.php';
        }
        return new Registro_Evento_QR_Public($this->plugin_name, $this->version);
    }


    /**
     * Manejador AJAX para validar un código QR desde el panel de admin.
     * @since 1.0.0
     */
    public function ajax_validate_qr_code_admin() {
        check_ajax_referer( 'reqr_validate_qr_admin_nonce', '_ajax_nonce' );

        if ( ! current_user_can( 'edit_others_posts' ) ) { // Misma capacidad que la página de validación
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'registro-evento-qr' ) ) );
            return;
        }

        $qr_code_hash = isset( $_POST['qr_code_hash'] ) ? sanitize_text_field( $_POST['qr_code_hash'] ) : '';

        if ( empty( $qr_code_hash ) ) {
            wp_send_json_error( array( 'message' => __( 'El código QR no puede estar vacío.', 'registro-evento-qr' ) ) );
            return;
        }

        // Usar la lógica de validación de la clase Public (o duplicarla/refactorizarla si es necesario)
        // Para este ejemplo, vamos a replicar y adaptar la lógica aquí.
        global $wpdb;
        $table_name = $wpdb->prefix . 'registros_evento';
        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE qr_code_hash = %s", $qr_code_hash ) );

        if ( ! $registro ) {
            wp_send_json_success( array(
                'status' => 'error',
                'message' => __( 'Código QR no encontrado en la base de datos.', 'registro-evento-qr' )
            ) );
            return;
        }

        $message = '';
        $status_type = '';

        if ( $registro->confirmado ) {
            $status_type = 'warning';
            $message = __( 'Este registro ya fue validado anteriormente.', 'registro-evento-qr' );
        } else {
            $updated = $wpdb->update(
                $table_name,
                array( 'confirmado' => 1 ),
                array( 'id' => $registro->id ),
                array( '%d' ), // formato para 'confirmado'
                array( '%d' )  // formato para 'id'
            );

            if ( false === $updated ) {
                $status_type = 'error';
                $message = __( 'Error al actualizar el estado del registro en la base de datos.', 'registro-evento-qr' );
            } else {
                $status_type = 'success';
                $message = __( '¡Registro validado exitosamente!', 'registro-evento-qr' );
                $registro->confirmado = 1; // Actualizar el objeto para la respuesta
            }
        }

        // Preparamos los detalles para enviar en la respuesta JSON
        $details_for_response = array(
            'nombre' => $registro->nombre,
            'email' => $registro->email,
            'empresa' => $registro->empresa,
            'puesto' => $registro->puesto,
            'telefono' => $registro->telefono,
            'confirmado' => $registro->confirmado,
            'fecha_registro' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $registro->fecha_registro ) )
        );

        wp_send_json_success( array(
            'status' => $status_type,
            'message' => $message,
            'details' => $details_for_response
        ) );
    }

    /**
     * Manejador AJAX para reenviar el correo de confirmación.
     * @since 1.0.0
     */
    public function ajax_resend_confirmation_email() {
        check_ajax_referer( 'reqr_resend_email_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para esta acción.', 'registro-evento-qr' ) ) );
        }

        $registration_id = isset( $_POST['registration_id'] ) ? absint( $_POST['registration_id'] ) : 0;

        if ( ! $registration_id ) {
            wp_send_json_error( array( 'message' => __( 'ID de registro no válido.', 'registro-evento-qr' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'registros_evento';
        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $registration_id ) );

        if ( ! $registro ) {
            wp_send_json_error( array( 'message' => __( 'Registro no encontrado.', 'registro-evento-qr' ) ) );
        }

        // Necesitamos la URL de la imagen QR. Esta se genera y guarda durante el registro.
        // Asumimos que podemos reconstruirla o recuperarla si es necesario.
        // Para simplificar, asumimos que ya existe y la URL es conocida o se puede generar de nuevo.
        // La clase Public tiene la lógica para generar la URL de la imagen.
        $public_class = $this->get_public_class_instance();
        $qr_image_url = $public_class->generate_qr_code_image( $registro->qr_code_hash, $registro->id ); // Regenerar o buscar

        if (!$qr_image_url) {
             wp_send_json_error( array( 'message' => __( 'No se pudo generar/encontrar la imagen QR para el correo.', 'registro-evento-qr' ) ) );
        }

        $form_data = (array) $registro; // Convertir a array para la función de envío de correo
        $public_class->send_confirmation_email( $form_data, $registro->qr_code_hash, $qr_image_url );

        wp_send_json_success( array( 'message' => sprintf(__( 'Correo de confirmación reenviado a %s.', 'registro-evento-qr' ), $registro->email) ) );
    }


    /**
     * Manejador AJAX para eliminar un registro.
     * @since 1.0.0
     */
    public function ajax_delete_registration() {
        check_ajax_referer( 'reqr_delete_registration_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) { // Solo administradores pueden borrar
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para eliminar registros.', 'registro-evento-qr' ) ) );
        }

        $registration_id = isset( $_POST['registration_id'] ) ? absint( $_POST['registration_id'] ) : 0;

        if ( ! $registration_id ) {
            wp_send_json_error( array( 'message' => __( 'ID de registro no válido.', 'registro-evento-qr' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'registros_evento';

        // Opcional: eliminar el archivo QR asociado si existe
        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT qr_code_hash FROM $table_name WHERE id = %d", $registration_id ) );
        if ($registro) {
            $qr_filename_pattern = 'qr_registro_' . $registration_id . '_' . md5($registro->qr_code_hash) . '.png';
            $upload_dir = wp_upload_dir();
            $qr_filepath = $upload_dir['basedir'] . '/registro-evento-qr/' . $qr_filename_pattern;
            if (file_exists($qr_filepath)) {
                wp_delete_file($qr_filepath);
            }
        }

        $deleted = $wpdb->delete( $table_name, array( 'id' => $registration_id ), array( '%d' ) );

        if ( false === $deleted ) {
            wp_send_json_error( array( 'message' => __( 'Error al eliminar el registro de la base de datos.', 'registro-evento-qr' ) ) );
        } elseif ( 0 === $deleted ) {
            wp_send_json_error( array( 'message' => __( 'El registro no fue encontrado para eliminar o ya fue eliminado.', 'registro-evento-qr' ) ) );
        }


        wp_send_json_success( array( 'message' => __( 'Registro eliminado exitosamente.', 'registro-evento-qr' ) ) );
    }


}
