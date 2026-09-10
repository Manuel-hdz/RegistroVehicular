@extends('layouts.app')

@section('title','Crear unidad')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h5">Crear unidad</h1>

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

            <form action="{{ route('empleados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @include('Empleados._form')

                <div class="mt-3 d-grid d-sm-flex gap-2">
                    {{-- Botones finales del formulario. --}}
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection
