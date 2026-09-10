@extends('layouts.app')

@section('title','Fotos masivas')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3">
                <h1 class="h5 mb-0">Carga masiva de fotos</h1>
                <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <p class="mb-1">Revisa las fotos seleccionadas:</p>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('resultado_fotos'))
                @php($resultado = session('resultado_fotos'))
                <div class="alert alert-info">
                    <p class="mb-1">Fotos guardadas: {{ $resultado['guardadas'] }}</p>
                    @if(! empty($resultado['sin_equipo']))
                        <p class="mb-1">Fotos sin unidad encontrada:</p>
                        <ul class="mb-0">
                            @foreach($resultado['sin_equipo'] as $archivo)
                                <li>{{ $archivo }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <form action="{{ route('empleados.fotos-masivas.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label for="fotos" class="form-label">Fotos de unidades</label>
                        <input
                            type="file"
                            class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror"
                            id="fotos"
                            name="fotos[]"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            required
                        >
                        <div class="form-text">El nombre de cada foto debe incluir la clave de la unidad para asociarla automaticamente.</div>
                        @error('fotos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('fotos.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 d-grid d-sm-flex gap-2">
                    <button type="submit" class="btn btn-primary">Subir fotos</button>
                    <a href="{{ route('empleados.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
