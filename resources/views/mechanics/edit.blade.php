@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Mecanico</h2>
    <form method="POST" action="{{ route('mechanics.update', $mechanic) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div style="grid-column: 1/-1;">
            <label for="mechanic_personnel_id">Personal registrado</label>
            <select
                id="mechanic_personnel_id"
                class="searchable-select"
                name="personnel_id"
                data-name-target="#mechanic_name_display"
                data-employee-target="#mechanic_employee_display"
            >
                <option value=""></option>
                @foreach($personnelOptions as $personnel)
                    <option
                        value="{{ $personnel->id }}"
                        data-personnel-name="{{ $personnel->full_name }}"
                        data-employee-number="{{ $personnel->employee_number }}"
                        @selected(old('personnel_id', $mechanic->personnel_id) == $personnel->id)
                    >
                        {{ $personnel->employee_number }} - {{ $personnel->full_name }}{{ $personnel->position ? ' / '.$personnel->position : '' }}
                    </option>
                @endforeach
            </select>
            <small style="color:#64748b;">El nombre se actualiza con base en el personal seleccionado.</small>
        </div>
        <div>
            <label>Nombre</label>
            <input id="mechanic_name_display" value="{{ old('personnel_id', $mechanic->personnel_id) ? '' : $mechanic->name }}" readonly>
        </div>
        <div>
            <label>Numero de empleado</label>
            <input id="mechanic_employee_display" value="{{ old('personnel_id') ? '' : optional($mechanic->personnel)->employee_number }}" readonly>
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

@include('partials.searchable-select-scripts')
