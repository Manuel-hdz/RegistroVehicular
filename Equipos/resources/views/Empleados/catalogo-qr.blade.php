@extends('layouts.app')

@section('title', 'Catalogo QR')

@section('content')
    <form action="{{ route('empleados.catalogo-qr.download') }}" method="POST" id="qrCatalogForm">
        @csrf

        <section class="brand-hero">
            <div>
                <p class="brand-hero__label">Impresion y descarga</p>
                <h1 class="h3 mb-1">Catalogo QR</h1>
                <p class="mb-0">Selecciona codigos y descargalos en un solo ZIP.</p>
            </div>
        </section>

        <div class="d-flex flex-column flex-sm-row justify-content-end align-items-stretch align-items-sm-center gap-2 mb-3">
            <div class="d-grid d-sm-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('empleados.index') }}">Volver a unidades</a>
                <button type="button" class="btn btn-outline-primary" id="selectAllQr">Seleccionar todos</button>
                <button type="submit" class="btn btn-success">Descargar seleccionados</button>
            </div>
        </div>

        <div class="alert alert-info py-2" id="qrDownloadStatus">
            Selecciona los QR que necesites y descargalos en un archivo ZIP.
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center qr-catalog-table">
                <tbody>
                    @forelse($empleados->chunk(3) as $fila)
                        <tr>
                            @foreach($fila as $empleado)
                                <td class="qr-catalog-cell">
                                    <label class="d-block h-100">
                                        <input
                                            type="checkbox"
                                            class="form-check-input qr-check mb-2"
                                            name="unidades[]"
                                            value="{{ $empleado->id }}"
                                            data-clave="{{ $empleado->clave }}"
                                        >
                                        <span class="d-block fw-semibold mb-2">{{ $empleado->clave }}</span>
                                        <img
                                            src="{{ $empleado->qrImagenUrl(180) }}"
                                            alt="QR de {{ $empleado->clave }}"
                                            class="img-fluid border rounded p-2 bg-white"
                                            width="180"
                                        >
                                        <span class="d-block small text-muted mt-2">{{ $empleado->nombre_equipo }}</span>
                                    </label>
                                    <a class="btn btn-sm btn-outline-success mt-2" href="{{ route('empleados.qr.download', $empleado) }}">
                                        Descargar QR
                                    </a>
                                </td>
                            @endforeach

                            @for($i = $fila->count(); $i < 3; $i++)
                                <td class="qr-catalog-cell qr-catalog-cell-empty"></td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No hay QR disponibles porque no hay unidades registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .qr-catalog-table {
            table-layout: fixed;
        }

        .qr-catalog-cell {
            width: 33.333%;
            min-width: 220px;
            vertical-align: top;
        }

        .qr-catalog-cell-empty {
            background: #f8f9fa;
        }

        @media (max-width: 767.98px) {
            .qr-catalog-table,
            .qr-catalog-table tbody,
            .qr-catalog-table tr,
            .qr-catalog-table td {
                display: block;
                width: 100%;
            }

            .qr-catalog-cell-empty {
                display: none !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const qrCatalogForm = document.getElementById('qrCatalogForm');
        const selectAllQr = document.getElementById('selectAllQr');
        const qrDownloadStatus = document.getElementById('qrDownloadStatus');

        const checksQr = () => Array.from(document.querySelectorAll('.qr-check'));
        const checkedQr = () => checksQr().filter((check) => check.checked);

        selectAllQr?.addEventListener('click', () => {
            const checks = checksQr();
            const shouldCheck = checks.some((check) => !check.checked);

            checks.forEach((check) => {
                check.checked = shouldCheck;
            });

            selectAllQr.textContent = shouldCheck ? 'Quitar seleccion' : 'Seleccionar todos';
        });

        qrCatalogForm?.addEventListener('submit', (event) => {
            const selected = checkedQr();

            if (selected.length === 0) {
                event.preventDefault();
                qrDownloadStatus.className = 'alert alert-warning py-2';
                qrDownloadStatus.textContent = 'Selecciona al menos un QR para descargar.';
                return;
            }

            qrDownloadStatus.className = 'alert alert-info py-2';
            qrDownloadStatus.textContent = `Generando ZIP con ${selected.length} QR...`;
        });
    </script>
@endpush