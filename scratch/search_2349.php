<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Search for 2349 in purcases
$pc = DB::table('pur.purcases')->where('pcs_id', 2349)->first();
var_dump($pc);

// Check if pcs_id 2349 was in another table
$dec = DB::table('pur.purdecisions')->where('pdec_pcs_id', 2349)->get();
echo "Decisions for 2349: " . count($dec) . "\n";

$items = DB::table('pur.purcaseitems')->where('pci_pcs_id', 2349)->get();
echo "Items for 2349: " . count($items) . "\n";

$prj = DB::table('prj.projects')->where('prj_id', 2349)->first();
echo "Project 2349: " . ($prj ? $prj->prj_title : 'none') . "\n";

$head = DB::table('cen.heads')->where('hed_id', 2349)->first();
echo "Head 2349: " . ($head ? $head->hed_name : 'none') . "\n";
