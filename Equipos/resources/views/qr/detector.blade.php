@extends('layouts.app')

@section('title', 'Detector QR')

@push('styles')
    {{-- CSS adaptado desde detector qr/style.css y limitado a esta vista. --}}
    <link rel="stylesheet" href="{{ asset('css/detector-qr.css') }}">
@endpush

@section('content')
    <section id="qrScanner" class="qr-scanner mx-auto">
        <div class="d-flex align-items-center justify-content-end gap-3 mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm d-none" data-qr-switch-camera>
                Cambiar camara
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm d-none" data-qr-stop>
                Detener camara
            </button>
        </div>

        <div class="qr-scanner__viewer" data-qr-viewer>
            <input type="file" accept="image/*" hidden data-qr-file>
            <img alt="Codigo QR seleccionado" data-qr-image>
            <video muted playsinline data-qr-video></video>

            <div class="qr-scanner__actions" data-qr-actions>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-3">
                    <button type="button" class="btn btn-primary" data-qr-upload>
                        Subir imagen
                    </button>
                    <button type="button" class="btn btn-success" data-qr-camera>
                        Usar camara
                    </button>
                </div>
                <p class="mb-0" data-qr-message>Sube una imagen o usa la camara para leer un codigo QR.</p>
            </div>
        </div>

        <div class="qr-scanner__result mt-3 d-none" data-qr-result>
            <label class="form-label" for="qrText">Texto detectado</label>
            <textarea id="qrText" class="form-control" rows="5" readonly data-qr-text></textarea>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-primary" data-qr-copy>Copiar</button>
                <button type="button" class="btn btn-outline-secondary" data-qr-close>Cerrar</button>
            </div>
        </div>

    </section>
    <section class="brand-hero">
        <div>
            <p class="brand-hero__label">Acceso rapido</p>
            <h1 class="h3 mb-1">Detector QR</h1>
            <p class="mb-0">Escanea codigos para abrir la informacion de cada unidad.</p>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/detector-qr.js') }}?v=20260630-coke-hidden"></script>
@endpush
