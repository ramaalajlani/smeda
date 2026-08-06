<?php

namespace App\Services\Gis;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

final class SpreadsheetChunkReadFilter implements IReadFilter
{
    /** @var list<string> */
    private const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'O', 'P'];

    public function __construct(
        private readonly int $startRow,
        private readonly int $endRow,
    ) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        if ($row < $this->startRow || $row > $this->endRow) {
            return false;
        }

        return in_array($columnAddress, self::COLUMNS, true);
    }
}
