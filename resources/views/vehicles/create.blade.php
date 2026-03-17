@extends('layouts.app')

@section('content')
@include('vehicles.partials.vtype-selector-styles')

<div class="card">
    <h2 style="margin-top:0">Nuevo vehiculo</h2>
    <form method="POST" action="{{ route('vehicles.store') }}" class="grid grid-3" enctype="multipart/form-data">
        @csrf
        @include('vehicles.partials.form-fields', [
            'vehicle' => null,
            'idPrefix' => 'create_vtype',
        ])
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('vehicles.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection
