<?php

namespace App\Services\OldDataImport;

class SqlDumpAnalyzer
{
    /**
     * @return array<string, array{columns: array<int, string>, engine: ?string}>
     */
    public function analyzeFile(string $path): array
    {
        if (! is_file($path)) {
            throw new \InvalidArgumentException("SQL dump not found: {$path}");
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open SQL dump: {$path}");
        }

        $tables = [];
        $currentTable = null;
        $buffer = '';

        while (! feof($handle)) {
            $line = fgets($handle);
            if ($line === false) {
                break;
            }

            if (preg_match('/^CREATE TABLE `([^`]+)` \(/', $line, $m)) {
                $currentTable = $m[1];
                $tables[$currentTable] = ['columns' => [], 'engine' => null];
                $buffer = $line;
                continue;
            }

            if ($currentTable !== null) {
                $buffer .= $line;

                if (preg_match('/^\s*`([^`]+)`\s+/m', $line, $col)) {
                    $tables[$currentTable]['columns'][] = $col[1];
                }

                if (str_contains($line, ') ENGINE=')) {
                    if (preg_match('/ENGINE=([A-Za-z0-9]+)/', $line, $engine)) {
                        $tables[$currentTable]['engine'] = $engine[1];
                    }
                    $currentTable = null;
                    $buffer = '';
                }
            }
        }

        fclose($handle);

        return $tables;
    }

    /**
     * @return array<string, int>
     */
    public function countInserts(string $path): array
    {
        $counts = [];
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return $counts;
        }

        while (! feof($handle)) {
            $line = fgets($handle);
            if ($line === false) {
                break;
            }

            if (preg_match('/^INSERT INTO `([^`]+)`/i', $line, $m)) {
                $table = $m[1];
                $counts[$table] = ($counts[$table] ?? 0) + 1;
            }
        }

        fclose($handle);

        return $counts;
    }
}
