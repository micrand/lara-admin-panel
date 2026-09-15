<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisioning_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('client_id')->nullable();
            $table->uuid('server_id')->nullable();
            $table->uuid('service_id')->nullable();

            $table->string('operation', 30);
            $table->string('resource_type', 100);
            $table->uuid('resource_id')->nullable();

            $table->string('status', 30)
                ->default('pending');

            $table->unsignedInteger('attempts')
                ->default(0);

            $table->string('idempotency_key', 255)->unique();

            $table->jsonb('request')->nullable();
            $table->jsonb('response')->nullable();

            $table->text('errors')->nullable();

            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->timestampsTz();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();

            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->nullOnDelete();

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->nullOnDelete();

            $table->index('client_id');
            $table->index('server_id');
            $table->index('service_id');

            $table->index([
                'resource_type',
                'resource_id',
            ]);

            $table->index('status');
        });

        DB::statement("
            ALTER TABLE provisioning_operations
            ADD CONSTRAINT provisioning_operations_operation_check
            CHECK (
                operation IN (
                    'create',
                    'update',
                    'delete',
                    'sync'
                )
            )
        ");

        DB::statement("
            ALTER TABLE provisioning_operations
            ADD CONSTRAINT provisioning_operations_status_check
            CHECK (
                status IN (
                    'pending',
                    'processing',
                    'completed',
                    'failed',
                    'cancelled'
                )
            )
        ");

        DB::statement("
            ALTER TABLE provisioning_operations
            ADD CONSTRAINT provisioning_operations_attempts_check
            CHECK (attempts >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_operations');
    }
};