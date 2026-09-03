<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== TESTING CONTIGUOUS PROJECT SPANS GROUPING ===\n\n";

$today = Carbon::today()->toDateString();

// Let's test on Contract #1024
$plans = DB::table('hr.contractplans as p')
    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'p.cpn_hed_id')
    ->leftJoin('prj.projects as prj', function ($join) {
        $join->on('prj.prj_id', '=', 'h.hed_prj_id')
             ->orOn('prj.prj_id', '=', 'p.cpn_hed_id')
             ->orOn('prj.prj_id', '=', 'h.hed_id');
    })
    ->where('p.cpn_ctr_id', 1024)
    ->select('p.*', 'h.hed_code', 'h.hed_name', 'prj.prj_code', 'prj.prj_title')
    ->orderBy('p.cpn_startdt', 'asc')
    ->get();

$projectSpans = [];
$currentSpan = null;

foreach ($plans as $p) {
    $headId = $p->cpn_hed_id;
    $pCode = $p->hed_code ?: ($p->prj_code ?: 'Unassigned');
    $pTitle = $p->prj_title ?: ($p->hed_name ?: $pCode);

    if ($currentSpan === null || $currentSpan['head_id'] !== $headId) {
        if ($currentSpan !== null) {
            $projectSpans[] = $currentSpan;
        }
        $currentSpan = [
            'head_id'      => $headId,
            'code'         => $pCode,
            'title'        => $pTitle,
            'start_dt'     => $p->cpn_startdt,
            'end_dt'       => $p->cpn_enddt,
            'start_label'  => Carbon::parse($p->cpn_startdt)->format('M Y'),
            'end_label'    => Carbon::parse($p->cpn_enddt)->format('M Y'),
            'months_count' => 1,
            'is_current'   => ($today >= $p->cpn_startdt && $today <= $p->cpn_enddt),
        ];
    } else {
        $currentSpan['end_dt'] = $p->cpn_enddt;
        $currentSpan['end_label'] = Carbon::parse($p->cpn_enddt)->format('M Y');
        $currentSpan['months_count']++;
        if ($today >= $p->cpn_startdt && $today <= $p->cpn_enddt) {
            $currentSpan['is_current'] = true;
        }
    }
}
if ($currentSpan !== null) {
    $projectSpans[] = $currentSpan;
}

echo "Total monthly plan rows: " . count($plans) . "\n";
echo "Total grouped project spans: " . count($projectSpans) . "\n\n";

foreach ($projectSpans as $idx => $span) {
    $periodText = ($span['start_label'] === $span['end_label']) 
        ? $span['start_label'] . " (1 Month)"
        : "From {$span['start_label']} To {$span['end_label']} ({$span['months_count']} Months)";
    $currentTag = $span['is_current'] ? " [CURRENT]" : "";

    echo sprintf("Span %d: [%s] %s\n        Period: %s%s\n\n",
        $idx + 1,
        $span['code'],
        $span['title'],
        $periodText,
        $currentTag
    );
}
