@extends('layouts.app')

@section('content')
@include('maintenance.partials.tabs')
@php
    $systems = \App\Models\MaintenanceBacklogItem::SYSTEMS;
    $criticalities = \App\Models\MaintenanceBacklogItem::CRITICALITIES;
    $statuses = \App\Models\MaintenanceBacklogItem::STATUSES;
@endphp
<div class="card mb-3">
    <h2>BackLog</h2>
    <p class="text-muted mb-0">Piezas requeridas, compradas y utilizadas por equipo y centro de costos.</p>
</div>
@if($costCenters->isEmpty())
    <div class="alert alert-info">No tienes centros de costos activos asignados. Solicita su asignación en Configuración de usuarios para consultar y registrar piezas.</div>
@else
    <form method="GET" class="card mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="cost-center" class="form-label">Centro de costos</label>
                <select id="cost-center" name="cost_center_id" class="form-select">
                    @foreach($costCenters as $center)
                        <option value="{{ $center->id }}" @selected($center->id === $selectedCostCenter->id)>{{ $center->code }} · {{ $center->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary" type="submit">Consultar</button></div>
        </div>
    </form>
    <div class="card mb-3">
        <h3>Equipos</h3>
        <div class="table-responsive" style="max-height:360px; overflow:auto;">
            <table class="table table-hover align-middle">
                <thead><tr><th>Equipo</th><th>Centro de costos</th><th>Placa</th><th>Disponibilidad</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse($vehicles as $vehicle)
                    <tr class="{{ $selectedVehicle?->id === $vehicle->id ? 'table-success' : '' }}">
                        <td>{{ $vehicle->identifier ?: $vehicle->make_model ?: 'Equipo '.$vehicle->id }}</td>
                        <td>{{ $selectedCostCenter->name }}</td>
                        <td>{{ $vehicle->plate ?: '—' }}</td>
                        <td>{{ $vehicle->availability === 'available' ? 'Disponible' : 'En mantenimiento / no disponible' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('maintenance.backlog.index', ['cost_center_id' => $selectedCostCenter->id, 'vehicle_id' => $vehicle->id]) }}">Seleccionar equipo</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No hay equipos activos asignados a este centro de costos. Asigna el centro al editar el equipo en Consulta de unidades.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($selectedVehicle)
        <div class="card mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div><h3 class="mb-1">{{ $selectedVehicle->identifier ?: $selectedVehicle->plate }} · Piezas asignadas</h3>
                <small>{{ $selectedCostCenter->name }} · {{ $items->total() }} registros</small></div>
                <button type="button" class="btn btn-primary" id="add-backlog">Dar de alta pieza</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Equipo</th><th>Descripción</th><th>Cantidad</th><th>Unidad de medida</th><th>Costo unitario</th><th>Costo total</th><th>Sistema</th><th>Criticidad</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $selectedVehicle->identifier ?: $selectedVehicle->plate }}</td>
                            <td style="min-width:200px; white-space:normal;">{{ $item->description }}</td>
                            <td>{{ number_format((float)$item->quantity, 2) }}</td><td>{{ $item->unit }}</td>
                            <td>${{ number_format((float)$item->unit_cost, 2) }}</td><td>${{ number_format((float)$item->total_cost, 2) }}</td>
                            <td>{{ $systems[$item->system_type] }}</td>
                            <td><span class="badge {{ ['bajo' => 'bg-success', 'medio' => 'bg-warning text-dark', 'alto' => 'bg-danger'][$item->criticality] }}"><span aria-hidden="true">&#9679;</span> {{ $criticalities[$item->criticality] }}</span></td>
                            <td>{{ $statuses[$item->status] }}</td>
                            <td><button type="button" class="btn btn-sm btn-outline-secondary edit-backlog" data-item="{{ json_encode($item->only(['id', 'quantity', 'unit', 'description', 'unit_cost', 'system_type', 'criticality', 'status'])) }}" data-url="{{ route('maintenance.backlog.update', $item) }}">Editar</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="10">No hay piezas registradas para este equipo en el centro seleccionado. Usa «Dar de alta pieza» para comenzar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </div>
        <section class="card mb-3" id="backlog-editor" @if(!$errors->any()) hidden @endif>
            <h3 id="editor-title">{{ old('_backlog_id') ? 'Editar pieza' : 'Dar de alta pieza' }}</h3>
            <p>{{ $selectedVehicle->identifier ?: $selectedVehicle->plate }} · {{ $selectedCostCenter->name }}</p>
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" id="backlog-form" action="{{ old('_backlog_id') ? route('maintenance.backlog.update', (int)old('_backlog_id')) : route('maintenance.backlog.store') }}" data-create-url="{{ route('maintenance.backlog.store') }}">
                @csrf
                <input type="hidden" name="_method" value="{{ old('_backlog_id') ? 'PUT' : 'POST' }}">
                <input type="hidden" name="_backlog_id" value="{{ old('_backlog_id') }}">
                <input type="hidden" name="vehicle_id" value="{{ $selectedVehicle->id }}">
                <input type="hidden" name="cost_center_id" value="{{ $selectedCostCenter->id }}">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label" for="description">Descripción de pieza o refacción</label><textarea id="description" class="form-control" name="description" maxlength="1000" required>{{ old('description') }}</textarea></div>
                    <div class="col-md-3"><label class="form-label" for="quantity">Cantidad</label><input id="quantity" class="form-control" type="number" name="quantity" min="0.01" max="999999.99" step="0.01" value="{{ old('quantity', 1) }}" required></div>
                    <div class="col-md-3"><label class="form-label" for="unit">Unidad de medida</label><input id="unit" class="form-control" name="unit" maxlength="50" list="measure-units" value="{{ old('unit', 'Pieza') }}" required><datalist id="measure-units"><option value="Pieza"><option value="Litro"><option value="Kilogramo"><option value="Metro"><option value="Juego"><option value="Servicio"></datalist></div>
                    <div class="col-md-3"><label class="form-label" for="unit-cost">Costo unitario</label><input id="unit-cost" class="form-control" type="number" name="unit_cost" min="0" max="9999999.99" step="0.01" value="{{ old('unit_cost', 0) }}" required></div>
                    <div class="col-md-3"><label class="form-label" for="total-cost">Costo total</label><output id="total-cost" class="form-control" aria-live="polite">$0.00</output></div>
                    <div class="col-md-4"><label class="form-label" for="system-type">Tipo de sistema</label><select id="system-type" class="form-select" name="system_type" required>@foreach($systems as $value => $label)<option value="{{ $value }}" @selected(old('system_type') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label" for="criticality">Criticidad</label><select id="criticality" class="form-select" name="criticality" required>@foreach($criticalities as $value => $label)<option value="{{ $value }}" @selected(old('criticality', 'bajo') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label" for="item-status">Estado de la pieza</label><select id="item-status" class="form-select" name="status" required>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', 'required') === $value)>{{ $label }}</option>@endforeach</select></div>
                </div>
                <div class="d-flex gap-2 mt-3"><button class="btn btn-primary" type="submit">Guardar pieza</button><button class="btn btn-secondary" type="button" id="cancel-backlog">Cancelar</button></div>
            </form>
        </section>
        <div class="card">
            <h3>Requisiciones del equipo</h3>
            <p class="text-muted">Solicitudes existentes del centro seleccionado. Los costos, el sistema y la criticidad se capturan al dar de alta la pieza en BackLog.</p>
            <div class="table-responsive"><table class="table"><thead><tr><th>Folio</th><th>Descripción</th><th>Cantidad</th><th>Estado de requisición</th><th>Pedido</th><th>En almacén</th></tr></thead><tbody>
            @forelse($requisitionItems as $requestedItem)
                <tr><td>{{ $requestedItem->requisition->folio }}</td><td>{{ $requestedItem->material_name }}</td><td>{{ $requestedItem->quantity }}</td><td>{{ $requestedItem->requisition->status_label }}</td><td>{{ $requestedItem->is_ordered ? 'Sí' : 'No' }}</td><td>{{ $requestedItem->is_in_storage ? 'Sí' : 'No' }}</td></tr>
            @empty
                <tr><td colspan="6">No hay requisiciones vinculadas a este equipo y centro de costos.</td></tr>
            @endforelse
            </tbody></table></div>
            {{ $requisitionItems->links() }}
        </div>
    @else
        <div class="alert alert-info">Selecciona un equipo para consultar sus piezas y registrar su BackLog.</div>
    @endif
@endif
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('backlog-form');
    if (!form) return;
    const editor = document.getElementById('backlog-editor');
    const total = () => {
        const quantity = Number(form.elements.quantity.value) || 0;
        const cost = Number(form.elements.unit_cost.value) || 0;
        document.getElementById('total-cost').textContent = '$' + (quantity * cost).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };
    const open = () => { editor.hidden = false; editor.scrollIntoView({behavior: 'smooth', block: 'start'}); form.elements.description.focus(); total(); };
    document.getElementById('add-backlog').addEventListener('click', () => {
        form.action = form.dataset.createUrl;
        form.elements._method.value = 'POST';
        form.elements._backlog_id.value = '';
        for (const [key, value] of Object.entries({description: '', quantity: 1, unit: 'Pieza', unit_cost: 0, system_type: 'hidraulico', criticality: 'bajo', status: 'required'})) form.elements[key].value = value;
        document.getElementById('editor-title').textContent = 'Dar de alta pieza';
        open();
    });
    document.querySelectorAll('.edit-backlog').forEach(button => button.addEventListener('click', () => {
        const item = JSON.parse(button.dataset.item);
        form.action = button.dataset.url;
        form.elements._method.value = 'PUT';
        form.elements._backlog_id.value = item.id;
        for (const key of ['description', 'quantity', 'unit', 'unit_cost', 'system_type', 'criticality', 'status']) form.elements[key].value = item[key];
        document.getElementById('editor-title').textContent = 'Editar pieza';
        open();
    }));
    document.getElementById('cancel-backlog').addEventListener('click', () => { editor.hidden = true; });
    form.elements.quantity.addEventListener('input', total);
    form.elements.unit_cost.addEventListener('input', total);
    total();
})();
</script>
@endpush
