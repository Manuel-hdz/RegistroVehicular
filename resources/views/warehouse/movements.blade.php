@extends('layouts.app')

@section('content')
@push('head')
<style>
    .warehouse-movement-layout {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(320px, 1.1fr);
        gap: 18px;
        align-items: start;
    }
    .warehouse-action-panel {
        display: grid;
        gap: 14px;
    }
    .warehouse-action-panel .btn {
        width: 100%;
        justify-content: flex-start;
        min-height: 54px;
        padding-inline: 1.15rem;
    }
    .warehouse-kpis {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .warehouse-kpi {
        border: 1px solid rgba(16, 52, 37, .08);
        border-radius: 16px;
        background: #fff;
        padding: 14px;
    }
    .warehouse-kpi span {
        display: block;
        color: #6d8178;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .warehouse-kpi strong {
        display: block;
        margin-top: 4px;
        color: #16362a;
        font-size: 2rem;
        line-height: 1;
    }
    .warehouse-history {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 14px;
    }
    .warehouse-history-card {
        border: 1px solid rgba(16, 52, 37, .08);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }
    .warehouse-history-card h3 {
        margin: 0;
        padding: 12px 14px;
        font-size: .96rem;
        border-bottom: 1px solid rgba(16, 52, 37, .08);
        background: #f6faf7;
    }
    .warehouse-history-list {
        display: grid;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .warehouse-history-list li {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid rgba(16, 52, 37, .06);
    }
    .warehouse-history-list li:last-child {
        border-bottom: 0;
    }
    .warehouse-history-title {
        color: #16362a;
        font-weight: 800;
        line-height: 1.25;
    }
    .warehouse-history-id {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:64px;
        padding:7px 12px;
        border-radius:10px;
        background:#eef7f2;
        color:#006847;
        font-weight:900;
        border:1px solid rgba(0, 104, 71, .18);
    }
    .warehouse-history-id:hover,
    .warehouse-history-id:focus {
        background:#dff1e7;
        color:#004a33;
    }
    .warehouse-record-row-highlight > * {
        background:#fff3bf !important;
        transition:background-color .3s ease;
    }
    .warehouse-history-meta {
        color: #6d8178;
        font-size: .82rem;
        font-weight: 700;
    }
    .warehouse-history-date {
        color: #4a5f56;
        font-size: .82rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .warehouse-charts {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
    }
    .warehouse-chart-card {
        min-height: 260px;
    }
    .warehouse-chart-card h3 {
        margin: 0 0 12px;
        font-size: 1rem;
    }
    .warehouse-chart-canvas {
        width: 100%;
        height: 190px;
        display: block;
    }
    .warehouse-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
        color: #4a5f56;
        font-size: .9rem;
        font-weight: 700;
    }
    .warehouse-legend span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .warehouse-swatch {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        display: inline-block;
    }
    .warehouse-generated-id {
        border: 1px dashed rgba(0, 104, 71, .34);
        border-radius: 16px;
        background: #f6faf7;
        padding: 12px 14px;
        color: #16362a;
        font-weight: 800;
        min-height: 48px;
        display: flex;
        align-items: center;
    }
    .warehouse-characteristics {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }
    .entry-material-search {
        position: relative;
    }
    .entry-material-suggestions {
        position: absolute;
        z-index: 1080;
        top: calc(100% + 6px);
        right: 0;
        left: 0;
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid rgba(16, 52, 37, .16);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 16px 36px rgba(16, 52, 37, .16);
        padding: 6px;
    }
    .entry-material-suggestion {
        display: block;
        width: 100%;
        border: 0;
        border-radius: 10px;
        background: transparent;
        padding: 10px 12px;
        color: #16362a;
        text-align: left;
    }
    .entry-material-suggestion:hover,
    .entry-material-suggestion:focus {
        background: #eef7f2;
        outline: none;
    }
    .entry-material-suggestion strong,
    .entry-material-suggestion small {
        display: block;
    }
    .entry-material-suggestion small,
    .entry-material-no-results {
        margin-top: 3px;
        color: #6d8178;
        font-size: .78rem;
        font-weight: 700;
    }
    .entry-material-no-results {
        padding: 10px 12px;
    }
    .warehouse-characteristic-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(130px, .7fr) auto auto;
        gap: 8px;
        align-items: center;
    }
    .warehouse-characteristic-row input {
        min-width: 0;
    }
    .warehouse-add-characteristic {
        width: 42px;
        min-width: 42px;
        height: 42px;
        min-height: 42px;
        padding: 0;
        border-radius: 999px;
    }
    .warehouse-remove-characteristic {
        width: 42px;
        min-width: 42px;
        height: 42px;
        min-height: 42px;
        padding: 0;
        border-radius: 999px;
    }
    @media (max-width: 991.98px) {
        .warehouse-movement-layout,
        .warehouse-history,
        .warehouse-charts {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    @media (max-width: 575.98px) {
        .warehouse-kpis {
            grid-template-columns: minmax(0, 1fr);
        }
        .warehouse-characteristic-row {
            grid-template-columns: minmax(0, 1fr);
        }
        .warehouse-history-list li {
            grid-template-columns: minmax(0, 1fr);
        }
        .warehouse-history-date {
            white-space: normal;
        }
        .warehouse-add-characteristic,
        .warehouse-remove-characteristic {
            width: 100%;
        }
        .warehouse-chart-canvas {
            height: 180px;
        }
    }
</style>
@endpush

<div class="warehouse-movement-layout">
    <div>
        <div class="card">
            <h2 style="margin:0;">Entradas y salidas</h2>
            <p style="margin:6px 0 0;">Almacén separado de <strong>{{ $selectedCostCenter->name }}</strong>.</p>
            <form method="GET" action="{{ route('warehouse.movements') }}" style="margin-top:14px; max-width:420px;">
                <label for="warehouseCostCenter">Centro de costos</label>
                <select id="warehouseCostCenter" name="cost_center_id" onchange="this.form.submit()">
                    @foreach($costCenters as $costCenter)
                        <option value="{{ $costCenter->id }}" @selected($selectedCostCenter->id === $costCenter->id)>{{ $costCenter->name }}</option>
                    @endforeach
                </select>
            </form>
            @if(auth()->user()?->canManageWarehouseLocations())
                <a class="btn btn-secondary" href="{{ route('warehouse.locations.index', ['cost_center_id' => $selectedCostCenter->id]) }}" style="margin-top:12px;">
                    <i class="bi bi-geo-alt"></i>
                    <span>Administrar ubicaciones</span>
                </a>
            @endif
        </div>

        <div class="card warehouse-action-panel">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#entryTypeModal">
                <i class="bi bi-box-arrow-in-down-left"></i>
                <span>Entradas</span>
            </button>
            <button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#materialExitModal">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Salidas</span>
            </button>
            <a class="btn btn-secondary" href="{{ route('warehouse.inventory', ['cost_center_id' => $selectedCostCenter->id]) }}">
                <i class="bi bi-boxes"></i>
                <span>Inventario</span>
            </a>
            <a class="btn btn-secondary" href="{{ route('warehouse.reports.index', ['cost_center_id' => $selectedCostCenter->id]) }}">
                <i class="bi bi-clipboard-data"></i>
                <span>Reportes por fecha</span>
            </a>
        </div>

        <div class="warehouse-history">
            <div class="warehouse-history-card">
                <h3>Ultimas 10 entradas</h3>
                <ul class="warehouse-history-list">
                    @forelse($latestEntries as $entry)
                        <li>
                            <a class="warehouse-history-id" href="#entriesRecordModal" data-bs-toggle="modal" data-bs-target="#entriesRecordModal" data-record-row="entry-record-{{ $entry->id }}" aria-label="Ver entrada {{ $entry->entry_key ?? $entry->id }} en la tabla completa">{{ $entry->entry_key ?? '#'.$entry->id }}</a>
                        </li>
                    @empty
                        <li>
                            <div class="warehouse-history-meta">Sin entradas registradas</div>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="warehouse-history-card">
                <h3>Ultimas 10 salidas</h3>
                <ul class="warehouse-history-list">
                    @forelse($latestDepartures as $departure)
                        <li>
                            <a class="warehouse-history-id" href="#exitsRecordModal" data-bs-toggle="modal" data-bs-target="#exitsRecordModal" data-record-row="exit-record-{{ $departure->id }}" aria-label="Ver salida {{ $departure->voucher_number ?? $departure->id }} en la tabla completa">{{ $departure->voucher_number ?? '#'.$departure->id }}</a>
                        </li>
                    @empty
                        <li>
                            <div class="warehouse-history-meta">Sin salidas registradas</div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="warehouse-charts">
        <div class="card warehouse-chart-card">
            <h3>Entradas contra salidas semanalmente</h3>
            <canvas id="weeklyPieChart" class="warehouse-chart-canvas"></canvas>
            <div class="warehouse-legend">
                <span><i class="warehouse-swatch" style="background:#006847;"></i>Entradas</span>
                <span><i class="warehouse-swatch" style="background:#FFCD11;"></i>Salidas</span>
            </div>
        </div>

        <div class="card warehouse-chart-card">
            <h3>Entradas contra salidas mensualmente</h3>
            <canvas id="monthlyBarChart" class="warehouse-chart-canvas"></canvas>
            <div class="warehouse-legend">
                <span><i class="warehouse-swatch" style="background:#006847;"></i>Entradas</span>
                <span><i class="warehouse-swatch" style="background:#FFCD11;"></i>Salidas</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="entryTypeModal" tabindex="-1" aria-labelledby="entryTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="entryTypeModalLabel">Menu de entradas</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <h4 style="margin:0 0 12px; font-size:1rem; font-weight:800; color:#16362a;">Tipo de entradas</h4>
                <div class="warehouse-action-panel">
                    <button class="btn btn-primary" type="button" data-entry-type="Pase de salida">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <span>Pase de salida</span>
                    </button>
                    <button class="btn btn-secondary" type="button" data-entry-type="Compra">
                        <i class="bi bi-cash-coin"></i>
                        <span>Compra</span>
                    </button>
                </div>

                <div style="margin-top:18px; padding-top:16px; border-top:1px solid rgba(16, 52, 37, .10);">
                    <h4 style="margin:0 0 12px; font-size:1rem; font-weight:800; color:#16362a;">Consultar</h4>
                    <div class="warehouse-action-panel">
                        <button class="btn btn-outline-primary" type="button" data-bs-target="#entriesRecordModal" data-bs-toggle="modal">
                            <i class="bi bi-journal-text"></i>
                            <span>Registro de entradas</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="entriesRecordModal" tabindex="-1" aria-labelledby="entriesRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="entriesRecordModalLabel">Tabla completa de entradas</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Folio</th>
                                <th>Material</th>
                                <th>Características</th>
                                <th>Ubicación</th>
                                <th>Cantidad</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Registró</th>
                                <th>Factura</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registeredEntries as $entry)
                                <tr id="entry-record-{{ $entry->id }}">
                                    <td><strong>#{{ $entry->id }}</strong></td>
                                    <td>{{ $entry->entry_key ?? 'Sin folio' }}</td>
                                    <td>
                                        @foreach($entry->materials as $entryMaterial)
                                            <div>{{ $entryMaterial->part?->name ?? 'Material sin nombre' }} &middot; {{ $entryMaterial->part?->clave ?? 'Sin clave' }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($entry->materials as $entryMaterial)
                                            <div>{{ implode(', ', $entryMaterial->part?->characteristics ?? []) ?: 'Sin características' }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($entry->materials as $entryMaterial)
                                            <div>{{ $entryMaterial->location?->name ?? 'Sin ubicación' }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($entry->materials as $entryMaterial)
                                            <div>{{ number_format((float) $entryMaterial->quantity, 2) }}</div>
                                        @endforeach
                                    </td>
                                    <td>{{ $entry->entry_type ?? 'Sin tipo' }}</td>
                                    <td>{{ $entry->entry_date?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $entry->registeredBy?->name ?? $entry->registered_by_username ?? 'Registro previo' }}</td>
                                    <td>
                                        @if($entry->invoice_path)
                                            <a href="{{ asset('storage/' . $entry->invoice_path) }}" target="_blank" rel="noopener">Ver factura</a>
                                        @else
                                            <span class="warehouse-history-meta">Sin factura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Sin entradas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-target="#entryTypeModal" data-bs-toggle="modal">Regresar</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exitsRecordModal" tabindex="-1" aria-labelledby="exitsRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exitsRecordModalLabel">Tabla completa de salidas</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vale</th>
                                <th>Material</th>
                                <th>Características</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Despachó</th>
                                <th>Llevó</th>
                                <th>Destino</th>
                                <th>Responsable</th>
                                <th>Registró</th>
                                <th>Estatus</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registeredDepartures as $departure)
                                <tr id="exit-record-{{ $departure->id }}">
                                    <td><strong>#{{ $departure->id }}</strong></td>
                                    <td>{{ $departure->voucher_number ?? 'Sin vale' }}</td>
                                    <td>{{ $departure->part?->name ?? 'Material sin nombre' }}<br><small>{{ $departure->part?->clave ?? 'Sin clave' }}</small></td>
                                    <td>{{ implode(', ', $departure->part?->characteristics ?? []) ?: 'Sin características' }}</td>
                                    <td>{{ number_format((float) $departure->quantity, 2) }}</td>
                                    <td>{{ $departure->exit_date?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $departure->dispatched_by ?: $departure->source ?: 'Sin registrar' }}</td>
                                    <td>{{ $departure->carried_by ?: 'Sin registrar' }}</td>
                                    <td>{{ $departure->destination ?: 'Sin registrar' }}</td>
                                    <td>{{ $departure->responsible ?: 'Sin registrar' }}</td>
                                    <td>{{ $departure->registeredBy?->name ?? $departure->registered_by_username ?? 'Registro previo' }}</td>
                                    <td>{{ $departure->status === 'entregado' ? 'Entregado' : 'En curso' }}</td>
                                    <td>
                                        <a href="{{ route('warehouse.material-exits.voucher', ['warehouseMaterialExit' => $departure, 'cost_center_id' => $selectedCostCenter->id]) }}" target="_blank" rel="noopener">Ver vale</a>
                                        @if($departure->invoice_path)
                                            <br><a href="{{ asset('storage/' . $departure->invoice_path) }}" target="_blank" rel="noopener">Ver factura</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">Sin salidas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="materialEntryModal" tabindex="-1" aria-labelledby="materialEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('warehouse.materials.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cost_center_id" value="{{ $selectedCostCenter->id }}">
                <div class="modal-header">
                    <h3 class="modal-title" id="materialEntryModalLabel">Registrar entrada</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                                <div class="modal-body">
                    <input type="hidden" id="materialEntryType" name="entry_type" value="{{ old('entry_type') }}">
                    <div style="margin-top:14px;">
                        <label for="materialEntryDate">Fecha y hora de entrada</label>
                        <input id="materialEntryDate" type="datetime-local" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div id="invoiceUploadGroup" style="margin-top:14px;" hidden>
                        <label for="invoiceFile">Factura</label>
                        <input id="invoiceFile" type="file" name="invoice_file" accept="application/pdf,image/*,.pdf,.jpg,.jpeg,.png,.webp">
                        <div class="warehouse-history-meta" style="margin-top:6px;">Puedes subir PDF, JPG, PNG o WEBP.</div>
                    </div>
                    <div id="entryMaterialsList" class="warehouse-characteristics">
                        <div class="warehouse-entry-material" data-material-index="0">
                            <div>
                                <label>Nombre del material</label>
                                <div class="entry-material-search">
                                    <input class="entry-material-id" type="hidden" name="materials[0][part_id]">
                                    <input class="entry-material-name" name="materials[0][name]" required autocomplete="off" placeholder="Escribe para buscar materiales registrados" role="combobox" aria-autocomplete="list" aria-expanded="false">
                                    <div class="entry-material-suggestions" role="listbox" hidden></div>
                                </div>
                            </div>
                            <div style="margin-top:10px;">
                                <label>Cantidad</label>
                                <input class="entry-material-quantity" type="text" inputmode="decimal" name="materials[0][quantity]" value="1" required autocomplete="off">
                            </div>
                            <div style="margin-top:10px;">
                                <label>Ubicación</label>
                                <select class="entry-material-location" name="materials[0][warehouse_location_id]" required>
                                    <option value="">Selecciona una ubicación</option>
                                    @foreach($warehouseLocations as $location)
                                        <option value="{{ $location->id }}" @selected((int) old('materials.0.warehouse_location_id') === $location->id)>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-top:10px;">
                                <label>Caracteristicas</label>
                                <textarea class="entry-material-characteristics" name="materials[0][characteristics]" placeholder="Una por linea o separadas por coma"></textarea>
                            </div>
                            <div class="warehouse-generated-id entry-material-clave" style="margin-top:10px;">Escribe el nombre del material</div>
                        </div>
                    </div>
                    <button class="btn btn-secondary" type="button" id="addEntryMaterialBtn" style="margin-top:14px;">
                        <i class="bi bi-plus-circle"></i>
                        <span>Agregar otro material</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar entrada</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="materialExitModal" tabindex="-1" aria-labelledby="materialExitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('warehouse.material-exits.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cost_center_id" value="{{ $selectedCostCenter->id }}">
                <div class="modal-header">
                    <h3 class="modal-title" id="materialExitModalLabel">Menu de salidas</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <h4 style="margin:0 0 12px; font-size:1rem; font-weight:800; color:#16362a;">Tipo de salidas</h4>
                    <input type="hidden" id="exitPartId" name="part_id" value="{{ old('part_id') }}">
                    <div>
                        <label for="exitMaterialName">Nombre del material</label>
                        <div style="display:grid; grid-template-columns:minmax(0, 1fr) 44px; gap:8px; align-items:center;">
                            <input id="exitMaterialName" type="text" value="" autocomplete="off" placeholder="Escribe el nombre del material">
                            <button id="openInventorySelector" class="btn btn-secondary" type="button" title="Buscar en inventario" aria-label="Buscar en inventario" style="width:44px; min-width:44px; height:42px; padding:0; justify-content:center;">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitMaterialIdDisplay">Clave del material</label>
                        <input id="exitMaterialIdDisplay" type="text" value="" placeholder="Selecciona un material" readonly>
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitQuantity">Cantidad</label>
                        <input id="exitQuantity" type="text" inputmode="decimal" name="quantity" value="{{ old('quantity', 1) }}" autocomplete="off">
                        <div id="exitStockDisplay" class="warehouse-history-meta" style="margin-top:6px;">Selecciona un material para ver la existencia.</div>
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitDispatchedBy">Quien despacho</label>
                        <input id="exitDispatchedBy" type="text" name="dispatched_by" value="{{ old('dispatched_by') }}" autocomplete="off">
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitCarriedBy">Quien se lo lleva</label>
                        <input id="exitCarriedBy" type="text" name="carried_by" value="{{ old('carried_by') }}" autocomplete="off">
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitDestination">Destino</label>
                        <input id="exitDestination" type="text" name="destination" value="{{ old('destination') }}" autocomplete="off">
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitDate">Fecha y hora de salida</label>
                        <input id="exitDate" type="datetime-local" name="exit_date" value="{{ old('exit_date', now()->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitResponsible">Responsable</label>
                        <input id="exitResponsible" type="text" name="responsible" value="{{ old('responsible') }}" autocomplete="off">
                    </div>
                    <div style="margin-top:14px;">
                        <label for="exitInvoiceFile">Factura</label>
                        <input id="exitInvoiceFile" type="file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                        <div class="warehouse-history-meta" style="margin-top:6px;">Archivo PDF o imagen. Maximo 10 MB.</div>
                    </div>
                    <div class="warehouse-generated-id" style="margin-top:14px;">
                        Al guardar se generara un vale con estos datos.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Generar vale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="inventorySelectorModal" tabindex="-1" aria-labelledby="inventorySelectorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="inventorySelectorModalLabel">Seleccionar material del inventario</h3>
                <button id="closeInventorySelector" type="button" class="btn-close" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label for="inventoryMaterialSearch">Buscar por nombre</label>
                <input id="inventoryMaterialSearch" type="search" autocomplete="off" placeholder="Nombre del material">
                <div style="overflow-x:auto; margin-top:14px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:10px;">Material</th>
                                <th style="text-align:left; padding:10px;">Clave</th>
                                <th style="text-align:left; padding:10px;">Caracteristicas</th>
                                <th style="text-align:right; padding:10px;">Existencia</th>
                                <th style="width:54px;"></th>
                            </tr>
                        </thead>
                        <tbody id="inventoryMaterialRows">
                            @foreach($materialOptions as $material)
                                @php
                                    $characteristics = is_array($material->characteristics)
                                        ? implode(', ', $material->characteristics)
                                        : (string) $material->characteristics;
                                    $stock = (float) ($material->stock_quantity ?? 0);
                                @endphp
                                <tr class="inventory-material-row" data-name="{{ Illuminate\Support\Str::lower($material->name) }}">
                                    <td style="padding:10px; border-top:1px solid rgba(16,52,37,.08); font-weight:800;">{{ $material->name }}</td>
                                    <td style="padding:10px; border-top:1px solid rgba(16,52,37,.08);">{{ $material->clave }}</td>
                                    <td style="padding:10px; border-top:1px solid rgba(16,52,37,.08);">{{ $characteristics ?: 'Sin caracteristicas' }}</td>
                                    <td style="padding:10px; border-top:1px solid rgba(16,52,37,.08); text-align:right; font-weight:800;">{{ number_format($stock, 2) }}</td>
                                    <td style="padding:8px; border-top:1px solid rgba(16,52,37,.08);">
                                        <button class="btn btn-primary select-inventory-material" type="button" title="Seleccionar material" aria-label="Seleccionar {{ $material->name }}" data-id="{{ $material->id }}" data-name="{{ $material->name }}" data-clave="{{ $material->clave }}" data-stock="{{ number_format($stock, 2, '.', '') }}" {{ $stock <= 0 ? 'disabled' : '' }} style="width:40px; min-width:40px; height:40px; padding:0; justify-content:center;">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="inventoryNoResults" hidden>
                                <td colspan="5" style="padding:18px; text-align:center; color:#6d8178; font-weight:700;">No hay materiales con ese nombre.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button id="returnToExitModal" type="button" class="btn btn-secondary">Volver</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        var weekly = {
            entries: @json($weeklyEntries),
            departures: @json($weeklyDepartures)
        };
        var monthly = {
            labels: @json($monthlyLabels),
            entries: @json($monthlyEntries),
            departures: @json($monthlyDepartures)
        };
        var colors = {
            entries: '#006847',
            departures: '#FFCD11',
            text: '#203129',
            muted: '#6d8178',
            grid: '#dde7e1'
        };

        function prepareCanvas(canvas) {
            var ratio = window.devicePixelRatio || 1;
            var rect = canvas.getBoundingClientRect();
            canvas.width = Math.max(1, Math.floor(rect.width * ratio));
            canvas.height = Math.max(1, Math.floor(rect.height * ratio));
            var ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            return { ctx: ctx, width: rect.width, height: rect.height };
        }

        function drawEmpty(ctx, width, height) {
            ctx.fillStyle = colors.muted;
            ctx.font = '700 14px Manrope, Segoe UI, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Sin datos para mostrar', width / 2, height / 2);
        }

        function drawPie() {
            var canvas = document.getElementById('weeklyPieChart');
            if (!canvas) return;
            var chart = prepareCanvas(canvas);
            var ctx = chart.ctx;
            var width = chart.width;
            var height = chart.height;
            var values = [weekly.entries, weekly.departures];
            var total = values[0] + values[1];
            var radius = Math.min(width, height) * .34;
            var centerX = width / 2;
            var centerY = height / 2;
            ctx.clearRect(0, 0, width, height);
            if (!total) {
                drawEmpty(ctx, width, height);
                return;
            }
            var start = -Math.PI / 2;
            [colors.entries, colors.departures].forEach(function(color, index){
                var slice = (values[index] / total) * Math.PI * 2;
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, start, start + slice);
                ctx.closePath();
                ctx.fillStyle = color;
                ctx.fill();
                start += slice;
            });
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius * .56, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.fillStyle = colors.text;
            ctx.font = '800 24px Manrope, Segoe UI, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(total, centerX, centerY - 2);
            ctx.fillStyle = colors.muted;
            ctx.font = '700 12px Manrope, Segoe UI, sans-serif';
            ctx.fillText('movimientos', centerX, centerY + 18);
        }

        function drawBars() {
            var canvas = document.getElementById('monthlyBarChart');
            if (!canvas) return;
            var chart = prepareCanvas(canvas);
            var ctx = chart.ctx;
            var width = chart.width;
            var height = chart.height;
            var padding = { top: 14, right: 16, bottom: 34, left: 34 };
            var plotWidth = width - padding.left - padding.right;
            var plotHeight = height - padding.top - padding.bottom;
            var maxValue = Math.max.apply(null, monthly.entries.concat(monthly.departures).concat([1]));
            var groups = monthly.labels.length || 1;
            var groupWidth = plotWidth / groups;
            var barWidth = Math.min(18, groupWidth * .26);
            ctx.clearRect(0, 0, width, height);

            ctx.strokeStyle = colors.grid;
            ctx.lineWidth = 1;
            ctx.fillStyle = colors.muted;
            ctx.font = '700 11px Manrope, Segoe UI, sans-serif';
            ctx.textAlign = 'right';
            for (var i = 0; i <= 4; i++) {
                var y = padding.top + plotHeight - (plotHeight * i / 4);
                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(width - padding.right, y);
                ctx.stroke();
                ctx.fillText(Math.round(maxValue * i / 4), padding.left - 8, y + 4);
            }

            monthly.labels.forEach(function(label, index){
                var center = padding.left + groupWidth * index + groupWidth / 2;
                var entryHeight = (monthly.entries[index] / maxValue) * plotHeight;
                var departureHeight = (monthly.departures[index] / maxValue) * plotHeight;
                var baseY = padding.top + plotHeight;

                ctx.fillStyle = colors.entries;
                ctx.fillRect(center - barWidth - 2, baseY - entryHeight, barWidth, entryHeight);
                ctx.fillStyle = colors.departures;
                ctx.fillRect(center + 2, baseY - departureHeight, barWidth, departureHeight);

                ctx.fillStyle = colors.muted;
                ctx.font = '800 11px Manrope, Segoe UI, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(label, center, height - 10);
            });
        }

        function drawAll() {
            drawPie();
            drawBars();
        }

        window.addEventListener('resize', drawAll);
        drawAll();
    })();
    (function(){
        var materialCatalog = @json($entryMaterialSuggestions);
        var warehouseLocationCatalog = @json($warehouseLocations->map(fn ($location) => ['id' => $location->id, 'name' => $location->name])->values());

        function normalize(value) {
            return value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-zA-Z0-9\s]/g, '')
                .trim()
                .toUpperCase();
        }

        function normalizeCharacteristics(value) {
            return (value || '')
                .split(/[\n,]+/)
                .map(function(item){
                    return item
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .trim()
                        .toLowerCase()
                        .replace(/\s+/g, ' ');
                })
                .filter(Boolean)
                .sort()
                .join('|');
        }

        function hashText(value) {
            var hash = 0;
            for (var index = 0; index < value.length; index++) {
                hash = ((hash << 5) - hash + value.charCodeAt(index)) >>> 0;
            }
            return hash.toString(16).toUpperCase().padStart(6, '0').slice(0, 6);
        }

        function claveFor(name, characteristics) {
            var cleanName = normalize(name);
            if (!cleanName) return '';
            var prefix = cleanName.split(/\s+/).join('').slice(0, 6).padEnd(6, 'X');
            return 'MAT-' + prefix + '-' + hashText(cleanName + '|' + normalizeCharacteristics(characteristics));
        }

        function refreshMaterialClave(row) {
            var nameInput = row.querySelector('.entry-material-name');
            var characteristicsInput = row.querySelector('.entry-material-characteristics');
            var partIdInput = row.querySelector('.entry-material-id');
            var output = row.querySelector('.entry-material-clave');
            var clave = claveFor(nameInput ? nameInput.value : '', characteristicsInput ? characteristicsInput.value : '');
            if (partIdInput && partIdInput.value && row.dataset.selectedClave) {
                output.textContent = 'Material existente: ' + row.dataset.selectedClave;
                return;
            }
            output.textContent = clave || 'Escribe el nombre del material';
        }

        function searchable(value) {
            return (value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim()
                .toLowerCase();
        }

        function clearSelectedMaterial(row) {
            var partIdInput = row.querySelector('.entry-material-id');
            if (partIdInput) partIdInput.value = '';
            delete row.dataset.selectedClave;
        }

        function hideSuggestions(row) {
            var suggestions = row.querySelector('.entry-material-suggestions');
            var nameInput = row.querySelector('.entry-material-name');
            if (suggestions) suggestions.hidden = true;
            if (nameInput) nameInput.setAttribute('aria-expanded', 'false');
        }

        function selectExistingMaterial(row, material) {
            var partIdInput = row.querySelector('.entry-material-id');
            var nameInput = row.querySelector('.entry-material-name');
            var characteristicsInput = row.querySelector('.entry-material-characteristics');
            partIdInput.value = material.id;
            nameInput.value = material.name;
            characteristicsInput.value = (material.characteristics || []).join('\n');
            row.dataset.selectedClave = material.clave || '';
            var locationInput = row.querySelector('.entry-material-location');
            if (locationInput && material.warehouse_location_id) {
                var previousLocation = locationInput.querySelector('option[value="' + material.warehouse_location_id + '"]');
                if (previousLocation) locationInput.value = String(material.warehouse_location_id);
            }
            hideSuggestions(row);
            refreshMaterialClave(row);
        }

        function populateLocationSelect(row) {
            var locationInput = row.querySelector('.entry-material-location');
            if (!locationInput) return;

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Selecciona una ubicación';
            locationInput.appendChild(placeholder);

            warehouseLocationCatalog.forEach(function(location){
                var option = document.createElement('option');
                option.value = location.id;
                option.textContent = location.name;
                locationInput.appendChild(option);
            });
        }

        function renderSuggestions(row) {
            var nameInput = row.querySelector('.entry-material-name');
            var suggestions = row.querySelector('.entry-material-suggestions');
            var query = searchable(nameInput.value);
            suggestions.replaceChildren();

            if (!query) {
                hideSuggestions(row);
                return;
            }

            var matches = materialCatalog.filter(function(material){
                var text = [material.name, material.clave]
                    .concat(material.characteristics || [])
                    .join(' ');
                return searchable(text).includes(query);
            }).slice(0, 8);

            if (matches.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'entry-material-no-results';
                empty.textContent = 'No hay coincidencias. Se creará un material nuevo.';
                suggestions.appendChild(empty);
            } else {
                matches.forEach(function(material){
                    var button = document.createElement('button');
                    var title = document.createElement('strong');
                    var detail = document.createElement('small');
                    button.type = 'button';
                    button.className = 'entry-material-suggestion';
                    button.setAttribute('role', 'option');
                    title.textContent = material.name;
                    detail.textContent = (material.clave || 'Sin clave') + ' · ' +
                        ((material.characteristics || []).join(', ') || 'Sin características');
                    button.appendChild(title);
                    button.appendChild(detail);
                    button.addEventListener('click', function(){ selectExistingMaterial(row, material); });
                    suggestions.appendChild(button);
                });
            }

            suggestions.hidden = false;
            nameInput.setAttribute('aria-expanded', 'true');
        }

        function wireMaterialRow(row) {
            var nameInput = row.querySelector('.entry-material-name');
            var characteristicsInput = row.querySelector('.entry-material-characteristics');
            nameInput.addEventListener('input', function(){
                clearSelectedMaterial(row);
                refreshMaterialClave(row);
                renderSuggestions(row);
            });
            nameInput.addEventListener('focus', function(){ renderSuggestions(row); });
            nameInput.addEventListener('keydown', function(event){
                if (event.key === 'ArrowDown') {
                    var firstSuggestion = row.querySelector('.entry-material-suggestion');
                    if (firstSuggestion) {
                        event.preventDefault();
                        firstSuggestion.focus();
                    }
                }
                if (event.key === 'Escape') hideSuggestions(row);
            });
            characteristicsInput.addEventListener('input', function(){
                clearSelectedMaterial(row);
                refreshMaterialClave(row);
            });
            var removeButton = row.querySelector('.remove-entry-material-btn');
            if (removeButton) {
                removeButton.addEventListener('click', function(){ row.remove(); });
            }
            refreshMaterialClave(row);
        }

        var list = document.getElementById('entryMaterialsList');
        var addButton = document.getElementById('addEntryMaterialBtn');
        if (!list || !addButton) return;
        var nextMaterialIndex = list.querySelectorAll('.warehouse-entry-material').length;

        addButton.addEventListener('click', function(){
            var index = nextMaterialIndex++;
            var row = document.createElement('div');
            row.className = 'warehouse-entry-material';
            row.dataset.materialIndex = index;
            row.style.marginTop = '14px';
            row.style.paddingTop = '14px';
            row.style.borderTop = '1px solid rgba(16, 52, 37, .10)';
            row.innerHTML = '' +
                '<div>' +
                    '<label>Nombre del material</label>' +
                    '<div class="entry-material-search">' +
                        '<input class="entry-material-id" type="hidden" name="materials[' + index + '][part_id]">' +
                        '<input class="entry-material-name" name="materials[' + index + '][name]" required autocomplete="off" placeholder="Escribe para buscar materiales registrados" role="combobox" aria-autocomplete="list" aria-expanded="false">' +
                        '<div class="entry-material-suggestions" role="listbox" hidden></div>' +
                    '</div>' +
                '</div>' +
                '<div style="margin-top:10px;">' +
                    '<label>Cantidad</label>' +
                    '<input class="entry-material-quantity" type="text" inputmode="decimal" name="materials[' + index + '][quantity]" value="1" required autocomplete="off">' +
                '</div>' +
                '<div style="margin-top:10px;">' +
                    '<label>Ubicación</label>' +
                    '<select class="entry-material-location" name="materials[' + index + '][warehouse_location_id]" required></select>' +
                '</div>' +
                '<div style="margin-top:10px;">' +
                    '<label>Caracteristicas</label>' +
                    '<textarea class="entry-material-characteristics" name="materials[' + index + '][characteristics]" placeholder="Una por linea o separadas por coma"></textarea>' +
                '</div>' +
                '<div class="warehouse-generated-id entry-material-clave" style="margin-top:10px;">Escribe el nombre del material</div>' +
                '<button class="btn btn-secondary remove-entry-material-btn" type="button" style="margin-top:10px;">Quitar material</button>';
            list.appendChild(row);
            populateLocationSelect(row);
            wireMaterialRow(row);
            row.querySelector('.entry-material-name').focus();
        });

        list.querySelectorAll('.warehouse-entry-material').forEach(wireMaterialRow);
        document.addEventListener('click', function(event){
            list.querySelectorAll('.warehouse-entry-material').forEach(function(row){
                if (!row.contains(event.target)) hideSuggestions(row);
            });
        });
    })();
    (function(){
        var exitModalElement = document.getElementById('materialExitModal');
        var inventoryModalElement = document.getElementById('inventorySelectorModal');
        var materialNameInput = document.getElementById('exitMaterialName');
        var materialIdInput = document.getElementById('exitPartId');
        var materialIdDisplay = document.getElementById('exitMaterialIdDisplay');
        var stockDisplay = document.getElementById('exitStockDisplay');
        var searchInput = document.getElementById('inventoryMaterialSearch');
        var openButton = document.getElementById('openInventorySelector');
        var returnButton = document.getElementById('returnToExitModal');
        var closeButton = document.getElementById('closeInventorySelector');
        var noResults = document.getElementById('inventoryNoResults');
        var rows = Array.from(document.querySelectorAll('.inventory-material-row'));
        if (!exitModalElement || !inventoryModalElement || !materialNameInput || typeof bootstrap === 'undefined') return;

        var exitModal = bootstrap.Modal.getOrCreateInstance(exitModalElement);
        var inventoryModal = bootstrap.Modal.getOrCreateInstance(inventoryModalElement);

        function normalized(value) {
            return (value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
        }

        function filterInventory() {
            var query = normalized(searchInput.value);
            var visible = 0;
            rows.forEach(function(row){
                var matches = !query || normalized(row.dataset.name).includes(query);
                row.hidden = !matches;
                if (matches) visible++;
            });
            noResults.hidden = visible !== 0;
        }

        function showInventory() {
            searchInput.value = materialNameInput.value;
            filterInventory();
            exitModal.hide();
            exitModalElement.addEventListener('hidden.bs.modal', function openSelector(){
                exitModalElement.removeEventListener('hidden.bs.modal', openSelector);
                inventoryModal.show();
            });
        }

        function returnToExit() {
            inventoryModal.hide();
            inventoryModalElement.addEventListener('hidden.bs.modal', function reopenExit(){
                inventoryModalElement.removeEventListener('hidden.bs.modal', reopenExit);
                exitModal.show();
            });
        }

        openButton.addEventListener('click', showInventory);
        materialNameInput.addEventListener('keydown', function(event){
            if (event.key === 'Enter') {
                event.preventDefault();
                showInventory();
            }
        });
        materialNameInput.addEventListener('input', function(){
            materialIdInput.value = '';
            materialIdDisplay.value = '';
            materialIdDisplay.placeholder = 'Selecciona un material del inventario';
            stockDisplay.textContent = 'Selecciona un material para ver la existencia.';
        });
        searchInput.addEventListener('input', filterInventory);
        returnButton.addEventListener('click', returnToExit);
        closeButton.addEventListener('click', returnToExit);
        inventoryModalElement.addEventListener('shown.bs.modal', function(){
            searchInput.focus();
            searchInput.select();
        });

        rows.forEach(function(row){
            var button = row.querySelector('.select-inventory-material');
            if (!button || button.disabled) return;
            button.addEventListener('click', function(){
                materialIdInput.value = button.dataset.id;
                materialNameInput.value = button.dataset.name;
                materialIdDisplay.value = button.dataset.clave;
                stockDisplay.textContent = 'Existencia disponible: ' + button.dataset.stock;
                returnToExit();
            });
        });
    })();
    (function(){
        var typeModalElement = document.getElementById('entryTypeModal');
        var materialModalElement = document.getElementById('materialEntryModal');
        var entryTypeInput = document.getElementById('materialEntryType');
        var invoiceUploadGroup = document.getElementById('invoiceUploadGroup');
        var invoiceFile = document.getElementById('invoiceFile');
        if (!typeModalElement || !materialModalElement || typeof bootstrap === 'undefined') return;

        var typeModal = bootstrap.Modal.getOrCreateInstance(typeModalElement);
        var materialModal = bootstrap.Modal.getOrCreateInstance(materialModalElement);

        function setEntryType(entryType) {
            if (entryTypeInput) {
                entryTypeInput.value = entryType;
            }

            var isPurchase = entryType === 'Compra';
            if (invoiceUploadGroup) {
                invoiceUploadGroup.hidden = !isPurchase;
            }
            if (!isPurchase && invoiceFile) {
                invoiceFile.value = '';
            }
        }

        typeModalElement.querySelectorAll('[data-entry-type]').forEach(function(button){
            button.addEventListener('click', function(){
                setEntryType(button.getAttribute('data-entry-type') || '');
                typeModal.hide();
                typeModalElement.addEventListener('hidden.bs.modal', function openMaterialModal(){
                    typeModalElement.removeEventListener('hidden.bs.modal', openMaterialModal);
                    materialModal.show();
                });
            });
        });

        setEntryType(entryTypeInput ? entryTypeInput.value : '');
    })();
    (function(){
        document.querySelectorAll('[data-record-row]').forEach(function(link){
            link.addEventListener('click', function(){
                var modalSelector = link.getAttribute('data-bs-target');
                var modalElement = modalSelector ? document.querySelector(modalSelector) : null;
                if (modalElement) {
                    modalElement.dataset.focusRow = link.getAttribute('data-record-row') || '';
                }
            });
        });

        ['entriesRecordModal', 'exitsRecordModal'].forEach(function(modalId){
            var modalElement = document.getElementById(modalId);
            if (!modalElement) return;

            modalElement.addEventListener('shown.bs.modal', function(){
                modalElement.querySelectorAll('.warehouse-record-row-highlight').forEach(function(row){
                    row.classList.remove('warehouse-record-row-highlight');
                });

                var rowId = modalElement.dataset.focusRow;
                var row = rowId ? document.getElementById(rowId) : null;
                if (!row) return;

                row.classList.add('warehouse-record-row-highlight');
                row.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            });
        });
    })();
    @if(session('status') && str_starts_with(session('status'), 'Entrada registrada'))
    (function(){
        var played = false;
        var audio = new Audio(@json(asset('audio/entrada-tuberia.mpeg')));
        audio.preload = 'auto';

        function playEntrySound() {
            if (played) return;

            audio.currentTime = 0;
            var playback = audio.play();
            if (playback) {
                playback.then(function(){
                    played = true;
                }).catch(function(){
                    document.addEventListener('click', playEntrySound, { once: true });
                    document.addEventListener('keydown', playEntrySound, { once: true });
                });
            }
        }

        playEntrySound();
    })();
    @endif
</script>
@endpush
@endsection


