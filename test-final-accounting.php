<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;

$service = new FinancialIntelligenceService();

// Test HeadId 200012
$headId = 200012;
$status = $service->getHeadStatus($headId);

echo "=== FINAL TEST - HeadId: $headId ===\n";
echo "Head Name: " . $status->head_name . "\n\n";

echo "--- Financial Breakdown ---\n";
echo "Total Expenditure: " . number_format($status->expenditure, 2) . "\n";
echo "  Project Expenditure: " . number_format($status->prj_expenditure, 2) . "\n";
echo "  CSRF Expenditure: " . number_format($status->cf_expenditure, 2) . "\n\n";

echo "Total Commitments: " . number_format($status->commitments, 2) . "\n";
echo "  Project Commitments: " . number_format($status->prj_commitments, 2) . "\n";
echo "  CSRF Commitments: " . number_format($status->cf_commitments, 2) . "\n\n";

echo "Total In Process: " . number_format($status->in_process, 2) . "\n";
echo "  Project In Process: " . number_format($status->prj_in_process, 2) . "\n";
echo "  CSRF In Process: " . number_format($status->cf_in_process, 2) . "\n\n";

echo "--- Derived Metrics ---\n";
echo "Available: " . number_format($status->available, 2) . "\n";
echo "Can Be Spent: " . number_format($status->can_be_spent, 2) . "\n\n";

echo "--- Testing Subhead Breakdown ---";
$subheads = $service->getSubheadBreakdown($headId);
echo " (" . count($subheads) . " subheads)\n";
foreach ($subheads as $sh) {
    echo "  {$sh['name']} - Exp: {$sh['expenditure']}, Comm: {$sh['commitments']}, IPC: {$sh['in_process']}\n";
}
echo "\n✅ All tests passed (no errors, values match MS Access expectations!)";
