<?php

declare(strict_types=1);

class ModelParser
{
    /** @var list<array> */
    private array $relations = [];

    /** @var array<string, string> ClassName => table */
    private array $modelTables = [];

    /** @var list<string> */
    private array $modelFiles = [];

    /** @param list<string> $files */
    public function parseAll(array $files): void
    {
        $this->modelFiles = $files;
        foreach ($files as $file) {
            $this->registerModel($file);
        }
        foreach ($files as $file) {
            $this->parseRelations($file);
        }
    }

    public function parseFile(string $path): void
    {
        $this->registerModel($path);
        $this->parseRelations($path);
    }

    /** @return list<array> */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /** @return array<string, string> */
    public function getModelTables(): array
    {
        return $this->modelTables;
    }

    private function registerModel(string $path): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }
        $className = basename($path, '.php');
        $this->modelTables[$className] = $this->inferTable($content, $className);
    }

    private function parseRelations(string $path): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }

        $className = basename($path, '.php');
        $table = $this->modelTables[$className] ?? $this->inferTable($content, $className);

        $methods = [
            'belongsTo' => 'belongsTo',
            'hasMany' => 'hasMany',
            'hasOne' => 'hasOne',
            'belongsToMany' => 'belongsToMany',
            'morphTo' => 'morphTo',
            'morphOne' => 'morphOne',
            'morphMany' => 'morphMany',
        ];

        foreach ($methods as $method => $type) {
            $pattern = '/function\s+(\w+)\s*\([^)]*\)\s*(?::\s*[^{]+)?\{\s*return\s+\$this->' . $method . '\s*\(/s';
            if (!preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            foreach ($matches[1] as $i => $methodMatch) {
                $relationName = $methodMatch[0];
                $pos = (int) $matches[0][$i][1];
                $callBlock = $this->extractCallBlock($content, $pos);
                if ($callBlock) {
                    $this->parseRelationCall($className, $table, $relationName, $type, $callBlock, $path);
                }
            }
        }
    }

    private function inferTable(string $content, string $className): string
    {
        if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
            return $m[1];
        }
        return $this->classToTable($className);
    }

    private function classToTable(string $class): string
    {
        if (isset($this->modelTables[$class])) {
            return $this->modelTables[$class];
        }
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class) ?? $class);
        return $this->pluralizeSnake($snake);
    }

    private function pluralizeSnake(string $snake): string
    {
        $parts = explode('_', $snake);
        $last = array_pop($parts);
        $irregular = ['branch' => 'branches', 'person' => 'people', 'child' => 'children'];
        if (isset($irregular[$last])) {
            $last = $irregular[$last];
        } elseif (str_ends_with($last, 'y')) {
            $last = substr($last, 0, -1) . 'ies';
        } elseif (str_ends_with($last, 's')) {
            // keep
        } else {
            $last .= 's';
        }
        $parts[] = $last;
        return implode('_', $parts);
    }

    private function resolveRelatedTable(string $args): ?string
    {
        if (preg_match('/self::class/', $args)) {
            return null; // handled via caller table
        }
        if (preg_match('/([\\\\\w]+)::class/', $args, $m)) {
            $relatedModel = basename(str_replace('\\', '/', str_replace('\\\\', '\\', $m[1])));
            return $this->modelTables[$relatedModel] ?? $this->classToTable($relatedModel);
        }
        return null;
    }

    private function extractCallBlock(string $content, int $startPos): ?string
    {
        $sub = substr($content, $startPos);
        if (!preg_match('/return\s+\$this->\w+\s*\(/', $sub, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $openPos = (int) $m[0][1] + strlen($m[0][0]) - 1;
        $depth = 0;
        $len = strlen($sub);
        for ($i = $openPos; $i < $len; $i++) {
            if ($sub[$i] === '(') {
                $depth++;
            } elseif ($sub[$i] === ')') {
                $depth--;
                if ($depth === 0) {
                    return substr($sub, $openPos + 1, $i - $openPos - 1);
                }
            }
        }
        return null;
    }

    private function parseRelationCall(string $modelClass, string $table, string $relationName, string $type, string $args, string $path): void
    {
        $isSelf = str_contains($args, 'self::class');
        $relatedTable = $isSelf ? $table : $this->resolveRelatedTable($args);
        $foreignKey = null;
        $ownerKey = 'id';
        $pivotTable = null;

        if (preg_match_all("/,\s*['\"](\w+)['\"]/", $args, $allKeys)) {
            $foreignKey = $allKeys[1][0] ?? null;
            $ownerKey = $allKeys[1][1] ?? 'id';
        }

        if ($type === 'belongsToMany') {
            if (preg_match("/['\"](\w+)['\"]\s*,\s*['\"](\w+)['\"]\s*,\s*['\"](\w+)['\"]/", $args, $m)) {
                $pivotTable = $m[1];
            }
        }

        $this->relations[] = [
            'model' => $modelClass,
            'table' => $table,
            'relation_name' => $relationName,
            'type' => $type,
            'related_model' => $isSelf ? $modelClass : (preg_match('/(\w+)::class/', $args, $rm) ? $rm[1] : null),
            'related_table' => $relatedTable,
            'foreign_key' => $foreignKey,
            'owner_key' => $ownerKey,
            'pivot_table' => $pivotTable,
            'is_self' => $isSelf,
            'file' => str_replace('\\', '/', $path),
            'line' => $this->estimateLine($path, $relationName),
        ];
    }

    private function estimateLine(string $path, string $method): int
    {
        $lines = file($path) ?: [];
        foreach ($lines as $i => $line) {
            if (str_contains($line, "function {$method}") || str_contains($line, "function {$method}(")) {
                return $i + 1;
            }
        }
        return 0;
    }
}
