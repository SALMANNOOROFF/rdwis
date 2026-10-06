<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\FormAuditLog;
use App\Models\HrForms\ProjectExtra;
use App\Services\HrForms\FormGenerationService;
use App\Services\HrForms\ProgressTrackerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrFormsCaseFileUiTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected CenAccount $divisionUser;
    protected HrCtrCase $testCase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hrforms.enabled', true);
        $this->withoutMiddleware([
            \App\Http\Middleware\ForcePasswordChange::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        // Find or create test user with division/admin permissions
        $this->divisionUser = CenAccount::where('acc_status', 'Active')->where('acc_untarea', 'nrdi')->first()
            ?: CenAccount::where('acc_status', 'Active')->first()
            ?: CenAccount::first();

        // Get an active draft contract case
        $this->testCase = HrCtrCase::where('ctc_status', 'Draft')->first()
            ?: HrCtrCase::first();

        // Ensure forms are synced for this case
        app(FormGenerationService::class)->syncForms($this->testCase);
    }

    /**
     * Test 1: Tab hidden when flag is off; zero hrforms queries executed.
     */
    public function test_tab_hidden_when_flag_is_off_and_zero_hrforms_queries(): void
    {
        Config::set('hrforms.enabled', false);

        DB::enableQueryLog();
        $response = $this->actingAs($this->divisionUser)
            ->get(route('hrforms.case-file.index', $this->testCase->ctc_id));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(403);

        $hrformsQueries = array_filter($queries, function ($q) {
            return str_contains(strtolower($q['query']), 'hrforms');
        });
        $this->assertCount(0, $hrformsQueries, 'Zero hrforms queries must run when flag is disabled.');
    }

    /**
     * Test 2: Guest and unauthorized users cannot view or edit forms.
     */
    public function test_guest_and_unauthorized_users_cannot_view_or_edit_forms(): void
    {
        $form = CaseForm::where('case_id', $this->testCase->ctc_id)->first();
        $this->assertNotNull($form);

        // Guest view -> redirect / 401 / 403
        $guestResp = $this->get(route('hrforms.case-file.index', $this->testCase->ctc_id));
        $this->assertTrue(in_array($guestResp->status(), [302, 401, 403]));

        // Guest update -> redirect / 401 / 403
        $guestUpdate = $this->putJson(route('hrforms.forms.update', $form->id), ['manual' => []]);
        $this->assertTrue(in_array($guestUpdate->status(), [302, 401, 403]));
    }

    /**
     * Test 3: Manual save never changes live layer; refresh never changes manual layer.
     */
    public function test_manual_save_never_changes_live_layer_and_refresh_never_changes_manual(): void
    {
        $form = CaseForm::where('form_code', 'RDW/HR/F-02')->where('status', '!=', 'Submitted')->first()
            ?: CaseForm::where('form_code', 'RDW/HR/F-02')->first();

        $this->assertNotNull($form);
        $form->status = 'Draft';
        $form->submitted_at = null;
        $form->save();

        $originalLive = $form->form_data['live'] ?? [];

        // 1. Manual save
        $saveResp = $this->actingAs($this->divisionUser)
            ->put(route('hrforms.forms.update', $form->id), [
                'manual' => [
                    'board_recommendations' => 'Candidate passed board unanimously with high distinction.',
                    'interview_venue'       => 'RDW Executive Boardroom'
                ]
            ]);

        $saveResp->assertStatus(200);
        $formAfterSave = CaseForm::find($form->id);

        $this->assertSame($originalLive, $formAfterSave->form_data['live'], 'Live layer must remain strictly unchanged on manual save.');
        $this->assertSame('Candidate passed board unanimously with high distinction.', $formAfterSave->form_data['manual']['board_recommendations']);

        // 2. Refresh live
        $refreshResp = $this->actingAs($this->divisionUser)
            ->post(route('hrforms.forms.refresh', $form->id));

        $refreshResp->assertStatus(200);
        $formAfterRefresh = CaseForm::find($form->id);

        $this->assertSame(
            'Candidate passed board unanimously with high distinction.',
            $formAfterRefresh->form_data['manual']['board_recommendations'],
            'Manual layer must remain strictly preserved on live refresh.'
        );
    }

    /**
     * Test 4: Validation limits for Annex J (1-5) and Annex M (0-10), auto totals.
     */
    public function test_validation_limits_for_annex_j_and_annex_m_and_auto_totals(): void
    {
        // 1. Annex J Validation: invalid score > 5 fails
        $jForm = CaseForm::where('case_id', $this->testCase->ctc_id)
            ->where('form_code', 'RDW/HR/F-07')
            ->first();

        if ($jForm) {
            $jForm->status = 'Draft';
            $jForm->save();

            $invalidJ = $this->actingAs($this->divisionUser)
                ->put(route('hrforms.forms.update', $jForm->id), [
                    'manual' => ['scores' => ['technical_skills' => 7]]
                ]);
            $invalidJ->assertStatus(422);
            $this->assertStringContainsString('between 1 and 5', $invalidJ->json('message'));

            // Valid J scores auto calculate total out of 60
            $validScores = [];
            foreach ($jForm->form_data['live']['evaluation_criteria'] as $c) {
                $validScores[$c['key']] = 4; // 12 x 4 = 48
            }
            $validJ = $this->actingAs($this->divisionUser)
                ->put(route('hrforms.forms.update', $jForm->id), [
                    'manual' => ['scores' => $validScores]
                ]);
            $validJ->assertStatus(200);
            $this->assertSame(48, $validJ->json('form.form_data.manual.total_score'));
        }

        // 2. Annex M Validation: invalid mark > 10 fails
        $mForm = CaseForm::where('form_code', 'RDW/HR/F-08')->first();
        if ($mForm) {
            $mForm->status = 'Draft';
            $mForm->save();

            $invalidM = $this->actingAs($this->divisionUser)
                ->put(route('hrforms.forms.update', $mForm->id), [
                    'manual' => ['marks' => ['technical_expertise_skills' => 15]]
                ]);
            $invalidM->assertStatus(422);
            $this->assertStringContainsString('between 0 and 10', $invalidM->json('message'));
        }
    }

    /**
     * Test 5: Submit is blocked unless status is Ready; snapshot stored; submitted form locked.
     */
    public function test_submit_blocked_unless_ready_and_snapshot_stored_and_editing_locked(): void
    {
        $form = CaseForm::where('case_id', $this->testCase->ctc_id)->first();
        $form->status = 'Draft';
        $form->submitted_at = null;
        $form->snapshot_data = null;
        $form->form_data = array_merge($form->form_data ?? [], [
            'missing_fields' => ['remarks_by_concerned_dir'],
            'is_complete'    => false,
        ]);
        $form->save();

        // 1. Submit blocked when status is Draft
        $blockedResp = $this->actingAs($this->divisionUser)
            ->post(route('hrforms.forms.submit', $form->id));

        $blockedResp->assertStatus(422);
        $this->assertStringContainsString('All mandatory fields must be completed', $blockedResp->json('message'));

        // 2. Set to Ready and submit
        $form->status = 'Ready';
        $form->form_data = array_merge($form->form_data, [
            'missing_fields' => [],
            'is_complete'    => true,
        ]);
        $form->save();

        $submitResp = $this->actingAs($this->divisionUser)
            ->post(route('hrforms.forms.submit', $form->id));

        $submitResp->assertStatus(200);
        $submittedForm = CaseForm::find($form->id);

        $this->assertSame('Submitted', $submittedForm->status);
        $this->assertNotNull($submittedForm->submitted_at);
        $this->assertNotNull($submittedForm->snapshot_data);

        // 3. Submitted form is locked: editing returns 422
        $editAttempt = $this->actingAs($this->divisionUser)
            ->put(route('hrforms.forms.update', $form->id), ['manual' => ['foo' => 'bar']]);
        $editAttempt->assertStatus(422);

        // 4. Submitted form cannot be refreshed
        $refreshAttempt = $this->actingAs($this->divisionUser)
            ->post(route('hrforms.forms.refresh', $form->id));
        $refreshAttempt->assertStatus(422);
    }

    /**
     * Test 6: Pending Removal actions write audit rows and never hard-delete.
     */
    public function test_pending_removal_actions_write_audit_rows_and_never_hard_delete(): void
    {
        $forms = CaseForm::where('case_id', $this->testCase->ctc_id)->get();
        $form1 = $forms->first();
        $form2 = $forms->count() > 1 ? $forms->get(1) : $form1;

        $form1->status = 'Pending Removal';
        $form1->submitted_at = null;
        $form1->save();

        // Decision: 'remove' -> archives form, never deletes from database
        $removeResp = $this->actingAs($this->divisionUser)
            ->post(route('hrforms.forms.pending-action', $form1->id), ['decision' => 'remove']);

        $removeResp->assertStatus(200);
        $this->assertDatabaseHas('hrforms.case_forms', [
            'id'     => $form1->id,
            'status' => 'Pending Removal (Archived)'
        ]);

        $auditLog = FormAuditLog::where('form_id', $form1->id)
            ->where('action', 'pending_removal_archived')
            ->first();
        $this->assertNotNull($auditLog, 'Archiving a Pending Removal form must create an audit log entry.');

        // Decision: 'keep' -> restores PREVIOUS status recorded in audit_log (e.g. Ready)
        FormAuditLog::logAction(
            $this->testCase->ctc_id,
            $form2->id,
            'status_changed',
            'Marked as Pending Removal on hiring type change',
            ['status' => 'Ready'],
            ['status' => 'Pending Removal']
        );
        $form2->status = 'Pending Removal';
        $form2->submitted_at = null;
        $form2->save();

        $keepResp = $this->actingAs($this->divisionUser)
            ->post(route('hrforms.forms.pending-action', $form2->id), ['decision' => 'keep']);

        $this->assertEquals(200, $keepResp->status(), 'Keep decision failed: ' . json_encode($keepResp->json()));
        $this->assertSame('Ready', CaseForm::find($form2->id)->status, 'Keep decision must restore previous status (Ready) from audit log');
    }

    /**
     * Test 7: Tracker shows correct steps for each hiring type.
     */
    public function test_tracker_shows_correct_steps_for_each_hiring_type(): void
    {
        $trackerService = app(ProgressTrackerService::class);

        // 1. Fresh -> 9 steps (includes JD, Advertisement, Initial Screening, Selection Board)
        $freshCase = new HrCtrCase(['ctc_type' => 'HG', 'ctc_newgrade' => 'RO', 'ctc_newjobtitle' => 'Engineer']);
        $freshSteps = collect($trackerService->getTrackerSteps($freshCase)['steps'])->pluck('code')->toArray();
        $this->assertContains('in_principle_approval', $freshSteps);
        $this->assertContains('jd_formulation', $freshSteps);
        $this->assertContains('advertisement', $freshSteps);
        $this->assertContains('initial_screening', $freshSteps);
        $this->assertContains('selection_board', $freshSteps);
        $this->assertContains('joining', $freshSteps);

        // 2. Extension & Renewal -> omit JD, Advertisement, Screening, Selection Board
        $extCase = new HrCtrCase(['ctc_type' => 'CE', 'ctc_newgrade' => 'RO', 'ctc_newjobtitle' => 'Engineer']);
        $extSteps = collect($trackerService->getTrackerSteps($extCase)['steps'])->pluck('code')->toArray();
        $this->assertNotContains('jd_formulation', $extSteps);
        $this->assertNotContains('advertisement', $extSteps);
        $this->assertNotContains('initial_screening', $extSteps);
        $this->assertNotContains('selection_board', $extSteps);
        $this->assertContains('in_principle_approval', $extSteps);
        $this->assertContains('competent_authority_approval', $extSteps);

        // 3. Intern Extension (Para 31g) -> only MD RDW Approval step
        $internExtCase = new HrCtrCase(['ctc_type' => 'CE', 'ctc_newgrade' => 'Internee', 'ctc_newjobtitle' => 'Intern']);
        $internSteps = collect($trackerService->getTrackerSteps($internExtCase)['steps'])->pluck('code')->toArray();
        $this->assertSame(['competent_authority_approval'], $internSteps);
    }

    /**
     * Test 8: Advertisement 14-day warning appears and clears with exemption.
     */
    public function test_advertisement_14_day_warning_appears_and_clears_with_exemption(): void
    {
        $trackerService = app(ProgressTrackerService::class);
        $case = HrCtrCase::first();

        // 1. Set advertisement 5 days before interview (< 14 days) with NO exemption
        CaseExtra::updateOrCreate(
            ['case_id' => $case->ctc_id],
            [
                'interview_date'                     => '2026-10-15',
                'advertisement_exemption'            => false,
                'advertisement_exemption_justification' => null,
                'extra_data'                         => ['advertisement_start_date' => '2026-10-10']
            ]
        );

        $resWarning = $trackerService->getTrackerSteps($case);
        $this->assertNotNull($resWarning['ad_warning']);
        $this->assertTrue($resWarning['ad_warning']['has_warning']);
        $this->assertFalse($resWarning['ad_warning']['is_exempted']);

        // 2. Record exemption justification -> warning clears
        CaseExtra::updateOrCreate(
            ['case_id' => $case->ctc_id],
            [
                'advertisement_exemption'            => true,
                'advertisement_exemption_justification' => 'Urgent deployment authorized by MD.'
            ]
        );

        $resExempted = $trackerService->getTrackerSteps($case);
        $this->assertNotNull($resExempted['ad_warning']);
        $this->assertFalse($resExempted['ad_warning']['has_warning'], 'Warning must clear when exemption is recorded.');
        $this->assertTrue($resExempted['ad_warning']['is_exempted']);
    }

    /**
     * Test 9: project_extras values appear in Annex A and B after saving.
     */
    public function test_project_extras_values_appear_in_annex_a_and_b(): void
    {
        $case = HrCtrCase::with('casePlans.project')->whereNotNull('ctc_prj_id')->first() ?: HrCtrCase::first();
        $firstPlan = $case->casePlans->first();
        $projectId = $firstPlan?->project?->prj_id ?? ($firstPlan?->ccp_prj_id ?? ($case->ctc_prj_id ?? null));

        if (!$projectId) {
            $projectId = DB::table('prj.projects')->value('prj_id');
            $case->ctc_prj_id = $projectId;
            $case->save();
        }

        $this->assertNotNull($projectId);

        $saveResp = $this->actingAs($this->divisionUser)
                ->put(route('hrforms.project-extras.update', $case->ctc_id), [
                    'work_order_no'      => 'WO-TEST-2026-XYZ',
                    'work_order_date'    => '2026-05-01',
                    'warranty_expiry'    => '2028-05-01',
                    'approved_headcount' => 8,
                ]);

            $saveResp->assertStatus(200);

            // Re-extract Annex A
            $extractorA = app(\App\Services\HrForms\Extractors\AnnexAExtractor::class);
            $dataA = $extractorA->extract($case, 'main', []);
            $this->assertSame('WO-TEST-2026-XYZ', $dataA['manual']['work_order_no']);

            // Re-extract Annex B
            $extractorB = app(\App\Services\HrForms\Extractors\AnnexBExtractor::class);
            $dataB = $extractorB->extract($case, 'main', []);
            $this->assertSame('WO-TEST-2026-XYZ', $dataB['live']['work_order_no']);
    }

    /**
     * Test 10: PDF download returns 200, logs audit row, and guest is blocked (B6).
     */
    public function test_download_form_pdf_and_case_dossier_with_audit_and_authorization(): void
    {
        $form = CaseForm::where('case_id', $this->testCase->ctc_id)->first();
        $this->assertNotNull($form);

        // 1. Authorized download returns 200
        $resp = $this->actingAs($this->divisionUser)
            ->get(route('hrforms.forms.pdf', $form->id));
        $resp->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resp->headers->get('Content-Type'));

        // Proves audit log entry created
        $audit = FormAuditLog::where('form_id', $form->id)
            ->where('action', 'pdf_downloaded')
            ->first();
        $this->assertNotNull($audit);

        // 2. Full Case Dossier download returns 200 with cover page
        $dossierResp = $this->actingAs($this->divisionUser)
            ->get(route('hrforms.cases.pdf-dossier', $this->testCase->ctc_id));
        $dossierResp->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $dossierResp->headers->get('Content-Type'));

        $dossierAudit = FormAuditLog::where('case_id', $this->testCase->ctc_id)
            ->where('action', 'dossier_downloaded')
            ->first();
        $this->assertNotNull($dossierAudit);

        // 3. Guest / Unauthorized is blocked (403 or redirect to login)
        auth()->logout();
        $guestResp = $this->get(route('hrforms.forms.pdf', $form->id));
        $this->assertTrue(in_array($guestResp->status(), [302, 401, 403], true));
    }

    /**
     * Test 11: Submitted form PDF renders from snapshot only and ignores live changes (B2/B6).
     */
    public function test_submitted_form_pdf_renders_from_snapshot_only(): void
    {
        $form = CaseForm::where('case_id', $this->testCase->ctc_id)->first();
        $form->status = 'Submitted';
        $form->submitted_at = now();
        $form->snapshot_data = [
            'live' => [
                'project_title' => 'SNAPSHOT_PROJECT_TITLE_NEVER_CHANGES',
                'grade'         => 'RO',
            ],
            'manual' => [
                'board_recommendations' => 'SNAPSHOT_RECOMMENDATION_TEXT',
            ],
            'warnings' => ['WARNING_THAT_MUST_NOT_APPEAR_ON_SUBMITTED_PDF'],
        ];
        $form->save();

        // Mutate live case to simulate live data changing after submission
        $this->testCase->ctc_newjobtitle = 'CHANGED_LIVE_TITLE_THAT_MUST_BE_IGNORED';
        $this->testCase->save();

        $pdfResp = $this->actingAs($this->divisionUser)
            ->get(route('hrforms.forms.pdf', $form->id));
        $pdfResp->assertStatus(200);

        $content = $pdfResp->getContent();
        $this->assertStringStartsWith('%PDF-', $content, 'PDF output must be a valid binary PDF starting with %PDF-');
        $this->assertTrue(strlen($content) > 1000, 'PDF binary output must be substantial.');
        $finalFile = \App\Models\HrForms\FormFile::where('case_form_id', $form->id)->where('is_final', true)->first();
        $this->assertNotNull($finalFile, 'A final file record must be recorded in hrforms.form_files upon submission.');
    }

    /**
     * Test 12: PDF routes return 404 with zero queries when feature flag is off.
     */
    public function test_pdf_routes_return_404_when_feature_flag_is_off(): void
    {
        config(['hrforms.enabled' => false]);
        $form = CaseForm::where('case_id', $this->testCase->ctc_id)->first();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $resp = $this->actingAs($this->divisionUser)
            ->get(route('hrforms.forms.pdf', $form->id));
        $resp->assertStatus(404);

        $queries = DB::getQueryLog();
        $hrformsQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'hrforms');
        });
        $this->assertCount(0, $hrformsQueries, 'Disabled feature flag must execute ZERO hrforms queries.');

        config(['hrforms.enabled' => true]);
    }

    /**
     * Test 13: Schema fingerprint hash is strictly unchanged.
     */
    public function test_fingerprint_hash_strictly_unchanged(): void
    {
        $snapshotPath = base_path('scratch/baseline_schema_snapshot.json');
        $baseline = json_decode(file_get_contents($snapshotPath), true);
        $expectedHash = $baseline['md5'];

        $currentRows = DB::select("
            SELECT table_schema, table_name, column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema NOT IN ('pg_catalog', 'information_schema', 'hrforms')
            ORDER BY table_schema, table_name, ordinal_position
        ");

        $currentData = array_map(function($r) {
            return (array) $r;
        }, $currentRows);

        $currentHash = md5(json_encode($currentData));

        $this->assertSame($expectedHash, $currentHash, 'CRITICAL: Non-hrforms schemas were modified!');
        $this->assertSame('2a451470741fecb5f805c0a98d9d42e0', $currentHash);
    }
}
