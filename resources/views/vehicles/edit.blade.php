@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Vehículo</h2>
    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="page" value="{{ request('page') }}">
        <div>
            <label>Placa</label>
            <input name="plate" value="{{ old('plate', $vehicle->plate) }}" required>
        </div>
        <div>
            <label>Identificador</label>
            <input name="identifier" value="{{ old('identifier', $vehicle->identifier) }}">
        </div>
        <div>
            <label>Modelo</label>
            <input name="model" value="{{ old('model', $vehicle->model) }}">
        </div>
        <div>
            <label>Año</label>
            <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" min="1900" max="2100">
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $vehicle->active) ? 'checked' : '' }}> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('vehicles.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
    </form>
</div>
@endsection
