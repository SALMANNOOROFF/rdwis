<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$total = DB::table('pur.purcases')->count();
$withShd = DB::table('pur.purcases_shd')->distinct('pcd_pcs_id')->count('pcd_pcs_id');
$withItemShd = DB::table('pur.purcaseitems')->whereNotNull('pci_subhead')->where('pci_subhead', '!=', '')->distinct('pci_pcs_id')->count('pci_pcs_id');

$bothOrEither = DB::table('pur.purcases as c')
    ->leftJoin('pur.purcases_shd as shd', 'shd.pcd_pcs_id', '=', 'c.pcs_id')
    ->leftJoin('pur.purcaseitems as i', 'i.pci_pcs_id', '=', 'c.pcs_id')
    ->where(function($q) {
        $q->whereNotNull('shd.pcd_subhead')->where('shd.pcd_subhead', '!=', '')
          ->orWhere(function($q2) {
              $q2->whereNotNull('i.pci_subhead')->where('i.pci_subhead', '!=', '');
          });
    })
    ->distinct('c.pcs_id')
    ->count('c.pcs_id');

echo "Total cases: $total\n";
echo "With purcases_shd: $withShd\n";
echo "With item pci_subhead: $withItemShd\n";
echo "With either: $bothOrEither\n";

// Let's check recent 20 cases from year 2025/2026:
$recent = DB::table('pur.purcases')->orderBy('pcs_id', 'desc')->take(10)->get();
foreach ($recent as $r) {
    $pcd = DB::table('pur.purcases_shd')->where('pcd_pcs_id', $r->pcs_id)->pluck('pcd_subhead')->filter()->unique()->values()->all();
    $pci = DB::table('pur.purcaseitems')->where('pci_pcs_id', $r->pcs_id)->pluck('pci_subhead')->filter()->unique()->values()->all();
    echo "ID: {$r->pcs_id} | Date: {$r->pcs_date} | Title: {$r->pcs_title} | SHD: " . implode(',', $pcd) . " | ItemSHD: " . implode(',', $pci) . "\n";
}
