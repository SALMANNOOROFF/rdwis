<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['emps', 'empsexta', 'empsextb', 'empsextc', 'qualifs', 'jobs', 'vehicles', 'devices', 'bnkaccounts'];

foreach ($tables as $tbl) {
    echo "\n=== HR.$tbl COLUMNS ===\n";
    $columns = DB::select("
        SELECT column_name, data_type, character_maximum_length 
        FROM information_schema.columns 
        WHERE table_schema = 'hr' AND table_name = ?
        ORDER BY ordinal_position;
    ", [$tbl]);
    foreach ($columns as $col) {
        if ($col->character_maximum_length !== null) {
            echo "  * " . str_pad($col->column_name, 25) . " | " . str_pad($col->data_type, 20) . " | " . $col->character_maximum_length . "\n";
        }
    }
}