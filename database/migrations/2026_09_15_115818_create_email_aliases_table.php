<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('email_account_id');

            $table->string('alias', 255);

            $table->string('status', 30)
                ->default('active');

            $table->timestampsTz();

            $table->foreign('email_account_id')
                ->references('id')
                ->on('email_accounts')
                ->restrictOnDelete();

            $table->index('email_account_id');
        });

        DB::statement("
            ALTER TABLE email_aliases
            ADD CONSTRAINT email_aliases_status_check
            CHECK (
                status IN (
                    'active',
                    'inactive',
                    'deleted'
                )
            )
        ");

        DB::statement("
            CREATE UNIQUE INDEX email_aliases_alias_unique
            ON email_aliases (LOWER(alias))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('email_aliases');
    }
};