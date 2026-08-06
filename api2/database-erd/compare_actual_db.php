<?php
/**
 * Compare actual MySQL database with migrations, models, and existing ERD files.
 * READ-ONLY — does not modify the database or project source files.
 */

declare(strict_types=1);

$apiRoot = dirname(__DIR__);
$erdDir = dirname($apiRoot) . '/database-erd';
$libDir = __DIR__ . '/lib';

require $apiRoot . '/vendor/autoload.php';
$app = require $apiRoot . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require $libDir . '/MigrationParser.php';
require $libDir . '/ModelParser.php';
require $libDir . '/SchemaBuilder.php';
require $libDir . '/Writers.php';
require __DIR__ . '/lib/ActualDbExtractor.php';
require __DIR__ . '/lib/ComparisonReport.php';

use Illuminate\Support\Facades\DB;

echo "=== Actual Database Comparison ===\n";

$dbName = config('database.connections.mysql.database');
echo "Database: {$dbName}\n";

// 1. Extract actual DB schema (read-only)
$extractor = new ActualDbExtractor(DB::connection()->getPdo(), $dbName);
$actualSchema = $extractor->extract();
echo "Actual tables: " . count($actualSchema) . "\n";

// 2. Parse migrations
$migrationFiles = glob($apiRoot . '/database/migrations/*.php') ?: [];
sort($migrationFiles);
$migrationParser = new MigrationParser();
foreach ($migrationFiles as $file) {
    $migrationParser->parseFile($file);
}
$migrationSchema = $migrationParser->getSchema();
echo "Migration tables: " . count($migrationSchema) . "\n";

// 3. Parse models
$modelFiles = glob($apiRoot . '/app/Models/*.php') ?: [];
$modelParser = new ModelParser();
$modelParser->parseAll($modelFiles);
$eloquentRelations = $modelParser->getRelations();
echo "Model relations: " . count($eloquentRelations) . "\n";

// 4. Build migration-based relationships for comparison
$sqlComparison = ['only_in_migrations' => [], 'only_in_dumps' => [], 'newest_dump' => null, 'dump_files' => []];
$migrationBuilder = new SchemaBuilder($migrationSchema, $eloquentRelations, $sqlComparison, $migrationFiles, $modelFiles, []);
$migrationReport = $migrationBuilder->build();

// 5. Build actual-DB-based schema report
$actualBuilder = new SchemaBuilder($actualSchema, $eloquentRelations, $sqlComparison, $migrationFiles, $modelFiles, []);
$actualReport = $actualBuilder->build();

// 6. Compare and generate report
$comparator = new ComparisonReport(
    $actualSchema,
    $migrationSchema,
    $eloquentRelations,
    $migrationReport['relationships'],
    $actualReport['relationships'],
    $migrationFiles,
    $modelFiles
);
$comparison = $comparator->build();

// 7. Write ACTUAL_DATABASE_COMPARISON_AR.md
$comparator->writeReport($erdDir . '/ACTUAL_DATABASE_COMPARISON_AR.md', $comparison, $dbName);

// 8. Update schema.dbml and erd.mmd from ACTUAL database
$actualReport['schema'] = $actualSchema;
$actualReport['comparison_meta'] = $comparison['summary'];
$writers = new Writers($erdDir, $actualReport);
$writers->writeDbml();
$writers->writeMermaid();
$writers->validateOutputs();

// 9. Update RELATIONSHIPS with source tags
$comparator->writeEnhancedRelationships($erdDir . '/RELATIONSHIPS_AR.md', $comparison);

echo "\nComparison Summary:\n";
foreach ($comparison['summary'] as $k => $v) {
    echo "  {$k}: {$v}\n";
}
echo "\nOutput: {$erdDir}/ACTUAL_DATABASE_COMPARISON_AR.md\n";
echo "Updated: schema.dbml, erd.mmd, RELATIONSHIPS_AR.md\n";
