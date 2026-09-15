<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 100);
            $table->string('slug', 100)->unique();

            $table->string('type', 50);

            $table->string('status', 30)
                ->default('active');

            $table->jsonb('capabilities')->nullable();
            $table->jsonb('configuration')->nullable();

            $table->timestampsTz();

            $table->index(['type', 'status']);
        });

        DB::statement("
            ALTER TABLE providers
            ADD CONSTRAINT providers_status_check
            CHECK (
                status IN (
                    'active',
                    'inactive',
                    'maintenance',
                    'error'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};