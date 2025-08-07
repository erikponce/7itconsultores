<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://7itconsultores.com/
 * @since      1.0.0
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/admin
 * @author     7itconsultores <contacto@7itconsultores.com>
 */
class Evento_Checkin_Admin {

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
    /**
     * The options group name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $options_group    The options group name.
     */
    private $options_group;

    /**
     * The options name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $options_name    The options name.
     */
    private $options_name;


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
        $this->options_group = $this->plugin_name . '-settings-group';
        $this->options_name = $this->plugin_name . '-settings';

    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/evento-checkin-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook_suffix ) {

        // Only load scanner scripts on the scanner page.
        if ( 'evento-check-in_page_evento-check-in-scan-qr' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_script(
            'html5-qrcode',
            'https://unpkg.com/html5-qrcode/html5-qrcode.min.js',
            [],
            '2.3.4',
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-scanner',
            plugin_dir_url( __FILE__ ) . 'js/evento-checkin-admin-scanner.js',
            [ 'jquery', 'html5-qrcode' ],
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-scanner',
            'evento_checkin_scanner_ajax',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'evento_checkin_scanner_nonce' ),
            ]
        );
    }

    /**
     * Add the top-level admin menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Evento Check-in', 'evento-check-in' ),
            __( 'Evento Check-in', 'evento-check-in' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'render_settings_page' ),
            'dashicons-camera-alt'
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Settings', 'evento-check-in' ),
            __( 'Settings', 'evento-check-in' ),
            'manage_options',
            $this->plugin_name, // This makes it the default page
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Attendees', 'evento-check-in' ),
            __( 'Attendees', 'evento-check-in' ),
            'manage_options',
            $this->plugin_name . '-attendees',
            array( $this, 'render_attendees_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Scan QR', 'evento-check-in' ),
            __( 'Scan QR', 'evento-check-in' ),
            'manage_options',
            $this->plugin_name . '-scan-qr',
            array( $this, 'render_scanner_page' )
        );
    }

    /**
     * Render the settings page for the plugin.
     *
     * @since    1.0.0
     */
    public function render_settings_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/evento-checkin-admin-settings.php';
    }

    /**
     * Render the attendees page for the plugin.
     *
     * @since    1.0.0
     */
    public function render_attendees_page() {
        require_once plugin_dir_path( __FILE__ ) . 'class-evento-checkin-attendees-list-table.php';
        require_once plugin_dir_path( __FILE__ ) . 'partials/evento-checkin-admin-attendees.php';
    }

    /**
     * Render the scanner page for the plugin.
     *
     * @since    1.0.0
     */
    public function render_scanner_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/evento-checkin-admin-scanner.php';
    }

    /**
     * Register the settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            $this->options_group,
            $this->options_name,
            array( $this, 'sanitize_settings' )
        );

        // General Section
        add_settings_section(
            $this->plugin_name . '-general-section',
            __( 'General Settings', 'evento-check-in' ),
            array( $this, 'render_general_section_info' ),
            $this->plugin_name
        );

        add_settings_field(
            'event_title',
            __( 'Event Title', 'evento-check-in' ),
            array( $this, 'render_event_title_field' ),
            $this->plugin_name,
            $this->plugin_name . '-general-section'
        );

        // Email Section
        add_settings_section(
            $this->plugin_name . '-email-section',
            __( 'Confirmation Email Settings', 'evento-check-in' ),
            array( $this, 'render_email_section_info' ),
            $this->plugin_name
        );

        add_settings_field(
            'email_subject',
            __( 'Email Subject', 'evento-check-in' ),
            array( $this, 'render_email_subject_field' ),
            $this->plugin_name,
            $this->plugin_name . '-email-section'
        );

        add_settings_field(
            'email_body',
            __( 'Email Body', 'evento-check-in' ),
            array( $this, 'render_email_body_field' ),
            $this->plugin_name,
            $this->plugin_name . '-email-section'
        );
    }

    /**
     * Sanitize the settings.
     *
     * @since    1.0.0
     */
    public function sanitize_settings( $input ) {
        $new_input = array();
        if ( isset( $input['event_title'] ) ) {
            $new_input['event_title'] = sanitize_text_field( $input['event_title'] );
        }
        if ( isset( $input['email_subject'] ) ) {
            $new_input['email_subject'] = sanitize_text_field( $input['email_subject'] );
        }
        if ( isset( $input['email_body'] ) ) {
            $new_input['email_body'] = wp_kses_post( $input['email_body'] );
        }
        return $new_input;
    }

    /**
     * Render the general section info.
     *
     * @since    1.0.0
     */
    public function render_general_section_info() {
        echo '<p>' . __( 'General settings for the event.', 'evento-check-in' ) . '</p>';
    }

    /**
     * Render the email section info.
     *
     * @since    1.0.0
     */
    public function render_email_section_info() {
        echo '<p>' . __( 'Customize the confirmation email that attendees receive after registration.', 'evento-check-in' ) . '</p>';
    }

    /**
     * Render the event title field.
     *
     * @since    1.0.0
     */
    public function render_event_title_field() {
        $options = get_option( $this->options_name );
        $value = isset( $options['event_title'] ) ? $options['event_title'] : '';
        echo '<input type="text" id="event_title" name="' . $this->options_name . '[event_title]" value="' . esc_attr( $value ) . '" class="regular-text">';
    }

    /**
     * Render the email subject field.
     *
     * @since    1.0.0
     */
    public function render_email_subject_field() {
        $options = get_option( $this->options_name );
        $value = isset( $options['email_subject'] ) ? $options['email_subject'] : '';
        echo '<input type="text" id="email_subject" name="' . $this->options_name . '[email_subject]" value="' . esc_attr( $value ) . '" class="regular-text">';
    }

    /**
     * Render the email body field.
     *
     * @since    1.0.0
     */
    public function render_email_body_field() {
        $options = get_option( $this->options_name );
        $content = isset( $options['email_body'] ) ? $options['email_body'] : '';
        $editor_id = 'email_body';
        $settings = array(
            'textarea_name' => $this->options_name . '[email_body]',
            'media_buttons' => false,
            'textarea_rows' => 10,
        );
        wp_editor( $content, $editor_id, $settings );
    }

    /**
     * AJAX handler for checking in an attendee.
     *
     * @since    1.0.0
     */
    public function ajax_check_in_attendee() {
        check_ajax_referer( 'evento_checkin_scanner_nonce', 'nonce' );

        global $wpdb;
        $table_name = $wpdb->prefix . 'evento_attendees';

        $qr_code = sanitize_text_field( $_POST['qr_code'] );

        $attendee = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE qr_code = %s", $qr_code ),
            ARRAY_A
        );

        if ( ! $attendee ) {
            wp_send_json_error( [ 'message' => __( 'Invalid QR Code.', 'evento-check-in' ) ] );
        }

        if ( $attendee['check_in_status'] ) {
            wp_send_json_error( [
                'message' => sprintf(
                    __( '%s has already been checked in at %s.', 'evento-check-in' ),
                    $attendee['name'],
                    $attendee['check_in_date']
                ),
            ] );
        }

        $wpdb->update(
            $table_name,
            [
                'check_in_status' => 1,
                'check_in_date'   => current_time( 'mysql' ),
            ],
            [ 'id' => $attendee['id'] ]
        );

        wp_send_json_success( [
            'message' => sprintf(
                __( 'Success! %s has been checked in.', 'evento-check-in' ),
                $attendee['name']
            ),
        ] );

        wp_die();
    }
}
