<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE certificates MODIFY status ENUM(
                'draft',
                'pending_center_approval',
                'pending_training_approval',
                'pending_deputy_approval',
                'pending_general_director_approval',
                'approved',
                'rejected',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'");

            DB::statement("ALTER TABLE certificate_approvals MODIFY approval_step ENUM(
                'center_approval',
                'training_manager_approval',
                'deputy_director_approval',
                'general_director_approval'
            ) NOT NULL");
        }

        $certificateIdsWithGdStep = DB::table('certificate_approvals')
            ->where('approval_step', 'general_director_approval')
            ->pluck('certificate_id')
            ->all();

        $pendingCertificates = DB::table('certificates')
            ->whereNotIn('status', ['approved', 'rejected', 'cancelled', 'draft'])
            ->when($certificateIdsWithGdStep !== [], fn ($q) => $q->whereNotIn('id', $certificateIdsWithGdStep))
            ->pluck('id');

        $now = now();

        foreach ($pendingCertificates as $certificateId) {
            DB::table('certificate_approvals')->insert([
                'certificate_id' => $certificateId,
                'approved_by' => null,
                'approval_step' => 'general_director_approval',
                'decision' => 'pending',
                'decision_at' => null,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('certificate_approvals')
            ->where('approval_step', 'general_director_approval')
            ->where('decision', 'pending')
            ->delete();

        DB::table('certificates')
            ->where('status', 'pending_general_director_approval')
            ->update(['status' => 'pending_deputy_approval']);

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE certificates MODIFY status ENUM(
                'draft',
                'pending_center_approval',
                'pending_training_approval',
                'pending_deputy_approval',
                'approved',
                'rejected',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'");

            DB::statement("ALTER TABLE certificate_approvals MODIFY approval_step ENUM(
                'center_approval',
                'training_manager_approval',
                'deputy_director_approval'
            ) NOT NULL");
        }
    }
};
