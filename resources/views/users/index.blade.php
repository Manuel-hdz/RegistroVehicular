@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Usuarios</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Nuevo Usuario</a>
    </div>
</div>

<div class="card">
    <form method="GET" class="row" style="gap:10px; align-items:end; justify-content:flex-end; margin-bottom:10px;">
        <div>
            <label>Departamento</label>
            <select name="department">
                @foreach(($departments ?? [''=>'Todos']) as $k=>$label)
                    <option value="{{ $k }}" @selected(request('department','')===$k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button class="btn btn-secondary" type="submit">Filtrar</button>
            <a class="btn btn-link" href="{{ route('users.index') }}">Limpiar</a>
        </div>
    </form>
    @if ($users->total() > 0)
        <div style="margin-bottom:10px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} resultados
            @if ($users->hasPages())
                <span> · Página {{ $users->currentPage() }} de {{ $users->lastPage() }} ·
                @if ($users->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}">Siguiente</a>
                @else
                    <span style="opacity:.6;">Siguiente</span>
                @endif
                </span>
            @endif
        </div>
    @endif
    <table>
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Departamento</th>
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
                <td>{{ $u->department ? ucfirst($u->department) : '—' }}</td>
                <td>{{ $u->active ? 'Sí' : 'No' }}</td>
                <td>
                    <a class="btn btn-secondary" href="{{ route('users.edit', $u) }}?page={{ $users->currentPage() }}">Editar</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if ($users->total() > 0)
        <div style="margin-top:12px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} resultados
        </div>
        @if ($users->hasPages())
            <div style="margin-top:6px; color:#555; font-size:14px; text-align:right;">
                Página {{ $users->currentPage() }} de {{ $users->lastPage() }} ·
                @if ($users->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}">Siguiente</a>
                @else
                    <span style="opacity:.6;">Siguiente</span>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
