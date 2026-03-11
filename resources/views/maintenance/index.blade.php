@extends('layouts.app')

@section('content')
@push('head')
<style>
  /* Evitar conflicto con Bootstrap .modal */
  .backdrop { z-index: 1060; }
  .backdrop .modal { display: block !important; position: relative; width: min(560px, 92vw); }
  .backdrop .modal header { border-radius: 10px 10px 0 0; }
  .backdrop .modal .content { max-height: 70vh; overflow:auto; }
  /* Icono más grande y clicable */
  .maint-icon svg { width: 72px; height: 72px; }
  .maint-icon i { font-size: 72px; line-height: 1; }
  @media (max-width: 576px){
    .maint-icon svg { width: 64px; height: 64px; }
    .maint-icon i { font-size: 64px; }
  }
</style>
@endpush
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Estado de Unidades</h2>
    </div>
</div>

<div class="card">
    <div class="grid grid-2">
    @foreach($vehicles as $v)
        @php
            $color = $v->availability === 'available' ? '#16a34a' : '#dc2626';
            $title = $v->identifier ?: ($v->model ? $v->model.' '.$v->year : 'Unidad');
        @endphp
        <div class="card">
            <div class="row" style="align-items:center; gap:12px;">
                <button type="button" class="maint-icon btn btn-icon" style="color: {{ $color }}; border-color: transparent; background:transparent; padding:6px;"
                    title="Cambiar estado" aria-label="Cambiar estado"
                    data-update-url="{{ route('maintenance.update', $v) }}"
                    data-availability="{{ $v->availability }}"
                    data-note="{{ $v->maintenance_note }}"
                    data-title="{{ $title }}">
                    @include('vehicles.partials.vtype-icon', ['type' => $v->vtype, 'size' => 64])
                </button>
                <div>
                    <strong>{{ $title }}</strong>
                    <div style="font-size:14px; color:#555; margin-top:4px;">Estado: {{ $v->availability === 'available' ? 'Disponible' : 'No disponible' }}</div>
                    @if($v->maintenance_note)
                        <div style="font-size:14px; color:#9a3412; margin-top:6px;">{{ $v->maintenance_note }}</div>
                    @endif
                    <div style="margin-top:6px;">
                        <a class="btn btn-link" href="{{ route('repairs.index', ['vehicle_id' => $v->id]) }}">Historial de reparaciones</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>
</div>
<div class="backdrop" id="maintBackdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="maintTitle">
    <div class="modal" role="document">
        <header>
            <strong id="maintTitle">Actualizar estado</strong>
            <button class="close-x" type="button" aria-label="Cerrar" id="btnCloseMaint">×</button>
        </header>
        <div class="content">
            <form id="maintForm" method="POST" action="#" class="grid" style="gap:10px;">
                @csrf
                @method('PUT')
                <div>
                    <label>Estado</label>
                    <select name="availability" id="maintAvailability" required>
                        <option value="available">Disponible</option>
                        <option value="unavailable">No disponible</option>
                    </select>
                </div>
                <div>
                    <label>Descripción (opcional)</label>
                    <textarea name="maintenance_note" id="maintNote"></textarea>
                </div>
                <div class="row" style="justify-content:flex-end; gap:8px;">
                    <button class="btn btn-secondary" type="button" id="btnCancelMaint">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    </div>
<script>
    (function(){
        const backdrop = document.getElementById('maintBackdrop');
        const closeX = document.getElementById('btnCloseMaint');
        const cancelBtn = document.getElementById('btnCancelMaint');
        const form = document.getElementById('maintForm');
        const sel = document.getElementById('maintAvailability');
        const note = document.getElementById('maintNote');
        const title = document.getElementById('maintTitle');
        function open(){ backdrop.setAttribute('aria-hidden','false'); }
        function close(){ backdrop.setAttribute('aria-hidden','true'); }
        document.querySelectorAll('.maint-icon').forEach(function(btn){
            btn.addEventListener('click', function(){
                form.action = btn.dataset.updateUrl;
                sel.value = btn.dataset.availability || 'available';
                note.value = btn.dataset.note || '';
                title.textContent = 'Actualizar: ' + (btn.dataset.title || 'Unidad');
                open();
            });
        });
        if(closeX) closeX.addEventListener('click', close);
        if(cancelBtn) cancelBtn.addEventListener('click', close);
        if(backdrop) backdrop.addEventListener('click', function(e){ if(e.target === backdrop) close(); });
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
    })();
</script>
@endsection
