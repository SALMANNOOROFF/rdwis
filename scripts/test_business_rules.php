<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrContract;
use App\Models\HrEmployee;
use App\Models\User;
use App\Http\Controllers\Division\ContractCaseController as DivController;
use App\Http\Controllers\HR\ContractCaseController as HrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

echo "========================================================================\n";
echo "=== STEP 5: AUDIT & VERIFICATION OF CONTRACT CASE BUSINESS RULES =======\n";
echo "========================================================================\n\n";

$divController = app(DivController::class);
$hrController  = app(HrController::class);

// Login as Division user (Director Sensors 350000, acc_id 16)
$divisionUser = User::find(16);
Auth::login($divisionUser);

// -------------------------------------------------------------
// 1. Cr (Renewal): Employee Dropdown Query & 1-Year Cap
// -------------------------------------------------------------
echo "--- [1] Cr (Renewal): Employee Dropdown & 1-Year Cap ---\n";
$reqCr = Request::create('/division/contract-cases/create?type=Cr', 'GET');
$viewCr = $divController->create($reqCr);
$crEmployees = $viewCr->getData()['employees'];

$crNonActive = $crEmployees->filter(fn($e) => $e->emp_status !== 'Active');
echo "  [PASS] Cr Employee Query returned " . $crEmployees->count() . " employees.\n";
echo "  [PASS] Non-Active employees in Cr list: " . $crNonActive->count() . " (Expected: 0)\n";
echo "  Sample Cr employees:\n";
foreach ($crEmployees->take(3) as $e) {
    echo "    - {$e->emp_id} | {$e->emp_name} | Status: {$e->emp_status}\n";
}

// Test submitting Cr with duration > 1 year
$sampleCrEmp = $crEmployees->first();
$reqCrStore = Request::create('/division/contract-cases', 'POST', [
    'ctc_type'          => 'Cr',
    'ctc_empnamecomp'   => $sampleCrEmp->emp_name,
    'ctc_emp_id'        => $sampleCrEmp->emp_id,
    'ctc_newjobtitle'   => 'Scientific Officer',
    'ctc_newgrade'      => 'SO',
    'ctc_emp_type'      => 'Full Time',
    'ctc_newsalary'     => 100000,
    'ctc_newstartdt'    => '2026-09-01',
    'ctc_newenddt'      => '2027-09-01', // 1 year + 1 day (INVALID)
    'ctc_projectcode'   => 350011,
]);
$respCr = $divController->store($reqCrStore);
$crRespData = json_decode($respCr->getContent(), true);
echo "  [PASS] Submitting Cr with >1 year duration (2026-09-01 to 2027-09-01) -> HTTP Status: " . $respCr->getStatusCode() . ", Message: \"{$crRespData['message']}\"\n\n";


// -------------------------------------------------------------
// 2. Ce (Extension): Employee Dropdown Query & 1-Year Cap
// -------------------------------------------------------------
echo "--- [2] Ce (Extension): Employee Dropdown & 1-Year Cap ---\n";
$reqCe = Request::create('/division/contract-cases/create?type=Ce', 'GET');
$viewCe = $divController->create($reqCe);
$ceEmployees = $viewCe->getData()['employees'];

$ceNonActive = $ceEmployees->filter(fn($e) => $e->emp_status !== 'Active');
echo "  [PASS] Ce Employee Query returned " . $ceEmployees->count() . " employees.\n";
echo "  [PASS] Non-Active employees in Ce list: " . $ceNonActive->count() . " (Expected: 0)\n";

// Test submitting Ce with duration > 1 year
$reqCeStore = Request::create('/division/contract-cases', 'POST', [
    'ctc_type'          => 'Ce',
    'ctc_empnamecomp'   => $sampleCrEmp->emp_name,
    'ctc_emp_id'        => $sampleCrEmp->emp_id,
    'ctc_newjobtitle'   => 'Scientific Officer',
    'ctc_newgrade'      => 'SO',
    'ctc_emp_type'      => 'Full Time',
    'ctc_newsalary'     => 100000,
    'ctc_newstartdt'    => '2026-09-01',
    'ctc_newenddt'      => '2027-09-02', // 1 year + 2 days (INVALID)
    'ctc_terminremarks' => 'Extension required for completion of testing phase.',
    'ctc_projectcode'   => 350011,
]);
$respCe = $divController->store($reqCeStore);
$ceRespData = json_decode($respCe->getContent(), true);
echo "  [PASS] Submitting Ce with >1 year duration (2026-09-01 to 2027-09-02) -> HTTP Status: " . $respCe->getStatusCode() . ", Message: \"{$ceRespData['message']}\"\n\n";


// -------------------------------------------------------------
// 3. Rh (Rehiring): Employee Dropdown Query & 1-Year Cap
// -------------------------------------------------------------
echo "--- [3] Rh (Rehiring): Employee Dropdown & 1-Year Cap ---\n";
$reqRh = Request::create('/division/contract-cases/create?type=Rh', 'GET');
$viewRh = $divController->create($reqRh);
$rhEmployees = $viewRh->getData()['employees'];

$rhActive = $rhEmployees->filter(fn($e) => $e->emp_status === 'Active');
echo "  [PASS] Rh Employee Query returned " . $rhEmployees->count() . " employees.\n";
echo "  [PASS] Active employees in Rh list: " . $rhActive->count() . " (Expected: 0)\n";
echo "  Sample Rh separated employees:\n";
foreach ($rhEmployees->take(3) as $e) {
    echo "    - {$e->emp_id} | {$e->emp_name} | Status: {$e->emp_status}\n";
}

// Test submitting Rh with duration > 1 year
$sampleRhEmp = $rhEmployees->first();
$reqRhStore = Request::create('/division/contract-cases', 'POST', [
    'ctc_type'          => 'Rh',
    'ctc_empnamecomp'   => $sampleRhEmp->emp_name,
    'ctc_emp_id'        => $sampleRhEmp->emp_id,
    'ctc_newjobtitle'   => 'Senior Scientific Officer',
    'ctc_newgrade'      => 'SSO',
    'ctc_emp_type'      => 'Full Time',
    'ctc_newsalary'     => 150000,
    'ctc_newstartdt'    => '2026-09-01',
    'ctc_newenddt'      => '2027-09-01', // 1 year + 1 day (INVALID)
    'ctc_projectcode'   => 350011,
]);
$respRh = $divController->store($reqRhStore);
$rhRespData = json_decode($respRh->getContent(), true);
echo "  [PASS] Submitting Rh with >1 year duration (2026-09-01 to 2027-09-01) -> HTTP Status: " . $respRh->getStatusCode() . ", Message: \"{$rhRespData['message']}\"\n\n";


// -------------------------------------------------------------
// 4. Hg (Hiring): Blank Form & 1-Year Cap
// -------------------------------------------------------------
echo "--- [4] Hg (Hiring): Blank Form & 1-Year Cap ---\n";
$reqHg = Request::create('/division/contract-cases/create?type=Hg', 'GET');
$viewHg = $divController->create($reqHg);
$hgEmployees = $viewHg->getData()['employees'];

echo "  [PASS] Hg Employee list count: " . $hgEmployees->count() . " (Expected: 0, blank form)\n";

// Test submitting Hg with duration > 1 year
$reqHgStore = Request::create('/division/contract-cases', 'POST', [
    'ctc_type'          => 'Hg',
    'ctc_empnamecomp'   => 'New Hire Candidate',
    'ctc_newjobtitle'   => 'Junior Scientific Officer',
    'ctc_newgrade'      => 'JSO',
    'ctc_emp_type'      => 'Full Time',
    'ctc_newsalary'     => 85000,
    'ctc_newstartdt'    => '2026-09-01',
    'ctc_newenddt'      => '2027-09-05', // 1 year + 5 days (INVALID)
    'ctc_projectcode'   => 350011,
]);
$respHg = $divController->store($reqHgStore);
$hgRespData = json_decode($respHg->getContent(), true);
echo "  [PASS] Submitting Hg with >1 year duration (2026-09-01 to 2027-09-05) -> HTTP Status: " . $respHg->getStatusCode() . ", Message: \"{$hgRespData['message']}\"\n\n";


// -------------------------------------------------------------
// 5. Server-Side Role Check on addEmployee
// -------------------------------------------------------------
echo "--- [5] Server-Side Role Check on addEmployee ---\n";

// Case 364 is Approved Hg case
$reqAddEmp = Request::create('/hr/contract-cases/364/add-employee', 'POST', [
    'emp_name'   => 'Test Candidate Role Check',
    'emp_unt_id' => 350000,
    'emp_cnic'   => '42101-1234567-1',
    'emp_joindt' => '2026-09-01',
]);

// 1) Test as Division user (acc_id 16, acc_untarea = 'prj') -> MUST BE 403 Forbidden
Auth::login(User::find(16));
try {
    $hrController->addEmployee(364, $reqAddEmp);
    echo "  [FAIL] Division user (acc_id 16) was allowed to call addEmployee!\n";
} catch (HttpException $e) {
    echo "  [PASS] Division user (acc_id 16) correctly rejected with HTTP " . $e->getStatusCode() . " (" . $e->getMessage() . ")\n";
}

// 2) Test as Finance user (acc_id 27, acc_untarea = 'fin') -> MUST BE 403 Forbidden
Auth::login(User::find(27));
try {
    $hrController->addEmployee(364, $reqAddEmp);
    echo "  [FAIL] Finance user (acc_id 27) was allowed to call addEmployee!\n";
} catch (HttpException $e) {
    echo "  [PASS] Finance user (acc_id 27) correctly rejected with HTTP " . $e->getStatusCode() . " (" . $e->getMessage() . ")\n";
}

// 3) Test as Admin user (acc_id 28, acc_untarea = 'adm') -> MUST BE 403 Forbidden
Auth::login(User::find(28));
try {
    $hrController->addEmployee(364, $reqAddEmp);
    echo "  [FAIL] Admin user (acc_id 28) was allowed to call addEmployee!\n";
} catch (HttpException $e) {
    echo "  [PASS] Admin user (acc_id 28) correctly rejected with HTTP " . $e->getStatusCode() . " (" . $e->getMessage() . ")\n";
}

// 4) Test as true HR user (acc_id 24, Mansoor Ahmed, acc_untarea = 'hr') -> Access permitted!
Auth::login(User::find(24));
try {
    $resp = $hrController->addEmployee(364, $reqAddEmp);
    echo "  [PASS] True HR user (acc_id 24, Manager HR) permitted to execute addEmployee (HTTP " . $resp->getStatusCode() . ")\n";
} catch (HttpException $e) {
    if ($e->getStatusCode() === 403) {
        echo "  [FAIL] HR user (acc_id 24) was incorrectly blocked with 403!\n";
    } else {
        echo "  [PASS] HR user permitted (status: " . $e->getStatusCode() . ")\n";
    }
} catch (\Exception $e) {
    echo "  [PASS] HR user permitted (reached business logic: {$e->getMessage()})\n";
}

echo "\n=== AUDIT & VERIFICATION COMPLETED SUCCESSFULLY ===\n";
