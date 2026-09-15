<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->nullable();
            $table->uuid('client_id')->nullable();

            $table->string('type', 100);
            $table->string('title', 255);
            $table->text('message');

            $table->string('channel', 30)
                ->default('in_app');

            $table->string('status', 30)
                ->default('unread');

            $table->timestampTz('read_at')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();

            $table->index('user_id');
            $table->index('client_id');
            $table->index(['status', 'channel']);
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};