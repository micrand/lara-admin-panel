<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('client_id');
            $table->uuid('service_id');

            $table->uuid('hosting_account_id')->nullable();
            $table->uuid('parent_domain_id')->nullable();

            $table->string('name', 255);
            $table->string('fqdn', 255);

            $table->string('type', 30);

            $table->string('status', 30)
                ->default('pending');

            $table->timestampTz('expires_at')->nullable();

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

            $table->foreign('hosting_account_id')
                ->references('id')
                ->on('hosting_accounts')
                ->nullOnDelete();

            $table->index('client_id');
            $table->index('service_id');
            $table->index('hosting_account_id');
            $table->index('parent_domain_id');
            $table->index('status');
            $table->index('external_id');

            $table->unique(['name', 'type']);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->foreign('parent_domain_id')
                ->references('id')
                ->on('domains')
                ->nullOnDelete();
        });

        DB::statement("
            ALTER TABLE domains
            ADD CONSTRAINT domains_type_check
            CHECK (
                type IN (
                    'primary',
                    'subdomain',
                    'alias'
                )
            )
        ");

        DB::statement("
            ALTER TABLE domains
            ADD CONSTRAINT domains_status_check
            CHECK (
                status IN (
                    'pending',
                    'provisioning',
                    'active',
                    'suspended',
                    'expired',
                    'deleting',
                    'deleted',
                    'failed'
                )
            )
        ");

        DB::statement("
            ALTER TABLE domains
            ADD CONSTRAINT domains_parent_check
            CHECK (
                parent_domain_id IS NULL
                OR parent_domain_id <> id
            )
        ");

        DB::statement("
            CREATE UNIQUE INDEX domains_fqdn_unique_active
            ON domains (LOWER(fqdn))
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};