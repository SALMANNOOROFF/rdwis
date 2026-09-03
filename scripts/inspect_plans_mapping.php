<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECK CONTRACTPLANS VALUES ===\n";
$plans = DB::table('hr.contractplans')->orderBy('cpn_id', 'desc')->limit(15)->get();
foreach ($plans as $p) {
    // Check if cpn_hed_id matches cen.heads or prj.projects
    $hed = DB::table('cen.heads')->where('hed_id', $p->cpn_hed_id)->first();
    $prj = DB::table('prj.projects')->where('prj_id', $p->cpn_hed_id)->first();
    echo "Contract Plan ID: {$p->cpn_id} | CtrID: {$p->cpn_ctr_id} | cpn_hed_id: {$p->cpn_hed_id} | As Head: " . ($hed ? $hed->hed_code : 'NO') . " | As Project: " . ($prj ? $prj->prj_code : 'NO') . "\n";
}

echo "\n=== CHECK HR.CONTRACTS VALUES ===\n";
$contracts = DB::table('hr.contracts')->orderBy('ctr_id', 'desc')->limit(15)->get();
foreach ($contracts as $c) {
    $hed = $c->ctr_hed_id ? DB::table('cen.heads')->where('hed_id', $c->ctr_hed_id)->first() : null;
    $prj = $c->ctr_hed_id ? DB::table('prj.projects')->where('prj_id', $c->ctr_hed_id)->first() : null;
    echo "Contract ID: {$c->ctr_id} | Num: {$c->ctr_num} | ctr_hed_id: " . ($c->ctr_hed_id ?? 'NULL') . " | As Head: " . ($hed ? $hed->hed_code : 'NO') . " | As Project: " . ($prj ? $prj->prj_code : 'NO') . "\n";
}

echo "\n=== CHECK HR.EMPS VALUES ===\n";
$emps = DB::table('hr.emps')->orderBy('emp_joindt', 'desc')->limit(15)->get();
foreach ($emps as $e) {
    $hed = $e->emp_hed_id ? DB::table('cen.heads')->where('hed_id', $e->emp_hed_id)->first() : null;
    $prj = $e->emp_hed_id ? DB::table('prj.projects')->where('prj_id', $e->emp_hed_id)->first() : null;
    echo "Emp ID: {$e->emp_id} | Name: {$e->emp_name} | emp_hed_id: " . ($e->emp_hed_id ?? 'NULL') . " | As Head: " . ($hed ? $hed->hed_code : 'NO') . " | As Project: " . ($prj ? $prj->prj_code : 'NO') . "\n";
}
