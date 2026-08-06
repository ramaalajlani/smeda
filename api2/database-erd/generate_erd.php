<?php
/**
 * ERD generator — reads migrations, models, and SQL dumps (read-only).
 * Does NOT modify the database or project source files.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$outDir = dirname(__DIR__, 2) . '/database-erd';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}
$migrationsDir = $root . '/database/migrations';
$modelsDir = $root . '/app/Models';

require __DIR__ . '/lib/MigrationParser.php';
require __DIR__ . '/lib/ModelParser.php';
require __DIR__ . '/lib/SqlDumpParser.php';
require __DIR__ . '/lib/SchemaBuilder.php';
require __DIR__ . '/lib/Writers.php';

echo "=== SMEDC Database ERD Generator ===\n";

// 1. Parse migrations (source of truth)
$migrationFiles = glob($migrationsDir . '/*.php') ?: [];
sort($migrationFiles);
$parser = new MigrationParser();
foreach ($migrationFiles as $file) {
    $parser->parseFile($file);
}
$schema = $parser->getSchema();

// 2. Parse models for Eloquent relationships
$modelFiles = glob($modelsDir . '/*.php') ?: [];
$modelParser = new ModelParser();
$modelParser->parseAll($modelFiles);
$eloquentRelations = $modelParser->getRelations();

// 3. Parse SQL dumps for comparison
$sqlFiles = [
    $root . '/../u142331648_authority32 (2).sql',
    $root . '/storage/import_authority32.sql',
    $root . '/backup_before_import.sql',
    $root . '/u142331648_entrep_db.sql',
    $root . '/database/idlib_training_import.sql',
];
$sqlParser = new SqlDumpParser();
foreach ($sqlFiles as $sqlFile) {
    if (is_file($sqlFile)) {
        $sqlParser->parseFile($sqlFile);
    }
}
$sqlComparison = $sqlParser->compareWithSchema($schema);

// 4. Build unified relationship map + audit issues
$builder = new SchemaBuilder($schema, $eloquentRelations, $sqlComparison, $migrationFiles, $modelFiles, $sqlFiles);
$report = $builder->build();

// 5. Write output files
$writers = new Writers($outDir, $report);
$writers->writeAll();

echo "\nSummary:\n";
echo "  DB-related files scanned: " . $report['stats']['db_files'] . "\n";
echo "  Migration files: " . count($migrationFiles) . "\n";
echo "  Model files: " . count($modelFiles) . "\n";
echo "  SQL dump files: " . $report['stats']['sql_files'] . "\n";
echo "  Tables: " . $report['stats']['tables'] . "\n";
echo "  Confirmed FK relations: " . $report['stats']['confirmed_relations'] . "\n";
echo "  Strong (model/join) relations: " . $report['stats']['strong_relations'] . "\n";
echo "  Probable relations: " . $report['stats']['probable_relations'] . "\n";
echo "  Unresolved relations: " . $report['stats']['unresolved_relations'] . "\n";
echo "  Audit issues: " . count($report['audit_issues']) . "\n";
echo "\nOutput written to: {$outDir}\n";
