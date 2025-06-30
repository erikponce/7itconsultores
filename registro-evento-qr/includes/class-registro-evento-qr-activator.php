<?php
/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/includes
 * @author     Jules <jules@example.com>
 */
class Registro_Evento_QR_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'registros_evento';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			nombre tinytext NOT NULL,
			email varchar(100) NOT NULL,
			empresa tinytext NOT NULL,
			puesto tinytext NULL,
			telefono varchar(20) NULL,
			qr_code_hash varchar(255) NOT NULL,
			fecha_registro datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			confirmado boolean DEFAULT 0 NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY qr_code_hash (qr_code_hash)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

        // Guardar la versión del plugin para futuras actualizaciones de BD
        add_option( 'registro_evento_qr_db_version', REG_EVENTO_QR_VERSION );
	}

}
