<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->boolean('dry_run')->default(true);
            $table->string('status')->default('pending');
            $table->unsignedInteger('tables_mapped')->default(0);
            $table->unsignedInteger('tables_unmapped')->default(0);
            $table->unsignedInteger('rows_read')->default(0);
            $table->unsignedInteger('rows_inserted')->default(0);
            $table->unsignedInteger('rows_updated')->default(0);
            $table->unsignedInteger('rows_skipped')->default(0);
            $table->unsignedInteger('errors_count')->default(0);
            $table->json('report')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legacy_import_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50);
            $table->string('entity', 100);
            $table->unsignedBigInteger('old_id');
            $table->unsignedBigInteger('new_id');
            $table->string('dedupe_key')->nullable();
            $table->timestamps();

            $table->unique(['source', 'entity', 'old_id']);
            $table->index(['source', 'entity', 'new_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_import_id_mappings');
        Schema::dropIfExists('legacy_import_runs');
    }
};
