<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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
    <p><?php esc_html_e( 'Aquí se mostrará la lista de personas registradas.', 'registro-evento-qr' ); ?></p>
    <!-- Aquí irá la tabla de registros, con filtros y opciones de búsqueda -->
</div>
