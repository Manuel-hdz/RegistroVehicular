@extends('layouts.app')

@section('content')
@include('vehicles.partials.vtype-selector-styles')

<div class="card">
    <h2 style="margin-top:0">Editar vehiculo</h2>
    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="grid grid-3" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="page" value="{{ request('page') }}">
        @include('vehicles.partials.form-fields', [
            'vehicle' => $vehicle,
            'idPrefix' => 'edit_vtype',
        ])
        <div style="grid-column: 1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('vehicles.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
    </form>
</div>
@endsection
