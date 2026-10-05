<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'hr' AND table_name = 'applicqualifs'");
echo "hr.applicqualifs columns: " . PHP_EOL;
foreach ($cols as $c) {
    echo "  {$c->column_name} ({$c->data_type})" . PHP_EOL;
}

$colsJobs = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'hr' AND table_name = 'applicjobs'");
echo PHP_EOL . "hr.applicjobs columns: " . PHP_EOL;
foreach ($colsJobs as $c) {
    echo "  {$c->column_name} ({$c->data_type})" . PHP_EOL;
}
