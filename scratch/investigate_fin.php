<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use Illuminate\Support\Facades\DB;
use App\Services\FinancialIntelligenceService;

echo "=== CASE 375 FINANCIAL INVESTIGATION ===" . PHP_EOL;
$headId = 200002;
$subheads = DB::table('fin.subheads')->where('sbh_hed_id', $headId)->get();
echo "fin.subheads count: " . $subheads->count() . PHP_EOL;
foreach ($subheads as $sh) {
    echo "  Subhead: " . json_encode($sh) . PHP_EOL;
}
$finService = app(FinancialIntelligenceService::class);
$breakdown = $finService->getSubheadBreakdown($headId);
echo "FinancialIntelligenceService::getSubheadBreakdown({$headId}): " . json_encode($breakdown, JSON_PRETTY_PRINT) . PHP_EOL;

echo PHP_EOL . "=== SEARCHING FOR A CASE WITH REAL SUBHEAD ALLOCATIONS ===" . PHP_EOL;
$realSubheads = DB::table('fin.subheads')
    ->where('sbh_alloc', '>', 0)
    ->get();

echo "Subheads with alloc > 0 count: " . $realSubheads->count() . PHP_EOL;
foreach ($realSubheads->take(10) as $rsh) {
    echo "  Head ID: {$rsh->sbh_hed_id}, Name: {$rsh->sbh_name}, Alloc: {$rsh->sbh_alloc}" . PHP_EOL;
    $case = HrCtrCase::where('ctc_prj_id', $rsh->sbh_hed_id)
        ->orWhereHas('casePlans', function($q) use ($rsh) {
            $q->where('ccp_hed_id', $rsh->sbh_hed_id);
        })->first();
    if ($case) {
        echo "    -> Found matching case! Case ID: {$case->ctc_id}, Type: {$case->ctc_type}, Job: {$case->ctc_newjobtitle}" . PHP_EOL;
    }
}
