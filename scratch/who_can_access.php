<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$case = App\Models\HrCtrCase::find(376);
$u = DB::table('cen.accounts')->where('acc_id', $case->ctc_createdby)->first();
echo "Creator: " . ($u ? $u->acc_username . ' (' . $u->acc_name . ')' : 'unknown') . PHP_EOL;

$divUsers = DB::table('cen.accounts')
    ->where('acc_untarea', $case->ctc_divisionid)
    ->where('acc_status', 'Active')
    ->get(['acc_id', 'acc_username', 'acc_name', 'acc_type']);
echo "Division Users count: " . $divUsers->count() . PHP_EOL;
foreach ($divUsers->take(3) as $d) {
    echo " - " . $d->acc_username . " / " . $d->acc_name . " (type: " . ($d->acc_type ?? 'N/A') . ")" . PHP_EOL;
}
