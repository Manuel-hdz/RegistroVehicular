(() => {
    const root = document.getElementById('qrScanner');

    if (!root) {
        return;
    }

    const fileInput = root.querySelector('[data-qr-file]');
    const image = root.querySelector('[data-qr-image]');
    const video = root.querySelector('[data-qr-video]');
    const actions = root.querySelector('[data-qr-actions]');
    const message = root.querySelector('[data-qr-message]');
    const result = root.querySelector('[data-qr-result]');
    const textarea = root.querySelector('[data-qr-text]');
    const uploadButton = root.querySelector('[data-qr-upload]');
    const cameraButton = root.querySelector('[data-qr-camera]');
    const switchCameraButton = root.querySelector('[data-qr-switch-camera]');
    const stopButton = root.querySelector('[data-qr-stop]');
    const copyButton = root.querySelector('[data-qr-copy]');
    const closeButton = root.querySelector('[data-qr-close]');

    let stream = null;
    let detector = null;
    let scanTimer = null;
    let remoteScanBusy = false;
    let nativeDetectorUnavailable = false;
    let facingMode = 'environment';
    const easterEggCodes = new Set(['75007614']);
    const preferredFormats = ['qr_code', 'ean_8', 'ean_13', 'upc_a', 'upc_e'];

    const hasNativeDetector = () => !nativeDetectorUnavailable && 'BarcodeDetector' in window;

    const stopStream = () => {
        if (scanTimer) {
            window.clearInterval(scanTimer);
            scanTimer = null;
        }

        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        video.srcObject = null;
    };

    const resetReader = () => {
        stopStream();
        message.textContent = 'Sube una imagen o usa la camara para leer un codigo QR.';
        actions.classList.remove('d-none');
        image.classList.remove('is-visible');
        video.classList.remove('is-visible');
        result.classList.add('d-none');
        switchCameraButton.classList.add('d-none');
        stopButton.classList.add('d-none');
        textarea.value = '';
        fileInput.value = '';
        image.removeAttribute('src');
    };

    const isValidUrl = (text) => {
        try {
            const url = new URL(text);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch {
            return false;
        }
    };

    const normalizeScannedUrl = (text) => {
        if (!isValidUrl(text)) {
            return text;
        }

        const url = new URL(text);
        const localHosts = ['localhost', '127.0.0.1'];

        if (localHosts.includes(url.hostname)) {
            return `${window.location.origin}${url.pathname}${url.search}${url.hash}`;
        }

        if (url.protocol === 'http:' && window.location.protocol === 'https:' && url.hostname === window.location.hostname) {
            url.protocol = 'https:';
        }

        return url.toString();
    };

    const normalizeBarcode = (text) => String(text || '').replace(/\D+/g, '');

    const showResult = (text, previewSrc = null) => {
        if (!text) {
            message.textContent = 'No se pudo leer el codigo QR.';
            return;
        }

        text = normalizeScannedUrl(text);
        stopStream();

        if (easterEggCodes.has(normalizeBarcode(text))) {
            window.location.href = window.location.origin + '/creadores/coca-cola';
            return;
        }

        if (isValidUrl(text)) {
            window.location.href = text;
            return;
        }

        actions.classList.add('d-none');
        result.classList.remove('d-none');
        video.classList.remove('is-visible');
        switchCameraButton.classList.add('d-none');
        stopButton.classList.add('d-none');
        textarea.value = text;

        if (previewSrc) {
            image.src = previewSrc;
            image.classList.add('is-visible');
        }
    };

    const getDetector = async () => {
        if (!hasNativeDetector()) {
            return null;
        }

        if (!detector) {
            try {
                const supportedFormats = typeof BarcodeDetector.getSupportedFormats === 'function'
                    ? await BarcodeDetector.getSupportedFormats()
                    : preferredFormats;
                const formats = preferredFormats.filter((format) => supportedFormats.includes(format));

                detector = new BarcodeDetector({ formats: formats.length ? formats : ['qr_code'] });
            } catch {
                nativeDetectorUnavailable = true;
                return null;
            }
        }

        return detector;
    };

    const detectFromBitmap = async (source) => {
        const reader = await getDetector();

        if (!reader) {
            return null;
        }

        const codes = await reader.detect(source);
        return codes[0]?.rawValue || null;
    };

    const readQrWithService = async (blob) => {
        const formData = new FormData();
        formData.append('file', blob, 'qr.png');

        const response = await fetch('https://api.qrserver.com/v1/read-qr-code/', {
            method: 'POST',
            body: formData,
        });
        const data = await response.json();

        return data?.[0]?.symbol?.[0]?.data || null;
    };

    const readImageQr = async (file) => {
        const previewSrc = URL.createObjectURL(file);
        image.src = previewSrc;
        image.classList.add('is-visible');
        message.textContent = 'Leyendo codigo QR...';

        try {
            const bitmap = await createImageBitmap(file);
            const text = await detectFromBitmap(bitmap);

            if (text) {
                showResult(text, previewSrc);
                return;
            }
        } catch {
            // Si el navegador no puede leer el bitmap, se intenta el servicio externo.
        }

        readQrWithService(file)
            .then((text) => showResult(text, previewSrc))
            .catch(() => {
                message.textContent = 'No se pudo leer el QR. Prueba con una imagen mas clara o usa Chrome/Edge actualizado.';
            });
    };

    const canvasToBlob = (canvas) => new Promise((resolve) => {
        canvas.toBlob(resolve, 'image/png', 0.85);
    });

    const detectVideoWithService = async () => {
        if (remoteScanBusy || !stream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
            return;
        }

        remoteScanBusy = true;

        try {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            const blob = await canvasToBlob(canvas);

            if (!blob) {
                return;
            }

            const text = await readQrWithService(blob);

            if (text) {
                showResult(text);
            }
        } catch {
            message.textContent = 'No se pudo leer desde la camara. Intenta subir una foto del QR.';
        } finally {
            remoteScanBusy = false;
        }
    };

    const scanVideoFrame = async () => {
        if (!stream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
            return;
        }

        try {
            const text = hasNativeDetector()
                ? await detectFromBitmap(video)
                : null;

            if (text) {
                showResult(text);
            }
        } catch {
            nativeDetectorUnavailable = true;
            if (scanTimer) {
                window.clearInterval(scanTimer);
            }
            message.textContent = 'Escaneando QR con respaldo externo...';
            scanTimer = window.setInterval(detectVideoWithService, 1200);
        }
    };

    const startCameraScan = async () => {
        if (!window.isSecureContext) {
            message.textContent = 'La camara solo funciona con HTTPS. Activa SSL en Hostinger para este subdominio.';
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            message.textContent = 'Este navegador no permite abrir la camara desde la pagina.';
            return;
        }

        stopStream();
        message.textContent = 'Abriendo camara...';

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: facingMode } },
                audio: false,
            });
        } catch {
            message.textContent = 'No se pudo acceder a la camara. Revisa permisos del navegador y que el sitio tenga HTTPS.';
            return;
        }

        video.srcObject = stream;
        await video.play();

        actions.classList.add('d-none');
        image.classList.remove('is-visible');
        video.classList.add('is-visible');
        stopButton.classList.remove('d-none');
        switchCameraButton.classList.remove('d-none');
        message.textContent = hasNativeDetector()
            ? 'Escaneando QR con la camara...'
            : 'Escaneando QR con respaldo externo...';
        scanTimer = window.setInterval(hasNativeDetector() ? scanVideoFrame : detectVideoWithService, hasNativeDetector() ? 450 : 1200);
    };

    const switchCamera = () => {
        facingMode = facingMode === 'environment' ? 'user' : 'environment';
        startCameraScan();
    };

    uploadButton.addEventListener('click', () => fileInput.click());
    cameraButton.addEventListener('click', startCameraScan);
    switchCameraButton.addEventListener('click', switchCamera);
    stopButton.addEventListener('click', resetReader);
    closeButton.addEventListener('click', resetReader);

    copyButton.addEventListener('click', () => {
        navigator.clipboard?.writeText(textarea.value);
    });

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];

        if (file) {
            readImageQr(file);
        }
    });
})();