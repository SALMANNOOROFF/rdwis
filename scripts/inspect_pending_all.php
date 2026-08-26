<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CenAccount;
use App\Models\Purchase;
use App\Models\HrCtrCase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== INSPECTING CASES PER ROLE ===\n\n";

// 1. Check all pending purchase cases
echo "--- ALL NON-FULFILLED PURCHASE CASES ---\n";
$pcs = Purchase::whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
    ->select('pcs_id', 'pcs_status', 'pcs_type', 'pcs_price', 'pcs_unt_id')
    ->get();
echo "Total Active PCs: " . $pcs->count() . "\n";
foreach ($pcs->groupBy('pcs_status') as $st => $group) {
    echo "  Status '{$st}': " . $group->count() . " cases\n";
    foreach ($group->take(3) as $g) {
        echo "    - PC#{$g->pcs_id} unt={$g->pcs_unt_id} type={$g->pcs_type} price={$g->pcs_price}\n";
    }
}

// 2. Check all pending contract cases
echo "\n--- ALL NON-CLOSED CONTRACT CASES ---\n";
$ccs = HrCtrCase::whereNotIn('ctc_status', ['Fulfilled', 'Closed', 'Rejected', 'Cancelled'])
    ->select('ctc_id', 'ctc_status', 'ctc_empnamecomp', 'ctc_unt_id')
    ->get();
echo "Total Active Contract Cases: " . $ccs->count() . "\n";
foreach ($ccs->groupBy('ctc_status') as $st => $group) {
    echo "  Status '{$st}': " . $group->count() . " cases\n";
    foreach ($group->take(3) as $g) {
        echo "    - CC#{$g->ctc_id} unt={$g->ctc_unt_id} emp={$g->ctc_empnamecomp}\n";
    }
}

// 3. Check what Procurement Dashboard queries for Purchase Cases
echo "\n--- PROCUREMENT PURCHASE CASES QUERY ---\n";
$procUser = CenAccount::where('acc_untarea', 'proc')->first() ?? CenAccount::where('acc_untarea', 'prc')->first();
if ($procUser) {
    echo "Procurement user: {$procUser->acc_username} ({$procUser->acc_untarea})\n";
    // Check Procurement controller
}

// 4. Check what MD Dashboard queries for Contract Cases
echo "\n--- MD CONTRACT CASES QUERY ---\n";
$mdCases = HrCtrCase::whereIn('ctc_status', ['Under Approval'])->get();
echo "MD Under Approval Contract Cases count: " . $mdCases->count() . "\n";
foreach ($mdCases as $mc) {
    echo "  - CC#{$mc->ctc_id}: {$mc->ctc_empnamecomp} status={$mc->ctc_status}\n";
}

// 5. Check Expiring Contracts for HR / Emps
echo "\n--- EXPIRING CONTRACTS ---\n";
$threshold = Carbon::today()->addDays(45);
$expiringEmps = DB::table('hr.contracts as c')
    ->join('hr.emps as e', 'e.emp_id', '=', 'c.ctr_num')
    ->whereIn(DB::raw('LOWER(e.emp_status)'), ['active', 'current'])
    ->where('c.ctr_enddt', '<=', $threshold->toDateString())
    ->select('c.ctr_num', 'e.emp_name', 'c.ctr_enddt')
    ->get();
echo "Expiring Contracts (<= 45 days or expired while active): " . $expiringEmps->count() . "\n";
