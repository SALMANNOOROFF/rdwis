<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$bands = DB::table('hrforms.salary_bands')->get();
foreach ($bands as $b) {
    echo "Designation: '{$b->designation}', Subgrades: {$b->sub_grades}, Min: {$b->min_salary}, Max: {$b->max_salary}" . PHP_EOL;
}
