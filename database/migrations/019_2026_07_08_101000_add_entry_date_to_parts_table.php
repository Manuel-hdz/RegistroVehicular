<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('parts', 'entry_date')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dateTime('entry_date')->nullable()->after('entry_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('entry_date');
        });
    }
};
