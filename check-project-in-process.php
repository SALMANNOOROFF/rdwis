<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$headId = 200012;

echo "Checking Project In Process records for HeadId: $headId\n";

$records = DB::table('pur.purcases')
    ->select('pcs_id', 'pcs_title', 'pcs_status', 'pcs_price')
    ->where('pcs_hed_id', $headId)
    ->where(function($query) {
        $query->where(DB::raw('LOWER(pcs_status)'), 'finance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'with finance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'with dfinance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'audit')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'command')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'approved_pending_commit');
    })
    ->get();

echo "Found " . count($records) . " records:\n";
foreach ($records as $r) {
    echo "  ID: {$r->pcs_id}, Title: {$r->pcs_title}, Status: {$r->pcs_status}, Price: " . number_format($r->pcs_price, 2) . "\n";
}

$total = array_sum(array_column($records, 'pcs_price'));
echo "Total Project In Process: " . number_format($total, 2) . "\n";

echo "\n--- What does the DIAGNOSTIC_REPORT say? ---\n";
echo "Let's check what's in DIAGNOSTIC_REPORT_200012.md:\n";
echo file_get_contents(__DIR__ . '/DIAGNOSTIC_REPORT_200012.md');
