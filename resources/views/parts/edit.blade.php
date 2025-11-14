@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Refacción</h2>
    <form method="POST" action="{{ route('parts.update', $part) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name', $part->name) }}" required>
        </div>
        <div>
            <label>Costo unitario</label>
            <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', $part->unit_cost) }}" min="0" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $part->active) ? 'checked' : '' }}> Activa</label>
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('parts.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

