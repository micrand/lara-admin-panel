<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('domain_id');
            $table->uuid('alias_domain_id');

            $table->string('status', 30)
                ->default('pending');

            $table->timestampsTz();

            $table->foreign('domain_id')
                ->references('id')
                ->on('domains')
                ->restrictOnDelete();

            $table->foreign('alias_domain_id')
                ->references('id')
                ->on('domains')
                ->restrictOnDelete();

            $table->unique([
                'domain_id',
                'alias_domain_id',
            ]);

            $table->index('domain_id');
            $table->index('alias_domain_id');
        });

        DB::statement("
            ALTER TABLE domain_aliases
            ADD CONSTRAINT domain_aliases_status_check
            CHECK (
                status IN (
                    'pending',
                    'active',
                    'suspended',
                    'deleted'
                )
            )
        ");

        DB::statement("
            ALTER TABLE domain_aliases
            ADD CONSTRAINT domain_aliases_not_self_check
            CHECK (
                domain_id <> alias_domain_id
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_aliases');
    }
};