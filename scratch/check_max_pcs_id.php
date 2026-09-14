<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$max = DB::table('pur.purcases')->max('pcs_id');
echo "Max pcs_id: $max\n";

$recent = DB::table('pur.purcases')->orderByDesc('pcs_id')->limit(10)->get(['pcs_id', 'pcs_title', 'pcs_date', 'pcs_type', 'pcs_status']);
foreach ($recent as $r) {
    echo "ID: {$r->pcs_id} | Date: {$r->pcs_date} | Type: {$r->pcs_type} | Status: {$r->pcs_status} | Title: {$r->pcs_title}\n";
}
