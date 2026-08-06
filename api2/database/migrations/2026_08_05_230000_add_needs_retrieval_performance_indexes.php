<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('needs', function (Blueprint $table) {
            // تسريع قوائم الاحتياجات: branch/created_by + ترتيب id
            $table->index(['branch_id', 'id'], 'needs_branch_id_idx');
            $table->index(['created_by', 'id'], 'needs_created_by_id_idx');
            $table->index(['governorate_id', 'id'], 'needs_gov_id_idx');
        });

        Schema::table('syria_locations', function (Blueprint $table) {
            $table->index(['gov_pcode', 'district_name_ar'], 'syria_gov_district_idx');
            $table->index(['gov_pcode', 'district_name_ar', 'subdistrict_name_ar'], 'syria_gov_dist_sub_idx');
        });
    }

    public function down(): void
    {
        Schema::table('needs', function (Blueprint $table) {
            $table->dropIndex('needs_branch_id_idx');
            $table->dropIndex('needs_created_by_id_idx');
            $table->dropIndex('needs_gov_id_idx');
        });

        Schema::table('syria_locations', function (Blueprint $table) {
            $table->dropIndex('syria_gov_district_idx');
            $table->dropIndex('syria_gov_dist_sub_idx');
        });
    }
};
