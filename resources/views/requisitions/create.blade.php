@extends('layouts.app')

@php
    $oldItems = old('items', [
        ['material_name' => '', 'quantity' => 1, 'equipment_vehicle_id' => '', 'justification' => ''],
    ]);
@endphp

@section('content')
<div class="card" style="background:linear-gradient(135deg, #f5fff9, #ffffff 55%, #eefbf4); border-color:#cfe8d8;">
    <div class="row" style="justify-content: space-between; align-items:flex-start; gap:12px;">
        <div>
            <h2 style="margin:0 0 6px;">Solicitud de materiales y refacciones</h2>
            <p style="margin:0; color:#4b5563;">
                Captura tu solicitud para el area de compras. Puedes agregar varios materiales en un solo envio.
            </p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('requisitions.store') }}" class="grid" id="requisitionForm">
    @csrf
    <div class="card">
        <h3 style="margin-top:0">Datos principales</h3>
        <div class="grid grid-3">
            <div style="grid-column: span 2;">
                <label for="requester_name">Solicitante</label>
                <input id="requester_name" name="requester_name" value="{{ old('requester_name') }}" required>
            </div>
            <div style="grid-column: span 1;">
                <label for="cost_center_id">Centro de costos</label>
                <select id="cost_center_id" name="cost_center_id" required {{ $costCenters->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Selecciona un centro</option>
                    @foreach($costCenters as $costCenter)
                        <option value="{{ $costCenter->id }}" @selected(old('cost_center_id') == $costCenter->id)>
                            {{ $costCenter->code }} - {{ $costCenter->name }}
                        </option>
                    @endforeach
                </select>
                @if($costCenters->isEmpty())
                    <small style="color:#b45309;">No hay centros de costos activos. Pide a administración que registre uno.</small>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div style="margin-bottom:14px;">
            <h3 style="margin:0;">Material solicitado</h3>
            <p style="margin:4px 0 0; color:#6b7280;">Agrega cada material o refaccion por separado.</p>
        </div>

        <datalist id="partSuggestions">
            @foreach($partSuggestions as $partName)
                <option value="{{ $partName }}"></option>
            @endforeach
        </datalist>

        <div id="requisitionItems" class="grid" style="gap:16px;">
            @foreach($oldItems as $index => $item)
                <div class="card requisition-item" data-index="{{ $index }}" style="margin-bottom:0; border-color:#d7e8de; background:#fbfffc;">
                    <div class="row" style="justify-content: space-between; align-items:center; margin-bottom:10px;">
                        <strong>Registro {{ $loop->iteration }}</strong>
                        <button type="button" class="btn btn-secondary btn-remove-item" {{ count($oldItems) === 1 ? 'style=display:none;' : '' }}>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="grid grid-2">
                        <div>
                            <label>Material o refaccion</label>
                            <input name="items[{{ $index }}][material_name]" value="{{ $item['material_name'] ?? '' }}" list="partSuggestions" required>
                        </div>
                        <div>
                            <label>Cantidad</label>
                            <input type="number" step="0.01" min="0.01" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required>
                        </div>
                        <div>
                            <label>Equipo destino</label>
                            <select name="items[{{ $index }}][equipment_vehicle_id]">
                                <option value="">Sin equipo especifico</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected(($item['equipment_vehicle_id'] ?? '') == $vehicle->id)>
                                        {{ $vehicle->identifier ?: 'Unidad '.$vehicle->id }}{{ $vehicle->plate ? ' / '.$vehicle->plate : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Justificacion</label>
                            <textarea name="items[{{ $index }}][justification]" maxlength="255" placeholder="Explica brevemente por que se solicita">{{ $item['justification'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                @if($loop->first)
                    <div class="row" id="addItemRow" style="justify-content:flex-start;">
                        <button type="button" class="btn btn-secondary" id="addItemButton">
                            <i class="bi bi-plus-circle me-1"></i>Agregar material
                        </button>
                    </div>
                @endif
            @endforeach
        </div>

        <template id="requisitionItemTemplate">
            <div class="card requisition-item" data-index="__INDEX__" style="margin-bottom:0; border-color:#d7e8de; background:#fbfffc;">
                <div class="row" style="justify-content: space-between; align-items:center; margin-bottom:10px;">
                    <strong>Registro __NUMBER__</strong>
                    <button type="button" class="btn btn-secondary btn-remove-item">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="grid grid-2">
                    <div>
                        <label>Material o refaccion</label>
                        <input name="items[__INDEX__][material_name]" value="" list="partSuggestions" required>
                    </div>
                    <div>
                        <label>Cantidad</label>
                        <input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required>
                    </div>
                    <div>
                        <label>Equipo destino</label>
                        <select name="items[__INDEX__][equipment_vehicle_id]">
                            <option value="">Sin equipo especifico</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->identifier ?: 'Unidad '.$vehicle->id }}{{ $vehicle->plate ? ' / '.$vehicle->plate : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Justificacion</label>
                        <textarea name="items[__INDEX__][justification]" maxlength="255" placeholder="Explica brevemente por que se solicita"></textarea>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="card actions-stick">
        <div class="row" style="justify-content: space-between; align-items:center;">
            <small style="color:#4b5563;">La solicitud se guardara como pendiente para revision del area de compras.</small>
            <button type="submit" class="btn btn-primary" {{ $costCenters->isEmpty() ? 'disabled' : '' }}>Guardar solicitud</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    <script>
        (function () {
            var list = document.getElementById('requisitionItems');
            var template = document.getElementById('requisitionItemTemplate');
            var addButton = document.getElementById('addItemButton');
            var addButtonRow = document.getElementById('addItemRow');

            if (!list || !template || !addButton || !addButtonRow) {
                return;
            }

            function refreshRows() {
                var items = list.querySelectorAll('.requisition-item');

                items.forEach(function (item, index) {
                    var title = item.querySelector('strong');
                    var removeButton = item.querySelector('.btn-remove-item');

                    item.dataset.index = index;
                    if (title) {
                        title.textContent = 'Registro ' + (index + 1);
                    }

                    item.querySelectorAll('input, select, textarea').forEach(function (field) {
                        if (!field.name) {
                            return;
                        }

                        field.name = field.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                    });

                    if (removeButton) {
                        removeButton.style.display = items.length === 1 ? 'none' : '';
                    }
                });

                if (items.length > 0) {
                    items[0].insertAdjacentElement('afterend', addButtonRow);
                }
            }

            addButton.addEventListener('click', function () {
                var index = list.querySelectorAll('.requisition-item').length;
                var html = template.innerHTML
                    .replaceAll('__INDEX__', index)
                    .replaceAll('__NUMBER__', index + 1);
                list.insertAdjacentHTML('beforeend', html);
                refreshRows();
            });

            list.addEventListener('click', function (event) {
                var removeButton = event.target.closest('.btn-remove-item');
                if (!removeButton) {
                    return;
                }

                var item = removeButton.closest('.requisition-item');
                if (!item) {
                    return;
                }

                item.remove();
                refreshRows();
            });

            refreshRows();
        })();
    </script>
@endpush
