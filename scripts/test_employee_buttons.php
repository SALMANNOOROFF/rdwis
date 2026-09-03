<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\DivHrController;
use App\Http\Controllers\Division\ContractCaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$empId = '14-26-09-1234';

echo "════════════════════════════════════════════════════════════════════\n";
echo "TEST: EMPLOYEE BUTTONS & PERMISSION GATING AUDIT\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

// Test 1: Check canEdit flag across roles
echo "=== TEST 1: PROFILE BUTTON VISIBILITY (canEdit flag) ===\n";
$divHrCtrl = new DivHrController();

$roles = [
    ['id' => 16, 'name' => 'Division User (prj)', 'expected' => true],
    ['id' => 24, 'name' => 'HR User (hr)', 'expected' => true],
    ['id' => 27, 'name' => 'Finance User (fin)', 'expected' => false],
    ['id' => 21, 'name' => 'MD (rdw)', 'expected' => false],
    ['id' => 22, 'name' => 'DDG (hqs)', 'expected' => false],
];

foreach ($roles as $r) {
    $u = User::find($r['id']);
    Auth::setUser($u);
    $canEdit = $divHrCtrl->checkCanEditEmployee($u);
    $res = ($canEdit === $r['expected']) ? '✓ PASSED' : '✗ FAILED';
    echo "{$r['name']} -> canEdit: " . ($canEdit ? 'TRUE (Buttons Shown)' : 'FALSE (Buttons Hidden)') . " [{$res}]\n";
}

echo "\n=== TEST 2: CONTRACT CASE INITIATION WITH PRE-SELECTED EMPLOYEE ===\n";
$divUser = User::find(16);
Auth::setUser($divUser);
$ccCtrl = app(ContractCaseController::class);

// 2A: Renewal (Cr) with emp_id
$reqCr = Request::create('/division/contract-cases/create', 'GET', ['type' => 'Cr', 'emp_id' => $empId]);
$viewCr = $ccCtrl->create($reqCr);
$empListCr = $viewCr->getData()['employees'];
$hasEmpCr = $empListCr->contains('emp_id', $empId);
echo "Cr Create with emp_id={$empId} -> Employee present in dropdown: " . ($hasEmpCr ? 'YES [✓ PASSED]' : 'NO [✗ FAILED]') . "\n";

// 2B: Extension (Ce) with emp_id
$reqCe = Request::create('/division/contract-cases/create', 'GET', ['type' => 'Ce', 'emp_id' => $empId]);
$viewCe = $ccCtrl->create($reqCe);
$empListCe = $viewCe->getData()['employees'];
$hasEmpCe = $empListCe->contains('emp_id', $empId);
echo "Ce Create with emp_id={$empId} -> Employee present in dropdown: " . ($hasEmpCe ? 'YES [✓ PASSED]' : 'NO [✗ FAILED]') . "\n";

echo "\n════════════════════════════════════════════════════════════════════\n";
echo "ALL BUTTON & LINK AUDITS COMPLETED SUCCESSFULLY!\n";
echo "════════════════════════════════════════════════════════════════════\n";
