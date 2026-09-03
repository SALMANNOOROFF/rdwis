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
use Illuminate\Support\Facades\DB;

$approvalService = app(ContractCaseApprovalService::class);
$pricingService = app(ContractCasePricingService::class);
$fulfillmentService = app(ContractCaseFulfillmentService::class);

echo "===============================================================\n";
echo "=== STEP 3 AUDIT: MANUAL SPOT-CHECK OF Hg & Ce CASES ==========\n";
echo "===============================================================\n\n";

// Load test users
$divisionUser = User::find(16) ?? (object)['acc_id' => 16, 'acc_name' => 'Attaullah Memon', 'acc_rank' => 'Director', 'acc_desig' => 'PI Systems'];
$hrUser       = User::find(28) ?? (object)['acc_id' => 28, 'acc_name' => 'Muhammad Assad', 'acc_rank' => 'Officer', 'acc_desig' => 'Admin Officer'];
$financeUser  = User::find(27) ?? (object)['acc_id' => 27, 'acc_name' => 'Muhammad Saleem', 'acc_rank' => 'Officer', 'acc_desig' => 'Accounts Officer'];
$mdUser       = User::find(21) ?? (object)['acc_id' => 21, 'acc_name' => 'Junaid Hussain', 'acc_rank' => 'MD', 'acc_desig' => 'Managing Director RDW'];

// -------------------------------------------------------------
// PART A: HG (HIRING) CASE AUDIT WALKTHROUGH
// -------------------------------------------------------------
echo "--- [PART A] WALKING HG (HIRING) CASE THROUGH LIFECYCLE ---\n";

// 1. Create Draft Hg Case
$hgCase = new HrCtrCase();
$hgCase->ctc_type          = 'Hg';
$hgCase->ctc_empnamecomp   = 'Audit Hg Candidate ' . date('His');
$hgCase->ctc_newjobtitle   = 'Research Scientist';
$hgCase->ctc_newgrade      = 'RS-1';
$hgCase->ctc_emp_type      = 'Full Time';
$hgCase->ctc_newsalary     = 125000.00;
$hgCase->ctc_newstartdt    = '2026-09-01';
$hgCase->ctc_newenddt      = '2027-08-31';
$hgCase->ctc_newprob       = 3;
$hgCase->ctc_newprobsal    = 100000.00;
$hgCase->ctc_divisionid    = 300000;
$hgCase->ctc_unt_id        = 300000;
$hgCase->ctc_newunt_id     = 300000;
$hgCase->ctc_date          = date('Y-m-d');
$hgCase->ctc_status        = 'Draft';
$hgCase->ctc_createdby     = $divisionUser->acc_id;
$hgCase->ctc_newctrtype    = 1;
$hgCase->ctc_cnic          = '42101-' . rand(1000000, 9999999) . '-1';
$hgCase->ctc_contact       = '0300-1234567';
$hgCase->ctc_remarks       = 'Audit Hg Case initial creation';

// Approved defaults
$hgCase->ctc_approvedunt_id   = 300000;
$hgCase->ctc_approvedstartdt  = $hgCase->ctc_newstartdt;
$hgCase->ctc_approvedenddt    = $hgCase->ctc_newenddt;
$hgCase->ctc_approvedgrade    = $hgCase->ctc_newgrade;
$hgCase->ctc_approvedjobtitle = $hgCase->ctc_newjobtitle;
$hgCase->ctc_approvedsalary   = $hgCase->ctc_newsalary;
$hgCase->ctc_approvedctrtype  = 1;
$hgCase->ctc_approvedprob     = 3;
$hgCase->ctc_approvedprobsal  = 100000.00;

$hgCase->save();
$hgId = $hgCase->ctc_id;

// Initialize substatus
DB::table('hr.ctrcase_substatus')->insert([
    'css_ctc_id'     => $hgId,
    'css_stage'      => 'Division',
    'css_is_current' => true,
    'css_since'      => now(),
    'css_until'      => null,
]);

// Generate plans and calculate price
$pricingService->generatePlans($hgId, $hgCase->ctc_newstartdt, $hgCase->ctc_newenddt, null, 300001);
$pricingService->calculatePrice($hgCase);

echo "1. Created Hg Draft Case #{$hgId}: status={$hgCase->ctc_status}, price={$hgCase->ctc_price}\n";

// 2. Release to HR
$approvalService->release($hgCase, $divisionUser, 'Audit: Division releasing to HR');
$hgCase->refresh();
echo "2. Released to HR: status={$hgCase->ctc_status}, stage={$hgCase->currentSubstatus->css_stage}\n";

// 3. HR Scrutiny -> Forward to Finance
$approvalService->forward($hgCase, $hrUser, 'Audit: HR verified qualifications and forwarded to Finance');
$hgCase->refresh();
echo "3. Forwarded to Finance: status={$hgCase->ctc_status}, stage={$hgCase->currentSubstatus->css_stage}\n";

// 4. Finance Scrutiny -> Forward to MD
$approvalService->forward($hgCase, $financeUser, 'Audit: Finance budget allocated and verified');
$hgCase->refresh();
echo "4. Forwarded to MD: status={$hgCase->ctc_status}, stage={$hgCase->currentSubstatus->css_stage}\n";

// 5. MD Approval
$approvalService->approve($hgCase, $mdUser, [
    'ctc_approvedstartdt'  => '2026-09-01',
    'ctc_approvedenddt'    => '2027-08-31',
    'ctc_approvedsalary'   => 125000.00,
    'ctc_approvedgrade'    => 'RS-1',
    'ctc_approvedjobtitle' => 'Research Scientist',
    'ctc_approvedprob'     => 3,
    'ctc_approvedprobsal'  => 100000.00,
    'ctc_approvedctrtype'  => 1,
    'ctc_approvedunt_id'   => 300000,
], 'Audit: Approved by MD');
$hgCase->refresh();
echo "5. Approved by MD: status={$hgCase->ctc_status}, stage={$hgCase->currentSubstatus->css_stage}\n";

// 6. HR Fulfillment
$fulfillmentService->fulfill($hgCase, $hrUser, [
    'ctc_newsigndt' => '2026-08-27',
]);
$hgCase->refresh();
echo "6. Fulfilled by HR: status={$hgCase->ctc_status}, stage={$hgCase->currentSubstatus->css_stage}, newctr_id={$hgCase->ctc_newctr_id}\n\n";


// -------------------------------------------------------------
// PART B: CE (EXTENSION) CASE AUDIT WALKTHROUGH
// -------------------------------------------------------------
echo "--- [PART B] WALKING CE (EXTENSION) CASE THROUGH LIFECYCLE ---\n";

// First, setup an existing employee and existing contract to extend
$testEmpId = 'AUD-CE-' . rand(100, 999);
DB::table('hr.emps')->insert([
    'emp_id'     => $testEmpId,
    'emp_name'   => 'Audit Extension Employee',
    'emp_cnic'   => '42101-' . rand(1000000, 9999999) . '-2',
    'emp_unt_id' => 300000,
    'emp_title'  => 'Senior Project Engineer',
    'emp_rank'   => 'SPE',
    'emp_joindt' => '2025-01-01',
    'emp_status' => 'Active',
]);

$oldContractId = DB::table('hr.contracts')->insertGetId([
    'ctr_num'      => $testEmpId,
    'ctr_date'     => '2025-01-01',
    'ctr_unt_id'   => 300000,
    'ctr_startdt'  => '2025-01-01',
    'ctr_enddt'    => '2025-12-31',
    'ctr_salary'   => 150000,
    'ctr_jobtitle' => 'Senior Project Engineer',
    'ctr_grade'    => 'SPE',
    'ctr_type'     => 1,
    'ctr_termindt' => null,
    'ctr_remarks'  => 'Initial baseline employment contract.',
], 'ctr_id');

// Seed existing contract plans for old contract
$pricingService->adjustContractPlans($oldContractId, '2025-12-31');

echo "Setup: Created baseline employee {$testEmpId} with Contract #{$oldContractId} (Ends 2025-12-31)\n";

// 1. Create Draft Ce Case
$ceCase = new HrCtrCase();
$ceCase->ctc_type          = 'Ce';
$ceCase->ctc_emp_id        = $testEmpId;
$ceCase->ctc_ctr_id        = $oldContractId;
$ceCase->ctc_empnamecomp   = 'Audit Extension Employee';
$ceCase->ctc_newjobtitle   = 'Senior Project Engineer';
$ceCase->ctc_newgrade      = 'SPE';
$ceCase->ctc_emp_type      = 'Full Time';
$ceCase->ctc_newsalary     = 150000.00;
$ceCase->ctc_newstartdt    = '2025-01-01'; // Same as old start date
$ceCase->ctc_newenddt      = '2026-06-30'; // Extended end date (+6 months)
$ceCase->ctc_terminremarks = 'Extension justified due to critical project milestone deliverables.';
$ceCase->ctc_divisionid    = 300000;
$ceCase->ctc_unt_id        = 300000;
$ceCase->ctc_newunt_id     = 300000;
$ceCase->ctc_date          = date('Y-m-d');
$ceCase->ctc_status        = 'Draft';
$ceCase->ctc_createdby     = $divisionUser->acc_id;
$ceCase->ctc_newctrtype    = 1;
$ceCase->ctc_remarks       = 'Extension case raised by division';

// Approved defaults
$ceCase->ctc_approvedunt_id   = 300000;
$ceCase->ctc_approvedstartdt  = $ceCase->ctc_newstartdt;
$ceCase->ctc_approvedenddt    = $ceCase->ctc_newenddt;
$ceCase->ctc_approvedgrade    = $ceCase->ctc_newgrade;
$ceCase->ctc_approvedjobtitle = $ceCase->ctc_newjobtitle;
$ceCase->ctc_approvedsalary   = $ceCase->ctc_newsalary;
$ceCase->ctc_approvedctrtype  = 1;

$ceCase->save();
$ceId = $ceCase->ctc_id;

// Initialize substatus
DB::table('hr.ctrcase_substatus')->insert([
    'css_ctc_id'     => $ceId,
    'css_stage'      => 'Division',
    'css_is_current' => true,
    'css_since'      => now(),
    'css_until'      => null,
]);

// Generate plans and calculate price
$pricingService->generatePlans($ceId, $ceCase->ctc_newstartdt, $ceCase->ctc_newenddt, null, 300001);
$pricingService->calculatePrice($ceCase);

echo "1. Created Ce Draft Case #{$ceId}: status={$ceCase->ctc_status}, price={$ceCase->ctc_price}\n";

// 2. Release to HR
$approvalService->release($ceCase, $divisionUser, 'Audit: Division releasing Ce case to HR');
$ceCase->refresh();
echo "2. Released to HR: status={$ceCase->ctc_status}, stage={$ceCase->currentSubstatus->css_stage}\n";

// 3. HR Scrutiny -> Forward to Finance
$approvalService->forward($ceCase, $hrUser, 'Audit: HR verified extension justification');
$ceCase->refresh();
echo "3. Forwarded to Finance: status={$ceCase->ctc_status}, stage={$ceCase->currentSubstatus->css_stage}\n";

// 4. Finance Scrutiny -> Forward to MD
$approvalService->forward($ceCase, $financeUser, 'Audit: Finance budget cleared for 6 months extension');
$ceCase->refresh();
echo "4. Forwarded to MD: status={$ceCase->ctc_status}, stage={$ceCase->currentSubstatus->css_stage}\n";

// 5. MD Approval
$approvalService->approve($ceCase, $mdUser, [
    'ctc_approvedstartdt'  => '2025-01-01',
    'ctc_approvedenddt'    => '2026-06-30',
    'ctc_approvedsalary'   => 150000.00,
    'ctc_approvedgrade'    => 'SPE',
    'ctc_approvedjobtitle' => 'Senior Project Engineer',
    'ctc_approvedctrtype'  => 1,
    'ctc_approvedunt_id'   => 300000,
], 'Audit: Extension approved by MD');
$ceCase->refresh();
echo "5. Approved by MD: status={$ceCase->ctc_status}, stage={$ceCase->currentSubstatus->css_stage}\n";

// 6. HR Fulfillment
$fulfillmentService->fulfill($ceCase, $hrUser, [
    'ctc_terminremarks' => 'Extension approved as per MD minutes dated 27-Aug-2026.',
]);
$ceCase->refresh();
echo "6. Fulfilled by HR: status={$ceCase->ctc_status}, stage={$ceCase->currentSubstatus->css_stage}, newctr_id=" . var_export($ceCase->ctc_newctr_id, true) . "\n\n";

echo "=== AUDIT WALKTHROUGH COMPLETE ===\n";
echo "Hg Case ID: {$hgId}\n";
echo "Ce Case ID: {$ceId}\n";
echo "Old Contract ID for Ce: {$oldContractId}\n";
