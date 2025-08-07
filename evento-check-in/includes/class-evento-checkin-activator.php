<?php

/**
 * Fired during plugin activation.
 *
 * @link       https://7itconsultores.com/
 * @since      1.0.0
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/includes
 * @author     7itconsultores <contacto@7itconsultores.com>
 */
class Evento_Checkin_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'evento_attendees';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email text NOT NULL,
            qr_code text NOT NULL,
            registration_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            check_in_status tinyint(1) NOT NULL DEFAULT 0,
            check_in_date datetime,
            custom_fields text,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

}
