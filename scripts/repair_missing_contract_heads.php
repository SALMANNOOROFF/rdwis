<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING SALMAN NOOR (11-26-08-9879) DETAILS ===\n";
$salmanEmp = DB::table('hr.emps')->where('emp_id', '11-26-08-9879')->first();
$salmanCtr = DB::table('hr.contracts')->where('ctr_num', '11-26-08-9879')->first();
$salmanPlans = $salmanCtr ? DB::table('hr.contractplans')->where('cpn_ctr_id', $salmanCtr->ctr_id)->get() : collect();

echo "Salman Emp Head ID: " . ($salmanEmp->emp_hed_id ?? 'NULL') . "\n";
echo "Salman Contract Head ID: " . ($salmanCtr->ctr_hed_id ?? 'NULL') . "\n";
echo "Salman Plans Count: " . count($salmanPlans) . "\n";
foreach ($salmanPlans as $sp) {
    echo "  -> Plan Head ID: {$sp->cpn_hed_id}\n";
}

echo "\n=== BACKFILLING MISSING ctr_hed_id FROM CONTRACTPLANS ===\n";
$missingContracts = DB::table('hr.contracts as c')
    ->whereNull('c.ctr_hed_id')
    ->get();

echo "Found " . count($missingContracts) . " contracts with NULL ctr_hed_id.\n";

$fixedCount = 0;
foreach ($missingContracts as $mc) {
    // Try to get from contractplans
    $planHeadId = DB::table('hr.contractplans')
        ->where('cpn_ctr_id', $mc->ctr_id)
        ->whereNotNull('cpn_hed_id')
        ->value('cpn_hed_id');

    // Try to get from ctrcases
    if (!$planHeadId && $mc->ctr_ctc_id) {
        $case = DB::table('hr.ctrcases')->where('ctc_id', $mc->ctr_ctc_id)->first();
        if ($case && $case->ctc_prj_id) {
            $planHeadId = $case->ctc_prj_id;
        }
    }

    // Try to get from hr.emps
    if (!$planHeadId) {
        $empHeadId = DB::table('hr.emps')->where('emp_id', $mc->ctr_num)->value('emp_hed_id');
        if ($empHeadId) {
            $planHeadId = $empHeadId;
        }
    }

    if ($planHeadId) {
        DB::table('hr.contracts')->where('ctr_id', $mc->ctr_id)->update(['ctr_hed_id' => $planHeadId]);
        // Also ensure emp has emp_hed_id
        DB::table('hr.emps')->where('emp_id', $mc->ctr_num)->whereNull('emp_hed_id')->update(['emp_hed_id' => $planHeadId]);
        $fixedCount++;
    }
}

echo "Successfully backfilled ctr_hed_id for $fixedCount contracts!\n";
