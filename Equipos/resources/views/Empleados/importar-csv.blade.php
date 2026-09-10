@extends('layouts.app')

@section('title','Importar CSV')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3">
                <h1 class="h5 mb-0">Importar unidades desde CSV</h1>
                <div class="d-grid d-sm-flex gap-2">
                    <a href="{{ route('empleados.importar-csv.plantilla') }}" class="btn btn-outline-primary">Descargar plantilla</a>
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <p class="mb-1">Revisa el archivo seleccionado:</p>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('resultado_csv'))
                @php($resultado = session('resultado_csv'))
                <div class="alert alert-info">
                    <p class="mb-1">Registros creados: {{ $resultado['creados'] }}</p>
                    <p class="mb-1">Registros actualizados: {{ $resultado['actualizados'] }}</p>
                    @if(! empty($resultado['errores']))
                        <p class="mb-1">Registros no cargados:</p>
                        <ul class="mb-0">
                            @foreach($resultado['errores'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <form id="csvImportForm" action="{{ route('empleados.importar-csv.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label for="archivo_csv" class="form-label">Archivo CSV</label>
                        <input
                            type="file"
                            class="form-control @error('archivo_csv') is-invalid @enderror"
                            id="archivo_csv"
                            name="archivo_csv"
                            accept=".csv,text/csv"
                            required
                        >
                        <div class="form-text">Usa la plantilla para conservar los encabezados esperados.</div>
                        @error('archivo_csv')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 d-grid d-sm-flex gap-2">
                    <button type="button" class="btn btn-primary" id="previewCsvButton">Previsualizar registros</button>
                    <a href="{{ route('empleados.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="csvPreviewModal" tabindex="-1" aria-labelledby="csvPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="csvPreviewModalLabel">Registros reconocidos</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="csvPreviewAlert" class="alert alert-warning d-none"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead id="csvPreviewHead"></thead>
                            <tbody id="csvPreviewBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Revisar archivo</button>
                    <button type="button" class="btn btn-primary" id="submitCsvButton">Cargar a base de datos</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const expectedHeaders = [
                'clave',
                'nombre_equipo',
                'fecha_alta',
                'marca_modelo',
                'modelo',
                'numero_serie',
                'numero_serie_eq_adicional',
                'tenencia',
                'tarjeta_circulacion',
                'tipo_motor',
                'tipo_filtro',
                'familia',
                'fecha_fabricacion',
                'estado',
                'proveedor',
                'horometro_odometro',
                'descripcion',
                'refacciones',
            ];
            const previewHeaders = [...expectedHeaders.slice(0, 5), 'placas', ...expectedHeaders.slice(5)];
            const headerAliases = {
                clave: 'clave',
                nombreequipo: 'nombre_equipo',
                nombredeequipo: 'nombre_equipo',
                fechaalta: 'fecha_alta',
                marca: 'marca_modelo',
                marcamodelo: 'marca_modelo',
                modelo: 'modelo',
                placas: 'placas',
                placa: 'placas',
                numeroserie: 'numero_serie',
                numerodeserie: 'numero_serie',
                numeroserieeqadicional: 'numero_serie_eq_adicional',
                numerodeserieeqadicional: 'numero_serie_eq_adicional',
                tenencia: 'tenencia',
                tarjetacirculacion: 'tarjeta_circulacion',
                tarjetadecirculacion: 'tarjeta_circulacion',
                tipomotor: 'tipo_motor',
                tipodemotor: 'tipo_motor',
                tipofiltro: 'tipo_filtro',
                filtrosdemotor: 'tipo_filtro',
                familia: 'familia',
                fechafabricacion: 'fecha_fabricacion',
                fechadefabricacion: 'fecha_fabricacion',
                estado: 'estado',
                proveedor: 'proveedor',
                horometroodometro: 'horometro_odometro',
                descripcion: 'descripcion',
                refacciones: 'refacciones',
            };

            const form = document.getElementById('csvImportForm');
            const input = document.getElementById('archivo_csv');
            const previewButton = document.getElementById('previewCsvButton');
            const submitButton = document.getElementById('submitCsvButton');
            const alertBox = document.getElementById('csvPreviewAlert');
            const head = document.getElementById('csvPreviewHead');
            const body = document.getElementById('csvPreviewBody');
            const modal = new bootstrap.Modal(document.getElementById('csvPreviewModal'));

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const detectDelimiter = (text) => {
                const firstLine = String(text ?? '').replace(/^\uFEFF/, '').split(/\r?\n/)[0] ?? '';
                const delimiters = [',', ';', '\t', '|'];

                return delimiters.reduce((best, delimiter) => {
                    const count = firstLine.split(delimiter).length - 1;
                    return count > best.count ? { delimiter, count } : best;
                }, { delimiter: ',', count: 0 }).delimiter;
            };

            const parseLine = (line, delimiter) => {
                const values = [];
                let value = '';
                let quoted = false;

                for (let i = 0; i < line.length; i++) {
                    const char = line[i];
                    const next = line[i + 1];

                    if (char === '"' && quoted && next === '"') {
                        value += '"';
                        i++;
                    } else if (char === '"') {
                        quoted = ! quoted;
                    } else if (char === delimiter && ! quoted) {
                        values.push(value.trim());
                        value = '';
                    } else {
                        value += char;
                    }
                }

                values.push(value.trim());

                return values;
            };

            const parseCsv = (text) => {
                const delimiter = detectDelimiter(text);

                return text
                    .replace(/^\uFEFF/, '')
                    .split(/\r?\n/)
                    .filter((line) => line.trim() !== '')
                    .map((line) => parseLine(line, delimiter));
            };

            const normalizeHeaderKey = (value) => String(value ?? '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '');

            const resolveHeader = (header) => {
                const key = normalizeHeaderKey(header);

                if (headerAliases[key]) {
                    return headerAliases[key];
                }

                if (key.includes('placa')) {
                    return 'placas';
                }

                if (key === 'marca' || key.includes('marca')) {
                    return 'marca_modelo';
                }

                if (key.includes('marca') && key.includes('modelo')) {
                    return 'marca_modelo';
                }

                if (key === 'modelo' || key.includes('modelo')) {
                    return 'modelo';
                }

                return header;
            };

            const normalizeHeaders = (headers) => headers.map(resolveHeader);

            const fillPreview = (rows) => {
                const headers = normalizeHeaders(rows[0] ?? []);
                const records = rows.slice(1);
                const missing = expectedHeaders.filter((header) => ! headers.includes(header));

                alertBox.classList.toggle('d-none', missing.length === 0);
                alertBox.textContent = missing.length > 0
                    ? `Faltan columnas en el CSV: ${missing.join(', ')}`
                    : '';
                submitButton.disabled = records.length === 0 || missing.length > 0;

                head.innerHTML = `<tr>${previewHeaders.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}</tr>`;
                body.innerHTML = records.map((record) => {
                    const row = Object.fromEntries(headers.map((header, index) => [header, record[index] ?? '']));
                    const cells = previewHeaders.map((header) => `<td>${escapeHtml(row[header] ?? '')}</td>`).join('');
                    return `<tr>${cells}</tr>`;
                }).join('');
            };

            previewButton.addEventListener('click', () => {
                const file = input.files[0];

                if (! file) {
                    input.reportValidity();
                    return;
                }

                const reader = new FileReader();
                reader.onload = () => {
                    fillPreview(parseCsv(String(reader.result ?? '')));
                    modal.show();
                };
                reader.readAsText(file);
            });

            submitButton.addEventListener('click', () => {
                form.submit();
            });
        })();
    </script>
@endpush
