<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/public
 * @author     Jules <jules@example.com>
 */
class Registro_Evento_QR_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Registro_Evento_QR_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Registro_Evento_QR_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, REG_EVENTO_QR_PLUGIN_URL . 'public/css/registro-evento-qr-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Registro_Evento_QR_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Registro_Evento_QR_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, REG_EVENTO_QR_PLUGIN_URL . 'public/js/registro-evento-qr-public.js', array( 'jquery' ), $this->version, true ); // Changed to true for footer

        // Para la página de validación si se hace pública y necesita el escaner JS
        // Comprobaremos si estamos en la página que usa la plantilla 'template-qr-validation-page.php'
        // o si hay un shortcode específico de validación en la página.
        global $post;
        if ( (is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'vista_validacion_qr_evento' )) || is_page_template('public/partials/template-qr-validation-page.php') ) {
            // Asumimos que la librería html5-qrcode.min.js está en public/js/libs/
             if (file_exists(REG_EVENTO_QR_PLUGIN_DIR . 'public/js/libs/html5-qrcode.min.js')) {
                wp_enqueue_script( $this->plugin_name . '-qr-scanner-lib', REG_EVENTO_QR_PLUGIN_URL . 'public/js/libs/html5-qrcode.min.js', array(), $this->version, true );
             } else {
                // Podríamos mostrar un error en el admin si el archivo no existe, o intentar cargarlo desde un CDN como fallback.
                // Por ahora, solo lo omitimos si no está.
             }
            // Localize script para AJAX URL y nonce para la validación pública
            wp_localize_script( $this->plugin_name, 'reqr_ajax_object', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'reqr_validate_qr_ajax_public_nonce' )
            ));
        }
	}

    /**
     * Render the registration form using a shortcode.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the form.
     */
    public function render_registration_form( $atts ) {
        ob_start();
        // No llamamos a process_registration_form aquí, se llama en 'template_redirect'

        // Comprobar si hay un estado de éxito o error de la variable de sesión/transient
        if ( isset( $_SESSION['reqr_form_status'] ) ) {
            $status = $_SESSION['reqr_form_status'];
            if ( $status['type'] === 'success' ) {
                $GLOBALS['reqr_registration_success'] = $status['message'];
                if(isset($status['qr_image_url'])) {
                    $GLOBALS['reqr_qr_image_url'] = $status['qr_image_url'];
                }
                 // Incluir la vista de confirmación en lugar del formulario
                require_once REG_EVENTO_QR_PLUGIN_DIR . 'public/partials/registro-evento-qr-public-display-confirmation.php';
                unset( $_SESSION['reqr_form_status'] ); // Limpiar estado
                return ob_get_clean();
            } elseif ( $status['type'] === 'error' ) {
                $GLOBALS['reqr_registration_errors'] = $status['messages'];
                 // También pasar los datos enviados previamente para repoblar el formulario
                if(isset($status['posted_data'])) {
                    $_POST = array_merge($_POST, $status['posted_data']);
                }
            }
            unset( $_SESSION['reqr_form_status'] ); // Limpiar estado
        }

        require_once REG_EVENTO_QR_PLUGIN_DIR . 'public/partials/registro-evento-qr-public-display-form.php';
        return ob_get_clean();
    }

    /**
     * Process the registration form submission early on 'template_redirect'.
     * @since 1.0.0
     */
    public function process_registration_form_early() {
        // Iniciar sesión si no está iniciada, para los mensajes flash
        if ( ! session_id() ) {
            session_start();
        }

        if ( ! isset( $_POST['reqr_submit_registration'] ) || ! isset( $_POST['reqr_nonce_field'] ) || ! wp_verify_nonce( $_POST['reqr_nonce_field'], 'reqr_registration_action' ) ) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'registros_evento';
        $errors = array();
        $form_data = array(); // Para repoblar el formulario

        $form_data['nombre'] = isset( $_POST['reqr_nombre'] ) ? sanitize_text_field( $_POST['reqr_nombre'] ) : '';
        $form_data['email'] = isset( $_POST['reqr_email'] ) ? sanitize_email( $_POST['reqr_email'] ) : '';
        $form_data['empresa'] = isset( $_POST['reqr_empresa'] ) ? sanitize_text_field( $_POST['reqr_empresa'] ) : '';
        $form_data['puesto'] = isset( $_POST['reqr_puesto'] ) ? sanitize_text_field( $_POST['reqr_puesto'] ) : '';
        $form_data['telefono'] = isset( $_POST['reqr_telefono'] ) ? sanitize_text_field( $_POST['reqr_telefono'] ) : '';

        // Validaciones básicas
        if ( empty( $form_data['nombre'] ) ) $errors[] = __( 'El nombre es obligatorio.', 'registro-evento-qr' );
        if ( empty( $form_data['email'] ) || ! is_email( $form_data['email'] ) ) $errors[] = __( 'El correo electrónico no es válido o está vacío.', 'registro-evento-qr' );
        if ( empty( $form_data['empresa'] ) ) $errors[] = __( 'La empresa es obligatoria.', 'registro-evento-qr' );
        // Puesto y teléfono son opcionales por defecto

        // Verificar si el email ya está registrado (opcional, pero buena práctica)
        $existing_email = $wpdb->get_var( $wpdb->prepare( "SELECT email FROM $table_name WHERE email = %s", $form_data['email'] ) );
        if ( $existing_email ) {
            $errors[] = __( 'Este correo electrónico ya ha sido registrado.', 'registro-evento-qr' );
        }


        if ( ! empty( $errors ) ) {
            $_SESSION['reqr_form_status'] = array( 'type' => 'error', 'messages' => $errors, 'posted_data' => $form_data );
            // No redirigir, los errores se mostrarán en el mismo shortcode
            return;
        }

        // Generar hash único para el QR
        // Usaremos una combinación de email y timestamp para asegurar unicidad, luego un hash.
        // Es importante que sea difícil de adivinar.
        $qr_code_content = $form_data['email'] . time() . wp_generate_password(12, false);
        $qr_code_hash = hash('sha256', $qr_code_content);

        // Insertar en la base de datos
        $insert_result = $wpdb->insert(
            $table_name,
            array(
                'nombre' => $form_data['nombre'],
                'email' => $form_data['email'],
                'empresa' => $form_data['empresa'],
                'puesto' => $form_data['puesto'],
                'telefono' => $form_data['telefono'],
                'qr_code_hash' => $qr_code_hash,
                'fecha_registro' => current_time( 'mysql', 1 ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( false === $insert_result ) {
            $errors[] = __( 'Hubo un error al guardar tu registro. Por favor, inténtalo de nuevo.', 'registro-evento-qr' );
            $_SESSION['reqr_form_status'] = array( 'type' => 'error', 'messages' => $errors, 'posted_data' => $form_data );
            return;
        }

        $registration_id = $wpdb->insert_id;

        // Generar el código QR
        $qr_image_url = $this->generate_qr_code_image( $qr_code_hash, $registration_id );

        if ( ! $qr_image_url ) {
             $errors[] = __( 'Hubo un error al generar tu código QR. Por favor, contacta a los organizadores.', 'registro-evento-qr' );
             // Considerar eliminar el registro si el QR no se puede generar o marcarlo para revisión.
             // $wpdb->delete( $table_name, array( 'id' => $registration_id ) );
             $_SESSION['reqr_form_status'] = array( 'type' => 'error', 'messages' => $errors, 'posted_data' => $form_data );
             return;
        }

        // Enviar correo electrónico de confirmación
        $this->send_confirmation_email( $form_data, $qr_code_hash, $qr_image_url );

        // Establecer mensaje de éxito y datos para la página de confirmación
        $_SESSION['reqr_form_status'] = array(
            'type' => 'success',
            'message' => __( '¡Registro exitoso! Revisa tu correo para ver tu código QR. También puedes verlo a continuación.', 'registro-evento-qr' ),
            'qr_image_url' => $qr_image_url, // Para mostrarlo en la página de confirmación
            'registration_details' => (object) $form_data // Para mostrar resumen si se desea
        );

        // Redirigir a la misma página para mostrar el mensaje de éxito y evitar reenvío del formulario
        // Es importante que la URL de redirección no contenga parámetros que puedan ser eliminados por algunos sistemas de cache.
        // Usar add_query_arg para mantener los parámetros existentes si los hay.
        wp_redirect( esc_url_raw(add_query_arg(array()))); // Redirige a la URL actual limpia
        exit;
    }

    /**
     * Genera la imagen del código QR y la guarda.
     * @param string $qr_code_hash El hash que contendrá el QR.
     * @param int $registration_id El ID del registro.
     * @return string|false URL de la imagen QR o false en error.
     */
    private function generate_qr_code_image( $qr_code_hash, $registration_id ) {
        if ( ! class_exists( 'QRcode' ) ) {
            // Intentar incluir la librería si no está disponible (ej. desde una carpeta 'libs' en el plugin)
            $lib_path = REG_EVENTO_QR_PLUGIN_DIR . 'includes/libs/phpqrcode/qrlib.php';
            if ( file_exists( $lib_path ) ) {
                require_once $lib_path;
            } else {
                // Log error o notificar al admin
                error_log("Librería PHPQRCode no encontrada en: " . $lib_path);
                return false;
            }
        }

        $upload_dir = wp_upload_dir();
        $qr_dir = $upload_dir['basedir'] . '/registro-evento-qr/';
        $qr_url_base = $upload_dir['baseurl'] . '/registro-evento-qr/';

        // Crear el directorio si no existe
        if ( ! file_exists( $qr_dir ) ) {
            wp_mkdir_p( $qr_dir );
        }

        // Crear un .htaccess para proteger el directorio si es necesario (ej. denegar listado)
        if ( ! file_exists( $qr_dir . '.htaccess' ) ) {
            $htaccess_content = "Options -Indexes";
            file_put_contents( $qr_dir . '.htaccess', $htaccess_content );
        }
        // Crear un index.html vacío para mayor seguridad
        if ( ! file_exists( $qr_dir . 'index.html' ) ) {
            file_put_contents( $qr_dir . 'index.html', '' );
        }


        $filename = 'qr_registro_' . $registration_id . '_' . md5($qr_code_hash) . '.png'; // Nombre de archivo único
        $filepath = $qr_dir . $filename;
        $fileurl = $qr_url_base . $filename;

        // Contenido del QR: URL de validación con el hash
        // La página de validación de los organizadores usará este hash.
        // El formato de la URL puede ser: home_url('/validar-registro-evento/?qr_code=' . $qr_code_hash)
        // Necesitaremos crear una página en WordPress con el slug 'validar-registro-evento' y la plantilla de validación.
        $validation_page_slug = 'validar-registro'; // Este slug debe coincidir con la página de validación
        $qr_content_url = trailingslashit( home_url( $validation_page_slug ) ) . '?qr_code=' . urlencode( $qr_code_hash );


        try {
            QRcode::png( $qr_content_url, $filepath, QR_ECLEVEL_L, 10, 2 ); // Tamaño 10, Margen 2
        } catch (Exception $e) {
            error_log("Error generando QR: " . $e->getMessage());
            return false;
        }

        if ( file_exists( $filepath ) ) {
            return $fileurl;
        }

        return false;
    }

    /**
     * Envía el correo electrónico de confirmación al usuario.
     * @param array $form_data Datos del formulario.
     * @param string $qr_code_hash Hash del QR.
     * @param string $qr_image_url URL de la imagen QR.
     */
    private function send_confirmation_email( $form_data, $qr_code_hash, $qr_image_url ) {
        $to = $form_data['email'];
        $subject = __( 'Confirmación de Registro para el Evento', 'registro-evento-qr' );

        // Obtener logo y cuerpo del correo desde las opciones del plugin
        $event_logo_url = get_option( 'reqr_event_logo_url', '' );
        $email_body_template = get_option( 'reqr_email_body', $this->get_default_email_body() );

        // Placeholders
        $placeholders = array(
            '{nombre_usuario}' => $form_data['nombre'],
            '{email_usuario}' => $form_data['email'],
            '{empresa_usuario}' => $form_data['empresa'],
            '{puesto_usuario}' => $form_data['puesto'] ? $form_data['puesto'] : __( 'N/A', 'registro-evento-qr'),
            '{telefono_usuario}' => $form_data['telefono'] ? $form_data['telefono'] : __( 'N/A', 'registro-evento-qr'),
            '{qr_code_image_tag}' => '<img src="' . esc_url( $qr_image_url ) . '" alt="' . __( 'Tu Código QR', 'registro-evento-qr' ) . '" style="max-width:250px; height:auto;"/>',
            '{qr_code_url}' => trailingslashit( home_url( 'validar-registro' ) ) . '?qr_code=' . urlencode( $qr_code_hash ), // URL para validación
            '{event_logo_tag}' => $event_logo_url ? '<img src="' . esc_url( $event_logo_url ) . '" alt="' . __( 'Logo del Evento', 'registro-evento-qr' ) . '" style="max-width:200px; height:auto;"/>' : '',
            // Podríamos añadir más detalles del evento aquí si se configuran en el admin
            '{detalles_evento}' => __( '¡Esperamos verte pronto!', 'registro-evento-qr' )
        );

        $email_body = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_body_template );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $from_name = get_bloginfo('name');
        $from_email = get_bloginfo('admin_email'); // O un email específico del evento
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

        // Para adjuntar la imagen QR en lugar de solo enlazarla (más complejo, requiere manejo de adjuntos)
        // $attachments = array( str_replace( content_url(), WP_CONTENT_DIR, $qr_image_url ) );
        // wp_mail( $to, $subject, wpautop( $email_body ), $headers, $attachments );

        wp_mail( $to, $subject, wpautop( $email_body ), $headers ); // wpautop para formatear como HTML
    }

    /**
     * Devuelve el cuerpo del correo electrónico por defecto.
     * @return string
     */
    private function get_default_email_body() {
        $body = "<h1>" . __( '¡Gracias por registrarte!', 'registro-evento-qr' ) . "</h1>";
        $body .= "<p>" . __( 'Hola {nombre_usuario},', 'registro-evento-qr' ) . "</p>";
        $body .= "<p>" . __( 'Tu registro para nuestro evento ha sido confirmado. Aquí tienes tu código QR personal. Por favor, preséntalo al ingresar:', 'registro-evento-qr' ) . "</p>";
        $body .= "<p style=\"text-align:center;\">{qr_code_image_tag}</p>";
        $body .= "<p>" . __( 'Si el código QR no se muestra correctamente, puedes acceder a él también a través de este enlace (solo para referencia, no es necesario hacer clic): {qr_code_url}', 'registro-evento-qr' ) . "</p>";
        $body .= "<h3>" . __( 'Detalles de tu registro:', 'registro-evento-qr' ) . "</h3>";
        $body .= "<ul>";
        $body .= "<li><strong>" . __( 'Nombre:', 'registro-evento-qr' ) . "</strong> {nombre_usuario}</li>";
        $body .= "<li><strong>" . __( 'Email:', 'registro-evento-qr' ) . "</strong> {email_usuario}</li>";
        $body .= "<li><strong>" . __( 'Empresa:', 'registro-evento-qr' ) . "</strong> {empresa_usuario}</li>";
        $body .= "<li><strong>" . __( 'Puesto:', 'registro-evento-qr' ) . "</strong> {puesto_usuario}</li>";
        $body .= "<li><strong>" . __( 'Teléfono:', 'registro-evento-qr' ) . "</strong> {telefono_usuario}</li>";
        $body .= "</ul>";
        $body .= "<p>{detalles_evento}</p>";
        $body .= "<p>" . __( 'Saludos,', 'registro-evento-qr' ) . "<br>" . get_bloginfo('name') . "</p>";
        $body .= "<div style='text-align:center; margin-top:20px;'>{event_logo_tag}</div>";
        return $body;
    }


    /**
     * Handles the QR validation page.
     * This could be a specific page template or a custom endpoint.
     * For simplicity, we might create a page template `template-qr-validation.php`
     * and this function would be hooked to `template_redirect` to handle logic if that template is active.
     *
     * @since 1.0.0
     */
    public function handle_qr_validation_page() {
        // if ( is_page_template( 'template-qr-validation.php' ) ) {
            // // Verificar permisos de usuario (solo organizadores)
            // if ( ! current_user_can( 'edit_posts' ) ) { // Ajustar capacidad según se defina
            //     wp_redirect( home_url() );
            //     exit;
            // }

            // // Lógica de validación aquí (cuando se envía un código por POST o GET)
            // $validation_result = null;
            // $qr_code_hash = null;

            // if ( isset( $_POST['reqr_qr_code_hash_manual'] ) && isset( $_POST['reqr_validate_nonce'] ) && wp_verify_nonce( $_POST['reqr_validate_nonce'], 'reqr_validate_action' ) ) {
            //     $qr_code_hash = sanitize_text_field( $_POST['reqr_qr_code_hash_manual'] );
            // } elseif ( isset( $_GET['qr_code'] ) ) { // Para validación directa desde URL del QR
            //     $qr_code_hash = sanitize_text_field( $_GET['qr_code'] );
            // }

            // if ( $qr_code_hash ) {
            //     // Buscar en BD y actualizar estado
            //     // $validation_result = $this->validate_qr_in_db( $qr_code_hash );
            //     // $GLOBALS['reqr_validation_result'] = $validation_result; // Para mostrar en la plantilla
            // }

            // // La plantilla 'template-qr-validation.php' se encargará de mostrar el formulario y los resultados.
        // }
    }

    /**
     * Validates QR code in database (dummy implementation).
     * @param string $qr_hash
     * @return array result (status: 'success', 'warning', 'error', message: string, data: object|null)
     */
    private function validate_qr_in_db( $qr_code_hash ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'registros_evento';
        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE qr_code_hash = %s", $qr_code_hash ) );

        if ( ! $registro ) {
            return array(
                'status' => 'error',
                'message' => __( 'Código QR no encontrado en la base de datos.', 'registro-evento-qr' ),
                'data' => null
            );
        }

        if ( $registro->confirmado ) {
            return array(
                'status' => 'warning',
                'message' => __( 'Este registro ya fue validado anteriormente.', 'registro-evento-qr' ),
                'data' => $registro
            );
        }

        // Marcar como confirmado
        $updated = $wpdb->update(
            $table_name,
            array( 'confirmado' => 1 ),
            array( 'id' => $registro->id ),
            array( '%d' ), // formato para 'confirmado'
            array( '%d' )  // formato para 'id'
        );

        if ( false === $updated ) {
            return array(
                'status' => 'error',
                'message' => __( 'Error al actualizar el estado del registro en la base de datos.', 'registro-evento-qr' ),
                'data' => $registro // Devolver los datos originales
            );
        }

        $registro->confirmado = 1; // Actualizar el objeto para la respuesta
        return array(
            'status' => 'success',
            'message' => __( '¡Registro validado exitosamente!', 'registro-evento-qr' ),
            'data' => $registro
        );
    }


    /**
     * Maneja la lógica de la página de validación de QR pública.
     * Se activa en `template_redirect`.
     * @since 1.0.0
     */
    public function handle_qr_validation_page_public() {
        // Comprobar si estamos en la página que usa la plantilla 'template-qr-validation-page.php'
        // O si la página tiene un slug específico como 'validar-registro' (que es lo que usa el QR)
        if ( is_page_template('public/partials/template-qr-validation-page.php') || is_page('validar-registro') ) {

            // 1. Verificar permisos de usuario (solo organizadores)
            // Esta es una capacidad de ejemplo, podría ser un rol personalizado.
            // 'edit_others_posts' es una capacidad que suelen tener Editores y Administradores.
            if ( ! current_user_can( 'edit_others_posts' ) ) {
                // Si el usuario no está logueado o no tiene permisos, redirigir al login o a la home.
                // Podríamos mostrar un mensaje de "Acceso denegado" en la propia plantilla también.
                $login_url = wp_login_url( get_permalink() );
                wp_redirect( $login_url );
                exit;
            }

            // 2. Lógica de validación si se envía un código (GET desde QR, o POST desde formulario manual)
            // Esta parte se manejará principalmente por AJAX para una mejor UX.
            // Sin embargo, si se accede directamente con ?qr_code=HASH, podemos intentar validarlo.

            $qr_code_to_validate = null;
            if ( isset( $_GET['qr_code'] ) && !empty($_GET['qr_code']) ) {
                $qr_code_to_validate = sanitize_text_field( $_GET['qr_code'] );
            }
            // El POST del formulario manual será manejado por AJAX directamente en `ajax_validate_qr_code_public`.

            if ( $qr_code_to_validate ) {
                $validation_result = $this->validate_qr_in_db( $qr_code_to_validate );
                // Pasar el resultado a la plantilla para que lo muestre inicialmente.
                // Usamos una variable global o una propiedad de la clase que la plantilla pueda acceder.
                $GLOBALS['reqr_initial_validation_result'] = $validation_result;
            }

            // La plantilla 'template-qr-validation-page.php' se encargará de mostrar el formulario y los resultados.
            // El JS encolado (registro-evento-qr-public.js y html5-qrcode.min.js) se encargará del escaneo y AJAX.
        }
    }

    /**
     * Manejador AJAX para validar un código QR desde la página pública de validación.
     * @since 1.0.0
     */
    public function ajax_validate_qr_code_public() {
        check_ajax_referer( 'reqr_validate_qr_ajax_public_nonce', '_ajax_nonce' );

        // Verificar permisos de nuevo por seguridad en el endpoint AJAX.
        if ( ! current_user_can( 'edit_others_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'registro-evento-qr' ) ) );
            return;
        }

        $qr_code_hash = isset( $_POST['qr_code_hash'] ) ? sanitize_text_field( $_POST['qr_code_hash'] ) : '';

        if ( empty( $qr_code_hash ) ) {
            wp_send_json_error( array( 'message' => __( 'El código QR no puede estar vacío.', 'registro-evento-qr' ) ) );
            return;
        }

        $result = $this->validate_qr_in_db( $qr_code_hash );

        // Preparar los datos del registro para la respuesta JSON, si existen.
        $details_for_response = null;
        if ( isset($result['data']) && is_object($result['data']) ) {
            $details_for_response = array(
                'nombre' => $result['data']->nombre,
                'email' => $result['data']->email, // Considerar si mostrar email en público
                'empresa' => $result['data']->empresa,
                'puesto' => $result['data']->puesto,
                // 'telefono' => $result['data']->telefono, // No mostrar teléfono en público por privacidad
                'confirmado' => $result['data']->confirmado,
                'fecha_registro' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $result['data']->fecha_registro ) )
            );
        }

        if ( $result['status'] === 'error' && $result['data'] === null) { // Código no encontrado
             wp_send_json_error( array(
                'status' => $result['status'], // 'error'
                'message' => $result['message']
            ) );
        } else { // Éxito o advertencia (ya validado), o error actualizando
            wp_send_json_success( array(
                'status' => $result['status'],
                'message' => $result['message'],
                'details' => $details_for_response
            ) );
        }
    }

}
