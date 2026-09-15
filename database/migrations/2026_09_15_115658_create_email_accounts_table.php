<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('client_id');
            $table->uuid('service_id');
            $table->uuid('domain_id');
            $table->uuid('hosting_account_id');

            $table->string('local_part', 100);
            $table->string('email', 255);

            $table->unsignedBigInteger('quota')
                ->default(0);

            $table->string('status', 30)
                ->default('pending');

            $table->string('external_id', 150)->nullable();

            $table->timestampTz('last_synced_at')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->restrictOnDelete();

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->restrictOnDelete();

            $table->foreign('domain_id')
                ->references('id')
                ->on('domains')
                ->restrictOnDelete();

            $table->foreign('hosting_account_id')
                ->references('id')
                ->on('hosting_accounts')
                ->restrictOnDelete();

            $table->index('client_id');
            $table->index('service_id');
            $table->index('domain_id');
            $table->index('hosting_account_id');
            $table->index('external_id');
            $table->index('status');
        });

        DB::statement("
            ALTER TABLE email_accounts
            ADD CONSTRAINT email_accounts_status_check
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

        DB::statement("
            ALTER TABLE email_accounts
            ADD CONSTRAINT email_accounts_quota_check
            CHECK (quota >= 0)
        ");

        DB::statement("
            CREATE UNIQUE INDEX email_accounts_domain_local_active_unique
            ON email_accounts (
                domain_id,
                LOWER(local_part)
            )
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};