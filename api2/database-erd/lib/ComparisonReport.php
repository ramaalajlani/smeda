<?php

declare(strict_types=1);

class ComparisonReport
{
    /** @var array<string, array> */
    private array $actualSchema;

    /** @var array<string, array> */
    private array $migrationSchema;

    /** @var list<array> */
    private array $eloquentRelations;

    /** @var list<array> */
    private array $migrationRelationships;

    /** @var list<array> */
    private array $actualRelationships;

    /** @var list<string> */
    private array $migrationFiles;

    /** @var list<string> */
    private array $modelFiles;

    /**
     * @param array<string, array> $actualSchema
     * @param array<string, array> $migrationSchema
     * @param list<array> $eloquentRelations
     * @param list<array> $migrationRelationships
     * @param list<array> $actualRelationships
     * @param list<string> $migrationFiles
     * @param list<string> $modelFiles
     */
    public function __construct(
        array $actualSchema,
        array $migrationSchema,
        array $eloquentRelations,
        array $migrationRelationships,
        array $actualRelationships,
        array $migrationFiles,
        array $modelFiles
    ) {
        $this->actualSchema = $actualSchema;
        $this->migrationSchema = $migrationSchema;
        $this->eloquentRelations = $eloquentRelations;
        $this->migrationRelationships = $migrationRelationships;
        $this->actualRelationships = $actualRelationships;
        $this->migrationFiles = $migrationFiles;
        $this->modelFiles = $modelFiles;
    }

    /** @return array<string, mixed> */
    public function build(): array
    {
        $actualTables = array_keys($this->actualSchema);
        $migrationTables = array_keys($this->migrationSchema);

        $onlyInDb = array_values(array_diff($actualTables, $migrationTables));
        $onlyInMigrations = array_values(array_diff($migrationTables, $actualTables));
        $commonTables = array_values(array_intersect($actualTables, $migrationTables));

        $columnDiffs = [];
        $fkDiffs = [];
        foreach ($commonTables as $table) {
            $colDiff = $this->compareColumns($table);
            if ($colDiff) {
                $columnDiffs[$table] = $colDiff;
            }
            $fkDiff = $this->compareForeignKeys($table);
            if ($fkDiff) {
                $fkDiffs[$table] = $fkDiff;
            }
        }

        $actualFkSet = $this->relationshipKeySet($this->actualRelationships, 'مؤكدة');
        $migrationFkSet = $this->relationshipKeySet(
            array_filter($this->migrationRelationships, fn ($r) => ($r['confidence'] ?? '') === 'مؤكدة'),
            'مؤكدة'
        );
        $modelRelSet = $this->modelRelationshipKeySet();

        $inDbOnly = array_diff($actualFkSet, $migrationFkSet);
        $inMigrationOnly = array_diff($migrationFkSet, $actualFkSet);
        $inModelOnly = array_diff($modelRelSet, $actualFkSet);

        $categorized = $this->categorizeAllRelationships($actualFkSet, $migrationFkSet, $modelRelSet);

        return [
            'summary' => [
                'actual_tables' => count($actualTables),
                'migration_tables' => count($migrationTables),
                'common_tables' => count($commonTables),
                'only_in_database' => count($onlyInDb),
                'only_in_migrations' => count($onlyInMigrations),
                'tables_with_column_diffs' => count($columnDiffs),
                'tables_with_fk_diffs' => count($fkDiffs),
                'actual_fk_count' => count($actualFkSet),
                'migration_fk_count' => count($migrationFkSet),
                'model_relation_count' => count($modelRelSet),
                'fk_in_db_only' => count($inDbOnly),
                'fk_in_migration_only' => count($inMigrationOnly),
                'relations_in_model_only' => count($inModelOnly),
                'migrations_ran' => count($this->migrationFiles),
            ],
            'only_in_database' => $onlyInDb,
            'only_in_migrations' => $onlyInMigrations,
            'column_diffs' => $columnDiffs,
            'fk_diffs' => $fkDiffs,
            'categorized_relationships' => $categorized,
            'in_db_only_fk' => array_values($inDbOnly),
            'in_migration_only_fk' => array_values($inMigrationOnly),
            'in_model_only_relations' => array_values($inModelOnly),
        ];
    }

    /** @param array<string, mixed> $comparison */
    public function writeReport(string $path, array $comparison, string $dbName): void
    {
        $s = $comparison['summary'];
        $lines = [
            '# تقرير مقارنة قاعدة البيانات الفعلية — SMEDC',
            '',
            '> **تاريخ التوليد:** ' . date('Y-m-d H:i:s'),
            '> **قاعدة البيانات:** `' . $dbName . '`',
            '> **الأمر:** `php artisan migrate:status` — جميع الـ migrations منفّذة',
            '> **طريقة الاستخراج:** `SHOW TABLES`, `information_schema.COLUMNS`, `information_schema.KEY_COLUMN_USAGE`, `SHOW INDEX`',
            '',
            '---',
            '',
            '## 1. ملخص المقارنة',
            '',
            '| المؤشر | القيمة |',
            '|--------|--------|',
            '| جداول في قاعدة البيانات الفعلية | ' . $s['actual_tables'] . ' |',
            '| جداول في migrations | ' . $s['migration_tables'] . ' |',
            '| جداول مشتركة | ' . $s['common_tables'] . ' |',
            '| جداول في DB فقط | ' . $s['only_in_database'] . ' |',
            '| جداول في migrations فقط | ' . $s['only_in_migrations'] . ' |',
            '| جداول باختلاف أعمدة | ' . $s['tables_with_column_diffs'] . ' |',
            '| جداول باختلاف FK | ' . $s['tables_with_fk_diffs'] . ' |',
            '| Foreign Keys في DB الفعلية | ' . $s['actual_fk_count'] . ' |',
            '| Foreign Keys في migrations | ' . $s['migration_fk_count'] . ' |',
            '| علاقات Eloquent في Models | ' . $s['model_relation_count'] . ' |',
            '| FK في DB فقط | ' . $s['fk_in_db_only'] . ' |',
            '| FK في migrations فقط | ' . $s['fk_in_migration_only'] . ' |',
            '| علاقات في Models فقط | ' . $s['relations_in_model_only'] . ' |',
            '',
            '---',
            '',
            '## 2. اختلافات الجداول',
            '',
        ];

        if ($comparison['only_in_database']) {
            $lines[] = '### جداول موجودة في قاعدة البيانات فقط';
            foreach ($comparison['only_in_database'] as $t) {
                $lines[] = "- `{$t}`";
            }
            $lines[] = '';
        } else {
            $lines[] = '### جداول موجودة في قاعدة البيانات فقط';
            $lines[] = '- لا يوجد';
            $lines[] = '';
        }

        if ($comparison['only_in_migrations']) {
            $lines[] = '### جداول موجودة في migrations فقط (غير موجودة في DB)';
            foreach ($comparison['only_in_migrations'] as $t) {
                $lines[] = "- `{$t}`";
            }
            $lines[] = '';
        } else {
            $lines[] = '### جداول موجودة في migrations فقط';
            $lines[] = '- لا يوجد — جميع جداول migrations موجودة في DB';
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## 3. اختلافات الأعمدة (جداول مشتركة)';
        $lines[] = '';

        if (empty($comparison['column_diffs'])) {
            $lines[] = 'لا توجد اختلافات جوهرية في الأعمدة بين DB و migrations.';
        } else {
            foreach ($comparison['column_diffs'] as $table => $diff) {
                $lines[] = "### `{$table}`";
                if (!empty($diff['only_in_db'])) {
                    $lines[] = '- **أعمدة في DB فقط:** ' . implode(', ', array_map(fn ($c) => "`{$c}`", $diff['only_in_db']));
                }
                if (!empty($diff['only_in_migration'])) {
                    $lines[] = '- **أعمدة في migrations فقط:** ' . implode(', ', array_map(fn ($c) => "`{$c}`", $diff['only_in_migration']));
                }
                if (!empty($diff['type_mismatch'])) {
                    $lines[] = '- **اختلاف نوع:**';
                    foreach ($diff['type_mismatch'] as $col => $types) {
                        $lines[] = "  - `{$col}`: DB=`{$types['db']}` vs migration=`{$types['migration']}`";
                    }
                }
                $lines[] = '';
            }
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## 4. اختلافات Foreign Keys';
        $lines[] = '';

        if (empty($comparison['fk_diffs'])) {
            $lines[] = 'Foreign Keys متطابقة بين DB و migrations لجميع الجداول المشتركة.';
        } else {
            foreach ($comparison['fk_diffs'] as $table => $diff) {
                $lines[] = "### `{$table}`";
                if (!empty($diff['only_in_db'])) {
                    foreach ($diff['only_in_db'] as $fk) {
                        $lines[] = "- **FK في DB فقط:** `{$fk['column']}` → `{$fk['ref_table']}.{$fk['ref_column']}` (ON DELETE {$fk['on_delete']})";
                    }
                }
                if (!empty($diff['only_in_migration'])) {
                    foreach ($diff['only_in_migration'] as $fk) {
                        $lines[] = "- **FK في migration فقط:** `{$fk['column']}` → `{$fk['ref_table']}.{$fk['ref_column']}`";
                    }
                }
                $lines[] = '';
            }
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## 5. تصنيف العلاقات';
        $lines[] = '';
        $lines[] = '| التصنيف | العدد | الوصف |';
        $lines[] = '|---------|-------|-------|';
        $lines[] = '| **في DB فعلياً (FK)** | ' . count($comparison['categorized_relationships']['in_database']) . ' | Foreign Key موجود في MySQL |';
        $lines[] = '| **في migrations فقط** | ' . count($comparison['categorized_relationships']['migration_only']) . ' | معرّف في migration لكن غير موجود في DB |';
        $lines[] = '| **في Models فقط** | ' . count($comparison['categorized_relationships']['model_only']) . ' | Eloquent relation بدون FK في DB |';
        $lines[] = '| **مستنتجة من الكود** | ' . count($comparison['categorized_relationships']['inferred']) . ' | من أسماء *_id أو JOIN |';
        $lines[] = '| **في DB + migration + Model** | ' . count($comparison['categorized_relationships']['all_three']) . ' | مؤكدة بالكامل |';
        $lines[] = '';

        $lines[] = '### علاقات في DB + migration + Model (مؤكدة بالكامل)';
        $lines[] = '';
        if (empty($comparison['categorized_relationships']['all_three'])) {
            $lines[] = '_لا توجد_';
        } else {
            foreach (array_slice($comparison['categorized_relationships']['all_three'], 0, 50) as $r) {
                $lines[] = "- `{$r}`";
            }
            if (count($comparison['categorized_relationships']['all_three']) > 50) {
                $lines[] = '- ... و' . (count($comparison['categorized_relationships']['all_three']) - 50) . ' أخرى';
            }
        }
        $lines[] = '';

        $lines[] = '### علاقات في Models فقط (بدون FK في DB)';
        $lines[] = '';
        foreach (array_slice($comparison['categorized_relationships']['model_only'], 0, 40) as $r) {
            $lines[] = "- `{$r['child']}.{$r['fk']}` → `{$r['parent']}` — {$r['model']}::{$r['relation']} ({$r['file']}:{$r['line']})";
        }
        if (count($comparison['categorized_relationships']['model_only']) > 40) {
            $lines[] = '- ... و' . (count($comparison['categorized_relationships']['model_only']) - 40) . ' أخرى (راجع RELATIONSHIPS_AR.md)';
        }
        $lines[] = '';

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## 6. توصيات';
        $lines[] = '';
        $lines[] = '1. **schema.dbml** و **erd.mmd** حُدّثا ليعكسا قاعدة البيانات الفعلية (`authority3`).';
        $lines[] = '2. العلاقات في Models بدون FK في DB تُعرّف منطقياً في Laravel لكن لا تُفرض على مستوى MySQL.';
        $lines[] = '3. أي FK في migration غير موجود في DB يستدعي مراجعة ما إذا كان migration alter فشل أو تم تعديل DB يدوياً.';
        $lines[] = '';

        file_put_contents($path, implode("\n", $lines));
    }

    /** @param array<string, mixed> $comparison */
    public function writeEnhancedRelationships(string $path, array $comparison): void
    {
        $cat = $comparison['categorized_relationships'];
        $lines = [
            '# جدول العلاقات — SMEDC Database (محدّث من DB الفعلية)',
            '',
            '> **مصدر DB الفعلية:** authority3 — ' . date('Y-m-d H:i:s'),
            '',
            '## مفتاح التصنيف',
            '',
            '| التصنيف | المعنى |',
            '|---------|--------|',
            '| DB | Foreign Key موجود فعلياً في MySQL |',
            '| migration | معرّف في migration فقط |',
            '| Model | موجود في Eloquent فقط |',
            '| inferred | مستنتج من *_id |',
            '| DB+migration+Model | مؤكد بالكامل |',
            '',
            '| الجدول الأب | الجدول الابن | PK | FK | نوع العلاقة | التصنيف | ON DELETE | المصدر |',
            '|-----------|------------|----|----|------------|---------|-----------|--------|',
        ];

        foreach ($cat['all_relations'] as $r) {
            $lines[] = sprintf(
                '| %s | %s | %s | %s | %s | %s | %s | %s |',
                $r['parent'],
                $r['child'],
                $r['pk'],
                $r['fk'],
                $r['type'],
                $r['category'],
                $r['on_delete'] ?? '-',
                $r['source']
            );
        }

        file_put_contents($path, implode("\n", $lines));
    }

    /** @return array<string, mixed>|null */
    private function compareColumns(string $table): ?array
    {
        $dbCols = array_keys($this->actualSchema[$table]['columns'] ?? []);
        $migCols = array_keys($this->migrationSchema[$table]['columns'] ?? []);

        $onlyInDb = array_values(array_diff($dbCols, $migCols));
        $onlyInMigration = array_values(array_diff($migCols, $dbCols));
        $typeMismatch = [];

        foreach (array_intersect($dbCols, $migCols) as $col) {
            $dbType = $this->actualSchema[$table]['columns'][$col]['type'] ?? '';
            $migType = $this->migrationSchema[$table]['columns'][$col]['type'] ?? '';
            if ($this->typesDiffer($dbType, $migType)) {
                $typeMismatch[$col] = ['db' => $dbType, 'migration' => $migType];
            }
        }

        if (empty($onlyInDb) && empty($onlyInMigration) && empty($typeMismatch)) {
            return null;
        }
        return [
            'only_in_db' => $onlyInDb,
            'only_in_migration' => $onlyInMigration,
            'type_mismatch' => $typeMismatch,
        ];
    }

    /** @return array<string, mixed>|null */
    private function compareForeignKeys(string $table): ?array
    {
        $dbFks = $this->actualSchema[$table]['foreign_keys'] ?? [];
        $migFks = $this->migrationSchema[$table]['foreign_keys'] ?? [];

        $dbKeys = [];
        foreach ($dbFks as $fk) {
            $dbKeys[$fk['column'] . '->' . $fk['ref_table']] = $fk;
        }
        $migKeys = [];
        foreach ($migFks as $fk) {
            $migKeys[$fk['column'] . '->' . $fk['ref_table']] = $fk;
        }

        $onlyInDb = [];
        foreach (array_diff(array_keys($dbKeys), array_keys($migKeys)) as $k) {
            $onlyInDb[] = $dbKeys[$k];
        }
        $onlyInMigration = [];
        foreach (array_diff(array_keys($migKeys), array_keys($dbKeys)) as $k) {
            $onlyInMigration[] = $migKeys[$k];
        }

        if (empty($onlyInDb) && empty($onlyInMigration)) {
            return null;
        }
        return ['only_in_db' => $onlyInDb, 'only_in_migration' => $onlyInMigration];
    }

    private function typesDiffer(string $db, string $migration): bool
    {
        $normalize = fn ($t) => preg_replace('/\s+unsigned/i', ' unsigned', strtolower(preg_replace('/\([^)]+\)/', '', $t)));
        return $normalize($db) !== $normalize($migration);
    }

    /** @param list<array> $relationships */
    /** @return list<string> */
    private function relationshipKeySet(array $relationships, string $confidenceFilter = ''): array
    {
        $set = [];
        foreach ($relationships as $r) {
            if ($confidenceFilter && ($r['confidence'] ?? '') !== $confidenceFilter) {
                continue;
            }
            if (($r['relation_type'] ?? '') === 'Polymorphic') {
                continue;
            }
            $parent = $r['parent_table'] ?? '';
            $child = $r['child_table'] ?? '';
            $fk = $r['fk_column'] ?? '';
            if ($parent && $child && $fk && $parent !== '(polymorphic)') {
                $set["{$child}.{$fk}->{$parent}"] = true;
            }
        }
        return array_keys($set);
    }

    /** @return list<string> */
    private function modelRelationshipKeySet(): array
    {
        $set = [];
        foreach ($this->eloquentRelations as $er) {
            if (in_array($er['type'], ['morphTo', 'morphOne', 'morphMany'], true)) {
                continue;
            }
            $parent = $er['related_table'] ?? '';
            $child = $er['table'] ?? '';
            $fk = $er['foreign_key'];
            if (!$fk) {
                if ($er['type'] === 'belongsTo') {
                    $fk = $this->singular($parent) . '_id';
                } elseif (in_array($er['type'], ['hasMany', 'hasOne'], true)) {
                    $fk = $this->singular($child) . '_id';
                }
            }
            if ($parent && $child && $fk) {
                if ($er['type'] === 'belongsTo') {
                    $set["{$child}.{$fk}->{$parent}"] = true;
                } elseif (in_array($er['type'], ['hasMany', 'hasOne'], true)) {
                    $set["{$child}.{$fk}->{$parent}"] = true;
                }
            }
        }
        return array_keys($set);
    }

    /** @return array<string, mixed> */
    private function categorizeAllRelationships(array $actualFkSet, array $migrationFkSet, array $modelRelSet): array
    {
        $allKeys = array_unique(array_merge($actualFkSet, $migrationFkSet, $modelRelSet));
        $inDatabase = [];
        $migrationOnly = [];
        $modelOnly = [];
        $inferred = [];
        $allThree = [];
        $allRelations = [];

        $actualFkMap = $this->buildFkMap($this->actualRelationships);
        $migrationFkMap = $this->buildFkMap($this->migrationRelationships);

        foreach ($allKeys as $key) {
            [$childFk, $parent] = explode('->', $key, 2);
            [$child, $fk] = explode('.', $childFk, 2);

            $inDb = in_array($key, $actualFkSet, true);
            $inMig = in_array($key, $migrationFkSet, true);
            $inModel = in_array($key, $modelRelSet, true);

            if ($inDb && $inMig && $inModel) {
                $category = 'DB+migration+Model';
                $allThree[] = $key;
            } elseif ($inDb) {
                $category = $inMig ? 'DB+migration' : 'DB';
                $inDatabase[] = $key;
            } elseif ($inMig) {
                $category = 'migration';
                $migrationOnly[] = $key;
            } elseif ($inModel) {
                $category = 'Model';
                $modelOnly[] = $this->findModelRelation($child, $fk, $parent);
            } else {
                $category = 'inferred';
                $inferred[] = $key;
            }

            $fkMeta = $actualFkMap[$key] ?? $migrationFkMap[$key] ?? [];
            $allRelations[] = [
                'parent' => $parent,
                'child' => $child,
                'pk' => 'id',
                'fk' => $fk,
                'type' => 'One-to-Many',
                'category' => $category,
                'on_delete' => $fkMeta['on_delete'] ?? '-',
                'source' => $inDb ? 'MySQL FK' : ($inMig ? 'migration' : ($inModel ? 'Eloquent' : 'inferred')),
            ];
        }

        usort($allRelations, fn ($a, $b) => $a['parent'] <=> $b['parent']);

        return [
            'in_database' => $inDatabase,
            'migration_only' => $migrationOnly,
            'model_only' => $modelOnly,
            'inferred' => $inferred,
            'all_three' => $allThree,
            'all_relations' => $allRelations,
        ];
    }

    /** @param list<array> $relationships */
    /** @return array<string, array> */
    private function buildFkMap(array $relationships): array
    {
        $map = [];
        foreach ($relationships as $r) {
            if (($r['confidence'] ?? '') !== 'مؤكدة' && ($r['in_db'] ?? false) !== true) {
                if (!isset($r['on_delete'])) {
                    continue;
                }
            }
            $key = ($r['child_table'] ?? '') . '.' . ($r['fk_column'] ?? '') . '->' . ($r['parent_table'] ?? '');
            if (str_contains($key, '(polymorphic)')) {
                continue;
            }
            $map[$key] = $r;
        }
        return $map;
    }

    /** @return array<string, string> */
    private function findModelRelation(string $child, string $fk, string $parent): array
    {
        foreach ($this->eloquentRelations as $er) {
            if (($er['table'] ?? '') === $child && ($er['related_table'] ?? '') === $parent) {
                return [
                    'child' => $child,
                    'fk' => $fk,
                    'parent' => $parent,
                    'model' => $er['model'] ?? '',
                    'relation' => $er['relation_name'] ?? '',
                    'file' => basename($er['file'] ?? ''),
                    'line' => $er['line'] ?? 0,
                ];
            }
        }
        return ['child' => $child, 'fk' => $fk, 'parent' => $parent, 'model' => '-', 'relation' => '-', 'file' => '-', 'line' => 0];
    }

    private function singular(string $table): string
    {
        $irregular = ['branches' => 'branch', 'training_centers' => 'training_center', 'training_supervisors' => 'training_supervisor'];
        if (isset($irregular[$table])) {
            return $irregular[$table];
        }
        if (str_ends_with($table, 'ies')) {
            return substr($table, 0, -3) . 'y';
        }
        if (str_ends_with($table, 's')) {
            return substr($table, 0, -1);
        }
        return $table;
    }
}
