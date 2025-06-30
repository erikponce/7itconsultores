<?php
/**
 * Provide a admin area view for the QR validation page
 *
 * This file is used to markup the admin-facing aspects of the plugin for QR validation.
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
    <p><?php esc_html_e( 'Utiliza esta página para validar los códigos QR de los asistentes.', 'registro-evento-qr' ); ?></p>

    <div id="reqr-validation-area">
        <h2><?php esc_html_e( 'Escanear Código QR', 'registro-evento-qr' ); ?></h2>
        <div id="reqr-qr-reader" style="width: 500px;"></div>
        <div id="reqr-qr-reader-results"></div>

        <hr>

        <h2><?php esc_html_e( 'Validación Manual por Clave', 'registro-evento-qr' ); ?></h2>
                        <form id="reqr-admin-manual-validation-form" method="post" action="">
                            <?php wp_nonce_field( 'reqr_validate_qr_admin_nonce', '_wpnonce_reqr_validate_qr_admin' ); // Nonce para AJAX ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                                        <label for="reqr_qr_code_hash_manual_admin"><?php esc_html_e( 'Clave del QR:', 'registro-evento-qr' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="reqr_qr_code_hash_manual_admin" name="reqr_qr_code_hash_manual_admin" value="" class="regular-text"/>
                        <p class="description"><?php esc_html_e( 'Ingresa la clave única del código QR.', 'registro-evento-qr' ); ?></p>
                    </td>
                </tr>
            </table>
                            <button type="submit" id="reqr_submit_validate_manual_admin" class="button button-primary">
                                <?php esc_html_e( 'Validar Clave', 'registro-evento-qr' ); ?>
                            </button>
        </form>
                        <div id="reqr-manual-validation-results" class="reqr-validation-output-area">
                            <p><?php esc_html_e( 'Esperando validación...', 'registro-evento-qr' ); ?></p>
            <?php
            // // Mostrar resultados de la validación si existen
            // if ( isset( $GLOBALS['reqr_admin_validation_result'] ) ) {
            //     $result = $GLOBALS['reqr_admin_validation_result'];
            //     $status_class = $result['status'] === 'success' ? 'notice-success' : ($result['status'] === 'warning' ? 'notice-warning' : 'notice-error');
            //     echo '<div class="notice ' . $status_class . ' is-dismissible"><p>' . esc_html( $result['message'] ) . '</p></div>';
            //     if ( isset( $result['data'] ) ) {
            //         echo '<h4>' . esc_html__( 'Datos del Registrado:', 'registro-evento-qr' ) . '</h4>';
            //         echo '<p>';
            //         echo '<strong>' . esc_html__( 'Nombre:', 'registro-evento-qr' ) . '</strong> ' . esc_html( $result['data']->nombre ) . '<br>';
            //         echo '<strong>' . esc_html__( 'Email:', 'registro-evento-qr' ) . '</strong> ' . esc_html( $result['data']->email ) . '<br>';
            //         echo '<strong>' . esc_html__( 'Empresa:', 'registro-evento-qr' ) . '</strong> ' . esc_html( $result['data']->empresa ) . '<br>';
            //         // ... más datos si es necesario
            //         echo '</p>';
            //     }
            // }
            ?>
        </div>
    </div>
</div>

<script type="text/javascript">
// // Lógica para el scanner QR (html5-qrcode) se añadirá aquí o en un archivo JS encolado.
// // Esto es solo un placeholder.
// function onScanSuccessAdmin(decodedText, decodedResult) {
//     // Manejar el éxito del escaneo
//     console.log(`Code matched = ${decodedText}`, decodedResult);
//     document.getElementById('reqr-qr-reader-results').innerText = `Resultado: ${decodedText}`;
//     // Aquí se haría una petición AJAX para validar el código `decodedText`
//     // y mostrar el resultado de la validación.
// }

// function onScanFailureAdmin(error) {
//     // Manejar el fallo del escaneo, opcionalmente mostrar algo.
//     // console.warn(`Code scan error = ${error}`);
// }

// document.addEventListener('DOMContentLoaded', (event) => {
//     // Comprobar si la librería html5QrCode está disponible
//     if (typeof Html5Qrcode !== 'undefined') {
//         let html5QrcodeScanner = new Html5Qrcode("reqr-qr-reader");
//         html5QrcodeScanner.start(
//             { facingMode: "environment" }, // Usar cámara trasera
//             {
//                 fps: 10,    // Frames por segundo, opcional
//                 qrbox: { width: 250, height: 250 }  // Tamaño del cuadro de escaneo, opcional
//             },
//             onScanSuccessAdmin,
//             onScanFailureAdmin
//         ).catch((err) => {
//             console.log("Error al iniciar el scanner: " + err);
//             document.getElementById('reqr-qr-reader-results').innerText = "Error al iniciar el scanner: " + err;
//         });
//     } else {
//         console.error("Html5Qrcode no está definido. Asegúrate de que la librería esté cargada.");
//         document.getElementById('reqr-qr-reader-results').innerText = "Error: Librería de escaneo no cargada.";
//     }
// });
</script>
