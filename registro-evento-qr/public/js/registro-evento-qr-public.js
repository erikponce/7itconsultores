(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This file is used to define the public-facing JavaScript functions for the plugin.
	 */

	$(document).ready(function() {

        // Validación básica del formulario de registro (ejemplo)
        $('#reqr-registration-form').submit(function(e) {
            var nombre = $('#reqr_nombre').val().trim();
            var email = $('#reqr_email').val().trim();
            var empresa = $('#reqr_empresa').val().trim();
            var errors = [];

            if (nombre === '') {
                errors.push('El nombre es obligatorio.');
                $('#reqr_nombre').css('border-color', 'red');
            } else {
                 $('#reqr_nombre').css('border-color', '#ccc');
            }

            if (email === '') {
                errors.push('El correo electrónico es obligatorio.');
                 $('#reqr_email').css('border-color', 'red');
            } else if (!isValidEmail(email)) {
                errors.push('El formato del correo electrónico no es válido.');
                $('#reqr_email').css('border-color', 'red');
            } else {
                $('#reqr_email').css('border-color', '#ccc');
            }

            if (empresa === '') {
                errors.push('La empresa es obligatoria.');
                $('#reqr_empresa').css('border-color', 'red');
            } else {
                $('#reqr_empresa').css('border-color', '#ccc');
            }

            if (errors.length > 0) {
                e.preventDefault(); // Detener el envío del formulario
                // Mostrar errores (esto podría ser más sofisticado)
                var errorHtml = '<div class="reqr-errors"><p>Por favor, corrige los siguientes errores:</p><ul>';
                $.each(errors, function(index, error) {
                    errorHtml += '<li>' + error + '</li>';
                });
                errorHtml += '</ul></div>';

                // Eliminar mensajes de error anteriores y añadir el nuevo
                $('#reqr-registration-form-wrapper .reqr-errors').remove();
                $('#reqr-registration-form').before(errorHtml);

                // Scroll to a los errores
                $('html, body').animate({
                    scrollTop: $("#reqr-registration-form-wrapper .reqr-errors").offset().top - 50 // 50px de offset
                }, 500);

            } else {
                 $('#reqr-registration-form-wrapper .reqr-errors').remove();
            }
        });

        function isValidEmail(email) {
            var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
            return pattern.test(email);
        }


        // Lógica para el escáner QR en la página de validación pública (template-qr-validation-page.php)
        // Esta parte se activará si existe el div #reqr-public-qr-reader y la librería Html5Qrcode
        if ($('#reqr-public-qr-reader').length && typeof Html5Qrcode !== 'undefined') {
            const html5QrCodePublic = new Html5Qrcode("reqr-public-qr-reader");
            const qrConfigPublic = { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0 }; // qrbox más pequeño para móvil
            const qrResultDivPublic = $('#reqr-public-qr-reader-results');
            const ajaxOutputDivPublic = $('#reqr-ajax-validation-output');

            const onScanSuccessPublic = (decodedText, decodedResult) => {
                qrResultDivPublic.html(`<b>Código Escaneado:</b> ${decodedText} <br><button id="validate-scanned-btn" class="button">Validar este Código</button>`);
                $('#validate-scanned-btn').click(function(){
                    validateQrCodeAjaxPublic(decodedText, ajaxOutputDivPublic);
                });
                // Opcional: detener el escáner después de un escaneo exitoso
                // html5QrCodePublic.stop().then(ignore => {
                //     console.log("QR Code scanning stopped.");
                // }).catch(err => {
                //     console.log("Unable to stop scanning.");
                // });
            };

            const onScanFailurePublic = (error) => {
                // No mostrar errores continuamente, podría ser molesto
                // qrResultDivPublic.html( `<span style="color:red;">Error de escaneo: ${error}</span>` );
            };

            html5QrCodePublic.start({ facingMode: "environment" }, qrConfigPublic, onScanSuccessPublic, onScanFailurePublic)
            .catch(err => {
                qrResultDivPublic.html( `<p style="color:red;"><b>Error al iniciar el escáner:</b> ${err}.<br>Asegúrate de haber concedido permisos para acceder a la cámara y que tu navegador sea compatible.</p>` );
                console.error("Error al iniciar el escáner QR público: ", err);
            });

             // Botón para detener el escáner manualmente, si es necesario
            // qrResultDivPublic.append('<br><button id="stop-scan-btn">Detener Escáner</button>');
            // $('#stop-scan-btn').click(function(){
            //     html5QrCodePublic.stop().then(ignore => {
            //         qrResultDivPublic.append("<p>Escáner detenido.</p>");
            //     }).catch(err => console.log("Error al detener."));
            // });
        }

        // Lógica para la validación manual por clave vía AJAX en la página pública
        $('#reqr-manual-validation-section form').submit(function(e){
            e.preventDefault();
            const qrCode = $('#reqr_qr_code_hash_manual_public').val();
            const ajaxOutputDivPublic = $('#reqr-ajax-validation-output');

            if (qrCode.trim() === '') {
                ajaxOutputDivPublic.html('<div class="reqr-result-error"><p>Por favor, ingresa una clave para validar.</p></div>');
                return;
            }
            validateQrCodeAjaxPublic(qrCode, ajaxOutputDivPublic);
        });


        /**
         * Función para validar el código QR vía AJAX (versión pública).
         * @param {string} qrCode El código/hash QR a validar.
         * @param {object} resultContainer El elemento jQuery donde mostrar el resultado.
         */
        function validateQrCodeAjaxPublic(qrCode, resultContainer) {
            resultContainer.html('<p>Validando...</p>');

            // `reqr_ajax_object` debe ser definido vía wp_localize_script
            if (typeof reqr_ajax_object === 'undefined' || typeof reqr_ajax_object.ajax_url === 'undefined') {
                resultContainer.html('<div class="reqr-result-error"><p>Error de configuración: AJAX URL no definido.</p></div>');
                console.error("reqr_ajax_object o reqr_ajax_object.ajax_url no está definido.");
                return;
            }

            $.ajax({
                url: reqr_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'reqr_validate_qr_code_public', // Esta acción debe ser manejada en PHP
                    qr_code_hash: qrCode,
                    _ajax_nonce: reqr_ajax_object.nonce // Nonce pasado por wp_localize_script
                },
                success: function(response) {
                    if (response.success) {
                        let data = response.data;
                        let messageHtml = '<div class="reqr-result-' + data.status + '"><p>' + data.message + '</p></div>';
                        if (data.details) {
                            messageHtml += '<h4>Detalles del Registro:</h4>';
                            messageHtml += '<p>';
                            messageHtml += '<strong>Nombre:</strong> ' + data.details.nombre + '<br>';
                            messageHtml += '<strong>Email:</strong> ' + data.details.email + '<br>';
                            messageHtml += '<strong>Empresa:</strong> ' + data.details.empresa + '<br>';
                            if(data.details.puesto) messageHtml += '<strong>Puesto:</strong> ' + data.details.puesto + '<br>';
                            // No mostrar teléfono en la parte pública por privacidad, a menos que sea un requisito
                            // if(data.details.telefono) messageHtml += '<strong>Teléfono:</strong> ' + data.details.telefono + '<br>';
                            messageHtml += '<strong>Estado:</strong> ' + (data.details.confirmado == 1 ? '<span style="color:green; font-weight:bold;">Confirmado</span>' : '<span style="color:red;">No Confirmado</span>') + '<br>';
                            messageHtml += '</p>';
                        }
                        resultContainer.html(messageHtml);
                    } else {
                        resultContainer.html('<div class="reqr-result-error"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    resultContainer.html('<div class="reqr-result-error"><p>Error de AJAX: ' + textStatus + ' - ' + errorThrown + '</p></div>');
                    console.error("AJAX error público: ", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        }

	}); // end document ready

})( jQuery );
