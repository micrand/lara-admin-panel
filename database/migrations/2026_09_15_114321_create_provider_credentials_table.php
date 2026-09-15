<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('provider_id');
            $table->uuid('server_id')->nullable();

            $table->string('name', 100);
            $table->string('type', 50);

            $table->text('encrypted_data');

            $table->string('status', 30)
                ->default('active');

            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('last_used_at')->nullable();

            $table->timestampsTz();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->restrictOnDelete();

            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->nullOnDelete();

            $table->index('provider_id');
            $table->index('server_id');
            $table->index('status');
        });

        DB::statement("
            ALTER TABLE provider_credentials
            ADD CONSTRAINT provider_credentials_status_check
            CHECK (
                status IN (
                    'active',
                    'inactive',
                    'expired',
                    'revoked'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_credentials');
    }
};