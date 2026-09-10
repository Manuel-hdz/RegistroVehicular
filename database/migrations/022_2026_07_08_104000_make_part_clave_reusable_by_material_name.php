<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (\App\Support\MigrationSchemaInspector::hasIndex('parts', 'parts_clave_unique')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dropUnique('parts_clave_unique');
            });
        }

        DB::table('parts')
            ->orderBy('id')
            ->select(['id', 'name'])
            ->chunkById(100, function ($parts): void {
                foreach ($parts as $part) {
                    DB::table('parts')
                        ->where('id', $part->id)
                        ->update(['clave' => $this->materialClave((string) $part->name)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->unique('clave');
        });
    }

    private function materialClave(string $name): string
    {
        $cleanName = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9\s]/', '')
            ->trim();

        $prefix = $cleanName
            ->replaceMatches('/\s+/', '')
            ->substr(0, 6)
            ->padRight(6, 'X');

        $hash = 0;
        $normalizedName = (string) $cleanName;
        for ($index = 0; $index < strlen($normalizedName); $index++) {
            $hash = (($hash << 5) - $hash + ord($normalizedName[$index])) & 0xFFFFFFFF;
        }
        $suffix = strtoupper(substr(str_pad(dechex($hash), 6, '0', STR_PAD_LEFT), 0, 6));

        return 'MAT-'.$prefix.'-'.$suffix;
    }
};
