<?php

namespace App\Services\OldDataImport;

use Illuminate\Support\Facades\DB;

class IdMapper
{
    public function __construct(
        private readonly bool $dryRun = true,
    ) {}

    public function remember(string $source, string $entity, int $oldId, int $newId, ?string $dedupeKey = null): void
    {
        if ($this->dryRun) {
            return;
        }

        DB::table('legacy_import_id_mappings')->updateOrInsert(
            [
                'source' => $source,
                'entity' => $entity,
                'old_id' => $oldId,
            ],
            [
                'new_id' => $newId,
                'dedupe_key' => $dedupeKey,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function resolve(string $source, string $entity, ?int $oldId): ?int
    {
        if ($oldId === null) {
            return null;
        }

        $mapped = DB::table('legacy_import_id_mappings')
            ->where('source', $source)
            ->where('entity', $entity)
            ->where('old_id', $oldId)
            ->value('new_id');

        return $mapped !== null ? (int) $mapped : null;
    }

    public function has(string $source, string $entity, int $oldId): bool
    {
        return $this->resolve($source, $entity, $oldId) !== null;
    }
}
