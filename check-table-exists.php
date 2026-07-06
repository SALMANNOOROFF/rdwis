<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// List of required tables
$tables = [
    'fin.acc_received1',
    'fin.commitments',
    'fin.transactions',
    'pur.purcases',
    'pur.purcases_shd',
    'prj.milestones',
    'fin.msncosts',
    'hr.salreqs',
    'fin.salorders',
];

echo "Checking for required tables in the database:\n";
foreach ($tables as $table) {
    try {
        // Try to select 1 row
        $exists = DB::table($table)->count() >= 0;
        echo "✅ $table exists!\n";
    } catch (\Exception $e) {
        echo "❌ $table NOT found! Error: " . $e->getMessage() . "\n";
    }
}
