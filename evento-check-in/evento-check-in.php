<?php
/**
 * Plugin Name: Evento Check-in
 * Plugin URI: https://github.com/7itconsultores/evento-check-in
 * Description: A plugin to manage event registrations and check-ins using QR codes.
 * Version: 1.1.0
 * Author: 7itconsultores
 * Author URI: https://7itconsultores.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: evento-check-in
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'EVENTO_CHECKIN_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-evento-checkin-activator.php
 */
function activate_evento_checkin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-evento-checkin-activator.php';
    Evento_Checkin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-evento-checkin-deactivator.php
 */
function deactivate_evento_checkin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-evento-checkin-deactivator.php';
    Evento_Checkin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_evento_checkin' );
register_deactivation_hook( __FILE__, 'deactivate_evento_checkin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-evento-checkin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_evento_checkin() {

    $plugin = new Evento_Checkin();
    $plugin->run();

}
run_evento_checkin();
?>
