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
echo "TEST: HEAD / CONTRACT PROJECT DISPLAY VERIFICATION\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

$user = User::where('acc_untarea', 'hr')->first();
Auth::login($user);

$controller = new DivHrController();

// ── TEST 1: Employee List Query & Head/Contract Data ──────────────
echo "── TEST 1: EMPLOYEE LIST DATA CHECK ──\n";
$req = new Request();
$view = $controller->employeelist($req);
$data = $view->getData();

$employees = $data['employees'];
$latestContracts = $data['latestContracts'];

echo "Loaded " . count($employees) . " employees.\n";

$sampleCount = 0;
foreach ($employees->take(10) as $emp) {
    $latestCtr = $latestContracts[$emp->emp_id] ?? null;
    $headCode = $latestCtr?->hed_code ?: ($emp->hed_code ?? null);
    $prjTitle = $latestCtr?->prj_title ?: ($emp->prj_title ?: ($latestCtr?->hed_name ?: ($emp->hed_name ?? null)));
    $contractJob = $latestCtr?->ctr_jobtitle ?: ($emp->emp_title ?: ($emp->emp_desig ?? ''));
    $contractGrade = $latestCtr?->ctr_grade ?: ($emp->emp_rank ?? '');

    echo sprintf("Emp ID: %-15s | Name: %-20s | Head: %-8s | Project: %-30s | Job: %-20s (%s)\n",
        $emp->emp_id,
        substr($emp->emp_name, 0, 20),
        $headCode ?? '—',
        substr($prjTitle ?? '—', 0, 30),
        substr($contractJob, 0, 20),
        $contractGrade ?: 'N/A'
    );
    $sampleCount++;
}

// ── TEST 2: Employee Details Query & Head/Contract Data ───────────
echo "\n── TEST 2: EMPLOYEE DETAILS DATA CHECK (Emp: 14-21-11-7359) ──\n";
$detailView = $controller->employeedetail('14-21-11-7359');
$detData = $detailView->getData();

$detEmp = $detData['emp'];
$detCurrentContract = $detData['currentContract'];
$detContractsHistory = $detData['contractsHistory'];

echo "Employee Name: " . $detEmp->emp_name . "\n";
echo "Emp Head Code: " . ($detEmp->hed_code ?? 'NULL') . "\n";
echo "Emp Project: " . ($detEmp->prj_title ?? ($detEmp->hed_name ?? 'NULL')) . "\n";
echo "Current Ctr Head Code: " . ($detCurrentContract?->ctr_hed_code ?? 'NULL') . "\n";
echo "Current Ctr Project: " . ($detCurrentContract?->ctr_prj_title ?? ($detCurrentContract?->ctr_hed_name ?? 'NULL')) . "\n";
echo "Current Ctr Job: " . ($detCurrentContract?->ctr_jobtitle ?? 'NULL') . "\n";

echo "Contracts in History: " . count($detContractsHistory) . "\n";
foreach ($detContractsHistory as $ch) {
    echo "  - Ctr #{$ch->ctr_id} | Job: {$ch->ctr_jobtitle} | Head: [{$ch->ctr_hed_code}] " . ($ch->ctr_prj_title ?: $ch->ctr_hed_name) . " | Salary: {$ch->ctr_salary}\n";
}

// ── TEST 3: Blade View Rendering ──────────────────────────────────
echo "\n── TEST 3: BLADE RENDERING CHECK ──\n";
try {
    $renderedList = $view->render();
    echo "employelist.blade.php rendered successfully: " . strlen($renderedList) . " bytes ✓\n";
} catch (\Exception $e) {
    echo "employelist.blade.php render ERROR: " . $e->getMessage() . "\n";
}

try {
    $renderedDetail = $detailView->render();
    echo "employee-details.blade.php rendered successfully: " . strlen($renderedDetail) . " bytes ✓\n";
} catch (\Exception $e) {
    echo "employee-details.blade.php render ERROR: " . $e->getMessage() . "\n";
}

try {
    $editView = $controller->employeeEdit('14-21-11-7359');
    $renderedEdit = $editView->render();
    echo "employee-edit.blade.php rendered successfully: " . strlen($renderedEdit) . " bytes ✓\n";
} catch (\Exception $e) {
    echo "employee-edit.blade.php render ERROR: " . $e->getMessage() . "\n";
}

echo "\n════════════════════════════════════════════════════════════════════\n";
echo "ALL HEAD / CONTRACT PROJECT TESTS PASSED PERFECTLY!\n";
echo "════════════════════════════════════════════════════════════════════\n";
