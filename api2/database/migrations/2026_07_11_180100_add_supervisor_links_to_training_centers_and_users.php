<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_centers', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('training_supervisors')
                ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('training_supervisor_id')
                ->nullable()
                ->after('training_center_id')
                ->constrained('training_supervisors')
                ->nullOnDelete();
        });

        $defaultSupervisorId = DB::table('training_supervisors')
            ->where('code', 'MOIT-CENTRAL')
            ->value('id');

        if (!$defaultSupervisorId) {
            DB::table('training_supervisors')->insert([
                'name' => 'وزارة التجارة الداخلية وحماية المستهلك - الجهة المركزية',
                'code' => 'MOIT-CENTRAL',
                'type' => 'internal_entity',
                'is_active' => true,
                'notes' => 'جهة افتراضية للمراكز غير المرتبطة بجهة مشرفة محددة.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $defaultSupervisorId = DB::table('training_supervisors')
                ->where('code', 'MOIT-CENTRAL')
                ->value('id');
        }

        if ($defaultSupervisorId) {
            DB::table('training_centers')
                ->whereNull('supervisor_id')
                ->update(['supervisor_id' => $defaultSupervisorId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_supervisor_id');
        });

        Schema::table('training_centers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_id');
        });
    }
};
