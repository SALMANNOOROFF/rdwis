<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CONTRACT CASE RECENT RECORDS ===\n";
$cases = DB::table('hr.ctr_cases')->orderBy('ctc_id', 'desc')->limit(10)->get();
foreach ($cases as $c) {
    echo "Case ID: {$c->ctc_id} | Type: {$c->ctc_type} | EmpID: {$c->ctc_empid} | Name: {$c->ctc_name} | Status: {$c->ctc_status}\n";
    $plans = DB::table('hr.ctr_cases_plans')->where('ccp_ctc_id', $c->ctc_id)->get();
    foreach ($plans as $p) {
        $prj = $p->ccp_prj_id ? DB::table('prj.projects')->where('prj_id', $p->ccp_prj_id)->first() : null;
        $hed = $p->ccp_hed_id ? DB::table('cen.heads')->where('hed_id', $p->ccp_hed_id)->first() : null;
        echo "  -> Plan: PrjID=" . ($p->ccp_prj_id ?? 'NULL') . " [" . ($prj->prj_code ?? 'None') . " " . ($prj->prj_title ?? '') . "] | HedID=" . ($p->ccp_hed_id ?? 'NULL') . " [" . ($hed->hed_code ?? 'None') . "]\n";
    }
}

echo "\n=== RECENT HR.CONTRACTS RECORDS ===\n";
$ctrs = DB::table('hr.contracts')->orderBy('ctr_id', 'desc')->limit(10)->get();
foreach ($ctrs as $ctr) {
    $hed = $ctr->ctr_hed_id ? DB::table('cen.heads')->where('hed_id', $ctr->ctr_hed_id)->first() : null;
    $prj = $hed && $hed->hed_prj_id ? DB::table('prj.projects')->where('prj_id', $hed->hed_prj_id)->first() : null;
    echo "Ctr ID: {$ctr->ctr_id} | Num: {$ctr->ctr_num} | Title: {$ctr->ctr_jobtitle} | HedID: " . ($ctr->ctr_hed_id ?? 'NULL') . " | HeadCode: " . ($hed->hed_code ?? 'NULL') . " | Prj: " . ($prj->prj_code ?? 'NULL') . " | CtrCaseID: " . ($ctr->ctr_ctc_id ?? 'NULL') . "\n";
}

echo "\n=== RECENT HR.EMPS RECORDS ===\n";
$emps = DB::table('hr.emps')->orderBy('emp_joindt', 'desc')->limit(10)->get();
foreach ($emps as $e) {
    $hed = $e->emp_hed_id ? DB::table('cen.heads')->where('hed_id', $e->emp_hed_id)->first() : null;
    echo "Emp ID: {$e->emp_id} | Name: {$e->emp_name} | Joindt: {$e->emp_joindt} | HedID: " . ($e->emp_hed_id ?? 'NULL') . " | HeadCode: " . ($hed->hed_code ?? 'NULL') . "\n";
}

echo "\n=== HOW ARE CEN.HEADS AND PRJ.PROJECTS LINKED? ===\n";
$headsWithPrj = DB::table('cen.heads as h')
    ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
    ->select('h.hed_id', 'h.hed_code', 'h.hed_name', 'h.hed_prj_id', 'p.prj_id', 'p.prj_code', 'p.prj_title')
    ->limit(10)
    ->get();
foreach ($headsWithPrj as $hp) {
    echo "Head ID: {$hp->hed_id} | Head Code: {$hp->hed_code} | Head Prj ID: " . ($hp->hed_prj_id ?? 'NULL') . " | Prj Code: " . ($hp->prj_code ?? 'NULL') . " | Prj Title: " . ($hp->prj_title ?? 'NULL') . "\n";
}
