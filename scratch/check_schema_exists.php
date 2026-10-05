<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$exists = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'hrforms'");
echo "hrforms schema exists: " . (count($exists) > 0 ? "YES" : "NO") . "\n";
