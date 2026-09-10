@extends('layouts.app')

@section('title','Carga masiva')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3">
                <h1 class="h5 mb-0">Carga masiva de archivos</h1>
                <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <p class="mb-1">Revisa los archivos seleccionados:</p>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('resultado_carga'))
                @php($resultado = session('resultado_carga'))
                <div class="alert alert-info">
                    <p class="mb-1">Archivos guardados: {{ $resultado['guardados'] }}</p>
                    @if(! empty($resultado['sin_equipo']))
                        <p class="mb-1">Archivos sin unidad encontrada:</p>
                        <ul class="mb-0">
                            @foreach($resultado['sin_equipo'] as $archivo)
                                <li>{{ $archivo }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <form action="{{ route('empleados.carga-masiva.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="tipo_documento" class="form-label">Tipo de documento</label>
                        <select id="tipo_documento" name="tipo_documento" class="form-select @error('tipo_documento') is-invalid @enderror" required>
                            <option value="poliza" @selected(old('tipo_documento') === 'poliza')>Polizas</option>
                            <option value="factura" @selected(old('tipo_documento') === 'factura')>Facturas</option>
                        </select>
                        @error('tipo_documento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="archivos" class="form-label">Archivos PDF</label>
                        <input
                            type="file"
                            class="form-control @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror"
                            id="archivos"
                            name="archivos[]"
                            accept="application/pdf"
                            multiple
                            required
                        >
                        <div class="form-text">El nombre de cada archivo debe incluir la clave de la unidad para asociarlo automaticamente.</div>
                        @error('archivos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('archivos.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 d-grid d-sm-flex gap-2">
                    <button type="submit" class="btn btn-primary">Subir archivos</button>
                    <a href="{{ route('empleados.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
