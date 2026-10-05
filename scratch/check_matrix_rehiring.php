<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$m = DB::table('hrforms.form_matrix')->where('hiring_type', 'Rehiring')->get();
echo json_encode($m, JSON_PRETTY_PRINT) . PHP_EOL;
