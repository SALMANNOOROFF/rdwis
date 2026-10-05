<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seeds initial configuration into hrforms schema.
     * Idempotent: safe to run multiple times without creating duplicates.
     */
    public function up(): void
    {
        // 1. Seed form templates (upsert on form_code)
        $templates = [
            [
                'form_code'     => 'RDW/HR/F-01',
                'annex'         => 'A',
                'title'         => 'In-Principle Approval for Hiring of HR',
                'category'      => 'approval',
                'view_template' => 'hrforms.templates.annex_a',
            ],
            [
                'form_code'     => 'RDW/HR/F-02',
                'annex'         => 'B',
                'title'         => 'Hiring Board for Selection of Contract Employee',
                'category'      => 'board',
                'view_template' => 'hrforms.templates.annex_b',
            ],
            [
                'form_code'     => 'RDW/HR/F-07',
                'annex'         => 'J',
                'title'         => 'Initial Interview / Screening Form - RDW',
                'category'      => 'screening',
                'view_template' => 'hrforms.templates.annex_j',
            ],
            [
                'form_code'     => 'ANNEX-T',
                'annex'         => 'T',
                'title'         => 'Comparison Matrix of Shortlisted Candidates',
                'category'      => 'matrix',
                'view_template' => 'hrforms.templates.annex_t',
            ],
            [
                'form_code'     => 'RDW/HR/F-09',
                'annex'         => 'N',
                'title'         => 'Renewal / Extension of Contract(s)',
                'category'      => 'renewal',
                'view_template' => 'hrforms.templates.annex_n',
            ],
            [
                'form_code'     => 'RDW/HR/F-08',
                'annex'         => 'M',
                'title'         => 'Performance Appraisal Form',
                'category'      => 'appraisal',
                'view_template' => 'hrforms.templates.annex_m',
            ],
            [
                'form_code'     => 'RDW/HR/F-03',
                'annex'         => 'D',
                'title'         => 'Non-Disclosure Agreement',
                'category'      => 'joining',
                'view_template' => 'hrforms.templates.annex_d',
            ],
            [
                'form_code'     => 'RDW/HR/F-11',
                'annex'         => 'U',
                'title'         => 'Personal Data Form',
                'category'      => 'joining',
                'view_template' => 'hrforms.templates.annex_u',
            ],
        ];

        foreach ($templates as $t) {
            DB::table('hrforms.form_templates')->updateOrInsert(
                ['form_code' => $t['form_code']],
                array_merge($t, ['is_active' => true, 'updated_at' => now()])
            );
        }

        // 2. Seed Form-to-Hiring-Type Matrix (upsert on form_code + hiring_type)
        // requirement values: required, not_required, conditional, at_joining, optional, relaxed
        $matrix = [
            // Fresh Hiring
            ['form_code' => 'RDW/HR/F-01', 'hiring_type' => 'Fresh', 'requirement' => 'conditional',  'condition_rule' => 'headcount_not_in_proposal', 'default_order' => 1],
            ['form_code' => 'RDW/HR/F-02', 'hiring_type' => 'Fresh', 'requirement' => 'required',     'condition_rule' => null,                         'default_order' => 2],
            ['form_code' => 'RDW/HR/F-07', 'hiring_type' => 'Fresh', 'requirement' => 'required',     'condition_rule' => null,                         'default_order' => 3],
            ['form_code' => 'ANNEX-T',     'hiring_type' => 'Fresh', 'requirement' => 'required',     'condition_rule' => null,                         'default_order' => 4],
            ['form_code' => 'RDW/HR/F-09', 'hiring_type' => 'Fresh', 'requirement' => 'not_required', 'condition_rule' => null,                         'default_order' => 5],
            ['form_code' => 'RDW/HR/F-08', 'hiring_type' => 'Fresh', 'requirement' => 'not_required', 'condition_rule' => null,                         'default_order' => 6],
            ['form_code' => 'RDW/HR/F-03', 'hiring_type' => 'Fresh', 'requirement' => 'at_joining',   'condition_rule' => null,                         'default_order' => 7],
            ['form_code' => 'RDW/HR/F-11', 'hiring_type' => 'Fresh', 'requirement' => 'at_joining',   'condition_rule' => null,                         'default_order' => 8],

            // Contract Extension (<1 year)
            ['form_code' => 'RDW/HR/F-01', 'hiring_type' => 'Extension', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 1],
            ['form_code' => 'RDW/HR/F-02', 'hiring_type' => 'Extension', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 2],
            ['form_code' => 'RDW/HR/F-07', 'hiring_type' => 'Extension', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 3],
            ['form_code' => 'ANNEX-T',     'hiring_type' => 'Extension', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 4],
            ['form_code' => 'RDW/HR/F-09', 'hiring_type' => 'Extension', 'requirement' => 'required',     'condition_rule' => null, 'default_order' => 5],
            ['form_code' => 'RDW/HR/F-08', 'hiring_type' => 'Extension', 'requirement' => 'required',     'condition_rule' => null, 'default_order' => 6],
            ['form_code' => 'RDW/HR/F-03', 'hiring_type' => 'Extension', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 7],
            ['form_code' => 'RDW/HR/F-11', 'hiring_type' => 'Extension', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 8],

            // Contract Renewal (1 year)
            ['form_code' => 'RDW/HR/F-01', 'hiring_type' => 'Renewal', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 1],
            ['form_code' => 'RDW/HR/F-02', 'hiring_type' => 'Renewal', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 2],
            ['form_code' => 'RDW/HR/F-07', 'hiring_type' => 'Renewal', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 3],
            ['form_code' => 'ANNEX-T',     'hiring_type' => 'Renewal', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 4],
            ['form_code' => 'RDW/HR/F-09', 'hiring_type' => 'Renewal', 'requirement' => 'required',     'condition_rule' => null, 'default_order' => 5],
            ['form_code' => 'RDW/HR/F-08', 'hiring_type' => 'Renewal', 'requirement' => 'required',     'condition_rule' => null, 'default_order' => 6],
            ['form_code' => 'RDW/HR/F-03', 'hiring_type' => 'Renewal', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 7],
            ['form_code' => 'RDW/HR/F-11', 'hiring_type' => 'Renewal', 'requirement' => 'not_required', 'condition_rule' => null, 'default_order' => 8],

            // Rehiring
            ['form_code' => 'RDW/HR/F-01', 'hiring_type' => 'Rehiring', 'requirement' => 'conditional',  'condition_rule' => 'headcount_not_in_proposal', 'default_order' => 1],
            ['form_code' => 'RDW/HR/F-02', 'hiring_type' => 'Rehiring', 'requirement' => 'required',     'condition_rule' => 'single_candidate_allowed',  'default_order' => 2],
            ['form_code' => 'RDW/HR/F-07', 'hiring_type' => 'Rehiring', 'requirement' => 'required',     'condition_rule' => null,                         'default_order' => 3],
            ['form_code' => 'ANNEX-T',     'hiring_type' => 'Rehiring', 'requirement' => 'required',     'condition_rule' => 'single_candidate_allowed',  'default_order' => 4],
            ['form_code' => 'RDW/HR/F-09', 'hiring_type' => 'Rehiring', 'requirement' => 'not_required', 'condition_rule' => null,                         'default_order' => 5],
            ['form_code' => 'RDW/HR/F-08', 'hiring_type' => 'Rehiring', 'requirement' => 'optional',     'condition_rule' => 'attach_last_appraisal',     'default_order' => 6],
            ['form_code' => 'RDW/HR/F-03', 'hiring_type' => 'Rehiring', 'requirement' => 'at_joining',   'condition_rule' => null,                         'default_order' => 7],
            ['form_code' => 'RDW/HR/F-11', 'hiring_type' => 'Rehiring', 'requirement' => 'at_joining',   'condition_rule' => null,                         'default_order' => 8],

            // Internship
            ['form_code' => 'RDW/HR/F-01', 'hiring_type' => 'Internship', 'requirement' => 'not_required', 'condition_rule' => null,                         'default_order' => 1],
            ['form_code' => 'RDW/HR/F-02', 'hiring_type' => 'Internship', 'requirement' => 'required',     'condition_rule' => 'single_candidate_internee', 'default_order' => 2],
            ['form_code' => 'RDW/HR/F-07', 'hiring_type' => 'Internship', 'requirement' => 'required',     'condition_rule' => 'single_candidate_internee', 'default_order' => 3],
            ['form_code' => 'ANNEX-T',     'hiring_type' => 'Internship', 'requirement' => 'relaxed',      'condition_rule' => 'relaxed_candidate_min',     'default_order' => 4],
            ['form_code' => 'RDW/HR/F-09', 'hiring_type' => 'Internship', 'requirement' => 'not_required', 'condition_rule' => null,                         'default_order' => 5],
            ['form_code' => 'RDW/HR/F-08', 'hiring_type' => 'Internship', 'requirement' => 'not_required', 'condition_rule' => null,                         'default_order' => 6],
            ['form_code' => 'RDW/HR/F-03', 'hiring_type' => 'Internship', 'requirement' => 'at_joining',   'condition_rule' => null,                         'default_order' => 7],
            ['form_code' => 'RDW/HR/F-11', 'hiring_type' => 'Internship', 'requirement' => 'at_joining',   'condition_rule' => null,                         'default_order' => 8],
        ];

        foreach ($matrix as $m) {
            DB::table('hrforms.form_matrix')->updateOrInsert(
                ['form_code' => $m['form_code'], 'hiring_type' => $m['hiring_type']],
                array_merge($m, ['is_active' => true, 'updated_at' => now()])
            );
        }

        // 3. Seed Annex K Salary Bands (upsert on designation)
        $bands = [
            // Technical Designations
            [
                'designation' => 'Intern',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['INTERN', 'INT']),
                'min_salary'  => 40000.00,
                'max_salary'  => 50000.00,
                'notes'       => 'Paid internship band per Annex K',
            ],
            [
                'designation' => 'EA',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['EA', 'ENGINEERING AIDE', 'ENG AIDE']),
                'min_salary'  => 40000.00,
                'max_salary'  => 50000.00,
                'notes'       => 'Undergraduate student position',
            ],
            [
                'designation' => 'RA',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['RA', 'RESEARCH AIDE', 'RES AIDE']),
                'min_salary'  => 50000.00,
                'max_salary'  => 65000.00,
                'notes'       => 'Postgraduate student position',
            ],
            [
                'designation' => 'LA',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['LA', 'LAB ATTENDANT', 'LABORATORY ATTENDANT']),
                'min_salary'  => 40000.00,
                'max_salary'  => 50000.00,
                'notes'       => 'Matric or equivalent',
            ],
            [
                'designation' => 'JRT',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['JRT', 'JRT-I', 'JRT-II', 'JUNIOR RESEARCH TECHNICIAN']),
                'min_salary'  => 50000.00,
                'max_salary'  => 60000.00,
                'notes'       => 'Intermediate or equivalent',
            ],
            [
                'designation' => 'RT',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['RT', 'RT-I', 'RT-II', 'RESEARCH TECHNICIAN']),
                'min_salary'  => 60000.00,
                'max_salary'  => 75000.00,
                'notes'       => 'DAE or equivalent',
            ],
            [
                'designation' => 'SRT',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['SRT', 'SRT-I', 'SRT-II', 'SENIOR RESEARCH TECHNICIAN']),
                'min_salary'  => 75000.00,
                'max_salary'  => 100000.00,
                'notes'       => 'B.Tech or equivalent',
            ],
            [
                'designation' => 'RO',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['RO', 'RO-I', 'RO-II', 'RO-III', 'RESEARCH OFFICER']),
                'min_salary'  => 60000.00,
                'max_salary'  => 125000.00,
                'notes'       => 'BE/BS up to 3 years of experience',
            ],
            [
                'designation' => 'SRO',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['SRO', 'SRO-I', 'SRO-II', 'SENIOR RESEARCH OFFICER']),
                'min_salary'  => 125000.00,
                'max_salary'  => 225000.00,
                'notes'       => 'MS/BE/BS with more than 3 years experience',
            ],
            [
                'designation' => 'PRO',
                'category'    => 'Technical',
                'sub_grades'  => json_encode(['PRO', 'PRINCIPAL RESEARCH OFFICER', 'PRIN RESEARCH OFFICER']),
                'min_salary'  => 225000.00,
                'max_salary'  => 325000.00,
                'notes'       => 'PhD/MS with more than 5 years experience',
            ],

            // Administrative Designations
            [
                'designation' => 'Director',
                'category'    => 'Administrative',
                'sub_grades'  => json_encode(['DIRECTOR', 'DIR']),
                'min_salary'  => 200000.00,
                'max_salary'  => 275000.00,
                'notes'       => 'Masters/Bachelors with 15 years experience',
            ],
            [
                'designation' => 'Manager',
                'category'    => 'Administrative',
                'sub_grades'  => json_encode(['MANAGER', 'MGR']),
                'min_salary'  => 150000.00,
                'max_salary'  => 200000.00,
                'notes'       => 'Masters/Bachelors with 12 years experience',
            ],
            [
                'designation' => 'Assistant Manager',
                'category'    => 'Administrative',
                'sub_grades'  => json_encode(['ASSISTANT MANAGER', 'AM']),
                'min_salary'  => 110000.00,
                'max_salary'  => 130000.00,
                'notes'       => 'Bachelors with 05 years experience',
            ],
            [
                'designation' => 'Junior Assistant',
                'category'    => 'Administrative',
                'sub_grades'  => json_encode(['JUNIOR ASSISTANT', 'JA', 'ADMINISTRATIVE JUNIOR ASSISTANT']),
                'min_salary'  => 45000.00,
                'max_salary'  => 55000.00,
                'notes'       => 'Intermediate or equivalent',
            ],
            [
                'designation' => 'Support Staff',
                'category'    => 'Administrative',
                'sub_grades'  => json_encode(['SUPPORT STAFF', 'SS', 'WORKER', 'LABOR', 'GARDENER', 'NAIB QASID', 'DIVER', 'MAALI']),
                'min_salary'  => 40000.00,
                'max_salary'  => 50000.00,
                'notes'       => 'Middle/Matric',
            ],
        ];

        foreach ($bands as $b) {
            DB::table('hrforms.salary_bands')->updateOrInsert(
                ['designation' => $b['designation']],
                array_merge($b, ['currency' => 'PKR', 'is_active' => true, 'updated_at' => now()])
            );
        }

        // 4. Seed Approval Chains (upsert on form_code + grade_level + sequence)
        // grade_level strictly in ('ALL', 'RO_AND_ABOVE', 'RT_AND_BELOW')
        $annexAChain = [
            ['form_code' => 'RDW/HR/F-01', 'grade_level' => 'ALL', 'sequence' => 1, 'role_title' => 'Project Director',          'action_type' => 'Initiation',     'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-01', 'grade_level' => 'ALL', 'sequence' => 2, 'role_title' => 'Dir HR / SO HR',            'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-01', 'grade_level' => 'ALL', 'sequence' => 3, 'role_title' => 'Dir Finance / SO Finance',  'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-01', 'grade_level' => 'ALL', 'sequence' => 4, 'role_title' => 'MD RDW',                    'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-01', 'grade_level' => 'ALL', 'sequence' => 5, 'role_title' => 'Dir Log NRDI',             'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-01', 'grade_level' => 'ALL', 'sequence' => 6, 'role_title' => 'DG NRDI',                  'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true],
        ];

        $roForms = ['RDW/HR/F-02', 'RDW/HR/F-08', 'RDW/HR/F-09'];
        $dynamicChains = [];

        foreach ($roForms as $fc) {
            // RO and Above -> Final Approver DG NRDI
            $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 1, 'role_title' => 'Project Director',        'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
            $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 2, 'role_title' => 'Co-PI / Subject Expert',  'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
            $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 3, 'role_title' => 'Dir HR / SO HR',          'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
            if ($fc === 'RDW/HR/F-09') {
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 4, 'role_title' => 'Dir Finance',          'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 5, 'role_title' => 'MD RDW',                'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 6, 'role_title' => 'DG NRDI',              'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true];
            } else {
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 4, 'role_title' => 'MD RDW',                'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 5, 'role_title' => 'DG NRDI',              'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true];
            }

            // RT and Below -> Final Approver MD R&D Wing
            $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RT_AND_BELOW', 'sequence' => 1, 'role_title' => 'Project Director',        'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
            $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RT_AND_BELOW', 'sequence' => 2, 'role_title' => 'Co-PI / Subject Expert',  'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
            $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RT_AND_BELOW', 'sequence' => 3, 'role_title' => 'Dir HR / SO HR',          'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
            if ($fc === 'RDW/HR/F-09') {
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RT_AND_BELOW', 'sequence' => 4, 'role_title' => 'Dir Finance',          'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false];
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RT_AND_BELOW', 'sequence' => 5, 'role_title' => 'MD RDW',                'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true];
            } else {
                $dynamicChains[] = ['form_code' => $fc, 'grade_level' => 'RT_AND_BELOW', 'sequence' => 4, 'role_title' => 'MD RDW',                'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true];
            }
        }

        foreach (array_merge($annexAChain, $dynamicChains) as $c) {
            DB::table('hrforms.approval_chains')->updateOrInsert(
                ['form_code' => $c['form_code'], 'grade_level' => $c['grade_level'], 'sequence' => $c['sequence']],
                array_merge($c, ['updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     * Truncates initial configuration data from hrforms schema.
     */
    public function down(): void
    {
        DB::statement('TRUNCATE TABLE hrforms.approval_chains CASCADE;');
        DB::statement('TRUNCATE TABLE hrforms.salary_bands CASCADE;');
        DB::statement('TRUNCATE TABLE hrforms.form_matrix CASCADE;');
        DB::statement('TRUNCATE TABLE hrforms.form_templates CASCADE;');
    }
};
