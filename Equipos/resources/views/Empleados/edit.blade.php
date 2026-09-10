@extends('layouts.app')

@section('title', $tituloFormulario ?? 'Editar unidad')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h5">{{ $tituloFormulario ?? 'Editar unidad' }}</h1>

            {{-- Lista los errores si algun dato del formulario no pasa la validacion. --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <p class="mb-1">Revisa los datos del formulario:</p>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($empleado->tieneFoto() && ! $empleado->es_easter_egg)
                <form id="deletePhotoForm" action="{{ route('empleados.foto.destroy', $empleado) }}" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

            <form action="{{ $formAction ?? route('empleados.update', $empleado) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('Empleados._form', ['empleado' => $empleado])

                <div class="mt-3 d-grid d-sm-flex gap-2">
                    {{-- Botones finales del formulario. --}}
                    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Actualizar' }}</button>
                    <a href="{{ $backUrl ?? route('empleados.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection
