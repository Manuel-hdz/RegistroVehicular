@extends('layouts.app')

@section('content')
<section class="card" style="overflow:hidden; background:linear-gradient(135deg, #033624, #0c7c58 56%, #1e9b71); color:#fff; padding:34px 28px;">
    <div class="grid grid-2" style="align-items:center;">
        <div>
            <div style="display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em;">
                Plataforma operativa MARCA
            </div>
            <h1 style="margin:18px 0 10px; font-size:clamp(2.1rem, 3vw, 3.4rem); line-height:1.03; color:#fff;">
                Control claro, rapido y agradable para toda la operacion.
            </h1>
            <p style="margin:0 0 18px; color:rgba(255,255,255,.84); max-width:38rem;">
                Unifica movimientos, mantenimiento, almacen, RRHH y compras en una experiencia mas limpia, con mejor lectura y acciones compactas.
            </p>
            <div class="row" style="align-items:center;">
                <a class="btn btn-warning" href="/registroVehicular">
                    <i class="bi bi-speedometer2"></i>Entrar al sistema
                </a>
                <a class="btn btn-outline-light" href="#modulos">
                    <i class="bi bi-grid"></i>Ver modulos
                </a>
            </div>
        </div>
        <div class="grid grid-2">
            <div class="card" style="margin-bottom:0; background:rgba(255,255,255,.14); border-color:rgba(255,255,255,.18); color:#fff;">
                <div style="font-size:2rem; font-weight:800; line-height:1;">10k+</div>
                <div style="color:rgba(255,255,255,.78);">movimientos controlados</div>
            </div>
            <div class="card" style="margin-bottom:0; background:rgba(255,255,255,.14); border-color:rgba(255,255,255,.18); color:#fff;">
                <div style="font-size:2rem; font-weight:800; line-height:1;">24/7</div>
                <div style="color:rgba(255,255,255,.78);">visibilidad operativa</div>
            </div>
            <div class="card" style="margin-bottom:0; background:rgba(255,255,255,.14); border-color:rgba(255,255,255,.18); color:#fff;">
                <div style="font-size:2rem; font-weight:800; line-height:1;">Roles</div>
                <div style="color:rgba(255,255,255,.78);">permisos y trazabilidad</div>
            </div>
            <div class="card" style="margin-bottom:0; background:rgba(255,255,255,.14); border-color:rgba(255,255,255,.18); color:#fff;">
                <div style="font-size:2rem; font-weight:800; line-height:1;">UX</div>
                <div style="color:rgba(255,255,255,.78);">formularios mas ligeros</div>
            </div>
        </div>
    </div>
</section>

<section id="modulos" class="grid grid-3">
    <div class="card">
        <h3 style="margin-top:0;">Registro vehicular</h3>
        <p>Salidas, entradas, consulta de unidades y trazabilidad diaria con filtros claros.</p>
    </div>
    <div class="card">
        <h3 style="margin-top:0;">Mantenimiento</h3>
        <p>Reparaciones, refacciones, mecanicos y estado general de equipos en el mismo flujo.</p>
    </div>
    <div class="card">
        <h3 style="margin-top:0;">Compras</h3>
        <p>Requisiciones publicas, centros de costos y seguimiento por estatus desde pendientes hasta entrega.</p>
    </div>
</section>

<section class="card" style="text-align:center;">
    <h3 style="margin-top:0;">Un sistema mas ordenado se siente mas rapido</h3>
    <p style="max-width:42rem; margin:0 auto 16px;">
        El objetivo del rediseño es bajar friccion: menos botones estirados, mejores jerarquias y acciones mas faciles de encontrar en escritorio y movil.
    </p>
    <div class="row" style="justify-content:center;">
        <a class="btn btn-primary" href="/registroVehicular">Abrir plataforma</a>
    </div>
</section>
@endsection
