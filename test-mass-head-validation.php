<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;
use Illuminate\Support\Facades\DB;

$service = new FinancialIntelligenceService();

// Get a list of diverse head IDs to test
echo "=== Fetching Head IDs to Test ===\n";
$headIds = DB::table('cen.heads')
    ->orderBy('hed_id')
    ->limit(10) // Test up to 10 heads
    ->pluck('hed_id')
    ->toArray();

// Make sure HeadId 200012 is included
if (!in_array(200012, $headIds)) {
    array_unshift($headIds, 200012);
}

echo "Testing " . count($headIds) . " head IDs: " . implode(', ', $headIds) . "\n\n";

$results = [];
$allPassed = true;

foreach ($headIds as $headId) {
    echo str_repeat('=', 80) . "\n";
    echo "Testing Head ID: {$headId}\n";
    echo str_repeat('=', 80) . "\n";

    try {
        // Test getHeadStatus
        $status = $service->getHeadStatus($headId);
        echo "✅ getHeadStatus() passed\n";

        // Test getSubheadBreakdown
        $subheads = $service->getSubheadBreakdown($headId);
        echo "✅ getSubheadBreakdown() passed (" . count($subheads) . " subheads)\n";

        // Store results
        $results[] = [
            'head_id' => $headId,
            'head_name' => $status->head_name,
            'expenditure' => $status->expenditure,
            'commitments' => $status->commitments,
            'in_process' => $status->in_process,
            'available' => $status->available,
            'status' => 'PASS'
        ];

        echo "✅ Head ID {$headId} passed all tests\n\n";
    } catch (\Exception $e) {
        $allPassed = false;
        echo "❌ Head ID {$headId} FAILED: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n\n";

        $results[] = [
            'head_id' => $headId,
            'head_name' => 'Unknown',
            'expenditure' => null,
            'commitments' => null,
            'in_process' => null,
            'available' => null,
            'status' => 'FAIL - ' . $e->getMessage()
        ];
    }
}

// Print final summary
echo str_repeat('=', 80) . "\n";
echo "=== MASS VALIDATION SUMMARY ===\n";
echo str_repeat('=', 80) . "\n";

// Print table header
echo str_pad('Head ID', 10, ' ', STR_PAD_RIGHT) . " | " .
     str_pad('Head Name', 30, ' ', STR_PAD_RIGHT) . " | " .
     str_pad('Expenditure', 15, ' ', STR_PAD_LEFT) . " | " .
     str_pad('Commitments', 15, ' ', STR_PAD_LEFT) . " | " .
     str_pad('In Process', 15, ' ', STR_PAD_LEFT) . " | " .
     str_pad('Available', 15, ' ', STR_PAD_LEFT) . " | " .
     "Status\n";
echo str_repeat('-', 80) . "\n";

// Print table rows
foreach ($results as $row) {
    echo str_pad($row['head_id'], 10, ' ', STR_PAD_RIGHT) . " | " .
         str_pad(substr($row['head_name'], 0, 28), 30, ' ', STR_PAD_RIGHT) . " | " .
         (is_null($row['expenditure']) ? str_pad('N/A', 15, ' ', STR_PAD_LEFT) : str_pad(number_format($row['expenditure'], 2), 15, ' ', STR_PAD_LEFT)) . " | " .
         (is_null($row['commitments']) ? str_pad('N/A', 15, ' ', STR_PAD_LEFT) : str_pad(number_format($row['commitments'], 2), 15, ' ', STR_PAD_LEFT)) . " | " .
         (is_null($row['in_process']) ? str_pad('N/A', 15, ' ', STR_PAD_LEFT) : str_pad(number_format($row['in_process'], 2), 15, ' ', STR_PAD_LEFT)) . " | " .
         (is_null($row['available']) ? str_pad('N/A', 15, ' ', STR_PAD_LEFT) : str_pad(number_format($row['available'], 2), 15, ' ', STR_PAD_LEFT)) . " | " .
         $row['status'] . "\n";
}

echo str_repeat('=', 80) . "\n";
echo "OVERALL RESULT: " . ($allPassed ? "✅ ALL PASSED" : "❌ SOME FAILED") . "\n";
