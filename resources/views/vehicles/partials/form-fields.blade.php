<div><label>Clave</label><input name="identifier" value="{{ old('identifier', $vehicle->identifier ?? '') }}"></div>
<div><label>Nombre de unidad</label><input name="unit_name" value="{{ old('unit_name', $vehicle->unit_name ?? '') }}"></div>
<div><label>Fecha de alta</label><input type="date" name="registration_date" value="{{ old('registration_date', isset($vehicle) && $vehicle?->registration_date ? $vehicle->registration_date->format('Y-m-d') : '') }}"></div>
<div><label>Marca/modelo</label><input name="make_model" value="{{ old('make_model', $vehicle->make_model ?? '') }}"></div>
<div><label>Modelo</label><input name="model" value="{{ old('model', $vehicle->model ?? '') }}"></div>
<div><label>Placa</label><input name="plate" value="{{ old('plate', $vehicle->plate ?? '') }}"></div>
<div><label>Número de serie</label><input name="serial_number" value="{{ old('serial_number', $vehicle->serial_number ?? '') }}"></div>
<div><label>Serie eq. adicional</label><input name="additional_serial_number" value="{{ old('additional_serial_number', $vehicle->additional_serial_number ?? '') }}"></div>
<div>
    <label>Tenencia</label><input type="file" name="tenure" accept=".pdf,.jpg,.jpeg,.png">
    @if(!empty($vehicle?->tenure_url))<div style="margin-top:8px;"><a href="{{ $vehicle->tenure_url }}" target="_blank" rel="noopener">Ver archivo actual</a></div>@endif
</div>
<div>
    <label>Tarjeta de circulación</label><input type="file" name="circulation_card" accept=".pdf,.jpg,.jpeg,.png">
    @if(!empty($vehicle?->circulation_card_url))<div style="margin-top:8px;"><a href="{{ $vehicle->circulation_card_url }}" target="_blank" rel="noopener">Ver archivo actual</a></div>@endif
</div>
<div><label>Tipo de motor</label><input name="engine_type" value="{{ old('engine_type', $vehicle->engine_type ?? '') }}"></div>
<div><label>Filtros de motor</label><textarea name="engine_filters" rows="3">{{ old('engine_filters', $vehicle->engine_filters ?? '') }}</textarea></div>
<div><label>Área</label><input name="area" value="{{ old('area', $vehicle->area ?? '') }}"></div>
<div><label>Familia</label><input name="family" value="{{ old('family', $vehicle->family ?? '') }}"></div>
<div><label>Fecha de fabricación</label><input type="date" name="manufacture_date" value="{{ old('manufacture_date', isset($vehicle) && $vehicle?->manufacture_date ? $vehicle->manufacture_date->format('Y-m-d') : '') }}"></div>
<div><label>Asignado</label><input name="assigned_personnel" value="{{ old('assigned_personnel', $vehicle->assigned_personnel ?? '') }}"></div>
<div><label>Estado</label><input name="equipment_status" value="{{ old('equipment_status', $vehicle->equipment_status ?? '') }}"></div>
<div><label>Proveedor</label><input name="supplier" value="{{ old('supplier', $vehicle->supplier ?? '') }}"></div>