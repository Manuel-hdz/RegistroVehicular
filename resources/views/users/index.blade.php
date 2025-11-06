@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Usuarios</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Nuevo Usuario</a>
    </div>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
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
                <td>{{ $u->active ? 'Sí' : 'No' }}</td>
                <td>
                    <a class="btn btn-secondary" href="{{ route('users.edit', $u) }}">Editar</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $users->links() }}</div>
</div>
@endsection
