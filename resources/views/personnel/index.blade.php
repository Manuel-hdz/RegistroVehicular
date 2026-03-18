@extends('layouts.app')

@section('content')
@push('head')
<style>
    .personnel-toolbar {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: end;
    }
    .personnel-titlebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .personnel-profile {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 16px;
    }
    .personnel-photo-box {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #f8fafc;
        padding: 12px;
    }
    .personnel-photo {
        width: 100%;
        aspect-ratio: 3 / 4;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: #fff;
    }
    .personnel-photo-button {
        display: block;
        width: 100%;
        padding: 0;
        border: none;
        background: transparent;
        cursor: zoom-in;
    }
    .personnel-photo-placeholder {
        width: 100%;
        aspect-ratio: 3 / 4;
        border-radius: 10px;
        border: 1px dashed #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        background: #fff;
        font-size: 44px;
    }
    .personnel-status {
        font-size: 13px;
        font-weight: 700;
        border-radius: 999px;
        display: inline-flex;
        padding: 4px 10px;
        border: 1px solid transparent;
        align-items: center;
        min-height: 32px;
    }
    .personnel-status.active {
        background: #e8f8ef;
        color: #166534;
        border-color: #22c55e;
    }
    .personnel-status.inactive {
        background: #feeff0;
        color: #b91c1c;
        border-color: #f87171;
    }
    .personnel-status-row {
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .personnel-cardex-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
    }
    .personnel-meta-stack {
        display: grid;
        gap: 12px;
        margin-top: 14px;
    }
    .personnel-metric-card {
        border: 1px solid #d7e5db;
        border-radius: 14px;
        background: linear-gradient(180deg, #f7fbf8, #edf7f0);
        padding: 12px;
    }
    .personnel-metric-card small {
        display: block;
        margin-bottom: 4px;
        color: #567164;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
    }
    .personnel-metric-card strong {
        color: #16362a;
        font-size: 15px;
    }
    .personnel-vacation-badge {
        width: 88px;
        height: 88px;
        aspect-ratio: 1;
        margin: 0 auto;
        border-radius: 999px;
        background: radial-gradient(circle at top, #56d66f, #149647 68%, #0d6b34);
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 8px;
        box-shadow: 0 16px 28px rgba(20, 150, 71, .22);
    }
    .personnel-vacation-badge strong {
        font-size: 30px;
        line-height: 1;
        color: #fff;
    }
    .personnel-vacation-caption {
        margin-top: 8px;
        text-align: center;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #486556;
    }
    .personnel-details {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }
    .personnel-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
    }
    .personnel-field {
        border-bottom: 1px solid #eef2f7;
        padding-bottom: 6px;
    }
    .personnel-field small {
        color: #6b7280;
        display: block;
        font-size: 12px;
        margin-bottom: 2px;
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
    .discreet-action {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
    }
    .personnel-image-lightbox {
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
    .personnel-image-lightbox[aria-hidden="false"] {
        display: flex;
    }
    .personnel-image-lightbox img {
        max-width: min(100%, 1000px);
        max-height: 86vh;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .35);
        background: #fff;
    }
    .personnel-image-lightbox button {
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
        .personnel-toolbar {
            grid-template-columns: 1fr;
        }
        .personnel-profile {
            grid-template-columns: 1fr;
        }
        .personnel-fields {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="card">
    <div class="personnel-titlebar">
        <div>
            <h2 style="margin:0;">Perfiles de personal</h2>
            <p style="margin:8px 0 0; color:#4b5563;">Consulta por perfil y administra altas, bajas y reactivaciones.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('personnel.create') }}">Nuevo Colaborador</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('personnel.index') }}" class="personnel-toolbar">
        <div>
            <label>Seleccionar personal</label>
            <select id="personnelSelect" class="searchable-select" name="personnel_id" onchange="this.form.submit()">
                @foreach($personnelList as $person)
                    <option value="{{ $person->id }}" {{ (int) $selectedPersonnelId === (int) $person->id ? 'selected' : '' }}>
                        {{ $person->employee_number }} - {{ $person->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($selectedPersonnel)
            <a class="btn btn-secondary" href="{{ route('personnel.edit', $selectedPersonnel) }}">Editar</a>
        @else
            <button type="button" class="btn btn-secondary" disabled>Editar</button>
        @endif
    </form>
</div>

@if(!$selectedPersonnel)
    <div class="error">No hay personal registrado para mostrar el perfil.</div>
@else
    <div class="card">
        <div class="personnel-profile">
            <div class="personnel-photo-box">
                @if($selectedPersonnel->photo_url)
                    <button class="personnel-photo-button" type="button" data-personnel-lightbox-open="{{ $selectedPersonnel->photo_url }}">
                        <img class="personnel-photo" src="{{ $selectedPersonnel->photo_url }}" alt="Foto de {{ $selectedPersonnel->full_name }}">
                    </button>
                @else
                    <div class="personnel-photo-placeholder">
                        <i class="bi bi-person-badge"></i>
                    </div>
                @endif

                <div class="personnel-status-row">
                    <div class="personnel-status {{ $selectedPersonnel->active ? 'active' : 'inactive' }}">
                        {{ $selectedPersonnel->active ? 'Activo' : 'Baja' }}
                    </div>
                    <a class="btn btn-outline-primary btn-sm personnel-cardex-link" href="{{ route('cardex.index', ['personnel_id' => $selectedPersonnel->id, 'quick_code' => 'A']) }}">
                        Consultar Kardex
                    </a>
                </div>

                @if($selectedPersonnel->terminated_at)
                    <div style="margin-top:6px; font-size:13px; color:#9a3412;">
                        Fecha de baja: <strong>{{ $selectedPersonnel->terminated_at->format('d/m/Y') }}</strong>
                    </div>
                @endif

                <div class="personnel-meta-stack">
                    <div class="personnel-metric-card">
                        <small>Antiguedad</small>
                        <strong>{{ $selectedPersonnel->seniority_label }}</strong>
                    </div>
                    <div class="personnel-vacation-badge">
                        <strong>{{ (int) ($selectedPersonnel->pending_vacation_days ?? 0) }}</strong>
                    </div>
                    <div class="personnel-vacation-caption">Dias de vacaciones pendientes</div>
                </div>
            </div>

            <div class="personnel-details">
                <h3 style="margin-top:0;">{{ $selectedPersonnel->full_name }}</h3>
                <div class="personnel-fields">
                    <div class="personnel-field"><small>No. empleado</small><strong>{{ $selectedPersonnel->employee_number }}</strong></div>
                    <div class="personnel-field"><small>Puesto</small><strong>{{ $selectedPersonnel->position ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Departamento</small><strong>{{ $selectedPersonnel->department ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Fecha de ingreso</small><strong>{{ $selectedPersonnel->hire_date ? $selectedPersonnel->hire_date->format('d/m/Y') : '-' }}</strong></div>
                    <div class="personnel-field"><small>Estado civil</small><strong>{{ $selectedPersonnel->marital_status ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Sexo</small><strong>{{ $selectedPersonnel->sex ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Fecha de nacimiento</small><strong>{{ $selectedPersonnel->birth_date ? $selectedPersonnel->birth_date->format('d/m/Y') : '-' }}</strong></div>
                    <div class="personnel-field"><small>CURP</small><strong>{{ $selectedPersonnel->curp ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>RFC</small><strong>{{ $selectedPersonnel->rfc ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>NSS</small><strong>{{ $selectedPersonnel->nss ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Numero de cuenta</small><strong>{{ $selectedPersonnel->account_number ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Tipo de cuenta</small><strong>{{ $selectedPersonnel->account_type ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Telefono</small><strong>{{ $selectedPersonnel->phone ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Correo</small><strong>{{ $selectedPersonnel->email ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Domicilio</small><strong>{{ $selectedPersonnel->address ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Contacto emergencia</small><strong>{{ $selectedPersonnel->emergency_contact_name ?: '-' }}</strong></div>
                    <div class="personnel-field"><small>Telefono emergencia</small><strong>{{ $selectedPersonnel->emergency_contact_phone ?: '-' }}</strong></div>
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
                        <p style="margin:8px 0 0; color:#166534;">Este perfil no tiene campos vacios relevantes.</p>
                    @endif
                </div>

                <div class="discreet-action">
                    @if($selectedPersonnel->active)
                        <form method="POST" action="{{ route('personnel.deactivate', $selectedPersonnel) }}" onsubmit="return confirm('Dar de baja a este personal?');">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-link text-danger btn-sm" type="submit">Dar de baja</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('personnel.reactivate', $selectedPersonnel) }}" onsubmit="return confirm('Reactivar a este personal?');">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-link text-success btn-sm" type="submit">Reactivar</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<div class="personnel-image-lightbox" aria-hidden="true" id="personnelImageLightbox">
    <button type="button" aria-label="Cerrar" id="personnelImageLightboxClose">&times;</button>
    <img src="" alt="Fotografia ampliada del personal" id="personnelImageLightboxImage">
</div>
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
            input.placeholder = 'Buscar...';
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
            let filteredOptions = [];

            function applyOption(opt) {
                if (!opt) return;
                select.value = opt.value;
                input.value = opt.textContent;
                list.hidden = true;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function render(filter) {
                list.innerHTML = '';
                const term = (filter || '').toLowerCase();
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
            wrapper.appendChild(list);
            select.style.display = 'none';

            const selectedOpt = select.selectedOptions[0];
            if (selectedOpt) {
                input.value = selectedOpt.textContent;
            }
        }

        document.querySelectorAll('.searchable-select').forEach(makeSearchable);
    })();

    (function () {
        const lightbox = document.getElementById('personnelImageLightbox');
        const lightboxImage = document.getElementById('personnelImageLightboxImage');
        const closeButton = document.getElementById('personnelImageLightboxClose');

        function closeLightbox() {
            if (!lightbox || !lightboxImage) return;
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImage.src = '';
        }

        document.querySelectorAll('[data-personnel-lightbox-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!lightbox || !lightboxImage) return;
                lightboxImage.src = button.getAttribute('data-personnel-lightbox-open') || '';
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
