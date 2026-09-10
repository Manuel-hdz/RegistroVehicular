<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empleados') && ! Schema::hasTable('unidades')) {
            Schema::rename('empleados', 'unidades');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('unidades') && ! Schema::hasTable('empleados')) {
            Schema::rename('unidades', 'empleados');
        }
    }
};
