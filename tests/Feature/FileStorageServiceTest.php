<?php

namespace Tests\Feature;

use App\Facades\FileStorage;
use App\Models\AudAttachment;
use App\Models\Employee;
use App\Models\HrContract;
use App\Models\HrCtrCaseAttachment;
use App\Models\HrEmpAttachment;
use App\Models\InaAttachment;
use App\Models\Milestone;
use App\Models\PrgHistory;
use App\Models\PrjAttachment;
use App\Models\PurAttachment;
use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageServiceTest extends TestCase
{
    protected FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = app(FileStorageService::class);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ATTACH DATABASE ":memory:" AS pur;');
            DB::statement('CREATE TABLE IF NOT EXISTS pur.purattachments (
                pat_id INTEGER PRIMARY KEY AUTOINCREMENT,
                pat_objtype TEXT,
                pat_objid INTEGER,
                pat_type TEXT,
                pat_path TEXT
            );');

            DB::statement('ATTACH DATABASE ":memory:" AS hr;');
            DB::statement('CREATE TABLE IF NOT EXISTS hr.ctrcaseattachments (
                cat_id INTEGER PRIMARY KEY AUTOINCREMENT,
                cat_objtype TEXT,
                cat_objid INTEGER,
                cat_type TEXT,
                cat_path TEXT
            );');
            DB::statement('CREATE TABLE IF NOT EXISTS hr.contracts (
                ctr_id INTEGER PRIMARY KEY AUTOINCREMENT,
                ctr_ctc_id INTEGER,
                ctr_num TEXT,
                ctr_unt_id INTEGER,
                ctr_startdt TEXT,
                ctr_enddt TEXT,
                ctr_date TEXT,
                ctr_path TEXT,
                ctr_path2 TEXT
            );');
        }
    }

    public function test_can_store_file_in_purchase_module_with_prefix(): void
    {
        $file = UploadedFile::fake()->create('quotation.pdf', 100, 'application/pdf');
        $path = $this->service->store($file, 'pur', 'pcs-', '999991');

        $this->assertEquals('pur/pcs-999991.pdf', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        // Cleanup
        $this->service->delete($path);
    }

    public function test_duplicate_file_upload_appends_legacy_collision_suffix(): void
    {
        $file1 = UploadedFile::fake()->create('case_doc.pdf', 100, 'application/pdf');
        $path1 = $this->service->store($file1, 'pur', 'pcs-', '999992');
        $this->assertEquals('pur/pcs-999992.pdf', $path1);

        $file2 = UploadedFile::fake()->create('case_doc_v2.pdf', 100, 'application/pdf');
        $path2 = $this->service->store($file2, 'pur', 'pcs-', '999992');
        $this->assertEquals('pur/pcs-999992-03.pdf', $path2);

        $file3 = UploadedFile::fake()->create('case_doc_v3.pdf', 100, 'application/pdf');
        $path3 = $this->service->store($file3, 'pur', 'pcs-', '999992');
        $this->assertEquals('pur/pcs-999992-04.pdf', $path3);

        // Cleanup
        $this->service->delete($path1);
        $this->service->delete($path2);
        $this->service->delete($path3);
    }

    public function test_can_store_employee_photo_in_hr_photos(): void
    {
        $photo = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');
        $path = $this->service->store($photo, 'hr/photos', 'pht-emp-', '999993');

        $this->assertEquals('hr/photos/pht-emp-999993.jpg', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        // Cleanup
        $this->service->delete($path);
    }

    public function test_url_normalization_handles_forward_and_legacy_backslashes(): void
    {
        $url1 = $this->service->url('pur/pcs-123.pdf');
        $this->assertStringContainsString('/storage/pur/pcs-123.pdf', $url1);

        $url2 = $this->service->url('\\pur\\min-pcs-231.pdf');
        $this->assertStringContainsString('/storage/pur/min-pcs-231.pdf', $url2);

        $url3 = $this->service->url('\\hr\\photos\\photo-14-21-11-7359.JPG');
        $this->assertStringContainsString('/storage/hr/photos/photo-14-21-11-7359.JPG', $url3);

        $this->assertNull($this->service->url(null));
        $this->assertNull($this->service->url(''));
    }

    public function test_facade_resolves_methods(): void
    {
        $url = FileStorage::url('prj/ppf-prj-200006.pdf');
        $this->assertStringContainsString('/storage/prj/ppf-prj-200006.pdf', $url);
    }

    public function test_delete_removes_file_from_disk(): void
    {
        $file = UploadedFile::fake()->create('temp.pdf', 50, 'application/pdf');
        $path = $this->service->store($file, 'aud', 'rev-', '999994');

        $this->assertTrue($this->service->exists($path));
        $this->assertTrue($this->service->delete($path));
        $this->assertFalse($this->service->exists($path));
    }

    public function test_find_or_create_slot_creates_and_retrieves_slot(): void
    {
        $slot = $this->service->findOrCreateSlot('pur', 'pcs', 999995, 'Financial Status');
        $this->assertNotNull($slot);
        $this->assertEquals('pcs', $slot->pat_objtype);
        $this->assertEquals(999995, $slot->pat_objid);
        $this->assertEquals('Financial Status', $slot->pat_type);

        // Fetching again should return existing
        $slotAgain = $this->service->findOrCreateSlot('pur', 'pcs', 999995, 'Financial Status');
        $this->assertEquals($slot->pat_id, $slotAgain->pat_id);

        // Cleanup DB row
        DB::table('pur.purattachments')->where('pat_id', $slot->pat_id)->delete();
    }

    public function test_store_and_attach_updates_slot_and_handles_legacy_secondary_write(): void
    {
        $ctrcase = DB::table('hr.ctrcases')->first();
        if (!$ctrcase) {
            $this->markTestSkipped('No ctrcase found.');
        }

        $objId = $ctrcase->ctc_id;
        $file = UploadedFile::fake()->create('approval.pdf', 100, 'application/pdf');
        $path = $this->service->storeAndAttach($file, 'ctc', 'mx-ctc-', $objId, 'Approval', 'ctc');

        $this->assertEquals("hr/mx-ctc-{$objId}.pdf", $path);

        $slot = DB::table('hr.ctrcaseattachments')
            ->where('cat_objtype', 'ctc')
            ->where('cat_objid', $objId)
            ->where('cat_type', 'Approval')
            ->first();

        $this->assertNotNull($slot);
        $this->assertEquals($path, $slot->cat_path);

        // Cleanup
        $this->service->delete($path);
        DB::table('hr.ctrcaseattachments')->where('cat_id', $slot->cat_id)->delete();
    }
}
