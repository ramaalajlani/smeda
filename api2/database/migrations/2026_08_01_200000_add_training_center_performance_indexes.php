<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * فهارس أداء لمسارات المركز: صفوف، حضور، درجات، نطاق متدربي المركز.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_attendance', function (Blueprint $table) {
            $table->index(['course_session_id', 'status'], 'sa_session_status_idx');
        });

        Schema::table('training_course_trainee', function (Blueprint $table) {
            $table->index(['training_course_id', 'course_group_id'], 'tct_course_group_idx');
            $table->index(['course_group_id', 'trainee_id'], 'tct_group_trainee_idx');
        });

        Schema::table('course_sessions', function (Blueprint $table) {
            $table->index(['training_course_id', 'course_group_id', 'session_date'], 'cs_course_group_date_idx');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->index(['result', 'id'], 'cert_result_id_idx');
            $table->index(['training_program_id', 'status', 'id'], 'cert_program_stat_id_idx');
        });

        Schema::table('trainees', function (Blueprint $table) {
            if (! Schema::hasColumn('trainees', 'owned_training_center_id')) {
                $table->foreignId('owned_training_center_id')
                    ->nullable()
                    ->constrained('training_centers')
                    ->nullOnDelete();
                $table->index(['owned_training_center_id', 'status', 'id'], 'trainees_owned_center_stat_idx');
            }
        });

        // Backfill من وسم الملاحظات القديم [center:N]
        if (Schema::hasColumn('trainees', 'owned_training_center_id')) {
            DB::statement("
                UPDATE trainees
                SET owned_training_center_id = CAST(
                    SUBSTRING(notes, LOCATE('[center:', notes) + 8, LOCATE(']', notes, LOCATE('[center:', notes)) - LOCATE('[center:', notes) - 8)
                    AS UNSIGNED
                )
                WHERE owned_training_center_id IS NULL
                  AND notes LIKE '[center:%]%'
                  AND LOCATE(']', notes, LOCATE('[center:', notes)) > LOCATE('[center:', notes) + 8
            ");
        }
    }

    public function down(): void
    {
        Schema::table('session_attendance', function (Blueprint $table) {
            $table->dropIndex('sa_session_status_idx');
        });

        Schema::table('training_course_trainee', function (Blueprint $table) {
            $table->dropIndex('tct_course_group_idx');
            $table->dropIndex('tct_group_trainee_idx');
        });

        Schema::table('course_sessions', function (Blueprint $table) {
            $table->dropIndex('cs_course_group_date_idx');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex('cert_result_id_idx');
            $table->dropIndex('cert_program_stat_id_idx');
        });

        Schema::table('trainees', function (Blueprint $table) {
            if (Schema::hasColumn('trainees', 'owned_training_center_id')) {
                $table->dropIndex('trainees_owned_center_stat_idx');
                $table->dropConstrainedForeignId('owned_training_center_id');
            }
        });
    }
};
