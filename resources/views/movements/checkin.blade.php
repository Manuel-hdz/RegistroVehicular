@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Registrar Entrada</h2>
    <p>
        Vehículo: <strong>{{ $movement->vehicle->identifier }}</strong> — Conductor: <strong>{{ $movement->driver->name }}</strong><br>
        Salida: {{ $movement->departed_at->format('Y-m-d H:i') }}, Odómetro: {{ $movement->odometer_out }} km, Comb.: {{ $movement->fuel_out }}%
    </p>
    <form method="POST" action="{{ route('movements.checkin', $movement) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Odómetro entrada (km)</label>
            <input type="number" name="odometer_in" value="{{ old('odometer_in', $movement->odometer_out) }}" min="{{ $movement->odometer_out }}" required>
        </div>
        <div>
            <label>Combustible entrada</label>
            <div class="row" style="gap:8px;">
                <select name="fuel_in_base" required>
                    <option value="1/4" @selected(old('fuel_in_base')==='1/4')>1/4</option>
                    <option value="1/2" @selected(old('fuel_in_base','1/2')==='1/2')>1/2</option>
                    <option value="3/4" @selected(old('fuel_in_base')==='3/4')>3/4</option>
                    <option value="1" @selected(old('fuel_in_base')==='1')>Lleno</option>
                </select>
                <select name="fuel_in_dir" required>
                    <option value="below" @selected(old('fuel_in_dir')==='below')>Abajo de</option>
                    <option value="exact" @selected(old('fuel_in_dir','exact')==='exact')>Exacto</option>
                    <option value="above" @selected(old('fuel_in_dir')==='above')>Arriba de</option>
                </select>
            </div>
            <small style="color:#555;">Se almacenará como porcentaje aproximado.</small>
        </div>
        <div>
            <label>Fecha/Hora entrada</label>
            <input type="datetime-local" name="arrived_at" value="{{ old('arrived_at', now()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="grid" style="grid-column: 1/-1;">
            <label>Notas</label>
            <textarea name="notes_in">{{ old('notes_in') }}</textarea>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('movements.index') }}">Cancelar</a>
            <button class="btn btn-warning" type="submit">Guardar Entrada</button>
        </div>
    </form>
    
</div>
<script>
    (function(){
        var input = document.querySelector('input[name="arrived_at"]');
        if(input && !input.value){
            var d = new Date();
            var yyyy = d.getFullYear();
            var mm = String(d.getMonth()+1).padStart(2,'0');
            var dd = String(d.getDate()).padStart(2,'0');
            input.value = yyyy + '-' + mm + '-' + dd + 'T08:00';
        }
    })();
</script>
@endsection
