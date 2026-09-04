<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Table hr.ctrcase_substatus exists: " . (Schema::hasTable('hr.ctrcase_substatus') ? 'YES' : 'NO') . "\n";
echo "Table pur.purcasesubstatus exists: " . (Schema::hasTable('pur.purcasesubstatus') ? 'YES' : 'NO') . "\n";

$contractCasesCount = DB::table('hr.ctrcases')->count();
$contractSubstatusCount = Schema::hasTable('hr.ctrcase_substatus') ? DB::table('hr.ctrcase_substatus')->count() : 0;
echo "hr.ctrcases count: $contractCasesCount\n";
echo "hr.ctrcase_substatus count: $contractSubstatusCount\n";

$purchaseCasesCount = DB::table('pur.purchases')->count();
$purchaseSubstatusCount = Schema::hasTable('pur.purcasesubstatus') ? DB::table('pur.purcasesubstatus')->count() : 0;
echo "pur.purchases count: $purchaseCasesCount\n";
echo "pur.purcasesubstatus count: $purchaseSubstatusCount\n";
