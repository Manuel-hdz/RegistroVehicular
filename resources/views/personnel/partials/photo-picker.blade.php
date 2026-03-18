@props([
    'photoUrl' => null,
])

@once
    @push('head')
        <style>
            .personnel-photo-picker {
                display: grid;
                gap: 10px;
            }
            .personnel-photo-preview {
                width: 100%;
                max-width: 220px;
                aspect-ratio: 3 / 4;
                object-fit: cover;
                border-radius: 12px;
                border: 1px solid #d1d5db;
                background: linear-gradient(180deg, #ffffff, #eef2f7);
                display: none;
            }
            .personnel-photo-preview.is-visible {
                display: block;
            }
            .personnel-photo-empty {
                width: 100%;
                max-width: 220px;
                aspect-ratio: 3 / 4;
                border-radius: 12px;
                border: 1px dashed #cbd5e1;
                background: #f8fafc;
                color: #64748b;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 12px;
                font-size: 14px;
            }
            .personnel-photo-cropper {
                position: fixed;
                inset: 0;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 18px;
                background: rgba(15, 23, 42, .58);
                backdrop-filter: blur(6px);
                z-index: 2200;
            }
            .personnel-photo-cropper[aria-hidden="false"] {
                display: flex;
            }
            .personnel-photo-cropper-card {
                width: min(980px, 100%);
                max-height: 90vh;
                overflow: auto;
                border-radius: 18px;
                background: #fff;
                border: 1px solid #d1d5db;
                box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
            }
            .personnel-photo-cropper-head {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
                padding: 16px 18px 10px;
                border-bottom: 1px solid #eef2f7;
            }
            .personnel-photo-cropper-head h3 {
                margin: 0;
                font-size: 18px;
            }
            .personnel-photo-cropper-head p {
                margin: 6px 0 0;
                color: #6b7280;
                font-size: 13px;
            }
            .personnel-photo-cropper-close {
                border: none;
                background: transparent;
                color: #64748b;
                font-size: 28px;
                line-height: 1;
                cursor: pointer;
            }
            .personnel-photo-cropper-body {
                display: grid;
                grid-template-columns: minmax(320px, 1fr) minmax(260px, 300px);
                gap: 18px;
                padding: 18px;
            }
            .personnel-photo-stage {
                display: grid;
                place-items: center;
                min-height: 320px;
                border-radius: 16px;
                background: linear-gradient(180deg, #f8fafc, #eef2f7);
                border: 1px solid #e5e7eb;
                padding: 14px;
            }
            .personnel-photo-canvas {
                width: min(100%, 420px);
                aspect-ratio: 3 / 4;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 8px 20px rgba(15, 23, 42, .10);
            }
            .personnel-photo-controls {
                display: grid;
                gap: 14px;
                align-content: start;
            }
            .personnel-photo-control {
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                background: #f8fafc;
                padding: 12px;
            }
            .personnel-photo-control label {
                margin-bottom: 8px;
                font-size: 14px;
            }
            .personnel-photo-control input[type="range"] {
                width: 100%;
                padding: 0;
                border: none;
                outline: none;
                background: transparent;
            }
            .personnel-photo-cropper-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 0 18px 18px;
                flex-wrap: wrap;
            }
            @media (max-width: 860px) {
                .personnel-photo-cropper-body {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush
@endonce

<div class="personnel-photo-picker" data-personnel-photo-picker>
    <input type="file" name="photo" accept="image/*" data-photo-input>
    <input type="hidden" name="photo_cropped" value="" data-photo-cropped>

    <img
        src="{{ $photoUrl ?? '' }}"
        alt="Vista previa de la fotografia"
        class="personnel-photo-preview {{ !empty($photoUrl) ? 'is-visible' : '' }}"
        data-photo-preview
        {{ empty($photoUrl) ? 'hidden' : '' }}
    >

    <div class="personnel-photo-empty" data-photo-empty {{ !empty($photoUrl) ? 'hidden' : '' }}>
        Carga una fotografia para mostrarla en el perfil del colaborador.
    </div>

    <div class="personnel-photo-cropper" aria-hidden="true" data-photo-cropper>
        <div class="personnel-photo-cropper-card" role="dialog" aria-modal="true">
            <div class="personnel-photo-cropper-head">
                <div>
                    <h3>Ajustar fotografia</h3>
                    <p>Mueve el enfoque y acerca la imagen antes de guardarla.</p>
                </div>
                <button class="personnel-photo-cropper-close" type="button" aria-label="Cerrar" data-photo-cancel>&times;</button>
            </div>

            <div class="personnel-photo-cropper-body">
                <div class="personnel-photo-stage">
                    <canvas class="personnel-photo-canvas" width="900" height="1200" data-photo-canvas></canvas>
                </div>

                <div class="personnel-photo-controls">
                    <div class="personnel-photo-control">
                        <label>Zoom</label>
                        <input type="range" min="1" max="3" step="0.01" value="1" data-photo-zoom>
                    </div>
                    <div class="personnel-photo-control">
                        <label>Enfoque horizontal</label>
                        <input type="range" min="0" max="100" step="1" value="50" data-photo-pan-x>
                    </div>
                    <div class="personnel-photo-control">
                        <label>Enfoque vertical</label>
                        <input type="range" min="0" max="100" step="1" value="50" data-photo-pan-y>
                    </div>
                </div>
            </div>

            <div class="personnel-photo-cropper-actions">
                <button class="btn btn-secondary" type="button" data-photo-cancel>Cancelar</button>
                <button class="btn btn-primary" type="button" data-photo-apply>Usar imagen</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function initPhotoPicker(picker) {
                    const input = picker.querySelector('[data-photo-input]');
                    const croppedInput = picker.querySelector('[data-photo-cropped]');
                    const preview = picker.querySelector('[data-photo-preview]');
                    const empty = picker.querySelector('[data-photo-empty]');
                    const cropper = picker.querySelector('[data-photo-cropper]');
                    const canvas = picker.querySelector('[data-photo-canvas]');
                    const zoom = picker.querySelector('[data-photo-zoom]');
                    const panX = picker.querySelector('[data-photo-pan-x]');
                    const panY = picker.querySelector('[data-photo-pan-y]');
                    const apply = picker.querySelector('[data-photo-apply]');
                    const cancelButtons = picker.querySelectorAll('[data-photo-cancel]');
                    const context = canvas ? canvas.getContext('2d') : null;
                    let image = null;
                    let objectUrl = null;

                    function togglePreview(src) {
                        if (!preview || !empty) return;

                        if (src) {
                            preview.src = src;
                            preview.hidden = false;
                            preview.classList.add('is-visible');
                            empty.hidden = true;
                        } else {
                            preview.hidden = true;
                            preview.classList.remove('is-visible');
                            empty.hidden = false;
                        }
                    }

                    function draw() {
                        if (!context || !canvas || !image) return;

                        const zoomValue = Number(zoom.value || 1);
                        const panXValue = Number(panX.value || 50) / 100;
                        const panYValue = Number(panY.value || 50) / 100;
                        const targetWidth = canvas.width;
                        const targetHeight = canvas.height;
                        const baseScale = Math.max(targetWidth / image.width, targetHeight / image.height);
                        const scale = baseScale * zoomValue;
                        const drawWidth = image.width * scale;
                        const drawHeight = image.height * scale;
                        const overflowX = Math.max(0, drawWidth - targetWidth);
                        const overflowY = Math.max(0, drawHeight - targetHeight);
                        const drawX = -(overflowX * panXValue);
                        const drawY = -(overflowY * panYValue);

                        context.clearRect(0, 0, targetWidth, targetHeight);
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, targetWidth, targetHeight);
                        context.drawImage(image, drawX, drawY, drawWidth, drawHeight);
                    }

                    function openCropper() {
                        if (!cropper) return;
                        cropper.setAttribute('aria-hidden', 'false');
                    }

                    function closeCropper() {
                        if (!cropper) return;
                        cropper.setAttribute('aria-hidden', 'true');
                    }

                    function resetControls() {
                        zoom.value = '1';
                        panX.value = '50';
                        panY.value = '50';
                    }

                    function loadFile(file) {
                        if (!file || !file.type.startsWith('image/')) return;

                        if (objectUrl) {
                            URL.revokeObjectURL(objectUrl);
                        }

                        objectUrl = URL.createObjectURL(file);
                        image = new Image();
                        image.onload = function () {
                            resetControls();
                            draw();
                            openCropper();
                        };
                        image.src = objectUrl;
                    }

                    if (input) {
                        input.addEventListener('change', function () {
                            const file = input.files && input.files[0];
                            if (!file) return;
                            loadFile(file);
                        });
                    }

                    [zoom, panX, panY].forEach(function (control) {
                        if (!control) return;
                        control.addEventListener('input', draw);
                    });

                    if (apply) {
                        apply.addEventListener('click', function () {
                            if (!canvas || !croppedInput) return;
                            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                            croppedInput.value = dataUrl;
                            togglePreview(dataUrl);
                            closeCropper();
                        });
                    }

                    cancelButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            closeCropper();
                        });
                    });

                    if (cropper) {
                        cropper.addEventListener('click', function (event) {
                            if (event.target === cropper) {
                                closeCropper();
                            }
                        });
                    }
                }

                document.querySelectorAll('[data-personnel-photo-picker]').forEach(initPhotoPicker);
            })();
        </script>
    @endpush
@endonce
