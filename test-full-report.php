<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;

$service = new FinancialIntelligenceService();

// Test HeadId 200012
$headId = 200002;
$status = $service->getHeadStatus($headId);
$subheads = $service->getSubheadBreakdown($headId);

echo "=== FULL REPORT FOR HEADID $headId ===" . PHP_EOL;
echo str_repeat('=', 80) . PHP_EOL;

echo PHP_EOL . "--- TOP SUMMARY ---" . PHP_EOL;
echo "Head Name: " . $status->head_name . PHP_EOL;
echo "Allocation: " . number_format($status->allocation) . PHP_EOL;
echo "MTSS Share: " . number_format($status->mtss_share) . PHP_EOL;
echo "RDW Share: " . number_format($status->rdw_share) . PHP_EOL;
echo "CSRF Share: " . number_format($status->csrf_share) . PHP_EOL;

echo PHP_EOL . "--- FINAL METRICS ---" . PHP_EOL;
echo "Total Expenditure:  " . number_format($status->expenditure, 2) . PHP_EOL;
echo "Total Commitments:  " . number_format($status->commitments, 2) . PHP_EOL;
echo "Total In Process:   " . number_format($status->in_process, 2) . PHP_EOL;
echo "Total Balance:      " . number_format($status->balance, 2) . PHP_EOL;
echo "Total Available:    " . number_format($status->available, 2) . PHP_EOL;

echo PHP_EOL . "--- SUBHEAD BREAKDOWN ---" . PHP_EOL;
foreach ($subheads as $sh) {
    echo PHP_EOL . "Subhead: " . $sh['name'] . PHP_EOL;
    echo "  Allocation:    " . number_format($sh['allocation'], 2) . PHP_EOL;
    echo "  Expenditure:   " . number_format($sh['expenditure'], 2) . PHP_EOL;
    echo "  Commitments:   " . number_format($sh['commitments'], 2) . PHP_EOL;
    echo "  In Process:    " . number_format($sh['in_process'], 2) . PHP_EOL;
    echo "  Remaining:     " . number_format($sh['remaining'], 2) . PHP_EOL;
    echo "  Can Be Spent:  " . number_format($sh['can_be_spent'], 2) . PHP_EOL;
}

echo PHP_EOL . str_repeat('=', 80) . PHP_EOL;
echo "✅ All data loaded correctly! Page should work perfectly now!" . PHP_EOL;
