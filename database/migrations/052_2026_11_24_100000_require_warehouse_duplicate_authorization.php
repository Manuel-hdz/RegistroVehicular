<?php

use App\Models\Part;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->string('base_name', 150)->nullable()->index();
        });

        DB::table('parts')->orderBy('id')->chunkById(500, function ($parts) {
            foreach ($parts as $part) {
                DB::table('parts')->where('id', $part->id)->update([
                    'base_name' => Part::normalizeBaseName($part->name),
                ]);
            }
        });

        Schema::table('warehouse_entry_materials', function (Blueprint $table) {
            $table->string('duplicate_authorized_by', 180)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_entry_materials', function (Blueprint $table) {
            $table->dropColumn('duplicate_authorized_by');
        });
        Schema::table('parts', function (Blueprint $table) {
            $table->dropIndex(['base_name']);
            $table->dropColumn('base_name');
        });
    }
};
