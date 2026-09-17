<?php

namespace Tests\Feature;

use App\Enums\RevType;
use App\Models\AudRev;
use App\Models\AudRevComp;
use App\Models\AudRevData;
use App\Models\CenAccount;
use App\Services\DataRevisionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataRevisionExecutionServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected DataRevisionService $service;
    protected int $unitId;
    protected int $headId;
    protected int $projectId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DataRevisionService::class);

        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();
        $prj  = DB::table('prj.projects')->first();

        $this->unitId = $unit->unt_id;
        $this->headId = $head->hed_id;
        $this->projectId = $prj->prj_id;

        $user = CenAccount::first();
        $this->actingAs($user);
    }

    public function test_execute_purchase_case_cascade_reversal()
    {
        // 1. Create Purchase Case (Approved with timestamps)
        $pcsId = DB::table('pur.purcases')->insertGetId([
            'pcs_date'       => now()->toDateString(),
            'pcs_title'      => 'Execution Test PC',
            'pcs_unt_id'     => $this->unitId,
            'pcs_intunt_id'  => $this->unitId,
            'pcs_status'     => 'Approved',
            'pcs_approvedtg' => now(),
            'pcs_closedtg'   => now(),
            'pcs_price'      => 50000,
            'pcs_type'       => 'Ps',
            'pcs_effhed_id'  => $this->headId,
            'pcs_effunt_id'  => $this->unitId,
            'pcs_hed_id'     => $this->headId,
            'pcs_minute'     => 1,
            'pcs_transtype'  => 1,
        ], 'pcs_id');

        // Subhead entry
        DB::table('pur.purcases_shd')->insert([
            'pcd_pcs_id'  => $pcsId,
            'pcd_subhead' => 'Equipment',
            'pcd_ratio'   => 1,
        ]);

        // Case item with fulfilment
        $pciId = DB::table('pur.purcaseitems')->insertGetId([
            'pci_pcs_id'     => $pcsId,
            'pci_serial'     => 1,
            'pci_desc'       => 'Items to reverse',
            'pci_qty'        => 5,
            'pci_qtyunit'    => 'Units',
            'pci_type'       => 1,
            'pci_subtype'    => 'General',
            'pci_fulfilment' => 5,
        ], 'pci_id');

        // Commitment and Transaction
        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => $pcsId,
            'cmt_type'      => 'Ps',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -50000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        $trnId = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => -50000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => 0,
            'trn_amount2'   => -50000,
            'trn_transtype' => 1,
        ], 'trn_id');

        // Receipt and Receipt Item
        $prtId = DB::table('pur.purreceipts')->insertGetId([
            'prt_pcs_id' => $pcsId,
            'prt_status' => 'Finalized',
            'prt_date'   => now()->toDateString(),
            'prt_unt_id' => $this->unitId,
            'prt_prj_id' => $this->headId,
        ], 'prt_id');

        $ptiId = DB::table('pur.purreceiptitems')->insertGetId([
            'pti_prt_id'  => $prtId,
            'pti_desc'    => 'Delivered',
            'pti_qty'     => 5,
            'pti_qtyunit' => 'Units',
            'pti_pci_id'  => $pciId,
            'pti_serial'  => 1,
        ], 'pti_id');

        // Attachment
        $patId = DB::table('pur.purattachments')->insertGetId([
            'pat_objtype' => 'pcs',
            'pat_objid'   => $pcsId,
            'pat_type'    => 'Invoice',
            'pat_path'    => '/pur/inv-exec.pdf',
        ], 'pat_id');

        // Create cascade revision
        $rev = $this->service->createDataRevision(
            'Purchase Case',
            $pcsId,
            $this->unitId,
            RevType::FULL_CASCADE
        );

        // Execute revision
        $executedRev = $this->service->executeDataRevision($rev);

        // Verify aud.revs updated
        $this->assertEquals('Fulfilled', $executedRev->rev_status);
        $this->assertNotNull($executedRev->rev_closedtg);

        // Verify pcs_rev: pur.purcases reset to 'Under Revision' and timestamps null
        $pc = DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
        $this->assertEquals('Under Revision', $pc->pcs_status);
        $this->assertNull($pc->pcs_approvedtg);
        $this->assertNull($pc->pcs_closedtg);

        // Verify subhead deleted
        $this->assertNull(DB::table('pur.purcases_shd')->where('pcd_pcs_id', $pcsId)->first());

        // Verify items fulfilment nulled
        $item = DB::table('pur.purcaseitems')->where('pci_id', $pciId)->first();
        $this->assertNull($item->pci_fulfilment);

        // Verify cmt_del and trn_del: deleted
        $this->assertNull(DB::table('fin.commitments')->where('cmt_id', $cmtId)->first());
        $this->assertNull(DB::table('fin.transactions')->where('trn_id', $trnId)->first());

        // Verify prt_del: receipt and receipt items deleted
        $this->assertNull(DB::table('pur.purreceipts')->where('prt_id', $prtId)->first());
        $this->assertNull(DB::table('pur.purreceiptitems')->where('pti_id', $ptiId)->first());

        // Verify pat_del: attachment deleted
        $this->assertNull(DB::table('pur.purattachments')->where('pat_id', $patId)->first());
    }

    public function test_execute_salary_order_cascade_reversal()
    {
        $emp = DB::table('hr.emps')->first();
        $empId = $emp ? $emp->emp_id : '99-99-99-9999';

        $srqId = DB::table('hr.salreqs')->insertGetId([
            'srq_emp_id'       => $empId,
            'srq_empnamecomp'  => 'Test Emp',
            'srq_unt_id'       => $this->unitId,
            'srq_effunt_id'    => $this->unitId,
            'srq_effhed_id'    => $this->headId,
            'srq_month'        => '2026-09-01',
            'srq_unpaiddays'   => 0,
            'srq_salary'       => 50000,
            'srq_status'       => 'Approved',
            'srq_closedtg'     => now(),
            'srq_fulfilment'   => 1,
            'srq_ctrsalary'    => 50000,
            'srq_grosalary'    => 50000,
            'srq_netsalary'    => 50000,
            'srq_bnkaccdetail' => '12345678',
            'srq_bnkacctitle'  => 'Test Emp',
            'srq_contracts'    => 'NK',
            'srq_checked'      => false,
            'srq_arrears'      => 0,
            'srq_dues'         => 0,
            'srq_overwork'     => 0,
            'srq_underwork'    => 0,
            'srq_loaned'       => 0,
            'srq_withheld'     => 0,
            'srq_award'        => 0,
            'srq_penalty'      => 0,
            'srq_paidalready'  => 0,
            'srq_paidholidays' => 0,
        ], 'srq_id');

        $sorId = DB::table('fin.salorders')->insertGetId([
            'sor_month'        => '2026-09-01',
            'sor_unt_id'       => $this->unitId,
            'sor_hed_id'       => $this->headId,
            'sor_effhed_id'    => $this->headId,
            'sor_effunt_id'    => $this->unitId,
            'sor_srq_id'       => $srqId,
            'sor_status'       => 'Approved',
            'sor_closedtg'     => now(),
            'sor_type'         => 'Sa',
            'sor_transtype'    => 1,
            'sor_netsalary'    => 50000,
            'sor_salary'       => 50000,
            'sor_emp_id'       => $empId,
            'sor_empnamecomp'  => 'Test Emp',
            'sor_bnkacctitle'  => 'Test Title',
            'sor_bnkaccdetail' => '12345678',
            'sor_ctrsalary'    => 0,
            'sor_checked'      => false,
            'sor_contracts'    => 'NK',
            'sor_noloan'       => false,
            'sor_grosalary'    => 50000,
            'sor_arrears'      => 0,
            'sor_dues'         => 0,
            'sor_overwork'     => 0,
            'sor_underwork'    => 0,
            'sor_loaned'       => 0,
            'sor_withheld'     => 0,
            'sor_award'        => 0,
            'sor_penalty'      => 0,
            'sor_paidalready'  => 0,
        ], 'sor_id');

        $rev = $this->service->createDataRevision(
            'Salary Order',
            $sorId,
            $this->unitId,
            RevType::FULL_CASCADE
        );

        $this->service->executeDataRevision($rev);

        // Verify sor_rev: fin.salorders status 'Under Revision', sor_closedtg null
        $sor = DB::table('fin.salorders')->where('sor_id', $sorId)->first();
        $this->assertEquals('Under Revision', $sor->sor_status);
        $this->assertNull($sor->sor_closedtg);

        // Verify srq_rev: hr.salreqs srq_fulfilment 0, status 'In Process', closedtg null
        $srq = DB::table('hr.salreqs')->where('srq_id', $srqId)->first();
        $this->assertEquals(0, $srq->srq_fulfilment);
        $this->assertEquals('In Process', $srq->srq_status);
        $this->assertNull($srq->srq_closedtg);
    }

    public function test_execute_commitment_status_reversal()
    {
        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => 777,
            'cmt_type'      => 'Pt',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -10000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        $rev = $this->service->createDataRevision(
            'Commitment',
            $cmtId,
            $this->unitId,
            RevType::FULL_CASCADE
        );

        $this->service->executeDataRevision($rev);

        // Verify cmt_rev resets cmt_status to 'Awaited'
        $cmt = DB::table('fin.commitments')->where('cmt_id', $cmtId)->first();
        $this->assertEquals('Awaited', $cmt->cmt_status);
    }

    public function test_execute_allocation_and_funding_cascade_deletions()
    {
        // 1. Transfers
        $trfId = DB::table('fin.transfers')->insertGetId([
            'trf_date'    => now()->toDateString(),
            'trf_type'    => 'Trf',
            'trf_title'   => 'Transfer Del Test',
            'trf_amount'  => 25000,
            'trf_fromhed' => $this->headId,
            'trf_fromunt' => $this->unitId,
            'trf_tohed'   => $this->headId,
            'trf_tount'   => $this->unitId,
            'trf_status'  => 'Approved',
        ], 'trf_id');

        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => $trfId,
            'cmt_type'      => 'Tr',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => 25000,
            'cmt_status'    => 'Awaited',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        // 2. SharesAlloc
        $shaId = DB::table('fin.sharesalloc')->insertGetId([
            'sha_hed_id'   => $this->headId,
            'sha_ficmt_id' => $cmtId,
        ], 'sha_id');

        $trnId = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => 25000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => 0,
            'trn_amount2'   => 25000,
            'trn_transtype' => 1,
        ], 'trn_id');

        // 3. SharesInstall
        $shiId = DB::table('fin.sharesinstall')->insertGetId([
            'shi_hed_id'   => $this->headId,
            'shi_fitrn_id' => $trnId,
        ], 'shi_id');

        // Create rev with components
        $rev = AudRev::create([
            'rev_date'      => now()->toDateString(),
            'rev_type'      => RevType::LINKED_CASCADE,
            'rev_intunt_id' => $this->unitId,
            'rev_obj'       => 'Allocation',
            'rev_objid'     => (string) $shaId,
            'rev_unt_id'    => $this->unitId,
            'rev_status'    => 'Draft',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'fin_sharesalloc',
            'rvc_rowid'  => (string) $shaId,
            'rvc_action' => 'alc_del',
            'rvc_type'   => 1,
            'rvc_detail' => 'alc_del test',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'fin_transfers',
            'rvc_rowid'  => (string) $trfId,
            'rvc_action' => 'trf_del',
            'rvc_type'   => 3,
            'rvc_detail' => 'trf_del test',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'fin_sharesinstall',
            'rvc_rowid'  => (string) $shiId,
            'rvc_action' => 'fnd_del',
            'rvc_type'   => 1,
            'rvc_detail' => 'fnd_del test',
        ]);

        $this->service->executeDataRevision($rev);

        $this->assertNull(DB::table('fin.sharesalloc')->where('sha_id', $shaId)->first());
        $this->assertNull(DB::table('fin.transfers')->where('trf_id', $trfId)->first());
        $this->assertNull(DB::table('fin.sharesinstall')->where('shi_id', $shiId)->first());
    }

    public function test_execute_contract_del_and_milestone_reversals()
    {
        $emp = DB::table('hr.emps')->first();
        $empId = $emp ? $emp->emp_id : '99-99-99-9999';

        $ctrId = DB::table('hr.contracts')->insertGetId([
            'ctr_unt_id'   => $this->unitId,
            'ctr_num'      => $empId,
            'ctr_startdt'  => now()->toDateString(),
            'ctr_enddt'    => now()->addYear()->toDateString(),
            'ctr_date'     => now()->toDateString(),
            'ctr_jobtitle' => 'Temporary Contractor',
            'ctr_grade'    => 'G-1',
            'ctr_salary'   => 45000,
            'ctr_type'     => 1,
        ], 'ctr_id');

        $msnIdd = DB::table('prj.milestones')->insertGetId([
            'msn_id'      => 88,
            'msn_xprj_id' => $this->projectId,
            'msn_type'    => 'Activity',
            'msn_desc'    => 'Activity Test Milestone',
            'msn_status'  => 'Completed',
            'msn_comp'    => 100,
        ], 'msn_idd');

        $rev = AudRev::create([
            'rev_date'      => now()->toDateString(),
            'rev_type'      => RevType::FULL_CASCADE,
            'rev_intunt_id' => $this->unitId,
            'rev_obj'       => 'Contract',
            'rev_objid'     => (string) $ctrId,
            'rev_unt_id'    => $this->unitId,
            'rev_status'    => 'Draft',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'hr_contracts',
            'rvc_rowid'  => (string) $ctrId,
            'rvc_action' => 'ctr_del',
            'rvc_type'   => 1,
            'rvc_detail' => 'ctr_del test',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'prj_milestones',
            'rvc_rowid'  => (string) $msnIdd,
            'rvc_action' => 'msn_idd',
            'rvc_type'   => 1,
            'rvc_detail' => 'msn_idd test',
        ]);

        $this->service->executeDataRevision($rev);

        $this->assertNull(DB::table('hr.contracts')->where('ctr_id', $ctrId)->first());

        $msn = DB::table('prj.milestones')->where('msn_idd', $msnIdd)->first();
        $this->assertEquals('In progress', $msn->msn_status);
        $this->assertEquals(50, $msn->msn_comp);
    }

    public function test_execute_unhandled_action_throws_logic_exception_and_rolls_back()
    {
        $rev = AudRev::create([
            'rev_date'      => now()->toDateString(),
            'rev_type'      => RevType::FULL_CASCADE,
            'rev_intunt_id' => $this->unitId,
            'rev_obj'       => 'Employee',
            'rev_objid'     => '99-99-99-9999',
            'rev_unt_id'    => $this->unitId,
            'rev_status'    => 'Draft',
        ]);

        AudRevComp::create([
            'rvc_rev_id' => $rev->rev_id,
            'rvc_table'  => 'hr_emps',
            'rvc_rowid'  => '99-99-99-9999',
            'rvc_action' => 'emp_rev',
            'rvc_type'   => 1,
            'rvc_detail' => 'emp_rev test',
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Action 'emp_rev' is not supported for execution in legacy reversal engine.");

        $this->service->executeDataRevision($rev);
    }

    public function test_execute_field_level_updates_with_conversions_and_suffix_stripping()
    {
        $emp = DB::table('hr.emps')->first();
        $empId = $emp ? $emp->emp_id : '99-99-99-9999';

        $ctrId = DB::table('hr.contracts')->insertGetId([
            'ctr_unt_id'   => $this->unitId,
            'ctr_num'      => $empId,
            'ctr_startdt'  => now()->toDateString(),
            'ctr_enddt'    => now()->addYear()->toDateString(),
            'ctr_date'     => now()->toDateString(),
            'ctr_jobtitle' => 'Junior Analyst',
            'ctr_grade'    => 'G-1',
            'ctr_salary'   => 40000,
            'ctr_remarks'  => 'Initial remarks',
            'ctr_type'     => 1,
        ], 'ctr_id');

        $prj = DB::table('prj.projects')->where('prj_id', $this->projectId)->first();
        $prjCode = $prj->prj_code ?? 'PRJ-100';

        $fieldDiffs = [
            // 1. Direct number update
            [
                'table'      => 'hr_contracts',
                'rowid'      => (string) $ctrId,
                'attrib'     => 'ctr_salary',
                'oldvalue'   => '40000',
                'newvalue'   => '55000',
                'datatype'   => 'Number',
                'colname'    => 'ctr_id',
            ],
            // 2. Direct text update
            [
                'table'      => 'hr_contracts',
                'rowid'      => (string) $ctrId,
                'attrib'     => 'ctr_jobtitle',
                'oldvalue'   => 'Junior Analyst',
                'newvalue'   => 'Lead Analyst',
                'datatype'   => 'Text',
                'colname'    => 'ctr_id',
            ],
            // 3. _x suffix stripping: ctr_remarks_x2 -> ctr_remarks
            [
                'table'      => 'hr_contracts',
                'rowid'      => (string) $ctrId,
                'attrib'     => 'ctr_remarks_x2',
                'oldvalue'   => 'Initial remarks',
                'newvalue'   => 'Updated via revision engine',
                'datatype'   => 'Text',
                'colname'    => 'ctr_id',
            ],
            // 4. Conversion 'i' with project code -> converts to prj_id
            [
                'table'      => 'hr_contracts',
                'rowid'      => (string) $ctrId,
                'attrib'     => 'ctr_hed_id',
                'oldvalue'   => null,
                'newvalue'   => $prjCode,
                'datatype'   => 'Number',
                'conversion' => 'i',
                'colname'    => 'ctr_id',
            ],
        ];

        $rev = $this->service->createDataRevision(
            'Contract',
            $ctrId,
            $this->unitId,
            RevType::FIELD_LEVEL,
            'FIELD-REV-1',
            null,
            null,
            'Contract details update',
            $fieldDiffs
        );

        $this->service->executeDataRevision($rev);

        $updatedCtr = DB::table('hr.contracts')->where('ctr_id', $ctrId)->first();
        $this->assertEquals(55000, $updatedCtr->ctr_salary);
        $this->assertEquals('Lead Analyst', $updatedCtr->ctr_jobtitle);
        $this->assertEquals('Updated via revision engine', $updatedCtr->ctr_remarks);
        $this->assertEquals($this->projectId, $updatedCtr->ctr_hed_id);
    }

    public function test_execute_field_level_rejects_unwhitelisted_table()
    {
        $rev = AudRev::create([
            'rev_date'      => now()->toDateString(),
            'rev_type'      => RevType::FIELD_LEVEL,
            'rev_intunt_id' => $this->unitId,
            'rev_obj'       => 'Security',
            'rev_objid'     => '1',
            'rev_unt_id'    => $this->unitId,
            'rev_status'    => 'Draft',
        ]);

        AudRevData::create([
            'rvd_rev_id'   => $rev->rev_id,
            'rvd_table'    => 'cen_accounts', // Not in allowedDataTables
            'rvd_rowid'    => '1',
            'rvd_attrib'   => 'acc_email',
            'rvd_oldvalue' => 'old@example.com',
            'rvd_newvalue' => 'new@example.com',
            'rvd_datatype' => 'Text',
            'rvd_type'     => 1,
            'rvd_colname'  => 'acc_id',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Table 'cen_accounts' is not permitted for field-level revision execution.");

        $this->service->executeDataRevision($rev);
    }

    public function test_cannot_re_execute_already_fulfilled_revision()
    {
        $rev = AudRev::create([
            'rev_date'      => now()->toDateString(),
            'rev_type'      => RevType::FIELD_LEVEL,
            'rev_intunt_id' => $this->unitId,
            'rev_obj'       => 'Contract',
            'rev_objid'     => '10',
            'rev_unt_id'    => $this->unitId,
            'rev_status'    => 'Fulfilled',
            'rev_closedtg'  => now(),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Data revision #{$rev->rev_id} is already fulfilled.");

        $this->service->executeDataRevision($rev);
    }

    public function test_apply_conversion_standalone_cases()
    {
        // 1. Negation ('n')
        $this->assertEquals(-5000, $this->service->applyConversion(5000, 'n'));
        $this->assertEquals(7500, $this->service->applyConversion(-7500, 'n'));

        // 2. GST transtype
        $this->assertEquals(1, $this->service->applyConversion('Without GST', 'i', 'pcs_transtype'));
        $this->assertEquals(2, $this->service->applyConversion('With GST', 'i', 'pcs_transtype'));

        // 3. Attendance code <-> label
        $this->assertEquals('Present', $this->service->applyConversion('P', 'i', 'att_1'));
        $this->assertEquals('P', $this->service->applyConversion('Present', 'i', 'att_1'));
        $this->assertEquals('Leave', $this->service->applyConversion('L', 'i', 'att_12'));
        $this->assertEquals('L', $this->service->applyConversion('Leave', 'i', 'att_12'));

        // 4. Project ID <-> Code
        $prj = DB::table('prj.projects')->where('prj_id', $this->projectId)->first();
        if ($prj && $prj->prj_code) {
            $this->assertEquals($prj->prj_code, $this->service->applyConversion($this->projectId, 'i', 'ctr_hed_id'));
            $this->assertEquals($this->projectId, $this->service->applyConversion($prj->prj_code, 'i', 'ctr_hed_id'));
        }

        // 5. Placeholder branches (numeric values preserved as-is per legacy dead code comment)
        $this->assertEquals(350010, $this->service->applyConversion(350010, 'i', 'pcs_hed_id'));
        $this->assertEquals(350000, $this->service->applyConversion(350000, 'i', 'emp_unt_id'));
    }
}
