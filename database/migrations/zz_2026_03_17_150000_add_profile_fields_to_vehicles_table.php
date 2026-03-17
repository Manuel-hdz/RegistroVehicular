<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('serial_number', 120)->nullable()->after('identifier');
            $table->string('additional_serial_number', 120)->nullable()->after('serial_number');
            $table->string('engine_number', 120)->nullable()->after('additional_serial_number');
            $table->string('supplier', 150)->nullable()->after('engine_number');
            $table->string('assigned_personnel', 150)->nullable()->after('supplier');
            $table->text('description')->nullable()->after('model');
            $table->string('circulation_card_path')->nullable()->after('description');
            $table->string('insurance_policy_path')->nullable()->after('circulation_card_path');
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
