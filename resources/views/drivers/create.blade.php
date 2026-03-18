@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo Conductor</h2>
    <form method="POST" action="{{ route('drivers.store') }}" class="grid grid-3">
        @csrf
        <div style="grid-column: 1/-1;">
            <label for="driver_personnel_id">Personal registrado</label>
            <select
                id="driver_personnel_id"
                class="searchable-select"
                name="personnel_id"
                data-name-target="#driver_name_display"
                data-employee-target="#driver_employee_display"
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
            <small style="color:#64748b;">Selecciona a la persona desde RRHH para enlazar este conductor.</small>
        </div>
        <div>
            <label>Nombre</label>
            <input id="driver_name_display" value="" readonly>
        </div>
        <div>
            <label>Numero de empleado</label>
            <input id="driver_employee_display" value="" readonly>
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

@include('partials.searchable-select-scripts')
