<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;
use App\Models\PurCaseSubstatus;
use App\Models\PurCaseDecision;
use App\Models\CenAccount;
use App\Services\PurchaseApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING PURCHASE CASE APPROVAL & REJECTION LIFECYCLES ===\n\n";

$purchaseService = app(PurchaseApprovalService::class);

$user = CenAccount::first();
if ($user) {
    Auth::login($user);
}

// Test Purchase Approval on temporary case in transaction
DB::beginTransaction();
try {
    $head = DB::table('cen.heads')->first();
    $headId = $head ? $head->hed_id : 1;

    $case = Purchase::create([
        'pcs_type' => 'Store',
        'pcs_title' => 'Test Purchase Item',
        'pcs_price' => 200000,
        'pcs_unt_id' => 1,
        'pcs_effunt_id' => 1,
        'pcs_intunt_id' => 1,
        'pcs_transtype' => 1,
        'pcs_status' => 'Under Approval',
        'pcs_date' => now(),
        'pcs_effhed_id' => $headId
    ]);

    PurCaseSubstatus::create([
        'pss_pcs_id' => $case->pcs_id,
        'pss_stage' => 'MD',
        'pss_is_current' => true,
        'pss_since' => now()
    ]);

    echo "1. Created Purchase Case #{$case->pcs_id} with status: {$case->pcs_status}\n";

    // Action: Approve
    $purchaseService->processDecision($case, 'approve', '<ol><li>Approved by MD</li></ol>');
    $case->refresh();

    echo "   - After Approval: pcs_status = {$case->pcs_status}\n";
    assert($case->pcs_status === 'Approved');

    $activeSubstatus = PurCaseSubstatus::where('pss_pcs_id', $case->pcs_id)->where('pss_is_current', true)->first();
    echo "   - Active substatus count: " . ($activeSubstatus ? 'Found' : 'Closed (None)') . "\n";
    assert($activeSubstatus === null);

} finally {
    DB::rollBack();
    echo "   - Purchase approval test rolled back cleanly.\n";
}

// Test Purchase Reject
DB::beginTransaction();
try {
    $head = DB::table('cen.heads')->first();
    $headId = $head ? $head->hed_id : 1;

    $case = Purchase::create([
        'pcs_type' => 'Store',
        'pcs_title' => 'Test Purchase Item Reject',
        'pcs_price' => 200000,
        'pcs_unt_id' => 1,
        'pcs_effunt_id' => 1,
        'pcs_intunt_id' => 1,
        'pcs_transtype' => 1,
        'pcs_status' => 'Under Approval',
        'pcs_date' => now(),
        'pcs_effhed_id' => $headId
    ]);

    PurCaseSubstatus::create([
        'pss_pcs_id' => $case->pcs_id,
        'pss_stage' => 'MD',
        'pss_is_current' => true,
        'pss_since' => now()
    ]);

    echo "\n2. Created Purchase Case #{$case->pcs_id} for rejection test\n";

    // Action: Reject
    $purchaseService->processDecision($case, 'reject', '<ol><li>Rejected by MD</li></ol>');
    $case->refresh();

    echo "   - After Reject: pcs_status = {$case->pcs_status}\n";
    assert($case->pcs_status === 'Not Approved');

    $activeSubstatus = PurCaseSubstatus::where('pss_pcs_id', $case->pcs_id)->where('pss_is_current', true)->first();
    echo "   - Active substatus count: " . ($activeSubstatus ? 'Found' : 'Closed (None)') . "\n";
    assert($activeSubstatus === null);

} finally {
    DB::rollBack();
    echo "   - Purchase rejection test rolled back cleanly.\n";
}

echo "\nALL PURCHASE LIFECYCLE TESTS PASSED!\n";
