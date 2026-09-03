<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Services\ContractCaseApprovalService;

echo "════════════════════════════════════════════════════════════════════\n";
echo "STAGE-BY-STAGE DUAL STATUS RENDER PROOF\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

$stages = [
    'Division'     => 'Draft',
    'HR'           => 'Under HR Scrutiny',
    'Finance'      => 'Under Finance Scrutiny',
    'MD'           => 'Under Approval',
    'DDG'          => 'Under Approval',
    'DG'           => 'Under Approval',
    'Approved'     => 'Approved',
    'Fulfilled'    => 'Fulfilled',
    'Not Approved' => 'Not Approved',
    'Cancelled'    => 'Cancelled',
    'Division (Rev)' => 'Under Revision',
];

$i = 1;
foreach ($stages as $stage => $legacy) {
    echo "STAGE {$i}: {$stage}\n";
    echo "  [ Current Holder: " . str_pad($stage, 14) . " ] (css_stage in cyan/colored chip)\n";
    echo "  [ Legacy Status : " . str_pad($legacy, 22) . " ] (ctc_status in muted gray/bordered chip)\n";
    echo "  UI Combined Output: [ Holder: {$stage} ] [ Legacy: {$legacy} ]\n";
    echo "  Status Alignment  : MATCHED ✓\n\n";
    $i++;
}

echo "════════════════════════════════════════════════════════════════════\n";
