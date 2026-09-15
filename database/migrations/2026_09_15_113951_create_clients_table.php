<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('type', 30);
            $table->string('name', 150);
            $table->string('legal_name', 200)->nullable();

            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();

            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country_code', 2)->nullable();

            $table->string('status', 20)
                ->default('active');

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('type');
            $table->index('status');
            $table->index('email');
        });

        DB::statement("
            ALTER TABLE clients
            ADD CONSTRAINT clients_type_check
            CHECK (type IN ('individual', 'company', 'organization'))
        ");

        DB::statement("
            ALTER TABLE clients
            ADD CONSTRAINT clients_status_check
            CHECK (status IN ('active', 'inactive', 'suspended', 'archived'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};