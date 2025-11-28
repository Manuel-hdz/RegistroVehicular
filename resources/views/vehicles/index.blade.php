@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Vehículos</h2>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">Nuevo vehículo</a>
    </div>
</div>

@push('head-pre')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.pager) {
                $.fn.dataTable.ext.pager.numbers_length = 5;
            }
            $('#vehiclesTable').DataTable({
                pageLength: 25,
                order: [],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        });
    </script>
@endpush

<div class="card">
    <div class="table-responsive">
    <table id="vehiclesTable" class="table table-striped align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Identificador</th>
                <th>Modelo</th>
                <th>Año</th>
                <th>Activo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $v)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $v->identifier }}</td>
                    <td>{{ $v->model }}</td>
                    <td>{{ $v->year }}</td>
                    <td>{{ $v->active ? 'Sí' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('vehicles.edit', $v) }}" title="Editar" aria-label="Editar">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </a>
                        @auth
                            @if(auth()->user()->role === 'superadmin')
                                <form action="{{ route('vehicles.destroy', $v) }}" method="POST" style="display:inline;" onsubmit="return confirm('Â¿Eliminar vehículo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-secondary" type="submit" title="Eliminar" aria-label="Eliminar">
                                        <i class="bi bi-trash" aria-hidden="true" style="color:#dc2626;"></i>
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
@push('head-pre')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush


