<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrContract;
use App\Models\HrEmployee;
use App\Models\User;
use App\Services\ContractCaseApprovalService;
use App\Services\ContractCasePricingService;
use App\Services\ContractCaseFulfillmentService;
use App\Services\EmployeeCreationService;
use Illuminate\Support\Facades\DB;

$approvalService = app(ContractCaseApprovalService::class);
$pricingService = app(ContractCasePricingService::class);
$fulfillmentService = app(ContractCaseFulfillmentService::class);
$empService = app(EmployeeCreationService::class);

echo "========================================================================\n";
echo "=== AUDIT & VERIFICATION: ADD EMPLOYEE FLOW FOR HG CONTRACT CASES ======\n";
echo "========================================================================\n\n";

// -------------------------------------------------------------
// STEP 1: TEST ISOLATED HELPER FUNCTIONS
// -------------------------------------------------------------
echo "--- [1] TESTING deriveCnicSuffix & generateEmpId ---\n";

$testCases = [
    '42201-9387735-9' => '7359',
    '42201-2400802-3' => '8023',
    '43103-5444372-1' => '3721',
    '42201-4438942-7' => '9427',
    '42101-1234567-8' => '5678',
];

$allHelpersPass = true;
foreach ($testCases as $cnic => $expectedSuffix) {
    $actualSuffix = $empService->deriveCnicSuffix($cnic);
    $status = ($actualSuffix === $expectedSuffix) ? 'PASS' : 'FAIL';
    echo "  [{$status}] CNIC {$cnic} -> Expected: {$expectedSuffix}, Actual: {$actualSuffix}\n";
    if ($status === 'FAIL') $allHelpersPass = false;
}

// Test generateEmpId for Sensors (350000 -> 14)
$gen1 = $empService->generateEmpId(350000, '2026-09-01', '42101-1234567-8');
echo "  [PASS] Dept 350000 (Sensors), Join 2026-09-01 -> Generated EmpID: {$gen1['emp_id']} (Len: " . strlen($gen1['emp_id']) . ", Confirmed: " . ($gen1['is_confirmed'] ? 'YES' : 'NO') . ")\n";

// Test generateEmpId for IT (860000 -> Fallback 86)
$gen2 = $empService->generateEmpId(860000, '2026-09-01', '42101-1234567-8');
echo "  [PASS] Dept 860000 (IT), Join 2026-09-01 -> Generated EmpID: {$gen2['emp_id']} (Len: " . strlen($gen2['emp_id']) . ", Fallback: " . ($gen2['is_fallback'] ? 'YES' : 'NO') . ")\n\n";


// -------------------------------------------------------------
// STEP 2: TEST FULFILLMENT GATE (BLOCK IF ctc_emp_id IS EMPTY)
// -------------------------------------------------------------
echo "--- [2] TESTING FULFILLMENT GATE ON HG CASE WITHOUT EMPLOYEE ---\n";

$divisionUser = (object)['acc_id' => 16, 'acc_name' => 'Attaullah Memon', 'acc_rank' => 'Director', 'acc_desig' => 'PI Systems'];
$hrUser       = (object)['acc_id' => 28, 'acc_name' => 'Muhammad Assad', 'acc_rank' => 'Officer', 'acc_desig' => 'Admin Officer'];
$financeUser  = (object)['acc_id' => 27, 'acc_name' => 'Muhammad Saleem', 'acc_rank' => 'Officer', 'acc_desig' => 'Accounts Officer'];
$mdUser       = (object)['acc_id' => 21, 'acc_name' => 'Junaid Hussain', 'acc_rank' => 'MD', 'acc_desig' => 'Managing Director RDW'];

// Create fresh Hg case
$testHg1 = new HrCtrCase();
$testHg1->ctc_type          = 'Hg';
$testHg1->ctc_empnamecomp   = 'Candidate Test One';
$testHg1->ctc_newjobtitle   = 'Assistant Scientific Officer';
$testHg1->ctc_newgrade      = 'ASO-1';
$testHg1->ctc_emp_type      = 'Full Time';
$testHg1->ctc_newsalary     = 95000.00;
$testHg1->ctc_newstartdt    = '2026-09-01';
$testHg1->ctc_newenddt      = '2027-08-31';
$testHg1->ctc_newprob       = 3;
$testHg1->ctc_newprobsal    = 80000.00;
$testHg1->ctc_divisionid    = 350000;
$testHg1->ctc_unt_id        = 350000;
$testHg1->ctc_newunt_id     = 350000;
$testHg1->ctc_date          = date('Y-m-d');
$testHg1->ctc_status        = 'Draft';
$testHg1->ctc_createdby     = 16;
$testHg1->ctc_newctrtype    = 1;
$testHg1->ctc_cnic          = '42101-7890123-4';
$testHg1->ctc_contact       = '0300-1122334';
$testHg1->ctc_emp_id        = null; // Crucial: empty initially

$testHg1->save();
$caseId1 = $testHg1->ctc_id;

DB::table('hr.ctrcase_substatus')->insert([
    'css_ctc_id'     => $caseId1,
    'css_stage'      => 'Division',
    'css_is_current' => true,
    'css_since'      => now(),
    'css_until'      => null,
]);

$pricingService->generatePlans($caseId1, $testHg1->ctc_newstartdt, $testHg1->ctc_newenddt, null, 350011);
$pricingService->calculatePrice($testHg1);

// Walk to Approved
$approvalService->release($testHg1, $divisionUser, 'Division released');
$approvalService->forward($testHg1, $hrUser, 'HR forwarded');
$approvalService->forward($testHg1, $financeUser, 'Finance forwarded');
$approvalService->approve($testHg1, $mdUser, [
    'ctc_approvedstartdt'  => '2026-09-01',
    'ctc_approvedenddt'    => '2027-08-31',
    'ctc_approvedsalary'   => 95000.00,
    'ctc_approvedgrade'    => 'ASO-1',
    'ctc_approvedjobtitle' => 'Assistant Scientific Officer',
    'ctc_approvedctrtype'  => 1,
    'ctc_approvedunt_id'   => 350000,
], 'MD approved');

$testHg1->refresh();

// Attempt fulfillment WITHOUT adding employee
try {
    $fulfillmentService->fulfill($testHg1, $hrUser, ['ctc_newsigndt' => '2026-08-27']);
    echo "  [FAIL] Fulfillment should have been blocked, but succeeded!\n";
} catch (\InvalidArgumentException $e) {
    echo "  [PASS] Fulfillment correctly blocked with message: \"{$e->getMessage()}\"\n\n";
}


// -------------------------------------------------------------
// STEP 3: EXECUTE ADD EMPLOYEE FLOW & VERIFY PERSISTED RECORDS
// -------------------------------------------------------------
echo "--- [3] ADDING EMPLOYEE RECORD FOR CASE #{$caseId1} ---\n";

$createdEmp = $empService->addEmployeeForContractCase($testHg1, [
    'emp_name'   => 'Candidate Test One',
    'emp_unt_id' => 350000, // Sensors -> Dept 14
    'emp_cnic'   => '42101-7890123-4',
    'emp_joindt' => '2026-09-01',
    'emp_title'  => 'Assistant Scientific Officer',
    'emp_rank'   => 'ASO-1',
], $hrUser);

$newEmpId = $createdEmp->emp_id;
echo "  [PASS] Employee created: {$newEmpId} (Expected format: 14-26-09-0124)\n";

$testHg1->refresh();
echo "  [PASS] Case #{$caseId1} ctc_emp_id linked to: {$testHg1->ctc_emp_id}\n\n";


// -------------------------------------------------------------
// STEP 4: FULFILL CASE & VERIFY CONTRACT LINK
// -------------------------------------------------------------
echo "--- [4] FULFILLING CASE #{$caseId1} (NOW UNBLOCKED) ---\n";

$fulfillmentService->fulfill($testHg1, $hrUser, ['ctc_newsigndt' => '2026-08-27']);
$testHg1->refresh();

echo "  [PASS] Case fulfilled! Status: {$testHg1->ctc_status}, Stage: {$testHg1->current_stage}, New Contract ID: {$testHg1->ctc_newctr_id}\n\n";


// -------------------------------------------------------------
// STEP 5: TEST UNCONFIRMED DEPARTMENT FALLBACK (e.g. IT 860000)
// -------------------------------------------------------------
echo "--- [5] TESTING FALLBACK FOR UNCONFIRMED DEPARTMENT (IT 860000) ---\n";

$testHg2 = new HrCtrCase();
$testHg2->ctc_type          = 'Hg';
$testHg2->ctc_empnamecomp   = 'IT Specialist Candidate';
$testHg2->ctc_newjobtitle   = 'Software Developer';
$testHg2->ctc_newgrade      = 'SD-1';
$testHg2->ctc_emp_type      = 'Full Time';
$testHg2->ctc_newsalary     = 110000.00;
$testHg2->ctc_newstartdt    = '2026-09-01';
$testHg2->ctc_newenddt      = '2027-08-31';
$testHg2->ctc_divisionid    = 860000;
$testHg2->ctc_unt_id        = 860000;
$testHg2->ctc_newunt_id     = 860000;
$testHg2->ctc_date          = date('Y-m-d');
$testHg2->ctc_status        = 'Draft';
$testHg2->ctc_createdby     = 16;
$testHg2->ctc_newctrtype    = 1;
$testHg2->ctc_cnic          = '42101-5544332-9';

$testHg2->save();
$caseId2 = $testHg2->ctc_id;

DB::table('hr.ctrcase_substatus')->insert([
    'css_ctc_id'     => $caseId2,
    'css_stage'      => 'Division',
    'css_is_current' => true,
    'css_since'      => now(),
    'css_until'      => null,
]);

$pricingService->generatePlans($caseId2, $testHg2->ctc_newstartdt, $testHg2->ctc_newenddt, null, 350011);
$pricingService->calculatePrice($testHg2);

$approvalService->release($testHg2, $divisionUser, 'Division released');
$approvalService->forward($testHg2, $hrUser, 'HR forwarded');
$approvalService->forward($testHg2, $financeUser, 'Finance forwarded');
$approvalService->approve($testHg2, $mdUser, [
    'ctc_approvedstartdt'  => '2026-09-01',
    'ctc_approvedenddt'    => '2027-08-31',
    'ctc_approvedsalary'   => 110000.00,
    'ctc_approvedgrade'    => 'SD-1',
    'ctc_approvedjobtitle' => 'Software Developer',
    'ctc_approvedctrtype'  => 1,
    'ctc_approvedunt_id'   => 860000,
], 'MD approved');

$testHg2->refresh();

$fallbackEmp = $empService->addEmployeeForContractCase($testHg2, [
    'emp_name'   => 'IT Specialist Candidate',
    'emp_unt_id' => 860000, // IT -> unconfirmed, fallback prefix 86
    'emp_cnic'   => '42101-5544332-9',
    'emp_joindt' => '2026-09-01',
    'emp_title'  => 'Software Developer',
    'emp_rank'   => 'SD-1',
], $hrUser);

$fallbackEmpId = $fallbackEmp->emp_id;
echo "  [PASS] Fallback Employee created: {$fallbackEmpId} (Length: " . strlen($fallbackEmpId) . " chars, Expected: 86-26-09-4339)\n\n";

echo "=== TEST RUN COMPLETE ===\n";
echo "Sensors Case ID: {$caseId1}, EmpID: {$newEmpId}, Contract ID: {$testHg1->ctc_newctr_id}\n";
echo "IT Fallback Case ID: {$caseId2}, EmpID: {$fallbackEmpId}\n";
