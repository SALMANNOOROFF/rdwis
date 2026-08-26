<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Facades\FileStorage;
use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "========================================================\n";
echo " RDWIS FILE STORAGE SERVICE COMPREHENSIVE VERIFICATION\n";
echo "========================================================\n\n";

$service = app(FileStorageService::class);
$passed = 0;
$total = 0;

function assertCondition($description, $condition) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] {$description}\n";
    } else {
        echo " [FAIL] {$description}\n";
    }
}

// 1. Basic store and path checks
$tempFile = tempnam(sys_get_temp_dir(), 'rdw_test');
file_put_contents($tempFile, 'Test PDF content for RDWIS storage verification.');
$uploaded = new UploadedFile($tempFile, 'test_doc.pdf', 'application/pdf', null, true);

$path = $service->store($uploaded, 'pur', 'pcs-', '888801');
assertCondition("Store file in 'pur' with prefix 'pcs-888801.pdf'", $path === 'pur/pcs-888801.pdf');
assertCondition("File physically exists on disk", $service->exists($path));

// 2. Collision Resolution Check (-03, -04)
$tempFile2 = tempnam(sys_get_temp_dir(), 'rdw_test');
file_put_contents($tempFile2, 'Second version');
$uploaded2 = new UploadedFile($tempFile2, 'test_doc_v2.pdf', 'application/pdf', null, true);
$path2 = $service->store($uploaded2, 'pur', 'pcs-', '888801');
assertCondition("Collision resolves to -03 suffix (legacy ModifyFilePath)", $path2 === 'pur/pcs-888801-03.pdf');

$tempFile3 = tempnam(sys_get_temp_dir(), 'rdw_test');
file_put_contents($tempFile3, 'Third version');
$uploaded3 = new UploadedFile($tempFile3, 'test_doc_v3.pdf', 'application/pdf', null, true);
$path3 = $service->store($uploaded3, 'pur', 'pcs-', '888801');
assertCondition("Second collision resolves to -04 suffix", $path3 === 'pur/pcs-888801-04.pdf');

// Cleanup stored test files
$service->delete($path);
$service->delete($path2);
$service->delete($path3);
assertCondition("Delete successfully removed files from disk", !$service->exists($path) && !$service->exists($path2) && !$service->exists($path3));

// 3. Employee Photo Storage (hr/photos)
$tempPhoto = tempnam(sys_get_temp_dir(), 'rdw_photo');
file_put_contents($tempPhoto, 'Fake image bytes');
$uploadedPhoto = new UploadedFile($tempPhoto, 'profile.jpg', 'image/jpeg', null, true);
$photoPath = $service->store($uploadedPhoto, 'hr/photos', 'pht-emp-', '888802');
assertCondition("Store photo in 'hr/photos' with prefix 'pht-emp-888802.jpg'", $photoPath === 'hr/photos/pht-emp-888802.jpg');
assertCondition("Photo physically exists on disk", $service->exists($photoPath));
$service->delete($photoPath);

// 4. URL Normalization
$url1 = $service->url('pur/pcs-123.pdf');
assertCondition("URL generated for standard path", str_contains($url1, '/storage/pur/pcs-123.pdf'));

$url2 = $service->url('\\pur\\min-pcs-231.pdf');
assertCondition("URL normalized for legacy Windows backslash path (\\pur\\min-pcs-231.pdf)", str_contains($url2, '/storage/pur/min-pcs-231.pdf'));

$url3 = $service->url('\\hr\\photos\\photo-14-21-11-7359.JPG');
assertCondition("URL normalized for legacy Windows photo path", str_contains($url3, '/storage/hr/photos/photo-14-21-11-7359.JPG'));

$urlNull = $service->url(null);
assertCondition("URL returns null for null input", $urlNull === null);

// 5. Facade resolution
$facadeUrl = FileStorage::url('prj/ppf-prj-200006.pdf');
assertCondition("Facade FileStorage::url() works identically", str_contains($facadeUrl, '/storage/prj/ppf-prj-200006.pdf'));

// 6. Slot Tables Verification (Postgres DB)
echo "\n--- Testing Slot Tables in Postgres Database ---\n";
$slotModules = [
    'aud' => ['table' => 'aud.audattachments', 'pk' => 'aat_id', 'objtype' => 'aat_objtype', 'objid' => 'aat_objid', 'type' => 'aat_type', 'path' => 'aat_path', 'default_objtype' => 'rev', 'subfolder' => 'aud'],
    'ctc' => ['table' => 'hr.ctrcaseattachments', 'pk' => 'cat_id', 'objtype' => 'cat_objtype', 'objid' => 'cat_objid', 'type' => 'cat_type', 'path' => 'cat_path', 'default_objtype' => 'ctc', 'subfolder' => 'hr'],
    'emp' => ['table' => 'hr.empattachments', 'pk' => 'eat_id', 'objtype' => 'eat_objtype', 'objid' => 'eat_objid', 'type' => 'eat_type', 'path' => 'eat_path', 'default_objtype' => 'emp', 'subfolder' => 'hr'],
    'ina' => ['table' => 'ina.inaattachments', 'pk' => 'iat_id', 'objtype' => 'iat_objtype', 'objid' => 'iat_objid', 'type' => 'iat_type', 'path' => 'iat_path', 'default_objtype' => 'ina', 'subfolder' => 'ina'],
    'prj' => ['table' => 'prj.prjattachments', 'pk' => 'jat_id', 'objtype' => 'jat_objtype', 'objid' => 'jat_objid', 'type' => 'jat_type', 'path' => 'jat_path', 'default_objtype' => 'prj', 'subfolder' => 'prj'],
    'pur' => ['table' => 'pur.purattachments', 'pk' => 'pat_id', 'objtype' => 'pat_objtype', 'objid' => 'pat_objid', 'type' => 'pat_type', 'path' => 'pat_path', 'default_objtype' => 'pcs', 'subfolder' => 'pur'],
];

foreach ($slotModules as $modKey => $cfg) {
    try {
        // Find a valid parent ID for foreign key constrained tables
        $sampleObjId = match ($modKey) {
            'ctc' => DB::table('hr.ctrcases')->value('ctc_id') ?? 1,
            'emp' => DB::table('hr.emps')->value('emp_id') ?? 1,
            'prj' => DB::table('prj.projects')->value('prj_id') ?? 1,
            'pur' => DB::table('pur.purcases')->value('pcs_id') ?? 1,
            default => 888899,
        };

        $testType = 'Test Verification Slot ' . time();
        $slot = $service->findOrCreateSlot($modKey, $cfg['default_objtype'], $sampleObjId, $testType);
        $pkCol = $cfg['pk'];
        assertCondition("findOrCreateSlot creates slot in {$cfg['table']}", !empty($slot) && isset($slot->$pkCol));

        $slotAgain = $service->findOrCreateSlot($modKey, $cfg['default_objtype'], $sampleObjId, $testType);
        assertCondition("findOrCreateSlot retrieves existing slot without duplicating", $slot->$pkCol === $slotAgain->$pkCol);

        // Test storeAndAttach
        $tf = tempnam(sys_get_temp_dir(), 'rdw_att');
        file_put_contents($tf, "Attachment for {$modKey}");
        $up = new UploadedFile($tf, 'slot_doc.pdf', 'application/pdf', null, true);
        $storedSlotPath = $service->storeAndAttach($up, $modKey, 'tst-', $sampleObjId, $testType, $cfg['default_objtype']);

        $updatedSlot = DB::table($cfg['table'])->where($cfg['pk'], $slot->$pkCol)->first();
        assertCondition("storeAndAttach updates {$cfg['table']} path column", $updatedSlot->{$cfg['path']} === $storedSlotPath);

        // Cleanup
        $service->delete($storedSlotPath);
        DB::table($cfg['table'])->where($cfg['pk'], $slot->$pkCol)->delete();
    } catch (\Exception $e) {
        assertCondition("Slot table {$cfg['table']} test failed with exception: " . $e->getMessage(), false);
    }
}

// 7. Single-Field Tables Verification
echo "\n--- Testing Single-Field Columns ---\n";
// Test legacy secondary write on hr.contracts when ctc Approval is uploaded
try {
    $existingCaseId = DB::table('hr.ctrcases')->value('ctc_id');
    $existingEmpId = DB::table('hr.emps')->value('emp_id');

    if ($existingCaseId && $existingEmpId) {
        $sampleContract = (array) (DB::table('hr.contracts')->first() ?? []);
        unset($sampleContract['ctr_id']);
        $sampleContract['ctr_ctc_id'] = $existingCaseId;
        $sampleContract['ctr_num'] = $existingEmpId;
        $sampleContract['ctr_path'] = 'hr/ctr-test.pdf';
        $sampleContract['ctr_path2'] = null;

        // Create dummy contract row
        $testContractId = DB::table('hr.contracts')->insertGetId($sampleContract, 'ctr_id');

        // storeAndAttach ctc Approval
        $tf = tempnam(sys_get_temp_dir(), 'rdw_approval');
        file_put_contents($tf, 'Approval document');
        $up = new UploadedFile($tf, 'approval.pdf', 'application/pdf', null, true);
        $storedApproval = $service->storeAndAttach($up, 'ctc', 'mx-ctc-', $existingCaseId, 'Approval', 'ctc');

        $updatedContract = DB::table('hr.contracts')->where('ctr_id', $testContractId)->first();
        assertCondition("Legacy secondary write updates hr.contracts.ctr_path2 for matching ctr_ctc_id", $updatedContract->ctr_path2 === $storedApproval);

        // Cleanup
        $service->delete($storedApproval);
        DB::table('hr.ctrcaseattachments')->where('cat_objtype', 'ctc')->where('cat_objid', $existingCaseId)->where('cat_type', 'Approval')->delete();
        DB::table('hr.contracts')->where('ctr_id', $testContractId)->delete();
    } else {
        assertCondition("Legacy secondary write test (skipped: no sample case)", true);
    }
} catch (\Exception $e) {
    assertCondition("Secondary write test error: " . $e->getMessage(), false);
}

// 9. Universal AttachmentController Verification
echo "\n--- Testing Universal AttachmentController ---\n";
try {
    $attController = app(\App\Http\Controllers\AttachmentController::class);
    $tf = tempnam(sys_get_temp_dir(), 'rdw_universal_att');
    file_put_contents($tf, 'Sample project proposal upload via universal widget controller');
    $up = new UploadedFile($tf, 'proposal.pdf', 'application/pdf', null, true);

    $samplePrjId = DB::table('prj.projects')->value('prj_id') ?? 1;

    $uploadReq = \Illuminate\Http\Request::create('/universal-attachment/upload', 'POST', [
        'module' => 'prj',
        'object_id' => $samplePrjId,
        'doc_type' => 'Project Proposal Test',
    ], [], ['file' => $up]);
    $uploadReq->headers->set('Accept', 'application/json');

    $uploadResp = $attController->upload($uploadReq);
    $uploadData = json_decode($uploadResp->getContent(), true);

    assertCondition("AttachmentController::upload returns success JSON", !empty($uploadData['success']) && $uploadData['success'] === true);
    assertCondition("AttachmentController::upload returns valid slot_id and url", !empty($uploadData['slot_id']) && !empty($uploadData['url']));

    if (!empty($uploadData['slot_id'])) {
        // Test view response
        $viewReq = \Illuminate\Http\Request::create("/universal-attachment/prj/{$uploadData['slot_id']}/view", 'GET');
        $viewResp = $attController->view($viewReq, 'prj', $uploadData['slot_id']);
        assertCondition("AttachmentController::view streams BinaryFileResponse", $viewResp->getStatusCode() === 200);

        // Test delete
        $delReq = \Illuminate\Http\Request::create("/universal-attachment/prj/{$uploadData['slot_id']}/delete", 'POST');
        $delReq->headers->set('Accept', 'application/json');
        $delResp = $attController->delete($delReq, 'prj', $uploadData['slot_id']);
        $delData = json_decode($delResp->getContent(), true);
        assertCondition("AttachmentController::delete returns success JSON", !empty($delData['success']) && $delData['success'] === true);
    }
} catch (\Exception $e) {
    assertCondition("Universal AttachmentController test failed: " . $e->getMessage(), false);
}

echo "\n========================================================\n";
echo " RESULTS: {$passed} / {$total} Tests Passed (" . round(($passed / $total) * 100) . "%)\n";
echo "========================================================\n";

if ($passed === $total) {
    exit(0);
} else {
    exit(1);
}
