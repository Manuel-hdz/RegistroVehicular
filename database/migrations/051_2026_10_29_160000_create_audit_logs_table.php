<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Las identidades se conservan aunque el usuario o registro sea eliminado.
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_username')->nullable();
            $table->string('action', 40)->index();
            $table->string('subject_type', 100)->nullable()->index();
            $table->string('subject_id', 100)->nullable();
            $table->string('description', 500);
            $table->longText('before_values')->nullable();
            $table->longText('after_values')->nullable();
            $table->longText('metadata')->nullable();
            $table->uuid('request_id')->nullable()->index();
            $table->string('route')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('path', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
