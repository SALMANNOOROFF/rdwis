<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'fin' AND table_name = 'salorders' ORDER BY ordinal_position");
echo "fin.salorders columns:\n";
foreach ($cols as $c) {
    echo "  - " . $c->column_name . "\n";
}

$sample = DB::table('fin.salorders')->first();
echo "Sample salorders: " . json_encode($sample) . "\n";

$sampleShd = DB::table('fin.salorders_shd')->first();
echo "Sample salorders_shd: " . json_encode($sampleShd) . "\n";
