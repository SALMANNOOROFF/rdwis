<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$info = DB::select("SELECT current_database(), inet_server_port(), inet_server_addr()");
print_r($info);

// Also check all databases on this postgres server
$dbs = DB::select("SELECT datname FROM pg_database WHERE datistemplate = false");
print_r($dbs);

// Check pur.pur_it_letters in each database if possible or see if there's multiple schemas/ports
$tables = DB::select("SELECT table_schema, table_name FROM information_schema.tables WHERE table_name = 'pur_it_letters'");
print_r($tables);
