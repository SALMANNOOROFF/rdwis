<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'hr' AND table_name = 'salreqs' ORDER BY ordinal_position");
echo "hr.salreqs columns:\n";
foreach ($cols as $c) {
    echo "  - " . $c->column_name . "\n";
}

$cols2 = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'fin' AND table_name = 'salorders_shd' ORDER BY ordinal_position");
echo "fin.salorders_shd columns:\n";
foreach ($cols2 as $c) {
    echo "  - " . $c->column_name . "\n";
}
