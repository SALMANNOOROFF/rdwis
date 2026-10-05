<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$interns = DB::table('hr.ctrcases')
    ->where('ctc_newjobtitle', 'ILIKE', '%intern%')
    ->orWhere('ctc_newgrade', 'ILIKE', '%intern%')
    ->get();

echo "Intern cases count: " . $interns->count() . PHP_EOL;
foreach ($interns as $i) {
    echo "ID: {$i->ctc_id}, Type: {$i->ctc_type}, Job: {$i->ctc_newjobtitle}, Grade: {$i->ctc_newgrade}" . PHP_EOL;
}
