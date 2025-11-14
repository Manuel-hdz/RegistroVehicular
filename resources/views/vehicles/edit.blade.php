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
        <div style="grid-column: 1/-1;">
            <label>Icono del vehículo</label>
            <div class="row" style="gap:12px; flex-wrap:wrap;">
                @php($val = old('vtype', $vehicle->vtype))
                <label class="btn btn-secondary btn-icon" style="display:flex; align-items:center; gap:8px;">
                    <input type="radio" name="vtype" value="auto" {{ $val==='auto' ? 'checked' : '' }}> 
                    <span style="display:inline-flex; color:#374151;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 11l2.5-4.5A2 2 0 0 1 9.2 5h5.6a2 2 0 0 1 1.7 1L19 11h1a1 1 0 0 1 1 1v3h-1.05a2.5 2.5 0 1 1-4.9 0H9.95a2.5 2.5 0 1 1-4.9 0H4v-3a1 1 0 0 1 1-1h0Zm2.3 0h9.4l-1.8-3.24a1 1 0 0 0-.87-.5H9.2a1 1 0 0 0-.87.5L7.3 11Z"/></svg>
                    </span>
                    Auto
                </label>
                <label class="btn btn-secondary btn-icon" style="display:flex; align-items:center; gap:8px;">
                    <input type="radio" name="vtype" value="pickup" {{ $val==='pickup' ? 'checked' : '' }}> 
                    <span style="display:inline-flex; color:#374151;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 13v-2a2 2 0 0 1 2-2h4l2 3h6a2 2 0 0 1 2 2v2h-1a2.5 2.5 0 1 1-5 0H9a2.5 2.5 0 1 1-5 0H3v-3Zm3-2a1 1 0 0 0-1 1v1h6l-1.33-2H6Z"/></svg>
                    </span>
                    Pickup
                </label>
                <label class="btn btn-secondary btn-icon" style="display:flex; align-items:center; gap:8px;">
                    <input type="radio" name="vtype" value="furgoneta" {{ $val==='furgoneta' ? 'checked' : '' }}> 
                    <span style="display:inline-flex; color:#374151;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2 12h11V7h4l3 4v5h-1a2.5 2.5 0 1 1-5 0H9a2.5 2.5 0 1 1-5 0H2v-4Zm17 0h2l-2-3h-2v3Z"/></svg>
                    </span>
                    Furgoneta
                </label>
                <label class="btn btn-secondary btn-icon" style="display:flex; align-items:center; gap:8px;">
                    <input type="radio" name="vtype" value="camion" {{ $val==='camion' ? 'checked' : '' }}> 
                    <span style="display:inline-flex; color:#374151;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 7h10v7h2.5a2.5 2.5 0 0 1 2.45 2h.55a2.5 2.5 0 1 1 0 1H18a2.5 2.5 0 0 1-4.9 0H8.9a2.5 2.5 0 0 1-4.9 0H3V8a1 1 0 0 1 0-1Zm11 1H4v6h10V8Zm1 6h2.5a1.5 1.5 0 0 1 1.5 1.5V16h-5v-.5A1.5 1.5 0 0 1 15 14Z"/></svg>
                    </span>
                    Camión
                </label>
            </div>
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
