<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("
    SELECT table_schema, table_name, column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_schema NOT IN ('pg_catalog', 'information_schema', 'hrforms')
    ORDER BY table_schema, table_name, ordinal_position
");

$data = array_map(function($r) {
    return (array) $r;
}, $rows);

$json = json_encode($data);
$hash = md5($json);

file_put_contents(__DIR__ . '/baseline_schema_snapshot.json', json_encode([
    'total_columns' => count($rows),
    'md5' => $hash,
    'data' => $data
], JSON_PRETTY_PRINT));

echo "Recorded baseline schema: " . count($rows) . " columns across existing schemas. Hash: {$hash}\n";
