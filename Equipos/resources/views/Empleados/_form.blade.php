@php
    $empleado = $empleado ?? null;
    $fields = [
        ['clave', 'Clave', 'text', false],
        ['nombre_equipo', 'Nombre de unidad', 'text', false],
        ['fecha_alta', 'Fecha alta', 'date', false],
        ['marca_modelo', 'Marca/modelo', 'text', false],
        ['modelo', 'Modelo', 'text', false],
        ['placas', 'Placas', 'text', false],
        ['numero_serie', 'Numero de serie', 'text', false],
        ['numero_serie_eq_adicional', 'Numero de serie eq. adicional', 'text', false],
        ['tenencia', 'Tenencia', 'text', false],
        ['tarjeta_circulacion', 'Tarjeta de circulacion', 'text', false],
        ['tipo_motor', 'Tipo motor', 'text', false],
        ['tipo_filtro', 'Filtros de motor', 'text', false],
        ['familia', 'Familia', 'text', false],
        ['fecha_fabricacion', 'Fecha de fabricacion', 'date', false],
        ['proveedor', 'Proveedor', 'text', false],
    ];

    $estadoValue = old('estado', $empleado?->estado ?? 'Activo');
    $estadoValue = ucfirst(strtolower((string) $estadoValue));
    $medidorValue = old('horometro_odometro', $empleado?->horometro_odometro);
    $medidorValue = ucfirst(strtolower((string) $medidorValue));
@endphp

<div class="row g-3">
    @foreach($fields as [$name, $label, $type, $required])
        @php
            $value = old($name, $empleado?->{$name});
            if ($value instanceof \Illuminate\Support\Carbon) {
                $value = $value->format('Y-m-d');
            }
        @endphp

        <div class="col-12 col-md-6 col-xl-4">
            <label for="{{ $name }}" class="form-label">{{ $label }}</label>
            <input
                type="{{ $type }}"
                class="form-control @error($name) is-invalid @enderror"
                id="{{ $name }}"
                name="{{ $name }}"
                value="{{ $value }}"
                @required($required)
            >
            @error($name)
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endforeach

    <div class="col-12 col-md-6">
        <label class="form-label d-block">Estado</label>
        <div class="btn-group" role="group" aria-label="Estado de la unidad">
            @foreach(['Activo', 'Inactivo'] as $option)
                <input
                    type="radio"
                    class="btn-check @error('estado') is-invalid @enderror"
                    name="estado"
                    id="estado_{{ strtolower($option) }}"
                    value="{{ $option }}"
                    autocomplete="off"
                    @checked($estadoValue === $option)
                >
                <label class="btn btn-outline-primary" for="estado_{{ strtolower($option) }}">{{ $option }}</label>
            @endforeach
        </div>
        @error('estado')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label d-block">Horometro/odometro</label>
        <div class="btn-group" role="group" aria-label="Tipo de medicion">
            @foreach(['Horometro', 'Odometro'] as $option)
                <input
                    type="radio"
                    class="btn-check @error('horometro_odometro') is-invalid @enderror"
                    name="horometro_odometro"
                    id="medidor_{{ strtolower($option) }}"
                    value="{{ $option }}"
                    autocomplete="off"
                    @checked($medidorValue === $option)
                >
                <label class="btn btn-outline-primary" for="medidor_{{ strtolower($option) }}">{{ $option }}</label>
            @endforeach
        </div>
        @error('horometro_odometro')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 {{ $medidorValue === 'Horometro' ? '' : 'd-none' }}" id="horometroPanel">
        <div class="border rounded p-3 bg-light">
            <label for="horometro_horas" class="form-label">Reloj del horometro (horas del ciclo actual)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                max="999.99"
                class="form-control @error('horometro_horas') is-invalid @enderror"
                id="horometro_horas"
                name="horometro_horas"
                value="{{ old('horometro_horas', $empleado?->horometro_horas ?? 0) }}"
            >
            <div class="form-text">
                El ciclo se reinicia al llegar a 1000 horas. Alertas: 75, 200, 325, 450, 575, 700, 825 y 950 horas.
            </div>
            @error('horometro_horas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <label for="descripcion" class="form-label">Descripcion</label>
        <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $empleado?->descripcion) }}</textarea>
        @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="refacciones" class="form-label">Refacciones</label>
        <textarea class="form-control @error('refacciones') is-invalid @enderror" id="refacciones" name="refacciones" rows="3">{{ old('refacciones', $empleado?->refacciones) }}</textarea>
        @error('refacciones')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="foto" class="form-label">Foto de la unidad</label>
        <input
            type="file"
            class="form-control d-none @error('foto') is-invalid @enderror"
            id="foto"
            name="foto"
            accept="image/*"
        >
        <input
            type="file"
            class="form-control d-none @error('foto_trasera') is-invalid @enderror"
            id="foto_trasera"
            name="foto_trasera"
            accept="image/*"
            capture="environment"
        >
        <input
            type="file"
            class="form-control d-none @error('foto_delantera') is-invalid @enderror"
            id="foto_delantera"
            name="foto_delantera"
            accept="image/*"
            capture="user"
        >
        <div class="d-grid d-sm-flex gap-2">
            <button type="button" class="btn btn-outline-primary" id="tomarFotoTraseraBtn">Camara trasera</button>
            <button type="button" class="btn btn-outline-primary" id="tomarFotoDelanteraBtn">Camara delantera</button>
            <button type="button" class="btn btn-outline-secondary" id="agregarFotoBtn">Agregar foto</button>
        </div>
        <div class="border rounded p-2 mt-2 d-none" id="camaraPanel">
            <video id="camaraVideo" class="w-100 rounded bg-dark" style="max-width: 420px;" autoplay playsinline></video>
            <canvas id="camaraCanvas" class="d-none"></canvas>
            <div class="d-grid d-sm-flex gap-2 mt-2">
                <button type="button" class="btn btn-primary" id="capturarFotoBtn">Usar esta foto</button>
                <button type="button" class="btn btn-outline-secondary" id="cerrarCamaraBtn">Cancelar</button>
            </div>
            <div class="small text-muted mt-1" id="camaraMensaje"></div>
        </div>
        <div class="mt-2">
            <img
                src="{{ $empleado?->tieneFoto() ? route('empleados.foto', $empleado) : '' }}"
                alt="Foto de la unidad"
                class="img-thumbnail {{ $empleado?->tieneFoto() ? '' : 'd-none' }}"
                id="fotoPreview"
                style="max-width: 180px;"
            >
            <div class="small text-muted mt-1" id="fotoNombre"></div>
            @if($empleado?->tieneFoto() && ! $empleado?->es_easter_egg)
                <div class="mt-2">
                    <button
                        type="submit"
                        form="deletePhotoForm"
                        class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('Deseas eliminar la foto de esta unidad?')"
                    >
                        Eliminar foto
                    </button>
                </div>
            @endif
        </div>
        @error('foto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @error('foto_trasera')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('foto_delantera')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="poliza_pdf" class="form-label">Anexar poliza en PDF</label>
        <input type="file" class="form-control @error('poliza_pdf') is-invalid @enderror" id="poliza_pdf" name="poliza_pdf" accept="application/pdf">
        {{-- Si no se sube archivo, Laravel busca por clave en CONCRETO2/Polizas. --}}
        @if($empleado?->tienePolizaPdf())
            <a class="small d-inline-block mt-1" href="{{ route('empleados.pdf', [$empleado, 'poliza']) }}" target="_blank">Ver poliza actual</a>
        @endif
        @error('poliza_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="factura_pdf" class="form-label">Anexar factura en PDF</label>
        <input type="file" class="form-control @error('factura_pdf') is-invalid @enderror" id="factura_pdf" name="factura_pdf" accept="application/pdf">
        {{-- Al subir una factura nueva se elimina la anterior guardada. --}}
        @if($empleado?->tieneFacturaPdf())
            <a class="small d-inline-block mt-1" href="{{ route('empleados.pdf', [$empleado, 'factura']) }}" target="_blank">Ver factura actual</a>
        @endif
        @error('factura_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const input = document.getElementById('foto');
            const inputTrasera = document.getElementById('foto_trasera');
            const inputDelantera = document.getElementById('foto_delantera');
            const tomarTraseraBtn = document.getElementById('tomarFotoTraseraBtn');
            const tomarDelanteraBtn = document.getElementById('tomarFotoDelanteraBtn');
            const agregarBtn = document.getElementById('agregarFotoBtn');
            const preview = document.getElementById('fotoPreview');
            const nombre = document.getElementById('fotoNombre');
            const panel = document.getElementById('camaraPanel');
            const video = document.getElementById('camaraVideo');
            const canvas = document.getElementById('camaraCanvas');
            const capturarBtn = document.getElementById('capturarFotoBtn');
            const cerrarBtn = document.getElementById('cerrarCamaraBtn');
            const mensaje = document.getElementById('camaraMensaje');
            const horometroPanel = document.getElementById('horometroPanel');
            const medidorHorometro = document.getElementById('medidor_horometro');
            const medidorOdometro = document.getElementById('medidor_odometro');
            let stream = null;

            const actualizarPanelHorometro = () => {
                if (!horometroPanel || !medidorHorometro) {
                    return;
                }

                horometroPanel.classList.toggle('d-none', !medidorHorometro.checked);
            };

            medidorHorometro?.addEventListener('change', actualizarPanelHorometro);
            medidorOdometro?.addEventListener('change', actualizarPanelHorometro);
            actualizarPanelHorometro();

            if (!input || !inputTrasera || !inputDelantera || !tomarTraseraBtn || !tomarDelanteraBtn || !agregarBtn || !preview || !nombre || !panel || !video || !canvas || !capturarBtn || !cerrarBtn || !mensaje) {
                return;
            }

            const cerrarCamara = () => {
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }

                video.srcObject = null;
                panel.classList.add('d-none');
            };

            const abrirCamara = async (facingMode) => {
                cerrarCamara();
                mensaje.textContent = 'Abriendo camara...';
                panel.classList.remove('d-none');

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { exact: facingMode } },
                        audio: false,
                    });
                } catch (error) {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode },
                            audio: false,
                        });
                    } catch (fallbackError) {
                        mensaje.textContent = 'No se pudo abrir la camara. Revisa permisos del navegador y usa localhost o HTTPS.';
                        return;
                    }
                }

                video.srcObject = stream;
                mensaje.textContent = facingMode === 'environment'
                    ? 'Camara trasera lista.'
                    : 'Camara delantera lista.';
            };

            tomarTraseraBtn.addEventListener('click', () => abrirCamara('environment'));

            tomarDelanteraBtn.addEventListener('click', () => abrirCamara('user'));

            agregarBtn.addEventListener('click', () => {
                cerrarCamara();
                input.click();
            });

            const mostrarPreview = (inputSeleccionado) => {
                for (const field of [input, inputTrasera, inputDelantera]) {
                    if (field !== inputSeleccionado) {
                        field.value = '';
                    }
                }

                const file = inputSeleccionado.files?.[0];

                if (!file) {
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
                nombre.textContent = file.name;
            };

            input.addEventListener('change', () => {
                const file = input.files?.[0];

                if (!file) {
                    return;
                }

                mostrarPreview(input);
            });

            inputTrasera.addEventListener('change', () => mostrarPreview(inputTrasera));
            inputDelantera.addEventListener('change', () => mostrarPreview(inputDelantera));

            capturarBtn.addEventListener('click', () => {
                if (!stream || !video.videoWidth || !video.videoHeight) {
                    mensaje.textContent = 'La camara aun no esta lista.';
                    return;
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob((blob) => {
                    if (!blob) {
                        mensaje.textContent = 'No se pudo tomar la foto.';
                        return;
                    }

                    const file = new File([blob], `foto-unidad-${Date.now()}.jpg`, { type: 'image/jpeg' });
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                    mostrarPreview(input);
                    cerrarCamara();
                }, 'image/jpeg', 0.9);
            });

            cerrarBtn.addEventListener('click', cerrarCamara);
        })();
    </script>
@endpush
