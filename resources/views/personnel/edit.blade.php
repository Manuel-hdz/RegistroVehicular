@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar personal</h2>
    <form method="POST" action="{{ route('personnel.update', $personnel) }}" class="grid grid-3" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="page" value="{{ request('page') }}">
        <div>
            <label>No. de empleado</label>
            <input name="employee_number" value="{{ old('employee_number', $personnel->employee_number) }}" required>
        </div>
        <div>
            <label>Nombre(s)</label>
            <input name="first_name" value="{{ old('first_name', $personnel->first_name) }}" required>
        </div>
        <div>
            <label>Apellido paterno</label>
            <input name="last_name" value="{{ old('last_name', $personnel->last_name) }}" required>
        </div>
        <div>
            <label>Apellido materno</label>
            <input name="middle_name" value="{{ old('middle_name', $personnel->middle_name) }}">
        </div>
        <div>
            <label>CURP</label>
            <input name="curp" value="{{ old('curp', $personnel->curp) }}">
        </div>
        <div>
            <label>RFC</label>
            <input name="rfc" value="{{ old('rfc', $personnel->rfc) }}">
        </div>
        <div>
            <label>NSS</label>
            <input name="nss" value="{{ old('nss', $personnel->nss) }}">
        </div>
        <div>
            <label>Puesto</label>
            <input name="position" value="{{ old('position', $personnel->position) }}">
        </div>
        <div>
            <label>Departamento</label>
            <input name="department" value="{{ old('department', $personnel->department) }}">
        </div>
        <div>
            <label>Fecha de ingreso</label>
            <input type="date" name="hire_date" value="{{ old('hire_date', optional($personnel->hire_date)->format('Y-m-d')) }}">
        </div>
        <div>
            <label>Telefono</label>
            <input name="phone" value="{{ old('phone', $personnel->phone) }}">
        </div>
        <div>
            <label>Correo</label>
            <input type="email" name="email" value="{{ old('email', $personnel->email) }}">
        </div>
        <div style="grid-column: 1/-1;">
            <label>Domicilio</label>
            <input name="address" value="{{ old('address', $personnel->address) }}">
        </div>
        <div>
            <label>Contacto de emergencia</label>
            <input name="emergency_contact_name" value="{{ old('emergency_contact_name', $personnel->emergency_contact_name) }}">
        </div>
        <div>
            <label>Telefono de emergencia</label>
            <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $personnel->emergency_contact_phone) }}">
        </div>
        <div>
            <label>Fotografia</label>
            <input type="file" name="photo" accept="image/*">
            @if($personnel->photo_url)
                <div style="margin-top:8px;">
                    <img src="{{ $personnel->photo_url }}" alt="Foto actual" style="max-width:120px; border-radius:8px; border:1px solid #d1d5db;">
                </div>
            @endif
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $personnel->active) ? 'checked' : '' }}> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('personnel.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
    </form>
</div>
@endsection
