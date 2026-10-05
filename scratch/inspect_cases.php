<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$types = DB::table('hr.ctrcases')->select('ctc_type', DB::raw('count(*) as count'))->groupBy('ctc_type')->get();
echo "Case Types in hr.ctrcases:" . PHP_EOL;
foreach ($types as $t) {
    echo "Type: '{$t->ctc_type}' -> count: {$t->count}" . PHP_EOL;
}

$sample = DB::table('hr.ctrcases')->orderBy('ctc_id', 'desc')->limit(5)->get();
echo PHP_EOL . "Recent 5 cases:" . PHP_EOL;
foreach ($sample as $c) {
    echo "ID: {$c->ctc_id}, Type: {$c->ctc_type}, Status: {$c->ctc_status}, Job: {$c->ctc_newjobtitle}, Grade: {$c->ctc_newgrade}" . PHP_EOL;
}
