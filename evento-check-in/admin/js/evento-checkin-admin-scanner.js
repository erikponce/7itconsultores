document.addEventListener("DOMContentLoaded", function() {
    const resultContainer = document.getElementById('qr-reader-result');
    let lastScannedCode = null;
    let scanTimeout = null;

    function onScanSuccess(decodedText, decodedResult) {
        // To prevent multiple scans of the same code in quick succession
        if (decodedText === lastScannedCode) {
            return;
        }
        lastScannedCode = decodedText;

        // Reset the last scanned code after a delay
        clearTimeout(scanTimeout);
        scanTimeout = setTimeout(() => {
            lastScannedCode = null;
        }, 3000); // 3 second cooldown

        resultContainer.innerHTML = 'Processing...';

        const formData = new URLSearchParams();
        formData.append('action', 'evento_checkin_attendee');
        formData.append('nonce', evento_checkin_scanner_ajax.nonce);
        formData.append('qr_code', decodedText);

        fetch(evento_checkin_scanner_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                resultContainer.innerHTML = `<div class="notice notice-success is-dismissible"><p>${response.data.message}</p></div>`;
            } else {
                resultContainer.innerHTML = `<div class="notice notice-error is-dismissible"><p>${response.data.message}</p></div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultContainer.innerHTML = `<div class="notice notice-error is-dismissible"><p>An error occurred.</p></div>`;
        });
    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader",
        { fps: 10, qrbox: { width: 250, height: 250 } },
        /* verbose= */ false);

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
});
