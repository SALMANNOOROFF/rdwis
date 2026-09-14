<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$c = DB::table('pur.purcases')->where('pcs_id', 2349)->first();
if ($c) {
    echo "Found in updatedrdwV1: Title = {$c->pcs_title}, Status = {$c->pcs_status}\n";
} else {
    echo "Not found in updatedrdwV1!\n";
}

// Check pur_it_letters for 2349
$it = DB::table('pur.pur_it_letters')->where('pit_pcs_id', 2349)->first();
if ($it) {
    echo "pur_it_letters exists for 2349: ID = {$it->pit_id}\n";
} else {
    echo "No pur_it_letters for 2349\n";
}
