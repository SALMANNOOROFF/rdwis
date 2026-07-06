<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;

$service = new FinancialIntelligenceService();

// Test HeadId 200012
$headId = 200012;
$status = $service->getHeadStatus($headId);

echo "All properties on status object:\n";
print_r(array_keys((array)$status));

echo "\n=== Testing view‑required properties ===\n";
$viewProps = ['allocation', 'mtss_share', 'rdw_share', 'csrf_share', 'received', 'expenditure', 'balance', 'commitments', 'in_process', 'available', 'yet_to_be_received', 'can_be_spent'];
foreach ($viewProps as $p) {
    $exists = property_exists($status, $p);
    echo "  $p: " . ($exists ? "✅ exists" : "❌ missing") . "\n";
    if ($exists) {
        echo "    Value: " . var_export($status->$p, true) . "\n";
    }
}
