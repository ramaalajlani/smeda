<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->index('status');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
        });

        Schema::table('success_stories', function (Blueprint $table) {
            $table->index('status');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
        });

        Schema::table('entrepreneur_profiles', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
        });

        Schema::table('incubation_applications', function (Blueprint $table) {
            $table->index('status');
            $table->index('applicant_user_id');
        });

        Schema::table('incubated_projects', function (Blueprint $table) {
            $table->index('status');
            $table->index('owner_user_id');
            $table->index('incubator_id');
        });

        Schema::table('incubation_progress_reports', function (Blueprint $table) {
            $table->index('project_id');
            $table->index('created_at');
        });

        Schema::table('inbox_messages', function (Blueprint $table) {
            $table->index(['sender_id', 'recipient_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('success_stories', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('entrepreneur_profiles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('incubation_applications', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['applicant_user_id']);
        });

        Schema::table('incubated_projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['owner_user_id']);
            $table->dropIndex(['incubator_id']);
        });

        Schema::table('incubation_progress_reports', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('inbox_messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'recipient_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
