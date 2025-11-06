@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Movimiento (Solo SuperAdmin)</h2>
    <form method="POST" action="{{ route('movements.update', $movement) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Vehículo</label>
            <select name="vehicle_id" required>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(old('vehicle_id', $movement->vehicle_id)==$v->id)>{{ $v->plate }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Conductor</label>
            <select name="driver_id" required>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected(old('driver_id', $movement->driver_id)==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Estado</label>
            <select name="status" required>
                @foreach(['open' => 'Abierto', 'closed' => 'Cerrado', 'cancelled' => 'Cancelado'] as $k=>$label)
                    <option value="{{ $k }}" @selected(old('status', $movement->status)===$k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Odómetro salida (km)</label>
            <input type="number" name="odometer_out" value="{{ old('odometer_out', $movement->odometer_out) }}" min="0" required>
        </div>
        <div>
            <label>Combustible salida</label>
            <div class="row" style="gap:8px;">
                <select name="fuel_out_base">
                    @php($baseOut = match(true){$movement->fuel_out>=87=> '1', $movement->fuel_out>=62=> '3/4', $movement->fuel_out>=37=> '1/2', default => '1/4'})
                    <option value="1/4" @selected(old('fuel_out_base', $baseOut)==='1/4')>1/4</option>
                    <option value="1/2" @selected(old('fuel_out_base', $baseOut)==='1/2')>1/2</option>
                    <option value="3/4" @selected(old('fuel_out_base', $baseOut)==='3/4')>3/4</option>
                    <option value="1" @selected(old('fuel_out_base', $baseOut)==='1')>Lleno</option>
                </select>
                <select name="fuel_out_dir">
                    @php($dirOut = $movement->fuel_out%25===0 ? 'exact' : ($movement->fuel_out%25>0 && $movement->fuel_out%25<=12 ? 'above' : 'below'))
                    <option value="below" @selected(old('fuel_out_dir', $dirOut)==='below')>Abajo de</option>
                    <option value="exact" @selected(old('fuel_out_dir', $dirOut)==='exact')>Exacto</option>
                    <option value="above" @selected(old('fuel_out_dir', $dirOut)==='above')>Arriba de</option>
                </select>
            </div>
            <small style="color:#555;">Se almacenará como porcentaje aproximado. Actual: {{ $movement->fuel_out }}%</small>
        </div>
        <div>
            <label>Fecha/Hora salida</label>
            <input type="datetime-local" name="departed_at" value="{{ old('departed_at', optional($movement->departed_at)->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="grid" style="grid-column: 1/-1;">
            <label>Destino</label>
            <input type="text" name="destination" value="{{ old('destination', $movement->destination) }}">
        </div>
        <div class="grid" style="grid-column: 1/-1;">
            <label>Notas salida</label>
            <textarea name="notes_out">{{ old('notes_out', $movement->notes_out) }}</textarea>
        </div>
        <div>
            <label>Odómetro entrada (km)</label>
            <input type="number" name="odometer_in" value="{{ old('odometer_in', $movement->odometer_in) }}" min="0">
        </div>
        <div>
            <label>Combustible entrada</label>
            <div class="row" style="gap:8px;">
                @php($baseIn = match(true){($movement->fuel_in??0)>=87=> '1', ($movement->fuel_in??0)>=62=> '3/4', ($movement->fuel_in??0)>=37=> '1/2', default => '1/4'})
                <select name="fuel_in_base">
                    <option value="1/4" @selected(old('fuel_in_base', $baseIn)==='1/4')>1/4</option>
                    <option value="1/2" @selected(old('fuel_in_base', $baseIn)==='1/2')>1/2</option>
                    <option value="3/4" @selected(old('fuel_in_base', $baseIn)==='3/4')>3/4</option>
                    <option value="1" @selected(old('fuel_in_base', $baseIn)==='1')>Lleno</option>
                </select>
                @php($dirIn = ($movement->fuel_in ?? 0)%25===0 ? 'exact' : (($movement->fuel_in ?? 0)%25>0 && ($movement->fuel_in ?? 0)%25<=12 ? 'above' : 'below'))
                <select name="fuel_in_dir">
                    <option value="below" @selected(old('fuel_in_dir', $dirIn)==='below')>Abajo de</option>
                    <option value="exact" @selected(old('fuel_in_dir', $dirIn)==='exact')>Exacto</option>
                    <option value="above" @selected(old('fuel_in_dir', $dirIn)==='above')>Arriba de</option>
                </select>
            </div>
            <small style="color:#555;">Se almacenará como porcentaje aproximado. Actual: {{ $movement->fuel_in ?? '—' }}%</small>
        </div>
        <div>
            <label>Fecha/Hora entrada</label>
            <input type="datetime-local" name="arrived_at" value="{{ old('arrived_at', optional($movement->arrived_at)->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="grid" style="grid-column: 1/-1;">
            <label>Notas entrada</label>
            <textarea name="notes_in">{{ old('notes_in', $movement->notes_in) }}</textarea>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('movements.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
