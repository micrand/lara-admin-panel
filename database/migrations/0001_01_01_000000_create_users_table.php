<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 150);
            $table->string('email', 255)->unique();
            $table->string('password', 255);

            $table->string('status', 20)
                ->default('active');

            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();

            $table->rememberToken();

            $table->timestampsTz();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('users');
    }
};
