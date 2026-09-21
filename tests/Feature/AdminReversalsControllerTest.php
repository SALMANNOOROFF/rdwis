<?php

namespace Tests\Feature;

use App\Models\AudAttachment;
use App\Models\AudRev;
use App\Models\AudRevComp;
use App\Models\AudRevData;
use App\Models\CenAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminReversalsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected CenAccount $itApprover;
    protected CenAccount $finApprover;
    protected CenAccount $divApprover;
    protected CenAccount $viewerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\ForcePasswordChange::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        // 1. IT Approver (Unit 860000)
        $this->itApprover = CenAccount::where('acc_username', 'mumer')->first()
            ?? CenAccount::where('acc_unt_id', 860000)->where('acc_auth', 'approver')->first();

        // 2. Finance Approver (Unit 800000)
        $this->finApprover = CenAccount::where('acc_username', 'frahman')->first()
            ?? CenAccount::where('acc_untarea', 'fin')->where('acc_auth', 'approver')->first();

        // 3. Division Approver (Unit 350000)
        $this->divApprover = CenAccount::where('acc_username', 'tmiraj1')->first()
            ?? CenAccount::where('acc_unt_id', 350000)->where('acc_auth', 'approver')->first();

        // 4. Readonly Viewer (Unit 990000)
        $this->viewerUser = CenAccount::where('acc_username', 'mtanveer')->first()
            ?? CenAccount::where('acc_auth', 'viewer')->first();
    }

    /**
     * Test 1: Draft tab returns only Draft reversals for initiating user.
     */
    public function test_draft_tab_returns_only_draft_reversals_for_initiating_user(): void
    {
        $draftRev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90001',
            'rev_status'    => 'Draft',
            'rev_unt_id'    => $this->finApprover->acc_unt_id,
            'rev_intunt_id' => $this->finApprover->acc_unt_id,
            'rev_date'      => now()->toDateString(),
            'rev_reason'    => 'Test Draft Reversal',
        ]);

        $response = $this->actingAs($this->finApprover)->get(route('admin.reversals.draft'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reversals.index');
        $response->assertViewHas('tab', 'draft');

        $reversals = $response->viewData('reversals');
        $this->assertNotEmpty($reversals);
        foreach ($reversals as $item) {
            $this->assertSame('Draft', $item->rev_status);
        }
    }

    /**
     * Test 2: IT-only user is forbidden (403) from accessing the Draft tab.
     */
    public function test_it_only_user_is_forbidden_from_draft_tab(): void
    {
        $response = $this->actingAs($this->itApprover)->get(route('admin.reversals.draft'));

        $response->assertStatus(403);
    }

    /**
     * Test 3: IT-only user does not see the Draft tab link rendered in HTML.
     */
    public function test_it_only_user_does_not_see_draft_tab_link(): void
    {
        $response = $this->actingAs($this->itApprover)->get(route('admin.reversals.open'));

        $response->assertStatus(200);
        $response->assertDontSee(route('admin.reversals.draft'));
        $response->assertSee(route('admin.reversals.open'));
        $response->assertSee(route('admin.reversals.closed'));

        // Conversely, initiating user (finance approver) sees all 3 tabs
        $finResponse = $this->actingAs($this->finApprover)->get(route('admin.reversals.open'));
        $finResponse->assertStatus(200);
        $finResponse->assertSee(route('admin.reversals.draft'));
    }

    /**
     * Test 4: Open tab returns only 'In Process' and 'Under Revision' records.
     */
    public function test_open_tab_returns_in_process_and_under_revision_records(): void
    {
        $inProcessRev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90002',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Test In Process Reversal',
        ]);

        $underRev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90003',
            'rev_status'    => 'Under Revision',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Test Under Revision Reversal',
        ]);

        $response = $this->actingAs($this->itApprover)->get(route('admin.reversals.open'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reversals.index');
        $response->assertViewHas('tab', 'open');

        $reversals = $response->viewData('reversals');
        $this->assertNotEmpty($reversals);
        foreach ($reversals as $item) {
            $this->assertContains($item->rev_status, ['In Process', 'Under Revision']);
        }
    }

    /**
     * Test 5: Closed tab returns only 'Fulfilled' and 'Cancelled' records.
     */
    public function test_closed_tab_returns_fulfilled_and_cancelled_records(): void
    {
        $fulfilledRev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90004',
            'rev_status'    => 'Fulfilled',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_closedtg'  => now(),
            'rev_reason'    => 'Test Fulfilled Reversal',
        ]);

        $cancelledRev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90005',
            'rev_status'    => 'Cancelled',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_closedtg'  => now(),
            'rev_reason'    => 'Test Cancelled Reversal',
        ]);

        $response = $this->actingAs($this->itApprover)->get(route('admin.reversals.closed'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reversals.index');
        $response->assertViewHas('tab', 'closed');

        $reversals = $response->viewData('reversals');
        $this->assertNotEmpty($reversals);
        foreach ($reversals as $item) {
            $this->assertContains($item->rev_status, ['Fulfilled', 'Cancelled']);
        }
    }

    /**
     * Test 6: Show view displays header and eager loads comps/data.
     */
    public function test_show_displays_reversal_details_and_eager_loads_relations(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90006',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Test Detail View',
        ]);

        $comp = AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'pur.purcases',
            'rvc_rowid'  => '90006',
            'rvc_action' => 'pcs_rev',
            'rvc_type'   => 1,
            'rvc_detail' => 'pcs_id: 90006, pcs_status: Closed',
        ]);

        $response = $this->actingAs($this->itApprover)->get(route('admin.reversals.show', $rev->rev_id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reversals.show');
        $response->assertViewHas('rev');
        $response->assertSee('pcs_rev');
        $response->assertSee('90006');
        $response->assertSee('#' . $rev->rev_id);
    }

    /**
     * Test 6b: Show view displays Type-2 field-level data revisions (aud.revdata) and attachments.
     */
    public function test_show_displays_type_2_field_level_reversal_data_rows(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 2,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90099',
            'rev_status'    => 'Fulfilled',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Test Field-Level Revision View',
        ]);

        $revData = AudRevData::create([
            'rvd_rev_id'     => $rev->rev_id,
            'rvd_table'      => 'pur.purcases',
            'rvd_rowid'      => '90099',
            'rvd_attrib'     => 'pcs_title',
            'rvd_colname'    => 'pcs_title',
            'rvd_oldvalue'   => 'Original Case Title 123',
            'rvd_newvalue'   => 'Amended Case Title 456',
            'rvd_datatype'   => 'Text',
            'rvd_type'       => 1,
            'rvd_conversion' => 'i',
        ]);

        AudAttachment::create([
            'aat_objtype' => 'rev',
            'aat_objid'   => $rev->rev_id,
            'aat_type'    => 'Approval Document',
            'aat_path'    => 'uploads/reversals/rev_90099_memo.pdf',
        ]);

        $response = $this->actingAs($this->itApprover)->get(route('admin.reversals.show', $rev->rev_id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reversals.show');
        $response->assertViewHas('rev');
        $response->assertSee('Field-Level Data Revisions');
        $response->assertSee('pcs_title');
        $response->assertSee('Original Case Title 123');
        $response->assertSee('Amended Case Title 456');
        $response->assertSee('rev_90099_memo.pdf');
        $response->assertSee('Approval Document');
    }

    /**
     * Test 7: Show view returns 403 when user lacks unit and domain access.
     */
    public function test_show_forbidden_when_user_lacks_unit_scope(): void
    {
        // Division user from 350000 trying to view a case in unit 250000 with a non-PRJ object
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Secret Object',
            'rev_objid'     => '90007',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 250000,
            'rev_intunt_id' => 250000,
            'rev_date'      => now()->toDateString(),
            'rev_reason'    => 'Cross-unit case',
        ]);

        $response = $this->actingAs($this->divApprover)->get(route('admin.reversals.show', $rev->rev_id));

        $response->assertStatus(403);
    }

    /**
     * Test 8: Release action succeeds for initiating approver (Draft -> In Process, sets releasedtg).
     */
    public function test_release_action_succeeds_for_initiating_approver(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90008',
            'rev_status'    => 'Draft',
            'rev_unt_id'    => $this->divApprover->acc_unt_id,
            'rev_intunt_id' => $this->divApprover->acc_unt_id,
            'rev_date'      => now()->toDateString(),
            'rev_reason'    => 'Legitimate error in purchase case approval',
        ]);

        $this->assertNull($rev->rev_releasedtg);

        $response = $this->actingAs($this->divApprover)
            ->post(route('admin.reversals.release', $rev->rev_id));

        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
        $response->assertSessionHas('status');

        $refreshed = $rev->fresh();
        $this->assertSame('In Process', $refreshed->rev_status);
        $this->assertNotNull($refreshed->rev_releasedtg);
    }

    /**
     * Test 9: Release action returns 403 for unauthorized user.
     */
    public function test_release_action_forbidden_for_unauthorized_user(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90009',
            'rev_status'    => 'Draft',
            'rev_unt_id'    => 250000,
            'rev_intunt_id' => 250000,
            'rev_date'      => now()->toDateString(),
            'rev_reason'    => 'Cross-unit release test',
        ]);

        // Division approver from 350000 cannot release unit 250000 draft
        $response = $this->actingAs($this->divApprover)
            ->post(route('admin.reversals.release', $rev->rev_id));

        $response->assertStatus(403);
        $this->assertSame('Draft', $rev->fresh()->rev_status);
    }

    /**
     * Test 10: Execute action succeeds for IT approver and mutates DB records.
     */
    public function test_execute_action_succeeds_for_it_approver_and_mutates_database(): void
    {
        // 1. Create a dummy purchase case in Closed state
        $pcsId = DB::table('pur.purcases')->insertGetId([
            'pcs_status'     => 'Closed',
            'pcs_approvedtg' => now(),
            'pcs_closedtg'   => now(),
            'pcs_hed_id'     => 1,
            'pcs_effhed_id'  => 1,
            'pcs_unt_id'     => 860000,
            'pcs_effunt_id'  => 860000,
            'pcs_intunt_id'  => 860000,
            'pcs_title'      => 'Temporary Case for Execute Controller Test',
            'pcs_type'       => 'Ps',
            'pcs_transtype'  => 1,
            'pcs_date'       => now()->toDateString(),
        ], 'pcs_id');

        // 2. Create Reversal and Component Cascade
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => (string) $pcsId,
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Execute reversal via controller',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'pur.purcases',
            'rvc_rowid'  => (string) $pcsId,
            'rvc_action' => 'pcs_rev',
            'rvc_type'   => 1,
            'rvc_detail' => 'pcs_id: ' . $pcsId,
        ]);

        // 3. Post to execute
        $response = $this->actingAs($this->itApprover)
            ->post(route('admin.reversals.execute', $rev->rev_id));

        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
        $response->assertSessionHas('status');

        // 4. Assert AudRev status updated to Fulfilled
        $refreshed = $rev->fresh();
        $this->assertSame('Fulfilled', $refreshed->rev_status);
        $this->assertNotNull($refreshed->rev_closedtg);

        // 5. Assert live target table mutated according to pcs_rev action
        $pcsRecord = DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
        $this->assertSame('Under Revision', $pcsRecord->pcs_status);
        $this->assertNull($pcsRecord->pcs_approvedtg);
        $this->assertNull($pcsRecord->pcs_closedtg);
    }

    /**
     * Test 11: Execute action returns 403 for non-IT users.
     */
    public function test_execute_action_forbidden_for_non_it_user(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90010',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => $this->finApprover->acc_unt_id,
            'rev_intunt_id' => $this->finApprover->acc_unt_id,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Non-IT execute test',
        ]);

        // Finance Approver cannot execute
        $response = $this->actingAs($this->finApprover)
            ->post(route('admin.reversals.execute', $rev->rev_id));

        $response->assertStatus(403);
        $this->assertSame('In Process', $rev->fresh()->rev_status);
    }

    /**
     * Test 12: Execute action is idempotent-guarded against already fulfilled cases.
     */
    public function test_execute_action_idempotent_guard_on_already_fulfilled(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90011',
            'rev_status'    => 'Fulfilled',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_closedtg'  => now(),
            'rev_reason'    => 'Already fulfilled case',
        ]);

        // Policy denies execute on Fulfilled status -> 403
        $response = $this->actingAs($this->itApprover)
            ->post(route('admin.reversals.execute', $rev->rev_id));

        $response->assertStatus(403);
    }

    /**
     * Test 13: Return action succeeds for IT staff and preserves rev_releasedtg.
     */
    public function test_return_action_succeeds_for_it_staff(): void
    {
        $releasedTime = now()->subHour();

        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90012',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> $releasedTime,
            'rev_reason'    => 'Original Reason',
        ]);

        $response = $this->actingAs($this->itApprover)
            ->post(route('admin.reversals.return', $rev->rev_id), [
                'remarks' => 'Please provide missing invoice scan',
            ]);

        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
        $response->assertSessionHas('status');

        $refreshed = $rev->fresh();
        $this->assertSame('Under Revision', $refreshed->rev_status);
        $this->assertStringContainsString('Returned by IT', $refreshed->rev_reason);
        $this->assertStringContainsString('Please provide missing invoice scan', $refreshed->rev_reason);

        // Legacy parity: rev_releasedtg is NOT cleared upon return
        $this->assertNotNull($refreshed->rev_releasedtg);
    }

    /**
     * Test 14: Return action returns 403 for non-IT users.
     */
    public function test_return_action_forbidden_for_non_it_user(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90013',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => $this->finApprover->acc_unt_id,
            'rev_intunt_id' => $this->finApprover->acc_unt_id,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Non-IT return test',
        ]);

        $response = $this->actingAs($this->finApprover)
            ->post(route('admin.reversals.return', $rev->rev_id), [
                'remarks' => 'Unauthorized return',
            ]);

        $response->assertStatus(403);
        $this->assertSame('In Process', $rev->fresh()->rev_status);
    }

    /**
     * Test 15: Cancel action on Draft permanently deletes the record and its cascade comps.
     */
    public function test_cancel_draft_action_hard_deletes_record(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90014',
            'rev_status'    => 'Draft',
            'rev_unt_id'    => $this->divApprover->acc_unt_id,
            'rev_intunt_id' => $this->divApprover->acc_unt_id,
            'rev_date'      => now()->toDateString(),
            'rev_reason'    => 'Draft to be cancelled',
        ]);

        $comp = AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'pur.purcases',
            'rvc_rowid'  => '90014',
            'rvc_action' => 'pcs_rev',
            'rvc_type'   => 1,
            'rvc_detail' => 'pcs_id: 90014',
        ]);

        $revId = $rev->rev_id;
        $compId = $comp->rvc_id;

        $response = $this->actingAs($this->divApprover)
            ->post(route('admin.reversals.cancel', $revId));

        $response->assertRedirect(route('admin.reversals.draft'));
        $response->assertSessionHas('status');

        // Assert hard-deleted from both tables
        $this->assertDatabaseMissing('aud.revs', ['rev_id' => $revId]);
        $this->assertDatabaseMissing('aud.revcomps', ['rvc_id' => $compId]);
    }

    /**
     * Test 16: Cancel action on non-Draft (e.g. Under Revision) soft-cancels the record.
     */
    public function test_cancel_non_draft_action_soft_cancels_and_preserves_record(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90015',
            'rev_status'    => 'Under Revision',
            'rev_unt_id'    => $this->divApprover->acc_unt_id,
            'rev_intunt_id' => $this->divApprover->acc_unt_id,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Case under revision to cancel',
        ]);

        $response = $this->actingAs($this->divApprover)
            ->post(route('admin.reversals.cancel', $rev->rev_id), [
                'reason' => 'Duplicate case entered by error',
            ]);

        $response->assertRedirect(route('admin.reversals.show', $rev->rev_id));
        $response->assertSessionHas('status');

        $refreshed = $rev->fresh();
        $this->assertSame('Cancelled', $refreshed->rev_status);
        $this->assertNotNull($refreshed->rev_closedtg);
        $this->assertStringContainsString('Cancellation Reason on', $refreshed->rev_reason);
        $this->assertStringContainsString('Duplicate case entered by error', $refreshed->rev_reason);

        // Record still exists in database
        $this->assertDatabaseHas('aud.revs', ['rev_id' => $rev->rev_id, 'rev_status' => 'Cancelled']);
    }

    /**
     * Test 17: Cancel action returns 403 for unauthorized viewer user.
     */
    public function test_cancel_action_forbidden_for_unauthorized_user(): void
    {
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90016',
            'rev_status'    => 'Under Revision',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_reason'    => 'Cancel auth guard test',
        ]);

        $response = $this->actingAs($this->viewerUser)
            ->post(route('admin.reversals.cancel', $rev->rev_id));

        $response->assertStatus(403);
        $this->assertSame('Under Revision', $rev->fresh()->rev_status);
    }

    /**
     * Test 18: Reversal execution creates attachment slot, renders live upload form, and enables document viewing/downloading.
     */
    public function test_execute_creates_attachment_slot_and_allows_upload_and_download_on_fulfilled_reversal(): void
    {
        Storage::fake('public');

        // Create an in-process reversal ready for execution
        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90018',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Test Attachment Execution Flow',
        ]);

        // Execute reversal as IT Approver
        $execResp = $this->actingAs($this->itApprover)
            ->post(route('admin.reversals.execute', $rev->rev_id));

        $execResp->assertRedirect(route('admin.reversals.show', $rev->rev_id));
        $this->assertSame('Fulfilled', $rev->fresh()->rev_status);

        // 1. Confirm initial attachment slot created in DB with null path
        $slot = AudAttachment::where('aat_objtype', 'rev')
            ->where('aat_objid', $rev->rev_id)
            ->where('aat_type', 'Data Revision Case')
            ->first();

        $this->assertNotNull($slot);
        $this->assertNull($slot->aat_path);

        // 2. Load show page: attachment panel rendered with pending slot and upload form
        $showResp = $this->actingAs($this->itApprover)
            ->get(route('admin.reversals.show', $rev->rev_id));

        $showResp->assertStatus(200);
        $showResp->assertSee('Reversal Attachments');
        $showResp->assertSee('Pending Upload (Slot #' . $slot->aat_id . ')');
        $showResp->assertSee(route('universal.attachment.upload'));

        // 3. Upload file via universal attachment route
        $fakeFile = UploadedFile::fake()->create('reversal_justification.pdf', 250, 'application/pdf');

        $uploadResp = $this->actingAs($this->itApprover)
            ->post(route('universal.attachment.upload'), [
                'module'    => 'aud',
                'object_id' => $rev->rev_id,
                'doc_type'  => 'Data Revision Case',
                'file'      => $fakeFile,
            ]);

        $uploadResp->assertRedirect();

        // 4. Confirm slot updated with physical path and stored on disk
        $slot->refresh();
        $this->assertNotNull($slot->aat_path);
        Storage::disk('public')->assertExists($slot->aat_path);

        // 5. Fresh page load confirms live view and download links
        $showRespFresh = $this->actingAs($this->itApprover)
            ->get(route('admin.reversals.show', $rev->rev_id));

        $showRespFresh->assertStatus(200);
        $showRespFresh->assertSee(basename($slot->aat_path));
        $showRespFresh->assertSee(route('universal.attachment.view', ['module' => 'aud', 'id' => $slot->aat_id]));
        $showRespFresh->assertSee(route('universal.attachment.view', ['module' => 'aud', 'id' => $slot->aat_id, 'download' => 1]));

        // 6. View route streams file inline
        $viewResp = $this->actingAs($this->itApprover)
            ->get(route('universal.attachment.view', ['module' => 'aud', 'id' => $slot->aat_id]));

        $viewResp->assertStatus(200);

        // 7. Download route serves attachment disposition
        $downloadResp = $this->actingAs($this->itApprover)
            ->get(route('universal.attachment.view', ['module' => 'aud', 'id' => $slot->aat_id, 'download' => 1]));

        $downloadResp->assertStatus(200);
        $downloadResp->assertHeader('content-disposition');
        $this->assertStringContainsString('attachment', (string) $downloadResp->headers->get('content-disposition'));
    }

    /**
     * Test 19: Attachment panel is absent and direct upload/view blocked for non-fulfilled reversal.
     */
    public function test_attachment_panel_absent_and_upload_blocked_for_non_fulfilled_reversal(): void
    {
        Storage::fake('public');

        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90019',
            'rev_status'    => 'In Process',
            'rev_unt_id'    => 860000,
            'rev_intunt_id' => 860000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_reason'    => 'Non-Fulfilled Attachment Test',
        ]);

        // Panel must not be rendered on In Process reversal
        $showResp = $this->actingAs($this->itApprover)
            ->get(route('admin.reversals.show', $rev->rev_id));

        $showResp->assertStatus(200);
        $showResp->assertDontSee('Reversal Attachments');
        $showResp->assertDontSee('reversalAttachmentUploadForm');

        // Direct upload attempt must be rejected with 403
        $fakeFile = UploadedFile::fake()->create('blocked.pdf', 50, 'application/pdf');

        $uploadResp = $this->actingAs($this->itApprover)
            ->post(route('universal.attachment.upload'), [
                'module'    => 'aud',
                'object_id' => $rev->rev_id,
                'doc_type'  => 'Data Revision Case',
                'file'      => $fakeFile,
            ]);

        $uploadResp->assertStatus(403);
    }

    /**
     * Test 20: Attachment panel is absent and direct upload blocked for role outside allowed set.
     */
    public function test_attachment_panel_absent_and_upload_blocked_for_unauthorized_role(): void
    {
        Storage::fake('public');

        // Division staff with viewer role (can view cases in their unit, but NOT approver-s/editor-s)
        $divisionViewer = new CenAccount();
        $divisionViewer->acc_id = 99991;
        $divisionViewer->acc_username = 'div_viewer_test';
        $divisionViewer->acc_name = 'Division Viewer';
        $divisionViewer->acc_auth = 'viewer';
        $divisionViewer->acc_access = 'single';
        $divisionViewer->acc_unt_id = 350000;
        $divisionViewer->acc_untarea = 'prj';
        $divisionViewer->acc_desigtype = 'staff';
        $divisionViewer->acc_status = 'active';

        $rev = AudRev::create([
            'rev_type'      => 1,
            'rev_obj'       => 'Purchase Case',
            'rev_objid'     => '90020',
            'rev_status'    => 'Fulfilled',
            'rev_unt_id'    => 350000,
            'rev_intunt_id' => 350000,
            'rev_date'      => now()->toDateString(),
            'rev_releasedtg'=> now(),
            'rev_closedtg'  => now(),
            'rev_reason'    => 'Unauthorized Role Attachment Test',
        ]);

        AudAttachment::create([
            'aat_objtype' => 'rev',
            'aat_objid'   => $rev->rev_id,
            'aat_type'    => 'Data Revision Case',
            'aat_path'    => null,
        ]);

        // Viewer can see the reversal (unit scope), but attachment panel is absent
        $showResp = $this->actingAs($divisionViewer)
            ->get(route('admin.reversals.show', $rev->rev_id));

        $showResp->assertStatus(200);
        $showResp->assertDontSee('Reversal Attachments');
        $showResp->assertDontSee('reversalAttachmentUploadForm');

        // Direct upload attempt by viewer must return 403
        $fakeFile = UploadedFile::fake()->create('unauthorized.pdf', 50, 'application/pdf');

        $uploadResp = $this->actingAs($divisionViewer)
            ->post(route('universal.attachment.upload'), [
                'module'    => 'aud',
                'object_id' => $rev->rev_id,
                'doc_type'  => 'Data Revision Case',
                'file'      => $fakeFile,
            ]);

        $uploadResp->assertStatus(403);
    }
}
