<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSIS FOR HEAD ID 200012 ===\n\n";

// 1. Head-level Commitments query
echo "--- 1. HEAD-LEVEL COMMITMENTS QUERY ---\n";
$commitmentsQuery = DB::table('fin.commitments')
    ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
    ->select(
        'fin.commitments.cmt_id',
        'fin.commitments.cmt_docid',
        'fin.commitments.cmt_amount',
        'fin.commitments.cmt_status',
        'fin.commitments.cmt_hed_id',
        'fin.commitments.cmt_effhed_id',
        'pur.purcases.pcs_status'
    )
    ->where('fin.commitments.cmt_hed_id', 200012);

echo "SQL Query:\n" . $commitmentsQuery->toSql() . "\n\n";
$commitments = $commitmentsQuery->get();
echo "Results:\n";
print_r($commitments->toArray());
$sum = $commitments->sum('cmt_amount');
echo "\nTotal SUM(cmt_amount): $sum\n";

// 2. Subhead-level Commitments query
echo "\n--- 2. SUBHEAD-LEVEL COMMITMENTS QUERY ---\n";
$subheadCommitmentsQuery = DB::table('fin.commitments')
    ->join('pur.purcases_shd', 'fin.commitments.cmt_docid', '=', 'pur.purcases_shd.pcd_pcs_id')
    ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
    ->select(
        'fin.commitments.cmt_id',
        'fin.commitments.cmt_docid',
        'fin.commitments.cmt_amount',
        'fin.commitments.cmt_status',
        'fin.commitments.cmt_hed_id',
        'fin.commitments.cmt_effhed_id',
        'pur.purcases.pcs_status',
        'pur.purcases_shd.pcd_subhead',
        'pur.purcases_shd.pcd_ratio',
        DB::raw('fin.commitments.cmt_amount * pur.purcases_shd.pcd_ratio as subhead_cmt_amount')
    )
    ->where('fin.commitments.cmt_hed_id', 200012);

echo "SQL Query:\n" . $subheadCommitmentsQuery->toSql() . "\n\n";
$subheadCommitments = $subheadCommitmentsQuery->get();
echo "Results:\n";
print_r($subheadCommitments->toArray());

// 3. In Process query
echo "\n--- 3. IN PROCESS QUERY ---\n";
$inProcessQuery = DB::table('pur.purcases')
    ->select('pcs_id', 'pcs_status', 'pcs_hed_id', 'pcs_price')
    ->where('pcs_hed_id', 200012)
    ->where(function($query) {
        $query->where(DB::raw('LOWER(pcs_status)'), 'finance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'with finance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'with dfinance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'audit')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'command')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'approved_pending_commit');
    });

echo "SQL Query:\n" . $inProcessQuery->toSql() . "\n\n";
$inProcess = $inProcessQuery->get();
echo "Results:\n";
print_r($inProcess->toArray());
$sumInProcess = $inProcess->sum('pcs_price');
echo "\nTotal SUM(pcs_price): $sumInProcess\n";

// 4. Check for CSRF vs Project distinction
echo "\n--- 4. CHECKING SHARESALLOC DATA ---\n";
$shares = DB::table('fin.sharesalloc')->where('sha_hed_id', 200012)->first();
print_r($shares);
    