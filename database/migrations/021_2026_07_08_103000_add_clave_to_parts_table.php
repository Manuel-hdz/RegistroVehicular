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
        if (\App\Support\MigrationSchemaInspector::hasColumn('parts', 'clave')) {
            return;
        }

        Schema::table('parts', function (Blueprint $table) {
            $table->string('clave', 80)->nullable()->unique()->after('id');
        });

        DB::table('parts')
            ->orderBy('id')
            ->select(['id', 'name'])
            ->chunkById(100, function ($parts): void {
                foreach ($parts as $part) {
                    $prefix = Str::of((string) $part->name)
                        ->ascii()
                        ->upper()
                        ->replaceMatches('/[^A-Z0-9\s]/', '')
                        ->trim()
                        ->replaceMatches('/\s+/', '')
                        ->substr(0, 6)
                        ->padRight(6, 'X');

                    DB::table('parts')
                        ->where('id', $part->id)
                        ->update(['clave' => 'MAT-'.$prefix.'-'.str_pad((string) $part->id, 6, '0', STR_PAD_LEFT)]);
                }
            });
    }

    public function down(): void
    {
        if (\App\Support\MigrationSchemaInspector::hasIndex('parts', 'parts_clave_unique')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dropUnique('parts_clave_unique');
            });
        }

        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('clave');
        });
    }
};
