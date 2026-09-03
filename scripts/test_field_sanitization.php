<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DivHrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::where('acc_untarea', 'hr')->first();
Auth::login($user);

$controller = new DivHrController();
$req = new Request([
    'emp_name' => 'Tariq Mehmood Sanitized',
    'emp_cnic' => '35202-9876543-2',
    'emp_joindt' => '2026-08-01',
    'emp_unt_id' => 350000,
    'emp_status' => 'Active',
    'emp_father' => 'Mehmood Ul Hassan',
    'emp_father_cnic' => '44544846987778778', // 17 digits!
    'emp_mobile' => '03313096734111222', // extra long!
    'emp_nokname' => 'Ayesha Tariq',
    'emp_nokcnic' => '9999988888777666', // 16 digits!
    'devices' => [
        ['dvc_brand' => 'Apple', 'dvc_imei1' => '12345678901234567899999'] // 23 chars!
    ]
]);

try {
    $res = $controller->employeeUpdate($req, '14-26-09-1234');
    echo "SUCCESS: " . json_encode($res->getData()) . "\n";
    
    $empA = DB::table('hr.empsexta')->where('empexta_emp_id', '14-26-09-1234')->first();
    $empB = DB::table('hr.empsextb')->where('empextb_emp_id', '14-26-09-1234')->first();
    $dvc = DB::table('hr.devices')->where('dvc_emp_id', '14-26-09-1234')->first();
    
    echo "Stored Father CNIC: " . ($empA->emp_father_cnic ?? 'NULL') . " (len: " . strlen($empA->emp_father_cnic ?? '') . ")\n";
    echo "Stored Mobile: " . ($empA->emp_mobile ?? 'NULL') . " (len: " . strlen($empA->emp_mobile ?? '') . ")\n";
    echo "Stored NOK CNIC: " . ($empB->emp_nokcnic ?? 'NULL') . " (len: " . strlen($empB->emp_nokcnic ?? '') . ")\n";
    echo "Stored Device IMEI: " . ($dvc->dvc_imei1 ?? 'NULL') . " (len: " . strlen($dvc->dvc_imei1 ?? '') . ")\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
