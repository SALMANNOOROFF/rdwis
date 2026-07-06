<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;

$service = new FinancialIntelligenceService();

// Get some real HeadIds to test
echo "Testing accounting calculations...\n\n";
echo "Enter HeadIds to test (comma separated): ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
$headIds = array_map('trim', explode(',', $line));
fclose($handle);

if (empty($headIds)) {
    $headIds = [1, 2, 3, 4, 5]; // Default test IDs
}

echo "\n---\n";
echo "Testing HeadIds: " . implode(', ', $headIds) . "\n";
echo "---\n\n";

foreach ($headIds as $headId) {
    echo "Head ID: $headId\n";
    echo str_repeat("-", 50) . "\n";
    
    $status = $service->getHeadStatus($headId);
    $subheads = $service->getSubheadBreakdown($headId);
    $loans = $service->getLoans($headId);
    
    echo "  Allocation: " . $status->allocation . "\n";
    echo "  MTSS Share: " . $status->mtss_share . "\n";
    echo "  Acc Share: " . $status->acc_share . "\n";
    echo "  Pcc Share: " . $status->pcc_share . "\n";
    echo "  Cf Share: " . $status->cf_share . "\n";
    echo "  Prj Share: " . $status->prj_share . "\n";
    echo "  Received: " . $status->received . "\n";
    echo "  Expenditure: " . $status->expenditure . "\n";
    echo "  Commitments: " . $status->commitments . "\n";
    echo "  In Process: " . $status->in_process . "\n";
    echo "  Balance: " . $status->balance . "\n";
    echo "  Available: " . $status->available . "\n";
    echo "  Yet To Be Received: " . $status->yet_to_be_received . "\n";
    echo "  Can Be Spent: " . $status->can_be_spent . "\n";
    echo "  Pcc Loans Given: " . $loans->pcc_loans_given . "\n";
    echo "  Others Loans Taken: " . $loans->others_loans_taken . "\n";
    
    echo "\n  Subheads:\n";
    foreach ($subheads as $sh) {
        echo "    {$sh['name']}: Allocation={$sh['allocation']}, Expenditure={$sh['expenditure']}, Commitments={$sh['commitments']}, InProcess={$sh['in_process']}, CanBeSpent={$sh['can_be_spent']}\n";
    }
    
    echo "\n";
}

echo "\n---\n";
echo "Test completed!\n";
echo "---\n";
