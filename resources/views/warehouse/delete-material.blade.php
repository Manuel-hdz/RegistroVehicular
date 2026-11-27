@if(auth()->user()?->canDeleteAnyRecord())
    <form method="POST" action="{{ route('warehouse.materials.destroy', $deletableMaterial) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('¿Eliminar este material de TODOS los centros de costos? Se eliminarán sus entradas, salidas, vínculos con reparaciones y folios de entrada que queden vacíos. Esta acción es permanente.');">
        @csrf
        @method('DELETE')
        @if(isset($selectedCostCenter) || request()->filled('cost_center_id'))
            <input type="hidden" name="cost_center_id" value="{{ $selectedCostCenter->id ?? request('cost_center_id') }}">
        @endif
        <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-trash" aria-hidden="true"></i> Eliminar material</button>
    </form>
@endif
