<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$locks = DB::select("
    SELECT pid, usename, state, wait_event_type, wait_event, query, query_start
    FROM pg_stat_activity
    WHERE state != 'idle'
    ORDER BY query_start DESC
");

foreach ($locks as $l) {
    echo "PID: {$l->pid} | State: {$l->state} | Wait: {$l->wait_event_type}:{$l->wait_event} | Query: " . substr($l->query, 0, 100) . "\n";
}
