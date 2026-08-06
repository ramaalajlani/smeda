<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_sessions', function (Blueprint $table) {
            // الجلسة تخصّ صفاً محدداً (اختياري: null = جلسة على مستوى الدورة كاملة)
            $table->foreignId('course_group_id')
                ->nullable()
                ->after('training_course_id')
                ->constrained('course_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('course_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_group_id');
        });
    }
};
