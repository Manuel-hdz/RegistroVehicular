@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo personal</h2>
    <form method="POST" action="{{ route('personnel.store') }}" class="grid grid-3" enctype="multipart/form-data">
        @csrf
        <div>
            <label>No. de empleado</label>
            <input name="employee_number" value="{{ old('employee_number') }}" required>
        </div>
        <div>
            <label>Nombre(s)</label>
            <input name="first_name" value="{{ old('first_name') }}" required>
        </div>
        <div>
            <label>Apellido paterno</label>
            <input name="last_name" value="{{ old('last_name') }}" required>
        </div>
        <div>
            <label>Apellido materno</label>
            <input name="middle_name" value="{{ old('middle_name') }}">
        </div>
        <div>
            <label>CURP</label>
            <input name="curp" value="{{ old('curp') }}">
        </div>
        <div>
            <label>RFC</label>
            <input name="rfc" value="{{ old('rfc') }}">
        </div>
        <div>
            <label>NSS</label>
            <input name="nss" value="{{ old('nss') }}">
        </div>
        <div>
            <label>Puesto</label>
            <input name="position" value="{{ old('position') }}">
        </div>
        <div>
            <label>Departamento</label>
            <input name="department" value="{{ old('department') }}">
        </div>
        <div>
            <label>Fecha de ingreso</label>
            <input type="date" name="hire_date" value="{{ old('hire_date') }}">
        </div>
        <div>
            <label>Telefono</label>
            <input name="phone" value="{{ old('phone') }}">
        </div>
        <div>
            <label>Correo</label>
            <input type="email" name="email" value="{{ old('email') }}">
        </div>
        <div style="grid-column: 1/-1;">
            <label>Domicilio</label>
            <input name="address" value="{{ old('address') }}">
        </div>
        <div>
            <label>Contacto de emergencia</label>
            <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
        </div>
        <div>
            <label>Telefono de emergencia</label>
            <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">
        </div>
        <div>
            <label>Fotografia</label>
            @include('personnel.partials.photo-picker')
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('personnel.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection
