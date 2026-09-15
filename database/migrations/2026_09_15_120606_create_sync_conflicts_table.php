<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('sync_run_id');
            $table->uuid('server_id');

            $table->string('resource_type', 100);
            $table->uuid('resource_id')->nullable();

            $table->jsonb('local_state')->nullable();
            $table->jsonb('remote_state')->nullable();

            $table->string('resolution', 30)
                ->default('pending');

            $table->uuid('resolved_by')->nullable();
            $table->timestampTz('resolved_at')->nullable();

            $table->timestampsTz();

            $table->foreign('sync_run_id')
                ->references('id')
                ->on('sync_runs')
                ->restrictOnDelete();

            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->restrictOnDelete();

            $table->foreign('resolved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('sync_run_id');
            $table->index('server_id');

            $table->index([
                'resource_type',
                'resource_id',
            ]);

            $table->index('resolution');
        });

        DB::statement("
            ALTER TABLE sync_conflicts
            ADD CONSTRAINT sync_conflicts_resolution_check
            CHECK (
                resolution IN (
                    'pending',
                    'local_wins',
                    'remote_wins',
                    'manual',
                    'ignored'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_conflicts');
    }
};