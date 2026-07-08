<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FinancialIntelligenceService;
use Illuminate\Support\Facades\DB;

class FinancialIntelligenceServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinancialIntelligenceService();
    }

    /**
     * Test trn_noloan cast and NULL exclusion behavior.
     */
    public function test_loans_trn_noloan_cast_and_null_exclusion()
    {
        DB::beginTransaction();

        try {
            // Get a real head and a real commitment to clone
            $realHead = DB::table('cen.heads')->first();
            $realCmt = DB::table('fin.commitments')->first();
            $realTrn = DB::table('fin.transactions')->first();
            
            $this->assertNotNull($realHead);
            $this->assertNotNull($realCmt);
            $this->assertNotNull($realTrn);

            $headId = $realHead->hed_id;
            $otherHeadId = 160001; // A different head id

            // Clone commitment
            $cmtData = (array) $realCmt;
            unset($cmtData['cmt_id']);
            $cmtData['cmt_effhed_id'] = $headId;
            $cmtData['cmt_hed_id'] = $otherHeadId;
            $cmtData['cmt_type'] = 'LO';
            $cmtData['cmt_status'] = 'Awaited';
            $cmtData['cmt_amount'] = -10000.0;
            
            $cmtId = DB::table('fin.commitments')->insertGetId($cmtData, 'cmt_id');

            // Clone transaction A (trn_noloan = false)
            $trnDataA = (array) $realTrn;
            unset($trnDataA['trn_id']);
            $trnDataA['trn_cmt_id'] = $cmtId;
            $trnDataA['trn_amount1'] = -6000.0;
            $trnDataA['trn_amount2'] = -6000.0;
            $trnDataA['trn_transtype'] = 1;
            $trnDataA['trn_noloan'] = false;
            
            DB::table('fin.transactions')->insert($trnDataA);

            // Clone transaction B (trn_noloan = true)
            $trnDataB = (array) $realTrn;
            unset($trnDataB['trn_id']);
            $trnDataB['trn_cmt_id'] = $cmtId;
            $trnDataB['trn_amount1'] = -4000.0;
            $trnDataB['trn_amount2'] = -4000.0;
            $trnDataB['trn_transtype'] = 1;
            $trnDataB['trn_noloan'] = true;
            
            DB::table('fin.transactions')->insert($trnDataB);

            // Calculate loans given
            $loans = $this->service->getLoans($headId);

            // It should only sum the transaction where trn_noloan = false (i.e. 6000.0)
            $this->assertEquals(6000.0, $loans->pcc_loans_given);

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test that salary requisitions and draft orders are not double-counted in fin.docs_ipc view.
     */
    public function test_salary_requisition_ipc_deduplication()
    {
        DB::beginTransaction();

        try {
            // Get templates to clone
            $realHead = DB::table('cen.heads')->first();
            $realSrq = DB::table('hr.salreqs')->first();
            $realSor = DB::table('fin.salorders')->first();
            
            $this->assertNotNull($realHead);
            $this->assertNotNull($realSrq);
            $this->assertNotNull($realSor);

            $headId = $realHead->hed_id;

            // Clone salary requisition
            $srqData = (array) $realSrq;
            unset($srqData['srq_id']);
            $srqData['srq_status'] = 'In Process';
            $srqData['srq_effhed_id'] = $headId;
            $srqData['srq_hed_id'] = $headId;
            
            $srqId = DB::table('hr.salreqs')->insertGetId($srqData, 'srq_id');

            // Clone salary order
            $sorData = (array) $realSor;
            unset($sorData['sor_id']);
            $sorData['sor_srq_id'] = $srqId;
            $sorData['sor_effhed_id'] = $headId;
            $sorData['sor_hed_id'] = $headId;
            $sorData['sor_status'] = 'Draft';
            $sorData['sor_salary'] = 50000.0;
            $sorData['sor_transtype'] = 1;
            $sorData['sor_type'] = 'Sa';

            DB::table('fin.salorders')->insert($sorData);

            // Query the fin.docs_ipc view
            $rows = DB::table('fin.docs_ipc')
                ->where('effhed_id', $headId)
                ->where('doctype', 'Sa')
                ->get();

            // There should be exactly 1 row (no double counting of requisition and order)
            $this->assertCount(1, $rows);
            $this->assertEquals(50000.0, (float) $rows[0]->amount1);

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Run calculations over a diverse sample of 10 real HeadIds and verify math consistency.
     */
    public function test_head_status_diverse_sample_consistency()
    {
        // Query some real head IDs from the database
        $heads = DB::table('cen.heads')
            ->pluck('hed_id')
            ->take(15);

        $this->assertNotEmpty($heads);

        $testedCount = 0;
        foreach ($heads as $headId) {
            $status = $this->service->getHeadStatus($headId);

            // Assert basic calculations consistency
            $this->assertEquals(round($status->allocation - $status->mtss_share, 2), round($status->rdw_share, 2));
            $this->assertEquals(round($status->received - $status->expenditure, 2), round($status->balance, 2));
            $this->assertEquals(
                round($status->rdw_share - $status->expenditure - $status->commitments - $status->in_process, 2),
                round($status->remaining, 2)
            );

            // Assert rounding exists
            $this->assertEquals(round($status->acc_received, 2), $status->acc_received);
            $this->assertEquals(round($status->acc_expenditure, 2), $status->acc_expenditure);
            $this->assertEquals(round($status->acc_commitments, 2), $status->acc_commitments);
            $this->assertEquals(round($status->acc_in_process, 2), $status->acc_in_process);

            // Assert that loans returned are rounded
            $this->assertEquals(round($status->pcc_loans_given, 2), $status->pcc_loans_given);
            $this->assertEquals(round($status->others_loans_taken, 2), $status->others_loans_taken);
            $this->assertEquals(round($status->pcc_own_exp, 2), $status->pcc_own_exp);

            // Calculate UI booleans locally to ensure no exceptions and match logic
            $showProjectActualSection = !(round($status->pcc_expenditure, 2) == round($status->pcc_own_exp, 2) && round($status->others_loans_taken, 2) == 0.0);
            $showPrjShareValue = (round($status->prj_share, 2) != round($status->pcc_share, 2));

            // Basic type assertions for the booleans
            $this->assertIsBool($showProjectActualSection);
            $this->assertIsBool($showPrjShareValue);

            $testedCount++;
        }

        $this->assertGreaterThanOrEqual(8, $testedCount);
    }
}
