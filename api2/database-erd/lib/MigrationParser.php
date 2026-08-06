<?php

declare(strict_types=1);

class MigrationParser
{
    /** @var array<string, array> */
    private array $tables = [];

    /** @var array<string, string> Spatie permission table name mapping */
    private array $permissionTables = [
        'permissions' => 'permissions',
        'roles' => 'roles',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ];

    /** @var array<string, string> */
    private array $columnNameMap = [
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
    ];

    public function parseFile(string $path): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }

        // Only parse up() method body
        if (!preg_match('/function\s+up\s*\(\s*\)\s*(?::\s*\w+\s*)?\{(.*)\}\s*(?:public\s+function\s+down|private\s+function|\}\s*;)/s', $content, $upMatch)) {
            if (!preg_match('/function\s+up\s*\(\s*\)\s*\{(.*)\}\s*public\s+function\s+down/s', $content, $upMatch)) {
                return;
            }
        }
        $upBody = $upMatch[1];

        // Schema::create with literal table name
        $this->extractSchemaBlocks($upBody, 'create', $path);

        // Spatie permission tables ($tableNames['...'])
        if (str_contains(basename($path), 'permission_tables')) {
            foreach ($this->permissionTables as $key => $tableName) {
                $pattern = '/Schema::create\s*\(\s*\$tableNames\[[\'"]' . preg_quote($key, '/') . '[\'"]\]\s*,\s*(?:static\s+)?function\s*\(\s*Blueprint\s+\$table\s*\)[^{]*\{/s';
                if (preg_match($pattern, $upBody, $m, PREG_OFFSET_CAPTURE)) {
                    $start = (int) $m[0][1] + strlen($m[0][0]);
                    $blueprintBody = $this->extractBalancedBlock($upBody, $start - 1);
                    if ($blueprintBody !== null) {
                        $this->initTable($tableName, basename($path));
                        $this->applyBlueprintLines($tableName, $this->parseBlueprintBody($blueprintBody), basename($path));
                    }
                }
            }
        }

        // Schema::table alterations
        $this->extractSchemaBlocks($upBody, 'table', $path);

        // foreach tables pattern (branch scope migration)
        if (preg_match_all('/foreach\s*\(\s*\$this->tables\s+as\s+\$tableName\s*\)\s*\{([\s\S]*?)\}/', $upBody, $foreachMatches)) {
            foreach ($foreachMatches[1] as $foreachBody) {
                if (preg_match_all('/Schema::table\s*\(\s*\$tableName\s*,/', $foreachBody)) {
                    // Generic alter - columns will be added to known registration tables
                    $regTables = [
                        'training_center_registration_requests',
                        'trainer_registration_requests',
                        'trainee_registration_requests',
                        'course_registration_requests',
                    ];
                    foreach ($regTables as $t) {
                        $this->applyBlueprintLines($t, $this->extractBlueprintFromClosure($foreachBody), basename($path));
                    }
                }
            }
        }
    }

    /** @return array<string, array> */
    public function getSchema(): array
    {
        return $this->tables;
    }

    private function extractSchemaBlocks(string $body, string $mode, string $path): void
    {
        $pattern = $mode === 'create'
            ? '/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(?:static\s+)?function\s*\(\s*Blueprint\s+\$table\s*\)[^{]*\{/s'
            : '/Schema::table\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(?:static\s+)?function\s*\(\s*Blueprint\s+\$table\s*\)[^{]*\{/s';

        $offset = 0;
        while (preg_match($pattern, $body, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $tableName = $m[1][0];
            $start = (int) $m[0][1] + strlen($m[0][0]);
            $blueprintBody = $this->extractBalancedBlock($body, $start - 1);
            if ($blueprintBody !== null) {
                $lines = $this->parseBlueprintBody($blueprintBody);
                if ($mode === 'create') {
                    $this->initTable($tableName, basename($path));
                    $this->applyBlueprintLines($tableName, $lines, basename($path));
                } else {
                    if (!isset($this->tables[$tableName])) {
                        $this->initTable($tableName, basename($path) . ' (alter only)');
                    }
                    $this->applyBlueprintLines($tableName, $lines, basename($path));
                }
            }
            $offset = $start + strlen($blueprintBody ?? '');
        }
    }

    private function extractSchemaBlockForTable(string $body, string $mode, string $tableName, string $path, bool $isCreate): void
    {
        $search = "Schema::create(\$tableNames['" . array_search($tableName, $this->permissionTables, true) . "']";
        // fallback: search by resolved name in permission migration
        $patterns = [
            '/Schema::create\s*\(\s*\$tableNames\[[\'"]' . preg_quote(array_search($tableName, $this->permissionTables, true), '/') . '[\'"]\]\s*,\s*(?:static\s+)?function\s*\([^)]*\)\s*\{/s',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $m, PREG_OFFSET_CAPTURE)) {
                $start = (int) $m[0][1] + strlen($m[0][0]);
                $blueprintBody = $this->extractBalancedBlock($body, $start - 1);
                if ($blueprintBody !== null) {
                    $lines = $this->parseBlueprintBody($blueprintBody);
                    $this->initTable($tableName, basename($path));
                    $this->applyBlueprintLines($tableName, $lines, basename($path));
                }
                return;
            }
        }
    }

    private function extractBlueprintFromClosure(string $body): array
    {
        if (preg_match('/function\s*\([^)]*\)\s*\{([\s\S]*)\}/', $body, $m)) {
            return $this->parseBlueprintBody($m[1]);
        }
        return [];
    }

    private function extractBalancedBlock(string $content, int $openBracePos): ?string
    {
        $depth = 0;
        $len = strlen($content);
        $start = null;
        for ($i = $openBracePos; $i < $len; $i++) {
            $ch = $content[$i];
            if ($ch === '{') {
                if ($depth === 0) {
                    $start = $i + 1;
                }
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0 && $start !== null) {
                    return substr($content, $start, $i - $start);
                }
            }
        }
        return null;
    }

    /** @return list<array> */
    private function parseBlueprintBody(string $body): array
    {
        $lines = [];
        foreach (explode("\n", $body) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '//') || str_starts_with($line, '/*') || str_starts_with($line, '*')) {
                continue;
            }
            if (str_contains($line, '$table->')) {
                $lines[] = $line;
            }
        }
        return $lines;
    }

    private function initTable(string $name, string $source): void
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = [
                'name' => $name,
                'source' => $source,
                'description' => $this->inferTableDescription($name),
                'columns' => [],
                'primary_key' => [],
                'foreign_keys' => [],
                'indexes' => [],
                'unique_keys' => [],
                'soft_deletes' => false,
                'timestamps' => false,
            ];
        }
    }

    /** @param list<string> $lines */
    private function applyBlueprintLines(string $tableName, array $lines, string $source): void
    {
        $this->initTable($tableName, $source);
        $table = &$this->tables[$tableName];

        foreach ($lines as $line) {
            // Resolve config column names in permission migration
            foreach ($this->columnNameMap as $configKey => $colName) {
                $line = str_replace("\$columnNames['{$configKey}']", "'{$colName}'", $line);
                $line = str_replace('$pivotRole', "'role_id'", $line);
                $line = str_replace('$pivotPermission', "'permission_id'", $line);
            }

            if (preg_match('/\$table->id\s*\(\s*\)/', $line)) {
                $table['columns']['id'] = $this->col('bigint unsigned', false, null, true, true);
                $table['primary_key'] = ['id'];
                continue;
            }

            if (preg_match('/\$table->bigIncrements\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $table['columns'][$m[1]] = $this->col('bigint unsigned', false, null, true, true);
                $table['primary_key'] = [$m[1]];
                continue;
            }

            if (preg_match('/\$table->uuid\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $table['columns'][$m[1]] = $this->col('char(36)', false, null, str_contains($line, 'primary'), false);
                if (str_contains($line, 'primary')) {
                    $table['primary_key'] = [$m[1]];
                }
                continue;
            }

            if (preg_match('/\$table->foreignId\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $col = $m[1];
                $nullable = str_contains($line, '->nullable()');
                $table['columns'][$col] = $this->col('bigint unsigned', $nullable, null, false, false);
                $fk = $this->parseForeignKey($line, $col);
                if ($fk) {
                    $table['foreign_keys'][] = $fk;
                }
                continue;
            }

            if (preg_match('/\$table->unsignedBigInteger\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $nullable = str_contains($line, '->nullable()');
                $table['columns'][$m[1]] = $this->col('bigint unsigned', $nullable, null, false, false);
                $fk = $this->parseForeignKey($line, $m[1]);
                if ($fk) {
                    $table['foreign_keys'][] = $fk;
                }
                continue;
            }

            if (preg_match('/\$table->foreign\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $fk = $this->parseForeignKey($line, $m[1]);
                if ($fk) {
                    $table['foreign_keys'][] = $fk;
                }
                continue;
            }

            if (preg_match('/\$table->morphs\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $prefix = $m[1];
                $table['columns'][$prefix . '_type'] = $this->col('varchar(255)', false, null, false, false);
                $table['columns'][$prefix . '_id'] = $this->col('bigint unsigned', false, null, false, false);
                $table['indexes'][] = ['columns' => [$prefix . '_type', $prefix . '_id'], 'name' => "{$tableName}_{$prefix}_type_{$prefix}_id_index"];
                continue;
            }

            if (preg_match('/\$table->nullableMorphs\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $prefix = $m[1];
                $table['columns'][$prefix . '_type'] = $this->col('varchar(255)', true, null, false, false);
                $table['columns'][$prefix . '_id'] = $this->col('bigint unsigned', true, null, false, false);
                continue;
            }

            if (preg_match('/\$table->rememberToken\s*\(\s*\)/', $line)) {
                $table['columns']['remember_token'] = $this->col('varchar(100)', true, null, false, false);
                continue;
            }

            if (preg_match('/\$table->softDeletes\s*\(\s*\)/', $line)) {
                $table['columns']['deleted_at'] = $this->col('timestamp', true, null, false, false);
                $table['soft_deletes'] = true;
                continue;
            }

            if (preg_match('/\$table->timestamps\s*\(\s*\)/', $line)) {
                $table['columns']['created_at'] = $this->col('timestamp', true, null, false, false);
                $table['columns']['updated_at'] = $this->col('timestamp', true, null, false, false);
                $table['timestamps'] = true;
                continue;
            }

            if (preg_match('/\$table->timestamp\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $line, $m)) {
                $nullable = str_contains($line, '->nullable()');
                $table['columns'][$m[1]] = $this->col('timestamp', $nullable, null, false, false);
                continue;
            }

            // Typed columns
            $typePatterns = [
                'string' => '/\$table->string\s*\(\s*[\'"](\w+)[\'"](?:\s*,\s*(\d+))?\s*\)/',
                'text' => '/\$table->text\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'mediumText' => '/\$table->mediumText\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'longText' => '/\$table->longText\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'json' => '/\$table->json\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'jsonb' => '/\$table->jsonb\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'boolean' => '/\$table->boolean\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'integer' => '/\$table->integer\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'bigInteger' => '/\$table->bigInteger\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'tinyInteger' => '/\$table->tinyInteger\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'smallInteger' => '/\$table->smallInteger\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'float' => '/\$table->float\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'double' => '/\$table->double\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'decimal' => '/\$table->decimal\s*\(\s*[\'"](\w+)[\'"]\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/',
                'date' => '/\$table->date\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'dateTime' => '/\$table->dateTime\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'time' => '/\$table->time\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'year' => '/\$table->year\s*\(\s*[\'"](\w+)[\'"]\s*\)/',
                'char' => '/\$table->char\s*\(\s*[\'"](\w+)[\'"]\s*,\s*(\d+)\s*\)/',
            ];

            $matched = false;
            foreach ($typePatterns as $type => $pattern) {
                if (preg_match($pattern, $line, $m)) {
                    $colName = $m[1];
                    $sqlType = match ($type) {
                        'string' => 'varchar(' . ($m[2] ?? '255') . ')',
                        'text' => 'text',
                        'mediumText' => 'mediumtext',
                        'longText' => 'longtext',
                        'json' => 'json',
                        'jsonb' => 'jsonb',
                        'boolean' => 'boolean',
                        'integer' => 'int',
                        'bigInteger' => 'bigint',
                        'tinyInteger' => 'tinyint',
                        'smallInteger' => 'smallint',
                        'float' => 'float',
                        'double' => 'double',
                        'decimal' => 'decimal(' . $m[2] . ',' . $m[3] . ')',
                        'date' => 'date',
                        'dateTime' => 'datetime',
                        'time' => 'time',
                        'year' => 'year',
                        'char' => 'char(' . $m[2] . ')',
                        default => 'varchar(255)',
                    };
                    $nullable = str_contains($line, '->nullable()');
                    $default = null;
                    if (preg_match('/->default\s*\(\s*([^)]+)\s*\)/', $line, $dm)) {
                        $default = trim($dm[1], " '\"");
                    }
                    $table['columns'][$colName] = $this->col($sqlType, $nullable, $default, false, false);

                    if (str_contains($line, '->unique()')) {
                        $table['unique_keys'][] = ['columns' => [$colName], 'name' => "{$tableName}_{$colName}_unique"];
                    }
                    if (str_contains($line, '->primary()')) {
                        $table['primary_key'] = [$colName];
                        $table['columns'][$colName]['pk'] = true;
                    }
                    if (str_contains($line, '->index()')) {
                        $table['indexes'][] = ['columns' => [$colName], 'name' => "{$tableName}_{$colName}_index"];
                    }
                    $matched = true;
                    break;
                }
            }
            if ($matched) {
                continue;
            }

            if (preg_match('/\$table->enum\s*\(\s*[\'"](\w+)[\'"]\s*,\s*\[(.*?)\]\s*\)/', $line, $m)) {
                preg_match_all('/[\'"]([^\'"]+)[\'"]/', $m[2], $enumVals);
                $nullable = str_contains($line, '->nullable()');
                $default = null;
                if (preg_match('/->default\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $dm)) {
                    $default = $dm[1];
                }
                $table['columns'][$m[1]] = $this->col('enum', $nullable, $default, false, false, $enumVals[1] ?? []);
                continue;
            }

            if (preg_match('/\$table->index\s*\(\s*\[(.*?)\]/', $line, $m)) {
                preg_match_all('/[\'"](\w+)[\'"]/', $m[1], $cols);
                $name = null;
                if (preg_match('/,\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $nm)) {
                    $name = $nm[1];
                }
                $table['indexes'][] = ['columns' => $cols[1], 'name' => $name ?? implode('_', $cols[1]) . '_index'];
                continue;
            }

            if (preg_match('/\$table->unique\s*\(\s*\[(.*?)\]/', $line, $m)) {
                preg_match_all('/[\'"](\w+)[\'"]/', $m[1], $cols);
                $name = null;
                if (preg_match('/,\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $nm)) {
                    $name = $nm[1];
                }
                $table['unique_keys'][] = ['columns' => $cols[1], 'name' => $name ?? implode('_', $cols[1]) . '_unique'];
                continue;
            }

            if (preg_match('/\$table->primary\s*\(\s*\[(.*?)\]/', $line, $m)) {
                preg_match_all('/[\'"](\w+)[\'"]/', $m[1], $cols);
                $table['primary_key'] = $cols[1];
                continue;
            }
        }
    }

    /** @param list<string>|null $enumValues */
    private function col(string $type, bool $nullable, ?string $default, bool $pk, bool $ai, ?array $enumValues = null): array
    {
        return [
            'type' => $type,
            'nullable' => $nullable,
            'default' => $default,
            'pk' => $pk,
            'auto_increment' => $ai,
            'enum_values' => $enumValues,
        ];
    }

    /** @return array<string, mixed>|null */
    private function parseForeignKey(string $line, string $column): ?array
    {
        $refTable = null;
        $refColumn = 'id';
        $onDelete = 'restrict';
        $onUpdate = 'restrict';

        if (preg_match('/->constrained\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $m)) {
            $refTable = $m[1];
        } elseif (preg_match('/->constrained\s*\(\s*\)/', $line)) {
            $refTable = $this->guessTableFromColumn($column);
        } elseif (preg_match('/->references\s*\(\s*[\'"](\w+)[\'"]\s*\)->on\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $m)) {
            $refColumn = $m[1];
            $refTable = $m[2];
        } elseif (preg_match('/->on\s*\(\s*\$tableNames\[[\'"](\w+)[\'"]\]\s*\)/', $line, $m)) {
            $refTable = $this->permissionTables[$m[1]] ?? $m[1];
        } elseif (preg_match('/->on\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $m)) {
            $refTable = $m[1];
        }

        if (!$refTable) {
            return null;
        }

        if (str_contains($line, 'cascadeOnDelete') || str_contains($line, "onDelete('cascade')")) {
            $onDelete = 'cascade';
        } elseif (str_contains($line, 'nullOnDelete') || str_contains($line, "onDelete('set null')")) {
            $onDelete = 'set null';
        } elseif (str_contains($line, "onDelete('restrict')")) {
            $onDelete = 'restrict';
        }

        if (str_contains($line, 'cascadeOnUpdate') || str_contains($line, "onUpdate('cascade')")) {
            $onUpdate = 'cascade';
        }

        return [
            'column' => $column,
            'ref_table' => $refTable,
            'ref_column' => $refColumn,
            'on_delete' => $onDelete,
            'on_update' => $onUpdate,
            'name' => "{$column}_foreign",
            'source' => 'migration_fk',
        ];
    }

    private function guessTableFromColumn(string $column): string
    {
        if (str_ends_with($column, '_id')) {
            return $this->pluralize(substr($column, 0, -3));
        }
        return $column;
    }

    private function pluralize(string $base): string
    {
        $irregular = [
            'branch' => 'branches',
            'person' => 'people',
            'child' => 'children',
            'self' => 'users',
        ];
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

    private function inferTableDescription(string $name): string
    {
        $descriptions = [
            'users' => 'مستخدمي النظام (موظفون، مدربون، متدربون، مراكز تدريب)',
            'training_centers' => 'مراكز التدريب المعتمدة',
            'training_courses' => 'الدورات التدريبية',
            'training_programs' => 'البرامج التدريبية',
            'training_kits' => 'حقائب التدريب (Training Kits)',
            'trainers' => 'المدربون',
            'trainees' => 'المتدربون',
            'certificates' => 'الشهادات الصادرة',
            'permissions' => 'صلاحيات Spatie Permission',
            'roles' => 'أدوار Spatie Permission',
            'governorates' => 'المحافظات السورية',
            'branches' => 'فروع الهيئة',
            'consulting_requests' => 'طلبات الاستشارات',
            'consulting_offices' => 'مكاتب الاستشارات',
            'funding_applications' => 'طلبات التمويل',
            'incubators' => 'حاضنات الأعمال',
            'incubation_applications' => 'طلبات الاحتضان',
            'needs' => 'احتياجات/طلبات الدعم',
            'news' => 'الأخبار',
            'notifications' => 'إشعارات المستخدمين',
            'inbox_messages' => 'صندوق الوارد الداخلي',
            'audit_logs' => 'سجل التدقيق',
            'entrepreneur_profiles' => 'ملفات رواد الأعمال',
            'workforces' => 'القوى العاملة/الكادر',
            'job_postings' => 'إعلانات الوظائف',
            'sessions' => 'جلسات Laravel',
            'personal_access_tokens' => 'رموز Sanctum API',
        ];
        return $descriptions[$name] ?? 'جدول: ' . str_replace('_', ' ', $name);
    }
}
