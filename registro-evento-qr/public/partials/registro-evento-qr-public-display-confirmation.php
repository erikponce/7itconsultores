<?php
/**
 * Provide a public-facing view for the registration confirmation.
 * This might show the QR code and a thank you message.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/public/partials
 */
?>

<div id="reqr-confirmation-wrapper">
    <h1><?php esc_html_e( '¡Registro Confirmado!', 'registro-evento-qr' ); ?></h1>

    <?php if ( isset( $GLOBALS['reqr_registration_success'] ) && ! empty( $GLOBALS['reqr_registration_success'] ) ) : ?>
        <div class="reqr-success">
            <p><?php echo esc_html( $GLOBALS['reqr_registration_success'] ); ?></p>
        </div>
    <?php else: ?>
        <p><?php esc_html_e( 'Gracias por registrarte. Hemos enviado un correo electrónico con tu código QR de confirmación.', 'registro-evento-qr' ); ?></p>
    <?php endif; ?>

    <?php if ( isset( $GLOBALS['reqr_qr_image_url'] ) && ! empty( $GLOBALS['reqr_qr_image_url'] ) ) : ?>
        <div class="reqr-qr-code">
            <h2><?php esc_html_e( 'Tu Código QR:', 'registro-evento-qr' ); ?></h2>
            <img src="<?php echo esc_url( $GLOBALS['reqr_qr_image_url'] ); ?>" alt="<?php esc_attr_e( 'Código QR de Confirmación', 'registro-evento-qr' ); ?>" />
            <p><small><?php esc_html_e( 'Presenta este código QR al ingresar al evento. También lo recibirás por correo.', 'registro-evento-qr' ); ?></small></p>
        </div>
    <?php endif; ?>

    <?php
    // $GLOBALS['reqr_registration_details'] es poblado por la función de proceso si el registro es exitoso.
    // Viene de $_SESSION['reqr_form_status']['registration_details']
    if ( isset( $_SESSION['reqr_form_status']['registration_details'] ) ) {
         $details = $_SESSION['reqr_form_status']['registration_details'];
    } elseif (isset( $GLOBALS['reqr_registration_details'] ) ) {
         $details = $GLOBALS['reqr_registration_details'];
    }

    if ( isset( $details ) && is_object( $details ) ) :
    ?>
        <div class="reqr-registration-summary">
            <h3><?php esc_html_e( 'Resumen de tu registro:', 'registro-evento-qr' ); ?></h3>
            <p>
                <strong><?php esc_html_e( 'Nombre:', 'registro-evento-qr' ); ?></strong> <?php echo esc_html( $details->nombre ); ?><br>
                <strong><?php esc_html_e( 'Email:', 'registro-evento-qr' ); ?></strong> <?php echo esc_html( $details->email ); ?><br>
                <strong><?php esc_html_e( 'Empresa:', 'registro-evento-qr' ); ?></strong> <?php echo esc_html( $details->empresa ); ?><br>
                <?php if ( ! empty( $details->puesto ) ) : ?>
                    <strong><?php esc_html_e( 'Puesto:', 'registro-evento-qr' ); ?></strong> <?php echo esc_html( $details->puesto ); ?><br>
                <?php endif; ?>
                <?php if ( ! empty( $details->telefono ) ) : ?>
                    <strong><?php esc_html_e( 'Teléfono:', 'registro-evento-qr' ); ?></strong> <?php echo esc_html( $details->telefono ); ?><br>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <p><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( 'Volver al inicio', 'registro-evento-qr' ); ?></a></p>
</div>
