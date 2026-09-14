<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$cases = DB::table('pur.purcases')
    ->where('pcs_title', 'ILIKE', '%LED%')
    ->orWhere('pcs_title', 'ILIKE', '%monitor%')
    ->orWhere('pcs_title', 'ILIKE', '%IGS%')
    ->select('pcs_id', 'pcs_title', 'pcs_status', 'pcs_type')
    ->get();

echo "Found cases:\n";
foreach ($cases as $c) {
    echo "ID: {$c->pcs_id} | Type: {$c->pcs_type} | Status: {$c->pcs_status} | Title: {$c->pcs_title}\n";
}
