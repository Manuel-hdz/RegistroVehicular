@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo Conductor</h2>
    <form method="POST" action="{{ route('drivers.store') }}" class="grid grid-3">
        @csrf
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Número de empleado</label>
            <input name="employee_number" value="{{ old('employee_number') }}">
        </div>
        <div>
            <label>Licencia</label>
            <input name="license" value="{{ old('license') }}">
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('drivers.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
    
</div>
@endsection

