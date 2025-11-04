@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Registrar Entrada</h2>
    <p>
        Vehículo: <strong>{{ $movement->vehicle->plate }}</strong> — Conductor: <strong>{{ $movement->driver->name }}</strong><br>
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
            <label>Combustible entrada (%)</label>
            <input type="number" name="fuel_in" value="{{ old('fuel_in', $movement->fuel_out) }}" min="0" max="100" required>
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
@endsection
