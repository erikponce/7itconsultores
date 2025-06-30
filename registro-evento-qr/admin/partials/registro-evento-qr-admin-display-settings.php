<?php
/**
 * Provide a admin area view for the plugin settings
 *
 * This file is used to markup the admin-facing aspects of the plugin for settings.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="post" action="options.php">
        <?php
        // settings_fields( $this->plugin_name . '-settings-group' ); // El $this->plugin_name no está disponible directamente aquí. Se debe pasar o definir.
        // do_settings_sections( $this->plugin_name . '-settings-group' );
        // Para que funcione, necesitaríamos pasar $this->plugin_name a esta vista o usar la cadena directamente.
        // Por ahora, lo comentamos para evitar errores.
        ?>
        <?php settings_fields( 'registro-evento-qr-settings-group' ); ?>
        <?php do_settings_sections( 'registro-evento-qr-settings-group' ); ?>

        <?php submit_button( __( 'Guardar Cambios', 'registro-evento-qr' ) ); ?>
    </form>
</div>
