<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Adds the Document Manager admin menu page.
 */
function document_manager_add_admin_menu() {
    add_menu_page(
        'Document Manager',                  // Page title
        'Doc Manager',                       // Menu title
        'manage_options',                    // Capability
        'document-manager',                  // Menu slug
        'document_manager_admin_page_display', // Callback function
        'dashicons-media-document',          // Icon URL
        25                                   // Position
    );
}
add_action( 'admin_menu', 'document_manager_add_admin_menu' );

/**
 * Displays the content for the Document Manager admin page.
 */
function document_manager_admin_page_display() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php

    settings_errors(); // Display admin notices

    // Check if an action is set (add or edit)
    $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
    $doc_id = isset( $_GET['doc_id'] ) ? absint( $_GET['doc_id'] ) : 0;

    if ( ( $action === 'add' || $action === 'edit' ) && current_user_can( 'manage_options' ) ) { // Added capability check
        if ( $action === 'edit' && ! $doc_id ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Error: Document ID not specified for editing.', 'document-manager' ) . '</p></div>';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=document-manager' ) ) . '">' . esc_html__( 'Back to Document List', 'document-manager' ) . '</a>';
        } else {
            document_manager_render_add_edit_form( $action === 'edit' ? $doc_id : null );
        }
    } else {
        // Display the Document List Table
        require_once plugin_dir_path( __FILE__ ) . 'class-document-list-table.php';
        $list_table = new Document_List_Table();
        $list_table->prepare_items();

        echo '<a href="' . esc_url( admin_url( 'admin.php?page=document-manager&action=add' ) ) . '" class="page-title-action">' . esc_html__( 'Add New Document', 'document-manager' ) . '</a>';
        echo '<p>' . esc_html__( 'Manage your documents below. Use the "Add New Document" button to upload new files.', 'document-manager' ) . '</p>';

        // Form for the list table (for bulk actions)
        echo '<form method="post">';
        // For bulk actions, security, and other features
        wp_nonce_field( 'dm_bulk_action_nonce', 'dm_nonce_field' ); // Consider a more specific nonce for bulk actions if needed
        $list_table->display();
        echo '</form>';
    }
    ?>
    </div>
    <?php
}

/**
 * Renders the Add/Edit Document form.
 *
 * @param int|null $document_id The ID of the document to edit, or null for a new document.
 */
function document_manager_render_add_edit_form( $document_id = null ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'documents';
    $is_editing = ! is_null( $document_id );

    $document_title_value = '';
    $current_file_name = '';
    $current_allowed_roles = [];
    $current_allowed_users_string = '';

    if ( $is_editing ) {
        $document = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $document_id ), ARRAY_A );
        if ( $document ) {
            $document_title_value = $document['title'];
            $current_file_name = $document['file_name'];
            $current_allowed_roles = !empty($document['allowed_roles']) ? maybe_unserialize($document['allowed_roles']) : [];
            if(!is_array($current_allowed_roles)) $current_allowed_roles = []; // Ensure it's an array

            $raw_allowed_users = !empty($document['allowed_users']) ? maybe_unserialize($document['allowed_users']) : [];
            if (is_array($raw_allowed_users)) {
                 $user_logins = [];
                 foreach($raw_allowed_users as $user_id_or_login) {
                    if (is_numeric($user_id_or_login)) {
                        $user_data = get_userdata($user_id_or_login);
                        if ($user_data) $user_logins[] = $user_data->user_login;
                    } else {
                        $user_logins[] = $user_id_or_login; // Assume it's already a login if not numeric
                    }
                 }
                 $current_allowed_users_string = implode(', ', $user_logins);
            }

        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Error: Document not found.', 'document-manager' ) . '</p></div>';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=document-manager' ) ) . '">' . esc_html__( 'Back to Document List', 'document-manager' ) . '</a>';
            return;
        }
    }
    ?>
    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); // Point to admin-post.php for processing ?>">
        <?php wp_nonce_field( 'document_manager_save_document', 'document_manager_nonce' ); ?>
        <input type="hidden" name="action" value="document_manager_save_document_action" />
        <input type="hidden" name="doc_id" value="<?php echo esc_attr( $document_id ); ?>" />
        <input type="hidden" name="action_type" value="<?php echo $is_editing ? 'edit_document' : 'add_document'; ?>" />

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="document_title"><?php esc_html_e( 'Document Title', 'document-manager' ); ?></label>
                </th>
                <td>
                    <input type="text" id="document_title" name="document_title" class="regular-text" value="<?php echo esc_attr( $document_title_value ); ?>" required />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="document_file"><?php esc_html_e( 'Document File', 'document-manager' ); ?></label>
                </th>
                <td>
                    <input type="file" id="document_file" name="document_file" <?php echo $is_editing ? '' : 'required'; ?> />
                    <?php if ( $is_editing && $current_file_name ) : ?>
                        <p class="description"><?php esc_html_e( 'Current file:', 'document-manager' ); ?> <?php echo esc_html( $current_file_name ); ?>. <?php esc_html_e('Uploading a new file will replace the existing one.', 'document-manager'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label><?php esc_html_e( 'Allowed Roles', 'document-manager' ); ?></label>
                </th>
                <td>
                    <?php
                    $editable_roles = get_editable_roles();
                    foreach ( $editable_roles as $role_slug => $role_details ) {
                        ?>
                        <label>
                            <input type="checkbox" name="allowed_roles[]" value="<?php echo esc_attr( $role_slug ); ?>" <?php checked( in_array( $role_slug, $current_allowed_roles ) ); ?> />
                            <?php echo esc_html( $role_details['name'] ); ?>
                        </label><br/>
                        <?php
                    }
                    ?>
                     <p class="description"><?php esc_html_e( 'If no roles are selected, the document will be accessible by any authenticated user by default (unless specific users are listed). Consider a "subscriber" role for general authenticated access.', 'document-manager' ); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="allowed_users"><?php esc_html_e( 'Allowed Users', 'document-manager' ); ?></label>
                </th>
                <td>
                    <textarea id="allowed_users" name="allowed_users" rows="3" class="regular-text" placeholder="<?php esc_attr_e( 'Enter comma-separated user IDs or usernames', 'document-manager' ); ?>"><?php echo esc_textarea( $current_allowed_users_string ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Grant access to specific users. Overrides role restrictions if a user is listed here. Enter usernames or user IDs.', 'document-manager' ); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button( $is_editing ? __( 'Update Document', 'document-manager' ) : __( 'Add Document', 'document-manager' ) ); ?>
    </form>
    <?php
}

add_action( 'admin_post_document_manager_save_document_action', 'document_manager_handle_save_document' );

/**
 * Handles the submission of the Add/Edit Document form.
 */
function document_manager_handle_save_document() {
    // Security Checks
    if ( ! isset( $_POST['document_manager_nonce'] ) || ! wp_verify_nonce( $_POST['document_manager_nonce'], 'document_manager_save_document' ) ) {
        wp_die( esc_html__( 'Nonce verification failed.', 'document-manager' ) );
    }
    if ( ! current_user_can( 'manage_options' ) ) { // Replace with a specific capability later
        wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'document-manager' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'documents';

    // Retrieve and Sanitize Data
    $doc_id = isset( $_POST['doc_id'] ) && !empty($_POST['doc_id']) ? intval( $_POST['doc_id'] ) : null;
    $action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
    $document_title = isset( $_POST['document_title'] ) ? sanitize_text_field( $_POST['document_title'] ) : '';
    $allowed_roles = isset( $_POST['allowed_roles'] ) && is_array( $_POST['allowed_roles'] ) ? array_map( 'sanitize_text_field', $_POST['allowed_roles'] ) : [];

    $raw_allowed_users = isset( $_POST['allowed_users'] ) ? sanitize_textarea_field( $_POST['allowed_users'] ) : '';
    $allowed_user_ids = [];
    if ( ! empty( $raw_allowed_users ) ) {
        $user_inputs = array_map( 'trim', explode( ',', $raw_allowed_users ) );
        foreach ( $user_inputs as $user_input ) {
            if ( is_numeric( $user_input ) ) {
                $user_data = get_user_by( 'ID', intval( $user_input ) );
                if ( $user_data ) {
                    $allowed_user_ids[] = $user_data->ID;
                }
            } else {
                $user_data = get_user_by( 'login', $user_input );
                if ( $user_data ) {
                    $allowed_user_ids[] = $user_data->ID;
                } else {
                     $user_data = get_user_by( 'email', $user_input ); // Also check by email
                     if ($user_data) $allowed_user_ids[] = $user_data->ID;
                }
            }
        }
        $allowed_user_ids = array_unique( $allowed_user_ids ); // Remove duplicates
    }


    // Validate Data
    if ( empty( $document_title ) ) {
        add_settings_error( 'document_manager_errors', 'title_empty', __( 'Document Title cannot be empty.', 'document-manager' ), 'error' );
        $redirect_url = $doc_id ?
            admin_url( 'admin.php?page=document-manager&action=edit&doc_id=' . $doc_id ) :
            admin_url( 'admin.php?page=document-manager&action=add' );
        wp_redirect( $redirect_url );
        exit;
    }

    $data_to_save = [
        'title'         => $document_title,
        'uploaded_by'   => get_current_user_id(),
        'allowed_roles' => maybe_serialize( $allowed_roles ),
        'allowed_users' => maybe_serialize( $allowed_user_ids ),
    ];
    $format = ['%s', '%d', '%s', '%s']; // Format for title, uploaded_by, allowed_roles, allowed_users

    // Handle File Upload
    if ( isset( $_FILES['document_file'] ) && $_FILES['document_file']['size'] > 0 ) {
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Ensure upload directory exists (it should from activation, but double check)
        if ( ! file_exists( DOCUMENT_MANAGER_UPLOAD_DIR ) ) {
            wp_mkdir_p( DOCUMENT_MANAGER_UPLOAD_DIR );
        }
         if ( ! file_exists( DOCUMENT_MANAGER_UPLOAD_DIR . 'index.php' ) ) {
            @file_put_contents( DOCUMENT_MANAGER_UPLOAD_DIR . 'index.php', '<?php // Silence is golden.' );
        }
        if ( ! file_exists( DOCUMENT_MANAGER_UPLOAD_DIR . '.htaccess' ) ) {
             $htaccess_content = "Options -Indexes\nDeny from all";
            @file_put_contents( DOCUMENT_MANAGER_UPLOAD_DIR . '.htaccess', $htaccess_content );
        }


        $upload_overrides = ['test_form' => false, 'mimes' => get_allowed_mime_types()]; // Use WordPress allowed mime types
        $uploaded_file_info = wp_handle_upload( $_FILES['document_file'], $upload_overrides, current_time( 'mysql' ) );

        // Append year/month to DOCUMENT_MANAGER_UPLOAD_DIR like WP default uploads
        // wp_handle_upload places the file in wp-content/uploads/YYYY/MM if 'uploads_use_yearmonth_folders' is true (default)
        // The $uploaded_file_info['file'] will contain the full path.
        // We need to make sure our DOCUMENT_MANAGER_UPLOAD_DIR is not strictly enforced if WP places it elsewhere.
        // For simplicity now, we assume wp_handle_upload will respect the default WP upload structure.
        // The path stored should be relative to WP uploads or absolute. $uploaded_file_info['file'] is absolute.

        if ( isset( $uploaded_file_info['error'] ) ) {
            add_settings_error( 'document_manager_errors', 'upload_error', $uploaded_file_info['error'], 'error' );
            $redirect_url = $doc_id ?
                admin_url( 'admin.php?page=document-manager&action=edit&doc_id=' . $doc_id ) :
                admin_url( 'admin.php?page=document-manager&action=add' );
            wp_redirect( $redirect_url );
            exit;
        }

        // If editing and a previous file exists, delete it
        if ( $doc_id ) {
            $old_file_path = $wpdb->get_var( $wpdb->prepare( "SELECT file_path FROM {$table_name} WHERE id = %d", $doc_id ) );
            if ( $old_file_path && file_exists( $old_file_path ) ) {
                wp_delete_file( $old_file_path );
            }
        }

        $data_to_save['file_name'] = sanitize_file_name( basename( $_FILES['document_file']['name'] ) );
        $data_to_save['file_path'] = $uploaded_file_info['file']; // Full path to the uploaded file
        $data_to_save['file_type'] = $uploaded_file_info['type'];

        // Add formats for file fields
        $format[] = '%s'; // file_name
        $format[] = '%s'; // file_path
        $format[] = '%s'; // file_type

    } elseif ( $action_type === 'add_document' && ( !isset($_FILES['document_file']) || $_FILES['document_file']['size'] == 0) ) {
        // File is required for new documents
        add_settings_error( 'document_manager_errors', 'file_empty', __( 'A document file is required when adding a new document.', 'document-manager' ), 'error' );
        wp_redirect( admin_url( 'admin.php?page=document-manager&action=add' ) );
        exit;
    }


    // Save to Database
    if ( $doc_id ) { // Editing existing document
        $result = $wpdb->update( $table_name, $data_to_save, ['id' => $doc_id], $format, ['%d'] );
        if ( $result === false ) {
            add_settings_error( 'document_manager_errors', 'db_update_error', __( 'Error updating document in database.', 'document-manager' ), 'error' );
        } else {
            add_settings_error( 'document_manager_messages', 'document_updated', __( 'Document updated successfully.', 'document-manager' ), 'updated' );
        }
        $redirect_url = admin_url( 'admin.php?page=document-manager&action=edit&doc_id=' . $doc_id );

    } else { // Adding new document
        $data_to_save['uploaded_on'] = current_time( 'mysql' );
        $format[] = '%s'; // uploaded_on

        $result = $wpdb->insert( $table_name, $data_to_save, $format );
        if ( $result === false ) {
            add_settings_error( 'document_manager_errors', 'db_insert_error', __( 'Error adding document to database.', 'document-manager' ), 'error' );
            $redirect_url = admin_url( 'admin.php?page=document-manager&action=add' );
        } else {
            $doc_id = $wpdb->insert_id;
            add_settings_error( 'document_manager_messages', 'document_added', __( 'Document added successfully.', 'document-manager' ), 'updated' );
            $redirect_url = admin_url( 'admin.php?page=document-manager&action=edit&doc_id=' . $doc_id ); // Redirect to edit page of new doc
        }
    }

    set_transient('settings_errors', get_settings_errors(), 30); // Make sure errors are not lost on redirect
    wp_redirect( $redirect_url );
    exit;
}
