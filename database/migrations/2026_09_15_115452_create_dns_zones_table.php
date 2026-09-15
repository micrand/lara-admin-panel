<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dns_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('domain_id');

            $table->string('name', 255);

            $table->string('status', 30)
                ->default('pending');

            $table->unsignedBigInteger('serial')->nullable();

            $table->string('provider_zone_id', 150)->nullable();

            $table->timestampTz('last_synced_at')->nullable();

            $table->timestampsTz();

            $table->foreign('domain_id')
                ->references('id')
                ->on('domains')
                ->restrictOnDelete();

            $table->unique('domain_id');

            $table->index('name');
            $table->index('status');
            $table->index('provider_zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dns_zones');
    }
};