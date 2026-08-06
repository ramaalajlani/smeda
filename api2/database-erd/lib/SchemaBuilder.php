<?php

declare(strict_types=1);

class SchemaBuilder
{
    /** @var array<string, array> */
    private array $schema;

    /** @var list<array> */
    private array $eloquentRelations;

    /** @var array */
    private array $sqlComparison;

    /** @var list<string> */
    private array $migrationFiles;

    /** @var list<string> */
    private array $modelFiles;

    /** @var list<string> */
    private array $sqlFiles;

    /** @var list<array> */
    private array $relationships = [];

    /** @var list<array> */
    private array $auditIssues = [];

    /** @var list<array> */
    private array $unresolved = [];

    public function __construct(
        array $schema,
        array $eloquentRelations,
        array $sqlComparison,
        array $migrationFiles,
        array $modelFiles,
        array $sqlFiles
    ) {
        $this->schema = $schema;
        $this->eloquentRelations = $eloquentRelations;
        $this->sqlComparison = $sqlComparison;
        $this->migrationFiles = $migrationFiles;
        $this->modelFiles = $modelFiles;
        $this->sqlFiles = array_filter($sqlFiles, 'is_file');
    }

    public function build(): array
    {
        ksort($this->schema);

        // Index confirmed FKs
        $confirmedKeys = [];
        foreach ($this->schema as $tableName => $table) {
            foreach ($table['foreign_keys'] as $fk) {
                $rel = $this->buildRelationFromFk($tableName, $fk);
                $rel['confidence'] = 'مؤكدة';
                $rel['source'] = 'migration FK';
                $rel['evidence'] = ($table['source'] ?? '') . ' | ' . $fk['column'];
                $this->relationships[] = $rel;
                $confirmedKeys[$tableName . '.' . $fk['column']] = true;
            }
        }

        // Eloquent relations
        foreach ($this->eloquentRelations as $er) {
            $this->processEloquentRelation($er, $confirmedKeys);
        }

        // Probable *_id columns without FK
        foreach ($this->schema as $tableName => $table) {
            foreach ($table['columns'] as $colName => $col) {
                if (!str_ends_with($colName, '_id') || $colName === 'id') {
                    continue;
                }
                if ($this->isNonForeignIdColumn($colName)) {
                    continue;
                }
                $key = $tableName . '.' . $colName;
                if (isset($confirmedKeys[$key])) {
                    continue;
                }
                // Skip polymorphic type companion
                if (isset($table['columns'][str_replace('_id', '_type', $colName)])) {
                    continue;
                }
                $refTable = $this->guessRefTable($colName);
                if (!$refTable || !isset($this->schema[$refTable])) {
                    $this->unresolved[] = [
                        'child_table' => $tableName,
                        'column' => $colName,
                        'guessed_parent' => $refTable,
                        'reason' => 'عمود *_id بدون FK ولم يُؤكد جدول الهدف',
                        'files' => [$table['source'] ?? 'migration'],
                    ];
                    continue;
                }
                $exists = false;
                foreach ($this->relationships as $r) {
                    if ($r['child_table'] === $tableName && $r['fk_column'] === $colName) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $this->relationships[] = [
                        'parent_table' => $refTable,
                        'child_table' => $tableName,
                        'pk_column' => 'id',
                        'fk_column' => $colName,
                        'relation_type' => 'One-to-Many',
                        'pivot' => '',
                        'confidence' => 'محتملة',
                        'source' => 'اسم الحقل *_id',
                        'evidence' => ($table['source'] ?? '') . ' | ' . $colName,
                        'on_delete' => '',
                        'on_update' => '',
                        'in_db' => false,
                        'notes' => 'FK غير معرّف في migrations',
                    ];
                }
            }
        }

        // Polymorphic detection
        foreach ($this->schema as $tableName => $table) {
            foreach ($table['columns'] as $colName => $col) {
                if (str_ends_with($colName, '_type')) {
                    $idCol = str_replace('_type', '_id', $colName);
                    if (isset($table['columns'][$idCol])) {
                        $morphPrefix = str_replace('_type', '', $colName);
                        $exists = false;
                        foreach ($this->relationships as $r) {
                            if ($r['child_table'] === $tableName && ($r['relation_type'] ?? '') === 'Polymorphic') {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $this->relationships[] = [
                                'parent_table' => '(polymorphic)',
                                'child_table' => $tableName,
                                'pk_column' => $idCol,
                                'fk_column' => $colName . '+' . $idCol,
                                'relation_type' => 'Polymorphic',
                                'pivot' => '',
                                'confidence' => 'قوية',
                                'source' => 'morphs/nullableMorphs',
                                'evidence' => $table['source'] ?? '',
                                'on_delete' => '',
                                'on_update' => '',
                                'in_db' => true,
                                'notes' => "Morph prefix: {$morphPrefix}",
                            ];
                        }
                    }
                }
            }
        }

        $this->runAudit();

        $confirmed = count(array_filter($this->relationships, fn ($r) => $r['confidence'] === 'مؤكدة'));
        $strong = count(array_filter($this->relationships, fn ($r) => $r['confidence'] === 'قوية'));
        $probable = count(array_filter($this->relationships, fn ($r) => $r['confidence'] === 'محتملة'));

        return [
            'schema' => $this->schema,
            'relationships' => $this->relationships,
            'audit_issues' => $this->auditIssues,
            'unresolved' => $this->unresolved,
            'sql_comparison' => $this->sqlComparison,
            'stats' => [
                'db_files' => count($this->migrationFiles) + count($this->modelFiles) + count($this->sqlFiles),
                'sql_files' => count($this->sqlFiles),
                'tables' => count($this->schema),
                'confirmed_relations' => $confirmed,
                'strong_relations' => $strong,
                'probable_relations' => $probable,
                'unresolved_relations' => count($this->unresolved),
            ],
        ];
    }

    /** @param array<string, bool> $confirmedKeys */
    private function processEloquentRelation(array $er, array &$confirmedKeys): void
    {
        $childTable = $er['table'];
        $type = $this->mapEloquentType($er['type']);

        if ($er['type'] === 'morphTo') {
            return; // handled separately
        }

        if ($er['type'] === 'belongsToMany') {
            $pivot = $er['pivot_table'] ?? $this->guessPivotTable($childTable, $er['related_table'] ?? '');
            $parentTable = $er['related_table'] ?? '';
            if ($parentTable && isset($this->schema[$pivot])) {
                $this->addStrongRelation($parentTable, $pivot, 'id', $this->singular($parentTable) . '_id', 'Many-to-Many', $pivot, $er);
            }
            return;
        }

        if ($er['is_self'] ?? false) {
            $fk = $er['foreign_key'] ?? 'parent_id';
            $key = $childTable . '.' . $fk;
            if (!isset($confirmedKeys[$key])) {
                $this->addStrongRelation($childTable, $childTable, 'id', $fk, 'Self-referencing One-to-Many', '', $er);
            }
            return;
        }

        $parentTable = $er['related_table'] ?? '';
        if (!$parentTable || !isset($this->schema[$parentTable])) {
            if ($parentTable) {
                $this->unresolved[] = [
                    'child_table' => $childTable,
                    'column' => $er['foreign_key'] ?? $er['relation_name'] . '_id',
                    'guessed_parent' => $parentTable,
                    'reason' => 'Model يشير لجدول غير موجود في migrations',
                    'files' => [$er['file'] . ':' . $er['line']],
                ];
            }
            return;
        }

        $fk = $er['foreign_key'];
        if (!$fk) {
            if ($er['type'] === 'belongsTo') {
                $fk = $this->singular($parentTable) . '_id';
            } elseif (in_array($er['type'], ['hasMany', 'hasOne'], true)) {
                $fk = $this->singular($childTable) . '_id';
                // swap: parent has many child, FK on child
                $this->addStrongRelation($parentTable, $childTable, 'id', $fk, $type, '', $er);
                return;
            }
        }

        if ($fk) {
            $key = ($er['type'] === 'belongsTo' ? $childTable : $parentTable) . '.' . $fk;
            if (!isset($confirmedKeys[$key])) {
                if ($er['type'] === 'belongsTo') {
                    $this->addStrongRelation($parentTable, $childTable, $er['owner_key'] ?? 'id', $fk, $type, '', $er);
                } else {
                    $this->addStrongRelation($parentTable, $childTable, 'id', $fk, $type, '', $er);
                }
            }
        }
    }

    private function addStrongRelation(string $parent, string $child, string $pk, string $fk, string $type, string $pivot, array $er): void
    {
        foreach ($this->relationships as $r) {
            if ($r['parent_table'] === $parent && $r['child_table'] === $child && $r['fk_column'] === $fk) {
                return;
            }
        }
        $this->relationships[] = [
            'parent_table' => $parent,
            'child_table' => $child,
            'pk_column' => $pk,
            'fk_column' => $fk,
            'relation_type' => $type,
            'pivot' => $pivot,
            'confidence' => 'قوية',
            'source' => 'Eloquent Model',
            'evidence' => $er['file'] . ':' . $er['line'] . ' (' . $er['model'] . '::' . $er['relation_name'] . ')',
            'on_delete' => '',
            'on_update' => '',
            'in_db' => false,
            'notes' => $er['type'],
        ];
    }

    /** @param array<string, mixed> $fk */
    private function buildRelationFromFk(string $childTable, array $fk): array
    {
        return [
            'parent_table' => $fk['ref_table'],
            'child_table' => $childTable,
            'pk_column' => $fk['ref_column'],
            'fk_column' => $fk['column'],
            'relation_type' => 'One-to-Many',
            'pivot' => '',
            'on_delete' => $fk['on_delete'],
            'on_update' => $fk['on_update'],
            'in_db' => true,
            'notes' => $fk['name'] ?? '',
        ];
    }

    private function mapEloquentType(string $type): string
    {
        return match ($type) {
            'belongsTo' => 'One-to-Many (inverse)',
            'hasMany' => 'One-to-Many',
            'hasOne' => 'One-to-One',
            'belongsToMany' => 'Many-to-Many',
            'morphOne' => 'Polymorphic One-to-One',
            'morphMany' => 'Polymorphic One-to-Many',
            default => $type,
        };
    }

    private function guessRefTable(string $column): ?string
    {
        if (!str_ends_with($column, '_id')) {
            return null;
        }
        if ($this->isNonForeignIdColumn($column)) {
            return null;
        }

        $known = [
            'submitted_by_user_id' => 'users',
            'reviewed_by_user_id' => 'users',
            'approved_by_user_id' => 'users',
            'created_by_user_id' => 'users',
            'manager_user_id' => 'users',
            'parent_user_id' => 'users',
            'approved_trainee_id' => 'trainees',
            'approved_trainer_id' => 'trainers',
            'approved_training_center_id' => 'training_centers',
            'supervisor_id' => 'training_supervisors',
            'program_id' => 'training_programs',
            'service_reference_id' => null,
            'source_record_id' => null,
            'old_id' => null,
        ];
        if (array_key_exists($column, $known)) {
            return $known[$column];
        }

        $base = substr($column, 0, -3);
        $candidates = [
            $this->pluralize($base),
            $base . 's',
            $base . 'es',
            (str_ends_with($base, 'y') ? substr($base, 0, -1) . 'ies' : null),
            $base,
        ];
        foreach ($candidates as $c) {
            if ($c && isset($this->schema[$c])) {
                return $c;
            }
        }
        return $this->pluralize($base);
    }

    private function isNonForeignIdColumn(string $column): bool
    {
        $nonFk = [
            'national_id', 'guardian_national_id', 'old_id', 'new_id', 'source_record_id',
            'service_reference_id', 'tokenable_id', 'team_id',
        ];
        if (in_array($column, $nonFk, true)) {
            return true;
        }
        // morph companion handled separately
        return false;
    }

    private function pluralize(string $base): string
    {
        $irregular = ['branch' => 'branches', 'person' => 'people', 'child' => 'children'];
        if (isset($irregular[$base])) {
            return $irregular[$base];
        }
        if (str_ends_with($base, 'y') && !preg_match('/[aeiou]y$/', $base)) {
            return substr($base, 0, -1) . 'ies';
        }
        if (preg_match('/(?:s|x|z|ch|sh)$/', $base)) {
            return $base . 'es';
        }
        if (str_ends_with($base, 's')) {
            return $base;
        }
        return $base . 's';
    }

    private function guessPivotTable(string $a, string $b): string
    {
        $sa = $this->singular($a);
        $sb = $this->singular($b);
        $candidates = [
            $sa . '_' . $b,
            $b . '_' . $sa,
            $sa . '_' . $sb,
        ];
        foreach ($candidates as $c) {
            if (isset($this->schema[$c])) {
                return $c;
            }
        }
        return $sa . '_' . $b;
    }

    private function singular(string $table): string
    {
        $irregular = [
            'branches' => 'branch',
            'people' => 'person',
            'children' => 'child',
            'training_supervisors' => 'training_supervisor',
            'training_centers' => 'training_center',
            'training_courses' => 'training_course',
            'training_programs' => 'training_program',
            'training_kits' => 'training_kit',
        ];
        if (isset($irregular[$table])) {
            return $irregular[$table];
        }
        if (str_ends_with($table, 'ies')) {
            return substr($table, 0, -3) . 'y';
        }
        if (str_ends_with($table, 'ses')) {
            return substr($table, 0, -2);
        }
        if (str_ends_with($table, 's')) {
            return substr($table, 0, -1);
        }
        return $table;
    }

    private function runAudit(): void
    {
        // *_id without FK
        foreach ($this->schema as $tableName => $table) {
            $fkCols = array_column($table['foreign_keys'], 'column');
            foreach ($table['columns'] as $colName => $col) {
                if (str_ends_with($colName, '_id') && $colName !== 'id' && !in_array($colName, $fkCols, true)) {
                    if (isset($table['columns'][str_replace('_id', '_type', $colName)])) {
                        continue;
                    }
                    if ($this->isNonForeignIdColumn($colName)) {
                        continue;
                    }
                    $this->auditIssues[] = [
                        'severity' => 'متوسطة',
                        'category' => '*_id بدون Foreign Key',
                        'table' => $tableName,
                        'column' => $colName,
                        'file' => $table['source'] ?? '',
                        'recommendation' => "إضافة foreignId('{$colName}')->constrained() في migration",
                    ];
                }
            }

            if (empty($table['primary_key']) && !empty($table['columns'])) {
                $this->auditIssues[] = [
                    'severity' => 'عالية',
                    'category' => 'جدول بلا Primary Key',
                    'table' => $tableName,
                    'column' => '',
                    'file' => $table['source'] ?? '',
                    'recommendation' => 'تعريف primary key صريح',
                ];
            }
        }

        // FK to non-existent tables
        foreach ($this->schema as $tableName => $table) {
            foreach ($table['foreign_keys'] as $fk) {
                if (!isset($this->schema[$fk['ref_table']])) {
                    $this->auditIssues[] = [
                        'severity' => 'حرجة',
                        'category' => 'FK يشير لجدول غير موجود',
                        'table' => $tableName,
                        'column' => $fk['column'],
                        'file' => $table['source'] ?? '',
                        'recommendation' => "FK {$fk['column']} -> {$fk['ref_table']} غير موجود في migrations",
                    ];
                }
            }
        }

        // Cascade delete risks
        foreach ($this->schema as $tableName => $table) {
            foreach ($table['foreign_keys'] as $fk) {
                if ($fk['on_delete'] === 'cascade' && in_array($tableName, ['users', 'training_centers', 'training_courses'], true)) {
                    $this->auditIssues[] = [
                        'severity' => 'عالية',
                        'category' => 'Cascade Delete خطير',
                        'table' => $tableName,
                        'column' => $fk['column'],
                        'file' => $table['source'] ?? '',
                        'recommendation' => 'مراجعة cascadeOnDelete — قد يحذف بيانات تابعة بشكل غير قابل للاسترجاع',
                    ];
                }
            }
        }

        // SQL vs migrations conflicts
        foreach ($this->sqlComparison['only_in_migrations'] ?? [] as $t) {
            $this->auditIssues[] = [
                'severity' => 'منخفضة',
                'category' => 'جدول في migrations غير موجود في SQL dump',
                'table' => $t,
                'column' => '',
                'file' => 'SQL dump: ' . ($this->sqlComparison['newest_dump'] ?? ''),
                'recommendation' => 'الـ dump قديم — migrations تمثل البنية الحالية',
            ];
        }

        foreach ($this->sqlComparison['only_in_dumps'] ?? [] as $t) {
            if (in_array($t, ['migrations', 'telescope_entries'], true)) {
                continue;
            }
            $this->auditIssues[] = [
                'severity' => 'متوسطة',
                'category' => 'جدول في SQL dump غير موجود في migrations',
                'table' => $t,
                'column' => '',
                'file' => 'SQL dump: ' . ($this->sqlComparison['newest_dump'] ?? ''),
                'recommendation' => 'جدول legacy أو تم حذف migration — مراجعة يدوية',
            ];
        }
    }
}
