<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            // الحقول الأساسية الجديدة
            $table->string('title')->nullable()->after('name');  // الاسم العربي الرسمي
            $table->string('slug')->nullable()->unique()->after('title');

            $table->enum('type', [
                'entrepreneurial', 'financial', 'marketing',
                'technical', 'digital', 'administrative',
                'agricultural', 'craft', 'other',
            ])->nullable()->after('code');

            $table->string('sector')->nullable()->after('type');
            $table->string('target_audience')->nullable()->after('sector');

            $table->enum('level', ['beginner', 'intermediate', 'advanced'])
                ->default('beginner')->after('target_audience');

            $table->enum('delivery_mode', ['in_person', 'online', 'blended'])
                ->default('in_person')->after('level');

            $table->unsignedSmallInteger('suggested_hours')->default(0)->after('delivery_mode');
            $table->unsignedSmallInteger('suggested_sessions')->default(0)->after('suggested_hours');

            $table->text('prerequisites')->nullable()->after('suggested_sessions');
            $table->text('outcomes_summary')->nullable()->after('prerequisites');

            // الشهادات والتقييم
            $table->boolean('grants_certificate')->default(false)->after('outcomes_summary');
            $table->boolean('requires_final_exam')->default(false)->after('grants_certificate');
            $table->boolean('requires_project')->default(false)->after('requires_final_exam');
            $table->unsignedTinyInteger('min_attendance_percent')->default(80)->after('requires_project');
            $table->unsignedTinyInteger('passing_score')->default(60)->after('min_attendance_percent');

            // الحالة الموسّعة لسير الاعتماد
            $table->enum('bank_status', [
                'draft',
                'under_technical_review',
                'under_admin_review',
                'approved',
                'suspended',
                'archived',
            ])->default('draft')->after('status');

            // المسؤولون
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('bank_status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('suspended_at')->nullable()->after('approved_at');
            $table->timestamp('archived_at')->nullable()->after('suspended_at');

            $table->index('bank_status');
            $table->index('type');
            $table->index('level');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'slug', 'type', 'sector', 'target_audience',
                'level', 'delivery_mode', 'suggested_hours', 'suggested_sessions',
                'prerequisites', 'outcomes_summary', 'grants_certificate',
                'requires_final_exam', 'requires_project', 'min_attendance_percent',
                'passing_score', 'bank_status', 'created_by', 'approved_by',
                'approved_at', 'suspended_at', 'archived_at',
            ]);
        });
    }
};
