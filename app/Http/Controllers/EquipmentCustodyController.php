<?php

namespace App\Http\Controllers;

use App\Mail\EquipmentCustodyAssignedMail;
use App\Models\EquipmentCustody;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EquipmentCustodyController extends Controller
{
    public function index(): View
    {
        $records = EquipmentCustody::query()
            ->with('cancelledBy')
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        $types = EquipmentCustody::typeLabels();
        $recordsByType = [];

        foreach (array_keys($types) as $type) {
            $recordsByType[$type] = $records->where('equipment_type', $type)->values();
        }

        return view('equipment-custodies.index', [
            'equipmentTypes' => $types,
            'recordsByType' => $recordsByType,
            'registeredByDefault' => request()->user()?->username ?? request()->user()?->name ?? '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $types = array_keys(EquipmentCustody::typeLabels());

        $data = $request->validate([
            'equipment_type' => ['required', Rule::in($types)],
            'brand' => ['required', 'string', 'max:120'],
            'model' => ['required', 'string', 'max:120'],
            'serial_number' => ['required', 'string', 'max:120'],
            'accessories' => ['nullable', 'string', 'max:1000'],
            'assigned_at' => ['required', 'date'],
            'responsible' => ['required', 'string', 'max:180'],
            'notification_email' => ['nullable', 'email', 'max:190'],
            'physical_record' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = $request->user();
        $physicalRecordPath = $this->storePhysicalRecord($request);
        unset($data['physical_record']);

        $equipmentCustody = EquipmentCustody::create([
            ...$data,
            'registered_by_user_id' => $user?->id,
            'registered_by_username' => $user?->username ?: ($user?->name ?? 'Sistema'),
            'physical_record_path' => $physicalRecordPath,
        ]);

        $statusMessage = 'Resguardo registrado correctamente.';

        if ($equipmentCustody->notification_email) {
            try {
                Mail::to($equipmentCustody->notification_email)
                    ->send(new EquipmentCustodyAssignedMail($equipmentCustody));

                $statusMessage = 'Resguardo registrado y correo enviado correctamente.';
            } catch (\Throwable $exception) {
                report($exception);
                $statusMessage = 'Resguardo registrado, pero no se pudo enviar el correo de notificacion.';
            }
        }

        return redirect()
            ->route('equipment-custodies.index')
            ->with('status', $statusMessage);
    }

    public function cancel(Request $request, EquipmentCustody $equipmentCustody): RedirectResponse
    {
        if ($equipmentCustody->cancelled_at) {
            return redirect()
                ->route('equipment-custodies.index')
                ->with('status', 'Este resguardo ya habia sido cancelado.');
        }

        $equipmentCustody->update([
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('equipment-custodies.index')
            ->with('status', 'Resguardo cancelado correctamente.');
    }

    public function exportExcel(Request $request, string $type): StreamedResponse
    {
        $type = $this->resolveTypeOrFail($type);
        $typeLabels = EquipmentCustody::typeLabels();
        $label = $typeLabels[$type];

        $rows = EquipmentCustody::query()
            ->with('cancelledBy')
            ->where('equipment_type', $type)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="resguardos-' . Str::slug($type) . '.xls"',
        ];

        $callback = function () use ($rows, $label) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fwrite($out, '<html><head><meta charset="UTF-8"></head><body>');
            fwrite($out, '<table border="1" cellspacing="0" cellpadding="2">');
            fwrite($out, '<tr><th colspan="13">Resguardos de ' . e($label) . '</th></tr>');
            fwrite($out, '<tr><th>#</th><th>Marca</th><th>Modelo</th><th>Numero de serie</th><th>Accesorios</th><th>Fecha</th><th>Responsable</th><th>Registro sistemas</th><th>Correo notificacion</th><th>Resguardo fisico</th><th>Estatus</th><th>Cancelado por</th><th>Fecha cancelacion</th></tr>');

            foreach ($rows as $index => $row) {
                $status = $row->cancelled_at ? 'Cancelado' : 'Activo';
                $cells = [
                    e((string) ($index + 1)),
                    e($row->brand),
                    e($row->model),
                    e($row->serial_number),
                    e($row->accessories ?: ''),
                    e(optional($row->assigned_at)->format('Y-m-d')),
                    e($row->responsible),
                    e($row->registered_by_username),
                    e($row->notification_email ?: ''),
                    e($row->physical_record_path ? 'Adjunto' : ''),
                    e($status),
                    e($row->cancelledBy?->username ?? ''),
                    e(optional($row->cancelled_at)->format('Y-m-d H:i:s')),
                ];

                fwrite($out, '<tr><td>' . implode('</td><td>', $cells) . '</td></tr>');
            }

            fwrite($out, '</table></body></html>');
            fclose($out);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }

    public function physicalRecord(EquipmentCustody $equipmentCustody): BinaryFileResponse
    {
        if (!$equipmentCustody->physical_record_path) {
            abort(404);
        }

        if (!Storage::disk('local')->exists($equipmentCustody->physical_record_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($equipmentCustody->physical_record_path));
    }

    private function storePhysicalRecord(Request $request): ?string
    {
        if (!$request->hasFile('physical_record')) {
            return null;
        }

        $extension = $request->file('physical_record')->getClientOriginalExtension();
        $fileName = 'custody-' . Str::uuid()->toString() . '.' . $extension;
        $request->file('physical_record')->storeAs('equipment-custodies/physical-records', $fileName, 'local');

        return 'equipment-custodies/physical-records/' . $fileName;
    }

    private function resolveTypeOrFail(string $type): string
    {
        $normalized = Str::lower(trim($type));
        if (!array_key_exists($normalized, EquipmentCustody::typeLabels())) {
            abort(404);
        }

        return $normalized;
    }
}
