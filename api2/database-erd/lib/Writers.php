<?php

declare(strict_types=1);

class Writers
{
    private string $outDir;

    /** @var array */
    private array $report;

    public function __construct(string $outDir, array $report)
    {
        $this->outDir = $outDir;
        $this->report = $report;
    }

    public function writeAll(): void
    {
        $this->writeDbml();
        $this->writeMermaid();
        $this->writeSql();
        $this->writeDictionary();
        $this->writeRelationships();
        $this->writeAudit();
        $this->writeUnresolved();
        $this->validateOutputs();
    }

    public function writeDbml(): void
    {
        $lines = ["// SMEDC Authority Database Schema — ACTUAL DATABASE (authority3)", "// Generated from live MySQL — DO NOT EXECUTE", ''];
        $schema = $this->report['schema'];

        foreach ($schema as $tableName => $table) {
            $lines[] = "Table {$tableName} {";
            $lines[] = "  Note: '" . $this->escapeDbml($table['description'] ?? '') . "'";
            foreach ($table['columns'] as $colName => $col) {
                $dbmlType = $this->toDbmlType($col);
                $settings = [];
                if ($col['pk'] ?? false) {
                    $settings[] = 'pk';
                }
                if ($col['auto_increment'] ?? false) {
                    $settings[] = 'increment';
                }
                if (!($col['nullable'] ?? false)) {
                    $settings[] = 'not null';
                }
                if (($col['default'] ?? null) !== null) {
                    $settings[] = "default: '" . $this->escapeDbml((string) $col['default']) . "'";
                }
                if (!empty($col['enum_values'])) {
                    $settings[] = "note: 'ENUM: " . implode(', ', $col['enum_values']) . "'";
                }
                $settingStr = empty($settings) ? '' : ' [' . implode(', ', $settings) . ']';
                $lines[] = "  {$colName} {$dbmlType}{$settingStr}";
            }
            $lines[] = '}';
            $lines[] = '';
        }

        $seenRefs = [];
        foreach ($this->report['relationships'] as $rel) {
            if ($rel['confidence'] !== 'مؤكدة' || ($rel['relation_type'] ?? '') === 'Polymorphic') {
                continue;
            }
            $ref = "Ref: {$rel['child_table']}.{$rel['fk_column']} > {$rel['parent_table']}.{$rel['pk_column']}";
            if (!isset($seenRefs[$ref])) {
                $deleteNote = ($rel['on_delete'] ?? '') ? " // on delete: {$rel['on_delete']}" : '';
                $lines[] = $ref . $deleteNote;
                $seenRefs[$ref] = true;
            }
        }

        file_put_contents($this->outDir . '/schema.dbml', implode("\n", $lines));
    }

    public function writeMermaid(): void
    {
        $lines = ['erDiagram', ''];
        $schema = $this->report['schema'];

        foreach ($schema as $tableName => $table) {
            $safeName = $this->mermaidId($tableName);
            $lines[] = "    {$safeName} {";
            foreach ($table['columns'] as $colName => $col) {
                $type = $this->toMermaidType($col);
                $pk = ($col['pk'] ?? false) ? ' PK' : '';
                $lines[] = "        {$type} {$colName}{$pk}";
            }
            $lines[] = '    }';
        }

        $lines[] = '';
        $seen = [];
        foreach ($this->report['relationships'] as $rel) {
            if (($rel['relation_type'] ?? '') === 'Polymorphic') {
                continue;
            }
            if (!isset($schema[$rel['parent_table']]) || !isset($schema[$rel['child_table']])) {
                continue;
            }
            $parent = $this->mermaidId($rel['parent_table']);
            $child = $this->mermaidId($rel['child_table']);
            $cardinality = str_contains($rel['relation_type'], 'Many-to-Many') ? '}o--o{' : '||--o{';
            $label = $rel['fk_column'];
            $key = "{$parent}-{$child}-{$label}";
            if (isset($seen[$key])) {
                continue;
            }
            $lines[] = "    {$parent} {$cardinality} {$child} : \"{$label}\"";
            $seen[$key] = true;
        }

        file_put_contents($this->outDir . '/erd.mmd', implode("\n", $lines));
    }

    private function writeSql(): void
    {
        $schema = $this->report['schema'];
        $order = $this->topologicalSort($schema);
        $lines = [
            '-- SMEDC Database Schema (generated from migrations)',
            '-- DO NOT EXECUTE — documentation only',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach ($order as $tableName) {
            $table = $schema[$tableName];
            $colDefs = [];
            foreach ($table['columns'] as $colName => $col) {
                $def = "  `{$colName}` " . $this->toSqlType($col);
                if ($col['pk'] ?? false) {
                    $def .= ' NOT NULL';
                } elseif (!($col['nullable'] ?? false)) {
                    $def .= ' NOT NULL';
                } else {
                    $def .= ' NULL';
                }
                if ($col['auto_increment'] ?? false) {
                    $def .= ' AUTO_INCREMENT';
                }
                if (($col['default'] ?? null) !== null && !($col['auto_increment'] ?? false)) {
                    $def .= " DEFAULT '" . addslashes((string) $col['default']) . "'";
                }
                $colDefs[] = $def;
            }

            if (!empty($table['primary_key'])) {
                $pkCols = implode('`, `', $table['primary_key']);
                $colDefs[] = "  PRIMARY KEY (`{$pkCols}`)";
            }

            foreach ($table['unique_keys'] as $uk) {
                $cols = implode('`, `', $uk['columns']);
                $name = $uk['name'] ?? implode('_', $uk['columns']) . '_unique';
                $colDefs[] = "  UNIQUE KEY `{$name}` (`{$cols}`)";
            }

            foreach ($table['indexes'] as $idx) {
                $cols = implode('`, `', $idx['columns']);
                $name = $idx['name'] ?? implode('_', $idx['columns']) . '_index';
                if (!$this->isDuplicateKey($table, $idx['columns'], 'index')) {
                    $colDefs[] = "  KEY `{$name}` (`{$cols}`)";
                }
            }

            foreach ($table['foreign_keys'] as $fk) {
                $name = $fk['name'] ?? "{$fk['column']}_foreign";
                $onDelete = strtoupper($fk['on_delete'] ?? 'RESTRICT');
                $onUpdate = strtoupper($fk['on_update'] ?? 'RESTRICT');
                $colDefs[] = "  CONSTRAINT `{$name}` FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['ref_table']}` (`{$fk['ref_column']}`) ON DELETE {$onDelete} ON UPDATE {$onUpdate}";
            }

            $lines[] = "CREATE TABLE IF NOT EXISTS `{$tableName}` (";
            $lines[] = implode(",\n", $colDefs);
            $lines[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        file_put_contents($this->outDir . '/schema.sql', implode("\n", $lines));
    }

    private function writeDictionary(): void
    {
        $lines = [
            '# قاموس بيانات SMEDC — قاعدة بيانات الهيئة',
            '',
            '> **مصدر البيانات:** Laravel migrations (`api2/database/migrations/`)',
            '> **تاريخ التوليد:** ' . date('Y-m-d H:i:s'),
            '> **عدد الجداول:** ' . $this->report['stats']['tables'],
            '',
            '---',
            '',
        ];

        foreach ($this->report['schema'] as $tableName => $table) {
            $lines[] = "## جدول `{$tableName}`";
            $lines[] = '';
            $lines[] = '**الوظيفة:** ' . ($table['description'] ?? '-');
            $lines[] = '**مصدر التعريف:** `' . ($table['source'] ?? '-') . '`';
            $lines[] = '**Soft Delete:** ' . (($table['soft_deletes'] ?? false) ? 'نعم (`deleted_at`)' : 'لا');
            $lines[] = '**Timestamps:** ' . (($table['timestamps'] ?? false) ? 'نعم' : 'لا');
            $lines[] = '';
            $lines[] = '| العمود | النوع | NULL | الافتراضي | PK | FK | UNIQUE | INDEX | ENUM | الوصف |';
            $lines[] = '|-------|------|------|----------|----|----|--------|-------|------|-------|';

            $fkMap = [];
            foreach ($table['foreign_keys'] as $fk) {
                $fkMap[$fk['column']] = $fk['ref_table'] . '.' . $fk['ref_column'];
            }

            foreach ($table['columns'] as $colName => $col) {
                $enum = !empty($col['enum_values']) ? implode(', ', $col['enum_values']) : '-';
                $lines[] = sprintf(
                    '| `%s` | %s | %s | %s | %s | %s | %s | %s | %s | %s |',
                    $colName,
                    $col['type'] ?? '-',
                    ($col['nullable'] ?? false) ? 'YES' : 'NO',
                    ($col['default'] ?? null) !== null ? $col['default'] : '-',
                    ($col['pk'] ?? false) ? '✓' : '-',
                    $fkMap[$colName] ?? '-',
                    '-',
                    '-',
                    $enum,
                    $this->describeColumn($colName)
                );
            }

            $related = array_filter($this->report['relationships'], fn ($r) => $r['parent_table'] === $tableName || $r['child_table'] === $tableName);
            if ($related) {
                $lines[] = '';
                $lines[] = '**العلاقات:**';
                foreach ($related as $r) {
                    $dir = $r['parent_table'] === $tableName ? '→ ' . $r['child_table'] : '← ' . $r['parent_table'];
                    $lines[] = "- {$dir} ({$r['relation_type']}, {$r['confidence']})";
                }
            }
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
        }

        file_put_contents($this->outDir . '/DATABASE_DICTIONARY_AR.md', implode("\n", $lines));
    }

    private function writeRelationships(): void
    {
        $lines = [
            '# جدول العلاقات — SMEDC Database',
            '',
            '| الجدول الأب | الجدول الابن | PK | FK | نوع العلاقة | Pivot | مصدر العلاقة | درجة الثقة | الملاحظات |',
            '|-----------|------------|----|----|------------|-------|-------------|-----------|----------|',
        ];

        usort($this->report['relationships'], fn ($a, $b) => ($a['parent_table'] ?? '') <=> ($b['parent_table'] ?? ''));

        foreach ($this->report['relationships'] as $r) {
            $lines[] = sprintf(
                '| %s | %s | %s | %s | %s | %s | %s | %s | %s |',
                $r['parent_table'] ?? '-',
                $r['child_table'] ?? '-',
                $r['pk_column'] ?? 'id',
                $r['fk_column'] ?? '-',
                $r['relation_type'] ?? '-',
                ($r['pivot'] ?? '') !== '' ? ($r['pivot'] ?? '-') : '-',
                trim(($r['source'] ?? '') . ' — ' . ($r['evidence'] ?? ''), ' —'),
                $r['confidence'] ?? '-',
                (($r['in_db'] ?? false) ? 'FK في DB' : 'من الكود') . (($r['notes'] ?? '') ? '; ' . $r['notes'] : '')
            );
        }

        file_put_contents($this->outDir . '/RELATIONSHIPS_AR.md', implode("\n", $lines));
    }

    private function writeAudit(): void
    {
        $lines = [
            '# تقرير تدقيق ERD — SMEDC',
            '',
            '> **ملاحظة:** هذا التقرير للقراءة فقط — لم يُنفّذ أي إصلاح.',
            '',
            '## ملخص',
            '',
            '| المؤشر | القيمة |',
            '|--------|--------|',
            '| عدد الجداول | ' . $this->report['stats']['tables'] . ' |',
            '| علاقات مؤكدة (FK) | ' . $this->report['stats']['confirmed_relations'] . ' |',
            '| علاقات قوية (Model) | ' . $this->report['stats']['strong_relations'] . ' |',
            '| علاقات محتملة | ' . $this->report['stats']['probable_relations'] . ' |',
            '| علاقات غير محسومة | ' . $this->report['stats']['unresolved_relations'] . ' |',
            '| مشكلات مكتشفة | ' . count($this->report['audit_issues']) . ' |',
            '',
            '## تعارض SQL vs Migrations',
            '',
        ];

        $cmp = $this->report['sql_comparison'];
        $lines[] = '- **أحدث/أكمل SQL dump:** `' . ($cmp['newest_dump'] ?? 'N/A') . '`';
        $lines[] = '- **جداول في migrations فقط:** ' . count($cmp['only_in_migrations'] ?? []);
        if (!empty($cmp['only_in_migrations'])) {
            $lines[] = '  - ' . implode(', ', array_slice($cmp['only_in_migrations'], 0, 30));
            if (count($cmp['only_in_migrations']) > 30) {
                $lines[] = '  - ... و' . (count($cmp['only_in_migrations']) - 30) . ' أخرى';
            }
        }
        $lines[] = '- **جداول في SQL dump فقط (legacy):** ' . count($cmp['only_in_dumps'] ?? []);
        if (!empty($cmp['only_in_dumps'])) {
            $lines[] = '  - ' . implode(', ', array_slice($cmp['only_in_dumps'], 0, 20));
        }

        $lines[] = '';
        $lines[] = '## المشكلات المكتشفة';
        $lines[] = '';

        $bySeverity = ['حرجة' => [], 'عالية' => [], 'متوسطة' => [], 'منخفضة' => []];
        foreach ($this->report['audit_issues'] as $issue) {
            $bySeverity[$issue['severity']][] = $issue;
        }

        foreach ($bySeverity as $sev => $issues) {
            if (empty($issues)) {
                continue;
            }
            $lines[] = "### {$sev} (" . count($issues) . ')';
            $lines[] = '';
            foreach ($issues as $i) {
                $lines[] = "- **{$i['category']}** — جدول `{$i['table']}`" . ($i['column'] ? ", عمود `{$i['column']}`" : '');
                $lines[] = "  - الملف: `{$i['file']}`";
                $lines[] = "  - التوصية: {$i['recommendation']}";
            }
            $lines[] = '';
        }

        file_put_contents($this->outDir . '/ERD_AUDIT_REPORT_AR.md', implode("\n", $lines));
    }

    private function writeUnresolved(): void
    {
        $lines = [
            '# علاقات غير محسومة — SMEDC',
            '',
            '| الجدول | العمود | جدول مُخمّن | السبب | الملفات المراجَعة |',
            '|--------|-------|-----------|-------|-----------------|',
        ];

        foreach ($this->report['unresolved'] as $u) {
            $lines[] = sprintf(
                '| %s | %s | %s | %s | %s |',
                $u['child_table'],
                $u['column'],
                $u['guessed_parent'] ?? '-',
                $u['reason'],
                implode(', ', $u['files'])
            );
        }

        if (empty($this->report['unresolved'])) {
            $lines[] = '| — | — | — | لا توجد علاقات غير محسومة | — |';
        }

        $lines[] = '';
        $lines[] = '## ما يلزم للتأكيد';
        $lines[] = '';
        $lines[] = '1. مراجعة migrations alter التي تضيف أعمدة بشروط `Schema::hasColumn`';
        $lines[] = '2. مقارنة مع قاعدة بيانات production فعلية (`SHOW CREATE TABLE`)';
        $lines[] = '3. تتبع استعلامات `DB::raw` و `join` في Controllers/Services';

        file_put_contents($this->outDir . '/UNRESOLVED_RELATIONSHIPS_AR.md', implode("\n", $lines));
    }

    public function validateOutputs(): void
    {
        $issues = [];
        $schema = $this->report['schema'];
        $tableNames = array_keys($schema);

        // Check DBML refs
        $dbml = file_get_contents($this->outDir . '/schema.dbml') ?: '';
        if (preg_match_all('/Ref:\s*(\w+)\.(\w+)\s*>\s*(\w+)\.(\w+)/', $dbml, $refs, PREG_SET_ORDER)) {
            foreach ($refs as $ref) {
                [, $child, $fkCol, $parent, $pkCol] = $ref;
                if (!in_array($child, $tableNames, true)) {
                    $issues[] = "DBML: جدول غير موجود {$child}";
                }
                if (!in_array($parent, $tableNames, true)) {
                    $issues[] = "DBML: جدول غير موجود {$parent}";
                }
                if (isset($schema[$child]['columns']) && !isset($schema[$child]['columns'][$fkCol])) {
                    $issues[] = "DBML: عمود FK غير موجود {$child}.{$fkCol}";
                }
            }
        }

        // Duplicate tables in DBML
        preg_match_all('/^Table\s+(\w+)\s*\{/m', $dbml, $dbmlTables);
        $dupes = array_diff_assoc($dbmlTables[1], array_unique($dbmlTables[1]));
        foreach ($dupes as $d) {
            $issues[] = "DBML: جدول مكرر {$d}";
        }

        $validationFile = $this->outDir . '/validation.log';
        file_put_contents($validationFile, empty($issues) ? "OK — no validation issues\n" : implode("\n", $issues) . "\n");
    }

    /** @param array<string, array> $schema */
    private function topologicalSort(array $schema): array
    {
        $deps = [];
        foreach ($schema as $name => $table) {
            $deps[$name] = [];
            foreach ($table['foreign_keys'] as $fk) {
                if (isset($schema[$fk['ref_table']]) && $fk['ref_table'] !== $name) {
                    $deps[$name][] = $fk['ref_table'];
                }
            }
        }

        $sorted = [];
        $visited = [];
        $visit = function (string $n) use (&$visit, &$sorted, &$visited, $deps): void {
            if (isset($visited[$n])) {
                return;
            }
            $visited[$n] = true;
            foreach ($deps[$n] ?? [] as $d) {
                $visit($d);
            }
            $sorted[] = $n;
        };

        foreach (array_keys($schema) as $t) {
            $visit($t);
        }
        return $sorted;
    }

    private function toDbmlType(array $col): string
    {
        $type = $col['type'] ?? 'varchar';
        if (!empty($col['enum_values'])) {
            return 'varchar';
        }
        if (str_contains($type, 'bigint unsigned')) {
            return 'bigint';
        }
        if (str_starts_with($type, 'varchar')) {
            return 'varchar';
        }
        if (str_starts_with($type, 'decimal')) {
            return 'decimal';
        }
        return preg_replace('/\([^)]+\)/', '', $type) ?: 'varchar';
    }

    private function toMermaidType(array $col): string
    {
        if ($col['pk'] ?? false) {
            return 'bigint';
        }
        $type = $col['type'] ?? 'string';
        if (str_contains($type, 'int') || str_contains($type, 'bigint')) {
            return 'int';
        }
        if (str_contains($type, 'bool')) {
            return 'boolean';
        }
        if (str_contains($type, 'json')) {
            return 'json';
        }
        if (str_contains($type, 'timestamp') || str_contains($type, 'datetime')) {
            return 'datetime';
        }
        return 'string';
    }

    private function toSqlType(array $col): string
    {
        $type = $col['type'] ?? 'varchar(255)';
        if (!empty($col['enum_values'])) {
            $vals = array_map(fn ($v) => "'" . addslashes($v) . "'", $col['enum_values']);
            return 'ENUM(' . implode(',', $vals) . ')';
        }
        if ($type === 'boolean') {
            return 'TINYINT(1)';
        }
        if ($type === 'enum') {
            return 'VARCHAR(255)';
        }
        if ($type === 'int') {
            return 'INT';
        }
        if ($type === 'bigint unsigned') {
            return 'BIGINT UNSIGNED';
        }
        if ($type === 'json') {
            return 'JSON';
        }
        if ($type === 'timestamp') {
            return 'TIMESTAMP NULL';
        }
        return strtoupper($type);
    }

    private function mermaidId(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    }

    private function escapeDbml(string $s): string
    {
        return str_replace("'", "\\'", $s);
    }

    private function describeColumn(string $name): string
    {
        if ($name === 'created_at') {
            return 'تاريخ الإنشاء';
        }
        if ($name === 'updated_at') {
            return 'تاريخ آخر تحديث';
        }
        if ($name === 'deleted_at') {
            return 'تاريخ الحذف الناعم';
        }
        if (str_ends_with($name, '_id')) {
            return 'مفتاح خارجي';
        }
        return '-';
    }

    /** @param list<string> $cols */
    private function isDuplicateKey(array $table, array $cols, string $kind): bool
    {
        foreach ($table['primary_key'] as $pk) {
            if ($cols === [$pk]) {
                return true;
            }
        }
        return false;
    }
}
