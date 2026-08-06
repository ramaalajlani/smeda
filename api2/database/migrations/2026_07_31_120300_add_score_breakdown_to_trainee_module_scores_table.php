<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainee_module_scores', function (Blueprint $table) {
            // تفصيل الدرجة مطابق لتطبيق المرجع: أعمال السنة + درجة الامتحان
            $table->decimal('coursework_score', 5, 2)->nullable()->after('score');
            $table->decimal('exam_score', 5, 2)->nullable()->after('coursework_score');
        });
    }

    public function down(): void
    {
        Schema::table('trainee_module_scores', function (Blueprint $table) {
            $table->dropColumn(['coursework_score', 'exam_score']);
        });
    }
};
