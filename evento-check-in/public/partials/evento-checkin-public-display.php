<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://7itconsultores.com/
 * @since      1.0.0
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/public/partials
 */

$options = get_option( 'evento-check-in-settings' );
$event_title = isset( $options['event_title'] ) ? $options['event_title'] : 'Event Registration';

?>

<div id="evento-checkin-form-wrapper">

    <h2><?php echo esc_html( $event_title ); ?></h2>

    <form id="evento-checkin-form" method="post">
        <p>
            <label for="evento-name"><?php _e( 'Name', 'evento-check-in' ); ?></label>
            <input type="text" id="evento-name" name="evento_name" required>
        </p>
        <p>
            <label for="evento-email"><?php _e( 'Email', 'evento-check-in' ); ?></label>
            <input type="email" id="evento-email" name="evento_email" required>
        </p>

        <?php wp_nonce_field( 'evento_checkin_registration_form', 'evento_checkin_nonce' ); ?>

        <p>
            <input type="submit" name="evento_submit" value="<?php _e( 'Register', 'evento-check-in' ); ?>">
        </p>
    </form>
</div>
