<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rbCaseIds = DB::table('pur.purcases')->where('pcs_type', 'Rb')->pluck('pcs_id')->all();
if (!empty($rbCaseIds)) {
    $updated = DB::table('pur.purcaseitems')
        ->whereIn('pci_pcs_id', $rbCaseIds)
        ->where('pci_qtyunit', 'num')
        ->update(['pci_qtyunit' => 'Days']);
    echo "Updated {$updated} TA/DA items unit from 'num' to 'Days'.\n";
} else {
    echo "No Rb cases found.\n";
}
