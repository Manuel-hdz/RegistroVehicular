@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Registrar Salida</h2>
    <form method="POST" action="{{ route('movements.store') }}" class="grid grid-3">
        @csrf
        <div>
            <label>Vehículo</label>
            <select name="vehicle_id" required>
                <option value="">Seleccione…</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(old('vehicle_id')==$v->id)>
                        {{ $v->plate }} {{ $v->identifier ? '— '.$v->identifier : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Conductor</label>
            <select name="driver_id" required>
                <option value="">Seleccione…</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected(old('driver_id')==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Odómetro salida (km)</label>
            <input type="number" name="odometer_out" value="{{ old('odometer_out') }}" min="0" required>
        </div>
        <div>
            <label>Combustible salida</label>
            <div class="row" style="gap:8px;">
                <select name="fuel_out_base" required>
                    <option value="1/4" @selected(old('fuel_out_base')==='1/4')>1/4</option>
                    <option value="1/2" @selected(old('fuel_out_base','1/2')==='1/2')>1/2</option>
                    <option value="3/4" @selected(old('fuel_out_base')==='3/4')>3/4</option>
                    <option value="1" @selected(old('fuel_out_base')==='1')>Lleno</option>
                </select>
                <select name="fuel_out_dir" required>
                    <option value="below" @selected(old('fuel_out_dir')==='below')>Abajo de</option>
                    <option value="exact" @selected(old('fuel_out_dir','exact')==='exact')>Exacto</option>
                    <option value="above" @selected(old('fuel_out_dir')==='above')>Arriba de</option>
                </select>
            </div>
            <small style="color:#555;">Se almacenará como porcentaje aproximado.</small>
        </div>
        <div>
            <label>Fecha/Hora salida</label>
            <input type="datetime-local" name="departed_at" value="{{ old('departed_at', now()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div>
            <label>Destino</label>
            <input type="text" name="destination" value="{{ old('destination') }}">
        </div>
        <div class="grid" style="grid-column: 1/-1;">
            <label>Notas</label>
            <textarea name="notes_out">{{ old('notes_out') }}</textarea>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('movements.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar Salida</button>
        </div>
    </form>
</div>
@endsection
