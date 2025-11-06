@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Vehículos</h2>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">Nuevo Vehículo</a>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Identificador</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Año</th>
                <th>Activo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $v)
                <tr>
                    <td>{{ $v->identifier }}</td>
                    <td>{{ $v->plate }}</td>
                    <td>{{ $v->model }}</td>
                    <td>{{ $v->year }}</td>
                    <td>{{ $v->active ? 'Sí' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('vehicles.edit', $v) }}">Editar</a>
                        @auth
                            @if(auth()->user()->role === 'superadmin')
                                <form action="{{ route('vehicles.destroy', $v) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar vehículo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-secondary" type="submit">Eliminar</button>
                                </form>
                            @endif
                        @endauth
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $vehicles->links() }}</div>
</div>
@endsection
