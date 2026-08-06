<?php

namespace App\Services\OldDataImport;

class ImportReport
{
    public int $tablesOld = 0;

    public int $tablesMapped = 0;

    public int $tablesUnmapped = 0;

    public int $rowsRead = 0;

    public int $rowsInserted = 0;

    public int $rowsUpdated = 0;

    public int $rowsSkipped = 0;

    public int $errorsCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $errors = [];

    /** @var array<string, array<string, int>> */
    public array $byTable = [];

    /** @var array<string, string> */
    public array $unmappedTables = [];

    /** @var array<string, mixed> */
    public array $passwordNotes = [];

    /** @var array<string, mixed> */
    public array $missingFiles = [];

    public int $invalidPhonesCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $rejectedPhones = [];

    public function recordInvalidPhone(string $rawValue, array $context = []): void
    {
        $this->invalidPhonesCount++;

        if (count($this->rejectedPhones) < 20) {
            $this->rejectedPhones[] = array_merge([
                'raw_value' => $rawValue,
            ], $context);
        }
    }

    public function increment(string $table, string $metric, int $by = 1): void
    {
        $this->byTable[$table][$metric] = ($this->byTable[$table][$metric] ?? 0) + $by;

        match ($metric) {
            'read' => $this->rowsRead += $by,
            'inserted' => $this->rowsInserted += $by,
            'updated' => $this->rowsUpdated += $by,
            'skipped' => $this->rowsSkipped += $by,
            default => null,
        };
    }

    public function addError(string $table, string $message, array $context = []): void
    {
        $this->errorsCount++;
        $this->errors[] = [
            'table' => $table,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function toArray(): array
    {
        return [
            'summary' => [
                'tables_old' => $this->tablesOld,
                'tables_mapped' => $this->tablesMapped,
                'tables_unmapped' => $this->tablesUnmapped,
                'rows_read' => $this->rowsRead,
                'rows_inserted' => $this->rowsInserted,
                'rows_updated' => $this->rowsUpdated,
                'rows_skipped' => $this->rowsSkipped,
                'errors_count' => $this->errorsCount,
                'invalid_phones_count' => $this->invalidPhonesCount,
            ],
            'by_table' => $this->byTable,
            'unmapped_tables' => $this->unmappedTables,
            'password_notes' => $this->passwordNotes,
            'missing_files' => $this->missingFiles,
            'rejected_phones' => $this->rejectedPhones,
            'errors' => $this->errors,
        ];
    }
}
