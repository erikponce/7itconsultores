<?php
/**
 * Provide a public-facing view for the confirmation screen.
 *
 * @link       https://7itconsultores.com/
 * @since      1.1.0
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/public/partials
 */

// Note: The variables $name and $qr_code are passed from the render_confirmation_partial() method.

// Generate the QR code as a data URI
$qr_code_image_data = (new \chillerlan\QRCode\QRCode)->render($qr_code);

?>

<div id="evento-checkin-confirmation-wrapper">
    <h2><?php _e( 'Thank You for Registering!', 'evento-check-in' ); ?></h2>

    <p><?php printf( __( 'Hello %s, your registration is complete.', 'evento-check-in' ), esc_html( $name ) ); ?></p>

    <p><?php _e( 'Please keep the following registration key and QR code for check-in:', 'evento-check-in' ); ?></p>

    <p>
        <strong><?php _e( 'Registration Key:', 'evento-check-in' ); ?></strong>
        <code><?php echo esc_html( $qr_code ); ?></code>
    </p>

    <div class="evento-checkin-qr-code">
        <img src="<?php echo esc_attr( $qr_code_image_data ); ?>" alt="<?php _e( 'Your QR Code', 'evento-check-in' ); ?>">
    </div>

    <p><?php _e( 'You will also receive a confirmation email with this information.', 'evento-check-in' ); ?></p>
</div>
