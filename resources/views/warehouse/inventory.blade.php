@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin:0">Inventario de almacén</h2>
    <p style="margin:6px 0 0; color:#6b7280;">Existencias exclusivas de <strong>{{ $selectedCostCenter->name }}</strong>.</p>
</div>

<div class="card">
    <form method="GET" action="{{ route('warehouse.inventory') }}" role="search" style="display:flex; align-items:end; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
        <div style="flex:0 1 320px;">
            <label for="inventoryCostCenter" style="display:block; font-weight:700; margin-bottom:6px;">Centro de costos</label>
            <select id="inventoryCostCenter" name="cost_center_id" onchange="this.form.submit()">
                @foreach($costCenters as $costCenter)
                    <option value="{{ $costCenter->id }}" @selected($selectedCostCenter->id === $costCenter->id)>{{ $costCenter->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1 1 280px;">
            <label for="inventorySearch" style="display:block; font-weight:700; margin-bottom:6px;">Buscar en inventario</label>
            <div style="position:relative;">
                <i class="bi bi-search" aria-hidden="true" style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#6b7280;"></i>
                <input id="inventorySearch" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Clave, material o características" style="width:100%; padding-left:38px;">
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i><span>Buscar</span></button>
        @if($search !== '')
            <a class="btn btn-secondary" href="{{ route('warehouse.inventory', ['cost_center_id' => $selectedCostCenter->id]) }}"><i class="bi bi-x-lg" aria-hidden="true"></i><span>Limpiar</span></a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Material</th>
                    <th>Características</th>
                    <th>Ubicaciones registradas</th>
                    <th>Cantidad disponible</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $material)
                    <tr>
                        <td>{{ $material->clave ?? 'Sin clave' }}</td>
                        <td>
                            <div style="font-weight:800;">{{ $material->name }}</div>
                            @if($material->latestMaterialExit?->status)
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:7px;">
                                    @if($material->latestMaterialExit->status === 'en_curso')
                                        <span class="badge" style="background:#fff4cc; color:#735c00; border:1px solid #e5c84b;">En curso</span>
                                        @if(auth()->user()?->canManageOwnedDepartment('almacen'))
                                            <form method="POST" action="{{ route('warehouse.material-exits.delivered', $material->latestMaterialExit) }}" style="margin:0;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="cost_center_id" value="{{ $selectedCostCenter->id }}">
                                                <button class="btn btn-primary" type="submit" style="min-height:32px; padding:5px 10px;">Recibido</button>
                                            </form>
                                        @endif
                                    @elseif($material->latestMaterialExit->status === 'entregado')
                                        <span class="badge" style="background:#e8f5ee; color:#16603d; border:1px solid #8bc5a6;">Entregado</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @forelse(($material->characteristics ?? []) as $characteristic)
                                <span class="badge text-bg-light" style="margin:2px; border:1px solid rgba(16, 52, 37, .12);">{{ $characteristic }}</span>
                            @empty
                                <span class="text-muted">Sin características</span>
                            @endforelse
                        </td>
                        <td>
                            @php($locationNames = $material->entryMaterials->pluck('location.name')->filter()->unique()->sort()->values())
                            @forelse($locationNames as $locationName)
                                <span class="badge text-bg-light" style="margin:2px; border:1px solid rgba(16, 52, 37, .12);">{{ $locationName }}</span>
                            @empty
                                <span class="text-muted">Sin ubicación</span>
                            @endforelse
                        </td>
                                                <td>
                            @php($stockQuantity = (float) $material->stock_quantity)
                            @if($stockQuantity <= 0)
                                <strong style="color:#b91c1c;">Sin existencias</strong>
                            @else
                                <strong>{{ number_format($stockQuantity, 2) }}</strong>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">{{ $search !== '' ? 'No se encontraron materiales con ese criterio' : 'Sin materiales registrados' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px;">{{ $materials->links() }}</div>
</div>
@endsection
