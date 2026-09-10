@extends('layouts.app')

@section('title','Detalle de la unidad')

@section('content')
    <div class="card shadow-sm app-card">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
                <div>
                    <p class="brand-hero__label text-success mb-1">{{ $empleado->es_easter_egg ? 'Easter egg' : 'Ficha tecnica' }}</p>
                    <h1 class="h5 mb-0">{{ $empleado->es_easter_egg ? 'Creadores' : 'Detalle de la unidad' }}</h1>
                </div>
            </div>

            @if($empleado->es_easter_egg)
                <div class="alert alert-success">
                    Tarjeta secreta desbloqueada con el codigo Coca-Cola {{ \App\Models\Empleado::EASTER_EGG_BARCODE }}.
                </div>
            @endif

            {{-- Datos completos de la unidad seleccionada. --}}
            <div class="row g-3 mt-2">
                <div class="col-12 col-lg-8">
                    <div class="row g-2">
                        @foreach([
                            'Clave' => $empleado->clave,
                            'Nombre de unidad' => $empleado->nombre_equipo,
                            'Fecha alta' => $empleado->fecha_alta?->format('Y-m-d'),
                            'Marca/modelo' => $empleado->marca_modelo,
                            'Modelo' => $empleado->modelo,
                            'Numero de serie' => $empleado->numero_serie,
                            'Numero de serie eq. adicional' => $empleado->numero_serie_eq_adicional,
                            'Tenencia' => $empleado->tenencia,
                            'Tarjeta de circulacion' => $empleado->tarjeta_circulacion,
                            'Tipo motor' => $empleado->tipo_motor,
                            'Filtros de motor' => $empleado->tipo_filtro,
                            'Familia' => $empleado->familia,
                            'Fecha de fabricacion' => $empleado->fecha_fabricacion?->format('Y-m-d'),
                            'Estado' => $empleado->estado,
                            'Proveedor' => $empleado->proveedor,
                            'Horometro/odometro' => $empleado->horometro_odometro,
                        ] as $label => $value)
                            <div class="col-12 col-md-6"><strong>{{ $label }}:</strong> {{ $value }}</div>
                        @endforeach

                        <div class="col-12"><strong>Descripcion:</strong> {{ $empleado->descripcion }}</div>
                        <div class="col-12"><strong>Refacciones:</strong> {{ $empleado->refacciones }}</div>

                        <div class="col-12">
                            <strong>Documentos anexados:</strong>
                            {{-- PDFs generados por clave o cargados manualmente. --}}
                            <div class="d-flex flex-column flex-sm-row gap-2 mt-1">
                                @if($empleado->tienePolizaPdf())
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('empleados.pdf', [$empleado, 'poliza']) }}" target="_blank">Ver poliza</a>
                                @endif
                                @if($empleado->tieneFacturaPdf())
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('empleados.pdf', [$empleado, 'factura']) }}" target="_blank">Ver factura</a>
                                @endif
                                @if(! $empleado->tienePolizaPdf() && ! $empleado->tieneFacturaPdf())
                                    <span class="text-muted">Sin documentos anexados.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 text-center">
                    <strong class="d-block mb-2">Foto de la unidad</strong>
                    @if($empleado->tieneFoto())
                        <img src="{{ route('empleados.foto', $empleado) }}" alt="Foto de {{ $empleado->clave }}" class="img-fluid border rounded p-2 mb-3" style="max-height: 260px; object-fit: contain;">
                    @else
                        <p class="text-muted">Sin foto registrada.</p>
                    @endif

                    <strong class="d-block mb-2">Codigo QR</strong>
                    {{-- QR generado automaticamente para esta unidad. --}}
                    <img src="{{ $empleado->qrImagenUrl(220) }}" alt="QR de {{ $empleado->clave }}" class="img-fluid border rounded p-2" width="220">
                    <p class="small text-muted mb-0">Al escanearlo abre este detalle.</p>

                    @if($empleado->usaHorometro())
                        @php
                            $horasActuales = $empleado->horometroHorasActuales();
                            $alertaHorometro = $empleado->alertaHorometroActual();
                            $proximaAlerta = $empleado->proximaAlertaHorometro();
                        @endphp
                        <div class="meter-panel border rounded p-3 mt-3 text-start bg-light">
                            <strong class="d-block mb-2">Reloj horometro</strong>
                            <div
                                class="display-6 fw-bold text-success"
                                id="horometroReloj"
                                data-base-hours="{{ $empleado->horometro_horas ?? 0 }}"
                                data-running="{{ $empleado->horometro_en_marcha ? '1' : '0' }}"
                                data-started-at="{{ $empleado->horometro_iniciado_en?->toIso8601String() }}"
                            >
                                {{ number_format($horasActuales, 2) }} h
                            </div>
                            <p class="small text-muted mb-2">Ciclo actual de 0 a 1000 horas.</p>

                            @if($alertaHorometro)
                                <div class="alert alert-warning py-2 mb-2">
                                    <strong>{{ $alertaHorometro['horas'] }} h:</strong> {{ $alertaHorometro['mensaje'] }}
                                </div>
                            @endif

                            @if($proximaAlerta)
                                <p class="small mb-2">
                                    Proxima alerta: <strong>{{ $proximaAlerta['horas'] }} h</strong>
                                    (faltan {{ number_format($proximaAlerta['faltan'], 2) }} h).
                                </p>
                            @endif

                            <div class="d-grid gap-2">
                                @if($empleado->horometro_en_marcha)
                                    <form action="{{ route('empleados.horometro.detener', $empleado) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100">Detener y guardar horas</button>
                                    </form>
                                @else
                                    <form action="{{ route('empleados.horometro.iniciar', $empleado) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">Iniciar reloj</button>
                                    </form>
                                @endif
                                <form action="{{ route('empleados.horometro.reiniciar', $empleado) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Deseas reiniciar el ciclo del horometro?')">Reiniciar ciclo</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($empleado->usaOdometro())
                        @php
                            $kilometrosTotales = $empleado->odometroKilometrosTotales();
                            $kilometrosCiclo = $empleado->odometroKilometrosCiclo();
                            $alertaOdometro = $empleado->alertaOdometroActual();
                            $proximaOdometro = $empleado->proximaAlertaOdometro();
                            $historialOdometro = $empleado->odometroRegistros()->latest('registrado_en')->latest()->take(8)->get();
                        @endphp
                        <div class="meter-panel border rounded p-3 mt-3 text-start bg-light">
                            <strong class="d-block mb-2">Odometro</strong>
                            <div class="display-6 fw-bold text-success">{{ number_format($kilometrosCiclo, 2) }} km</div>
                            <p class="small text-muted mb-2">Kilometros acumulados desde el ultimo servicio. Se reinicia al registrar servicio.</p>

                            @if($alertaOdometro)
                                <div class="alert alert-warning py-2 mb-2">
                                    <strong>{{ $alertaOdometro['kilometros'] }} km:</strong> {{ $alertaOdometro['mensaje'] }}
                                </div>
                            @endif

                            @if($proximaOdometro)
                                <p class="small mb-2">
                                    Proxima alerta: <strong>{{ $proximaOdometro['kilometros'] }} km</strong>
                                    (faltan {{ number_format($proximaOdometro['faltan'], 2) }} km).
                                </p>
                            @endif

                            <form action="{{ route('empleados.odometro.store', $empleado) }}" method="POST" class="mb-3">
                                @csrf
                                <label for="kilometros" class="form-label">Kilometros avanzados</label>
                                <input type="number" step="0.01" min="0.01" class="form-control mb-2" id="kilometros" name="kilometros" required>
                                <label for="registrado_en" class="form-label">Fecha</label>
                                <input type="date" class="form-control mb-2" id="registrado_en" name="registrado_en" value="{{ now()->format('Y-m-d') }}">
                                <label for="nota" class="form-label">Nota</label>
                                <input type="text" class="form-control mb-2" id="nota" name="nota" maxlength="255" placeholder="Opcional">
                                <button type="submit" class="btn btn-success w-100">Agregar kilometros</button>
                            </form>

                            <strong class="d-block mb-2">Historial</strong>
                            @forelse($historialOdometro as $registro)
                                <div class="border-top py-2 small">
                                    <div><strong>{{ number_format((float) $registro->kilometros, 2) }} km</strong></div>
                                    <div class="text-muted">{{ $registro->registrado_en?->format('Y-m-d') ?? $registro->created_at->format('Y-m-d') }}</div>
                                    @if($registro->nota)
                                        <div>{{ $registro->nota }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="small text-muted mb-0">Aun no hay kilometros registrados.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            @php
                $historialServicios = $empleado->servicioRegistros()->latest('fecha_servicio')->latest()->take(5)->get();
            @endphp

            <div class="mt-4">
                <h2 class="h6 mb-2">Servicios registrados</h2>
                <div class="row g-2">
                    @forelse($historialServicios as $servicio)
                        <div class="col-12 col-lg-6">
                            <div class="border rounded p-3 h-100 bg-light">
                                <div class="d-flex justify-content-between gap-2 mb-1">
                                    <strong>{{ $servicio->tipo_servicio }}</strong>
                                    <span class="small text-muted">{{ $servicio->fecha_servicio?->format('Y-m-d') }}</span>
                                </div>
                                <div class="small text-muted mb-2">{{ $servicio->medidor }} - {{ $servicio->medicion }}</div>
                                <div class="small"><strong>Mecanico:</strong> {{ $servicio->mecanico }}</div>
                                <div class="small"><strong>Lugar:</strong> {{ $servicio->lugar }}</div>
                                <div class="small"><strong>Superviso:</strong> {{ $servicio->supervisor }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="small text-muted mb-0">Aun no hay servicios registrados.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            @if(auth()->user()->isAdministrador())
                <div class="mt-3 d-grid d-sm-flex gap-2">
                    {{-- Acciones para modificar el registro o regresar al listado. --}}
                    @if($empleado->es_easter_egg)
                        @unless($empleado->easter_egg_editado)
                            <a href="{{ route('creadores.edit') }}" class="btn btn-secondary">Editar una vez</a>
                        @endunless
                        <a href="{{ route('detector-qr') }}" class="btn btn-light">Volver al detector</a>
                    @else
                        <a href="{{ route('empleados.edit', $empleado) }}" class="btn btn-secondary">Editar</a>
                        <a href="{{ route('empleados.index') }}" class="btn btn-light">Volver</a>
                    @endif
                </div>
            @else
                <div class="mt-3 d-grid d-sm-flex gap-2">
                    @if($empleado->es_easter_egg && ! $empleado->easter_egg_editado)
                        <a href="{{ route('creadores.edit') }}" class="btn btn-secondary">Editar una vez</a>
                    @endif
                    <a href="{{ route('detector-qr') }}" class="btn btn-light">Volver al detector</a>
                </div>
            @endif
        </div>
    </div>

    @php
        $alertaModal = null;

        if ($empleado->usaHorometro() && ($alerta = $empleado->alertaHorometroActual())) {
            $alertaModal = [
                'tipo' => str_contains(strtolower($alerta['mensaje']), 'gama') ? 'Gama completa' : 'Medio servicio',
                'medicion' => $alerta['horas'] . ' h',
                'mensaje' => $alerta['mensaje'],
            ];
        }

        if ($empleado->usaOdometro() && ($alerta = $empleado->alertaOdometroActual())) {
            $alertaModal = [
                'tipo' => str_contains(strtolower($alerta['mensaje']), 'gama') ? 'Gama completa' : 'Medio servicio',
                'medicion' => $alerta['kilometros'] . ' km',
                'mensaje' => $alerta['mensaje'],
            ];
        }
    @endphp

    @if($alertaModal)
        <div class="modal fade" id="serviceAlertModal" tabindex="-1" aria-labelledby="serviceAlertModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-warning-subtle border-0">
                        <div>
                            <p class="small text-uppercase fw-bold text-warning-emphasis mb-1">Alerta de servicio</p>
                            <h2 class="modal-title h5" id="serviceAlertModalLabel">{{ $alertaModal['tipo'] }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="display-6 fw-bold text-success mb-2">{{ $alertaModal['medicion'] }}</div>
                        <p class="mb-3">{{ $alertaModal['mensaje'] }}</p>

                        <form id="serviceRegisterForm" action="{{ route('empleados.servicio.store', $empleado) }}" method="POST" class="service-form border rounded p-3 bg-light">
                            @csrf
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label for="fecha_servicio" class="form-label">Fecha del servicio</label>
                                    <input type="date" class="form-control" id="fecha_servicio" name="fecha_servicio" value="{{ old('fecha_servicio', now()->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="mecanico" class="form-label">Mecanico que realizo</label>
                                    <input type="text" class="form-control" id="mecanico" name="mecanico" value="{{ old('mecanico') }}" maxlength="255" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="lugar" class="form-label">Lugar</label>
                                    <input type="text" class="form-control" id="lugar" name="lugar" value="{{ old('lugar') }}" maxlength="255" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="supervisor" class="form-label">Quien supervisa</label>
                                    <input type="text" class="form-control" id="supervisor" name="supervisor" value="{{ old('supervisor') }}" maxlength="255" required>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Aceptar</button>
                        <button type="submit" form="serviceRegisterForm" class="btn btn-success">Registrar servicio</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (() => {
            const reloj = document.getElementById('horometroReloj');

            if (!reloj || reloj.dataset.running !== '1' || !reloj.dataset.startedAt) {
                return;
            }

            const limiteCiclo = 1000;
            const baseHours = Number.parseFloat(reloj.dataset.baseHours || '0');
            const startedAt = new Date(reloj.dataset.startedAt).getTime();

            const actualizarReloj = () => {
                const elapsedHours = Math.max(0, (Date.now() - startedAt) / 3600000);
                const cycleHours = (baseHours + elapsedHours) % limiteCiclo;
                reloj.textContent = `${cycleHours.toFixed(2)} h`;
            };

            actualizarReloj();
            setInterval(actualizarReloj, 30000);
        })();

        (() => {
            const modalElement = document.getElementById('serviceAlertModal');

            if (!modalElement || !window.bootstrap) {
                return;
            }

            new bootstrap.Modal(modalElement).show();
        })();
    </script>
@endpush


