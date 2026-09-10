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
        <div style="grid-column:1/-1;">
            <label>Centros de costos</label>
            <p style="margin:4px 0 10px; color:#6b7280;">Selecciona uno o varios almacenes a los que tendrá acceso.</p>
            @php($selectedCostCenterIds = array_map('intval', old('cost_center_ids', $user->costCenters->pluck('id')->all())))
            <div class="grid grid-3">
                @foreach($costCenters as $costCenter)
                    <label style="display:flex; align-items:center; gap:10px; margin:0; padding:14px 16px; border:1px solid #d9e3dd; border-radius:16px; background:#fff; text-transform:none; letter-spacing:0; color:#173629;">
                        <input type="checkbox" name="cost_center_ids[]" value="{{ $costCenter->id }}" {{ in_array($costCenter->id, $selectedCostCenterIds, true) ? 'checked' : '' }}>
                        <span style="font-weight:700;">{{ $costCenter->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('users.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
