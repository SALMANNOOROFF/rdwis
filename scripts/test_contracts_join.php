<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING CONTRACTS JOIN WITH BOTH CEN.HEADS AND PRJ.PROJECTS ===\n";

$contracts = DB::table('hr.contracts as c')
    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'c.ctr_hed_id')
    ->leftJoin('prj.projects as p', function ($join) {
        $join->on('p.prj_id', '=', 'h.hed_prj_id')
             ->orOn('p.prj_id', '=', 'c.ctr_hed_id')
             ->orOn('p.prj_id', '=', 'h.hed_id');
    })
    ->select(
        'c.ctr_id',
        'c.ctr_num',
        'c.ctr_hed_id',
        'c.ctr_jobtitle',
        'c.ctr_grade',
        'h.hed_code',
        'h.hed_name',
        'p.prj_code',
        'p.prj_title'
    )
    ->orderBy('c.ctr_id', 'desc')
    ->limit(20)
    ->get();

foreach ($contracts as $c) {
    $code = $c->hed_code ?: ($c->prj_code ?: '—');
    $title = $c->prj_title ?: ($c->hed_name ?: '—');
    echo sprintf("Ctr #%-4d | Emp: %-15s | Head/Prj Code: %-10s | Title: %-35s | Job: %s\n",
        $c->ctr_id,
        $c->ctr_num,
        $code,
        substr($title, 0, 35),
        $c->ctr_jobtitle
    );
}
