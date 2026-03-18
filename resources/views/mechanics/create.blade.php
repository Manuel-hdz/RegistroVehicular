@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo Mecanico</h2>
    <form method="POST" action="{{ route('mechanics.store') }}" class="grid grid-3">
        @csrf
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
                        @selected(old('personnel_id') == $personnel->id)
                    >
                        {{ $personnel->employee_number }} - {{ $personnel->full_name }}{{ $personnel->position ? ' / '.$personnel->position : '' }}
                    </option>
                @endforeach
            </select>
            <small style="color:#64748b;">Selecciona a la persona de RRHH para enlazar este mecanico.</small>
        </div>
        <div>
            <label>Nombre</label>
            <input id="mechanic_name_display" value="" readonly>
        </div>
        <div>
            <label>Numero de empleado</label>
            <input id="mechanic_employee_display" value="" readonly>
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

@include('partials.searchable-select-scripts')
