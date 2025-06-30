<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/includes
 * @author     Jules <jules@example.com>
 */
class Registro_Evento_QR {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Registro_Evento_QR_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'REG_EVENTO_QR_VERSION' ) ) {
			$this->version = REG_EVENTO_QR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'registro-evento-qr';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Registro_Evento_QR_Loader. Orchestrates the hooks of the plugin.
	 * - Registro_Evento_QR_i18n. Defines internationalization functionality.
	 * - Registro_Evento_QR_Admin. Defines all hooks for the admin area.
	 * - Registro_Evento_QR_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once REG_EVENTO_QR_PLUGIN_DIR . 'includes/class-registro-evento-qr-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once REG_EVENTO_QR_PLUGIN_DIR . 'includes/class-registro-evento-qr-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once REG_EVENTO_QR_PLUGIN_DIR . 'admin/class-registro-evento-qr-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once REG_EVENTO_QR_PLUGIN_DIR . 'public/class-registro-evento-qr-public.php';

		$this->loader = new Registro_Evento_QR_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Registro_Evento_QR_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Registro_Evento_QR_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Registro_Evento_QR_Admin( $this->get_plugin_name(), $this->get_version() );

		// Hooks para el área de administración
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // Hook para manejar la subida de logo con wp.media
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_media_uploader' );

        // Hooks para acciones AJAX (ej. validación de QR en admin, reenviar email, etc.)
        // La validación de QR en la página de admin se hará con AJAX para mejorar la UX.
        $this->loader->add_action( 'wp_ajax_reqr_validate_qr_code_admin', $plugin_admin, 'ajax_validate_qr_code_admin' );
        $this->loader->add_action( 'wp_ajax_reqr_resend_confirmation_email', $plugin_admin, 'ajax_resend_confirmation_email' );
        // Podríamos añadir más acciones AJAX aquí, como eliminar un registro.
        $this->loader->add_action( 'wp_ajax_reqr_delete_registration', $plugin_admin, 'ajax_delete_registration' );


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Registro_Evento_QR_Public( $this->get_plugin_name(), $this->get_version() );

		// Aquí registraremos los hooks para el área pública
        $this->loader->add_shortcode( 'formulario_registro_evento', $plugin_public, 'render_registration_form' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Acción para procesar el formulario ANTES de que se carguen las cabeceras (para posibles redirecciones)
        $this->loader->add_action( 'template_redirect', $plugin_public, 'process_registration_form_early' );

        // Hook para la página de validación de QR (plantilla pública)
        $this->loader->add_action( 'template_redirect', $plugin_public, 'handle_qr_validation_page_public' );

        // Hook para la acción AJAX de validación desde la página pública
        $this->loader->add_action( 'wp_ajax_reqr_validate_qr_code_public', $plugin_public, 'ajax_validate_qr_code_public' );
        $this->loader->add_action( 'wp_ajax_nopriv_reqr_validate_qr_code_public', $plugin_public, 'ajax_validate_qr_code_public' ); // Para usuarios no logueados, si se permite

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Registro_Evento_QR_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
