<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("
    SELECT table_schema, table_name, column_name, data_type, udt_name
    FROM information_schema.columns 
    WHERE (table_schema = 'hr' AND table_name = 'ctrcases' AND column_name = 'ctc_id')
       OR (table_schema = 'prj' AND table_name = 'projects' AND column_name = 'prj_id')
");

foreach ($rows as $r) {
    echo "{$r->table_schema}.{$r->table_name}.{$r->column_name}: data_type={$r->data_type}, udt_name={$r->udt_name}\n";
}
