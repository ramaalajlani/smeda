<?php

namespace App\Services\Gis;

final class SyriaLocationsImportResult
{
    public function __construct(
        public readonly string $file,
        public readonly string $sheet,
        public readonly int $totalRowsDetected,
        public readonly int $importedRows,
        public readonly int $skippedRows,
        public readonly int $upsertBatches,
    ) {}
}
