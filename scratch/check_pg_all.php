<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("SELECT pid, state, wait_event_type, wait_event, query FROM pg_stat_activity WHERE state != 'idle'");
print_r($rows);
