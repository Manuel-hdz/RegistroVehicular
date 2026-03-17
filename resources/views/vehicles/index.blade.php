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
        <a class="btn btn-primary" href="{{ route('vehicles.create') }}">Nueva unidad</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('vehicles.index') }}" class="vehicle-toolbar">
        <div>
            <label>Seleccionar unidad</label>
            <select id="vehicleSelect" class="searchable-select" name="vehicle_id" onchange="this.form.submit()">
                @foreach($vehicles as $vehicleOption)
                    <option value="{{ $vehicleOption->id }}" {{ (int) $selectedVehicleId === (int) $vehicleOption->id ? 'selected' : '' }}>
                        {{ $vehicleOption->identifier ?: 'Sin identificador' }} - {{ $vehicleOption->plate }}
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
                    <img class="vehicle-photo" src="{{ $selectedVehicle->photo_url }}" alt="Foto de {{ $selectedVehicle->identifier ?: $selectedVehicle->plate }}">
                @else
                    <div class="vehicle-hero">
                        <div class="vehicle-hero-text">
                            <strong style="display:block; font-size:1.2rem;">{{ $selectedVehicle->identifier ?: 'Unidad sin identificador' }}</strong>
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

                    @if($selectedVehicle->insurance_policy_url)
                        <a class="vehicle-doc-link" href="{{ $selectedVehicle->insurance_policy_url }}" target="_blank" rel="noopener">
                            <span>Poliza de seguro</span>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    @else
                        <div class="vehicle-doc-link missing">
                            <span>Poliza de seguro</span>
                            <span>No cargada</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="vehicle-details">
                <h3 style="margin-top:0;">{{ $selectedVehicle->identifier ?: 'Ficha de unidad' }}</h3>
                <div class="vehicle-fields">
                    <div class="vehicle-field"><small>Placa</small><strong>{{ $selectedVehicle->plate ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Tipo</small><strong>{{ $typeLabels[$selectedVehicle->vtype] ?? 'Auto' }}</strong></div>
                    <div class="vehicle-field"><small>Modelo</small><strong>{{ $selectedVehicle->model ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Anio</small><strong>{{ $selectedVehicle->year ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Numero de serie</small><strong>{{ $selectedVehicle->serial_number ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Numero de serie adicional</small><strong>{{ $selectedVehicle->additional_serial_number ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Motor</small><strong>{{ $selectedVehicle->engine_number ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Proveedor</small><strong>{{ $selectedVehicle->supplier ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Personal asignado</small><strong>{{ $selectedVehicle->assigned_personnel ?: '-' }}</strong></div>
                    <div class="vehicle-field"><small>Estatus operativo</small><strong>{{ $selectedVehicle->availability === 'available' ? 'Disponible' : 'En mantenimiento' }}</strong></div>
                    <div class="vehicle-field"><small>Ultimo comentario de mantenimiento</small><strong>{{ $selectedVehicle->maintenance_note ?: '-' }}</strong></div>
                </div>

                <div class="vehicle-description">
                    <small style="display:block; color:#6b7280; margin-bottom:6px;">Descripcion</small>
                    <strong>{{ $selectedVehicle->description ?: '-' }}</strong>
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
@endsection

@push('scripts')
<script>
    (function () {
        function makeSearchable(select) {
            if (!select) return;

            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.className = 'searchable-wrapper';

            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Buscar unidad...';
            input.className = 'searchable-input';
            input.autocomplete = 'off';

            const list = document.createElement('ul');
            list.className = 'searchable-list';
            list.style.position = 'absolute';
            list.style.left = '0';
            list.style.right = '0';
            list.style.top = '100%';
            list.style.zIndex = '10';
            list.style.maxHeight = '160px';
            list.style.overflowY = 'auto';
            list.style.margin = '4px 0 0';
            list.style.padding = '0';
            list.style.listStyle = 'none';
            list.style.background = '#fff';
            list.style.border = '1px solid #ccc';
            list.style.boxShadow = '0 2px 6px rgba(0,0,0,.1)';
            list.hidden = true;

            const originalOptions = Array.from(select.options);

            function render(filter) {
                list.innerHTML = '';
                const term = (filter || '').toLowerCase();
                originalOptions.forEach(function (opt) {
                    if (!opt.value) return;
                    const text = opt.textContent;
                    if (term && !text.toLowerCase().includes(term)) return;
                    const li = document.createElement('li');
                    li.textContent = text;
                    li.dataset.value = opt.value;
                    li.style.padding = '6px 8px';
                    li.style.cursor = 'pointer';
                    li.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        select.value = opt.value;
                        input.value = text;
                        list.hidden = true;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    list.appendChild(li);
                });
                list.hidden = list.children.length === 0;
            }

            input.addEventListener('focus', function () {
                input.select();
                render(input.value);
            });

            input.addEventListener('input', function () {
                render(this.value);
            });

            document.addEventListener('click', function (event) {
                if (!wrapper.contains(event.target)) {
                    list.hidden = true;
                }
            });

            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(input);
            wrapper.appendChild(list);
            select.style.display = 'none';

            const selectedOpt = select.selectedOptions[0];
            if (selectedOpt) {
                input.value = selectedOpt.textContent;
            }
        }

        document.querySelectorAll('.searchable-select').forEach(makeSearchable);
    })();
</script>
@endpush
