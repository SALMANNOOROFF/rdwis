<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$types = DB::table('hr.ctrcases')
    ->select('ctc_type', DB::raw('count(*) as count'))
    ->groupBy('ctc_type')
    ->orderByDesc('count')
    ->get();

echo "EXACT ctc_type values and counts in hr.ctrcases:" . PHP_EOL;
foreach ($types as $t) {
    echo "  '{$t->ctc_type}' => {$t->count}" . PHP_EOL;
}
