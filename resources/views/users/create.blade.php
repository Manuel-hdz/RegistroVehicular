@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo usuario (Solo SuperAdmin)</h2>
    <form method="POST" action="{{ route('users.store') }}" class="grid grid-3" id="userCreateForm">
        @csrf
        <div>
            <label>Nombre</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Usuario</label>
            <input type="text" name="username" value="{{ old('username') }}" required>
        </div>
        <div>
            <label>Contrasena</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Rol</label>
            <select name="role" required>
                @foreach($roles as $k => $label)
                    <option value="{{ $k }}" @selected(old('role') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Departamento</label>
            <select name="department" required>
                @foreach($departments as $k => $label)
                    <option value="{{ $k }}" @selected(old('department') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activo</label>
        </div>
        <div style="grid-column:1/-1;">
            <label style="display:flex; align-items:center; gap:10px; margin:0; padding:14px 16px; border:1px solid #d9e3dd; border-radius:16px; background:#fff; text-transform:none; letter-spacing:0; color:#173629;">
                <input type="checkbox" name="special_permissions" value="1" id="specialPermissionsToggle" {{ old('special_permissions') ? 'checked' : '' }}>
                <span style="font-weight:700;">Permisos especiales</span>
            </label>
        </div>
        <div id="specialPermissionsPanel" style="grid-column:1/-1; display:{{ old('special_permissions') ? 'block' : 'none' }};">
            <div style="padding:16px; border:1px solid #d9e3dd; border-radius:18px; background:#f8fbf9;">
                <label style="margin-bottom:12px;">Modulos adicionales</label>
                <div class="grid grid-3">
                    @foreach($moduleOptions as $moduleKey => $label)
                        <label style="display:flex; align-items:center; gap:10px; margin:0; padding:14px 16px; border:1px solid #d9e3dd; border-radius:16px; background:#fff; text-transform:none; letter-spacing:0; color:#173629;">
                            <input type="checkbox" name="module_permissions[]" value="{{ $moduleKey }}" {{ in_array($moduleKey, old('module_permissions', []), true) ? 'checked' : '' }}>
                            <span style="font-weight:700;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('users.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var toggle = document.getElementById('specialPermissionsToggle');
        var panel = document.getElementById('specialPermissionsPanel');

        if (!toggle || !panel) {
            return;
        }

        function syncPanel() {
            panel.style.display = toggle.checked ? 'block' : 'none';
        }

        toggle.addEventListener('change', syncPanel);
        syncPanel();
    })();
</script>
@endpush
