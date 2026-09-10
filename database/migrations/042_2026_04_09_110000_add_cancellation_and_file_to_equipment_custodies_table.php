<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('equipment_custodies', 'physical_record_path')) {
            Schema::table('equipment_custodies', function (Blueprint $table) {
                $table->string('physical_record_path')->nullable()->after('notification_email');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('equipment_custodies', 'cancelled_at')) {
            Schema::table('equipment_custodies', function (Blueprint $table) {
                $table->timestamp('cancelled_at')->nullable()->after('physical_record_path');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('equipment_custodies', 'cancelled_by_user_id')) {
            Schema::table('equipment_custodies', function (Blueprint $table) {
                $table->foreignId('cancelled_by_user_id')
                    ->nullable()
                    ->after('cancelled_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('equipment_custodies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by_user_id');
            $table->dropColumn('cancelled_at');
            $table->dropColumn('physical_record_path');
        });
    }
};
