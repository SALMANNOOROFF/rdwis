<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== TESTING CURRENT MONTH PLAN & DISTINCT PROJECT COUNT LOGIC ===\n\n";

$today = Carbon::today()->toDateString();
echo "Today is: $today\n\n";

// 1. Get recent active contracts
$contracts = DB::table('hr.contracts as c')
    ->leftJoin('cen.heads as ch', 'c.ctr_hed_id', '=', 'ch.hed_id')
    ->leftJoin('prj.projects as cp', function ($join) {
        $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
             ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
             ->orOn('cp.prj_id', '=', 'ch.hed_id');
    })
    ->select('c.*', 'ch.hed_code', 'ch.hed_name', 'cp.prj_code', 'cp.prj_title')
    ->orderBy('c.ctr_startdt', 'desc')
    ->limit(10)
    ->get();

$contractIds = $contracts->pluck('ctr_id')->toArray();

$contractPlans = DB::table('hr.contractplans as p')
    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'p.cpn_hed_id')
    ->leftJoin('prj.projects as prj', function ($join) {
        $join->on('prj.prj_id', '=', 'h.hed_prj_id')
             ->orOn('prj.prj_id', '=', 'p.cpn_hed_id')
             ->orOn('prj.prj_id', '=', 'h.hed_id');
    })
    ->whereIn('p.cpn_ctr_id', $contractIds)
    ->select(
        'p.cpn_ctr_id',
        'p.cpn_startdt',
        'p.cpn_enddt',
        'p.cpn_hed_id',
        'h.hed_code',
        'h.hed_name',
        'prj.prj_code',
        'prj.prj_title'
    )
    ->orderBy('p.cpn_startdt', 'asc')
    ->get()
    ->groupBy('cpn_ctr_id');

foreach ($contracts as $ctr) {
    $plans = $contractPlans[$ctr->ctr_id] ?? collect();
    
    // Find current month's plan row
    $currentPlan = $plans->first(function ($p) use ($today) {
        return $today >= $p->cpn_startdt && $today <= $p->cpn_enddt;
    });

    if (!$currentPlan && $plans->isNotEmpty()) {
        $currentPlan = $plans->first();
    }

    // Distinct project heads across the contract duration
    $distinctHeads = $plans->pluck('cpn_hed_id')->filter()->unique();
    $distinctCount = $distinctHeads->count();

    $curHeadCode = $currentPlan?->hed_code ?: ($currentPlan?->prj_code ?: ($ctr->hed_code ?: ($ctr->prj_code ?? null)));
    $curPrjTitle = $currentPlan?->prj_title ?: ($currentPlan?->hed_name ?: ($ctr->prj_title ?: ($ctr->hed_name ?? null)));

    echo sprintf("Ctr #%-4d | Emp: %-15s | Distinct Projects: %d | Current Month Head: [%s] %s\n",
        $ctr->ctr_id,
        $ctr->ctr_num,
        $distinctCount,
        $curHeadCode ?? '—',
        substr($curPrjTitle ?? 'Not Assigned', 0, 35)
    );
    if ($distinctCount > 1) {
        echo "   -> Breakdown: ";
        foreach ($plans as $p) {
            $m = Carbon::parse($p->cpn_startdt)->format('M y');
            $code = $p->hed_code ?: ($p->prj_code ?: '#'.$p->cpn_hed_id);
            echo "$m: $code | ";
        }
        echo "\n";
    }
}
