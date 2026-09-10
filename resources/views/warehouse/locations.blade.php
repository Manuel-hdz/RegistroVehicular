@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;">Ubicaciones de almacén</h2>
            <p style="margin:6px 0 0; color:#6b7280;">Catálogo de ubicaciones para <strong>{{ $selectedCostCenter->name }}</strong>.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('warehouse.movements', ['cost_center_id' => $selectedCostCenter->id]) }}">Volver a entradas</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('warehouse.locations.index') }}" style="max-width:420px;">
        <label for="locationCostCenter">Centro de costos</label>
        <select id="locationCostCenter" name="cost_center_id" onchange="this.form.submit()">
            @foreach($costCenters as $costCenter)
                <option value="{{ $costCenter->id }}" @selected($selectedCostCenter->id === $costCenter->id)>{{ $costCenter->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Agregar ubicación</h3>
    <form method="POST" action="{{ route('warehouse.locations.store') }}" class="grid grid-3">
        @csrf
        <input type="hidden" name="cost_center_id" value="{{ $selectedCostCenter->id }}">
        <div>
            <label for="newLocationName">Nombre</label>
            <input id="newLocationName" name="name" value="{{ old('name') }}" maxlength="120" required placeholder="Ej. Estante A-01">
        </div>
        <div style="grid-column:span 2;">
            <label for="newLocationDescription">Descripción</label>
            <input id="newLocationDescription" name="description" value="{{ old('description') }}" maxlength="255" placeholder="Zona, nivel o referencia">
        </div>
        <div style="grid-column:1/-1;">
            <button class="btn btn-primary" type="submit">Guardar ubicación</button>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Ubicaciones registradas</h3>
    <div style="display:grid; gap:12px;">
        @forelse($locations as $location)
            <form method="POST" action="{{ route('warehouse.locations.update', $location) }}" style="padding:14px; border:1px solid rgba(16,52,37,.12); border-radius:16px; background:#fff;">
                @csrf
                @method('PATCH')
                <div class="grid grid-3">
                    <div>
                        <label>Nombre</label>
                        <input name="name" value="{{ $location->name }}" maxlength="120" required>
                    </div>
                    <div style="grid-column:span 2;">
                        <label>Descripción</label>
                        <input name="description" value="{{ $location->description }}" maxlength="255">
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label style="display:flex; align-items:center; gap:8px; margin:0; text-transform:none; letter-spacing:0;">
                            <input type="checkbox" name="active" value="1" @checked($location->active)>
                            Activa
                        </label>
                    </div>
                    <div style="grid-column:span 2; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                        <small style="color:#6b7280;">Última actualización: {{ $location->updated_at?->format('Y-m-d H:i') }}{{ $location->updater ? ' por '.$location->updater->name : '' }}</small>
                        <button class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                </div>
            </form>
        @empty
            <p style="margin:0; color:#6b7280;">No hay ubicaciones registradas.</p>
        @endforelse
    </div>
</div>
@endsection
