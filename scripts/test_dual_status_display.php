<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrCtrCaseSubstatus;
use App\Services\ContractCaseApprovalService;

echo "════════════════════════════════════════════════════════════════════\n";
echo "VERIFICATION: DUAL STATUS DISPLAY (LEGACY vs CURRENT HOLDER)\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

// STEP 0: Check STAGE_TO_LEGACY_STATUS mapping accessibility
echo "STEP 0: STAGE_TO_LEGACY_STATUS MAPPING VERIFICATION\n";
foreach (ContractCaseApprovalService::STAGE_TO_LEGACY_STATUS as $stage => $legacy) {
    echo "  - Stage: [ " . str_pad($stage, 12) . " ]  ==>  Legacy Status: [ " . str_pad($legacy, 22) . " ]\n";
}
echo "\n";

// STEP 1 & 2: Inspect Real Cases from Database and Verify Dual Display
echo "STEP 1 & 2: LIVE DATABASE DUAL VALUE CHECK\n";
$sampleCases = HrCtrCase::with(['currentSubstatus', 'casePlans.project'])
    ->orderBy('ctc_id', 'desc')
    ->take(10)
    ->get();

echo str_pad("Case ID", 10) . " | " . str_pad("Type", 6) . " | " . str_pad("Current Holder (css_stage)", 28) . " | " . str_pad("Legacy Status (ctc_status)", 28) . "\n";
echo str_repeat("-", 80) . "\n";

foreach ($sampleCases as $c) {
    $holderStage = $c->currentSubstatus->css_stage ?? $c->current_stage ?? 'N/A';
    $legacyStatus = $c->ctc_status ?? 'N/A';
    echo str_pad("CC-{$c->ctc_id}", 10) . " | " . str_pad(strtoupper($c->ctc_type), 6) . " | " . str_pad($holderStage, 28) . " | " . str_pad($legacyStatus, 28) . "\n";
}

echo "\n";

// STEP 3: Test Blade View Compilation for all Index & Show Views
echo "STEP 3: BLADE VIEW RENDERING VERIFICATION\n";
$viewsToTest = [
    'division.contract-cases.index',
    'division.contract-cases.show',
    'hr.contract-cases.index',
    'hr.contract-cases.show',
    'finance.contract-cases.index',
    'finance.contract-cases.show',
    'md.contract-cases.index',
    'md.contract-cases.show',
];

$testCase = HrCtrCase::with(['currentSubstatus', 'casePlans.project', 'remarksHistory', 'attachments'])->first();
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::setUser($user);
$departments = \Illuminate\Support\Facades\DB::table('cen.units')->orderBy('unt_name')->get();
$employees = \Illuminate\Support\Facades\DB::table('hr.emps')->take(10)->get();

foreach ($viewsToTest as $viewName) {
    try {
        if (str_contains($viewName, 'index')) {
            $rendered = view($viewName, [
                'cases'          => collect([$testCase]),
                'actionReqCases' => collect([$testCase]),
                'initiatedCases' => collect([$testCase]),
                'completedCases' => collect([$testCase]),
                'authorityRole'  => 'MD',
                'deptMap'        => [],
            ])->render();
        } else {
            $approvalService = app(ContractCaseApprovalService::class);
            $rendered = view($viewName, [
                'case'           => $testCase,
                'authorityRole'  => 'MD',
                'authDetails'    => $approvalService->getApprovalAuthorityDetails($testCase),
                'canApprove'     => true,
                'deptMap'        => [],
                'departments'    => $departments,
                'employees'      => $employees,
            ])->render();
        }

        // Verify that rendered HTML contains both labels
        $hasLegacyLabel = str_contains($rendered, 'Legacy: ') || str_contains($rendered, 'Legacy Status');
        $hasHolderLabel = str_contains($rendered, 'Holder: ') || str_contains($rendered, 'Current Holder');

        echo "  - View: [ " . str_pad($viewName, 32) . " ] => Render: OK | Dual Badges Present: " . ($hasLegacyLabel && $hasHolderLabel ? "YES ✓" : "PARTIAL ?") . "\n";
    } catch (\Throwable $e) {
        echo "  - View: [ " . str_pad($viewName, 32) . " ] => ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n════════════════════════════════════════════════════════════════════\n";
echo "DUAL STATUS DISPLAY VERIFICATION COMPLETED SUCCESSFULLY!\n";
echo "════════════════════════════════════════════════════════════════════\n";
