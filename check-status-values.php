<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Status Values in Candidate Tables ===\n\n";

$candidates = [
    'pur.purcases' => 'pcs_status',
    'fin.salorders' => 'sor_status',
    'hr.salreqs' => 'srq_status'
];

foreach ($candidates as $table => $statusCol) {
    echo "--- $table ---\n";
    $statuses = DB::table($table)
        ->select($statusCol, DB::raw('COUNT(*) as count'))
        ->groupBy($statusCol)
        ->get();
    
    foreach ($statuses as $s) {
        $statusValue = $s->$statusCol;
        $count = $s->count;
        echo "  $statusValue: $count\n";
    }
    echo "\n";
}

// Now check what columns are available in each to map to fin_docs_ipc structure
echo "=== Mapping Column Mapping to fin_docs_ipc Structure ===\n\n";

$ipcColumns = ['docid', 'doctype', 'title', 'effhed_id', 'amount', 'sudohed', 'transtype', 'amount1', 'tax1', 'amount2'];

$candidateMappings = [
    'pur.purcases' => [
        'docid' => 'pcs_id',
        'doctype' => 'pcs_type',
        'title' => 'pcs_title',
        'effhed_id' => 'pcs_effhed_id',
        'amount' => 'CASE WHEN pcs_transtype = 1 THEN pcs_intprice ELSE pcs_price END',
        'sudohed' => 'pcs_sudohed',
        'transtype' => 'pcs_transtype',
        'amount1' => 'pcs_intprice',
        'tax1' => 'pcs_inttax',
        'amount2' => 'pcs_price'
    ],
    'fin.salorders' => [
        'docid' => 'sor_id',
        'doctype' => 'sor_type',
        'title' => "sor_empnamecomp || ' (' || to_char(sor_month, 'YYYY-MM') || ' Salary)'",
        'effhed_id' => 'sor_effhed_id',
        'amount' => 'CASE WHEN sor_transtype = 1 THEN sor_salary ELSE sor_netsalary END',
        'sudohed' => 'sor_sudohed',
        'transtype' => 'sor_transtype',
        'amount1' => 'sor_salary',
        'tax1' => 'sor_withheld',
        'amount2' => 'sor_netsalary'
    ],
    'hr.salreqs' => [
        'docid' => 'srq_id',
        'doctype' => "'Srq'",
        'title' => "srq_empnamecomp || ' (' || to_char(srq_month, 'YYYY-MM') || ' Salary Request)'",
        'effhed_id' => 'srq_effhed_id',
        'amount' => 'srq_netsalary',
        'sudohed' => 'srq_sudohed',
        'transtype' => '1',
        'amount1' => 'srq_salary',
        'tax1' => 'srq_withheld',
        'amount2' => 'srq_netsalary'
    ]
];

echo "Proposed mapping:\n";
foreach ($candidateMappings as $table => $mapping) {
    echo "  $table:\n";
    foreach ($mapping as $ipcCol => $dbCol) {
        echo "    $ipcCol → $dbCol\n";
    }
}
