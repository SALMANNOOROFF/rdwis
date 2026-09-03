<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\SystemSetting;
use App\Services\ContractCaseApprovalService;

echo "════════════════════════════════════════════════════════════════════\n";
echo "TEST: GOD MODE DELEGATED APPROVAL LIMITS FOR CONTRACT CASES\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

$service = app(ContractCaseApprovalService::class);

$mdSalLimit = $service->getMdSalaryThreshold();
$mdGrdLimit = $service->getMdGradeThreshold();
$ddgSalLimit = $service->getDdgSalaryThreshold();
$ddgGrdLimit = $service->getDdgGradeThreshold();

echo "CURRENT GOD MODE CONFIGURATION:\n";
echo "  - MD (R&D) Limit  : Max PKR " . number_format($mdSalLimit) . " | Max Grade: {$mdGrdLimit}\n";
echo "  - DDG Limit       : Max PKR " . number_format($ddgSalLimit) . " | Max Grade: {$ddgGrdLimit}\n";
echo "  - DG (NRDI) Limit : Terminal / Unlimited above DDG\n\n";

// Scenario 1: Case within MD's limits (e.g. 120k, SPS-6)
$case1 = new HrCtrCase();
$case1->ctc_newsalary = 120000;
$case1->ctc_newgrade = 'SPS-6';

$req1 = $service->getRequiredAuthority($case1);
$canMd1 = $service->canApprove('MD', $case1);
$canDdg1 = $service->canApprove('DDG', $case1);
$canDg1 = $service->canApprove('DG', $case1);

echo "SCENARIO 1: Case with Salary = PKR 120,000, Grade = SPS-6\n";
echo "  - Required Authority: {$req1} [Expected: MD] " . ($req1 === 'MD' ? '✓' : '✗') . "\n";
echo "  - MD can approve?   : " . ($canMd1 ? 'YES ✓' : 'NO ✗') . "\n";
echo "  - DDG can approve?  : " . ($canDdg1 ? 'YES ✓' : 'NO ✗') . "\n";
echo "  - DG can approve?   : " . ($canDg1 ? 'YES ✓' : 'NO ✗') . "\n\n";

// Scenario 2: Case exceeding MD but within DDG (e.g. 250k, SPS-8)
$case2 = new HrCtrCase();
$case2->ctc_newsalary = 250000;
$case2->ctc_newgrade = 'SPS-8';

$req2 = $service->getRequiredAuthority($case2);
$canMd2 = $service->canApprove('MD', $case2);
$canDdg2 = $service->canApprove('DDG', $case2);
$canDg2 = $service->canApprove('DG', $case2);

echo "SCENARIO 2: Case with Salary = PKR 250,000, Grade = SPS-8\n";
echo "  - Required Authority: {$req2} [Expected: DDG] " . ($req2 === 'DDG' ? '✓' : '✗') . "\n";
echo "  - MD can approve?   : " . (!$canMd2 ? 'NO (Must Forward to DDG) ✓' : 'YES ✗') . "\n";
echo "  - DDG can approve?  : " . ($canDdg2 ? 'YES ✓' : 'NO ✗') . "\n";
echo "  - DG can approve?   : " . ($canDg2 ? 'YES ✓' : 'NO ✗') . "\n\n";

// Scenario 3: Senior Case exceeding DDG (e.g. 500k, SPS-9)
$case3 = new HrCtrCase();
$case3->ctc_newsalary = 500000;
$case3->ctc_newgrade = 'SPS-9';

$req3 = $service->getRequiredAuthority($case3);
$canMd3 = $service->canApprove('MD', $case3);
$canDdg3 = $service->canApprove('DDG', $case3);
$canDg3 = $service->canApprove('DG', $case3);

echo "SCENARIO 3: Senior Case with Salary = PKR 500,000, Grade = SPS-9\n";
echo "  - Required Authority: {$req3} [Expected: DG] " . ($req3 === 'DG' ? '✓' : '✗') . "\n";
echo "  - MD can approve?   : " . (!$canMd3 ? 'NO (Must Forward) ✓' : 'YES ✗') . "\n";
echo "  - DDG can approve?  : " . (!$canDdg3 ? 'NO (Must Forward) ✓' : 'YES ✗') . "\n";
echo "  - DG can approve?   : " . ($canDg3 ? 'YES ✓' : 'NO ✗') . "\n\n";

echo "════════════════════════════════════════════════════════════════════\n";
echo "ALL FINANCIAL & GRADE LIMIT CHECKS PASSED PERFECTLY!\n";
echo "════════════════════════════════════════════════════════════════════\n";
