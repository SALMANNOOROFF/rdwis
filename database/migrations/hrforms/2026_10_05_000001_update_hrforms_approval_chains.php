<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates seeded approval chains for Annex M (F-08) and Annex N (F-09) to strictly match PDF policy.
     */
    public function up(): void
    {
        // 1. Clear existing chains for F-08 and F-09
        DB::statement("DELETE FROM hrforms.approval_chains WHERE form_code IN ('RDW/HR/F-08', 'RDW/HR/F-09');");

        // 2. Insert corrected chains for RDW/HR/F-08 (Annex M: Performance Appraisal)
        // PDF: Concerned Director > MD RDW > Approval (DG NRDI for RO and above, MD RDW for RT and below)
        $mChains = [
            // RO_AND_ABOVE
            ['form_code' => 'RDW/HR/F-08', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 1, 'role_title' => 'Concerned Director', 'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-08', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 2, 'role_title' => 'MD RDW',             'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-08', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 3, 'role_title' => 'DG NRDI',            'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true],
            // RT_AND_BELOW
            ['form_code' => 'RDW/HR/F-08', 'grade_level' => 'RT_AND_BELOW', 'sequence' => 1, 'role_title' => 'Concerned Director', 'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-08', 'grade_level' => 'RT_AND_BELOW', 'sequence' => 2, 'role_title' => 'MD RDW',             'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true],
        ];

        // 3. Insert corrected chains for RDW/HR/F-09 (Annex N: Renewal / Extension)
        // PDF: Project Director / PI > Dir HR / SO HR > Dir Finance > MD RDW > Approval (DG NRDI / MD RDW)
        $nChains = [
            // RO_AND_ABOVE
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 1, 'role_title' => 'Project Director / PI', 'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 2, 'role_title' => 'Dir HR / SO HR',         'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 3, 'role_title' => 'Dir Finance',           'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 4, 'role_title' => 'MD RDW',                 'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RO_AND_ABOVE', 'sequence' => 5, 'role_title' => 'DG NRDI',               'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true],
            // RT_AND_BELOW
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RT_AND_BELOW', 'sequence' => 1, 'role_title' => 'Project Director / PI', 'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RT_AND_BELOW', 'sequence' => 2, 'role_title' => 'Dir HR / SO HR',         'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RT_AND_BELOW', 'sequence' => 3, 'role_title' => 'Dir Finance',           'action_type' => 'Recommendation', 'can_approve' => false, 'is_final_authority' => false],
            ['form_code' => 'RDW/HR/F-09', 'grade_level' => 'RT_AND_BELOW', 'sequence' => 4, 'role_title' => 'MD RDW',                 'action_type' => 'Approval',       'can_approve' => true,  'is_final_authority' => true],
        ];

        foreach (array_merge($mChains, $nChains) as $c) {
            DB::table('hrforms.approval_chains')->updateOrInsert(
                ['form_code' => $c['form_code'], 'grade_level' => $c['grade_level'], 'sequence' => $c['sequence']],
                array_merge($c, ['updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DELETE FROM hrforms.approval_chains WHERE form_code IN ('RDW/HR/F-08', 'RDW/HR/F-09');");
    }
};
