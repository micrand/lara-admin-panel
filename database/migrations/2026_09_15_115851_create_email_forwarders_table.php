<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_forwarders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('domain_id');

            $table->string('source', 255);
            $table->string('destination', 255);

            $table->string('status', 30)
                ->default('active');

            $table->string('external_id', 150)->nullable();

            $table->timestampsTz();

            $table->foreign('domain_id')
                ->references('id')
                ->on('domains')
                ->restrictOnDelete();

            $table->index('domain_id');
            $table->index('external_id');
            $table->index('status');
        });

        DB::statement("
            ALTER TABLE email_forwarders
            ADD CONSTRAINT email_forwarders_status_check
            CHECK (
                status IN (
                    'active',
                    'inactive',
                    'deleted'
                )
            )
        ");

        DB::statement("
            CREATE UNIQUE INDEX email_forwarders_unique
            ON email_forwarders (
                domain_id,
                LOWER(source),
                LOWER(destination)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('email_forwarders');
    }
};