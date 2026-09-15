<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('service_id');
            $table->uuid('server_id');

            $table->string('provider_account_id', 150)->nullable();

            $table->string('username', 100);
            $table->string('primary_domain', 255);

            $table->string('external_id', 150)->nullable();

            $table->string('status', 30)
                ->default('pending');

            $table->timestampTz('last_synced_at')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->restrictOnDelete();

            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->restrictOnDelete();

            $table->index('service_id');
            $table->index('server_id');
            $table->index('external_id');
            $table->index('status');

            $table->unique(['server_id', 'username']);
        });

        DB::statement("
            ALTER TABLE hosting_accounts
            ADD CONSTRAINT hosting_accounts_status_check
            CHECK (
                status IN (
                    'pending',
                    'provisioning',
                    'active',
                    'suspended',
                    'deleting',
                    'deleted',
                    'failed'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_accounts');
    }
};