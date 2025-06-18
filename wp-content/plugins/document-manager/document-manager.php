<?php
/**
 * Plugin Name:       Document Manager
 * Plugin URI:        https://example.com/document-manager
 * Description:       A plugin to manage document uploads with role and user-based permissions, and frontend views.
 * Version:           1.0.0
 * Author:            AI Developer
 * Author URI:        https://example.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       document-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants
if ( ! defined( 'DOCUMENT_MANAGER_UPLOAD_DIR' ) ) {
    define( 'DOCUMENT_MANAGER_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/document-manager/' );
}
if ( ! defined( 'DOCUMENT_MANAGER_UPLOAD_URL' ) ) {
    define( 'DOCUMENT_MANAGER_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/document-manager/' );
}

// Include the database schema.
require_once plugin_dir_path( __FILE__ ) . 'includes/db-schema.php';

/**
 * The code that runs during plugin activation.
 */
function document_manager_activate() {
    global $wpdb;

    // Create documents table
    $table_name = $wpdb->prefix . 'documents';
    $sql = get_document_manager_schema();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    // Create upload directory and security files
    if ( ! file_exists( DOCUMENT_MANAGER_UPLOAD_DIR ) ) {
        wp_mkdir_p( DOCUMENT_MANAGER_UPLOAD_DIR );
    }
    // Add index.php to prevent directory listing
    if ( ! file_exists( DOCUMENT_MANAGER_UPLOAD_DIR . 'index.php' ) ) {
        @file_put_contents( DOCUMENT_MANAGER_UPLOAD_DIR . 'index.php', '<?php // Silence is golden.' );
    }
    // Add .htaccess to prevent direct execution/listing (for Apache)
    if ( ! file_exists( DOCUMENT_MANAGER_UPLOAD_DIR . '.htaccess' ) ) {
        $htaccess_content = "Options -Indexes\nDeny from all";
        @file_put_contents( DOCUMENT_MANAGER_UPLOAD_DIR . '.htaccess', $htaccess_content );
    }

    // Store the plugin version.
    add_option( 'document_manager_version', '1.0.0' );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-document-manager-deactivator.php
 */
function document_manager_deactivate() {
    // Placeholder for deactivation tasks.
    // For example, log deactivation: error_log('Document Manager deactivated.');
}

register_activation_hook( __FILE__, 'document_manager_activate' );
register_deactivation_hook( __FILE__, 'document_manager_deactivate' );

// Initialize admin features
if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin/admin-menu.php';
}
