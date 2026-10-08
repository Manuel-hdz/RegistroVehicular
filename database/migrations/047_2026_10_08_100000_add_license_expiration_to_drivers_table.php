<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers') && ! \App\Support\MigrationSchemaInspector::hasColumn('drivers', 'license_expires_at')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->date('license_expires_at')->nullable()->after('license');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('drivers') && \App\Support\MigrationSchemaInspector::hasColumn('drivers', 'license_expires_at')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('license_expires_at');
            });
        }
    }
};
