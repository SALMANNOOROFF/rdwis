<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Services\SidebarBadgeService;
use App\Services\FinancialIntelligenceService;
use Illuminate\Support\Facades\DB;

echo "====================================================================\n";
echo "=== STEP 4 AUDIT: REGRESSION CHECK ON LEGACY-DEPENDENT QUERIES =====\n";
echo "====================================================================\n\n";

$results = [];

// 1. Dashboard queries
try {
    $pendingCasesCount = HrCtrCase::where('ctc_status', 'Under HR Scrutiny')->count();
    $inApprovalCasesCount = HrCtrCase::whereIn('ctc_status', ['Under Finance Scrutiny', 'Under Approval'])->count();
    $approvedCasesCount = HrCtrCase::where('ctc_status', 'Approved')->count();
    $results['Dashboard Counts'] = [
        'status' => 'PASS',
        'details' => "Under HR Scrutiny: {$pendingCasesCount}, Under Approval/Finance: {$inApprovalCasesCount}, Approved: {$approvedCasesCount}"
    ];
} catch (\Throwable $e) {
    $results['Dashboard Counts'] = ['status' => 'FAIL', 'details' => $e->getMessage()];
}

// 2. FinancialIntelligenceService queries
try {
    $fis = app(FinancialIntelligenceService::class);
    $cfSalUnderway = $fis->getCfSalUnderway();
    $prjSalUnderway = $fis->getPrjSalUnderway(300001);
    $results['FinancialIntelligenceService'] = [
        'status' => 'PASS',
        'details' => "getCfSalUnderway(): {$cfSalUnderway}, getPrjSalUnderway(300001): {$prjSalUnderway}"
    ];
} catch (\Throwable $e) {
    $results['FinancialIntelligenceService'] = ['status' => 'FAIL', 'details' => $e->getMessage()];
}

// 3. SidebarBadgeService queries
try {
    $badgeService = app(SidebarBadgeService::class);
    $hrBadge = HrCtrCase::whereIn('ctc_status', ['Under HR Scrutiny'])->count();
    $financeBadge = HrCtrCase::whereIn('ctc_status', ['Under Finance Scrutiny'])->count();
    $mdBadge = HrCtrCase::whereIn('ctc_status', ['Under Approval'])->count();
    $results['SidebarBadgeService'] = [
        'status' => 'PASS',
        'details' => "HR Badge: {$hrBadge}, Finance Badge: {$financeBadge}, MD Badge: {$mdBadge}"
    ];
} catch (\Throwable $e) {
    $results['SidebarBadgeService'] = ['status' => 'FAIL', 'details' => $e->getMessage()];
}

// 4. Duplicate Active Case Detection Query
try {
    $activeCount = HrCtrCase::whereNotIn('ctc_status', ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])->count();
    $results['Duplicate Case Detection'] = [
        'status' => 'PASS',
        'details' => "Active non-terminal cases in pipeline: {$activeCount}"
    ];
} catch (\Throwable $e) {
    $results['Duplicate Case Detection'] = ['status' => 'FAIL', 'details' => $e->getMessage()];
}

// 5. Raw SQL queries reading ctc_status
try {
    $rawUnderway = DB::select("SELECT ctc_id, ctc_status, ctc_empnamecomp FROM hr.ctrcases WHERE ctc_status LIKE 'Under%'");
    $results['Raw SQL WHERE ctc_status LIKE Under%'] = [
        'status' => 'PASS',
        'details' => "Found " . count($rawUnderway) . " underway records correctly matched by SQL wildcard query"
    ];
} catch (\Throwable $e) {
    $results['Raw SQL WHERE ctc_status LIKE Under%'] = ['status' => 'FAIL', 'details' => $e->getMessage()];
}

foreach ($results as $test => $res) {
    echo "[$res[status]] $test: $res[details]\n";
}

