@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin:0;">Cargar documentos</h2>
    <p style="margin:8px 0 0; color:#4b5563;">
        Carga un archivo CSV para registrar asistencia de varios empleados en una sola operacion.
    </p>
</div>

<div class="card">
    <h3 style="margin-top:0;">Formato esperado del CSV</h3>
    <p style="margin:8px 0 10px; color:#4b5563;">
        Encabezados recomendados: <strong>nombre, clave, fecha</strong>.
        Tambien se acepta el archivo sin encabezado, en ese orden de columnas.
    </p>
    <div class="row" style="margin-bottom:12px;">
        <a class="btn btn-secondary" href="{{ route('cardex.import.template') }}">Descargar plantilla CSV</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" style="margin-bottom:0;">
            <thead>
                <tr>
                    <th>nombre</th>
                    <th>clave</th>
                    <th>fecha</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Luis Hernandez Garcia</td>
                    <td>A</td>
                    <td>2026-03-11</td>
                </tr>
                <tr>
                    <td>Carla Lopez Ramirez</td>
                    <td>F</td>
                    <td>11/03/2026</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0;">Carga masiva</h3>
    <form method="POST" action="{{ route('cardex.import.store') }}" enctype="multipart/form-data" class="grid grid-2">
        @csrf
        <div>
            <label>Archivo CSV</label>
            <input type="file" name="csv_file" accept=".csv,.txt,text/csv" required>
        </div>
        <div>
            <label>Claves permitidas</label>
            <div style="border:2px solid #d1d5db; border-radius:8px; padding:10px 12px; background:#fff;">
                @foreach($codes as $code => $label)
                    <span style="display:inline-block; margin:2px 8px 2px 0; font-weight:700;">{{ $code }}</span>
                    <span style="margin-right:12px; color:#4b5563;">{{ $label }}</span>
                @endforeach
            </div>
        </div>
        <div style="grid-column: 1/-1;" class="row">
            <button class="btn btn-primary" type="submit">Importar CSV</button>
            <a class="btn btn-secondary" href="{{ route('cardex.index') }}">Ir a Kardex</a>
        </div>
    </form>
</div>

@if(session('import_errors') && is_array(session('import_errors')))
    <div class="card">
        <h3 style="margin-top:0;">Lineas con error</h3>
        <p style="margin:8px 0 10px; color:#9a3412;">
            Algunas lineas no se importaron. Revisa el detalle:
        </p>
        <ul style="margin:0;">
            @foreach(session('import_errors') as $errorLine)
                <li>{{ $errorLine }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
