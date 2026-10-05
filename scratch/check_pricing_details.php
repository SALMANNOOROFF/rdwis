<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrCtrCasePlan;

// Check case 38
$case38 = HrCtrCase::find(38);
echo "=== CASE 38 DETAILS ===" . PHP_EOL;
echo "ctc_id: {$case38->ctc_id}" . PHP_EOL;
echo "Dates: {$case38->ctc_newstartdt} to {$case38->ctc_newenddt}" . PHP_EOL;
echo "Salary: {$case38->ctc_newsalary}" . PHP_EOL;
echo "Probation months: {$case38->ctc_newprob}, Prob salary: {$case38->ctc_newprobsal}" . PHP_EOL;
echo "Stored ctc_price: {$case38->ctc_price}" . PHP_EOL;

$plans = HrCtrCasePlan::where('ccp_ctc_id', 38)->orderBy('ccp_startdt')->get();
echo "Plans count: " . $plans->count() . PHP_EOL;
foreach ($plans as $p) {
    echo "  Plan: {$p->ccp_startdt} to {$p->ccp_enddt}" . PHP_EOL;
}

// Check case 376
$case376 = HrCtrCase::find(376);
echo PHP_EOL . "=== CASE 376 DETAILS ===" . PHP_EOL;
echo "ctc_id: {$case376->ctc_id}" . PHP_EOL;
echo "Dates: {$case376->ctc_newstartdt} to {$case376->ctc_newenddt}" . PHP_EOL;
echo "Salary: {$case376->ctc_newsalary}" . PHP_EOL;
echo "Probation months: {$case376->ctc_newprob}, Prob salary: {$case376->ctc_newprobsal}" . PHP_EOL;
echo "Stored ctc_price: {$case376->ctc_price}" . PHP_EOL;
$plans376 = HrCtrCasePlan::where('ccp_ctc_id', 376)->orderBy('ccp_startdt')->get();
echo "Plans count: " . $plans376->count() . PHP_EOL;
foreach ($plans376 as $p) {
    echo "  Plan: {$p->ccp_startdt} to {$p->ccp_enddt}" . PHP_EOL;
}
