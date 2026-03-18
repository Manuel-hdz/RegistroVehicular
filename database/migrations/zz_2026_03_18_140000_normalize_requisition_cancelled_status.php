<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('requisitions')) {
            DB::table('requisitions')
                ->where('status', 'rejected')
                ->update(['status' => 'cancelled']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('requisitions')) {
            DB::table('requisitions')
                ->where('status', 'cancelled')
                ->update(['status' => 'rejected']);
        }
    }
};
