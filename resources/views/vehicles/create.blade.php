@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo Vehículo</h2>
    <form method="POST" action="{{ route('vehicles.store') }}" class="grid grid-3">
        @csrf
        <div>
            <label>Placa</label>
            <input name="plate" value="{{ old('plate') }}" required>
        </div>
        <div>
            <label>Identificador</label>
            <input name="identifier" value="{{ old('identifier') }}">
        </div>
        <div>
            <label>Modelo</label>
            <input name="model" value="{{ old('model') }}">
        </div>
        <div>
            <label>Año</label>
            <input type="number" name="year" value="{{ old('year') }}" min="1900" max="2100">
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('vehicles.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

