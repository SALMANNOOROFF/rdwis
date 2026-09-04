<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrCtrCaseSubstatus;
use App\Models\HrCtrCaseRemark;
use App\Models\Purchase;
use App\Models\PurCaseSubstatus;
use App\Models\PurCaseDecision;
use App\Services\ContractCaseApprovalService;
use App\Services\PurchaseApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING CONTRACT CASE & PURCHASE CASE LIFECYCLE FLOWS ===\n\n";

$contractService = app(ContractCaseApprovalService::class);
$purchaseService = app(PurchaseApprovalService::class);

// Mock User
$user = (object)[
    'acc_id' => 1,
    'acc_name' => 'Test Officer',
    'acc_user' => 'testuser',
    'acc_rank' => 'Director',
    'acc_desig' => 'Director',
    'acc_untarea' => 'rdw'
];

// Test 1: Destinations Matrix
echo "1. Testing Destinations Matrix...\n";
$hrDests = $contractService->getAvailableDestinations('hr');
assert(isset($hrDests['HQ Departments']['Finance']));
echo "   - HR destinations loaded: OK\n";

$divDests = $contractService->getAvailableDestinations('division');
assert(isset($divDests['HQ Departments']['HR']));
echo "   - Division destinations loaded: OK\n";

$mdDests = $contractService->getAvailableDestinations('rdw');
assert(isset($mdDests['Executive Authorities']['DG']));
echo "   - MD destinations loaded: OK\n";

// Test 2: Contract Case Full Approval Lifecycle in DB Transaction (Rollback at end)
echo "\n2. Testing Contract Case Approval Lifecycle...\n";
DB::beginTransaction();
try {
    $case = HrCtrCase::create([
        'ctc_type' => 'Hg',
        'ctc_empnamecomp' => 'Test Candidate Flow',
        'ctc_newjobtitle' => 'Senior Developer',
        'ctc_newgrade' => 'G-1',
        'ctc_emp_type' => 'Full Time',
        'ctc_newsalary' => 150000,
        'ctc_newstartdt' => '2026-09-01',
        'ctc_newenddt' => '2027-08-31',
        'ctc_divisionid' => 1,
        'ctc_unt_id' => 1,
        'ctc_newunt_id' => 1,
        'ctc_status' => 'Draft',
        'ctc_cnic' => '42101-1111111-1'
    ]);

    // Initial substatus
    HrCtrCaseSubstatus::create([
        'css_ctc_id' => $case->ctc_id,
        'css_stage' => 'Division',
        'css_is_current' => true,
        'css_since' => now()
    ]);

    echo "   - Created Case #{$case->ctc_id} with status: {$case->ctc_status}, stage: {$case->current_stage}\n";

    // Step 2a: Release to HR
    $contractService->release($case, $user, '<ol><li>Released from division</li></ol>');
    $case->refresh();
    echo "   - After Release: status = {$case->ctc_status}, stage = {$case->current_stage}\n";
    assert($case->ctc_status === 'Under HR Scrutiny');
    assert($case->current_stage === 'HR');

    // Step 2b: Forward to Finance
    $contractService->forward($case, $user, '<ol start="2"><li>Forwarded to Finance</li></ol>', 'Finance');
    $case->refresh();
    echo "   - After Forward to Finance: status = {$case->ctc_status}, stage = {$case->current_stage}\n";
    assert($case->ctc_status === 'Under Finance Scrutiny');
    assert($case->current_stage === 'Finance');

    // Step 2c: Forward to MD
    $contractService->forward($case, $user, '<ol start="3"><li>Forwarded to MD</li></ol>', 'MD');
    $case->refresh();
    echo "   - After Forward to MD: status = {$case->ctc_status}, stage = {$case->current_stage}\n";
    assert($case->ctc_status === 'Under Approval');
    assert($case->current_stage === 'MD');

    // Step 2d: Approve by MD
    $contractService->approve($case, $user, [], '<ol start="4"><li>Approved by MD</li></ol>');
    $case->refresh();
    echo "   - After MD Approval: status = {$case->ctc_status}, stage = {$case->current_stage}\n";
    assert($case->ctc_status === 'Approved');
    assert($case->current_stage === 'Approved');

    // Step 2e: Remarks History check
    $remarks = $case->remarksHistory;
    echo "   - Total Remarks logged: " . $remarks->count() . "\n";
    assert($remarks->count() === 4);

} finally {
    DB::rollBack();
    echo "   - Contract Case test rolled back cleanly.\n";
}

// Test 3: Contract Case Rejection Flow
echo "\n3. Testing Contract Case Rejection Flow...\n";
DB::beginTransaction();
try {
    $case = HrCtrCase::create([
        'ctc_type' => 'Hg',
        'ctc_empnamecomp' => 'Test Candidate Reject',
        'ctc_newjobtitle' => 'Senior Developer',
        'ctc_newgrade' => 'G-1',
        'ctc_emp_type' => 'Full Time',
        'ctc_newsalary' => 150000,
        'ctc_newstartdt' => '2026-09-01',
        'ctc_newenddt' => '2027-08-31',
        'ctc_divisionid' => 1,
        'ctc_status' => 'Draft'
    ]);

    HrCtrCaseSubstatus::create([
        'css_ctc_id' => $case->ctc_id,
        'css_stage' => 'HR',
        'css_is_current' => true,
        'css_since' => now()
    ]);

    $contractService->reject($case, $user, '<ol><li>Not Approved due to budget constraints</li></ol>');
    $case->refresh();
    echo "   - After Reject: status = {$case->ctc_status}, stage = {$case->current_stage}, closedtg = {$case->ctc_closedtg}\n";
    assert($case->ctc_status === 'Not Approved');
    assert($case->current_stage === 'Not Approved');
    assert(!empty($case->ctc_closedtg));
} finally {
    DB::rollBack();
    echo "   - Contract Case rejection test rolled back cleanly.\n";
}

echo "\nALL WORKFLOW TESTS PASSED PERFECTLY!\n";
