
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$headId = 200012;

echo "=== TEST/DUMMY PURCHASE CASES FOR HEAD ID: $headId ===\n\n";

$cases = DB::table('pur.purcases')->where('pcs_hed_id', $headId)->get();

echo "Total cases found: " . count($cases) . "\n\n";

$testCases = [];

foreach ($cases as $case) {
    $isTest = false;
    $reasons = [];

    // Check title for test-like patterns
    $title = strtolower($case->pcs_title);
    if (str_contains($title, 'test') || str_contains($title, 'testing') || str_contains($title, 'dsad') || str_contains($title, 'dummy') || str_contains($title, 'placeholder')) {
        $isTest = true;
        $reasons[] = "Title contains test-like pattern";
    }

    // Check if both pcs_frm_id and pcs_approvedtg are empty
    if (empty($case->pcs_frm_id) && empty($case->pcs_approvedtg)) {
        $isTest = true;
        $reasons[] = "pcs_frm_id and pcs_approvedtg both empty";
    }

    // Check remarks for placeholder-style JSON/text
    $remarks = strtolower($case->pcs_remarks ?? '');
    if (str_contains($remarks, '{') || str_contains($remarks, '}') || str_contains($remarks, 'placeholder')) {
        $isTest = true;
        $reasons[] = "Remarks appear to be placeholder-style";
    }

    if ($isTest) {
        $testCases[] = [
            'pcs_id' => $case->pcs_id,
            'pcs_title' => $case->pcs_title,
            'pcs_status' => $case->pcs_status,
            'pcs_price' => $case->pcs_price,
            'pcs_frm_id' => $case->pcs_frm_id,
            'pcs_approvedtg' => $case->pcs_approvedtg,
            'reasons' => $reasons
        ];
    }
}

if (count($testCases) > 0) {
    echo "--- POTENTIAL TEST/DUMMY CASES FOUND ---\n";
    foreach ($testCases as $tc) {
        echo "\n";
        echo "pcs_id: " . $tc['pcs_id'] . "\n";
        echo "pcs_title: " . $tc['pcs_title'] . "\n";
        echo "pcs_status: " . $tc['pcs_status'] . "\n";
        echo "pcs_price: " . $tc['pcs_price'] . "\n";
        echo "pcs_frm_id: " . ($tc['pcs_frm_id'] ?? '(empty)') . "\n";
        echo "pcs_approvedtg: " . ($tc['pcs_approvedtg'] ?? '(empty)') . "\n";
        echo "Reasons: " . implode(', ', $tc['reasons']) . "\n";
    }
} else {
    echo "No potential test/dummy cases found.\n";
}
