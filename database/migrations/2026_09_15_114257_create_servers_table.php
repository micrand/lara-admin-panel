<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('provider_id');

            $table->string('name', 100);
            $table->string('hostname', 255);
            $table->ipAddress('ip')->nullable();

            $table->unsignedSmallInteger('port')
                ->default(2087);

            $table->string('status', 30)
                ->default('active');

            $table->string('environment', 30)
                ->default('production');

            $table->jsonb('api_configuration')->nullable();

            $table->timestampTz('last_checked_at')->nullable();

            $table->timestampsTz();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->restrictOnDelete();

            $table->unique(['hostname', 'port']);

            $table->index('provider_id');
            $table->index(['status', 'environment']);
        });

        DB::statement("
            ALTER TABLE servers
            ADD CONSTRAINT servers_port_check
            CHECK (port BETWEEN 1 AND 65535)
        ");

        DB::statement("
            ALTER TABLE servers
            ADD CONSTRAINT servers_status_check
            CHECK (
                status IN (
                    'active',
                    'inactive',
                    'maintenance',
                    'unreachable',
                    'error'
                )
            )
        ");

        DB::statement("
            ALTER TABLE servers
            ADD CONSTRAINT servers_environment_check
            CHECK (
                environment IN (
                    'production',
                    'staging',
                    'testing',
                    'development'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};