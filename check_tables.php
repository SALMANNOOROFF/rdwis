<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// The key query used in finAllocationStatus already tells us the pattern.
// Let's see what the "account status" report needs:
// Account Status = Head, Allocation, MTSS Share, RDW Share, Received, Expenditure, In Process, Commitments, Available, Remaining

// Let's check what "Received" and "Expenditure" are in transactions
echo "=== Checking transactions for a known head ===\n";
$rows = DB::select("
    SELECT
        h.hed_code,
        sha.sha_pcc AS project_share,
        sha.sha_cf  AS csrf_share,
        ci.cmt_amount AS allocation,
        (-co.cmt_amount) AS mtss_share,
        (ci.cmt_amount + co.cmt_amount) AS rdw_share
    FROM cen.heads h
    JOIN fin.sharesalloc sha ON sha.sha_hed_id = h.hed_id
    JOIN fin.commitments ci ON sha.sha_ficmt_id = ci.cmt_id
    JOIN fin.commitments co ON sha.sha_focmt_id = co.cmt_id
    LIMIT 10
");
echo "head | project_share | csrf | allocation | mtss | rdw\n";
foreach ($rows as $r) {
    echo "{$r->hed_code} | {$r->project_share} | {$r->csrf_share} | {$r->allocation} | {$r->mtss_share} | {$r->rdw_share}\n";
}

echo "\n=== Checking Ps commitments joined with purcases for awaiting ===\n";
$rows2 = DB::select("
    SELECT
        c.cmt_id, c.cmt_type, c.cmt_status, c.cmt_amount, c.cmt_date,
        c.cmt_effhed_id, h.hed_code,
        pc.pcs_title, pc.pcs_minute,
        pc.pcs_date, pc.pcs_price
    FROM fin.commitments c
    LEFT JOIN cen.heads h ON h.hed_id = c.cmt_effhed_id
    LEFT JOIN pur.purcases pc ON pc.pcs_id = c.cmt_docid
    WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Awaited'
    LIMIT 10
");
echo "cmt_id | head | title | minute | date | cmt_amount | pcs_price\n";
foreach ($rows2 as $r) {
    echo "{$r->cmt_id} | {$r->hed_code} | " . substr($r->pcs_title ?? '', 0, 30) . " | {$r->pcs_minute} | {$r->cmt_date} | {$r->cmt_amount} | {$r->pcs_price}\n";
}

echo "\n=== Checking firm names in quotes ===\n";
$cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='pur' AND table_name='quotes' ORDER BY ordinal_position");
foreach ($cols as $c) echo "  {$c->column_name} ({$c->data_type})\n";

echo "\n=== Check how transactions relate to Ps commitments ===\n";
$rows3 = DB::select("
    SELECT
        c.cmt_id, c.cmt_type, c.cmt_status, c.cmt_amount,
        SUM(t.trn_amount1) as total_paid
    FROM fin.commitments c
    LEFT JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
    WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Awaited'
    GROUP BY c.cmt_id, c.cmt_type, c.cmt_status, c.cmt_amount
    LIMIT 10
");
echo "cmt_id | cmt_amount | total_paid\n";
foreach ($rows3 as $r) {
    echo "{$r->cmt_id} | {$r->cmt_amount} | {$r->total_paid}\n";
}
