<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Part;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseLocation;
use App\Models\WarehouseMaterialExit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filters_entries_and_exits_by_date_and_cost_center(): void
    {
        [$user, $charcas, $matriz] = $this->context();
        $includedPart = $this->part('REPORTE-INCLUIDO', 'Material incluido');
        $oldPart = $this->part('REPORTE-ANTERIOR', 'Material anterior');
        $otherCenterPart = $this->part('REPORTE-MATRIZ', 'Material de Matriz');

        $this->entry($charcas, $includedPart, '2026-09-10 08:00:00', 7);
        $this->entry($charcas, $oldPart, '2026-08-31 08:00:00', 2);
        $this->entry($matriz, $otherCenterPart, '2026-09-10 08:00:00', 9);
        $this->exit($charcas, $includedPart, '2026-09-12 09:00:00', 3);
        $this->exit($matriz, $otherCenterPart, '2026-09-12 09:00:00', 4);

        $this->actingAs($user)->get(route('warehouse.reports.index', [
            'cost_center_id' => $charcas->id,
            'date_from' => '2026-09-01',
            'date_to' => '2026-09-15',
            'movement_type' => 'all',
        ]))
            ->assertOk()
            ->assertSee('Material incluido')
            ->assertSee('7.00')
            ->assertSee('3.00')
            ->assertDontSee('Material anterior')
            ->assertDontSee('Material de Matriz');
    }

    public function test_report_can_show_only_entries_and_export_the_filtered_csv(): void
    {
        [$user, $charcas] = $this->context();
        $part = $this->part('REPORTE-CSV', 'Filtro para CSV');
        $this->entry($charcas, $part, '2026-09-05 10:00:00', 5);
        $this->exit($charcas, $part, '2026-09-06 10:00:00', 1);
        $filters = [
            'cost_center_id' => $charcas->id,
            'date_from' => '2026-09-01',
            'date_to' => '2026-09-30',
            'movement_type' => 'entries',
        ];

        $this->actingAs($user)
            ->get(route('warehouse.reports.index', $filters))
            ->assertOk()
            ->assertSee('Entradas del periodo')
            ->assertDontSee('Salidas del periodo');

        $response = $this->actingAs($user)->get(route('warehouse.reports.export', $filters));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Filtro para CSV', $content);
        $this->assertStringContainsString('Entrada', $content);
        $this->assertStringNotContainsString('Salida', $content);
    }

    public function test_report_exports_a_formatted_and_filterable_excel_file(): void
    {
        [$user, $charcas] = $this->context();
        $part = $this->part('REPORTE-XLSX', 'Filtro para Excel');
        $this->entry($charcas, $part, '2026-09-05 10:30:00', 5.5);
        $this->exit($charcas, $part, '2026-09-06 11:45:00', 1.25);

        $response = $this->actingAs($user)->get(route('warehouse.reports.export-excel', [
            'cost_center_id' => $charcas->id,
            'date_from' => '2026-09-01',
            'date_to' => '2026-09-30',
            'movement_type' => 'all',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertDownload('reporte-almacen-zarpeo-charcas-2026-09-01-a-2026-09-30.xlsx');
        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'warehouse-report-');
        file_put_contents($temporaryFile, $content);

        try {
            $archive = new \PharData($temporaryFile);
            $worksheet = $archive['xl/worksheets/sheet1.xml']->getContent();
            $styles = $archive['xl/styles.xml']->getContent();

            $this->assertStringContainsString('<autoFilter ref="A5:P7"/>', $worksheet);
            $this->assertStringContainsString('state="frozen"', $worksheet);
            $this->assertStringContainsString('Filtro para Excel', $worksheet);
            $this->assertStringContainsString('numFmtId="164" formatCode="dd/mm/yyyy hh:mm"', $styles);
            $this->assertStringContainsString('numFmtId="165" formatCode="#,##0.00"', $styles);
        } finally {
            unset($archive);
            @unlink($temporaryFile);
        }
    }

    public function test_report_shows_top_weekly_and_monthly_material_consumption(): void
    {
        Carbon::setTestNow('2026-09-10 12:00:00');

        try {
            [$user, $charcas, $matriz] = $this->context();
            $weeklyPart = $this->part('CONSUMO-SEMANA', 'Material semanal');
            $monthlyPart = $this->part('CONSUMO-MES', 'Material mensual');
            $otherCenterPart = $this->part('CONSUMO-OTRO', 'Material de otro centro');
            $this->exit($charcas, $weeklyPart, '2026-09-08 10:00:00', 3);
            $this->exit($charcas, $weeklyPart, '2026-09-09 10:00:00', 4);
            $this->exit($charcas, $monthlyPart, '2026-09-02 10:00:00', 5);
            $this->exit($matriz, $otherCenterPart, '2026-09-09 10:00:00', 100);

            $response = $this->actingAs($user)->get(route('warehouse.reports.index', [
                'cost_center_id' => $charcas->id,
            ]));

            $response->assertOk()
                ->assertSee('Materiales más consumidos esta semana')
                ->assertSee('Materiales más consumidos este mes');

            $weekly = collect($response->viewData('weeklyConsumption'))->keyBy('key');
            $monthly = collect($response->viewData('monthlyConsumption'))->keyBy('key');

            $this->assertSame(7.0, $weekly['CONSUMO-SEMANA']['quantity']);
            $this->assertSame(2, $weekly['CONSUMO-SEMANA']['requests']);
            $this->assertFalse($weekly->has('CONSUMO-MES'));
            $this->assertFalse($monthly->has('CONSUMO-OTRO'));
            $this->assertSame(5.0, $monthly['CONSUMO-MES']['quantity']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_report_rejects_invalid_dates_and_unassigned_cost_centers(): void
    {
        [$user, $charcas, $matriz] = $this->context();
        $user->costCenters()->sync([$charcas->id]);

        $this->actingAs($user)->get(route('warehouse.reports.index', [
            'cost_center_id' => $charcas->id,
            'date_from' => '2026-09-20',
            'date_to' => '2026-09-01',
            'movement_type' => 'all',
        ]))->assertSessionHasErrors('date_to');

        $this->actingAs($user)->get(route('warehouse.reports.index', [
            'cost_center_id' => $matriz->id,
        ]))->assertForbidden();
    }

    /**
     * @return array{User, CostCenter, CostCenter}
     */
    private function context(): array
    {
        $user = User::create([
            'name' => 'Usuario reportes',
            'username' => 'warehouse-reports',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$charcas->id, $matriz->id]);

        return [$user, $charcas, $matriz];
    }

    private function part(string $clave, string $name): Part
    {
        return Part::create([
            'clave' => $clave,
            'name' => $name,
            'characteristics' => ['estándar'],
            'unit_cost' => 0,
            'active' => true,
        ]);
    }

    private function entry(CostCenter $costCenter, Part $part, string $date, float $quantity): void
    {
        /** @var WarehouseLocation $location */
        $location = $costCenter->warehouseLocations()->where('normalized_name', 'general')->firstOrFail();
        $entry = WarehouseEntry::create([
            'cost_center_id' => $costCenter->id,
            'entry_key' => 'ENT-'.$part->id.'-'.str_replace(['-', ' ', ':'], '', $date),
            'entry_type' => 'Compra',
            'entry_date' => $date,
        ]);
        $entry->materials()->create([
            'part_id' => $part->id,
            'warehouse_location_id' => $location->id,
            'quantity' => $quantity,
        ]);
    }

    private function exit(CostCenter $costCenter, Part $part, string $date, float $quantity): void
    {
        WarehouseMaterialExit::create([
            'cost_center_id' => $costCenter->id,
            'part_id' => $part->id,
            'quantity' => $quantity,
            'exit_date' => $date,
            'source' => 'Almacén',
            'destination' => 'Obra',
            'responsible' => 'Responsable',
            'voucher_number' => 'VALE-'.$part->id.'-'.str_replace(['-', ' ', ':'], '', $date),
            'status' => 'entregado',
        ]);
    }
}
