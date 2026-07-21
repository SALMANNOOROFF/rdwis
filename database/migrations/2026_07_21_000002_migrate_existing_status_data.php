<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate existing cases with new-style pcs_status values ('With DFinance', 'With MD', etc.)
     * back to legacy pcs_status values + insert corresponding purcase_substatus rows.
     *
     * Also seeds initial substatus rows for Draft and Returned cases.
     */
    public function up(): void
    {
        // Map old-style pcs_status → [new pcs_status, pss_stage]
        $mappings = [
            'With DFinance' => ['Under Approval', 'DFinance'],
            'With MD'       => ['Under Approval', 'MD'],
            'With DDG'      => ['Under Approval', 'DDG'],
            'With DG'       => ['Under Approval', 'DG'],
        ];

        foreach ($mappings as $oldStatus => [$newPcsStatus, $stage]) {
            $cases = DB::table('pur.purcases')
                ->where('pcs_status', $oldStatus)
                ->get(['pcs_id']);

            foreach ($cases as $case) {
                // Update pcs_status to legacy value
                DB::table('pur.purcases')
                    ->where('pcs_id', $case->pcs_id)
                    ->update(['pcs_status' => $newPcsStatus]);

                // Insert current substatus row (only if not already exists)
                $exists = DB::table('pur.purcase_substatus')
                    ->where('pss_pcs_id', $case->pcs_id)
                    ->where('pss_is_current', true)
                    ->exists();

                if (!$exists) {
                    DB::table('pur.purcase_substatus')->insert([
                        'pss_pcs_id'    => $case->pcs_id,
                        'pss_stage'     => $stage,
                        'pss_is_current'=> true,
                    ]);
                }
            }
        }

        // Handle 'Returned' cases — keep pcs_status as 'Returned', stage = 'Division'
        $returnedCases = DB::table('pur.purcases')
            ->where('pcs_status', 'Returned')
            ->get(['pcs_id']);

        foreach ($returnedCases as $case) {
            $exists = DB::table('pur.purcase_substatus')
                ->where('pss_pcs_id', $case->pcs_id)
                ->where('pss_is_current', true)
                ->exists();

            if (!$exists) {
                DB::table('pur.purcase_substatus')->insert([
                    'pss_pcs_id'    => $case->pcs_id,
                    'pss_stage'     => 'Division',
                    'pss_is_current'=> true,
                ]);
            }
        }

        // Handle 'Draft' cases — stage = 'Division'
        $draftCases = DB::table('pur.purcases')
            ->where('pcs_status', 'Draft')
            ->get(['pcs_id']);

        foreach ($draftCases as $case) {
            $exists = DB::table('pur.purcase_substatus')
                ->where('pss_pcs_id', $case->pcs_id)
                ->where('pss_is_current', true)
                ->exists();

            if (!$exists) {
                DB::table('pur.purcase_substatus')->insert([
                    'pss_pcs_id'    => $case->pcs_id,
                    'pss_stage'     => 'Division',
                    'pss_is_current'=> true,
                ]);
            }
        }

        // Handle 'Under Scrutiny' cases from the new app era (pcs_id >= 1483)
        // These were cases forwarded to DProc in the new system — treat as DFinance stage
        $underScrutinyCases = DB::table('pur.purcases')
            ->where('pcs_status', 'Under Scrutiny')
            ->where('pcs_id', '>=', 1483)
            ->get(['pcs_id']);

        foreach ($underScrutinyCases as $case) {
            DB::table('pur.purcases')
                ->where('pcs_id', $case->pcs_id)
                ->update(['pcs_status' => 'Under Approval']);

            $exists = DB::table('pur.purcase_substatus')
                ->where('pss_pcs_id', $case->pcs_id)
                ->where('pss_is_current', true)
                ->exists();

            if (!$exists) {
                DB::table('pur.purcase_substatus')->insert([
                    'pss_pcs_id'    => $case->pcs_id,
                    'pss_stage'     => 'DFinance',
                    'pss_is_current'=> true,
                ]);
            }
        }

        // Handle 'Under Approval' cases that already exist (legacy)
        $underApprovalCases = DB::table('pur.purcases')
            ->where('pcs_status', 'Under Approval')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pur.purcase_substatus')
                    ->whereColumn('pss_pcs_id', 'pur.purcases.pcs_id')
                    ->where('pss_is_current', true);
            })
            ->get(['pcs_id']);

        foreach ($underApprovalCases as $case) {
            // Legacy 'Under Approval' — default to DFinance stage (best guess)
            DB::table('pur.purcase_substatus')->insert([
                'pss_pcs_id'    => $case->pcs_id,
                'pss_stage'     => 'DFinance',
                'pss_is_current'=> true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the status mappings
        $reverseMappings = [
            'DFinance' => 'With DFinance',
            'MD'       => 'With MD',
            'DDG'      => 'With DDG',
            'DG'       => 'With DG',
        ];

        foreach ($reverseMappings as $stage => $oldStatus) {
            $caseIds = DB::table('pur.purcase_substatus')
                ->where('pss_stage', $stage)
                ->where('pss_is_current', true)
                ->pluck('pss_pcs_id');

            if ($caseIds->isNotEmpty()) {
                DB::table('pur.purcases')
                    ->whereIn('pcs_id', $caseIds)
                    ->where('pcs_status', 'Under Approval')
                    ->update(['pcs_status' => $oldStatus]);
            }
        }

        // Remove all substatus rows (table will be dropped by the previous migration's down())
        DB::table('pur.purcase_substatus')->truncate();
    }
};
