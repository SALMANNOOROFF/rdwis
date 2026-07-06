<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking pur.purcases ID 1492 in detail:\n";
$record = DB::table('pur.purcases')->where('pcs_id', 1492)->first();
print_r($record);
