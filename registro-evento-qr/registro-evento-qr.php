<?php
/**
 * Plugin Name: Registro de Evento con QR
 * Plugin URI: https://example.com/
 * Description: Permite el registro de personas para un evento, genera un código QR de confirmación y lo envía por e-mail. Incluye panel de administración para personalizar y validar registros.
 * Version: 1.0.0
 * Author: Jules
 * Author URI: https://example.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: registro-evento-qr
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define constants
 */
define( 'REG_EVENTO_QR_VERSION', '1.0.0' );
define( 'REG_EVENTO_QR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'REG_EVENTO_QR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'REG_EVENTO_QR_PLUGIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 */
function activate_registro_evento_qr() {
	require_once REG_EVENTO_QR_PLUGIN_DIR . 'includes/class-registro-evento-qr-activator.php';
	Registro_Evento_QR_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_registro_evento_qr() {
	require_once REG_EVENTO_QR_PLUGIN_DIR . 'includes/class-registro-evento-qr-deactivator.php';
	Registro_Evento_QR_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_registro_evento_qr' );
register_deactivation_hook( __FILE__, 'deactivate_registro_evento_qr' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require REG_EVENTO_QR_PLUGIN_DIR . 'includes/class-registro-evento-qr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_registro_evento_qr() {

	$plugin = new Registro_Evento_QR();
	$plugin->run();

}
run_registro_evento_qr();
