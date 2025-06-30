(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This premium file is used to define the admin-specific JavaScript functions for the plugin.
	 *
	 * @TODO: Define any custom JavaScript functionality your plugin requires for the admin area.
	 */
	$(document).ready(function(){

        // Lógica para el uploader de medios de WordPress para el logo del evento
        if (typeof wp !== 'undefined' && wp.media) {
            var mediaUploader;
            $('#upload-btn').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Seleccionar o Subir Logo del Evento',
                    button: {
                        text: 'Usar este logo'
                    },
                    multiple: false // No permitir selección múltiple
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#reqr_event_logo_url').val(attachment.url);
                    $('#logo-preview-container').html('<img src="' + attachment.url + '" alt="Vista previa del Logo" style="max-width:200px; height:auto; border:1px solid #ddd;"/>');
                });
                mediaUploader.open();
            });
        }


        // Lógica para el escáner QR en la página de validación del admin (si html5-qrcode.min.js está encolado)
        // Esta parte se activará si existe el div #reqr-qr-reader y la librería Html5Qrcode
        if ($('#reqr-qr-reader').length && typeof Html5Qrcode !== 'undefined') {
            const html5QrCode = new Html5Qrcode("reqr-qr-reader");
            const qrConfig = { fps: 10, qrbox: { width: 250, height: 250 } };
            const qrResultDiv = $('#reqr-qr-reader-results');
            const ajaxOutputDiv = $('#reqr-manual-validation-results'); // Usamos el mismo div para resultados AJAX

            const onScanSuccess = (decodedText, decodedResult) => {
                qrResultDiv.html( `<b>Código Escaneado:</b> ${decodedText}` );
                // Realizar la validación vía AJAX
                validateQrCodeAjax(decodedText, ajaxOutputDiv);
            };

            const onScanFailure = (error) => {
                // qrResultDiv.html( `<span style="color:red;">Error de escaneo: ${error}</span>` );
            };

            html5QrCode.start({ facingMode: "environment" }, qrConfig, onScanSuccess, onScanFailure)
            .catch(err => {
                qrResultDiv.html( `<b style="color:red;">Error al iniciar el escáner: ${err}. Asegúrate de permitir el acceso a la cámara.</b>` );
                console.error("Error al iniciar el escáner QR: ", err);
            });
        }


        // Lógica para la validación manual por clave vía AJAX
        $('#reqr_submit_validate_manual_admin').click(function(e){
            e.preventDefault();
            const qrCode = $('#reqr_qr_code_hash_manual_admin').val();
            const ajaxOutputDiv = $('#reqr-manual-validation-results');

            if (qrCode.trim() === '') {
                ajaxOutputDiv.html('<div class="notice notice-error is-dismissible"><p>Por favor, ingresa una clave para validar.</p></div>');
                return;
            }
            validateQrCodeAjax(qrCode, ajaxOutputDiv);
        });


        /**
         * Función para validar el código QR vía AJAX.
         * @param {string} qrCode El código/hash QR a validar.
         * @param {object} resultContainer El elemento jQuery donde mostrar el resultado.
         */
        function validateQrCodeAjax(qrCode, resultContainer) {
            resultContainer.html('<p>Validando...</p>');

            $.ajax({
                url: ajaxurl, // `ajaxurl` está definido globalmente por WordPress en el admin
                type: 'POST',
                data: {
                    action: 'reqr_validate_qr_code_admin', // Esta acción debe ser manejada en PHP
                    qr_code_hash: qrCode,
                    _ajax_nonce: $('#_wpnonce_reqr_validate_qr_admin').val() // Asegúrate de que este nonce exista en tu form o sea pasado de otra forma
                },
                success: function(response) {
                    if (response.success) {
                        let data = response.data;
                        let messageHtml = '<div class="notice notice-' + data.status + ' is-dismissible"><p>' + data.message + '</p></div>';
                        if (data.details) {
                            messageHtml += '<h4>Detalles del Registro:</h4>';
                            messageHtml += '<p>';
                            messageHtml += '<strong>Nombre:</strong> ' + data.details.nombre + '<br>';
                            messageHtml += '<strong>Email:</strong> ' + data.details.email + '<br>';
                            messageHtml += '<strong>Empresa:</strong> ' + data.details.empresa + '<br>';
                            if(data.details.puesto) messageHtml += '<strong>Puesto:</strong> ' + data.details.puesto + '<br>';
                            if(data.details.telefono) messageHtml += '<strong>Teléfono:</strong> ' + data.details.telefono + '<br>';
                            messageHtml += '<strong>Estado:</strong> ' + (data.details.confirmado == 1 ? '<span class="status-confirmado">Confirmado</span>' : '<span class="status-no-confirmado">No Confirmado</span>') + '<br>';
                            messageHtml += '</p>';
                        }
                        resultContainer.html(messageHtml);
                        // Si la validación fue exitosa y se actualizó el estado, podrías querer actualizar la tabla de registros si está visible.
                        // Esto requeriría más lógica, por ejemplo, si la tabla usa WP_List_Table y se puede recargar con AJAX.
                    } else {
                        resultContainer.html('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    resultContainer.html('<div class="notice notice-error is-dismissible"><p>Error de AJAX: ' + textStatus + ' - ' + errorThrown + '</p></div>');
                    console.error("AJAX error: ", textStatus, errorThrown);
                }
            });
        }

        // Confirmación antes de eliminar un registro (si se añade botón de eliminar en la tabla)
        $('.reqr-delete-registro').on('click', function(e){
            if(!confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.')){
                e.preventDefault();
            }
        });

        // Confirmación antes de reenviar correo (si se añade botón en la tabla)
        $('.reqr-resend-email').on('click', function(e){
            if(!confirm('¿Estás seguro de que quieres reenviar el correo de confirmación a este usuario?')){
                e.preventDefault();
            }
            // Podría hacerse con AJAX para mejor UX
        });


	}); // end document ready

})( jQuery );
