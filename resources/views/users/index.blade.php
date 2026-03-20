@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin:0 0 12px;">Usuarios</h2>
    <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:14px;">
        <form method="GET" action="{{ route('users.index') }}" style="display:flex; align-items:flex-end; gap:8px; flex:0 0 33.333%; max-width:33.333%; min-width:260px; margin:0;">
            <input type="hidden" name="page" value="1">
            <div style="width:100%;">
                <label>Departamento</label>
                <select name="department" onchange="this.form.submit()">
                    @foreach($departments as $key => $label)
                        <option value="{{ $key }}" @selected(request('department', '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div style="display:flex; align-items:flex-end; margin-left:auto;">
            <a href="{{ route('users.create') }}" class="btn btn-primary" style="margin-bottom:1px;">Nuevo usuario</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Departamento</th>
                    <th>Permisos extra</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td style="text-transform:uppercase;">{{ $u->role }}</td>
                        <td>{{ $u->department ? ucfirst($u->department) : '-' }}</td>
                        <td>{{ count($u->grantedModules()) > 0 ? implode(', ', array_map(fn ($module) => $moduleOptions[$module] ?? $module, $u->grantedModules())) : 'Sin permisos extra' }}</td>
                        <td>{{ $u->active ? 'Si' : 'No' }}</td>
                        <td>
                            <div class="row" style="justify-content:flex-end; gap:8px;">
                                <a class="btn btn-secondary" href="{{ route('users.index', array_filter(['selected_user' => $u->id, 'department' => request('department', ''), 'page' => $users->currentPage()])) }}">Permisos</a>
                                <a class="btn btn-secondary" href="{{ route('users.edit', $u) }}?page={{ $users->currentPage() }}">Editar</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px;">{{ $users->links() }}</div>
</div>

@if($selectedUser)
    <div class="backdrop" id="permissionsBackdrop" aria-hidden="false" role="dialog" aria-modal="true" aria-labelledby="permissionsTitle">
        <div class="modal" role="document" style="width:min(860px, 94vw);">
            <header>
                <strong id="permissionsTitle">Permisos de {{ $selectedUser->name }}</strong>
                <a
                    href="{{ route('users.index', array_filter(['department' => request('department', ''), 'page' => request('page', $users->currentPage())])) }}"
                    class="close-x"
                    aria-label="Cerrar"
                    style="text-decoration:none;"
                >×</a>
            </header>
            <div class="content">
                <p style="margin:0 0 16px; color:#6b7280;">Asigna modulos adicionales sobre su acceso base por departamento.</p>
                <form method="POST" action="{{ route('users.permissions', $selectedUser) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="page" value="{{ request('page', $users->currentPage()) }}">
                    <input type="hidden" name="department" value="{{ request('department', '') }}">
                    <div class="grid grid-3">
                        @foreach($moduleOptions as $moduleKey => $label)
                            <label style="display:flex; align-items:center; gap:10px; margin:0; padding:14px 16px; border:1px solid #d9e3dd; border-radius:16px; background:#fff; text-transform:none; letter-spacing:0; color:#173629;">
                                <input type="checkbox" name="module_permissions[]" value="{{ $moduleKey }}" {{ in_array($moduleKey, old('module_permissions', $selectedUser->grantedModules()), true) ? 'checked' : '' }}>
                                <span style="font-weight:700;">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="actions" style="margin-top:18px;">
                        <a class="btn btn-secondary" href="{{ route('users.index', array_filter(['department' => request('department', ''), 'page' => request('page', $users->currentPage())])) }}">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar permisos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
