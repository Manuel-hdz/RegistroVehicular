<div>
    <label>Placa</label>
    <input name="plate" value="{{ old('plate', $vehicle->plate ?? '') }}" required>
</div>
<div>
    <label>Identificador</label>
    <input name="identifier" value="{{ old('identifier', $vehicle->identifier ?? '') }}">
</div>
<div>
    <label>Modelo</label>
    <input name="model" value="{{ old('model', $vehicle->model ?? '') }}">
</div>
<div>
    <label>Anio</label>
    <input type="number" name="year" value="{{ old('year', $vehicle->year ?? '') }}" min="1900" max="2100">
</div>
<div>
    <label>Numero de serie</label>
    <input name="serial_number" value="{{ old('serial_number', $vehicle->serial_number ?? '') }}">
</div>
<div>
    <label>Numero de serie adicional</label>
    <input name="additional_serial_number" value="{{ old('additional_serial_number', $vehicle->additional_serial_number ?? '') }}">
</div>
<div>
    <label>Motor</label>
    <input name="engine_number" value="{{ old('engine_number', $vehicle->engine_number ?? '') }}">
</div>
<div>
    <label>Proveedor</label>
    <input name="supplier" value="{{ old('supplier', $vehicle->supplier ?? '') }}">
</div>
<div>
    <label>Personal asignado</label>
    <input name="assigned_personnel" value="{{ old('assigned_personnel', $vehicle->assigned_personnel ?? '') }}">
</div>
<div style="grid-column: 1/-1;">
    <label style="margin-bottom:4px;">Icono del vehiculo</label>
    @include('vehicles.partials.vtype-selector', [
        'selected' => old('vtype', ($vehicle->vtype ?? 'auto') ?: 'auto'),
        'idPrefix' => $idPrefix,
    ])
</div>
<div style="grid-column: 1/-1;">
    <label>Descripcion</label>
    <textarea name="description">{{ old('description', $vehicle->description ?? '') }}</textarea>
</div>
<div>
    <label>Foto del equipo</label>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
    @if(!empty($vehicle?->photo_url))
        <div style="margin-top:8px;">
            <a href="{{ $vehicle->photo_url }}" target="_blank" rel="noopener">Ver foto actual</a>
        </div>
    @endif
</div>
<div>
    <label>Tarjeta de circulacion</label>
    <input type="file" name="circulation_card" accept=".pdf,.jpg,.jpeg,.png">
    @if(!empty($vehicle?->circulation_card_url))
        <div style="margin-top:8px;">
            <a href="{{ $vehicle->circulation_card_url }}" target="_blank" rel="noopener">Ver archivo actual</a>
        </div>
    @endif
</div>
<div>
    <label>Poliza de seguro</label>
    <input type="file" name="insurance_policy" accept=".pdf,.jpg,.jpeg,.png">
    @if(!empty($vehicle?->insurance_policy_url))
        <div style="margin-top:8px;">
            <a href="{{ $vehicle->insurance_policy_url }}" target="_blank" rel="noopener">Ver archivo actual</a>
        </div>
    @endif
</div>
@if(auth()->check() && auth()->user()->role === 'superadmin')
    <div>
        <label><input type="checkbox" name="active" value="1" {{ old('active', $vehicle->active ?? true) ? 'checked' : '' }}> Activo</label>
    </div>
@else
    <div>
        <label>Estatus</label>
        <input value="{{ ($vehicle->active ?? true) ? 'Activo' : 'Inactivo' }}" disabled>
    </div>
@endif
