<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'hr.ctrcases',
    'hr.ctrcaseplans',
    'hr.contracts',
    'hr.emps',
    'hr.empsexta',
    'hr.empsextb',
    'hr.empsextc',
    'hr.applicants',
    'hr.applicjobs',
    'hr.applicqualifs',
    'hr.hirings',
    'hr.boards',
    'hr.bdapps',
    'hr.bdmems',
    'hr.jobs',
    'hr.qualifs',
    'hr.bnkaccounts',
    'hr.vehicles',
    'prj.projects',
    'cen.units',
    'cen.heads',
    'fin.contractsverif',
    'fin.empeffheads',
    'fin.subheads'
];

foreach ($tables as $fullTable) {
    [$schema, $table] = explode('.', $fullTable);
    $cols = DB::select("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_schema = ? AND table_name = ?
        ORDER BY ordinal_position
    ", [$schema, $table]);

    echo "\n=== $fullTable (" . count($cols) . " columns) ===\n";
    foreach ($cols as $c) {
        echo "  {$c->column_name}: {$c->data_type}" . ($c->is_nullable === 'NO' ? ' NOT NULL' : '') . "\n";
    }
}
