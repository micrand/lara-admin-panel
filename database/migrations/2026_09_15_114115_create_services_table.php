<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('client_id');
            $table->uuid('service_type_id');

            $table->string('name', 150);
            $table->string('reference', 100)->unique();

            $table->string('status', 30)
                ->default('pending');

            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('expires_at')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->restrictOnDelete();

            $table->foreign('service_type_id')
                ->references('id')
                ->on('service_types')
                ->restrictOnDelete();

            $table->index('client_id');
            $table->index('service_type_id');
            $table->index('status');
        });

        DB::statement("
            ALTER TABLE services
            ADD CONSTRAINT services_status_check
            CHECK (
                status IN (
                    'pending',
                    'provisioning',
                    'active',
                    'suspended',
                    'cancelling',
                    'cancelled',
                    'failed'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};