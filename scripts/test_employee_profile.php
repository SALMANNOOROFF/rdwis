<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\DivHrController;
use Illuminate\Http\Request;

echo "════════════════════════════════════════════════════════════════════\n";
echo "EMPLOYEE PROFILE VIEW + EDIT CAPABILITY AUDIT & VERIFICATION\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

$targetEmpId = '14-26-09-1234';

// Ensure employee exists for testing
$existing = DB::table('hr.emps')->where('emp_id', $targetEmpId)->first();
if (!$existing) {
    echo "Creating employee $targetEmpId for test...\n";
    DB::table('hr.emps')->insert([
        'emp_id' => $targetEmpId,
        'emp_name' => 'Tariq Mehmood',
        'emp_cnic' => '35202-9876543-2',
        'emp_unt_id' => 350000,
        'emp_joindt' => '2026-09-01',
        'emp_status' => 'Active',
        'emp_title' => 'Senior Sensor Engineer',
        'emp_rank' => 'Grade 18',
    ]);
}

$controller = new DivHrController();

// ── TEST 1: PERMISSION & ACCESS GATING ──────────────────────────
echo "── TEST 1: ROLE-BASED ACCESS CONTROL ─────────────────────────────\n";

$testUsers = [
    ['acc_id' => 16, 'role' => 'Division User (prj)', 'expected' => 'ALLOW'],
    ['acc_id' => 24, 'role' => 'HR Manager (hr)',     'expected' => 'ALLOW'],
    ['acc_id' => 27, 'role' => 'Finance Officer (fin)','expected' => 'DENY'],
    ['acc_id' => 21, 'role' => 'MD (rdw)',            'expected' => 'DENY'],
    ['acc_id' => 22, 'role' => 'DDG (hqs)',           'expected' => 'DENY'],
    ['acc_id' => 20, 'role' => 'DG (nrdi)',           'expected' => 'DENY'],
];

foreach ($testUsers as $uData) {
    $user = User::find($uData['acc_id']);
    if (!$user) {
        echo "User acc_id {$uData['acc_id']} not found in DB, skipping.\n";
        continue;
    }
    Auth::setUser($user);

    $canEdit = $controller->checkCanEditEmployee($user);
    $result = $canEdit ? 'ALLOW' : 'DENY';
    $status = ($result === $uData['expected']) ? '✓ PASSED' : '✗ FAILED';

    echo "User: {$user->acc_name} (acc_id {$user->acc_id}, area: {$user->acc_untarea}) | Expected: {$uData['expected']} | Actual: {$result} -> {$status}\n";

    // Test server-side authorization abort for unauthorized roles
    if ($uData['expected'] === 'DENY') {
        try {
            $controller->authorizeEmployeeEdit($user);
            echo "  ✗ ERROR: authorizeEmployeeEdit did NOT throw 403 for {$uData['role']}!\n";
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            echo "  ✓ Confirmed HTTP {$e->getStatusCode()}: {$e->getMessage()}\n";
        }
    }
}
echo "\n";


// ── TEST 2: ATOMIC MULTI-TAB UPDATE BY DIVISION USER ────────────
echo "── TEST 2: MULTI-TAB UPDATE BY DIVISION USER (acc_id 16) ─────────\n";

$divUser = User::find(16);
Auth::setUser($divUser);

$postData = [
    // Core
    'emp_name' => 'Tariq Mehmood Updated',
    'emp_cnic' => '35202-9876543-2',
    'emp_unt_id' => 350000,
    'emp_joindt' => '2026-09-01',
    'emp_status' => 'Active',
    'emp_title' => 'Lead Sensor Engineer',
    'emp_rank' => 'Scale 18',
    'emp_lastdt' => null,
    'emp_remarks' => 'Updated by Division during profile review',

    // Personal 1
    'emp_father' => 'Mehmood Ul Hassan',
    'emp_father_cnic' => '35202-1234567-1',
    'emp_dob' => '1992-05-14',
    'emp_gender' => 'Male',
    'emp_marital' => 'Married',
    'emp_ntnlty' => 'Pakistani',
    'emp_pob' => 'Lahore',
    'emp_mobile' => '0300-1234567',
    'emp_mobile2' => '0321-7654321',
    'emp_landline' => '042-35890000',
    'emp_email' => 'tariq.mehmood@example.com',
    'emp_discip' => 'Electrical Engineering',
    'emp_spec' => 'RF & Radar Sensors',
    'emp_paddress' => 'House 123, Street 4, Sector G, DHA Phase 5, Lahore',
    'emp_taddress' => 'Apartment 4B, Naval Complex, E-8, Islamabad',

    // Personal 2
    'emp_nokname' => 'Ayesha Tariq',
    'emp_nokrelation' => 'Spouse',
    'emp_nokcnic' => '35202-8888888-4',
    'emp_emername' => 'Farhan Mehmood',
    'emp_emerrelation' => 'Brother',
    'emp_emermobile' => '0333-5554433',
    'emp_idmark' => 'Scar on left forearm',
    'emp_height' => 5.11,
    'emp_caste' => 'Rajput',
    'emp_religion' => 'Islam',
    'emp_sect' => 'Sunni',
    'emp_police' => 'Margalla Police Station',
    'emp_political' => 'None',

    // Official
    'emp_cnum' => 'SEC-CLR-2026-991',
    'emp_cissuedt' => '2026-08-01',
    'emp_cexpdt' => '2028-08-01',
    'emp_secclear' => 'Cleared',

    // Education (Degrees)
    'degrees' => [
        [
            'qlf_name' => 'B.Sc Electrical Engineering',
            'qlf_inst' => 'UET Lahore',
            'qlf_spec' => 'Telecommunications',
            'qlf_duration' => 4,
            'qlf_unit' => 'Years',
            'qlf_enddt' => '2014-06-30',
            'qlf_grade' => '3.75 CGPA',
            'qlf_level' => 16,
        ],
        [
            'qlf_name' => 'M.Sc RF & Microwave Systems',
            'qlf_inst' => 'NUST Islamabad',
            'qlf_spec' => 'Radar Systems',
            'qlf_duration' => 2,
            'qlf_unit' => 'Years',
            'qlf_enddt' => '2017-08-15',
            'qlf_grade' => '3.90 CGPA',
            'qlf_level' => 18,
        ],
    ],

    // Courses & Certs
    'certs' => [
        [
            'qlf_name' => 'Certified Radar Systems Specialist',
            'qlf_inst' => 'IEEE Aerospace',
            'qlf_license' => 'IEEE-RS-8821',
            'qlf_duration' => 6,
            'qlf_unit' => 'Months',
            'qlf_enddt' => '2020-03-10',
            'qlf_grade' => 'Distinction',
            'qlf_level' => 0,
        ],
    ],

    // Career
    'jobs' => [
        [
            'job_company' => 'National Electronics Complex',
            'job_jobtitle' => 'RF Design Engineer',
            'job_repto' => 'Dr. Kamran Malik',
            'job_team' => 6,
            'job_from' => '2017-09-01',
            'job_to' => '2021-08-31',
            'job_city' => 'Islamabad',
            'job_resp' => 'Designed L-band Transceiver modules for airborne radar',
            'job_ach' => 'Published 2 IEEE papers and filed 1 patent',
        ],
        [
            'job_company' => 'Optima Tech Solutions',
            'job_jobtitle' => 'Senior Sensor Specialist',
            'job_repto' => 'CEO',
            'job_team' => 12,
            'job_from' => '2021-09-01',
            'job_to' => '2026-08-15',
            'job_city' => 'Rawalpindi',
            'job_resp' => 'Led sensor integration team across 4 strategic defense projects',
            'job_ach' => 'Delivered all projects ahead of schedule with 99.8% MTBF',
        ],
    ],

    // Vehicles
    'vehicles' => [
        [
            'vcl_type' => 'Car',
            'vcl_maker' => 'Honda',
            'vcl_variant' => 'Civic Oriel 1.8',
            'vcl_year' => 2021,
            'vcl_regis' => 'ICT-LEE-440',
            'vcl_color' => 'Taffeta White',
        ],
    ],

    // Devices
    'devices' => [
        [
            'dvc_type' => 'Mobile Phone',
            'dvc_brand' => 'Apple',
            'dvc_model' => 'iPhone 15 Pro',
            'dvc_imei1' => '352984110987654',
            'dvc_imei2' => '352984110987655',
        ],
        [
            'dvc_type' => 'Laptop',
            'dvc_brand' => 'Dell',
            'dvc_model' => 'Precision 5570 Workstation',
            'dvc_imei1' => 'DELL-SN-99887711',
            'dvc_imei2' => null,
        ],
    ],

    // Bank Accounts
    'bank_accounts' => [
        [
            'bac_bnkname' => 'Habib Bank Limited',
            'bac_bchname' => 'F-7 Markaz Branch',
            'bac_bchcode' => '0482',
            'bac_acctitle' => 'Tariq Mehmood',
            'bac_accnum' => 'PK45HABB0004827901234501',
            'bac_bchcity' => 'Islamabad',
            'bac_selforpay' => 1,
        ],
        [
            'bac_bnkname' => 'Meezan Bank Limited',
            'bac_bchname' => 'Blue Area Branch',
            'bac_bchcode' => '0102',
            'bac_acctitle' => 'Tariq Mehmood',
            'bac_accnum' => 'PK88MEZN0001020104567890',
            'bac_bchcity' => 'Islamabad',
            'bac_selforpay' => 0,
        ],
    ],
];

$request = Request::create("/divhr/employee/{$targetEmpId}/update", 'POST', $postData);
$response = $controller->employeeUpdate($request, $targetEmpId);

echo "Update Response Status: " . $response->getStatusCode() . "\n";
echo "Update Response Body: " . json_encode($response->getData()) . "\n\n";

// ── TEST 2B: UPDATE BY HR USER (acc_id 24) ──────────────────────
echo "── TEST 2B: UPDATE BY HR USER (acc_id 24, Mansoor Ahmed) ─────────\n";
$hrUser = User::find(24);
Auth::setUser($hrUser);

$hrPostData = $postData;
$hrPostData['emp_title'] = 'Principal Sensor Systems Engineer';
$hrPostData['emp_rank'] = 'Scale 19';
$hrPostData['emp_remarks'] = 'Promoted & verified by HR';

$hrRequest = Request::create("/divhr/employee/{$targetEmpId}/update", 'POST', $hrPostData);
$hrResponse = $controller->employeeUpdate($hrRequest, $targetEmpId);

echo "HR Update Response Status: " . $hrResponse->getStatusCode() . "\n";
echo "HR Update Response Body: " . json_encode($hrResponse->getData()) . "\n";
$updatedRow = DB::table('hr.emps')->where('emp_id', $targetEmpId)->first();
echo "Confirmed in DB -> Title: {$updatedRow->emp_title} | Rank: {$updatedRow->emp_rank} | Remarks: {$updatedRow->emp_remarks}\n\n";

// ── TEST 3: RAW DATABASE RECORD VERIFICATION ────────────────────
echo "── TEST 3: RAW DATABASE RECORD VERIFICATION (SELECT * per table) ──\n";

$tables = [
    'hr.emps' => "SELECT emp_id, emp_name, emp_cnic, emp_unt_id, emp_title, emp_rank, emp_status FROM hr.emps WHERE emp_id = '{$targetEmpId}'",
    'hr.empsexta' => "SELECT empexta_emp_id, emp_father, emp_dob, emp_gender, emp_mobile, emp_email, emp_spec FROM hr.empsexta WHERE empexta_emp_id = '{$targetEmpId}'",
    'hr.empsextb' => "SELECT empextb_emp_id, emp_nokname, emp_nokrelation, emp_emername, emp_emermobile, emp_idmark, emp_religion FROM hr.empsextb WHERE empextb_emp_id = '{$targetEmpId}'",
    'hr.empsextc' => "SELECT empextc_emp_id, emp_cnum, emp_cissuedt, emp_cexpdt, emp_secclear FROM hr.empsextc WHERE empextc_emp_id = '{$targetEmpId}'",
    'hr.qualifs (Degree)' => "SELECT qlf_id, qlf_type, qlf_name, qlf_inst, qlf_duration, qlf_unit, qlf_grade FROM hr.qualifs WHERE qlf_emp_id = '{$targetEmpId}' AND qlf_type = 'Degree'",
    'hr.qualifs (Course)' => "SELECT qlf_id, qlf_type, qlf_name, qlf_inst, qlf_license, qlf_duration FROM hr.qualifs WHERE qlf_emp_id = '{$targetEmpId}' AND qlf_type = 'Course'",
    'hr.jobs' => "SELECT job_id, job_company, job_jobtitle, job_repto, job_team, job_from, job_to, job_city FROM hr.jobs WHERE job_emp_id = '{$targetEmpId}'",
    'hr.vehicles' => "SELECT vcl_id, vcl_type, vcl_maker, vcl_variant, vcl_year, vcl_regis, vcl_color FROM hr.vehicles WHERE vcl_emp_id = '{$targetEmpId}'",
    'hr.devices' => "SELECT dvc_id, dvc_type, dvc_brand, dvc_model, dvc_imei1, dvc_imei2 FROM hr.devices WHERE dvc_emp_id = '{$targetEmpId}'",
    'hr.bnkaccounts' => "SELECT bac_id, bac_bnkname, bac_acctitle, bac_accnum, bac_bchname, bac_bchcity, bac_selforpay FROM hr.bnkaccounts WHERE bac_emp_id = '{$targetEmpId}'",
];

foreach ($tables as $label => $sql) {
    echo "TABLE: {$label}\n";
    $rows = DB::select($sql);
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
}

echo "════════════════════════════════════════════════════════════════════\n";
echo "AUDIT COMPLETED SUCCESSFULLY!\n";
echo "════════════════════════════════════════════════════════════════════\n";
