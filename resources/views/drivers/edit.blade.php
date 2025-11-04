@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Conductor</h2>
    <form method="POST" action="{{ route('drivers.update', $driver) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name', $driver->name) }}" required>
        </div>
        <div>
            <label>Número de empleado</label>
            <input name="employee_number" value="{{ old('employee_number', $driver->employee_number) }}">
        </div>
        <div>
            <label>Licencia</label>
            <input name="license" value="{{ old('license', $driver->license) }}">
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $driver->active) ? 'checked' : '' }}> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('drivers.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
    </form>
    
</div>
@endsection

