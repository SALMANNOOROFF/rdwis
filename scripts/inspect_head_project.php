<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$results = DB::table('hr.emps as e')
    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'e.emp_hed_id')
    ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
    ->select('e.emp_id', 'e.emp_name', 'e.emp_status', 'e.emp_hed_id', 'h.hed_code', 'h.hed_name', 'p.prj_title', 'p.prj_code')
    ->limit(20)
    ->get();

echo sprintf("%-16s | %-22s | %-8s | %-30s | %-8s | %-30s\n", "EMP ID", "NAME", "EMP HEAD", "EMP PROJECT", "CTR HEAD", "CTR PROJECT");
echo str_repeat("-", 125) . "\n";

foreach ($results as $r) {
    $ctr = DB::table('hr.contracts as c')
        ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
        ->leftJoin('prj.projects as cp', 'cp.prj_id', '=', 'ch.hed_prj_id')
        ->where('c.ctr_num', $r->emp_id)
        ->orderBy('c.ctr_startdt', 'desc')
        ->select('c.ctr_jobtitle', 'ch.hed_code as ctr_hed_code', 'ch.hed_name as ctr_hed_name', 'cp.prj_title as ctr_prj_title', 'cp.prj_code as ctr_prj_code')
        ->first();
    
    echo sprintf("%-16s | %-22s | %-8s | %-30s | %-8s | %-30s\n", 
        $r->emp_id, 
        substr($r->emp_name, 0, 22), 
        $r->hed_code ?? 'NULL', 
        substr($r->prj_title ?? ($r->hed_name ?? 'NULL'), 0, 30),
        $ctr?->ctr_hed_code ?? 'NULL',
        substr($ctr?->ctr_prj_title ?? ($ctr?->ctr_hed_name ?? 'NULL'), 0, 30)
    );
}
