<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrFormsCtrCasesImmutabilityTest extends TestCase
{
    /**
     * Exact verified ground-truth counts in updatedrdwV1.
     */
    protected array $expectedTypeCounts = [
        'Cr' => 111,
        'Hg' => 104,
        'HG' => 22,
        'Ce' => 20,
        'CR' => 9,
    ];

    protected array $expectedStatusCounts = [
        'Fulfilled'         => 121,
        'Draft'             => 76,
        'Cancelled'         => 49,
        'Under Revision'    => 8,
        'Approved'          => 6,
        'Under HR Scrutiny' => 4,
        'Under Approval'    => 2,
    ];

    /**
     * Test: hr.ctrcases counts per ctc_type and ctc_status are strictly immutable.
     */
    public function test_ctrcases_counts_per_type_and_status_are_strictly_unchanged(): void
    {
        // 1. Snapshot live DB state before assertion
        $liveTypeRows = DB::table('hr.ctrcases')
            ->select('ctc_type', DB::raw('count(*) as cnt'))
            ->groupBy('ctc_type')
            ->get();

        $liveTypeCounts = [];
        $totalTypeCount = 0;
        foreach ($liveTypeRows as $row) {
            $liveTypeCounts[$row->ctc_type] = (int) $row->cnt;
            $totalTypeCount += (int) $row->cnt;
        }

        // 2. Snapshot live ctc_status counts
        $liveStatusRows = DB::table('hr.ctrcases')
            ->select('ctc_status', DB::raw('count(*) as cnt'))
            ->groupBy('ctc_status')
            ->get();

        $liveStatusCounts = [];
        $totalStatusCount = 0;
        foreach ($liveStatusRows as $row) {
            $liveStatusCounts[$row->ctc_status] = (int) $row->cnt;
            $totalStatusCount += (int) $row->cnt;
        }

        // 3. Verify total row count is exactly 266
        $this->assertSame(266, $totalTypeCount, "Total cases count in hr.ctrcases must remain exactly 266.");
        $this->assertSame(266, $totalStatusCount, "Total cases count across statuses must remain exactly 266.");

        // 4. Verify per-type counts match ground-truth exactly
        foreach ($this->expectedTypeCounts as $type => $expected) {
            $this->assertArrayHasKey($type, $liveTypeCounts, "ctc_type '{$type}' must exist in hr.ctrcases.");
            $this->assertSame(
                $expected,
                $liveTypeCounts[$type],
                "ctc_type '{$type}' count changed! Expected {$expected}, got {$liveTypeCounts[$type]}."
            );
        }

        // 5. Verify per-status counts match ground-truth exactly
        foreach ($this->expectedStatusCounts as $status => $expected) {
            $this->assertArrayHasKey($status, $liveStatusCounts, "ctc_status '{$status}' must exist in hr.ctrcases.");
            $this->assertSame(
                $expected,
                $liveStatusCounts[$status],
                "ctc_status '{$status}' count changed! Expected {$expected}, got {$liveStatusCounts[$status]}."
            );
        }
    }

    /**
     * Dynamic Immutability Test (No Hardcoded Literals):
     * Takes pre-suite live counts, triggers sync & form generation across multiple cases,
     * and asserts that per-ctc_type and per-ctc_status counts before and after are strictly identical.
     */
    public function test_dynamic_immutability_snapshot_before_and_after_hrforms_operations(): void
    {
        // 1. Dynamic Snapshot Before
        $typeCountsBefore = DB::table('hr.ctrcases')
            ->select('ctc_type', DB::raw('count(*) as cnt'))
            ->groupBy('ctc_type')
            ->pluck('cnt', 'ctc_type')
            ->toArray();

        $statusCountsBefore = DB::table('hr.ctrcases')
            ->select('ctc_status', DB::raw('count(*) as cnt'))
            ->groupBy('ctc_status')
            ->pluck('cnt', 'ctc_status')
            ->toArray();

        $allRowsBefore = DB::table('hr.ctrcases')
            ->select('ctc_id', 'ctc_type', 'ctc_status')
            ->get()
            ->keyBy('ctc_id');

        // 2. Perform HR Forms operations across multiple active cases
        $generationService = app(\App\Services\HrForms\FormGenerationService::class);
        $sampleCases = \App\Models\HrCtrCase::take(10)->get();
        foreach ($sampleCases as $case) {
            $generationService->syncForms($case);
        }

        // 3. Dynamic Snapshot After
        $typeCountsAfter = DB::table('hr.ctrcases')
            ->select('ctc_type', DB::raw('count(*) as cnt'))
            ->groupBy('ctc_type')
            ->pluck('cnt', 'ctc_type')
            ->toArray();

        $statusCountsAfter = DB::table('hr.ctrcases')
            ->select('ctc_status', DB::raw('count(*) as cnt'))
            ->groupBy('ctc_status')
            ->pluck('cnt', 'ctc_status')
            ->toArray();

        $allRowsAfter = DB::table('hr.ctrcases')
            ->select('ctc_id', 'ctc_type', 'ctc_status')
            ->get()
            ->keyBy('ctc_id');

        // 4. Compare before and after dynamically - zero modifications allowed
        $this->assertSame($typeCountsBefore, $typeCountsAfter, 'per-ctc_type counts must be 100% identical before and after operations.');
        $this->assertSame($statusCountsBefore, $statusCountsAfter, 'per-ctc_status counts must be 100% identical before and after operations.');
        $this->assertSame($allRowsBefore->count(), $allRowsAfter->count(), 'Total case rows count must be 100% identical before and after operations.');

        foreach ($allRowsBefore as $id => $rowBefore) {
            $rowAfter = $allRowsAfter->get($id);
            $this->assertNotNull($rowAfter, "Case #{$id} must persist unchanged.");
            $this->assertSame($rowBefore->ctc_type, $rowAfter->ctc_type, "Case #{$id} ctc_type was modified!");
            $this->assertSame($rowBefore->ctc_status, $rowAfter->ctc_status, "Case #{$id} ctc_status was modified!");
        }
    }
}
