<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Let's create or update a multi-project test employee / contract
// Test employee: 14-26-09-1234 (Contract #1024)
echo "=== SETTING UP MULTI-PROJECT CONTRACT PLANS FOR TESTING (Ctr #1024) ===\n";

// Get 3 different head IDs from cen.heads
$heads = DB::table('cen.heads')->whereNotNull('hed_code')->limit(3)->get();
$h1 = $heads[0]->hed_id; // e.g. DVBS / VTOL
$h2 = $heads[1]->hed_id; // e.g. ROV / ECM
$h3 = $heads[2]->hed_id; // e.g. HSTD

echo "Head 1: {$heads[0]->hed_code} (ID: {$h1})\n";
echo "Head 2: {$heads[1]->hed_code} (ID: {$h2})\n";
echo "Head 3: {$heads[2]->hed_code} (ID: {$h3})\n";

// Update plans for Ctr #1024 across 8 months
$plans = DB::table('hr.contractplans')->where('cpn_ctr_id', 1024)->orderBy('cpn_startdt', 'asc')->get();
$i = 0;
foreach ($plans as $p) {
    if ($i < 2) {
        $assignedHed = $h1;
    } elseif ($i < 4) {
        $assignedHed = $h2;
    } else {
        $assignedHed = $h3;
    }
    DB::table('hr.contractplans')->where('cpn_id', $p->cpn_id)->update(['cpn_hed_id' => $assignedHed]);
    $i++;
}

echo "Updated " . count($plans) . " plan slices for Ctr #1024.\n";

// Run check
$testPlans = DB::table('hr.contractplans as p')
    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'p.cpn_hed_id')
    ->leftJoin('prj.projects as prj', function ($join) {
        $join->on('prj.prj_id', '=', 'h.hed_prj_id')
             ->orOn('prj.prj_id', '=', 'p.cpn_hed_id')
             ->orOn('prj.prj_id', '=', 'h.hed_id');
    })
    ->where('p.cpn_ctr_id', 1024)
    ->select('p.*', 'h.hed_code', 'prj.prj_code', 'prj.prj_title')
    ->orderBy('p.cpn_startdt', 'asc')
    ->get();

$distinctCount = $testPlans->pluck('cpn_hed_id')->unique()->count();
echo "Ctr #1024 Distinct Head Count: $distinctCount\n";
foreach ($testPlans as $tp) {
    echo "  -> {$tp->cpn_startdt} to {$tp->cpn_enddt}: [{$tp->hed_code}] {$tp->prj_title}\n";
}
