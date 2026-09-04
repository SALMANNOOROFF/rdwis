<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$units = DB::table('cen.units')->select('unt_id', 'unt_name', 'unt_namesh', 'unt_leadname', 'unt_leadrank', 'unt_leaddesig')->get();
foreach ($units as $u) {
    echo "{$u->unt_id} | {$u->unt_name} ({$u->unt_namesh}) | Lead: {$u->unt_leadname} | Rank: {$u->unt_leadrank} | Desig: {$u->unt_leaddesig}\n";
}

echo "\n=== Active Accounts for each unit ===\n";
foreach ($units as $u) {
    $accs = DB::table('cen.accounts')
        ->where('acc_status', 'Open')
        ->where(function($q) use ($u) {
            $q->where('acc_lowers', $u->unt_id)
              ->orWhere('acc_lowerm', $u->unt_id);
        })
        ->select('acc_name', 'acc_rank', 'acc_desig')
        ->get();
    if ($accs->count() > 0) {
        echo "Unit {$u->unt_namesh}:\n";
        foreach ($accs as $a) {
            echo "   - {$a->acc_rank} {$a->acc_name} ({$a->acc_desig})\n";
        }
    }
}
