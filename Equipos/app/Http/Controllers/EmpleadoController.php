<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    private const QR_DOWNLOAD_SIZE = 1024;

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $empleados = Empleado::with('odometroRegistros')
            ->when(Schema::hasColumn('unidades', 'es_easter_egg'), fn ($query) => $query->where('es_easter_egg', false))
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($query) use ($busqueda) {
                    $query->where('clave', 'like', "%{$busqueda}%")
                        ->orWhere('nombre_equipo', 'like', "%{$busqueda}%")
                        ->orWhere('marca_modelo', 'like', "%{$busqueda}%")
                        ->orWhere('modelo', 'like', "%{$busqueda}%")
                        ->orWhere('numero_serie', 'like', "%{$busqueda}%")
                        ->orWhere('placas', 'like', "%{$busqueda}%");
                });
            })
            ->latest()
            ->get();

        return view('Empleados.index', compact('empleados', 'busqueda'));
    }

    public function create()
    {
        return view('Empleados.create');
    }

    public function store(Request $request)
    {
        $data = $this->validarEmpleado($request);
        $this->guardarFotoSubida($request, $data);
        $this->guardarPdfSubido($request, $data, 'poliza_pdf', 'poliza_pdf_path');
        $this->guardarPdfSubido($request, $data, 'factura_pdf', 'factura_pdf_path');

        $empleado = Empleado::create($data);
        $this->asignarPdfPorClave($empleado, true, true);

        return redirect()
            ->route('empleados.show', $empleado)
            ->with('success', 'Unidad creada correctamente. Se genero su codigo QR.');
    }

    public function show(Request $request, string $empleado)
    {
        $clave = $request->query('clave');

        $empleado = Empleado::query()
            ->where('clave', $empleado)
            ->when(is_numeric($empleado), fn ($query) => $query->orWhere('id', $empleado))
            ->when(filled($clave), fn ($query) => $query->orWhere('clave', $clave))
            ->firstOrFail();

        return view('Empleados.show', compact('empleado'));
    }

    public function edit(Empleado $empleado)
    {
        abort_if($empleado->es_easter_egg, 404);

        return view('Empleados.edit', compact('empleado'));
    }

    public function creadores()
    {
        $empleado = $this->obtenerEasterEgg();

        return view('Empleados.show', compact('empleado'));
    }

    public function editarCreadores()
    {
        $empleado = $this->obtenerEasterEgg();

        if ($empleado->easter_egg_editado) {
            return redirect()
                ->route('creadores.show')
                ->with('error', 'La tarjeta de creadores ya fue editada una vez.');
        }

        return view('Empleados.edit', [
            'empleado' => $empleado,
            'tituloFormulario' => 'Editar tarjeta de creadores',
            'formAction' => route('creadores.update'),
            'backUrl' => route('creadores.show'),
            'submitLabel' => 'Guardar tarjeta',
        ]);
    }

    public function cargaMasiva()
    {
        return view('Empleados.carga-masiva');
    }

    public function fotosMasivas()
    {
        return view('Empleados.fotos-masivas');
    }

    public function catalogoQr()
    {
        $empleados = Empleado::when(Schema::hasColumn('unidades', 'es_easter_egg'), fn ($query) => $query->where('es_easter_egg', false))->orderBy('clave')->get();

        return view('Empleados.catalogo-qr', compact('empleados'));
    }

    public function descargarQr(Empleado $empleado): Response
    {
        $qr = Http::timeout(15)->get($empleado->qrImagenUrl(self::QR_DOWNLOAD_SIZE));

        abort_unless($qr->successful(), 502, 'No se pudo generar el QR.');

        return response($qr->body(), 200, [
            'Content-Type' => $qr->header('Content-Type', 'image/png'),
            'Content-Disposition' => 'attachment; filename="' . $this->nombreArchivoQr($empleado) . '"',
        ]);
    }

    public function descargarQrSeleccionados(Request $request)
    {
        $data = $request->validate([
            'unidades' => ['required', 'array', 'min:1'],
            'unidades.*' => ['integer', 'exists:unidades,id'],
        ]);

        $empleados = Empleado::when(Schema::hasColumn('unidades', 'es_easter_egg'), fn ($query) => $query->where('es_easter_egg', false))->whereIn('id', $data['unidades'])->orderBy('clave')->get();
        $zipPath = tempnam(storage_path('app'), 'qr-catalogo-');
        $zip = new \ZipArchive();

        abort_unless($zip->open($zipPath, \ZipArchive::OVERWRITE) === true, 500, 'No se pudo crear el archivo ZIP.');

        $agregados = 0;

        foreach ($empleados as $empleado) {
            $qr = Http::timeout(15)->get($empleado->qrImagenUrl(self::QR_DOWNLOAD_SIZE));

            if (! $qr->successful()) {
                continue;
            }

            $zip->addFromString($this->nombreArchivoQr($empleado), $qr->body());
            $agregados++;
        }

        $zip->close();

        abort_unless($agregados > 0, 502, 'No se pudo generar ningun QR.');

        return response()->download($zipPath, 'catalogo-qr.zip')->deleteFileAfterSend(true);
    }

    public function guardarCargaMasiva(Request $request)
    {
        $data = $request->validate([
            'tipo_documento' => ['required', Rule::in(['poliza', 'factura'])],
            'archivos' => ['required', 'array', 'min:1'],
            'archivos.*' => ['file', 'extensions:pdf', 'max:51200'],
        ]);

        $resultado = [
            'guardados' => 0,
            'sin_equipo' => [],
        ];
        $empleados = Empleado::all();

        foreach ($request->file('archivos', []) as $file) {
            $empleado = $this->buscarEmpleadoPorNombreArchivo($file->getClientOriginalName(), $empleados);

            if (! $empleado) {
                $resultado['sin_equipo'][] = $file->getClientOriginalName();
                continue;
            }

            $this->guardarPdfMasivo($empleado, $file, $data['tipo_documento']);
            $resultado['guardados']++;
        }

        return redirect()
            ->route('empleados.carga-masiva')
            ->with('success', 'Carga masiva procesada.')
            ->with('resultado_carga', $resultado);
    }

    public function guardarFotosMasivas(Request $request)
    {
        $request->validate([
            'fotos' => ['required', 'array', 'min:1'],
            'fotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $resultado = [
            'guardadas' => 0,
            'sin_equipo' => [],
        ];
        $empleados = Empleado::all();

        foreach ($request->file('fotos', []) as $file) {
            $empleado = $this->buscarEmpleadoPorNombreArchivo($file->getClientOriginalName(), $empleados);

            if (! $empleado) {
                $resultado['sin_equipo'][] = $file->getClientOriginalName();
                continue;
            }

            $this->guardarFotoMasiva($empleado, $file);
            $resultado['guardadas']++;
        }

        return redirect()
            ->route('empleados.fotos-masivas')
            ->with('success', 'Carga masiva de fotos procesada.')
            ->with('resultado_fotos', $resultado);
    }

    public function importarCsv()
    {
        return view('Empleados.importar-csv');
    }

    public function descargarPlantillaCsv(): Response
    {
        $headers = $this->encabezadosCsv();
        $sample = [
            'EQ-001',
            'Compresor principal',
            '2026-06-11',
            'Atlas Copco XAS',
            'XAS-185',
            'ABC-123',
            'Serie-001',
            'Serie-extra-001',
            'Pagada',
            'TC-001',
            'Diesel',
            'Aceite/aire',
            'Maquinaria',
            '2024-01-15',
            'Activo',
            'Proveedor demo',
            'Horometro',
            'Unidad operativa',
            'Filtro de aceite',
        ];

        $csv = $this->generarLineaCsv($headers) . $this->generarLineaCsv($sample);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla-unidades.csv"',
        ]);
    }

    public function guardarImportacionCsv(Request $request)
    {
        $request->validate([
            'archivo_csv' => ['required', 'file', 'extensions:csv,txt', 'max:10240'],
        ]);

        $resultado = $this->importarRegistrosCsv($request->file('archivo_csv')->getRealPath());

        return redirect()
            ->route('empleados.importar-csv')
            ->with('success', 'Importacion CSV procesada.')
            ->with('resultado_csv', $resultado);
    }

    public function update(Request $request, Empleado $empleado)
    {
        abort_if($empleado->es_easter_egg, 404);

        $data = $this->validarEmpleado($request);
        $this->guardarFotoSubida($request, $data, $empleado);
        $this->guardarPdfSubido($request, $data, 'poliza_pdf', 'poliza_pdf_path', $empleado);
        $this->guardarPdfSubido($request, $data, 'factura_pdf', 'factura_pdf_path', $empleado);

        $empleado->update($data);
        $this->asignarPdfPorClave($empleado, ! $request->hasFile('poliza_pdf'), ! $request->hasFile('factura_pdf'));

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Unidad actualizada correctamente.');
    }

    public function actualizarCreadores(Request $request)
    {
        $empleado = $this->obtenerEasterEgg();

        if ($empleado->easter_egg_editado) {
            return redirect()
                ->route('creadores.show')
                ->with('error', 'La tarjeta de creadores ya fue editada una vez.');
        }

        $data = $this->validarEmpleado($request);
        $data['clave'] = Empleado::EASTER_EGG_CLAVE;
        $data['es_easter_egg'] = true;
        $data['easter_egg_editado'] = true;

        $this->guardarFotoSubida($request, $data, $empleado);
        $this->guardarPdfSubido($request, $data, 'poliza_pdf', 'poliza_pdf_path', $empleado);
        $this->guardarPdfSubido($request, $data, 'factura_pdf', 'factura_pdf_path', $empleado);

        $empleado->update($data);

        return redirect()
            ->route('creadores.show')
            ->with('success', 'Tarjeta de creadores guardada correctamente.');
    }

    public function destroy(Empleado $empleado)
    {
        abort_if($empleado->es_easter_egg, 404);

        $this->eliminarArchivoLocal($empleado->foto_path);
        $this->eliminarPdf($empleado->poliza_pdf_path);
        $this->eliminarPdf($empleado->factura_pdf_path);
        $empleado->delete();

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Unidad eliminada correctamente.');
    }

    public function destroyAll()
    {
        $empleados = Empleado::when(Schema::hasColumn('unidades', 'es_easter_egg'), fn ($query) => $query->where('es_easter_egg', false))->get();

        foreach ($empleados as $empleado) {
            $this->eliminarArchivoLocal($empleado->foto_path);
            $this->eliminarPdf($empleado->poliza_pdf_path);
            $this->eliminarPdf($empleado->factura_pdf_path);
        }

        Empleado::when(Schema::hasColumn('unidades', 'es_easter_egg'), fn ($query) => $query->where('es_easter_egg', false))->delete();

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Todas las unidades fueron eliminadas correctamente.');
    }

    public function verPdf(Empleado $empleado, string $tipo): Response
    {
        abort_unless(in_array($tipo, ['poliza', 'factura'], true), 404);

        $path = $tipo === 'poliza' ? $empleado->poliza_pdf_path : $empleado->factura_pdf_path;
        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    public function verFoto(Empleado $empleado): Response
    {
        abort_unless($empleado->tieneFoto(), 404);

        return response(Storage::disk('local')->get($empleado->foto_path), 200, [
            'Content-Type' => Storage::disk('local')->mimeType($empleado->foto_path) ?: 'image/jpeg',
            'Content-Disposition' => 'inline; filename="' . basename($empleado->foto_path) . '"',
        ]);
    }

    public function eliminarFoto(Empleado $empleado)
    {
        $this->eliminarArchivoLocal($empleado->foto_path);
        $empleado->forceFill(['foto_path' => null])->save();

        return back()->with('success', 'Foto eliminada correctamente.');
    }

    public function iniciarHorometro(Empleado $empleado)
    {
        abort_unless($empleado->usaHorometro(), 422, 'Esta unidad no usa horometro.');

        if (! $empleado->horometro_en_marcha) {
            $empleado->forceFill([
                'horometro_en_marcha' => true,
                'horometro_iniciado_en' => now(),
            ])->save();
        }

        return back()->with('success', 'Horometro iniciado.');
    }

    public function detenerHorometro(Empleado $empleado)
    {
        abort_unless($empleado->usaHorometro(), 422, 'Esta unidad no usa horometro.');

        $horas = $empleado->horometroHorasActuales();

        $empleado->forceFill([
            'horometro_horas' => $empleado->normalizarHorasCiclo($horas),
            'horometro_en_marcha' => false,
            'horometro_iniciado_en' => null,
        ])->save();

        return back()->with('success', 'Horometro detenido y horas guardadas.');
    }

    public function reiniciarHorometro(Empleado $empleado)
    {
        abort_unless($empleado->usaHorometro(), 422, 'Esta unidad no usa horometro.');

        $empleado->forceFill([
            'horometro_horas' => 0,
            'horometro_en_marcha' => false,
            'horometro_iniciado_en' => null,
        ])->save();

        return back()->with('success', 'Horometro reiniciado.');
    }

    public function guardarOdometro(Request $request, Empleado $empleado)
    {
        abort_unless($empleado->usaOdometro(), 422, 'Esta unidad no usa odometro.');

        $data = $request->validate([
            'kilometros' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'registrado_en' => ['nullable', 'date'],
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        $empleado->odometroRegistros()->create($data);

        return back()->with('success', 'Kilometros agregados al historial del odometro.');
    }

    public function registrarServicio(Request $request, Empleado $empleado)
    {
        abort_unless($empleado->usaHorometro() || $empleado->usaOdometro(), 422, 'Esta unidad no tiene medidor activo.');

        $data = $request->validate([
            'fecha_servicio' => ['required', 'date'],
            'mecanico' => ['required', 'string', 'max:255'],
            'lugar' => ['required', 'string', 'max:255'],
            'supervisor' => ['required', 'string', 'max:255'],
        ]);

        $alerta = $empleado->usaHorometro()
            ? $empleado->alertaHorometroActual()
            : $empleado->alertaOdometroActual();
        $mensaje = $alerta['mensaje'] ?? 'Servicio registrado.';
        $tipoServicio = str_contains(strtolower($mensaje), 'gama') ? 'Gama completa' : 'Medio servicio';
        $medicion = $empleado->usaHorometro()
            ? number_format($empleado->horometroHorasActuales(), 2) . ' h'
            : number_format($empleado->odometroKilometrosCiclo(), 2) . ' km';

        $empleado->servicioRegistros()->create([
            'medidor' => $empleado->horometro_odometro,
            'tipo_servicio' => $tipoServicio,
            'medicion' => $medicion,
            'fecha_servicio' => $data['fecha_servicio'],
            'mecanico' => $data['mecanico'],
            'lugar' => $data['lugar'],
            'supervisor' => $data['supervisor'],
            'mensaje' => $mensaje,
        ]);

        if ($empleado->usaHorometro()) {
            $empleado->forceFill([
                'horometro_horas' => 0,
                'horometro_en_marcha' => false,
                'horometro_iniciado_en' => null,
            ])->save();
        }

        if ($empleado->usaOdometro()) {
            $empleado->odometroRegistros()->delete();
        }

        return back()->with('success', 'Servicio registrado y medidor reiniciado.');
    }

    private function nombreArchivoQr(Empleado $empleado): string
    {
        return 'qr-' . Str::slug($empleado->clave ?: 'unidad-' . $empleado->id) . '.png';
    }

    private function obtenerEasterEgg(): Empleado
    {
        $empleado = Empleado::firstOrCreate(
            ['clave' => Empleado::EASTER_EGG_CLAVE],
            $this->datosInicialesCreadores()
        );

        if (! $empleado->es_easter_egg) {
            $empleado->forceFill([
                'es_easter_egg' => true,
                'easter_egg_editado' => false,
            ])->save();
        }

        return $empleado;
    }

    private function datosInicialesCreadores(): array
    {
        return [
            'nombre_equipo' => 'Creadores',
            'marca_modelo' => 'Coca-Cola',
            'modelo' => 'Easter egg',
            'numero_serie' => Empleado::EASTER_EGG_BARCODE,
            'familia' => 'Creadores',
            'estado' => 'Activo',
            'proveedor' => 'Equipo de desarrollo',
            'descripcion' => "Creadores:
Ismael
Companero 1
Companero 2
Companero 3
Companero 4",
            'refacciones' => 'Escaneado desde el codigo Coca-Cola.',
            'es_easter_egg' => true,
            'easter_egg_editado' => false,
        ];
    }

    private function validarEmpleado(Request $request): array
    {
        $data = $request->validate([
            'clave' => ['nullable', 'string', 'max:100'],
            'nombre_equipo' => ['nullable', 'string', 'max:255'],
            'fecha_alta' => ['nullable', 'date'],
            'marca_modelo' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'numero_serie_eq_adicional' => ['nullable', 'string', 'max:100'],
            'placas' => ['nullable', 'string', 'max:50'],
            'tenencia' => ['nullable', 'string', 'max:255'],
            'tarjeta_circulacion' => ['nullable', 'string', 'max:255'],
            'tipo_motor' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'familia' => ['nullable', 'string', 'max:150'],
            'fecha_fabricacion' => ['nullable', 'date'],
            'asignado_a' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in(['Activo', 'Inactivo'])],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'horometro_odometro' => ['nullable', Rule::in(['Horometro', 'Odometro'])],
            'horometro_horas' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'disponibilidad' => ['nullable', 'string', 'max:100'],
            'refacciones' => ['nullable', 'string'],
            'tipo_filtro' => ['nullable', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'foto_trasera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'foto_delantera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'poliza_pdf' => ['nullable', 'file', 'extensions:pdf', 'max:51200'],
            'factura_pdf' => ['nullable', 'file', 'extensions:pdf', 'max:51200'],
        ]);

        unset($data['foto'], $data['foto_trasera'], $data['foto_delantera'], $data['poliza_pdf'], $data['factura_pdf']);

        if (($data['horometro_odometro'] ?? null) !== 'Horometro') {
            $data['horometro_horas'] = 0;
            $data['horometro_en_marcha'] = false;
            $data['horometro_iniciado_en'] = null;
        }

        return $this->completarDatosEquipo($data);
    }

    private function completarDatosEquipo(array $data): array
    {
        $data['clave'] = filled($data['clave'] ?? null) ? $data['clave'] : 'SIN-CLAVE-' . Str::upper(Str::random(8));
        $data['nombre_equipo'] = filled($data['nombre_equipo'] ?? null) ? $data['nombre_equipo'] : 'Sin nombre';
        $data['estado'] = filled($data['estado'] ?? null) ? $data['estado'] : 'Activo';

        return $data;
    }

    private function guardarFotoSubida(Request $request, array &$data, ?Empleado $empleado = null): void
    {
        $input = collect(['foto', 'foto_trasera', 'foto_delantera'])
            ->first(fn (string $input) => $request->hasFile($input));

        if (! $input) {
            return;
        }

        $this->eliminarArchivoLocal($empleado?->foto_path);

        $clave = Str::slug($data['clave'] ?? 'unidad');
        $file = $request->file($input);
        $name = 'foto-' . $clave . '-' . time() . '.' . $file->extension();

        $data['foto_path'] = $file->storeAs('vehiculos/fotos', $name, 'local');
    }

    private function guardarPdfSubido(Request $request, array &$data, string $input, string $column, ?Empleado $empleado = null): void
    {
        if (! $request->hasFile($input)) {
            return;
        }

        $this->eliminarPdf($empleado?->{$column});

        $clave = Str::slug($data['clave'] ?? 'unidad');
        $tipo = str_replace('_pdf', '', $input);
        $file = $request->file($input);
        $name = $tipo . '-' . $clave . '-' . time() . '.pdf';

        $data[$column] = $file->storeAs('vehiculos/' . $tipo, $name, 'local');
    }

    private function asignarPdfPorClave(Empleado $empleado, bool $buscarPoliza = true, bool $buscarFactura = true): void
    {
        $updates = [];

        if ($buscarPoliza && blank($empleado->poliza_pdf_path)) {
            $updates['poliza_pdf_path'] = $this->copiarPdfCoincidente($empleado->clave, 'Polizas', 'poliza');
        }

        if ($buscarFactura && blank($empleado->factura_pdf_path)) {
            $updates['factura_pdf_path'] = $this->copiarPdfCoincidente($empleado->clave, 'Facturas', 'factura');
        }

        $updates = array_filter($updates);

        if ($updates !== []) {
            $empleado->forceFill($updates)->save();
        }
    }

    private function copiarPdfCoincidente(?string $clave, string $carpeta, string $tipo): ?string
    {
        if (blank($clave)) {
            return null;
        }

        $base = base_path('CONCRETO2/' . $carpeta);

        if (! is_dir($base)) {
            return null;
        }

        $claveNormalizada = $this->normalizarTexto($clave);

        foreach (File::allFiles($base) as $file) {
            if (strtolower($file->getExtension()) !== 'pdf') {
                continue;
            }

            if (! str_contains($this->normalizarTexto($file->getFilenameWithoutExtension()), $claveNormalizada)) {
                continue;
            }

            $destino = 'vehiculos/' . $tipo . '/' . Str::slug($clave) . '-' . time() . '.pdf';
            Storage::disk('local')->put($destino, File::get($file->getPathname()));

            return $destino;
        }

        return null;
    }

    private function buscarEmpleadoPorNombreArchivo(string $filename, $empleados): ?Empleado
    {
        $nombreNormalizado = $this->normalizarTexto(pathinfo($filename, PATHINFO_FILENAME));

        return $empleados->first(function (Empleado $empleado) use ($nombreNormalizado) {
            return filled($empleado->clave)
                && str_contains($nombreNormalizado, $this->normalizarTexto($empleado->clave));
        });
    }

    private function importarRegistrosCsv(string $path): array
    {
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($this->detectarDelimitadorCsv($path), '"', '\\');

        $headers = [];
        $resultado = [
            'creados' => 0,
            'actualizados' => 0,
            'errores' => [],
        ];

        foreach ($file as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $row = array_map(fn ($value) => trim((string) $value), $row);

            if ($headers === []) {
                $headers = $this->normalizarEncabezadosCsv($row);
                $faltantes = array_diff($this->encabezadosCsvRequeridos(), $headers);

                if ($faltantes !== []) {
                    $resultado['errores'][] = 'Faltan columnas: ' . implode(', ', $faltantes);
                    return $resultado;
                }

                continue;
            }

            if ($this->filaCsvVacia($row) || $this->filaCsvEsEncabezadoRepetido($row)) {
                continue;
            }

            $values = array_slice(array_pad($row, count($headers), ''), 0, count($headers));
            $data = array_combine($headers, $values);
            $data = array_merge(array_fill_keys($this->encabezadosCsv(), ''), $data);
            $data = $this->normalizarRegistroCsv($data);
            $line = $index + 1;

            if (! in_array($data['estado'], ['Activo', 'Inactivo'], true)) {
                $resultado['errores'][] = 'Linea ' . $line . ': estado debe ser Activo o Inactivo.';
                continue;
            }

            if (filled($data['horometro_odometro']) && ! in_array($data['horometro_odometro'], ['Horometro', 'Odometro'], true)) {
                $resultado['errores'][] = 'Linea ' . $line . ': horometro_odometro debe ser Horometro u Odometro.';
                continue;
            }

            $exists = Empleado::where('clave', $data['clave'])->exists();
            $empleado = Empleado::updateOrCreate(['clave' => $data['clave']], $data);
            $this->asignarPdfPorClave($empleado, true, true);

            $exists ? $resultado['actualizados']++ : $resultado['creados']++;
        }

        return $resultado;
    }

    private function encabezadosCsv(): array
    {
        return [
            'clave',
            'nombre_equipo',
            'fecha_alta',
            'marca_modelo',
            'modelo',
            'placas',
            'numero_serie',
            'numero_serie_eq_adicional',
            'tenencia',
            'tarjeta_circulacion',
            'tipo_motor',
            'tipo_filtro',
            'familia',
            'fecha_fabricacion',
            'estado',
            'proveedor',
            'horometro_odometro',
            'descripcion',
            'refacciones',
        ];
    }

    private function encabezadosCsvRequeridos(): array
    {
        return array_values(array_diff($this->encabezadosCsv(), ['placas']));
    }

    private function normalizarEncabezadosCsv(array $headers): array
    {
        return array_map(function (string $header): string {
            $header = trim(str_replace("\xEF\xBB\xBF", '', $header));

            return $this->resolverEncabezadoCsv($header);
        }, $headers);
    }

    private function resolverEncabezadoCsv(string $header): string
    {
        $normalizado = $this->normalizarTexto($header);

        if (isset($this->aliasEncabezadosCsv()[$normalizado])) {
            return $this->aliasEncabezadosCsv()[$normalizado];
        }

        if (str_contains($normalizado, 'placa')) {
            return 'placas';
        }

        if ($normalizado === 'marca' || str_contains($normalizado, 'marca')) {
            return 'marca_modelo';
        }

        if (str_contains($normalizado, 'marca') && str_contains($normalizado, 'modelo')) {
            return 'marca_modelo';
        }

        if ($normalizado === 'modelo' || str_contains($normalizado, 'modelo')) {
            return 'modelo';
        }

        return $header;
    }

    private function aliasEncabezadosCsv(): array
    {
        return [
            'clave' => 'clave',
            'nombreequipo' => 'nombre_equipo',
            'nombredeequipo' => 'nombre_equipo',
            'fechaalta' => 'fecha_alta',
            'marca' => 'marca_modelo',
            'marcamodelo' => 'marca_modelo',
            'modelo' => 'modelo',
            'placas' => 'placas',
            'placa' => 'placas',
            'numeroserie' => 'numero_serie',
            'numerodeserie' => 'numero_serie',
            'numeroserieeqadicional' => 'numero_serie_eq_adicional',
            'numerodeserieeqadicional' => 'numero_serie_eq_adicional',
            'tenencia' => 'tenencia',
            'tarjetacirculacion' => 'tarjeta_circulacion',
            'tarjetadecirculacion' => 'tarjeta_circulacion',
            'tipomotor' => 'tipo_motor',
            'tipodemotor' => 'tipo_motor',
            'tipofiltro' => 'tipo_filtro',
            'filtrosdemotor' => 'tipo_filtro',
            'familia' => 'familia',
            'fechafabricacion' => 'fecha_fabricacion',
            'fechadefabricacion' => 'fecha_fabricacion',
            'estado' => 'estado',
            'proveedor' => 'proveedor',
            'horometroodometro' => 'horometro_odometro',
            'descripcion' => 'descripcion',
            'refacciones' => 'refacciones',
        ];
    }

    private function filaCsvVacia(array $row): bool
    {
        return collect($row)->every(fn ($value) => blank($value));
    }

    private function detectarDelimitadorCsv(string $path): string
    {
        $line = '';
        $file = fopen($path, 'r');

        if ($file !== false) {
            $line = (string) fgets($file);
            fclose($file);
        }

        $delimiters = [',', ';', "\t", '|'];
        $counts = array_map(fn (string $delimiter) => substr_count($line, $delimiter), $delimiters);
        $max = max($counts);

        return $max > 0 ? $delimiters[array_search($max, $counts, true)] : ',';
    }

    private function filaCsvEsEncabezadoRepetido(array $row): bool
    {
        $primeraCelda = $this->normalizarTexto($row[0] ?? '');
        $segundaCelda = $this->normalizarTexto($row[1] ?? '');

        return $primeraCelda === 'clave'
            && in_array($segundaCelda, ['nombreequipo', 'nombredeequipo'], true);
    }

    private function normalizarRegistroCsv(array $data): array
    {
        $data = array_map(fn ($value) => trim((string) $value), $data);
        $data['estado'] = ucfirst(strtolower($data['estado'] ?: 'Activo'));
        $data['horometro_odometro'] = $data['horometro_odometro'] !== ''
            ? ucfirst(strtolower($data['horometro_odometro']))
            : null;

        foreach (['fecha_alta', 'fecha_fabricacion'] as $dateField) {
            $data[$dateField] = $this->normalizarFechaCsv($data[$dateField] ?? '');
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $this->completarDatosEquipo($data);
    }

    private function normalizarFechaCsv(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date && $date->format('Y-m-d') === $value) {
            return $value;
        }

        $normalizada = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\bdel\b/', 'de')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        if (! preg_match('/^(\d{1,2})(?: de)? ([a-z]+)(?: de)? (\d{4})$/', $normalizada, $matches)) {
            return null;
        }

        $months = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'setiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
        ];

        $day = (int) $matches[1];
        $month = $months[$matches[2]] ?? null;
        $year = (int) $matches[3];

        if (! $month || ! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function generarLineaCsv(array $row): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $row);
        rewind($stream);
        $line = stream_get_contents($stream);
        fclose($stream);

        return $line;
    }

    private function guardarPdfMasivo(Empleado $empleado, $file, string $tipo): void
    {
        $column = $tipo === 'poliza' ? 'poliza_pdf_path' : 'factura_pdf_path';

        $this->eliminarPdf($empleado->{$column});

        $name = $tipo . '-' . Str::slug($empleado->clave) . '-' . time() . '-' . Str::random(6) . '.pdf';
        $path = $file->storeAs('vehiculos/' . $tipo, $name, 'local');

        $empleado->forceFill([$column => $path])->save();
    }

    private function guardarFotoMasiva(Empleado $empleado, $file): void
    {
        $this->eliminarArchivoLocal($empleado->foto_path);

        $name = 'foto-' . Str::slug($empleado->clave) . '-' . time() . '-' . Str::random(6) . '.' . $file->extension();
        $path = $file->storeAs('vehiculos/fotos', $name, 'local');

        $empleado->forceFill(['foto_path' => $path])->save();
    }

    private function eliminarPdf(?string $path): void
    {
        $this->eliminarArchivoLocal($path);
    }

    private function eliminarArchivoLocal(?string $path): void
    {
        if (filled($path) && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    private function normalizarTexto(string $texto): string
    {
        return Str::of($texto)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }
}



