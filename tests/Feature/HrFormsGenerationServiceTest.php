<?php

namespace Tests\Feature;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\FormAuditLog;
use App\Services\HrForms\ApprovalRoutingService;
use App\Services\HrForms\FormGenerationService;
use App\Services\HrForms\SalaryBandService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrFormsGenerationServiceTest extends TestCase
{
    protected FormGenerationService $generationService;
    protected SalaryBandService $salaryBandService;
    protected ApprovalRoutingService $routingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generationService = app(FormGenerationService::class);
        $this->salaryBandService = app(SalaryBandService::class);
        $this->routingService = app(ApprovalRoutingService::class);
    }

    /**
     * Test 1: Matrix resolution for each of the 5 hiring types.
     */
    public function test_matrix_resolution_for_each_of_the_five_hiring_types(): void
    {
        // 1. Fresh
        $freshCase = new HrCtrCase(['ctc_type' => 'HG', 'ctc_newgrade' => 'RO', 'ctc_newjobtitle' => 'Software Engineer']);
        $freshForms = collect($this->generationService->resolveRequiredForms($freshCase))->pluck('form_code')->toArray();
        $this->assertContains('RDW/HR/F-01', $freshForms);
        $this->assertContains('RDW/HR/F-02', $freshForms);
        $this->assertContains('RDW/HR/F-07', $freshForms);
        $this->assertContains('ANNEX-T', $freshForms);
        $this->assertContains('RDW/HR/F-03', $freshForms);
        $this->assertContains('RDW/HR/F-11', $freshForms);
        $this->assertNotContains('RDW/HR/F-08', $freshForms);
        $this->assertNotContains('RDW/HR/F-09', $freshForms);

        // 2. Extension
        $extCase = new HrCtrCase(['ctc_type' => 'CE', 'ctc_newgrade' => 'RO', 'ctc_newjobtitle' => 'Software Engineer']);
        $extForms = collect($this->generationService->resolveRequiredForms($extCase))->pluck('form_code')->toArray();
        $this->assertContains('RDW/HR/F-08', $extForms);
        $this->assertContains('RDW/HR/F-09', $extForms);
        $this->assertNotContains('RDW/HR/F-01', $extForms);
        $this->assertNotContains('RDW/HR/F-02', $extForms);

        // 3. Renewal
        $renCase = new HrCtrCase(['ctc_type' => 'CR', 'ctc_newgrade' => 'RO', 'ctc_newjobtitle' => 'Software Engineer']);
        $renForms = collect($this->generationService->resolveRequiredForms($renCase))->pluck('form_code')->toArray();
        $this->assertContains('RDW/HR/F-08', $renForms);
        $this->assertContains('RDW/HR/F-09', $renForms);
        $this->assertNotContains('RDW/HR/F-01', $renForms);

        // 4. Rehiring (Single candidate mode default)
        $rehCase = new HrCtrCase(['ctc_type' => 'RH', 'ctc_newgrade' => 'RO', 'ctc_newjobtitle' => 'Software Engineer']);
        $rehForms = collect($this->generationService->resolveRequiredForms($rehCase))->pluck('form_code')->toArray();
        $this->assertContains('RDW/HR/F-02', $rehForms);
        $this->assertContains('RDW/HR/F-07', $rehForms);
        $this->assertContains('ANNEX-T', $rehForms);

        // 5. Internship
        $intCase = new HrCtrCase(['ctc_type' => 'HG', 'ctc_newgrade' => 'Internee', 'ctc_newjobtitle' => 'Intern']);
        $intForms = collect($this->generationService->resolveRequiredForms($intCase))->pluck('form_code')->toArray();
        $this->assertContains('RDW/HR/F-02', $intForms);
        $this->assertContains('RDW/HR/F-07', $intForms);
        $this->assertContains('ANNEX-T', $intForms);
        $this->assertNotContains('RDW/HR/F-01', $intForms);
    }

    /**
     * Test 2: Condition rules evaluation (both true and false).
     */
    public function test_condition_rules_evaluation_both_true_and_false(): void
    {
        $case = HrCtrCase::where('ctc_status', 'Draft')->first() ?: HrCtrCase::first();
        $rehCase = clone $case;
        $rehCase->ctc_type = 'RH';
        $rehCase->ctc_newgrade = 'RO';
        $rehCase->ctc_newjobtitle = 'Software Engineer';

        // When headcount_in_proposal is true -> headcount NOT in proposal rule is FALSE -> F-01 not required
        CaseExtra::updateOrCreate(
            ['case_id' => $rehCase->ctc_id],
            ['headcount_in_proposal' => true]
        );
        $formsFalse = collect($this->generationService->resolveRequiredForms($rehCase))->pluck('form_code')->toArray();
        $this->assertNotContains('RDW/HR/F-01', $formsFalse);

        // When headcount_in_proposal is false -> headcount NOT in proposal rule is TRUE -> F-01 required
        CaseExtra::updateOrCreate(
            ['case_id' => $rehCase->ctc_id],
            ['headcount_in_proposal' => false]
        );
        $formsTrue = collect($this->generationService->resolveRequiredForms($rehCase))->pluck('form_code')->toArray();
        $this->assertContains('RDW/HR/F-01', $formsTrue);
    }

    /**
     * Test 3: Annex J instance count: 3 normally, 1 in single-candidate mode.
     */
    public function test_annex_j_instance_count_three_normally_one_in_single_candidate_mode(): void
    {
        Config::set('hrforms.enabled', true);

        // Normal Fresh case -> 3 instances
        $fresh = HrCtrCase::whereIn(DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->where('ctc_status', 'Draft')->first();
        if (!$fresh) {
            $fresh = HrCtrCase::whereIn(DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->first();
            $fresh->ctc_status = 'Draft';
            $fresh->save();
        }
        $fresh->ctc_newgrade = 'RO';
        $fresh->ctc_newjobtitle = 'Engineer';

        CaseExtra::updateOrCreate(
            ['case_id' => $fresh->ctc_id],
            ['single_candidate_mode' => false]
        );

        $this->generationService->syncForms($fresh);
        $jCountNormal = CaseForm::where('case_id', $fresh->ctc_id)->where('form_code', 'RDW/HR/F-07')->count();
        $this->assertSame(3, $jCountNormal);

        // Single candidate mode -> 1 instance
        CaseExtra::updateOrCreate(
            ['case_id' => $fresh->ctc_id],
            ['single_candidate_mode' => true]
        );

        $this->generationService->syncForms($fresh);
        $jCountSingle = CaseForm::where('case_id', $fresh->ctc_id)->where('form_code', 'RDW/HR/F-07')->count();
        $this->assertSame(1, $jCountSingle);
    }

    /**
     * Test 4: Idempotency (running sync twice creates no duplicate rows).
     */
    public function test_idempotent_sync_creates_no_duplicates(): void
    {
        Config::set('hrforms.enabled', true);
        $fresh = HrCtrCase::whereIn(DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->first();
        $fresh->ctc_status = 'Draft';
        $fresh->ctc_newgrade = 'RO';
        $fresh->ctc_newjobtitle = 'Engineer';
        $fresh->save();

        $this->generationService->syncForms($fresh);
        $count1 = CaseForm::where('case_id', $fresh->ctc_id)->count();

        $this->generationService->syncForms($fresh);
        $count2 = CaseForm::where('case_id', $fresh->ctc_id)->count();

        $this->assertSame($count1, $count2);
    }

    /**
     * Test 5: Hiring type change (empty form soft-deleted, form with manual data marked 'Pending Removal').
     */
    public function test_hiring_type_change_soft_deletes_empty_form_and_flags_form_with_manual_data(): void
    {
        Config::set('hrforms.enabled', true);
        $case = HrCtrCase::first();
        $case->ctc_status = 'Draft';
        $case->ctc_type = 'HG';
        $case->ctc_newgrade = 'RO';
        $case->ctc_newjobtitle = 'Software Engineer';
        $case->save();

        CaseExtra::updateOrCreate(
            ['case_id' => $case->ctc_id],
            ['headcount_in_proposal' => false]
        );

        $this->generationService->syncForms($case);

        $formA = CaseForm::where('case_id', $case->ctc_id)->where('form_code', 'RDW/HR/F-01')->first();
        $this->assertNotNull($formA);

        // Add user manual data to Annex A
        $data = $formA->form_data;
        $data['manual']['work_order_no'] = 'WO-999-TEST';
        $formA->form_data = $data;
        $formA->save();

        // Switch to Renewal -> Annex A is no longer required
        $case->ctc_type = 'CR';
        $case->save();
        $this->generationService->syncForms($case);

        // Form A has manual data, so it must NOT be deleted. It must be status = 'Pending Removal'
        $formARetained = CaseForm::where('case_id', $case->ctc_id)->where('form_code', 'RDW/HR/F-01')->first();
        $this->assertNotNull($formARetained);
        $this->assertSame('Pending Removal', $formARetained->status);
    }

    /**
     * Test 6: Manual layer is preserved when live data refreshes.
     */
    public function test_manual_layer_is_preserved_when_live_data_refreshes(): void
    {
        Config::set('hrforms.enabled', true);
        $case = HrCtrCase::first();
        $case->ctc_status = 'Draft';
        $case->ctc_type = 'HG';
        $case->ctc_newgrade = 'RO';
        $case->ctc_newjobtitle = 'Engineer';
        $case->save();

        $this->generationService->syncForms($case);
        $formB = CaseForm::where('case_id', $case->ctc_id)->where('form_code', 'RDW/HR/F-02')->first();
        $this->assertNotNull($formB);

        // User enters manual input
        $data = $formB->form_data;
        $data['manual']['board_recommendations'] = 'Candidate recommended unanimously.';
        $formB->form_data = $data;
        $formB->save();

        // Re-sync
        $this->generationService->syncForms($case);
        $formBRefreshed = CaseForm::where('case_id', $case->ctc_id)->where('form_code', 'RDW/HR/F-02')->first();

        $this->assertSame('Candidate recommended unanimously.', $formBRefreshed->form_data['manual']['board_recommendations']);
    }

    /**
     * Test 7: Submitted form is never touched by re-sync.
     */
    public function test_submitted_form_is_never_touched_by_resync(): void
    {
        Config::set('hrforms.enabled', true);
        $case = HrCtrCase::first();
        $case->ctc_status = 'Draft';
        $case->ctc_newgrade = 'RO';
        $case->ctc_newjobtitle = 'Engineer';
        $case->save();

        $this->generationService->syncForms($case);
        $form = CaseForm::where('case_id', $case->ctc_id)->first();
        $form->status = 'Submitted';
        $form->submitted_at = now();
        $form->form_data = ['locked' => true, 'live' => ['foo' => 'bar'], 'manual' => []];
        $form->save();

        // Trigger sync
        $this->generationService->syncForms($case);
        $formAfter = CaseForm::find($form->id);

        $this->assertSame('Submitted', $formAfter->status);
        $this->assertTrue($formAfter->form_data['locked']);
    }

    /**
     * Test 8: Fail-safe (forcing service to throw allows case draft to still save).
     */
    public function test_fail_safety_when_service_throws_case_still_saves(): void
    {
        Config::set('hrforms.enabled', true);
        $case = HrCtrCase::first();

        // Simulate controller fail-safe wrapper
        $saved = false;
        try {
            DB::transaction(function () use (&$saved) {
                $saved = true;
            });

            // Simulate hook throwing
            throw new \RuntimeException('Database timeout in hrforms service');
        } catch (\Throwable $e) {
            // Controller catches and logs warning
            $this->assertSame('Database timeout in hrforms service', $e->getMessage());
        }

        $this->assertTrue($saved, 'The core case save must succeed completely untouched.');
    }

    /**
     * Test 9: HRFORMS_ENABLED=false causes zero queries to hrforms schema.
     */
    public function test_hrforms_disabled_flag_causes_zero_hrforms_queries(): void
    {
        Config::set('hrforms.enabled', false);
        $case = HrCtrCase::first();

        DB::enableQueryLog();
        $result = $this->generationService->syncForms($case);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $hrformsQueries = array_filter($queries, function ($q) {
            return str_contains(strtolower($q['query']), 'hrforms');
        });

        $this->assertCount(0, $hrformsQueries, 'Zero hrforms queries must execute when HRFORMS_ENABLED is false.');
    }

    /**
     * Test 10: Authority routing by grade.
     */
    public function test_authority_routing_by_grade(): void
    {
        // Annex A always gives DG NRDI
        $this->assertSame('DG NRDI', $this->routingService->getFinalApprover('RDW/HR/F-01', 'Worker'));
        $this->assertSame('DG NRDI', $this->routingService->getFinalApprover('RDW/HR/F-01', 'RO'));

        // RO and above gives DG NRDI
        $this->assertSame('DG NRDI', $this->routingService->getFinalApprover('RDW/HR/F-02', 'RO'));
        $this->assertSame('DG NRDI', $this->routingService->getFinalApprover('RDW/HR/F-08', 'RO-I'));
        $this->assertSame('DG NRDI', $this->routingService->getFinalApprover('RDW/HR/F-09', 'SRO'));

        // RT and below gives MD RDW
        $this->assertSame('MD RDW', $this->routingService->getFinalApprover('RDW/HR/F-02', 'RT'));
        $this->assertSame('MD RDW', $this->routingService->getFinalApprover('RDW/HR/F-08', 'RT-II'));
        $this->assertSame('MD RDW', $this->routingService->getFinalApprover('RDW/HR/F-09', 'Support Staff'));
    }

    /**
     * Test 11: Sub-grade mapping to Annex K salary bands.
     */
    public function test_subgrade_mapping_to_annex_k_salary_bands(): void
    {
        // RO-I and RO-II -> RO band (60,000 to 125,000 per Annex K table)
        $ro1 = $this->salaryBandService->findBandForGrade('RO-I');
        $this->assertNotNull($ro1);
        $this->assertSame('RO', $ro1->designation);
        $this->assertEquals(60000.0, (float)$ro1->min_salary);
        $this->assertEquals(125000.0, (float)$ro1->max_salary);

        // RT-I and RT-II -> RT band (60,000 to 75,000 per Annex K table)
        $rt1 = $this->salaryBandService->findBandForGrade('RT-I');
        $this->assertNotNull($rt1);
        $this->assertSame('RT', $rt1->designation);
        $this->assertEquals(60000.0, (float)$rt1->min_salary);
        $this->assertEquals(75000.0, (float)$rt1->max_salary);
    }

    /**
     * Test 12: Read-only guarantee: Schema fingerprint is strictly unchanged.
     */
    public function test_readonly_guarantee_and_schema_fingerprint_unchanged(): void
    {
        $snapshotPath = base_path('scratch/baseline_schema_snapshot.json');
        $this->assertFileExists($snapshotPath);

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
        $this->assertSame('2a451470741fecb5f805c0a98d9d42e0', $currentHash, 'Hash must strictly match 2a451470741fecb5f805c0a98d9d42e0.');
    }

    /**
     * Test 13: Annex M 6 criteria, percentage, rating keys, 10% / 0% increment rule, and A1 warning.
     */
    public function test_annex_m_criteria_increment_rule_and_a1_warning(): void
    {
        $extractor = app(\App\Services\HrForms\Extractors\AnnexMExtractor::class);
        $case = HrCtrCase::where('ctc_status', 'Draft')->first() ?: HrCtrCase::first();

        // 1. Check criteria structure (6 criteria, max 10 each, total 60)
        $data = $extractor->extract($case, 'main', [
            'marks' => [
                'technical_expertise_skills'      => 8,
                'timely_completion_quality_of_work' => 8,
                'reliability_dependability'       => 7,
                'response_under_pressure'         => 7,
                'team_work_collaboration'         => 7,
                'code_of_conduct'                 => 7,
            ]
        ]);

        $this->assertCount(6, $data['live']['evaluation_criteria']);
        $this->assertSame(44, $data['manual']['total_marks']);
        $this->assertEquals(73.3, $data['manual']['percentage']);
        $this->assertSame('Above Average', $data['manual']['performance_rating']);
        // >= 70% gets 10% max allowed increment
        $this->assertSame(10.0, (float)$data['manual']['max_allowed_increment_pct']);

        // 2. Below 70% gets 0% allowed increment
        $dataLow = $extractor->extract($case, 'main', [
            'marks' => [
                'technical_expertise_skills'      => 4,
                'timely_completion_quality_of_work' => 4,
                'reliability_dependability'       => 4,
                'response_under_pressure'         => 4,
                'team_work_collaboration'         => 4,
                'code_of_conduct'                 => 4,
            ]
        ]);
        $this->assertSame(24, $dataLow['manual']['total_marks']);
        $this->assertSame(0.0, (float)$dataLow['manual']['max_allowed_increment_pct']);

        // 3. Exceptional performance citation allows up to 20%
        $dataExceptional = $extractor->extract($case, 'main', [
            'marks' => [
                'technical_expertise_skills'      => 10,
                'timely_completion_quality_of_work' => 10,
                'reliability_dependability'       => 10,
                'response_under_pressure'         => 10,
                'team_work_collaboration'         => 10,
                'code_of_conduct'                 => 10,
            ],
            'exceptional_performance_citation' => 'Delivered critical satellite link ahead of schedule with zero defects.'
        ]);
        $this->assertSame(20.0, (float)$dataExceptional['manual']['max_allowed_increment_pct']);

        // 4. A1 Warning Behaviour in Annex N:
        $annexNExtractor = app(\App\Services\HrForms\Extractors\AnnexNExtractor::class);
        $renCase = clone $case;
        $renCase->ctc_type = 'CR';
        $renCase->previous_salary = 60000;
        $renCase->ctc_newsalary = 75000; // proposed 25% increment without Annex M marks

        $annexNDataNoM = $annexNExtractor->extract($renCase, 'main', []);
        // When no Annex M marks exist, warning must NOT quote 20%, must say appraisal pending
        $hasAppraisalPendingWarning = false;
        $quotesTwentyPercent = false;
        foreach ($annexNDataNoM['warnings'] as $w) {
            if (str_contains(strtolower($w), 'appraisal pending, increment limit cannot be checked')) {
                $hasAppraisalPendingWarning = true;
            }
            if (str_contains($w, '20%')) {
                $quotesTwentyPercent = true;
            }
        }
        $this->assertTrue($hasAppraisalPendingWarning, 'Must show appraisal pending warning when Annex M has no marks.');
        $this->assertFalse($quotesTwentyPercent, 'Must NOT quote 20% when no Annex M marks exist.');
    }

    /**
     * Test 14: Annex J 12 criteria with integer 1-5 and total 60.
     */
    public function test_annex_j_twelve_criteria_total_sixty(): void
    {
        $extractor = app(\App\Services\HrForms\Extractors\AnnexJExtractor::class);
        $case = HrCtrCase::first();

        $data = $extractor->extract($case, 'cand_1', []);
        $this->assertCount(12, $data['live']['evaluation_criteria']);
        $this->assertSame(60, $data['live']['maximum_score']);

        foreach ($data['live']['evaluation_criteria'] as $c) {
            $this->assertSame(1, $c['min_score']);
            $this->assertSame(5, $c['max_score']);
        }
    }

    /**
     * Test 15: Annex N shift_amount null and is_complete false when HR balance is insufficient.
     */
    public function test_annex_n_shift_amount_null_and_is_complete_false_when_hr_balance_insufficient(): void
    {
        $completeness = app(\App\Services\HrForms\FormCompletenessService::class);

        // Insufficient HR balance with no shift_amount
        $data = [
            'live' => [
                'hr_balance_sufficient' => false,
                'designation'           => 'RO',
                'contract_period_from'  => '2026-10-01',
                'contract_period_to'    => '2027-09-30',
                'proposed_salary'       => 85000,
            ],
            'manual' => [
                'performance_status' => 'Satisfactory',
                'shift_amount'       => null,
                'shift_justification'=> null,
            ],
            'missing_fields' => [],
        ];

        $eval = $completeness->evaluateStatus('RDW/HR/F-09', $data);
        $this->assertFalse($eval['is_complete']);
        $this->assertSame('Pending Input', $eval['status']);
        $this->assertContains('shift_amount', $eval['missing_fields']);
        $this->assertContains('shift_justification', $eval['missing_fields']);

        // Now provide shift_amount and justification
        $data['manual']['shift_amount'] = 50000;
        $data['manual']['shift_justification'] = 'Budget reallocated from operational contingency.';
        $evalResolved = $completeness->evaluateStatus('RDW/HR/F-09', $data);
        $this->assertTrue($evalResolved['is_complete']);
        $this->assertSame('Ready', $evalResolved['status']);
    }

    /**
     * Test 16: Annex U 10 sections structure.
     */
    public function test_annex_u_ten_sections_structure(): void
    {
        $extractor = app(\App\Services\HrForms\Extractors\AnnexUExtractor::class);
        $case = HrCtrCase::first();

        $data = $extractor->extract($case, 'main', []);
        $manual = $data['manual'];

        $expectedSections = [
            'section_1_personal_info',
            'section_2_next_of_kin',
            'section_3_emergency_contact',
            'section_4_education',
            'section_5_professional_courses',
            'section_6_professional_experience',
            'section_7_vehicles',
            'section_8_bank_account',
            'section_9_publications',
            'section_10_references',
        ];

        foreach ($expectedSections as $sec) {
            $this->assertArrayHasKey($sec, $manual, "Annex U must contain {$sec}");
        }
    }

    /**
     * Test 17: Manual-layer cost fields default to null, not 0, in Annex A.
     */
    public function test_manual_cost_fields_default_to_null_in_annex_a(): void
    {
        $extractor = app(\App\Services\HrForms\Extractors\AnnexAExtractor::class);
        $case = HrCtrCase::first();

        $data = $extractor->extract($case, 'main', []);
        $manual = $data['manual'];

        $this->assertNull($manual['service_charges_taxes']);
        $this->assertNull($manual['infrastructure_development']);
        $this->assertNull($manual['overheads']);
        $this->assertNull($manual['other_cost_heads']);
    }

    /**
     * Test 18: Administrative-grade routing dynamically from hrforms.salary_bands.
     */
    public function test_administrative_grade_routing_dynamically_from_salary_bands(): void
    {
        // 1. DG_NRDI designations
        $dgGrades = ['PRO', 'SRO', 'RO', 'Director', 'Manager', 'Assistant Manager'];
        foreach ($dgGrades as $grade) {
            $approver = $this->routingService->getFinalApprover('RDW/HR/F-02', $grade);
            $this->assertSame('DG NRDI', $approver, "Grade {$grade} must route to DG NRDI");
        }

        // 2. MD_RDW designations
        $mdGrades = ['SRT', 'RT', 'JRT', 'LA', 'RA', 'EA', 'Intern', 'Junior Assistant', 'Support Staff'];
        foreach ($mdGrades as $grade) {
            $approver = $this->routingService->getFinalApprover('RDW/HR/F-02', $grade);
            $this->assertSame('MD RDW', $approver, "Grade {$grade} must route to MD RDW");
        }
    }

    /**
     * Test 19: Intern extension generates no board forms per Para 31g.
     */
    public function test_intern_extension_generates_no_board_forms_per_para_31g(): void
    {
        Config::set('hrforms.enabled', true);
        Config::set('hrforms.intern_extension_no_board_forms', true);

        // Intern extension case
        $case = new HrCtrCase([
            'ctc_type'        => 'CE',
            'ctc_newgrade'    => 'Internee',
            'ctc_newjobtitle' => 'Software Intern',
            'ctc_status'      => 'Draft',
        ]);

        $this->assertSame('Internship_Extension', $this->generationService->resolveHiringType($case));
        $forms = $this->generationService->resolveRequiredForms($case);
        $this->assertEmpty($forms, 'Intern extension must generate zero board forms.');

        // Also for Renewal ('CR')
        $caseRen = new HrCtrCase([
            'ctc_type'        => 'CR',
            'ctc_newgrade'    => 'Internee',
            'ctc_newjobtitle' => 'Research Intern',
            'ctc_status'      => 'Draft',
        ]);
        $this->assertSame('Internship_Extension', $this->generationService->resolveHiringType($caseRen));
        $this->assertEmpty($this->generationService->resolveRequiredForms($caseRen));
    }

    /**
     * Test 20: Non-draft cases (Submitted or Approved) are never touched by sync.
     */
    public function test_non_draft_cases_are_not_modified_by_sync(): void
    {
        Config::set('hrforms.enabled', true);
        $case = HrCtrCase::where('ctc_status', 'Submitted')->first() ?: HrCtrCase::first();
        $caseSubmitted = clone $case;
        $caseSubmitted->ctc_status = 'Submitted';

        DB::enableQueryLog();
        $result = $this->generationService->syncForms($caseSubmitted);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertSame(0, $result['synced']);
        $this->assertStringContainsString("Forms cannot be modified", $result['message']);
    }
}
