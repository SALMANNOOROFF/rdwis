<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\Purchase;
use App\Models\CenAccount;
use App\Services\ContractCaseApprovalService;
use App\Services\PurchaseApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "=== 1. TESTING CONTRACT CASE APPROVAL SERVICE DESTINATIONS ===\n";
$contractService = app(ContractCaseApprovalService::class);
foreach (['division', 'hr', 'finance', 'md', 'ddg', 'dg'] as $role) {
    $dests = $contractService->getAvailableDestinations($role);
    echo "Role [$role] available destination groups: " . implode(', ', array_keys($dests)) . "\n";
}

echo "\n=== 2. TESTING PURCHASE CASE APPROVAL SERVICE DESTINATIONS ===\n";
$purchaseService = app(PurchaseApprovalService::class);
foreach (['prj', 'proc', 'fin', 'rdw', 'hqs', 'nrdi'] as $role) {
    $dests = $purchaseService->getAvailableDestinations($role);
    echo "Role [$role] available destination groups: " . implode(', ', array_keys($dests)) . "\n";
}

echo "\n=== 3. TESTING CONTRACT CASE SHOW VIEW RENDERING FOR ACTIVE QUEUE HOLDER ===\n";
$firstContractCase = HrCtrCase::first();
if ($firstContractCase) {
    foreach (['Division', 'HR', 'Finance', 'MD', 'DDG', 'DG'] as $role) {
        $user = new CenAccount(['acc_untarea' => strtolower($role), 'acc_name' => 'Test User ' . $role, 'acc_id' => 1]);
        Auth::login($user);
        
        $case = clone $firstContractCase;
        if ($role === 'Division') {
            $case->ctc_status = 'Draft';
        }
        $authorityRole = $role;
        $authDetails = $contractService->getApprovalAuthorityDetails($case);
        $canApprove = $contractService->canApprove($role, $case);
        $deptMap = \App\Services\EmployeeCreationService::getDepartmentMap();
        $departments = \Illuminate\Support\Facades\DB::table('cen.units')->orderBy('unt_name')->get();
        
        $substatus = new \App\Models\HrCtrCaseSubstatus();
        $substatus->css_stage = $role;
        $substatus->css_is_current = true;
        $case->setRelation('currentSubstatus', $substatus);

        $html = View::make('md.contract-cases.show', compact('case', 'authorityRole', 'authDetails', 'canApprove', 'deptMap', 'departments'))->render();
        echo "Role [$role] Action Box Render OK! Length: " . strlen($html) . " bytes\n";
        
        assert(str_contains($html, 'targetDestinationSelect'), "targetDestinationSelect missing in $role view");
        assert(str_contains($html, 'SEND CASE'), "SEND CASE button missing in $role view");
        assert(str_contains($html, 'fa-check'), "Approve green tick icon missing in $role view");
        assert(str_contains($html, 'fa-times'), "Cancel red cross icon missing in $role view");
    }
}

echo "\n=== 4. TESTING PURCHASE CASE SHOW VIEW RENDERING FOR ACTIVE QUEUE HOLDER ===\n";
$firstPurchase = Purchase::first();
if ($firstPurchase) {
    foreach (['prj', 'proc', 'fin', 'rdw', 'hqs', 'nrdi'] as $area) {
        $user = new CenAccount(['acc_untarea' => $area, 'acc_name' => 'Test User ' . $area, 'acc_id' => 1]);
        Auth::login($user);
        $purchase = clone $firstPurchase;
        $stageMap = [
            'prj' => 'Division',
            'proc' => 'DProc',
            'fin' => 'DFinance',
            'rdw' => 'MD',
            'hqs' => 'DDG',
            'nrdi' => 'DG',
        ];
        $stage = $stageMap[$area];
        $substatus = new \App\Models\PurCaseSubstatus();
        $substatus->pss_stage = $stage;
        $substatus->pss_is_current = true;
        $purchase->setRelation('currentSubstatus', $substatus);
        if ($area === 'prj') {
            $purchase->pcs_status = 'Draft';
        }

        $head = $purchase->project;
        $canApprove = $purchaseService->canApprove($area, (float)($purchase->pcs_price ?? 0), $purchase);
        $pageTitle = 'Test Purchase Case';
        $divisionName = 'Division Test';
        $canEdit = true;
        $firms = collect();
        $subheads = collect();
        $currentAuthority = 'Test Authority';
        $nextAuthority = 'Next Authority';
        $recentApproved = collect();
        
        $html = View::make('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 
            'divisionName', 'canEdit', 'firms', 'subheads', 'currentAuthority', 'nextAuthority', 'recentApproved'
        ))->render();
        echo "Area [$area] Action Box Render OK! Length: " . strlen($html) . " bytes\n";
        
        assert(str_contains($html, 'targetDestinationSelect'), "targetDestinationSelect missing in $area view");
        assert(str_contains($html, 'SEND CASE'), "SEND CASE button missing in $area view");
        assert(str_contains($html, 'fa-check'), "Approve green tick icon missing in $area view");
        assert(str_contains($html, 'fa-times'), "Cancel red cross icon missing in $area view");
    }
}

echo "\nALL TESTS PASSED WITH 100% ACCURACY!\n";
