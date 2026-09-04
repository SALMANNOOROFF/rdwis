<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PurchaseApprovalService;
use App\Services\ContractCaseApprovalService;
use App\Models\Purchase;
use App\Models\HrCtrCase;
use App\Models\CenAccount;
use App\Models\PurCaseSubstatus;
use App\Models\HrCtrCaseSubstatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "=======================================================\n";
echo "VERIFICATION TEST: USER REQUIREMENTS\n";
echo "=======================================================\n\n";

// 1. Check Purchase Destinations
$purService = app(PurchaseApprovalService::class);
$purDests = $purService->getAvailableDestinations('prj');
$purKeys = array_keys($purDests);

echo "1. PURCHASE DESTINATIONS CHECK:\n";
echo "   Total destinations: " . count($purKeys) . "\n";
echo "   Contains HR? " . (isset($purDests['HR']) ? "FAIL (HR found)" : "PASS (HR excluded)") . "\n";
echo "   Contains DProc? " . (isset($purDests['DProc']) ? "PASS" : "FAIL") . "\n";
echo "   Contains DFinance? " . (isset($purDests['DFinance']) ? "PASS" : "FAIL") . "\n";
echo "   Sample Director: " . ($purDests['DFinance']['director'] ?? 'N/A') . " (" . ($purDests['DFinance']['desig'] ?? '') . ")\n\n";

// 2. Check Contract Destinations
$ctrService = app(ContractCaseApprovalService::class);
$ctrDests = $ctrService->getAvailableDestinations('division');
$ctrKeys = array_keys($ctrDests);

echo "2. CONTRACT DESTINATIONS CHECK:\n";
echo "   Total destinations: " . count($ctrKeys) . "\n";
echo "   Contains DProc? " . (isset($ctrDests['DProc']) ? "FAIL (DProc found)" : "PASS (DProc excluded)") . "\n";
echo "   Contains HR? " . (isset($ctrDests['HR']) ? "PASS" : "FAIL") . "\n";
echo "   Contains Finance? " . (isset($ctrDests['Finance']) ? "PASS" : "FAIL") . "\n";
echo "   Sample Director: " . ($ctrDests['HR']['director'] ?? 'N/A') . " (" . ($ctrDests['HR']['desig'] ?? '') . ")\n\n";

// 3. Check Action Box View in Purchase Cases
echo "3. PURCHASE CASE ACTION BOX PERMISSION CHECK:\n";
$purchase = Purchase::with(['decisions'])->first();
if ($purchase) {
    // A. Test Non-approving user (Division / PRJ) with active stage
    $divUser = CenAccount::where('acc_untarea', 'prj')->first() ?? CenAccount::first();
    Auth::login($divUser);

    $substatusDiv = new PurCaseSubstatus(['pss_stage' => 'Division', 'pss_is_current' => true]);
    $purchase->setRelation('currentSubstatus', $substatusDiv);
    $purchase->pcs_status = 'Under Approval';
    $purchase->pcs_price = 500000;

    $htmlDiv = View::make('approvals_new._action_box', [
        'purchase' => $purchase,
        'area' => 'prj',
        'isCaseWithMe' => true
    ])->render();

    $divHasSend = str_contains($htmlDiv, 'id="btnForward"') && str_contains($htmlDiv, 'SEND CASE');
    $divHasApprove = str_contains($htmlDiv, 'id="btnApprove"');
    $divHasCancel = str_contains($htmlDiv, 'id="btnCancel"');
    echo "   Division User (Non-Approving, active in queue):\n";
    echo "     - SEND CASE button present: " . ($divHasSend ? "PASS" : "FAIL") . "\n";
    echo "     - APPROVE button excluded: " . (!$divHasApprove ? "PASS" : "FAIL") . "\n";
    echo "     - REJECT/CANCEL button excluded: " . (!$divHasCancel ? "PASS" : "FAIL") . "\n";

    // B. Test Approving user (DG / NRDI) with active stage
    $dgUser = CenAccount::where('acc_untarea', 'nrdi')->first() ?? CenAccount::first();
    Auth::login($dgUser);

    $substatusDg = new PurCaseSubstatus(['pss_stage' => 'DG', 'pss_is_current' => true]);
    $purchase->setRelation('currentSubstatus', $substatusDg);

    $htmlDg = View::make('approvals_new._action_box', [
        'purchase' => $purchase,
        'area' => 'nrdi',
        'isCaseWithMe' => true
    ])->render();

    $dgHasSend = str_contains($htmlDg, 'id="btnForward"') && str_contains($htmlDg, 'SEND CASE');
    $dgHasApprove = str_contains($htmlDg, 'id="btnApprove"');
    $dgHasCancel = str_contains($htmlDg, 'id="btnCancel"');
    $dgHasMenu = str_contains($htmlDg, 'pcDestDropdownMenu');
    $dgHasSearch = str_contains($htmlDg, 'pcDestSearchInput');
    echo "   DG User (Approving Authority, active in queue):\n";
    echo "     - SEND CASE button present: " . ($dgHasSend ? "PASS" : "FAIL") . "\n";
    echo "     - APPROVE button present: " . ($dgHasApprove ? "PASS" : "FAIL") . "\n";
    echo "     - REJECT/CANCEL button present: " . ($dgHasCancel ? "PASS" : "FAIL") . "\n";
    echo "     - Downward menu present: " . ($dgHasMenu ? "PASS" : "FAIL") . "\n";
    echo "     - Search box present: " . ($dgHasSearch ? "PASS" : "FAIL") . "\n";
} else {
    echo "   No purchase case found to test.\n";
}
echo "\n";

// 4. Check Contract Case View Action Box Permissions
echo "4. CONTRACT CASE ACTION BOX PERMISSION CHECK:\n";
$case = HrCtrCase::with(['remarksHistory'])->first();
if ($case) {
    // A. HR User (Non-Approving, active in queue)
    $case->ctc_status = 'Under Scrutiny';
    $substatusHr = new HrCtrCaseSubstatus(['css_stage' => 'HR', 'css_status' => 'Under Scrutiny']);
    $case->setRelation('currentSubstatus', $substatusHr);

    $hrUser = CenAccount::where('acc_untarea', 'hr')->first() ?? CenAccount::first();
    Auth::login($hrUser);

    $htmlHr = View::make('md.contract-cases.show', [
        'case' => $case,
        'role' => 'HR',
        'authorityRole' => 'HR',
        'authDetails' => ['can_md_approve' => false, 'can_ddg_approve' => false],
        'canApprove' => false,
        'recentCases' => collect(),
        'projectAttachments' => collect(),
        'caseAttachments' => collect(),
        'allocatedGroups' => collect(),
        'proposedSalary' => 100000,
        'previousSalary' => 90000,
        'salaryDiff' => 10000,
        'incrementPct' => 11.1,
        'annualImpact' => 1200000,
        'empName' => 'Test Candidate',
        'empDesignation' => 'Software Engineer',
        'empGrade' => 'O-2',
        'prevJobtitle' => 'Junior Engineer',
        'prevGrade' => 'O-1',
        'prevStart' => null,
        'prevEnd' => null,
        'projectCode' => 'RDW-01',
    ])->render();

    $hrHasSend = str_contains($htmlHr, 'id="btnForward"') && str_contains($htmlHr, 'SEND CASE');
    $hrHasApprove = str_contains($htmlHr, 'id="btnApprove"');
    $hrHasCancel = str_contains($htmlHr, 'id="btnCancel"');
    $hrHasAddAtt = str_contains($htmlHr, 'data-target="#modalAddCaseAttachment"');
    echo "   HR User (Non-Approving, active in queue):\n";
    echo "     - SEND CASE button present: " . ($hrHasSend ? "PASS" : "FAIL") . "\n";
    echo "     - APPROVE button excluded: " . (!$hrHasApprove ? "PASS" : "FAIL") . "\n";
    echo "     - REJECT/CANCEL button excluded: " . (!$hrHasCancel ? "PASS" : "FAIL") . "\n";
    echo "     - Case Attachments (+) button present: " . ($hrHasAddAtt ? "PASS" : "FAIL") . "\n";

    // B. DG User (Approving Authority, active in queue)
    $substatusDg = new HrCtrCaseSubstatus(['css_stage' => 'DG', 'css_status' => 'Under Approval']);
    $case->setRelation('currentSubstatus', $substatusDg);

    $dgUser = CenAccount::where('acc_untarea', 'nrdi')->first() ?? CenAccount::first();
    Auth::login($dgUser);

    $htmlDg = View::make('md.contract-cases.show', [
        'case' => $case,
        'role' => 'DG',
        'authorityRole' => 'DG',
        'authDetails' => ['can_md_approve' => false, 'can_ddg_approve' => false],
        'canApprove' => true,
        'recentCases' => collect(),
        'projectAttachments' => collect(),
        'caseAttachments' => collect(),
        'allocatedGroups' => collect(),
        'proposedSalary' => 100000,
        'previousSalary' => 90000,
        'salaryDiff' => 10000,
        'incrementPct' => 11.1,
        'annualImpact' => 1200000,
        'empName' => 'Test Candidate',
        'empDesignation' => 'Software Engineer',
        'empGrade' => 'O-2',
        'prevJobtitle' => 'Junior Engineer',
        'prevGrade' => 'O-1',
        'prevStart' => null,
        'prevEnd' => null,
        'projectCode' => 'RDW-01',
    ])->render();

    $dgHasSend = str_contains($htmlDg, 'id="btnForward"') && str_contains($htmlDg, 'SEND CASE');
    $dgHasApprove = str_contains($htmlDg, 'id="btnApprove"');
    $dgHasCancel = str_contains($htmlDg, 'id="btnCancel"');
    $dgHasMenu = str_contains($htmlDg, 'ccDestDropdownMenu');
    $dgHasSearch = str_contains($htmlDg, 'ccDestSearchInput');
    echo "   DG User (Approving Authority, active in queue):\n";
    echo "     - SEND CASE button present: " . ($dgHasSend ? "PASS" : "FAIL") . "\n";
    echo "     - APPROVE button present: " . ($dgHasApprove ? "PASS" : "FAIL") . "\n";
    echo "     - REJECT/CANCEL button present: " . ($dgHasCancel ? "PASS" : "FAIL") . "\n";
    echo "     - Downward menu present: " . ($dgHasMenu ? "PASS" : "FAIL") . "\n";
    echo "     - Search box present: " . ($dgHasSearch ? "PASS" : "FAIL") . "\n";
} else {
    echo "   No contract case found to test.\n";
}

echo "\n=======================================================\n";
echo "ALL CHECKS COMPLETED!\n";
echo "=======================================================\n";
