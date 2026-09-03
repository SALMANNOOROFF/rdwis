<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== TESTING PROJECT EMPLOYEE COUNTS ===\n\n";

$today = Carbon::today()->toDateString();

// 1. Get heads mapping
$heads = DB::table('cen.heads')->get();
$prjToHeads = [];
foreach ($heads as $h) {
    if ($h->hed_prj_id) {
        $prjToHeads[$h->hed_prj_id][] = $h->hed_id;
    }
    $prjToHeads[$h->hed_id][] = $h->hed_id;
}

// 2. Active employees direct from hr.emps
$empCountsByHead = DB::table('hr.emps as e')
    ->whereRaw("LOWER(emp_status) IN ('active','current')")
    ->whereNotNull('emp_hed_id')
    ->select('emp_hed_id', DB::raw('count(*) as count'))
    ->groupBy('emp_hed_id')
    ->pluck('count', 'emp_hed_id')
    ->toArray();

// 3. Active employees from contracts covering today
$planCountsByHead = DB::table('hr.contractplans as cp')
    ->join('hr.contracts as c', 'c.ctr_id', '=', 'cp.cpn_ctr_id')
    ->join('hr.emps as e', 'e.emp_id', '=', 'c.ctr_num')
    ->whereRaw("LOWER(e.emp_status) IN ('active','current')")
    ->whereRaw('? between cp.cpn_startdt and cp.cpn_enddt', [$today])
    ->whereNotNull('cp.cpn_hed_id')
    ->select('cp.cpn_hed_id', DB::raw('count(DISTINCT e.emp_id) as count'))
    ->groupBy('cp.cpn_hed_id')
    ->pluck('count', 'cp.cpn_hed_id')
    ->toArray();

$projects = DB::table('prj.projects')->limit(15)->get();

foreach ($projects as $p) {
    $relatedHeadIds = array_unique(array_merge([$p->prj_id], $prjToHeads[$p->prj_id] ?? []));
    
    $cnt = 0;
    foreach ($relatedHeadIds as $hid) {
        $cnt += $planCountsByHead[$hid] ?? ($empCountsByHead[$hid] ?? 0);
    }

    echo sprintf("Project: %-12s | Title: %-35s | Active Employees: %d\n",
        $p->prj_code ?? '—',
        substr($p->prj_title ?? '—', 0, 35),
        $cnt
    );
}
