@extends('layouts.app')

@php
    $badgeStyles = [
        'pending' => 'background:#fff4cc; color:#8a6500;',
        'reviewing' => 'background:#dbeafe; color:#1d4ed8;',
        'approved' => 'background:#dcfce7; color:#166534;',
        'purchased' => 'background:#ede9fe; color:#6d28d9;',
        'delivered' => 'background:#dbeafe; color:#1d4ed8;',
        'cancelled' => 'background:#fee2e2; color:#b91c1c;',
        'rejected' => 'background:#fee2e2; color:#b91c1c;',
    ];
@endphp

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between; align-items:center; gap:12px;">
        <div>
            <h2 style="margin:0;">Pendientes</h2>
            <p style="margin:6px 0 0; color:#6b7280;">Solicitudes registradas desde el formulario publico de requisiciones.</p>
        </div>
        <div class="row" style="gap:10px;">
            <a href="{{ route('requisitions.create') }}" class="btn btn-secondary">Abrir formulario</a>
            <form method="GET" action="{{ route('requisitions.pending') }}">
                <select name="status" onchange="this.form.submit()" style="min-width:190px;">
                    <option value="">Todos los estatus</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

@forelse($requisitions as $requisition)
    <div class="card">
        <div class="row" style="justify-content: space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <h3 style="margin:0;">{{ $requisition->folio }}</h3>
                    <span style="padding:6px 10px; border-radius:999px; font-weight:700; {{ $badgeStyles[$requisition->status] ?? 'background:#e5e7eb; color:#374151;' }}">
                        {{ $requisition->status_label }}
                    </span>
                </div>
                <p style="margin:8px 0 0; color:#4b5563;">
                    Solicitante: <strong>{{ $requisition->requester_name }}</strong>
                    · Centro de costos: <strong>{{ $requisition->costCenter?->code }} - {{ $requisition->costCenter?->name }}</strong>
                    · Registrada: {{ optional($requisition->created_at)->format('d/m/Y H:i') }}
                </p>
            </div>
            <div style="padding:10px 14px; border-radius:12px; background:#f7faf8; border:1px solid #dbe7df; text-align:center; min-width:240px;">
                <div style="font-size:28px; font-weight:800; color:#0f5132; line-height:1;">{{ $requisition->items->count() }}</div>
                <div style="color:#6b7280;">materiales solicitados</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Equipo destino</th>
                        <th>Justificacion</th>
                        <th>Encargado</th>
                        <th>En almacen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requisition->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->material_name }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>
                                {{ $item->equipmentVehicle?->identifier ?: 'Sin equipo especifico' }}
                                {{ $item->equipmentVehicle?->plate ? ' / '.$item->equipmentVehicle->plate : '' }}
                            </td>
                            <td>{{ $item->justification ?: 'Sin justificacion' }}</td>
                            <td>
                                <form method="POST" action="{{ route('requisitions.items.checks', $item) }}" class="item-check-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status_context" value="{{ $selectedStatus }}">
                                    <input type="hidden" name="field" value="is_ordered">
                                    <input type="hidden" name="value" value="{{ $item->is_ordered ? 0 : 1 }}">
                                    <label style="display:inline-flex; align-items:center; gap:8px; margin:0; font-size:.9rem; text-transform:none; letter-spacing:0; color:#203129; font-weight:700;">
                                        <input
                                            type="checkbox"
                                            class="item-check-toggle"
                                            data-confirm-check="Ya fue encargado este material?"
                                            {{ $item->is_ordered ? 'checked' : '' }}
                                        >
                                        <span>{{ $item->is_ordered ? 'Si' : 'No' }}</span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('requisitions.items.checks', $item) }}" class="item-check-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status_context" value="{{ $selectedStatus }}">
                                    <input type="hidden" name="field" value="is_in_storage">
                                    <input type="hidden" name="value" value="{{ $item->is_in_storage ? 0 : 1 }}">
                                    <label style="display:inline-flex; align-items:center; gap:8px; margin:0; font-size:.9rem; text-transform:none; letter-spacing:0; color:#203129; font-weight:700;">
                                        <input
                                            type="checkbox"
                                            class="item-check-toggle"
                                            data-confirm-check="Este material ya se encuentra en almacen?"
                                            {{ $item->is_in_storage ? 'checked' : '' }}
                                        >
                                        <span>{{ $item->is_in_storage ? 'Si' : 'No' }}</span>
                                    </label>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">
            @if($requisition->isFinalStatus())
                <div
                    style="padding:14px 16px; border-radius:18px; font-weight:800; text-align:center; {{ $requisition->status === 'delivered' ? 'background:#dbeafe; color:#1d4ed8;' : 'background:#fee2e2; color:#b91c1c;' }}"
                >
                    {{ $requisition->status_label }}
                </div>
            @else
                <form method="POST" action="{{ route('requisitions.status', $requisition) }}" class="requisition-status-form" data-requisition-status-form>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_context" value="{{ $selectedStatus }}">
                    <div class="grid grid-2" style="align-items:end; gap:12px;">
                        <div>
                            <label style="margin-bottom:8px;">Estatus general</label>
                            <select name="status" style="width:100%;">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected($requisition->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="visibility:hidden; margin-bottom:8px;">Guardar</label>
                            <button type="submit" class="btn btn-primary" style="width:100%;">Guardar estatus</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="card">
        <h3 style="margin-top:0;">Sin registros</h3>
        <p style="margin-bottom:0; color:#6b7280;">No hay solicitudes para el filtro seleccionado.</p>
    </div>
@endforelse

<div class="card" style="padding-top:12px; padding-bottom:12px;">
    {{ $requisitions->links() }}
</div>

<div class="backdrop" id="requisitionStatusBackdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="requisitionStatusTitle">
    <div class="modal" role="document">
        <header>
            <strong id="requisitionStatusTitle">Confirmar cambio</strong>
            <button class="close-x" type="button" aria-label="Cerrar" id="btnCloseRequisitionStatus">×</button>
        </header>
        <div class="content" id="requisitionStatusMessage">
            Confirma el cambio de estatus.
        </div>
        <div class="actions">
            <button class="btn btn-secondary" type="button" id="btnCancelRequisitionStatus">Cancelar</button>
            <button class="btn btn-primary" type="button" id="btnConfirmRequisitionStatus">Confirmar</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('.item-check-toggle').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var form = checkbox.closest('.item-check-form');
                if (!form) {
                    return;
                }

                if (checkbox.checked) {
                    var message = checkbox.dataset.confirmCheck || 'Confirmar accion?';
                    if (!window.confirm(message)) {
                        checkbox.checked = false;
                        return;
                    }
                }

                form.submit();
            });
        });
    })();

    (function () {
        var backdrop = document.getElementById('requisitionStatusBackdrop');
        var message = document.getElementById('requisitionStatusMessage');
        var closeButton = document.getElementById('btnCloseRequisitionStatus');
        var cancelButton = document.getElementById('btnCancelRequisitionStatus');
        var confirmButton = document.getElementById('btnConfirmRequisitionStatus');
        var activeForm = null;

        if (!backdrop || !message || !confirmButton) {
            return;
        }

        function closeModal() {
            backdrop.setAttribute('aria-hidden', 'true');
            activeForm = null;
        }

        document.querySelectorAll('[data-requisition-status-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var statusField = form.querySelector('select[name="status"]');
                if (!statusField) {
                    return;
                }

                if (statusField.value !== 'delivered' && statusField.value !== 'cancelled') {
                    return;
                }

                event.preventDefault();
                activeForm = form;
                message.textContent = statusField.value === 'delivered'
                    ? 'Esta requisicion sera marcada como entregada. Deseas continuar?'
                    : 'Esta requisicion sera marcada como cancelada. Deseas continuar?';
                backdrop.setAttribute('aria-hidden', 'false');
            });
        });

        confirmButton.addEventListener('click', function () {
            if (!activeForm) {
                closeModal();
                return;
            }

            var formToSubmit = activeForm;
            closeModal();
            HTMLFormElement.prototype.submit.call(formToSubmit);
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', closeModal);
        }

        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && backdrop.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
        });
    })();
</script>
@endpush
