<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'status')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->string('status', 30)->nullable()->after('invoice_path');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'delivered_at')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->dateTime('delivered_at')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('warehouse_material_exits', function (Blueprint $table) {
            $table->dropColumn(['status', 'delivered_at']);
        });
    }
};
