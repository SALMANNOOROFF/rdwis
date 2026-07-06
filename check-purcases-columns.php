<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get first row of pur.purcases
$firstRow = DB::table('pur.purcases')->first();
echo "pur.purcases columns from first row:\n";
print_r((array)$firstRow);
