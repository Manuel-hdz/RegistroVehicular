@extends('layouts.app')

@section('content')
@push('head')
<style>
    .vehicle-toolbar {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: end;
    }
    .vehicle-titlebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .vehicle-profile {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 16px;
    }
    .vehicle-side-box {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #f8fafc;
        padding: 12px;
    }
    .vehicle-hero {
        width: 100%;
        min-height: 280px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: linear-gradient(180deg, #ffffff, #eef2f7);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 14px;
        color: #475569;
        text-align: center;
        padding: 20px;
    }
    .vehicle-photo {
        width: 100%;
        min-height: 280px;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: #fff;
        display: block;
    }
    .vehicle-photo-button {
        display: block;
        padding: 0;
        border: none;
        background: transparent;
        width: 100%;
        cursor: zoom-in;
    }
    .vehicle-hero-text {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .vehicle-status {
        margin-top: 10px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 999px;
        display: inline-flex;
        padding: 4px 10px;
        border: 1px solid transparent;
    }
    .vehicle-status.active {
        background: #e8f8ef;
        color: #166534;
        border-color: #22c55e;
    }
    .vehicle-status.inactive {
        background: #feeff0;
        color: #b91c1c;
        border-color: #f87171;
    }
    .vehicle-availability {
        margin-top: 8px;
        display: inline-flex;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 13px;
        font-weight: 700;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #93c5fd;
    }
    .vehicle-doc-list {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }
    .vehicle-doc-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
        padding: 10px 12px;
        color: #1f2937;
        text-decoration: none;
        font-weight: 600;
    }
    .vehicle-doc-link.missing {
        color: #9ca3af;
        background: #f8fafc;
    }
    .vehicle-details {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }
    .vehicle-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
    }
    .vehicle-field {
        border-bottom: 1px solid #eef2f7;
        padding-bottom: 6px;
    }
    .vehicle-field small {
        color: #6b7280;
        display: block;
        font-size: 12px;
        margin-bottom: 2px;
    }
    .vehicle-description {
        margin-top: 14px;
        padding: 12px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        white-space: pre-wrap;
    }
    .empty-fields-box {
        border: 1px dashed #f59e0b;
        background: #fffbeb;
        border-radius: 12px;
        padding: 10px 12px;
        margin-top: 12px;
    }
    .empty-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
    .empty-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: 3px 10px;
        background: #fff;
        border: 1px solid #f59e0b;
        font-size: 12px;
        font-weight: 700;
        color: #92400e;
    }
    .vehicle-actions {
        margin-top: 12px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .vehicle-image-lightbox {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .74);
        backdrop-filter: blur(4px);
        z-index: 2300;
    }
    .vehicle-image-lightbox[aria-hidden="false"] {
        display: flex;
    }
    .vehicle-image-lightbox img {
        max-width: min(100%, 1200px);
        max-height: 86vh;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .35);
        background: #fff;
    }
    .vehicle-image-lightbox button {
        position: absolute;
        top: 18px;
        right: 18px;
        border: none;
        background: rgba(255,255,255,.14);
        color: #fff;
        width: 44px;
        height: 44px;
        border-radius: 999px;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
    }
    @media (max-width: 992px){
        .vehicle-toolbar {
            grid-template-columns: 1fr;
        }
        .vehicle-profile {
            grid-template-columns: 1fr;
        }
        .vehicle-fields {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="card">
    <div class="vehicle-titlebar">
        <div>
            <h2 style="margin:0;">Consulta de unidades</h2>
            <p style="margin:8px 0 0; color:#4b5563;">Revisa la ficha de cada unidad, sus documentos y datos de identificacion.</p>
        </div>
        <div class="row" style="margin:0;">
            <button class="btn btn-secondary" type="button" onclick="document.getElementById('vehicleCsvImport').toggleAttribute('hidden')"><i class="bi bi-file-earmark-spreadsheet"></i> Carga masiva CSV</button>
            <a class="btn btn-primary" href="{{ route('vehicles.create') }}">Nueva unidad</a>
        </div>
    </div>
</div>

<div class="card" id="vehicleCsvImport" {{ ($errors->has('csv_file') || session('import_errors')) ? '' : 'hidden' }}>
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
        <div><h3 style="margin:0;">Carga masiva de vehículos</h3><p style="margin:6px 0 0; color:#4b5563;">Registra unidades nuevas o actualiza las existentes por clave, placa o número de serie. Todos los datos son opcionales.</p></div>
        <a class="btn btn-secondary btn-sm" href="{{ route('vehicles.import.template') }}"><i class="bi bi-download"></i> Descargar plantilla</a>
    </div>
    <form method="POST" action="{{ route('vehicles.import.store') }}" enctype="multipart/form-data" style="margin-top:16px;">
        @csrf
        <div class="row" style="align-items:flex-end; margin:0;">
            <div style="flex:1; min-width:240px;"><label for="vehicleCsvFile">Archivo CSV (máximo 5 MB)</label><input id="vehicleCsvFile" type="file" name="csv_file" accept=".csv,.txt,text/csv" required></div>
            <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Importar vehículos</button>
        </div>
    </form>
    @error('csv_file')<div class="error" style="margin-top:12px;">{{ $message }}</div>@enderror
    @if(session('import_errors'))
        <details style="margin-top:12px;"><summary style="cursor:pointer; font-weight:700;">Ver líneas omitidas ({{ count(session('import_errors')) }})</summary><ul style="margin-bottom:0;">@foreach(session('import_errors') as $importError)<li>{{ $importError }}</li>@endforeach</ul></details>
    @endif
</div>
<div class="card">
    <form method="GET" action="{{ route('vehicles.index') }}" class="vehicle-toolbar">
        <div>
            <label>Seleccionar unidad</label>
            <select id="vehicleSelect" class="searchable-select" name="vehicle_id" data-placeholder="Buscar por clave, nombre, placa, marca, serie, área o asignado..." onchange="this.form.submit()">
                @foreach($vehicles as $vehicleOption)
                    <option value="{{ $vehicleOption->id }}" data-search="{{ implode(' ', array_filter([$vehicleOption->identifier, $vehicleOption->unit_name, $vehicleOption->plate, $vehicleOption->make_model, $vehicleOption->model, $vehicleOption->serial_number, $vehicleOption->additional_serial_number, $vehicleOption->engine_type, $vehicleOption->area, $vehicleOption->family, $vehicleOption->equipment_status, $vehicleOption->supplier, $vehicleOption->assigned_personnel])) }}" {{ (int) $selectedVehicleId === (int) $vehicleOption->id ? 'selected' : '' }}>
                        {{ $vehicleOption->identifier ?: ($vehicleOption->unit_name ?: ($vehicleOption->plate ?: 'Equipo sin clave')) }}{{ $vehicleOption->plate ? ' - '.$vehicleOption->plate : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($selectedVehicle)
            <a class="btn btn-secondary" href="{{ route('vehicles.edit', $selectedVehicle) }}">Editar</a>
        @else
            <button type="button" class="btn btn-secondary" disabled>Editar</button>
        @endif
    </form>
</div>

@if(!$selectedVehicle)
    <div class="error">No hay vehículos registrados para mostrar la ficha.</div>
@else
    <div class="card">
        <div class="vehicle-profile">
            <div class="vehicle-side-box">
                @if($selectedVehicle->photo_url)
                    <button class="vehicle-photo-button" type="button" data-lightbox-open="{{ $selectedVehicle->photo_url }}">
                        <img class="vehicle-photo" src="{{ $selectedVehicle->photo_url }}" alt="Foto de {{ $selectedVehicle->identifier ?: $selectedVehicle->plate }}">
                    </button>
                @else
                    <div class="vehicle-hero">
                        <div class="vehicle-hero-text">
                            <strong style="display:block; font-size:1.2rem;">{{ $selectedVehicle->unit_name ?: ($selectedVehicle->identifier ?: 'Unidad sin nombre') }}</strong>
                            <span style="display:block;">{{ $selectedVehicle->plate }}</span>
                        </div>
                    </div>
                @endif

                <div class="vehicle-status {{ $selectedVehicle->active ? 'active' : 'inactive' }}">
                    {{ $selectedVehicle->active ? 'Activo' : 'Inactivo' }}
                </div>
                <div class="vehicle-availability">
                    {{ $selectedVehicle->availability === 'available' ? 'Disponible' : 'En mantenimiento' }}
                </div>

                <div class="vehicle-doc-list">
                    @if($selectedVehicle->tenure_url)
                        <a class="vehicle-doc-link" href="{{ $selectedVehicle->tenure_url }}" target="_blank" rel="noopener"><span>Tenencia</span><i class="bi bi-box-arrow-up-right"></i></a>
                    @else
                        <div class="vehicle-doc-link missing"><span>Tenencia</span><span>No cargada</span></div>
                    @endif
                    @if($selectedVehicle->circulation_card_url)
                        <a class="vehicle-doc-link" href="{{ $selectedVehicle->circulation_card_url }}" target="_blank" rel="noopener">
                            <span>Tarjeta de circulacion</span>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    @else
                        <div class="vehicle-doc-link missing">
                            <span>Tarjeta de circulacion</span>
                            <span>No cargada</span>
                        </div>
                    @endif


                </div>
            </div>

            <div class="vehicle-details">
                <h3 style="margin-top:0;">{{ $selectedVehicle->unit_name ?: ($selectedVehicle->identifier ?: 'Ficha de unidad') }}</h3>
                <div class="vehicle-fields">
                    <div class="vehicle-field"><small>Clave</small><strong>{{ $selectedVehicle->identifier ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Nombre de unidad</small><strong>{{ $selectedVehicle->unit_name ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Fecha de alta</small><strong>{{ $selectedVehicle->registration_date ? $selectedVehicle->registration_date->format('d/m/Y') : '-' }}</strong></div>
                    <div class="vehicle-field"><small>Marca/modelo</small><strong>{{ $selectedVehicle->make_model ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Modelo</small><strong>{{ $selectedVehicle->model ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Placa</small><strong>{{ $selectedVehicle->plate ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Número de serie</small><strong>{{ $selectedVehicle->serial_number ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Serie eq. adicional</small><strong>{{ $selectedVehicle->additional_serial_number ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Tipo de motor</small><strong>{{ $selectedVehicle->engine_type ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Filtros de motor</small><strong>{{ $selectedVehicle->engine_filters ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Área</small><strong>{{ $selectedVehicle->area ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Familia</small><strong>{{ $selectedVehicle->family ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Fecha de fabricación</small><strong>{{ $selectedVehicle->manufacture_date ? $selectedVehicle->manufacture_date->format('d/m/Y') : '-' }}</strong></div>
                    <div class="vehicle-field"><small>Asignado</small><strong>{{ $selectedVehicle->assigned_personnel ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Estado</small><strong>{{ $selectedVehicle->equipment_status ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Proveedor</small><strong>{{ $selectedVehicle->supplier ?: '-' }}</strong></div>
                </div>



                <div class="empty-fields-box">
                    <strong>Campos vacios detectados</strong>
                    @if(count($emptyFields) > 0)
                        <div class="empty-list">
                            @foreach($emptyFields as $fieldLabel)
                                <span class="empty-pill">{{ $fieldLabel }}</span>
                            @endforeach
                        </div>
                    @else
                        <p style="margin:8px 0 0; color:#166534;">Esta unidad ya tiene capturados sus campos principales.</p>
                    @endif
                </div>

                <div class="vehicle-actions">
                    <a class="btn btn-secondary" href="{{ route('vehicles.edit', $selectedVehicle) }}">Editar ficha</a>
                    @if(auth()->check() && auth()->user()->role === 'superadmin')
                        <form action="{{ route('vehicles.destroy', $selectedVehicle) }}" method="POST" onsubmit="return confirm('Eliminar vehiculo?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-link text-danger btn-sm" type="submit">Eliminar unidad</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<div class="vehicle-image-lightbox" aria-hidden="true" id="vehicleImageLightbox">
    <button type="button" aria-label="Cerrar" id="vehicleImageLightboxClose">&times;</button>
    <img src="" alt="Imagen ampliada del equipo" id="vehicleImageLightboxImage">
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function makeSearchable(select) {
            if (!select) return;

            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.zIndex = '3000';
            wrapper.className = 'searchable-wrapper';

            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = select.dataset.placeholder || 'Buscar unidad...';
            input.className = 'searchable-input';
            input.autocomplete = 'off';

            const list = document.createElement('ul');
            list.className = 'searchable-list';
            list.style.position = 'fixed';
            list.style.zIndex = '3200';
            list.style.maxHeight = '160px';
            document.body.appendChild(list);
            list.style.overflowY = 'auto';
            list.style.margin = '4px 0 0';
            list.style.padding = '0';
            list.style.listStyle = 'none';
            list.style.background = '#fff';
            list.style.border = '1px solid #ccc';
            list.style.boxShadow = '0 2px 6px rgba(0,0,0,.1)';
            list.hidden = true;

            const originalOptions = Array.from(select.options);
            let filteredOptions = [];

            function applyOption(opt) {
                if (!opt) return;
                select.value = opt.value;
                input.value = opt.textContent;
                list.hidden = true;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function render(filter) {
                const rect = input.getBoundingClientRect();
                list.style.left = rect.left + 'px';
                list.style.top = rect.bottom + 'px';
                list.style.width = rect.width + 'px';

                list.innerHTML = '';
                const normalize = value => (value || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
                const term = normalize(filter);
                filteredOptions = originalOptions.filter(function (opt) {
                    if (!opt.value) return;
                    const text = opt.textContent;
                    return !term || text.toLowerCase().includes(term);
                });

                filteredOptions.forEach(function (opt) {
                    const text = opt.textContent;
                    const li = document.createElement('li');
                    li.textContent = text;
                    li.dataset.value = opt.value;
                    li.style.padding = '6px 8px';
                    li.style.cursor = 'pointer';
                    li.style.borderBottom = '1px solid #eef2f7';
                    li.style.background = '#fff';
                    li.addEventListener('mouseenter', function () {
                        li.style.background = '#eef6f1';
                    });
                    li.addEventListener('mouseleave', function () {
                        li.style.background = '#fff';
                    });
                    li.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        applyOption(opt);
                    });
                    list.appendChild(li);
                });
                const lastItem = list.lastElementChild;
                if (lastItem) {
                    lastItem.style.borderBottom = 'none';
                }
                list.hidden = filteredOptions.length === 0;
            }

            input.addEventListener('focus', function () {
                input.select();
                render(input.value);
            });

            input.addEventListener('input', function () {
                render(this.value);
            });

            input.addEventListener('click', function () {
                render(this.value);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                if (filteredOptions.length > 0) {
                    applyOption(filteredOptions[0]);
                }
            });

            document.addEventListener('click', function (event) {
                if (!wrapper.contains(event.target)) {
                    list.hidden = true;
                }
            });

            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(input);
            document.body.appendChild(list);
            select.style.display = 'none';

            const selectedOpt = select.selectedOptions[0];
            if (selectedOpt) {
                input.value = selectedOpt.textContent;
            }
        }

        document.querySelectorAll('.searchable-select').forEach(makeSearchable);
    })();

    (function () {
        const lightbox = document.getElementById('vehicleImageLightbox');
        const lightboxImage = document.getElementById('vehicleImageLightboxImage');
        const closeButton = document.getElementById('vehicleImageLightboxClose');

        function closeLightbox() {
            if (!lightbox || !lightboxImage) return;
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImage.src = '';
        }

        document.querySelectorAll('[data-lightbox-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!lightbox || !lightboxImage) return;
                lightboxImage.src = button.getAttribute('data-lightbox-open') || '';
                lightbox.setAttribute('aria-hidden', 'false');
            });
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeLightbox);
        }

        if (lightbox) {
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeLightbox();
            }
        });
    })();
</script>
@endpush
