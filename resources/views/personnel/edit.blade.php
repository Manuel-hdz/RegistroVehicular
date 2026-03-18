@extends('layouts.app')

@section('content')
@php
    $maritalStatusOptions = ['Soltero(a)', 'Casado(a)', 'Union libre', 'Divorciado(a)', 'Viudo(a)', 'Otro'];
    $sexOptions = ['Masculino', 'Femenino', 'Otro', 'Prefiero no decir'];
    $accountTypeOptions = ['Nomina', 'Ahorro', 'Cheques', 'Tarjeta', 'Otra'];
    $selectedMaritalStatus = old('marital_status', $personnel->marital_status);
    $selectedSex = old('sex', $personnel->sex);
    $selectedAccountType = old('account_type', $personnel->account_type);
@endphp
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
            <label>Estado civil</label>
            <select name="marital_status">
                <option value="">Selecciona una opcion</option>
                @foreach($maritalStatusOptions as $option)
                    <option value="{{ $option }}" {{ $selectedMaritalStatus === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
                @if($selectedMaritalStatus && !in_array($selectedMaritalStatus, $maritalStatusOptions, true))
                    <option value="{{ $selectedMaritalStatus }}" selected>{{ $selectedMaritalStatus }}</option>
                @endif
            </select>
        </div>
        <div>
            <label>Sexo</label>
            <select name="sex">
                <option value="">Selecciona una opcion</option>
                @foreach($sexOptions as $option)
                    <option value="{{ $option }}" {{ $selectedSex === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
                @if($selectedSex && !in_array($selectedSex, $sexOptions, true))
                    <option value="{{ $selectedSex }}" selected>{{ $selectedSex }}</option>
                @endif
            </select>
        </div>
        <div>
            <label>Fecha de nacimiento</label>
            <input type="date" name="birth_date" value="{{ old('birth_date', optional($personnel->birth_date)->format('Y-m-d')) }}">
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
            <label>Dias de vacaciones pendientes</label>
            <input type="number" name="pending_vacation_days" min="0" step="1" value="{{ old('pending_vacation_days', $personnel->pending_vacation_days ?? 0) }}">
        </div>
        <div>
            <label>Numero de cuenta</label>
            <input name="account_number" value="{{ old('account_number', $personnel->account_number) }}">
        </div>
        <div>
            <label>Tipo de cuenta</label>
            <select name="account_type">
                <option value="">Selecciona una opcion</option>
                @foreach($accountTypeOptions as $option)
                    <option value="{{ $option }}" {{ $selectedAccountType === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
                @if($selectedAccountType && !in_array($selectedAccountType, $accountTypeOptions, true))
                    <option value="{{ $selectedAccountType }}" selected>{{ $selectedAccountType }}</option>
                @endif
            </select>
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
            @include('personnel.partials.photo-picker', ['photoUrl' => $personnel->photo_url])
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
