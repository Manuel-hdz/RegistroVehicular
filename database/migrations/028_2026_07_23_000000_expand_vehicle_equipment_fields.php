<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('plate', 191)->nullable()->change();

            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'unit_name')) {
                $table->string('unit_name', 150)->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'registration_date')) {
                $table->date('registration_date')->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'make_model')) {
                $table->string('make_model', 150)->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'tenure_path')) {
                $table->string('tenure_path')->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'engine_type')) {
                $table->string('engine_type', 120)->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'engine_filters')) {
                $table->text('engine_filters')->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'area')) {
                $table->string('area', 120)->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'family')) {
                $table->string('family', 120)->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'manufacture_date')) {
                $table->date('manufacture_date')->nullable();
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'equipment_status')) {
                $table->string('equipment_status', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'unit_name',
                'registration_date',
                'make_model',
                'tenure_path',
                'engine_type',
                'engine_filters',
                'area',
                'family',
                'manufacture_date',
                'equipment_status',
            ]);
        });
    }
};
