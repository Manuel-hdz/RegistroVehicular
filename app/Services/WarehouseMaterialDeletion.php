<?php

namespace App\Services;

use App\Models\Part;
use App\Models\WarehouseEntry;
use Illuminate\Support\Facades\DB;

class WarehouseMaterialDeletion
{
    public function delete(Part $part): void
    {
        DB::transaction(function () use ($part): void {
            $part = Part::whereKey($part->id)->lockForUpdate()->firstOrFail();
            $entryIds = $part->entryMaterials()->pluck('warehouse_entry_id')->unique()->sort();
            $entries = WarehouseEntry::whereIn('id', $entryIds)->orderBy('id')->lockForUpdate()->get();
            $counts = [
                'entries' => $part->entryMaterials()->count(),
                'exits' => $part->materialExits()->count(),
                'repair_links' => $part->repairs()->count(),
            ];
            $part->materialExits()->delete();
            $part->entryMaterials()->delete();
            $part->repairs()->detach();
            foreach ($entries as $entry) {
                // Preserve entries that still contain other materials.
                if (! $entry->materials()->exists()) {
                    $entry->delete();
                }
            }
            $part->delete();
            app(SystemAudit::class)->record('deleted', 'Material y registros relacionados eliminados de todos los almacenes.', [
                'subject_type' => 'parts', 'subject_id' => (string) $part->id,
                'metadata' => ['related_records' => $counts, 'scope' => 'all_cost_centers'],
            ]);
        });
    }
}
