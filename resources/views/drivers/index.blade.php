@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Conductores</h2>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">Nuevo Conductor</a>
    </div>
</div>

<div class="card">
    @if ($drivers->total() > 0)
        <div style="margin-bottom:10px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $drivers->firstItem() }} a {{ $drivers->lastItem() }} de {{ $drivers->total() }} resultados
            @if ($drivers->hasPages())
                <span> · Página {{ $drivers->currentPage() }} de {{ $drivers->lastPage() }} ·
                @if ($drivers->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $drivers->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($drivers->hasMorePages())
                    <a href="{{ $drivers->nextPageUrl() }}">Siguiente</a>
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
                <th>Número</th>
                <th>Licencia</th>
                <th>Activo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $d)
                <tr>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->employee_number }}</td>
                    <td>{{ $d->license }}</td>
                    <td>{{ $d->active ? 'Sí' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('drivers.edit', $d) }}?page={{ $drivers->currentPage() }}">Editar</a>
                        @auth
                            @if(auth()->user()->role === 'superadmin')
                                <form action="{{ route('drivers.destroy', $d) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar conductor?');">
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
    @if ($drivers->total() > 0)
        <div style="margin-top:12px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $drivers->firstItem() }} a {{ $drivers->lastItem() }} de {{ $drivers->total() }} resultados
        </div>
        @if ($drivers->hasPages())
            <div style="margin-top:6px; color:#555; font-size:14px; text-align:right;">
                Página {{ $drivers->currentPage() }} de {{ $drivers->lastPage() }} ·
                @if ($drivers->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $drivers->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($drivers->hasMorePages())
                    <a href="{{ $drivers->nextPageUrl() }}">Siguiente</a>
                @else
                    <span style="opacity:.6;">Siguiente</span>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
