<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->string('photo_path', 255)->nullable()->after('emergency_contact_phone');
            $table->date('terminated_at')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'terminated_at']);
        });
    }
};
