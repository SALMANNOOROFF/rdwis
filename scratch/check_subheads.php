<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$subheads = DB::table('fin.subheads')
    ->select('sbh_name', DB::raw('count(*) as count'))
    ->groupBy('sbh_name')
    ->orderBy('count', 'desc')
    ->get();

foreach ($subheads as $s) {
    echo "{$s->sbh_name}: {$s->count}\n";
}
