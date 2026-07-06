<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get first row of fin.msncosts
$firstRow = DB::table('fin.msncosts')->first();
echo "fin.msncosts columns from first row:\n";
print_r((array)$firstRow);

// Check if fin.msncosts has a "received" column or similar
$columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = 'fin' AND table_name = 'msncosts'");
echo "\nAll columns in fin.msncosts:\n";
foreach ($columns as $c) {
    echo "- " . $c->column_name . "\n";
}
