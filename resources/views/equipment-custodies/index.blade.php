@extends('layouts.app')

@push('head')
<style>
    .equipment-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
        width: 100%;
    }
    .equipment-actions-header,
    .equipment-actions-cell {
        position: sticky;
        right: 0;
        z-index: 2;
        text-align: right;
        min-width: 210px;
    }
    .equipment-actions-header { background: #f6faf7; }
    .equipment-actions-cell { background: #ffffff; }
    tbody tr:nth-child(odd) .equipment-actions-cell { background: #fcfefd; }
    tbody tr:hover .equipment-actions-cell { background: #f3fbf6; }
    .equipment-actions-group {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .45rem;
        flex-wrap: wrap;
    }
    @media (max-width: 768px) {
        .equipment-toolbar {
            justify-content: stretch;
        }
        .equipment-toolbar .btn {
            flex: 1;
        }
    }
</style>
@endpush

@php($cancelTarget = null)
@foreach($equipmentTypes as $typeKey => $typeLabel)
    @php($targetRecord = ($recordsByType[$typeKey] ?? collect())->first(fn ($item) => !$item->cancelled_at))
    @if($targetRecord)
        @php($cancelTarget = $targetRecord)
        @break
    @endif
@endforeach

@section('content')
<div class="card">
    <h2 style="margin:0;">Resguardos de Equipos</h2>
    <p style="margin:.6rem 0 0;">
        Aqui puedes registrar y consultar resguardos de laptops, celulares, lamparas y EPP.
        Al registrar un correo, se envia una notificacion automatica cuando se asigna el resguardo.
    </p>
</div>

@foreach($equipmentTypes as $type => $label)
    @php($typeRecords = $recordsByType[$type] ?? collect())
    @php($oldTypeSelected = old('equipment_type') === $type)
    <div class="card">
        <div class="row" style="justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px;">
            <div>
                <h3 style="margin:0;">Listado de {{ $label }}</h3>
                <small>Total registrados: {{ $typeRecords->count() }}</small>
            </div>
            <div class="equipment-toolbar">
                <a class="btn btn-secondary" href="{{ route('equipment-custodies.export.excel', ['type' => $type]) }}">
                    <i class="bi bi-file-earmark-excel"></i>Exportar Excel
                </a>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="collapse"
                    data-bs-target="#form-{{ $type }}"
                    aria-expanded="{{ $oldTypeSelected ? 'true' : 'false' }}"
                    aria-controls="form-{{ $type }}"
                >
                    Nuevo resguardo
                </button>
            </div>
        </div>

        <div class="collapse {{ $oldTypeSelected ? 'show' : '' }}" id="form-{{ $type }}">
            <form method="POST" action="{{ route('equipment-custodies.store') }}" class="grid grid-3" style="margin-bottom:16px;" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="equipment_type" value="{{ $type }}">

                <div>
                    <label for="brand-{{ $type }}">Marca</label>
                    <input id="brand-{{ $type }}" name="brand" value="{{ $oldTypeSelected ? old('brand') : '' }}" required>
                </div>
                <div>
                    <label for="model-{{ $type }}">Modelo</label>
                    <input id="model-{{ $type }}" name="model" value="{{ $oldTypeSelected ? old('model') : '' }}" required>
                </div>
                <div>
                    <label for="serial-{{ $type }}">Numero de serie</label>
                    <input id="serial-{{ $type }}" name="serial_number" value="{{ $oldTypeSelected ? old('serial_number') : '' }}" required>
                </div>
                <div>
                    <label for="date-{{ $type }}">Fecha</label>
                    <input id="date-{{ $type }}" type="date" name="assigned_at" value="{{ $oldTypeSelected ? old('assigned_at') : '' }}" required>
                </div>
                <div>
                    <label for="responsible-{{ $type }}">Responsable</label>
                    <input id="responsible-{{ $type }}" name="responsible" value="{{ $oldTypeSelected ? old('responsible') : '' }}" required>
                </div>
                <div>
                    <label>Encargado de sistemas que lo registro</label>
                    <input value="{{ $registeredByDefault }}" readonly>
                </div>
                <div style="grid-column: 1/-1;">
                    <label for="accessories-{{ $type }}">Accesorios</label>
                    <textarea id="accessories-{{ $type }}" name="accessories" placeholder="Ejemplo: cargador, mochila, mouse...">{{ $oldTypeSelected ? old('accessories') : '' }}</textarea>
                </div>
                <div>
                    <label for="physical_record-{{ $type }}">Resguardo fisico (PDF o imagen)</label>
                    <input id="physical_record-{{ $type }}" type="file" name="physical_record" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div style="grid-column: span 2;">
                    <label for="email-{{ $type }}">Correo para notificacion</label>
                    <input
                        id="email-{{ $type }}"
                        type="email"
                        name="notification_email"
                        value="{{ $oldTypeSelected ? old('notification_email') : '' }}"
                        placeholder="correo@empresa.com"
                    >
                    <small>Si capturas un correo, el sistema enviara automaticamente el aviso de asignacion.</small>
                </div>
                <div style="grid-column: 1/-1;" class="row">
                    <button class="btn btn-primary" type="submit">Registrar resguardo</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Numero de serie</th>
                        <th>Accesorios</th>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th>Registro sistemas</th>
                        <th>Correo notificacion</th>
                        <th class="equipment-actions-header">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($typeRecords as $record)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $record->brand }}</td>
                            <td>{{ $record->model }}</td>
                            <td>{{ $record->serial_number }}</td>
                            <td>{{ $record->accessories ?: '-' }}</td>
                            <td>{{ optional($record->assigned_at)->format('d/m/Y') }}</td>
                            <td>{{ $record->responsible }}</td>
                            <td>{{ $record->registered_by_username }}</td>
                            <td>{{ $record->notification_email ?: '-' }}</td>
                            <td class="equipment-actions-cell">
                                <div class="equipment-actions-group">
                                    @if($record->physical_record_path)
                                        <a
                                            class="btn btn-secondary btn-sm btn-icon"
                                            href="{{ route('equipment-custodies.physical-record', $record) }}"
                                            target="_blank"
                                            title="Ver resguardo fisico"
                                            aria-label="Ver resguardo fisico"
                                        >
                                            <i class="bi bi-paperclip"></i>
                                        </a>
                                    @else
                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm btn-icon"
                                            title="Sin resguardo fisico"
                                            aria-label="Sin resguardo fisico"
                                            disabled
                                        >
                                            <i class="bi bi-paperclip"></i>
                                        </button>
                                    @endif

                                @if(!$record->cancelled_at)
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        data-cancel-trigger
                                        data-cancel-url="{{ route('equipment-custodies.cancel', $record) }}"
                                        data-cancel-brand="{{ $record->brand }}"
                                        data-cancel-model="{{ $record->model }}"
                                        data-cancel-serial="{{ $record->serial_number }}"
                                    >
                                        Cancelar
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                        Cancelado
                                    </button>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">No hay resguardos de {{ strtolower($label) }} registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

@if($cancelTarget)
    <div class="backdrop" id="cancelCustodyBackdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="cancelCustodyTitle">
        <div class="modal" role="document">
            <header>
                <strong id="cancelCustodyTitle">Confirmar cancelacion</strong>
                <button class="close-x" type="button" aria-label="Cerrar" id="btnCloseCancelCustody">×</button>
            </header>
            <div class="content">
                <p style="margin:0;">
                    Se cancelara el resguardo de <strong id="cancelCustodyEquipment">{{ $cancelTarget->brand }} {{ $cancelTarget->model }}</strong>
                    con serie <strong id="cancelCustodySerial">{{ $cancelTarget->serial_number }}</strong>.
                    Este movimiento guardara automaticamente el usuario que confirma y la fecha.
                </p>
            </div>
            <div class="actions">
                <button class="btn btn-secondary" type="button" id="btnCancelCustodyNo">No, volver</button>
                <form method="POST" action="{{ route('equipment-custodies.cancel', $cancelTarget) }}" id="cancelCustodyForm">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-primary" type="submit">Si, cancelar resguardo</button>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        const backdrop = document.getElementById('cancelCustodyBackdrop');
        const closeBtn = document.getElementById('btnCloseCancelCustody');
        const noBtn = document.getElementById('btnCancelCustodyNo');
        const equipment = document.getElementById('cancelCustodyEquipment');
        const serial = document.getElementById('cancelCustodySerial');
        const form = document.getElementById('cancelCustodyForm');
        const triggerButtons = document.querySelectorAll('[data-cancel-trigger]');

        if (!backdrop || !form || triggerButtons.length === 0) {
            return;
        }

        function open() {
            backdrop.setAttribute('aria-hidden', 'false');
        }

        function close() {
            backdrop.setAttribute('aria-hidden', 'true');
        }

        triggerButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const brand = button.getAttribute('data-cancel-brand') || '';
                const model = button.getAttribute('data-cancel-model') || '';
                const serialValue = button.getAttribute('data-cancel-serial') || '';
                const action = button.getAttribute('data-cancel-url') || '';

                if (equipment) equipment.textContent = (brand + ' ' + model).trim();
                if (serial) serial.textContent = serialValue;
                if (action) form.setAttribute('action', action);

                open();
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', close);
        if (noBtn) noBtn.addEventListener('click', close);
        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                close();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });
    })();
</script>
@endpush
