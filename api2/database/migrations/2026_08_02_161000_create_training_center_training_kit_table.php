<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_center_training_kit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_center_id')
                ->constrained('training_centers')
                ->cascadeOnDelete();

            $table->foreignId('training_kit_id')
                ->constrained('training_kits')
                ->cascadeOnDelete();

            $table->boolean('is_assigned')->default(true);
            $table->date('assigned_from')->nullable();
            $table->date('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['training_center_id', 'training_kit_id'], 'center_kit_unique');
            $table->index(['training_center_id', 'is_assigned', 'id'], 'center_kit_center_idx');
            $table->index(['training_kit_id', 'is_assigned', 'id'], 'center_kit_kit_idx');
        });

        // Backfill: kits used by center courses
        DB::table('training_courses')
            ->whereNotNull('training_center_id')
            ->whereNotNull('training_kit_id')
            ->select('training_center_id', 'training_kit_id')
            ->distinct()
            ->orderBy('training_center_id')
            ->chunk(200, function ($rows) {
                $now = now();
                $insert = [];
                foreach ($rows as $row) {
                    $insert[] = [
                        'training_center_id' => (int) $row->training_center_id,
                        'training_kit_id' => (int) $row->training_kit_id,
                        'is_assigned' => 1,
                        'assigned_from' => $now->toDateString(),
                        'assigned_to' => null,
                        'notes' => 'Backfilled from courses',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($insert) {
                    DB::table('training_center_training_kit')->insertOrIgnore($insert);
                }
            });

        // Backfill: kits authorized to center trainers
        $pairs = DB::table('trainer_training_kit as ttk')
            ->join('trainers as t', 't.id', '=', 'ttk.trainer_id')
            ->whereNotNull('t.training_center_id')
            ->whereNull('t.deleted_at')
            ->select('t.training_center_id', 'ttk.training_kit_id')
            ->distinct()
            ->get();

        if ($pairs->isNotEmpty()) {
            $now = now();
            $insert = [];
            foreach ($pairs as $row) {
                $insert[] = [
                    'training_center_id' => (int) $row->training_center_id,
                    'training_kit_id' => (int) $row->training_kit_id,
                    'is_assigned' => 1,
                    'assigned_from' => $now->toDateString(),
                    'assigned_to' => null,
                    'notes' => 'Backfilled from trainers',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($insert, 200) as $chunk) {
                DB::table('training_center_training_kit')->insertOrIgnore($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_center_training_kit');
    }
};
