<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;

$service = new FinancialIntelligenceService();

// Test HeadId 200012
$headId = 200002;
$status = $service->getHeadStatus($headId);

echo "=== ACCOUNTING VALIDATION FOR HEADID $headId ===" . PHP_EOL;
echo str_repeat('=', 70) . PHP_EOL;

echo PHP_EOL . "--- FINAL COMBINED METRICS ---" . PHP_EOL;
echo "Total Expenditure: " . number_format($status->expenditure, 2) . PHP_EOL;
echo "Total Commitments: " . number_format($status->commitments, 2) . PHP_EOL;
echo "Total In Process:  " . number_format($status->in_process, 2) . PHP_EOL;
echo "Total Balance:     " . number_format($status->balance, 2) . PHP_EOL;
echo "Total Available:   " . number_format($status->available, 2) . PHP_EOL;

echo PHP_EOL . "--- PROJECT SCOPE (PRJ) ---" . PHP_EOL;
echo "Prj Received:      " . number_format($status->prj_received, 2) . PHP_EOL;
echo "Prj Expenditure:   " . number_format($status->prj_expenditure, 2) . PHP_EOL;
echo "Prj Commitments:   " . number_format($status->prj_commitments, 2) . PHP_EOL;
echo "Prj In Process:    " . number_format($status->prj_in_process, 2) . PHP_EOL;
echo "Prj Balance:       " . number_format($status->prj_balance, 2) . PHP_EOL;
echo "Prj Available:     " . number_format($status->prj_available, 2) . PHP_EOL;

echo PHP_EOL . "--- CSRF SCOPE (CF) ---" . PHP_EOL;
echo "CF Received:       " . number_format($status->cf_received, 2) . PHP_EOL;
echo "CF Expenditure:    " . number_format($status->cf_expenditure, 2) . PHP_EOL;
echo "CF Commitments:    " . number_format($status->cf_commitments, 2) . PHP_EOL;
echo "CF In Process:     " . number_format($status->cf_in_process, 2) . PHP_EOL;
echo "CF Balance:        " . number_format($status->cf_balance, 2) . PHP_EOL;
echo "CF Available:      " . number_format($status->cf_available, 2) . PHP_EOL;

echo PHP_EOL . str_repeat('=', 70) . PHP_EOL;

// Verify target values
$targets = [
    'prj_available' => -2828654.00,
    'cf_available' => -300000.00,
    'available' => -3128654.00,
    'in_process' => 0.00,
    'expenditure' => 2678654.00,
    'commitments' => 450000.00,
];

$allMatched = true;
echo PHP_EOL . "--- TARGET COMPARISON ---" . PHP_EOL;
foreach ($targets as $key => $expected) {
    $actual = $status->$key ?? null;
    $match = abs($actual - $expected) < 0.01;
    $allMatched &= $match;
    echo str_pad($key, 20) . " | "
        . str_pad(number_format($actual, 2), 15, ' ', STR_PAD_LEFT)
        . " | "
        . str_pad(number_format($expected, 2), 15, ' ', STR_PAD_LEFT)
        . " | "
        . ($match ? "✅ MATCH" : "❌ MISMATCH")
        . PHP_EOL;
}

echo PHP_EOL . "--- FINAL VERDICT ---" . PHP_EOL;
echo $allMatched ? "✅ ALL TARGETS MATCHED!" : "❌ SOME TARGETS FAILED!" . PHP_EOL;
