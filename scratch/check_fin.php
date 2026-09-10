<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$u = App\Models\CenAccount::where('acc_untarea', 'ILIKE', 'fin')->first();
var_dump([
    'username' => $u->acc_username,
    'status' => $u->acc_status,
    'auth' => $u->acc_auth,
    'access' => $u->acc_access,
    'lowers' => $u->acc_lowers,
    'uppers' => $u->acc_uppers,
    'lowerm' => $u->acc_lowerm,
    'upperm' => $u->acc_upperm,
]);
