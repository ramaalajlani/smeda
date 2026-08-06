<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('syria_locations')) {
    echo "syria_locations table missing\n";
    exit(1);
}

$total = (int) DB::table('syria_locations')->count();
$distinct = (int) DB::table('syria_locations')->distinct()->count('community_pcode');
$dupGroups = DB::table('syria_locations')
    ->select('community_pcode', DB::raw('COUNT(*) as c'))
    ->groupBy('community_pcode')
    ->having('c', '>', 1)
    ->count();

echo "total_rows={$total}\n";
echo "distinct_community_pcode={$distinct}\n";
echo "duplicate_pcode_groups={$dupGroups}\n";

$indexes = DB::select("SHOW INDEX FROM syria_locations WHERE Column_name = 'community_pcode'");
foreach ($indexes as $idx) {
    echo "index: {$idx->Key_name} Non_unique={$idx->Non_unique} Column={$idx->Column_name}\n";
}
