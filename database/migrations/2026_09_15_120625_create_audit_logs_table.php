<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->nullable();

            $table->string('action', 100);

            $table->string('entity_type', 100);
            $table->uuid('entity_id')->nullable();

            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();

            $table->ipAddress('ip')->nullable();

            $table->text('user_agent')->nullable();

            $table->uuid('request_id')->nullable();

            $table->timestampTz('created_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('user_id');

            $table->index([
                'entity_type',
                'entity_id',
            ]);

            $table->index('action');
            $table->index('request_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};