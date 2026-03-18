@extends('layouts.app')

@section('content')
@php
    $importedType = session('imported_type');
    $importErrors = session('import_errors');
@endphp

<div class="card">
    <h2 style="margin:0;">Cargas masivas</h2>
    <p style="margin:8px 0 0; color:#4b5563;">
        Importa archivos CSV para registrar personal, vehiculos y refacciones en bloque. Cada tarjeta incluye su plantilla descargable.
    </p>
</div>

<div class="grid grid-2">
    @foreach($imports as $import)
        <div class="card">
            <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
                <div>
                    <h3 style="margin:0;">{{ $import['label'] }}</h3>
                    <p style="margin:8px 0 0; color:#4b5563;">{{ $import['description'] }}</p>
                </div>
                <a class="btn btn-secondary btn-sm" href="{{ route('bulk-imports.template', $import['key']) }}">
                    <i class="bi bi-download"></i>Plantilla CSV
                </a>
            </div>

            <div style="margin-top:14px; display:flex; flex-wrap:wrap; gap:8px;">
                @foreach($import['headers'] as $header)
                    <span style="display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; background:#f3f7f4; border:1px solid #d6e2db; color:#1f4033; font-size:12px; font-weight:800;">
                        {{ $header }}
                    </span>
                @endforeach
            </div>

            <details style="margin-top:14px;">
                <summary style="cursor:pointer; font-weight:800; color:#14532d;">Ver ejemplo de plantilla</summary>
                <div class="table-responsive" style="margin-top:10px;">
                    <table class="table align-middle" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                @foreach($import['headers'] as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($import['examples'] as $exampleRow)
                                <tr>
                                    @foreach($exampleRow as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <div style="margin-top:14px;">
                @foreach($import['notes'] as $note)
                    <div style="display:flex; gap:8px; align-items:flex-start; color:#4b5563; margin-bottom:6px;">
                        <i class="bi bi-info-circle" style="color:#0f766e;"></i>
                        <span>{{ $note }}</span>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('bulk-imports.store', $import['key']) }}" enctype="multipart/form-data" style="margin-top:16px;">
                @csrf
                <div class="grid" style="gap:12px;">
                    <div>
                        <label>Archivo CSV de {{ strtolower($import['label']) }}</label>
                        <input type="file" name="csv_file" accept=".csv,.txt,text/csv" required>
                    </div>
                    <div class="row" style="justify-content:flex-end; margin:0;">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-file-earmark-arrow-up"></i>Importar {{ $import['label'] }}
                        </button>
                    </div>
                </div>
            </form>

            @if($importedType === $import['key'] && is_array($importErrors) && count($importErrors) > 0)
                <div style="margin-top:16px; border:1px solid #fed7aa; background:#fff7ed; border-radius:16px; padding:14px 16px;">
                    <strong style="display:block; color:#9a3412;">Lineas con error</strong>
                    <ul style="margin:10px 0 0; padding-left:18px; color:#9a3412;">
                        @foreach($importErrors as $errorLine)
                            <li>{{ $errorLine }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection
