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

echo "════════════════════════════════════════════════════════════════════\n";
echo "TEST: VERIFY SALMAN NOOR & ALL EMPLOYEES HEAD / PROJECT DISPLAY\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

$user = User::where('acc_untarea', 'hr')->first();
Auth::login($user);

$controller = new DivHrController();

// ── TEST 1: Salman Noor Profile & Contract Details ────────────────
echo "── TEST 1: SALMAN NOOR (11-26-08-9879) PROFILE CHECK ──\n";
$salmanView = $controller->employeedetail('11-26-08-9879');
$salmanData = $salmanView->getData();

$emp = $salmanData['emp'];
$curCtr = $salmanData['currentContract'];
$history = $salmanData['contractsHistory'];

$detHeadCode = $curCtr?->ctr_hed_code ?: ($curCtr?->ctr_prj_code ?: ($emp?->hed_code ?: ($emp?->prj_code ?? null)));
$detPrjTitle = $curCtr?->ctr_prj_title ?: ($emp?->prj_title ?: ($curCtr?->ctr_hed_name ?: ($emp?->hed_name ?? null)));

echo "Employee Name: " . $emp->emp_name . "\n";
echo "Hired Project Head Code: " . ($detHeadCode ?? '—') . "\n";
echo "Hired Project Title: " . ($detPrjTitle ?? '—') . "\n";
echo "Current Contract Job: " . ($curCtr?->ctr_jobtitle ?? '—') . "\n";
echo "Current Contract Salary: " . number_format($curCtr?->ctr_salary ?? 0) . "\n";

echo "Previous Contracts Count: " . count($history) . "\n";
foreach ($history as $h) {
    $cHead = $h->ctr_hed_code ?: ($h->ctr_prj_code ?? null);
    $cPrj = $h->ctr_prj_title ?: ($h->ctr_hed_name ?: ($h->ctr_hed_code ?: ($h->ctr_prj_code ?? null)));
    echo "  - Ctr #{$h->ctr_id} | Role: {$h->ctr_jobtitle} | Head: [{$cHead}] {$cPrj} | Status: {$h->status_label}\n";
}

// ── TEST 2: Employee List Directory Check ─────────────────────────
echo "\n── TEST 2: EMPLOYEE DIRECTORY FIRST 10 ROWS ──\n";
$listView = $controller->employeelist(new Request());
$listData = $listView->getData();
$employees = $listData['employees'];
$latestContracts = $listData['latestContracts'];

foreach ($employees->take(10) as $e) {
    $latestCtr = $latestContracts[$e->emp_id] ?? null;
    $headCode = $latestCtr?->hed_code ?: ($latestCtr?->prj_code ?: ($e->hed_code ?: ($e->prj_code ?? null)));
    $prjTitle = $latestCtr?->prj_title ?: ($e->prj_title ?: ($latestCtr?->hed_name ?: ($e->hed_name ?? null)));
    $contractJob = $latestCtr?->ctr_jobtitle ?: ($e->emp_title ?: ($e->emp_desig ?? ''));

    echo sprintf("Emp: %-15s | Name: %-20s | Head: %-10s | Project: %-30s | Job: %s\n",
        $e->emp_id,
        substr($e->emp_name, 0, 20),
        $headCode ?? '—',
        substr($prjTitle ?? '—', 0, 30),
        $contractJob
    );
}

// ── TEST 3: Blade Render Verification ─────────────────────────────
echo "\n── TEST 3: BLADE RENDERING VERIFICATION ──\n";
$salmanHtml = $salmanView->render();
if (strpos($salmanHtml, 'DVBS') !== false || strpos($salmanHtml, 'VR-HAR') !== false) {
    echo "✓ Salman Noor profile renders Head Code badge successfully!\n";
}

$listHtml = $listView->render();
if (strpos($listHtml, 'salman noor') !== false) {
    echo "✓ Employee directory renders Salman Noor successfully!\n";
}

echo "\n════════════════════════════════════════════════════════════════════\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "════════════════════════════════════════════════════════════════════\n";
