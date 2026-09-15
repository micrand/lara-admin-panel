<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('client_id');

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('position', 100)->nullable();

            $table->boolean('is_primary')
                ->default(false);

            $table->timestampsTz();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->restrictOnDelete();

            $table->index(['client_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};