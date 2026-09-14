<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'pur' AND table_name = 'pur_it_letters' ORDER BY ordinal_position");

echo json_encode($cols, JSON_PRETTY_PRINT) . PHP_EOL;
