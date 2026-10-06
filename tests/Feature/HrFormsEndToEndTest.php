<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\FormFile;
use App\Models\HrForms\HiringTypeMap;
use App\Models\HrForms\SalaryBand;
use App\Services\HrForms\FormFileStorageService;
use App\Services\HrForms\FormGenerationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HrFormsEndToEndTest extends TestCase
{
    use DatabaseTransactions;

    protected CenAccount $user;
    protected FormGenerationService $generationService;
    protected FormFileStorageService $fileService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hrforms.enabled', true);
        $this->withoutMiddleware([
            \App\Http\Middleware\ForcePasswordChange::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        $this->user = CenAccount::where('acc_status', 'Active')->where('acc_untarea', 'nrdi')->first()
            ?: CenAccount::where('acc_status', 'Active')->first()
            ?: CenAccount::first();

        $this->generationService = app(FormGenerationService::class);
        $this->fileService = app(FormFileStorageService::class);
    }

    /**
     * Test 1: Fresh Hiring End-to-End Workflow.
     */
    public function test_fresh_hiring_end_to_end_workflow(): void
    {
        // 1. Locate a fresh hiring case
        $submittedCaseIds = CaseForm::where('status', 'Submitted')->pluck('case_id')->unique()->toArray();
        $freshCase = HrCtrCase::whereIn('ctc_type', ['Hg', 'HG'])
            ->where('ctc_status', 'Draft')
            ->whereNotIn('ctc_id', $submittedCaseIds)
            ->first();
        $this->assertNotNull($freshCase);

        // 2. Generate forms
        $syncResult = $this->generationService->syncForms($freshCase);
        $this->assertTrue(($syncResult['synced'] ?? $syncResult['synced_forms'] ?? 0) > 0);

        // Required forms must include Annex A (F-01), Annex B (F-02), Annex J (F-07), Annex T
        $bForm = CaseForm::where('case_id', $freshCase->ctc_id)->where('form_code', 'RDW/HR/F-02')->first();
        $this->assertNotNull($bForm);

        // 3. Verify draft PDF was auto-generated in storage
        $draftFile = FormFile::where('case_form_id', $bForm->id)->latest('version')->first();
        $this->assertNotNull($draftFile, 'Draft PDF file record must exist in hrforms.form_files.');
        $this->assertFalse($draftFile->is_final);
        $this->assertTrue(Storage::disk('local')->exists($draftFile->file_path));

        // 4. Fill manual data in Annex B (selection board)
        $updateResp = $this->actingAs($this->user)->putJson(route('hrforms.forms.update', $bForm->id), [
            'manual' => [
                'approved_hr_count'         => 1,
                'interview_date'            => '2026-10-10',
                'interview_time'            => '11:00',
                'interview_venue'           => 'Conference Room A',
                'single_candidate_mode'     => true,
                'single_candidate_justification' => 'Specialized AI and geospatial qualifications required.',
                'principal_candidate'       => 'Test Principal Candidate',
                'board_recommendations'     => 'Recommended unanimously by the Selection Board.',
            ]
        ]);
        $updateResp->assertStatus(200);
        $this->assertTrue($updateResp->json('success'));

        $bForm->refresh();
        $this->assertSame('Ready', $bForm->status);

        // 5. Submit Annex B
        $submitResp = $this->actingAs($this->user)->postJson(route('hrforms.forms.submit', $bForm->id));
        $submitResp->assertStatus(200);
        $this->assertTrue($submitResp->json('success'));

        $bForm->refresh();
        $this->assertSame('Submitted', $bForm->status);
        $this->assertNotNull($bForm->snapshot_data);

        // 6. Verify final immutable PDF generated with is_final = true
        $finalFile = FormFile::where('case_form_id', $bForm->id)->where('is_final', true)->first();
        $this->assertNotNull($finalFile);
        $this->assertTrue(Storage::disk('local')->exists($finalFile->file_path));

        // 7. Verify joining forms (Annex D & U) transitioned from Scheduled
        $dForm = CaseForm::where('case_id', $freshCase->ctc_id)->where('form_code', 'RDW/HR/F-03')->first();
        if ($dForm) {
            $this->assertNotSame('Scheduled', $dForm->status);
        }

        // 8. Download case dossier PDF
        $dossierResp = $this->actingAs($this->user)->get(route('hrforms.cases.pdf-dossier', $freshCase->ctc_id));
        $dossierResp->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $dossierResp->getContent());
    }

    /**
     * Test 2: Contract Renewal End-to-End Workflow with Informational Bands.
     */
    public function test_contract_renewal_end_to_end_workflow(): void
    {
        // 1. Locate a renewal case
        $submittedCaseIds = CaseForm::where('status', 'Submitted')->pluck('case_id')->unique()->toArray();
        $renCase = HrCtrCase::where('ctc_type', 'Cr')
            ->where('ctc_status', 'Draft')
            ->whereNotIn('ctc_id', $submittedCaseIds)
            ->first();
        $this->assertNotNull($renCase);

        // 2. Generate forms (Annex M & Annex N)
        $this->generationService->syncForms($renCase);

        $mForm = CaseForm::where('case_id', $renCase->ctc_id)->where('form_code', 'RDW/HR/F-08')->first();
        $nForm = CaseForm::where('case_id', $renCase->ctc_id)->where('form_code', 'RDW/HR/F-09')->first();
        $this->assertNotNull($mForm);
        $this->assertNotNull($nForm);

        // 3. Fill Annex M appraisal marks (6 criteria, 52/60 = 86.7% -> Very Good)
        $updateResp = $this->actingAs($this->user)->putJson(route('hrforms.forms.update', $mForm->id), [
            'manual' => [
                'marks' => [
                    'technical_expertise_skills'      => 9,
                    'timely_completion_quality_of_work'=> 9,
                    'reliability_dependability'        => 8,
                    'response_under_pressure'          => 8,
                    'team_work_collaboration'          => 9,
                    'code_of_conduct'                  => 9,
                ],
                'remarks_by_concerned_dir' => 'Exceptional performance during the annual review period.',
            ]
        ]);
        $updateResp->assertStatus(200);

        $mForm->refresh();
        $this->assertSame('Ready', $mForm->status);

        // 4. Fill Annex N (satisfactory performance, shift amount if deficit)
        $nData = [
            'performance_status' => 'Satisfactory',
            'director_remarks'   => 'Recommended for 1-year contract extension.',
        ];
        if (!($nForm->form_data['live']['hr_balance_sufficient'] ?? true)) {
            $nData['shift_amount'] = 100000;
            $nData['shift_justification'] = 'Budget shifted from savings under operating costs.';
        }
        $updateNResp = $this->actingAs($this->user)->putJson(route('hrforms.forms.update', $nForm->id), [
            'manual' => $nData
        ]);
        $updateNResp->assertStatus(200);

        $nForm->refresh();
        $this->assertSame('Ready', $nForm->status);

        // 5. Submit Annex M & N
        $this->actingAs($this->user)->postJson(route('hrforms.forms.submit', $mForm->id))->assertStatus(200);
        $this->actingAs($this->user)->postJson(route('hrforms.forms.submit', $nForm->id))->assertStatus(200);

        $mForm->refresh();
        $nForm->refresh();
        $this->assertSame('Submitted', $mForm->status);
        $this->assertSame('Submitted', $nForm->status);

        // 6. Verify PDFs generated
        $mPdfResp = $this->actingAs($this->user)->get(route('hrforms.forms.pdf', $mForm->id));
        $mPdfResp->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $mPdfResp->getContent());

        $nPdfResp = $this->actingAs($this->user)->get(route('hrforms.forms.pdf', $nForm->id));
        $nPdfResp->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $nPdfResp->getContent());
    }

    /**
     * Test 3: Admin Configuration Screen GET and POST.
     */
    public function test_admin_settings_screen_endpoints(): void
    {
        // 1. GET /hrforms/settings
        $getResp = $this->actingAs($this->user)->get(route('hrforms.settings'));
        $getResp->assertStatus(200);
        $getResp->assertSee('Salary Bands');
        $getResp->assertSee('Approval Chains');
        $getResp->assertSee('Hiring Type Mappings');

        // 2. POST /hrforms/settings update salary band
        $band = SalaryBand::first();
        $this->assertNotNull($band);

        $postBandResp = $this->actingAs($this->user)->postJson(route('hrforms.settings.update'), [
            'setting_type'   => 'salary_band',
            'id'             => $band->id,
            'min_salary'     => 55000,
            'max_salary'     => 75000,
            'approval_level' => 'DG_NRDI',
        ]);
        $postBandResp->assertStatus(200);
        $this->assertTrue($postBandResp->json('success'));

        // 3. POST /hrforms/settings update hiring_type_map
        $map = HiringTypeMap::where('ctc_type', 'Cr')->first();
        $this->assertNotNull($map);

        $postMapResp = $this->actingAs($this->user)->postJson(route('hrforms.settings.update'), [
            'setting_type' => 'hiring_type_map',
            'id'           => $map->id,
            'hiring_type'  => 'Renewal',
            'note'         => 'Contract Renewal (show.blade.php:353) - verified',
            'is_active'    => 1,
        ]);
        $postMapResp->assertStatus(200);
        $this->assertTrue($postMapResp->json('success'));
    }
}
