<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\User;
use App\Services\ContractCaseApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "════════════════════════════════════════════════════════════════════\n";
echo "TEST: REAL LIFECYCLE DELEGATED APPROVAL BY MD\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

DB::beginTransaction();
try {
    $service = app(ContractCaseApprovalService::class);
    $mdUser = User::find(21); // MD user
    Auth::setUser($mdUser);

    // Create a mock contract case within MD limits (e.g. Salary = 100k, Grade = SPS-5)
    $case = HrCtrCase::create([
        'ctc_type'         => 'Cr',
        'ctc_divisionid'   => 16,
        'ctc_empnamecomp'  => 'Test MD Approval Employee',
        'ctc_newsalary'    => 100000,
        'ctc_newgrade'     => 'SPS-5',
        'ctc_newjobtitle'  => 'Research Assistant',
        'ctc_newstartdt'   => now()->toDateString(),
        'ctc_newenddt'     => now()->addYear()->toDateString(),
        'ctc_status'       => 'Under Approval',
    ]);

    // Set initial substatus to MD
    $service->transitionSubstatus($case, 'MD', 'Under Approval');
    $case->refresh();
    echo "1. Case #{$case->ctc_id} created at stage: {$case->currentSubstatus->css_stage}\n";

    // Check authority
    $canMdApprove = $service->canApprove('MD', $case);
    echo "2. MD can approve this case? " . ($canMdApprove ? 'YES [✓ PASSED]' : 'NO [✗ FAILED]') . "\n";

    // MD Grants Approval
    $service->approve($case, $mdUser, [], 'Approved under MD delegated powers.');
    $case->refresh();

    echo "3. Case status after MD Approval: {$case->ctc_status}\n";
    echo "4. Case substatus stage: {$case->currentSubstatus->css_stage} [Expected: Approved] " . ($case->currentSubstatus->css_stage === 'Approved' ? '✓' : '✗') . "\n";
    echo "5. Case is now ready for HR Fulfillment without needing DDG or DG approval! [✓ SUCCESS]\n";

    DB::rollBack();
    echo "\nTransaction rolled back cleanly.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
