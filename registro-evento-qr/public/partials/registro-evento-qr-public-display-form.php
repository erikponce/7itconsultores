<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin, specifically the registration form.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/public/partials
 */
?>

<div id="reqr-registration-form-wrapper">
    <?php
    // Mostrar mensajes de error si existen (desde la variable global poblada por la clase Public)
    if ( isset( $GLOBALS['reqr_registration_errors'] ) && ! empty( $GLOBALS['reqr_registration_errors'] ) ) {
        echo '<div class="reqr-errors">';
        echo '<ul>';
        foreach ( $GLOBALS['reqr_registration_errors'] as $error ) {
            echo '<li>' . esc_html( $error ) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    // El mensaje de éxito se maneja mostrando la plantilla de confirmación.
    ?>

    <form id="reqr-registration-form" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
        <?php wp_nonce_field( 'reqr_registration_action', 'reqr_nonce_field' ); ?>

        <p>
            <label for="reqr_nombre"><?php esc_html_e( 'Nombre Completo:', 'registro-evento-qr' ); ?> <span class="required">*</span></label>
            <input type="text" id="reqr_nombre" name="reqr_nombre" value="<?php echo isset( $_POST['reqr_nombre'] ) ? esc_attr( wp_unslash( $_POST['reqr_nombre'] ) ) : ''; ?>" required>
        </p>
        <p>
            <label for="reqr_email"><?php esc_html_e( 'Correo Electrónico:', 'registro-evento-qr' ); ?> <span class="required">*</span></label>
            <input type="email" id="reqr_email" name="reqr_email" value="<?php echo isset( $_POST['reqr_email'] ) ? esc_attr( wp_unslash( $_POST['reqr_email'] ) ) : ''; ?>" required>
        </p>
        <p>
            <label for="reqr_empresa"><?php esc_html_e( 'Empresa:', 'registro-evento-qr' ); ?> <span class="required">*</span></label>
            <input type="text" id="reqr_empresa" name="reqr_empresa" value="<?php echo isset( $_POST['reqr_empresa'] ) ? esc_attr( wp_unslash( $_POST['reqr_empresa'] ) ) : ''; ?>" required>
        </p>
        <p>
            <label for="reqr_puesto"><?php esc_html_e( 'Puesto:', 'registro-evento-qr' ); ?></label>
            <input type="text" id="reqr_puesto" name="reqr_puesto" value="<?php echo isset( $_POST['reqr_puesto'] ) ? esc_attr( wp_unslash( $_POST['reqr_puesto'] ) ) : ''; ?>">
        </p>
        <p>
            <label for="reqr_telefono"><?php esc_html_e( 'Teléfono:', 'registro-evento-qr' ); ?></label>
            <input type="tel" id="reqr_telefono" name="reqr_telefono" value="<?php echo isset( $_POST['reqr_telefono'] ) ? esc_attr( wp_unslash( $_POST['reqr_telefono'] ) ) : ''; ?>">
        </p>
        <p>
            <input type="submit" name="reqr_submit_registration" value="<?php esc_attr_e( 'Registrarse', 'registro-evento-qr' ); ?>">
        </p>
    </form>
    <p><small><?php esc_html_e( 'Los campos marcados con * son obligatorios.', 'registro-evento-qr' ); ?></small></p>
</div>
