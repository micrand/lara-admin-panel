<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('server_id');

            $table->string('type', 30);
            $table->string('status', 30)
                ->default('pending');

            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->unsignedInteger('items_processed')
                ->default(0);

            $table->unsignedInteger('items_created')
                ->default(0);

            $table->unsignedInteger('items_updated')
                ->default(0);

            $table->unsignedInteger('items_deleted')
                ->default(0);

            $table->unsignedInteger('items_failed')
                ->default(0);

            $table->text('error')->nullable();

            $table->timestampsTz();

            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->restrictOnDelete();

            $table->index('server_id');
            $table->index(['type', 'status']);
            $table->index('started_at');
        });

        DB::statement("
            ALTER TABLE sync_runs
            ADD CONSTRAINT sync_runs_type_check
            CHECK (
                type IN (
                    'full',
                    'domains',
                    'emails',
                    'dns',
                    'hosting'
                )
            )
        ");

        DB::statement("
            ALTER TABLE sync_runs
            ADD CONSTRAINT sync_runs_status_check
            CHECK (
                status IN (
                    'pending',
                    'running',
                    'completed',
                    'failed',
                    'partial'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};