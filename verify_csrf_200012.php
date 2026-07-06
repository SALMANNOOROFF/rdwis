<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$headId = 200012;

echo "=== Verifying CSRF Data for HeadId: $headId ===\n\n";

// Step 1: Verify the raw CSRF data
$rawData = DB::select("
    SELECT c.cmt_id, c.cmt_docid, c.cmt_type, c.cmt_amount, c.cmt_status,
           c.cmt_effhed_id, c.cmt_hed_id, c.cmt_sudohed,
           t.trn_id, t.trn_amount1, t.trn_amount2, t.trn_transtype
    FROM fin.commitments c
    LEFT JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
    WHERE c.cmt_effhed_id = ?
      AND c.cmt_sudohed IS NOT NULL
      AND c.cmt_sudohed <> ''
      AND c.cmt_type IN ('Ps','Pt','Rb','Sa')
", [$headId]);

echo "Raw CSRF Rows Found:\n";
echo str_repeat("-", 120) . "\n";
foreach ($rawData as $row) {
    print_r($row);
}
echo str_repeat("-", 120) . "\n\n";

// Step 2: Calculate the sum manually
$totalAmount = 0;
foreach ($rawData as $row) {
    if ($row->trn_transtype == 1) {
        $amount = $row->trn_amount1;
    } else {
        $amount = $row->trn_amount2;
    }
    $totalAmount += $amount;
}

echo "Calculated Total: " . number_format($totalAmount, 2) . "\n";
echo "Expected Total: 300,000.00\n";
echo "Match? " . (abs($totalAmount - 300000) < 0.01 ? "YES" : "NO") . "\n\n";

// Step 3: Test the CF Expenditure query
$cfExpenditure = DB::table('fin.commitments as c')
    ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
    ->where('c.cmt_effhed_id', $headId)
    ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
    ->whereNotNull('c.cmt_sudohed')
    ->where('c.cmt_sudohed', '<>', '')
    ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
    ->value('total');

echo "CF Expenditure Query Result: " . number_format($cfExpenditure, 2) . "\n";
echo "CF Expenditure (abs, rounded): " . number_format(round(abs((float) ($cfExpenditure ?? 0)), 2), 2) . "\n\n";

// Step 4: Test the CF Commitments query
$paidByCommitment = DB::table('fin.transactions')
    ->select('trn_cmt_id', DB::raw('SUM(CASE WHEN trn_transtype = 1 THEN COALESCE(trn_amount1,0) ELSE COALESCE(trn_amount2,0) END) as paid'))
    ->groupBy('trn_cmt_id');

$cfCommitments = DB::table('fin.commitments as c')
    ->leftJoinSub($paidByCommitment, 'p', function ($join) {
        $join->on('c.cmt_id', '=', 'p.trn_cmt_id');
    })
    ->where('c.cmt_effhed_id', $headId)
    ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
    ->where('c.cmt_status', 'Awaited')
    ->whereNotNull('c.cmt_sudohed')
    ->where('c.cmt_sudohed', '<>', '')
    ->select(DB::raw('SUM(c.cmt_amount - COALESCE(p.paid, 0)) as net_outstanding'))
    ->value('net_outstanding');

echo "CF Commitments Query Result: " . number_format($cfCommitments, 2) . "\n";
echo "CF Commitments (abs, rounded): " . number_format(round(abs((float) ($cfCommitments ?? 0)), 2), 2) . "\n";
