<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class EmpleadoApiController extends Controller
{
    public function __construct()
    {
        // Protege todos los endpoints de esta API con tokens Sanctum.
        $this->middleware('auth:sanctum');
    }

    // GET /api/unidades
    public function index()
    {
        // Devuelve todas las unidades en formato JSON.
        $empleados = Empleado::when(Schema::hasColumn('unidades', 'es_easter_egg'), fn ($query) => $query->where('es_easter_egg', false))->latest()->get();
        return response()->json(['data' => $empleados]);
    }

    // POST /api/unidades
    public function store(Request $request)
    {
        $data = $this->validateEmpleado($request);
        $empleado = Empleado::create($data);

        return response()->json(['data' => $empleado], 201);
    }

    // GET /api/unidades/{empleado}
    public function show(Empleado $empleado)
    {
        return response()->json(['data' => $empleado]);
    }

    // PUT/PATCH /api/unidades/{empleado}
    public function update(Request $request, Empleado $empleado)
    {
        $data = $this->validateEmpleado($request);
        $empleado->update($data);

        return response()->json(['data' => $empleado]);
    }

    // DELETE /api/unidades/{empleado}
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();
        return response()->json(null, 204);
    }

    private function validateEmpleado(Request $request): array
    {
        return $request->validate([
            'clave' => ['required', 'string', 'max:100'],
            'nombre_equipo' => ['required', 'string', 'max:255'],
            'fecha_alta' => ['nullable', 'date'],
            'marca_modelo' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'poliza' => ['nullable', 'string', 'max:255'],
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
            'disponibilidad' => ['nullable', 'string', 'max:100'],
            'refacciones' => ['nullable', 'string'],
            'factura' => ['nullable', 'string', 'max:255'],
            'tipo_filtro' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
