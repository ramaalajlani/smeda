<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الصفوف/المجموعات داخل الدورة (طبقة: دورة ← صفوف ← متدربون)
        Schema::create('course_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')
                ->constrained('training_courses')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['training_course_id', 'id'], 'cg_course_id_idx');
        });

        // ربط المتدرب بصفّه داخل الدورة (على جدول التسجيل الموجود)
        Schema::table('training_course_trainee', function (Blueprint $table) {
            $table->foreignId('course_group_id')
                ->nullable()
                ->after('trainee_id')
                ->constrained('course_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_course_trainee', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_group_id');
        });
        Schema::dropIfExists('course_groups');
    }
};
