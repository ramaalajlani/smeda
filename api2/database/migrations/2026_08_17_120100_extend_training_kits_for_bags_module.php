<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_kits', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->text('short_description')->nullable()->after('description');
            $table->foreignId('category_id')->nullable()->after('category')->constrained('training_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('training_categories')->nullOnDelete();
            $table->unsignedSmallInteger('suggested_days')->nullable()->after('hours');
            $table->text('prerequisites')->nullable()->after('objective');
            $table->text('target_audience')->nullable()->after('prerequisites');
            $table->text('expected_outcomes')->nullable()->after('target_audience');

            $table->string('promotional_file_path')->nullable();
            $table->string('promotional_file_original_name')->nullable();
            $table->string('promotional_file_mime', 120)->nullable();
            $table->unsignedBigInteger('promotional_file_size')->nullable();

            $table->string('training_bag_file_path')->nullable();
            $table->string('training_bag_file_original_name')->nullable();
            $table->string('training_bag_file_mime', 120)->nullable();
            $table->unsignedBigInteger('training_bag_file_size')->nullable();

            $table->string('workflow_status', 30)->default('published')->after('status');
            $table->timestamp('published_at')->nullable()->after('workflow_status');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['workflow_status', 'id']);
            $table->index(['category_id', 'subcategory_id']);
        });

        // Map existing kits to published workflow (backward compatible).
        DB::table('training_kits')->where('status', 'active')->update([
            'workflow_status' => 'published',
            'published_at' => DB::raw('COALESCE(updated_at, created_at, NOW())'),
        ]);
        DB::table('training_kits')->where('status', 'inactive')->update(['workflow_status' => 'inactive']);
        DB::table('training_kits')->where('status', 'archived')->update(['workflow_status' => 'archived']);
    }

    public function down(): void
    {
        Schema::table('training_kits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');

            $table->dropColumn([
                'name_en',
                'short_description',
                'suggested_days',
                'prerequisites',
                'target_audience',
                'expected_outcomes',
                'promotional_file_path',
                'promotional_file_original_name',
                'promotional_file_mime',
                'promotional_file_size',
                'training_bag_file_path',
                'training_bag_file_original_name',
                'training_bag_file_mime',
                'training_bag_file_size',
                'workflow_status',
                'published_at',
            ]);
        });
    }
};
