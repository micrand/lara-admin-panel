<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dns_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('dns_zone_id');

            $table->string('type', 10);
            $table->string('name', 255);
            $table->text('value');

            $table->unsignedInteger('ttl')
                ->default(3600);

            $table->unsignedSmallInteger('priority')->nullable();
            $table->unsignedSmallInteger('weight')->nullable();
            $table->unsignedSmallInteger('port')->nullable();

            $table->string('status', 30)
                ->default('active');

            $table->string('external_id', 150)->nullable();

            $table->timestampsTz();

            $table->foreign('dns_zone_id')
                ->references('id')
                ->on('dns_zones')
                ->cascadeOnDelete();

            $table->index('dns_zone_id');
            $table->index(['dns_zone_id', 'type']);
            $table->index('external_id');
            $table->index('status');
        });

        DB::statement("
            ALTER TABLE dns_records
            ADD CONSTRAINT dns_records_type_check
            CHECK (
                type IN (
                    'A',
                    'AAAA',
                    'CNAME',
                    'MX',
                    'TXT',
                    'NS',
                    'SRV',
                    'CAA'
                )
            )
        ");

        DB::statement("
            ALTER TABLE dns_records
            ADD CONSTRAINT dns_records_status_check
            CHECK (
                status IN (
                    'active',
                    'inactive',
                    'pending',
                    'deleted'
                )
            )
        ");

        DB::statement("
            ALTER TABLE dns_records
            ADD CONSTRAINT dns_records_ttl_check
            CHECK (ttl > 0)
        ");

        DB::statement("
            ALTER TABLE dns_records
            ADD CONSTRAINT dns_records_port_check
            CHECK (
                port IS NULL
                OR port BETWEEN 1 AND 65535
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('dns_records');
    }
};