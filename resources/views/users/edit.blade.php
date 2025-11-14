@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar Usuario (Solo SuperAdmin)</h2>
    <form method="POST" action="{{ route('users.update', $user) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="page" value="{{ request('page') }}">
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div>
            <label>Usuario</label>
            <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
        </div>
        <div>
            <label>Nueva Contraseña (opcional)</label>
            <input type="password" name="password">
        </div>
        <div>
            <label>Rol</label>
            <select name="role" required>
                @foreach($roles as $k => $label)
                    <option value="{{ $k }}" @selected(old('role', $user->role)===$k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Departamento</label>
            <select name="department" required>
                @foreach($departments as $k => $label)
                    <option value="{{ $k }}" @selected(old('department', $user->department)===$k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $user->active) ? 'checked' : '' }}> Activo</label>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('users.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
