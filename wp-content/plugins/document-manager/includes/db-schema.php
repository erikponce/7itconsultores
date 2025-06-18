<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Returns the schema for the documents table.
 *
 * @return string SQL CREATE TABLE statement.
 */
function get_document_manager_schema() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'documents';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        uploaded_by BIGINT(20) UNSIGNED NOT NULL,
        uploaded_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        allowed_roles TEXT,
        allowed_users TEXT,
        PRIMARY KEY (id),
        KEY uploaded_by (uploaded_by)
    ) {$charset_collate};";

    return $sql;
}
