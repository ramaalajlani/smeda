<?php

namespace App\Services\Gis;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class SyriaLocationsImportService
{
    private const DEFAULT_SHEET = 'syria';

    private const BATCH_SIZE = 500;

    /** @var list<string> */
    private const DATA_COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'O', 'P'];

    private int $totalRowsDetected = 0;

    private int $importedRows = 0;

    private int $skippedRows = 0;

    private int $upsertBatches = 0;

    public function import(string $filePath, ?OutputInterface $output = null, int $batchSize = self::BATCH_SIZE): SyriaLocationsImportResult
    {
        $this->resetCounters();
        $output ??= new NullOutput();

        if (!is_file($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $sheetName = self::DEFAULT_SHEET;

        $output->writeln('<info>File:</info> ' . basename($filePath));

        if ($extension === 'csv') {
            $output->writeln('<info>Format:</info> CSV (streaming)');
            $sheetName = $this->importCsv($filePath, $output, $batchSize);
        } else {
            $sheetName = $this->importXlsx($filePath, $output, $batchSize);
        }

        $result = new SyriaLocationsImportResult(
            file: $filePath,
            sheet: $sheetName,
            totalRowsDetected: $this->totalRowsDetected,
            importedRows: $this->importedRows,
            skippedRows: $this->skippedRows,
            upsertBatches: $this->upsertBatches,
        );

        $this->printSummary($output, $result);

        return $result;
    }

    public function defaultFilePath(): string
    {
        $csv = base_path('storage/geo/Syria Location Int.csv');
        if (is_file($csv)) {
            return $csv;
        }

        return base_path('storage/geo/Syria Location Int.xlsx');
    }

    private function resetCounters(): void
    {
        $this->totalRowsDetected = 0;
        $this->importedRows = 0;
        $this->skippedRows = 0;
        $this->upsertBatches = 0;
    }

    private function importXlsx(string $filePath, OutputInterface $output, int $batchSize): string
    {
        $sheetName = $this->resolveSheetName($filePath);
        $output->writeln('<info>Sheet:</info> ' . $sheetName);
        $output->writeln('<info>Format:</info> XLSX (chunked PhpSpreadsheet reader)');

        $startRow = 2;
        $batch = [];

        while (true) {
            $endRow = $startRow + $batchSize - 1;
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $reader->setReadFilter(new SpreadsheetChunkReadFilter($startRow, $endRow));
            $reader->setLoadSheetsOnly([$sheetName]);

            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();
            $chunkHighest = $sheet->getHighestDataRow();

            if ($chunkHighest < $startRow) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet, $reader, $sheet);
                gc_collect_cycles();
                break;
            }

            $chunkEnd = min($endRow, $chunkHighest);
            $chunkHadRows = false;

            for ($row = $startRow; $row <= $chunkEnd; $row++) {
                $this->totalRowsDetected++;
                $record = $this->mapRowFromWorksheet($sheet, $row);

                if ($record === null) {
                    $this->skippedRows++;
                    continue;
                }

                $batch[] = $record;
                $chunkHadRows = true;

                if (count($batch) >= $batchSize) {
                    $this->flushBatch($batch, $output);
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader, $sheet);
            gc_collect_cycles();

            if (!$chunkHadRows || $chunkHighest < $endRow) {
                break;
            }

            $startRow += $batchSize;
        }

        if ($batch !== []) {
            $this->flushBatch($batch, $output);
        }

        return $sheetName;
    }

    private function resolveSheetName(string $filePath): string
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        if (method_exists($reader, 'listWorksheetNames')) {
            $names = $reader->listWorksheetNames($filePath);
            if (in_array(self::DEFAULT_SHEET, $names, true)) {
                return self::DEFAULT_SHEET;
            }

            return $names[0] ?? self::DEFAULT_SHEET;
        }

        return self::DEFAULT_SHEET;
    }

    private function importCsv(string $filePath, OutputInterface $output, int $batchSize): string
    {
        $output->writeln('<info>Sheet:</info> n/a (CSV)');

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV: {$filePath}");
        }

        $batch = [];

        try {
            if (fread($handle, 3) !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            if ($header === false) {
                return 'csv';
            }

            while (($row = fgetcsv($handle)) !== false) {
                if ($this->isCsvRowEmpty($row)) {
                    continue;
                }

                $this->totalRowsDetected++;
                $record = $this->mapRowFromCsv($row);

                if ($record === null) {
                    $this->skippedRows++;
                    continue;
                }

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    $this->flushBatch($batch, $output);
                }
            }
        } finally {
            fclose($handle);
        }

        if ($batch !== []) {
            $this->flushBatch($batch, $output);
        }

        return 'csv';
    }

    /**
     * @param  list<string|null>  $row
     */
    private function isCsvRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) ($value ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string|null>  $row
     * @return array<string, mixed>|null
     */
    private function mapRowFromCsv(array $row): ?array
    {
        $cells = [];
        foreach (self::DATA_COLUMNS as $index => $column) {
            $cells[$column] = trim((string) ($row[$this->csvColumnIndex($column)] ?? ''));
        }

        return $this->mapCells($cells);
    }

    private function csvColumnIndex(string $column): int
    {
        return ord($column) - ord('A');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapRowFromWorksheet(Worksheet $sheet, int $row): ?array
    {
        $cells = [];
        foreach (self::DATA_COLUMNS as $column) {
            $cells[$column] = $this->cellValue($sheet, $column, $row);
        }

        return $this->mapCells($cells);
    }

    private function cellValue(Worksheet $sheet, string $column, int $row): string
    {
        $value = $sheet->getCell($column . $row)->getCalculatedValue();

        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value)) {
            return trim((string) $value);
        }

        return trim((string) $value);
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, mixed>|null
     */
    private function mapCells(array $row): ?array
    {
        $communityPcode = $row['L'] ?? '';
        $govPcode = $row['C'] ?? '';
        $lat = $this->toFloat($row['O'] ?? '');
        $lng = $this->toFloat($row['P'] ?? '');

        if ($communityPcode === '' || $govPcode === '' || $lat === null || $lng === null) {
            return null;
        }

        if ($lat === 0.0 && $lng === 0.0) {
            return null;
        }

        return [
            'gov_pcode' => $govPcode,
            'gov_name_en' => $row['A'] ?? '',
            'gov_name_ar' => $row['B'] ?? '',
            'district_pcode' => $row['F'] ?? '',
            'district_name_en' => $row['D'] ?? '',
            'district_name_ar' => $row['E'] ?? '',
            'subdistrict_pcode' => $row['I'] ?? '',
            'subdistrict_name_en' => $row['G'] ?? '',
            'subdistrict_name_ar' => $row['H'] ?? '',
            'community_pcode' => $communityPcode,
            'community_name_en' => $row['J'] ?? '',
            'community_name_ar' => $row['K'] ?? '',
            'latitude' => $lat,
            'longitude' => $lng,
        ];
    }

    private function toFloat(string $value): ?float
    {
        $value = trim(str_replace(',', '.', $value));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * @param  list<array<string, mixed>>  $batch
     */
    private function flushBatch(array &$batch, OutputInterface $output): void
    {
        if ($batch === []) {
            return;
        }

        DB::table('syria_locations')->upsert($batch, ['community_pcode']);
        $this->importedRows += count($batch);
        $this->upsertBatches++;
        $output->writeln("  Upserted batch #{$this->upsertBatches} ({$this->importedRows} rows total so far)");
        $batch = [];
    }

    private function printSummary(OutputInterface $output, SyriaLocationsImportResult $result): void
    {
        $output->newLine();
        $output->writeln('<info>Import summary</info>');
        $output->writeln('  File:              ' . basename($result->file));
        $output->writeln('  Sheet:             ' . $result->sheet);
        $output->writeln('  Total rows read:   ' . $result->totalRowsDetected);
        $output->writeln('  Imported (upsert): ' . $result->importedRows);
        $output->writeln('  Skipped:           ' . $result->skippedRows);
        $output->writeln('  Upsert batches:    ' . $result->upsertBatches);
        $output->writeln('<info>Done.</info>');
    }
}
