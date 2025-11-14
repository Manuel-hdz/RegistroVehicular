@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nueva Refacción</h2>
    <form method="POST" action="{{ route('parts.store') }}" class="grid grid-3">
        @csrf
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Costo unitario</label>
            <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', 0) }}" min="0" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activa</label>
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('parts.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

