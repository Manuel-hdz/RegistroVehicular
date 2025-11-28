@extends('layouts.app')

@section('content')
<section class="card" style="text-align:center; padding:40px 20px; background:linear-gradient(135deg, #006847 0%, #08916a 100%); color:#fff;">
    <h1 style="margin:0; font-size:34px;">Concreto Lanzado de Fresnillo MARCA</h1>
    <p style="margin-top:10px; opacity:.95; font-size:18px;">Innovación y control para operaciones seguras y eficientes.</p>
    <div class="row" style="justify-content:center; margin-top:18px; gap:10px;">
        <a class="btn btn-primary" href="/registroVehicular">Registro Vehicular</a>
    </div>
    <div class="row" style="justify-content:center; gap:18px; margin-top:22px;">
        <div style="min-width:160px;">
            <strong style="font-size:26px;">10k+</strong>
            <div style="opacity:.9;">Movimientos controlados</div>
        </div>
        <div style="min-width:160px;">
            <strong style="font-size:26px;">99.9%</strong>
            <div style="opacity:.9;">Disponibilidad</div>
        </div>
        <div style="min-width:160px;">
            <strong style="font-size:26px;">24/7</strong>
            <div style="opacity:.9;">Operación</div>
        </div>
    </div>
    
    <div class="row" style="justify-content:center; gap:12px; margin-top:20px;">
        <a class="btn btn-secondary" href="#servicios">Servicios</a>
        <a class="btn btn-secondary" href="#ventajas">Ventajas</a>
        <a class="btn btn-secondary" href="#contacto">Contacto</a>
    </div>
</section>

<section id="servicios" class="grid grid-2">
    <div class="card">
        <h3 style="margin-top:0">Registro Vehicular</h3>
        <p>Control preciso de entradas y salidas, con trazabilidad de conductor y vehículo.</p>
        <ul>
            <li>Flujo de salidas y entradas</li>
            <li>Catálogo de vehículos y conductores</li>
            <li>Indicadores y reportes</li>
        </ul>
        <a class="btn btn-primary" href="/registroVehicular">Ir al sistema</a>
    </div>
</section>

<section id="ventajas" class="grid grid-2">
    <div class="card">
        <h3 style="margin-top:0">Seguridad</h3>
        <p>Accesos por rol, trazabilidad de cambios y datos protegidos.</p>
    </div>
    <div class="card">
        <h3 style="margin-top:0">Eficiencia</h3>
        <p>Menos tiempos de espera con flujos claros y simples.</p>
    </div>
    <div class="card">
        <h3 style="margin-top:0">Visibilidad</h3>
        <p>Indicadores clave para la operación en tiempo real.</p>
    </div>
    <div class="card">
        <h3 style="margin-top:0">Escalabilidad</h3>
        <p>Arquitectura lista para crecer con el negocio.</p>
    </div>
</section>

<section id="contacto" class="card" style="text-align:center;">
    <h3 style="margin-top:0">Contacto</h3>
    <p>Para más información, acércate al área de sistemas de Concreto Lanzado de Fresnillo MARCA.</p>
    <div class="row" style="justify-content:center; gap:10px;">
        <a class="btn btn-secondary" href="/registroVehicular">Ir al sistema</a>
        <a class="btn btn-secondary" href="#">Brochure</a>
    </div>
</section>
@endsection
