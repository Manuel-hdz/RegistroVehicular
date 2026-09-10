<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'serial_number')) {
                $table->string('serial_number', 120)->nullable()->after('identifier');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'additional_serial_number')) {
                $table->string('additional_serial_number', 120)->nullable()->after('serial_number');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'engine_number')) {
                $table->string('engine_number', 120)->nullable()->after('additional_serial_number');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'supplier')) {
                $table->string('supplier', 150)->nullable()->after('engine_number');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'assigned_personnel')) {
                $table->string('assigned_personnel', 150)->nullable()->after('supplier');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'description')) {
                $table->text('description')->nullable()->after('model');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'circulation_card_path')) {
                $table->string('circulation_card_path')->nullable()->after('description');
            }
            if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'insurance_policy_path')) {
                $table->string('insurance_policy_path')->nullable()->after('circulation_card_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'serial_number',
                'additional_serial_number',
                'engine_number',
                'supplier',
                'assigned_personnel',
                'description',
                'circulation_card_path',
                'insurance_policy_path',
            ]);
        });
    }
};
