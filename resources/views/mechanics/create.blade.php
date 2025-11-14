@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo Mecánico</h2>
    <form method="POST" action="{{ route('mechanics.store') }}" class="grid grid-3">
        @csrf
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Salario diario</label>
            <input type="number" step="0.01" name="daily_salary" value="{{ old('daily_salary', 0) }}" min="0" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activo</label>
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('mechanics.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

