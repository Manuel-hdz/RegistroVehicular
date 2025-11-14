@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Mecánico</h2>
    <form method="POST" action="{{ route('mechanics.update', $mechanic) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name', $mechanic->name) }}" required>
        </div>
        <div>
            <label>Salario diario</label>
            <input type="number" step="0.01" name="daily_salary" value="{{ old('daily_salary', $mechanic->daily_salary) }}" min="0" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $mechanic->active) ? 'checked' : '' }}> Activo</label>
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('mechanics.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

