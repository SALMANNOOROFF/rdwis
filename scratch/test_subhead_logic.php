<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;

$sampleCases = Purchase::orderBy('pcs_id', 'desc')->take(15)->get();
foreach ($sampleCases as $c) {
    // 1. Check pur.purcases_shd
    $shd = \Illuminate\Support\Facades\DB::table('pur.purcases_shd')
        ->where('pcd_pcs_id', $c->pcs_id)
        ->whereNotNull('pcd_subhead')
        ->where('pcd_subhead', '!=', '')
        ->pluck('pcd_subhead')
        ->unique()
        ->values()
        ->all();

    $itemShds = \Illuminate\Support\Facades\DB::table('pur.purcaseitems')
        ->where('pci_pcs_id', $c->pcs_id)
        ->whereNotNull('pci_subhead')
        ->where('pci_subhead', '!=', '')
        ->pluck('pci_subhead')
        ->unique()
        ->values()
        ->all();

    $resolved = !empty($shd) ? implode(', ', $shd) : (!empty($itemShds) ? implode(', ', $itemShds) : 'General / Equipment');
    echo "#{$c->pcs_id} | Type: {$c->pcs_type} | Resolved Subhead: {$resolved}\n";
}
