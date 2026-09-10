<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'photo_path')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
