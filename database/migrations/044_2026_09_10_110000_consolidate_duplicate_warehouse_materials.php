<?php

use App\Support\MigrationSchemaInspector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! MigrationSchemaInspector::hasColumn('parts', 'identity_key')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->string('identity_key', 64)->nullable()->after('characteristics');
            });
        }

        $keepers = [];

        foreach (DB::table('parts')->orderBy('id')->get(['id', 'name', 'characteristics']) as $part) {
            $identityKey = $this->identityKey((string) $part->name, $part->characteristics);

            if (! isset($keepers[$identityKey])) {
                $keepers[$identityKey] = (int) $part->id;
                DB::table('parts')->where('id', $part->id)->update(['identity_key' => $identityKey]);

                continue;
            }

            $this->mergePart((int) $part->id, $keepers[$identityKey]);
        }

        $this->consolidateRepeatedRows('warehouse_entry_materials', 'warehouse_entry_id');
        $this->consolidateRepeatedRows('repair_part', 'repair_id');

        if (! MigrationSchemaInspector::hasIndex('parts', 'parts_identity_key_unique')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->unique('identity_key');
            });
        }
    }

    public function down(): void
    {
        if (MigrationSchemaInspector::hasIndex('parts', 'parts_identity_key_unique')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dropUnique('parts_identity_key_unique');
            });
        }

        if (MigrationSchemaInspector::hasColumn('parts', 'identity_key')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dropColumn('identity_key');
            });
        }
    }

    private function mergePart(int $duplicateId, int $keeperId): void
    {
        if (Schema::hasTable('warehouse_entry_materials')) {
            DB::table('warehouse_entry_materials')
                ->where('part_id', $duplicateId)
                ->update(['part_id' => $keeperId]);
        }

        if (Schema::hasTable('warehouse_material_exits')) {
            DB::table('warehouse_material_exits')
                ->where('part_id', $duplicateId)
                ->update(['part_id' => $keeperId]);
        }

        if (Schema::hasTable('repair_part')) {
            DB::table('repair_part')
                ->where('part_id', $duplicateId)
                ->update(['part_id' => $keeperId]);
        }

        DB::table('parts')->where('id', $duplicateId)->delete();
    }

    private function consolidateRepeatedRows(string $table, string $parentColumn): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $duplicates = DB::table($table)
            ->select([
                $parentColumn,
                'part_id',
                DB::raw('MIN(id) as keeper_id'),
                DB::raw('SUM(quantity) as total_quantity'),
            ])
            ->groupBy($parentColumn, 'part_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table($table)
                ->where('id', $duplicate->keeper_id)
                ->update(['quantity' => $duplicate->total_quantity]);

            DB::table($table)
                ->where($parentColumn, $duplicate->{$parentColumn})
                ->where('part_id', $duplicate->part_id)
                ->where('id', '<>', $duplicate->keeper_id)
                ->delete();
        }
    }

    private function identityKey(string $name, mixed $characteristics): string
    {
        $normalizedName = (string) Str::of($name)
            ->ascii()
            ->lower()
            ->squish();

        return hash('sha256', $normalizedName.'|'.implode('|', $this->normalizeCharacteristics($characteristics)));
    }

    /**
     * @return array<int, string>
     */
    private function normalizeCharacteristics(mixed $characteristics): array
    {
        $decoded = is_string($characteristics) ? json_decode($characteristics, true) : null;
        $values = is_array($characteristics)
            ? $characteristics
            : (is_array($decoded) ? $decoded : preg_split('/[\r\n,]+/', (string) $characteristics));

        return collect($values ?: [])
            ->map(fn ($value) => (string) Str::of((string) $value)->ascii()->lower()->squish())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
};
