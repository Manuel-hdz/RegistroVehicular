<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username', 191)->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('plate', 191)->nullable()->unique();
                $table->string('identifier')->nullable();
                $table->string('model')->nullable();
                $table->smallInteger('year')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parts')) {
            Schema::create('parts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('unit_cost', 12, 2)->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Estas tablas también pertenecen a migraciones históricas.
    }
};
