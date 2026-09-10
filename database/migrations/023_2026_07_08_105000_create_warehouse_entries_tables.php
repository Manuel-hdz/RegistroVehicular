<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('parts', 'characteristics')) {
            Schema::table('parts', function (Blueprint $table) {
                // MySQL 5.6 no cuenta con el tipo JSON nativo. Laravel puede
                // serializar este campo normalmente cuando se almacena como TEXT.
                $table->text('characteristics')->nullable()->after('name');
            });
        }

        if (! Schema::hasTable('warehouse_entries')) {
            Schema::create('warehouse_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_key', 80)->nullable()->unique();
                $table->string('entry_type', 80)->nullable();
                $table->dateTime('entry_date');
                $table->string('invoice_path')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('warehouse_entry_materials')) {
            Schema::create('warehouse_entry_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_entry_id')->constrained('warehouse_entries')->onDelete('cascade');
                $table->foreignId('part_id')->constrained('parts');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_entry_materials');
        Schema::dropIfExists('warehouse_entries');

        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('characteristics');
        });
    }
};
