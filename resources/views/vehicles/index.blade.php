@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Vehículos</h2>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">Nuevo Vehículo</a>
    </div>
</div>

<div class="card">
    @if ($vehicles->total() > 0)
        <div style="margin-bottom:10px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $vehicles->firstItem() }} a {{ $vehicles->lastItem() }} de {{ $vehicles->total() }} resultados
            @if ($vehicles->hasPages())
                <span> · Página {{ $vehicles->currentPage() }} de {{ $vehicles->lastPage() }} ·
                @if ($vehicles->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $vehicles->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($vehicles->hasMorePages())
                    <a href="{{ $vehicles->nextPageUrl() }}">Siguiente</a>
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
                        <a class="btn btn-secondary" href="{{ route('vehicles.edit', $v) }}?page={{ $vehicles->currentPage() }}">Editar</a>
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
    @if ($vehicles->total() > 0)
        <div style="margin-top:12px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $vehicles->firstItem() }} a {{ $vehicles->lastItem() }} de {{ $vehicles->total() }} resultados
        </div>
        @if ($vehicles->hasPages())
            <div style="margin-top:6px; color:#555; font-size:14px; text-align:right;">
                Página {{ $vehicles->currentPage() }} de {{ $vehicles->lastPage() }} ·
                @if ($vehicles->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $vehicles->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($vehicles->hasMorePages())
                    <a href="{{ $vehicles->nextPageUrl() }}">Siguiente</a>
                @else
                    <span style="opacity:.6;">Siguiente</span>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
