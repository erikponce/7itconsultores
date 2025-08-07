<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://7itconsultores.com/
 * @since      1.0.0
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/public
 * @author     7itconsultores <contacto@7itconsultores.com>
 */
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

class Evento_Checkin_Public {

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
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Evento_Checkin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Evento_Checkin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/evento-checkin-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Evento_Checkin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Evento_Checkin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/evento-checkin-public.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Render the registration form.
     *
     * @since    1.0.0
     */
    public function render_registration_form() {
        ob_start();

        if ( isset( $_GET['registration'] ) && $_GET['registration'] === 'success' ) {
            echo '<p class="evento-checkin-success-message">' . __( 'Thank you for registering!', 'evento-check-in' ) . '</p>';
        } else {
            include_once 'partials/evento-checkin-public-display.php';
        }

        if ( isset( $_GET['registration_error'] ) ) {
            echo '<p class="evento-checkin-error-message">' . __( 'There was an error with your registration. Please try again.', 'evento-check-in' ) . '</p>';
        }

        return ob_get_clean();
    }

    /**
     * Handle the registration form submission.
     *
     * @since    1.0.0
     */
    public function handle_form_submission() {
        if ( ! isset( $_POST['evento_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['evento_checkin_nonce'] ) || ! wp_verify_nonce( $_POST['evento_checkin_nonce'], 'evento_checkin_registration_form' ) ) {
            wp_die( __( 'Security check failed.', 'evento-check-in' ) );
        }

        $name = sanitize_text_field( $_POST['evento_name'] );
        $email = sanitize_email( $_POST['evento_email'] );

        if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
            $this->redirect_with_error();
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'evento_attendees';

        $qr_code = wp_generate_uuid4();

        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'qr_code' => $qr_code,
                'registration_date' => current_time( 'mysql' ),
            )
        );

        if ( $result === false ) {
            $this->redirect_with_error();
            return;
        }

        $attendee_id = $wpdb->insert_id;

        $this->send_confirmation_email( $attendee_id, $name, $email, $qr_code );

        $redirect_url = remove_query_arg( 'registration_error', wp_get_referer() );
        $redirect_url = add_query_arg( 'registration', 'success', $redirect_url );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Send the confirmation email.
     *
     * @since    1.0.0
     */
    private function send_confirmation_email( $attendee_id, $name, $email, $qr_code_data ) {
        $options = get_option( $this->plugin_name . '-settings' );

        $subject = isset( $options['email_subject'] ) ? $options['email_subject'] : 'Your Event Registration';
        $body = isset( $options['email_body'] ) ? $options['email_body'] : 'Thank you for registering, [attendee_name]!';
        $event_title = isset( $options['event_title'] ) ? $options['event_title'] : 'Our Event';

        // Replace placeholders
        $body = str_replace( '[attendee_name]', $name, $body );
        $body = str_replace( '[event_title]', $event_title, $body );

        // Generate QR Code
        $upload_dir = wp_upload_dir();
        $qr_code_dir = $upload_dir['basedir'] . '/evento-checkin-qrcodes';
        if ( ! file_exists( $qr_code_dir ) ) {
            wp_mkdir_p( $qr_code_dir );
        }

        $qr_code_path = $qr_code_dir . '/' . $qr_code_data . '.png';

        try {
            $qr_options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L,
            ]);

            (new QRCode($qr_options))->render($qr_code_data, $qr_code_path);
        } catch (\Exception $e) {
            // Handle QR code generation error
            return;
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $attachments = array( $qr_code_path );

        wp_mail( $email, $subject, $body, $headers, $attachments );

        // Clean up the QR code file
        wp_delete_file( $qr_code_path );
    }

    /**
     * Redirect with error.
     *
     * @since    1.0.0
     */
    private function redirect_with_error() {
        $redirect_url = remove_query_arg( 'registration', wp_get_referer() );
        $redirect_url = add_query_arg( 'registration_error', 'true', $redirect_url );
        wp_safe_redirect( $redirect_url );
        exit;
    }

}
