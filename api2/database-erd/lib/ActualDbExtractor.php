<?php

declare(strict_types=1);

class ActualDbExtractor
{
    private PDO $pdo;
    private string $database;

    public function __construct(PDO $pdo, string $database)
    {
        $this->pdo = $pdo;
        $this->database = $database;
    }

    /** @return array<string, array> */
    public function extract(): array
    {
        $tables = $this->fetchTables();
        $schema = [];

        foreach ($tables as $tableName) {
            $schema[$tableName] = $this->extractTable($tableName);
        }

        ksort($schema);
        return $schema;
    }

    /** @return list<string> */
    private function fetchTables(): array
    {
        $stmt = $this->pdo->query('SHOW TABLES');
        $key = 'Tables_in_' . $this->database;
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables[] = $row[$key];
        }
        return $tables;
    }

    /** @return array<string, mixed> */
    private function extractTable(string $tableName): array
    {
        $columns = $this->fetchColumns($tableName);
        $indexes = $this->fetchIndexes($tableName);
        $foreignKeys = $this->fetchForeignKeys($tableName);
        $createSql = $this->fetchCreateTable($tableName);

        $primaryKey = [];
        $uniqueKeys = [];
        $indexList = [];

        foreach ($indexes as $idx) {
            $keyName = $idx['Key_name'];
            $colName = $idx['Column_name'];
            if ($keyName === 'PRIMARY') {
                if (!in_array($colName, $primaryKey, true)) {
                    $primaryKey[] = $colName;
                }
            } elseif ((int) $idx['Non_unique'] === 0) {
                $uniqueKeys[$keyName]['columns'][] = $colName;
            } else {
                $indexList[$keyName]['columns'][] = $colName;
            }
        }

        foreach ($columns as &$col) {
            $col['pk'] = in_array($col['name'], $primaryKey, true);
            $col['auto_increment'] = str_contains($col['extra'] ?? '', 'auto_increment');
        }
        unset($col);

        $colMap = [];
        foreach ($columns as $c) {
            $name = $c['name'];
            $colMap[$name] = [
                'type' => $this->normalizeType($c['column_type']),
                'nullable' => ($c['is_nullable'] ?? 'NO') === 'YES',
                'default' => $c['column_default'],
                'pk' => $c['pk'],
                'auto_increment' => $c['auto_increment'],
                'enum_values' => $this->parseEnum($c['column_type']),
            ];
        }

        $softDeletes = isset($colMap['deleted_at']);
        $timestamps = isset($colMap['created_at']) && isset($colMap['updated_at']);

        return [
            'name' => $tableName,
            'source' => 'actual_database',
            'description' => 'جدول فعلي في قاعدة البيانات authority3',
            'columns' => $colMap,
            'primary_key' => $primaryKey,
            'foreign_keys' => $foreignKeys,
            'indexes' => array_values(array_map(
                fn ($name, $i) => ['columns' => $i['columns'], 'name' => $name],
                array_keys($indexList),
                $indexList
            )),
            'unique_keys' => array_values(array_map(
                fn ($name, $u) => ['columns' => $u['columns'], 'name' => $name],
                array_keys($uniqueKeys),
                $uniqueKeys
            )),
            'soft_deletes' => $softDeletes,
            'timestamps' => $timestamps,
            'create_sql' => $createSql,
        ];
    }

    /** @return list<array> */
    private function fetchColumns(string $tableName): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COLUMN_NAME AS name, DATA_TYPE AS data_type, COLUMN_TYPE AS column_type,
                    IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default,
                    COLUMN_KEY AS column_key, EXTRA AS extra
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION'
        );
        $stmt->execute([$this->database, $tableName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array> */
    private function fetchIndexes(string $tableName): array
    {
        $stmt = $this->pdo->query("SHOW INDEX FROM `{$tableName}`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return list<array> */
    private function fetchForeignKeys(string $tableName): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT k.CONSTRAINT_NAME as name, k.COLUMN_NAME as column_name,
                    k.REFERENCED_TABLE_NAME as ref_table, k.REFERENCED_COLUMN_NAME as ref_column,
                    rc.UPDATE_RULE as on_update, rc.DELETE_RULE as on_delete
             FROM information_schema.KEY_COLUMN_USAGE k
             JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
               ON k.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
              AND k.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
             WHERE k.TABLE_SCHEMA = ? AND k.TABLE_NAME = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY k.CONSTRAINT_NAME, k.ORDINAL_POSITION'
        );
        $stmt->execute([$this->database, $tableName]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $fks = [];
        foreach ($rows as $row) {
            $fks[] = [
                'column' => $row['column_name'],
                'ref_table' => $row['ref_table'],
                'ref_column' => $row['ref_column'],
                'on_delete' => strtolower($row['on_delete']),
                'on_update' => strtolower($row['on_update']),
                'name' => $row['name'],
                'source' => 'actual_db_fk',
            ];
        }
        return $fks;
    }

    private function fetchCreateTable(string $tableName): string
    {
        $stmt = $this->pdo->query("SHOW CREATE TABLE `{$tableName}`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['Create Table'] ?? '';
    }

    private function normalizeType(string $columnType): string
    {
        return strtolower($columnType);
    }

    /** @return list<string>|null */
    private function parseEnum(string $columnType): ?array
    {
        if (!str_starts_with(strtolower($columnType), 'enum(')) {
            return null;
        }
        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $columnType, $m);
        return $m[1] ?: null;
    }
}
