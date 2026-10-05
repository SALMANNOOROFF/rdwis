<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$h = DB::table('cen.heads')->where('hed_code', 'LIKE', '%ILRD%')->orWhere('hed_name', 'LIKE', '%ILRD%')->get();
echo 'Heads with ILRD: ' . json_encode($h) . PHP_EOL;

$p = DB::table('prj.projects')->where('prj_code', 'LIKE', '%ILRD%')->orWhere('prj_title', 'LIKE', '%ILRD%')->get();
echo 'Projects with ILRD: ' . json_encode($p) . PHP_EOL;

// Check where ILRD appeared in case 375
$case375 = DB::table('hr.ctrcases')->where('ctc_id', 375)->first();
echo 'Case 375 row: ' . json_encode($case375) . PHP_EOL;
