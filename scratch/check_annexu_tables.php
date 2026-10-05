<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['empsexta', 'empsextb', 'vehicles', 'bnkaccounts'];
foreach ($tables as $t) {
    $cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'hr' AND table_name = '{$t}'");
    echo "hr.{$t} columns: " . PHP_EOL;
    foreach ($cols as $c) {
        echo "  {$c->column_name} ({$c->data_type})" . PHP_EOL;
    }
}
