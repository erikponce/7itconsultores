<?php
/**
 * Template Name: Página de Validación de QR
 *
 * This is the template that displays the QR validation interface for organizers.
 * It should typically be a private page, accessible only by authorized users.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Registro_Evento_QR
 * @subpackage Registro_Evento_QR/public/partials
 */

// // Es crucial asegurar que solo usuarios autorizados puedan acceder a esta página.
// // Esta verificación debería estar en `handle_qr_validation_page` en la clase Public,
// // pero una comprobación adicional aquí no hace daño.
// if ( ! current_user_can( 'edit_posts' ) ) { // Ajusta la capacidad según sea necesario. 'edit_posts' es un ejemplo.
//     wp_redirect( home_url() );
//     exit;
// }

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            </header><!-- .entry-header -->

            <div class="entry-content">
                <p><?php esc_html_e( 'Utiliza esta interfaz para validar los códigos QR de los asistentes al evento.', 'registro-evento-qr' ); ?></p>

                <div id="reqr-validation-interface">
                    <?php
                    // Mostrar resultado inicial si se accedió con ?qr_code=HASH
                    if ( isset( $GLOBALS['reqr_initial_validation_result'] ) ) {
                        $result = $GLOBALS['reqr_initial_validation_result'];
                        $status_class = 'reqr-result-' . esc_attr( $result['status'] ); // success, warning, error
                        echo '<h2>' . esc_html__( 'Resultado de Validación Inicial:', 'registro-evento-qr' ) . '</h2>';
                        echo '<div id="reqr-initial-validation-output" class="reqr-validation-output-area ' . $status_class . '"><p>' . esc_html( $result['message'] ) . '</p>';
                        if ( isset( $result['data'] ) && is_object( $result['data'] ) ) {
                            echo '<h4>' . esc_html__( 'Datos del Registrado:', 'registro-evento-qr' ) . '</h4>';
                            echo '<p>';
                            echo '<strong>' . esc_html__( 'Nombre:', 'registro-evento-qr' ) . '</strong> ' . esc_html( $result['data']->nombre ) . '<br>';
                            // No mostrar email/teléfono aquí por defecto en la parte pública, solo en AJAX si se decide.
                            echo '<strong>' . esc_html__( 'Empresa:', 'registro-evento-qr' ) . '</strong> ' . esc_html( $result['data']->empresa ) . '<br>';
                            echo '<strong>' . esc_html__( 'Estado:', 'registro-evento-qr' ) . '</strong> ' . ($result['data']->confirmado ? '<span style="color:green;">'.__('Confirmado','registro-evento-qr').'</span>' : '<span style="color:red;">'.__('No Confirmado','registro-evento-qr').'</span>') . '<br>';
                            echo '</p>';
                        }
                        echo '</div> <hr>';
                    }
                    ?>

                    <div id="reqr-scanner-section">
                        <h2><?php esc_html_e( 'Escanear Código QR', 'registro-evento-qr' ); ?></h2>
                        <p><?php esc_html_e( 'Apunta la cámara al código QR del asistente.', 'registro-evento-qr' ); ?></p>
                        <div id="reqr-public-qr-reader" style="width: 100%; max-width: 350px; margin: 10px auto; border-radius: 5px; overflow:hidden;"></div>
                        <div id="reqr-public-qr-reader-status" style="text-align: center; margin-top: 10px; font-style: italic;">
                            <?php esc_html_e( 'Iniciando cámara...', 'registro-evento-qr' ); ?>
                        </div>
                        <div id="reqr-public-qr-reader-results" class="reqr-validation-output-area" style="text-align: center; margin-top: 15px;">
                             <!-- Los resultados del escaneo y botón de validación aparecerán aquí -->
                        </div>
                    </div>

                    <hr style="margin: 40px 0;">

                    <div id="reqr-manual-validation-section">
                        <h2><?php esc_html_e( 'Validación Manual por Clave', 'registro-evento-qr' ); ?></h2>
                        <p><?php esc_html_e( 'Si el escáner no funciona, puedes ingresar la clave del QR manualmente.', 'registro-evento-qr' ); ?></p>
                        <form id="reqr-manual-validation-form" method="post" action="">
                            <?php // El nonce se maneja en JS para AJAX, o se podría añadir aquí si se hace submit tradicional.
                                  // wp_nonce_field( 'reqr_validate_action_public_manual', 'reqr_validate_nonce_public_manual' );
                            ?>
                            <table class="form-table" style="width:auto; margin: 0 auto;">
                                <tr valign="top">
                                    <th scope="row" style="padding-right:10px;">
                                        <label for="reqr_qr_code_hash_manual_public"><?php esc_html_e( 'Clave del QR:', 'registro-evento-qr' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="reqr_qr_code_hash_manual_public" name="reqr_qr_code_hash_manual_public" value="" class="regular-text" style="min-width:280px;"/>
                                    </td>
                                </tr>
                            </table>
                            <p style="text-align:center; margin-top:15px;">
                                <button type="submit" id="reqr_submit_validate_manual_public" class="button button-primary">
                                    <?php esc_html_e( 'Validar Clave', 'registro-evento-qr' ); ?>
                                </button>
                            </p>
                        </form>
                    </div>

                    <div id="reqr-validation-results-area" style="margin-top: 30px;">
                        <h3 style="text-align:center;"><?php esc_html_e( 'Resultado de la Validación', 'registro-evento-qr' ); ?></h3>
                        <div id="reqr-ajax-validation-output" class="reqr-validation-output-area">
                            <p><?php esc_html_e( 'Esperando validación...', 'registro-evento-qr' ); ?></p>
                        </div>
                    </div>

                </div><!-- #reqr-validation-interface -->
            </div><!-- .entry-content -->
        </article><!-- #post-## -->
    </main><!-- #main -->
</div><!-- #primary -->
<script type="text/javascript">
// // Lógica para el scanner QR (html5-qrcode) se añadirá aquí o en un archivo JS encolado.
// // Esto es solo un placeholder.
// function onScanSuccessPublic(decodedText, decodedResult) {
//     console.log(`Code matched = ${decodedText}`, decodedResult);
//     document.getElementById('reqr_qr_code_hash_manual_public').value = decodedText; // Opcional: rellenar el campo manual

//     // Simular envío del formulario o hacer AJAX
//     // Por ahora, solo mostramos el texto decodificado
//     let resultsDiv = document.getElementById('reqr-public-qr-reader-results');
//     resultsDiv.innerHTML = `<strong><?php esc_html_e('Código escaneado:', 'registro-evento-qr'); ?></strong> ${decodedText}<br><button onclick="validateScannedCode('${decodedText}')"><?php esc_html_e('Validar este código', 'registro-evento-qr'); ?></button>`;

//     // Aquí se haría una petición AJAX para validar el código `decodedText`
//     // y mostrar el resultado de la validación en `reqr-ajax-validation-output`.
//     // validateScannedCode(decodedText); // Llamada directa a la función de validación
// }

// function onScanFailurePublic(error) {
//     // console.warn(`Code scan error = ${error}`);
// }

// function validateScannedCode(qrCodeData) {
//     // Aquí iría la lógica AJAX para enviar qrCodeData al backend de WordPress
//     // y actualizar #reqr-ajax-validation-output con la respuesta.
//     // Por ejemplo, usando jQuery.ajax o fetch.

//     // Placeholder para mostrar que se está procesando:
//     document.getElementById('reqr-ajax-validation-output').innerHTML = '<p><?php esc_html_e("Procesando validación...", "registro-evento-qr"); ?></p>';

//     // Ejemplo de cómo podrías estructurar la petición AJAX (necesitaría jQuery o fetch):
//     /*
//     fetch(ajaxurl, { // ajaxurl debe estar definido por wp_localize_script
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/x-www-form-urlencoded',
//         },
//         body: new URLSearchParams({
//             action: 'reqr_validate_qr_ajax', // Define esta acción en PHP
//             qr_code: qrCodeData,
//             _ajax_nonce: '<?php // echo wp_create_nonce('reqr_validate_qr_ajax_nonce'); ?>' // Nonce para seguridad
//         })
//     })
//     .then(response => response.json())
//     .then(data => {
//         let outputHtml = '<p class="reqr-result-' + data.status + '">' + data.message + '</p>';
//         if (data.data) {
//             outputHtml += '<h4>Detalles:</h4>';
//             outputHtml += '<p>Nombre: ' + data.data.nombre + '<br>';
//             outputHtml += 'Email: ' + data.data.email + '<br>';
//             outputHtml += 'Empresa: ' + data.data.empresa + '</p>';
//         }
//         document.getElementById('reqr-ajax-validation-output').innerHTML = outputHtml;
//     })
//     .catch(error => {
//         console.error('Error en la validación AJAX:', error);
//         document.getElementById('reqr-ajax-validation-output').innerHTML = '<p class="reqr-result-error"><?php // esc_html_e("Error en la comunicación con el servidor.", "registro-evento-qr"); ?></p>';
//     });
//     */
// }


// document.addEventListener('DOMContentLoaded', (event) => {
//     if (typeof Html5Qrcode !== 'undefined') {
//         const html5QrCode = new Html5Qrcode("reqr-public-qr-reader");
//         const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

//         // Intenta iniciar la cámara
//         html5QrCode.start(
//             { facingMode: "environment" }, // o { deviceId: { exact: cameraId } } si se selecciona una cámara específica
//             config,
//             onScanSuccessPublic,
//             onScanFailurePublic
//         ).catch((err) => {
//             console.error("No se pudo iniciar el escáner QR: " + err);
//             document.getElementById('reqr-public-qr-reader-results').innerHTML = `<p style="color:red;"><strong>Error:</strong> No se pudo iniciar el escáner QR. ${err}</p><p>Asegúrate de haber concedido permisos para acceder a la cámara.</p>`;
//         });
//     } else {
//         console.error("Html5Qrcode no está definido. Asegúrate de que la librería esté cargada.");
//         document.getElementById('reqr-public-qr-reader-results').innerHTML = "<p style='color:red;'>Error: Librería de escaneo no cargada.</p>";
//     }

//     // // Manejar el envío del formulario manual con AJAX también si se desea,
//     // // para no recargar la página.
//     // const manualForm = document.querySelector('#reqr-manual-validation-section form');
//     // if(manualForm){
//     //     manualForm.addEventListener('submit', function(e){
//     //         e.preventDefault();
//     //         const qrCodeInput = document.getElementById('reqr_qr_code_hash_manual_public');
//     //         if(qrCodeInput && qrCodeInput.value.trim() !== ''){
//     //             validateScannedCode(qrCodeInput.value.trim());
//     //         } else {
//     //             document.getElementById('reqr-ajax-validation-output').innerHTML = '<p class="reqr-result-error"><?php // esc_html_e("Por favor, ingresa una clave para validar.", "registro-evento-qr"); ?></p>';
//     //         }
//     //     });
//     // }
// });
</script>

<?php
// // Encolar el script del escáner QR si no se ha hecho globalmente para esta plantilla
// // wp_enqueue_script( 'html5-qrcode', 'URL_A_TU_LIBRERIA/html5-qrcode.min.js', array(), 'VERSION', true );
// // wp_localize_script( 'TU_SCRIPT_HANDLE_PRINCIPAL_DE_VALIDACION', 'reqr_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce('reqr_validate_qr_ajax_nonce') ) );

get_footer();
?>
