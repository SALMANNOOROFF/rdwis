
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$headId = 200012;

echo "=== RAW DATA FOR HEAD ID: $headId ===\n\n";

// 1. Basic head info
echo "--- 1. BASIC HEAD INFO ---\n";
$head = DB::table('cen.heads')->where('hed_id', $headId)->first();
print_r($head);
echo "\n";

// 2. Shares alloc
echo "--- 2. SHARES ALLOC ---\n";
$shares = DB::table('fin.sharesalloc')->where('sha_hed_id', $headId)->first();
print_r($shares);
echo "\n";

// 3. Transfers (allocation, mtss)
echo "--- 3. TRANSFERS ---\n";
$transfers = DB::table('fin.transfers')->where(function($q) use ($headId) {
    $q->where('trf_tohed', $headId)->orWhere('trf_fromhed', $headId);
})->get();
print_r($transfers->toArray());
echo "\n";

// 4. Commitments (raw)
echo "--- 4. RAW FIN.COMMITMENTS ---\n";
$commitmentsRaw = DB::table('fin.commitments')->where('cmt_hed_id', $headId)->get();
print_r($commitmentsRaw->toArray());
echo "\n";

// 5. Commitments with pur.purcases
echo "--- 5. COMMITMENTS JOINED WITH PUR.PURCASES ---\n";
$commitmentsJoined = DB::table('fin.commitments')
    ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
    ->select('fin.commitments.*', 'pur.purcases.pcs_status', 'pur.purcases.pcs_id')
    ->where('fin.commitments.cmt_hed_id', $headId)
    ->get();
print_r($commitmentsJoined->toArray());
echo "\n";

// 6. In Process - pur.purcases raw
echo "--- 6. RAW PUR.PURCASES ---\n";
$purCases = DB::table('pur.purcases')->where('pcs_hed_id', $headId)->get();
print_r($purCases->toArray());
echo "\n";

// 7. Transactions raw
echo "--- 7. RAW FIN.TRANSACTIONS JOINED WITH COMMITMENTS ---\n";
$transactions = DB::table('fin.transactions')
    ->join('fin.commitments', 'trn_cmt_id', '=', 'cmt_id')
    ->where('cmt_hed_id', $headId)
    ->get();
print_r($transactions->toArray());
echo "\n";

// 8. Subheads
echo "--- 8. FIN.SUBHEADS ---\n";
$subheads = DB::table('fin.subheads')->where('sbh_hed_id', $headId)->get();
print_r($subheads->toArray());
echo "\n";

// 9. Shares installments
echo "--- 9. FIN.SHARESINSTALL ---\n";
$sharesInstall = DB::table('fin.sharesinstall')->where('shi_hed_id', $headId)->get();
print_r($sharesInstall->toArray());
echo "\n";

