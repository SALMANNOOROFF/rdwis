<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select("SELECT table_schema, table_name FROM information_schema.tables WHERE table_name LIKE '%setting%' OR table_name LIKE '%config%' OR table_name LIKE '%param%'");

echo json_encode($tables, JSON_PRETTY_PRINT);
