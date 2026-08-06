<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Need;
use App\Models\User;
use App\Support\NeedDataScope;
use App\Support\NeedStatus;
use Laravel\Sanctum\Sanctum;

echo "=== GIS Accounts ===\n";
echo 'data_entry: ' . User::role('data_entry')->count() . "\n";
echo 'data_reviewer: ' . User::role('data_reviewer')->count() . "\n";
echo 'governors scoped: ' . User::role('governor')->whereNotNull('governorate_id')->count()
    . '/' . User::role('governor')->count() . "\n";
echo 'branch_mgr scoped: ' . User::role('branch_manager')->whereNotNull('branch_id')->count()
    . '/' . User::role('branch_manager')->count() . "\n";
echo 'pending needs: ' . Need::where('status', NeedStatus::PENDING_GOVERNORATE_REVIEW)->count() . "\n";

$need = Need::query()->where('title', 'DEMO-PENDING-tartus')->first();
if (!$need) {
    echo "No demo need found\n";
    exit(0);
}

$reviewer = User::query()->where('email', 'data-reviewer.tartus@system.com')->first();
$manager = User::query()->where('email', 'branch.tartus@system.com')->first();
$general = User::query()->where('email', 'general@system.com')->first();

echo "\n=== Workflow test (tartus) ===\n";
echo "Need #{$need->id} status={$need->status}\n";

if ($reviewer) {
    Sanctum::actingAs($reviewer);
    $visible = NeedDataScope::scopeNeeds(Need::query()->whereKey($need->id), $reviewer)->exists();
    echo "Reviewer can see need: " . ($visible ? 'yes' : 'no') . "\n";
}

if ($manager) {
    Sanctum::actingAs($manager);
    $visible = NeedDataScope::scopeNeeds(Need::query()->whereKey($need->id), $manager)->exists();
    echo "Branch manager can see need: " . ($visible ? 'yes' : 'no') . "\n";
}

if ($general) {
    Sanctum::actingAs($general);
    $count = NeedDataScope::scopeNeeds(Need::query(), $general)->count();
    echo "General director sees needs: {$count}\n";
}

$gov = User::query()->where('email', 'governor.tartus@system.com')->first();
if ($gov) {
    Sanctum::actingAs($gov);
    $govCount = NeedDataScope::scopeNeeds(Need::query(), $gov)->count();
    echo "Governor tartus sees needs: {$govCount}\n";
}

if ($need->status === NeedStatus::PENDING_GOVERNORATE_REVIEW && $reviewer && $manager) {
    Sanctum::actingAs($reviewer);
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $reviewReq = Illuminate\Http\Request::create("/api/needs/{$need->id}/review", 'POST', ['note' => 'تدقيق تجريبي']);
    $reviewReq->headers->set('Accept', 'application/json');
    $reviewRes = $kernel->handle($reviewReq);
    echo 'Review HTTP: ' . $reviewRes->getStatusCode() . "\n";
    $need->refresh();
    echo "After review status={$need->status}\n";

    Sanctum::actingAs($manager);
    $approveReq = Illuminate\Http\Request::create("/api/needs/{$need->id}/approve", 'POST', ['note' => 'موافقة تجريبية']);
    $approveReq->headers->set('Accept', 'application/json');
    $approveRes = $kernel->handle($approveReq);
    echo 'Approve HTTP: ' . $approveRes->getStatusCode() . "\n";
    $need->refresh();
    echo "After approve status={$need->status}\n";
}

echo "\nDone.\n";
