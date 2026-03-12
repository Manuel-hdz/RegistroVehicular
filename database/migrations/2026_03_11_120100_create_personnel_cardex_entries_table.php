<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_cardex_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $table->date('entry_date');
            $table->string('code', 3);
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['personnel_id', 'entry_date']);
            $table->index('entry_date');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_cardex_entries');
    }
};
