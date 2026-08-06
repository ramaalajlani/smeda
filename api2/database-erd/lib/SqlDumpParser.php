<?php

declare(strict_types=1);

class SqlDumpParser
{
    /** @var array<string, array> table => parsed from each dump file */
    private array $dumps = [];

    /** @var array<string, string> file => label */
    private array $dumpLabels = [];

    public function parseFile(string $path): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }

        $label = basename($path);
        $this->dumpLabels[$path] = $label;
        $tables = [];

        // Match CREATE TABLE blocks
        if (preg_match_all('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?[`"]?(\w+)[`"]?\s*\((.*?)\)\s*(?:ENGINE|DEFAULT CHARSET|;)/si', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $tableName = $m[1];
                $body = $m[2];
                $columns = [];
                $foreignKeys = [];
                $primaryKey = [];

                foreach (explode("\n", $body) as $line) {
                    $line = trim($line, " \t,");
                    if ($line === '' || str_starts_with($line, '--')) {
                        continue;
                    }
                    if (preg_match('/^PRIMARY KEY\s*\(([^)]+)\)/i', $line, $pk)) {
                        preg_match_all('/[`"]?(\w+)[`"]?/', $pk[1], $pkCols);
                        $primaryKey = $pkCols[1];
                        continue;
                    }
                    if (preg_match('/^CONSTRAINT\s+[`"]?(\w+)[`"]?\s+FOREIGN KEY\s*\([`"]?(\w+)[`"]?\)\s+REFERENCES\s+[`"]?(\w+)[`"]?\s*\([`"]?(\w+)[`"]?\)/i', $line, $fk)) {
                        $onDelete = 'restrict';
                        if (preg_match('/ON DELETE\s+(\w+(?:\s+\w+)?)/i', $line, $od)) {
                            $onDelete = strtolower(str_replace(' ', ' ', $od[1]));
                        }
                        $foreignKeys[] = [
                            'name' => $fk[1],
                            'column' => $fk[2],
                            'ref_table' => $fk[3],
                            'ref_column' => $fk[4],
                            'on_delete' => $onDelete,
                        ];
                        continue;
                    }
                    if (preg_match('/^[`"]?(\w+)[`"]?\s+(\w+(?:\([^)]+\))?)/i', $line, $col)) {
                        $columns[$col[1]] = ['type' => strtolower($col[2])];
                    }
                }

                $tables[$tableName] = [
                    'columns' => $columns,
                    'primary_key' => $primaryKey,
                    'foreign_keys' => $foreignKeys,
                    'source_dump' => $label,
                ];
            }
        }

        $this->dumps[$path] = $tables;
    }

    /** @param array<string, array> $migrationSchema */
    public function compareWithSchema(array $migrationSchema): array
    {
        $migrationTables = array_keys($migrationSchema);
        $comparison = [
            'dump_files' => array_keys($this->dumps),
            'newest_dump' => null,
            'dump_table_counts' => [],
            'only_in_migrations' => [],
            'only_in_dumps' => [],
            'conflicts' => [],
        ];

        // Pick dump with most tables as "newest/most complete" among project dumps
        $bestDump = null;
        $bestCount = 0;
        foreach ($this->dumps as $path => $tables) {
            $count = count($tables);
            $comparison['dump_table_counts'][basename($path)] = $count;
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDump = $path;
            }
        }
        $comparison['newest_dump'] = $bestDump ? basename($bestDump) : null;

        if ($bestDump) {
            $dumpTables = array_keys($this->dumps[$bestDump]);
            $comparison['only_in_migrations'] = array_values(array_diff($migrationTables, $dumpTables));
            $comparison['only_in_dumps'] = array_values(array_diff($dumpTables, $migrationTables));
        }

        return $comparison;
    }

    /** @return array<string, array> */
    public function getDumps(): array
    {
        return $this->dumps;
    }
}
