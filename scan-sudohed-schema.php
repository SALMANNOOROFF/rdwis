<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Scanning PostgreSQL Schema for Sudohed Columns ===\n\n";

// 1. Find all tables with columns containing 'sudohed' in their name
$sudohedTables = DB::select("
    SELECT table_schema, table_name, column_name
    FROM information_schema.columns
    WHERE column_name LIKE '%sudohed%'
    ORDER BY table_schema, table_name
");

echo "Tables with sudohed columns:\n";
foreach ($sudohedTables as $row) {
    echo "  - $row->table_schema.$row->table_name.$row->column_name\n";
}

echo "\n=== Candidate Table Structures ===\n";
$candidateTables = [];
foreach ($sudohedTables as $row) {
    $candidateTables[] = "$row->table_schema.$row->table_name";
}

$candidateTables = array_unique($candidateTables);

foreach ($candidateTables as $table) {
    echo "\n--- $table ---\n";
    
    // Get all columns for this table
    $columns = DB::select("
        SELECT column_name, data_type
        FROM information_schema.columns
        WHERE table_schema = ?
        AND table_name = ?
        ORDER BY ordinal_position
    ", explode('.', $table));
    
    foreach ($columns as $col) {
        echo "  $col->column_name ($col->data_type)\n";
    }
    
    // Also get a sample row (limit 1)
    $sample = DB::table($table)->first();
    if ($sample) {
        echo "\n  Sample row:\n";
        foreach ((array)$sample as $key => $value) {
            echo "    $key: " . (is_string($value) ? $value : var_export($value, true)) . "\n";
        }
    }
}
