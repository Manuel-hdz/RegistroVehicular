<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('parts', 'entry_type')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->string('entry_type')->nullable()->after('active');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('parts', 'invoice_path')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->string('invoice_path')->nullable()->after('entry_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn(['entry_type', 'invoice_path']);
        });
    }
};
