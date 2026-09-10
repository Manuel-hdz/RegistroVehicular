const fileInput = document.querySelector("input"),
  img = document.querySelector("img"),
  video = document.querySelector("video"),
  qrCodeView = document.querySelector(".qrCodeView"),
  iconGroup = document.querySelector(".iconGroup"),
  textarea = document.querySelector("textarea"),
  displayMessage = document.querySelector("p"),
  qrTextDetails = document.querySelector(".qrTextDetails"),
  stopScan = document.getElementById("stopScan");

window.fileInput = fileInput;

let cameraStream = null;
let scanTimer = null;
let detector = null;
let remoteScanBusy = false;

function isValidUrl(text) {
  try {
    const url = new URL(text);
    return url.protocol === "http:" || url.protocol === "https:";
  } catch {
    return false;
  }
}

function normalizeScannedUrl(text) {
  if (!isValidUrl(text)) {
    return text;
  }

  const url = new URL(text);

  if (["localhost", "127.0.0.1"].includes(url.hostname)) {
    return `${window.location.origin}${url.pathname}${url.search}${url.hash}`;
  }

  if (url.protocol === "http:" && window.location.protocol === "https:" && url.hostname === window.location.hostname) {
    url.protocol = "https:";
  }

  return url.toString();
}

function stopCamera() {
  if (scanTimer) {
    clearInterval(scanTimer);
    scanTimer = null;
  }

  if (cameraStream) {
    cameraStream.getTracks().forEach((track) => track.stop());
    cameraStream = null;
  }

  video.srcObject = null;
}

function showResult(text, previewSrc = null) {
  if (!text) {
    displayMessage.innerText = "No se pudo leer el codigo QR";
    return;
  }

  text = normalizeScannedUrl(text);
  stopCamera();

  if (isValidUrl(text)) {
    window.location.href = text;
    return;
  }

  qrTextDetails.style.display = "block";
  qrCodeView.style.display = "flex";
  iconGroup.style.display = "none";
  video.style.display = "none";
  stopScan.style.display = "none";
  textarea.value = text;

  if (previewSrc) {
    img.src = previewSrc;
    img.style.display = "block";
  }
}

async function getQrDetector() {
  if (!("BarcodeDetector" in window)) {
    return null;
  }

  if (!detector) {
    try {
      detector = new BarcodeDetector({ formats: ["qr_code"] });
    } catch {
      return null;
    }
  }

  return detector;
}

async function detectWithNative(source) {
  const reader = await getQrDetector();

  if (!reader) {
    return null;
  }

  const codes = await reader.detect(source);
  return codes[0]?.rawValue || null;
}

async function readQrWithService(blob) {
  const formData = new FormData();
  formData.append("file", blob, "qr.png");

  const response = await fetch("https://api.qrserver.com/v1/read-qr-code/", {
    method: "POST",
    body: formData,
  });
  const data = await response.json();

  return data?.[0]?.symbol?.[0]?.data || null;
}

async function fetchQRCodeRequest(file) {
  const previewSrc = URL.createObjectURL(file);
  img.src = previewSrc;
  img.style.display = "block";
  displayMessage.innerText = "Leyendo codigo QR...";

  try {
    const bitmap = await createImageBitmap(file);
    const text = await detectWithNative(bitmap);

    if (text) {
      showResult(text, previewSrc);
      return;
    }
  } catch {
    // Si el navegador no puede leer el bitmap, se intenta el servicio externo.
  }

  try {
    showResult(await readQrWithService(file), previewSrc);
  } catch {
    displayMessage.innerText = "No se pudo leer el QR. Prueba con una imagen mas clara.";
  }
}

function copyQRCodeText() {
  navigator.clipboard?.writeText(textarea.value);
}

function closeQRCodeReader() {
  stopCamera();
  displayMessage.innerText = "Upload or Scan QR Code to Read";
  iconGroup.style.display = "block";
  img.style.display = "none";
  video.style.display = "none";
  qrTextDetails.style.display = "none";
  stopScan.style.display = "none";
  textarea.value = "";
  fileInput.value = "";
}

function canvasToBlob(canvas) {
  return new Promise((resolve) => canvas.toBlob(resolve, "image/png", 0.85));
}

async function detectCameraWithService() {
  if (remoteScanBusy || !cameraStream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
    return;
  }

  remoteScanBusy = true;

  try {
    const canvas = document.createElement("canvas");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video, 0, 0, canvas.width, canvas.height);

    const blob = await canvasToBlob(canvas);
    const text = blob ? await readQrWithService(blob) : null;

    if (text) {
      showResult(text);
    }
  } catch {
    displayMessage.innerText = "No se pudo leer desde la camara. Intenta subir una foto del QR.";
  } finally {
    remoteScanBusy = false;
  }
}

async function scanCameraFrame() {
  if (!cameraStream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
    return;
  }

  try {
    const text = await detectWithNative(video);

    if (text) {
      showResult(text);
    }
  } catch {
    if (scanTimer) {
      clearInterval(scanTimer);
    }
    displayMessage.innerText = "Escaneando QR con respaldo externo...";
    scanTimer = setInterval(detectCameraWithService, 1200);
  }
}

async function ScanQRImage() {
  if (!window.isSecureContext) {
    displayMessage.innerText = "La camara solo funciona con HTTPS. Activa SSL en el subdominio.";
    return;
  }

  if (!navigator.mediaDevices?.getUserMedia) {
    displayMessage.innerText = "Este navegador no permite abrir la camara desde la pagina.";
    return;
  }

  stopCamera();
  displayMessage.innerText = "Abriendo camara...";

  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: "environment" } },
      audio: false,
    });
  } catch {
    displayMessage.innerText = "No se pudo acceder a la camara. Revisa permisos del navegador.";
    return;
  }

  video.srcObject = cameraStream;
  await video.play();

  iconGroup.style.display = "none";
  img.style.display = "none";
  video.style.display = "block";
  stopScan.style.display = "inline";
  stopScan.onclick = closeQRCodeReader;
  displayMessage.innerText = "Escaneando QR con la camara...";

  scanTimer = (await getQrDetector())
    ? setInterval(scanCameraFrame, 450)
    : setInterval(detectCameraWithService, 1200);
}

fileInput.addEventListener("change", (event) => {
  const file = event.target.files[0];

  if (file) {
    fetchQRCodeRequest(file);
  }
});