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

class DataRevisionCreationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected DataRevisionService $service;
    protected int $unitId;
    protected int $headId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DataRevisionService::class);

        $unit = DB::table('cen.units')->first();
        $head = DB::table('cen.heads')->first();
        $this->unitId = $unit->unt_id;
        $this->headId = $head->hed_id;

        $user = CenAccount::first();
        $this->actingAs($user);
    }

    public function test_copy_row_data_produces_clean_comma_delimited_string()
    {
        $unit = DB::table('cen.units')->where('unt_id', $this->unitId)->first();
        $result = $this->service->copyRowData('cen_units', 'unt_id', $this->unitId);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString("unt_id: {$unit->unt_id}", $result);
        $this->assertStringContainsString("unt_name: {$unit->unt_name}", $result);
    }

    public function test_purchase_case_cascade_creation_bundles_items_and_quotes()
    {
        // 1. Create PurCase
        $pcsId = DB::table('pur.purcases')->insertGetId([
            'pcs_date'       => now()->toDateString(),
            'pcs_title'      => 'Cascade Test Case',
            'pcs_unt_id'     => $this->unitId,
            'pcs_intunt_id'  => $this->unitId,
            'pcs_status'     => 'Approved',
            'pcs_price'      => 75000,
            'pcs_type'       => 'Ps',
            'pcs_effhed_id'  => $this->headId,
            'pcs_effunt_id'  => $this->unitId,
            'pcs_hed_id'     => $this->headId,
            'pcs_minute'     => 1,
            'pcs_transtype'  => 1,
        ], 'pcs_id');

        // 2. Create PurCaseItem
        $pciId = DB::table('pur.purcaseitems')->insertGetId([
            'pci_pcs_id'  => $pcsId,
            'pci_serial'  => 1,
            'pci_desc'    => 'Server Rack',
            'pci_qty'     => 2,
            'pci_qtyunit' => 'Units',
            'pci_type'    => 1,
            'pci_subtype' => 'General',
        ], 'pci_id');

        // 3. Create Quote and QuoteItem
        $qteId = DB::table('pur.quotes')->insertGetId([
            'qte_pcs_id'   => $pcsId,
            'qte_firmname' => 'Tech Solutions',
            'qte_num'      => 'Q-100',
            'qte_frm_id'   => 1,
        ], 'qte_id');

        $qtiId = DB::table('pur.quoteitems')->insertGetId([
            'qti_qte_id'  => $qteId,
            'qti_serial'  => 1,
            'qti_desc'    => 'Server Rack Quote Item',
            'qti_price'   => 35000,
            'qti_qty'     => 2,
            'qti_qtyunit' => 'Units',
            'qti_pcsdesc' => 'Server Rack',
        ], 'qti_id');

        // 4. Create Commitment and Transaction
        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => $pcsId,
            'cmt_type'      => 'Ps',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -70000,
            'cmt_status'    => 'Awaited',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        $trnId = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => -70000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => 0,
            'trn_amount2'   => -70000,
            'trn_transtype' => 1,
        ], 'trn_id');

        // 5. Create Receipt and ReceiptItem
        $prtId = DB::table('pur.purreceipts')->insertGetId([
            'prt_pcs_id' => $pcsId,
            'prt_status' => 'Finalized',
            'prt_date'   => now()->toDateString(),
            'prt_unt_id' => $this->unitId,
            'prt_prj_id' => $this->headId,
        ], 'prt_id');

        $ptiId = DB::table('pur.purreceiptitems')->insertGetId([
            'pti_prt_id'  => $prtId,
            'pti_desc'    => 'Delivered Server Rack',
            'pti_qty'     => 2,
            'pti_qtyunit' => 'Units',
            'pti_pci_id'  => $pciId,
            'pti_serial'  => 1,
        ], 'pti_id');

        // 6. Create Attachment
        $patId = DB::table('pur.purattachments')->insertGetId([
            'pat_objtype' => 'pcs',
            'pat_objid'   => $pcsId,
            'pat_type'    => 'Invoice',
            'pat_path'    => '/pur/inv-100.pdf',
        ], 'pat_id');

        // Execute createDataRevision
        $rev = $this->service->createDataRevision(
            'Purchase Case',
            $pcsId,
            $this->unitId,
            RevType::FULL_CASCADE,
            'REF-123',
            null,
            null,
            'Cancellation due to vendor withdrawal'
        );

        $this->assertInstanceOf(AudRev::class, $rev);
        $this->assertEquals(RevType::FULL_CASCADE, $rev->rev_type);
        $this->assertEquals('Draft', $rev->rev_status);

        // Verify aud.revcomps created
        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(5, $comps);

        // Check pcs_rev bundled content
        $pcsComp = $comps->firstWhere('rvc_action', 'pcs_rev');
        $this->assertNotNull($pcsComp);
        $this->assertEquals('pur_purcases', $pcsComp->rvc_table);
        $this->assertEquals((string)$pcsId, $pcsComp->rvc_rowid);
        $this->assertEquals(1, $pcsComp->rvc_type);
        $this->assertStringContainsString('Purchase Case Items', $pcsComp->rvc_detail);
        $this->assertStringContainsString('Server Rack', $pcsComp->rvc_detail);
        $this->assertStringContainsString('Quotation', $pcsComp->rvc_detail);
        $this->assertStringContainsString('Tech Solutions', $pcsComp->rvc_detail);
        $this->assertStringContainsString('Quote Items', $pcsComp->rvc_detail);

        // Check cmt_del
        $cmtComp = $comps->firstWhere('rvc_action', 'cmt_del');
        $this->assertNotNull($cmtComp);
        $this->assertEquals('fin_commitments', $cmtComp->rvc_table);
        $this->assertEquals((string)$cmtId, $cmtComp->rvc_rowid);
        $this->assertEquals(2, $cmtComp->rvc_type);

        // Check trn_del
        $trnComp = $comps->firstWhere('rvc_action', 'trn_del');
        $this->assertNotNull($trnComp);
        $this->assertEquals('fin_transactions', $trnComp->rvc_table);
        $this->assertEquals((string)$trnId, $trnComp->rvc_rowid);
        $this->assertEquals(2, $trnComp->rvc_type);

        // Check prt_del
        $prtComp = $comps->firstWhere('rvc_action', 'prt_del');
        $this->assertNotNull($prtComp);
        $this->assertEquals('pur_purreceipts', $prtComp->rvc_table);
        $this->assertEquals((string)$prtId, $prtComp->rvc_rowid);
        $this->assertStringContainsString('Receipt Items', $prtComp->rvc_detail);

        // Check pat_del
        $patComp = $comps->firstWhere('rvc_action', 'pat_del');
        $this->assertNotNull($patComp);
        $this->assertEquals('pur_purattachments', $patComp->rvc_table);
        $this->assertEquals((string)$patId, $patComp->rvc_rowid);
    }

    public function test_salary_order_cascade_creation()
    {
        $emp = DB::table('hr.emps')->first();
        if (!$emp) {
            DB::table('hr.emps')->insert([
                'emp_id'     => '99-99-99-9999',
                'emp_cnic'   => '12345-6789012-3',
                'emp_name'   => 'Test Employee',
                'emp_joindt' => now()->toDateString(),
                'emp_status' => 'Active',
                'emp_unt_id' => $this->unitId,
            ]);
            $empId = '99-99-99-9999';
        } else {
            $empId = $emp->emp_id;
        }

        // 1. Create SalReq
        $srqId = DB::table('hr.salreqs')->insertGetId([
            'srq_emp_id'       => $empId,
            'srq_empnamecomp'  => 'Test Employee',
            'srq_unt_id'       => $this->unitId,
            'srq_effunt_id'    => $this->unitId,
            'srq_effhed_id'    => $this->headId,
            'srq_month'        => '2026-09-01',
            'srq_unpaiddays'   => 0,
            'srq_salary'       => 60000,
            'srq_status'       => 'Approved',
            'srq_ctrsalary'    => 60000,
            'srq_grosalary'    => 60000,
            'srq_netsalary'    => 60000,
            'srq_bnkaccdetail' => '12345678',
            'srq_bnkacctitle'  => 'Test Employee',
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

        // 2. Create SalOrder
        $sorId = DB::table('fin.salorders')->insertGetId([
            'sor_month'        => '2026-09-01',
            'sor_unt_id'       => $this->unitId,
            'sor_hed_id'       => $this->headId,
            'sor_effhed_id'    => $this->headId,
            'sor_effunt_id'    => $this->unitId,
            'sor_srq_id'       => $srqId,
            'sor_status'       => 'Pending',
            'sor_type'         => 'Sa',
            'sor_transtype'    => 1,
            'sor_netsalary'    => 60000,
            'sor_salary'       => 60000,
            'sor_emp_id'       => $empId,
            'sor_empnamecomp'  => 'Test Employee',
            'sor_bnkacctitle'  => 'Test Title',
            'sor_bnkaccdetail' => '12345678',
            'sor_ctrsalary'    => 0,
            'sor_checked'      => false,
            'sor_contracts'    => 'NK',
            'sor_noloan'       => false,
            'sor_grosalary'    => 60000,
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

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertTrue($comps->contains('rvc_action', 'sor_rev'));
        $this->assertTrue($comps->contains('rvc_action', 'srq_rev'));
    }

    public function test_allocation_cascade_creation()
    {
        // 1. Create Transfers
        $trf1 = DB::table('fin.transfers')->insertGetId([
            'trf_date'    => now()->toDateString(),
            'trf_type'    => 'Trf',
            'trf_title'   => 'Transfer Alloc In',
            'trf_amount'  => 50000,
            'trf_fromhed' => $this->headId,
            'trf_fromunt' => $this->unitId,
            'trf_tohed'   => $this->headId,
            'trf_tount'   => $this->unitId,
            'trf_status'  => 'Approved',
        ], 'trf_id');

        $trf2 = DB::table('fin.transfers')->insertGetId([
            'trf_date'    => now()->toDateString(),
            'trf_type'    => 'Trf',
            'trf_title'   => 'Transfer MTSS Out',
            'trf_amount'  => 50000,
            'trf_fromhed' => $this->headId,
            'trf_fromunt' => $this->unitId,
            'trf_tohed'   => $this->headId,
            'trf_tount'   => $this->unitId,
            'trf_status'  => 'Approved',
        ], 'trf_id');

        // 2. Create Commitments
        $cmt1 = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => $trf1,
            'cmt_type'      => 'Tr',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => 50000,
            'cmt_status'    => 'Awaited',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        $cmt2 = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => $trf2,
            'cmt_type'      => 'Tr',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -50000,
            'cmt_status'    => 'Awaited',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        // 3. Create SharesAlloc
        $shaId = DB::table('fin.sharesalloc')->insertGetId([
            'sha_hed_id'   => $this->headId,
            'sha_ficmt_id' => $cmt1,
            'sha_focmt_id' => $cmt2,
        ], 'sha_id');

        $rev = $this->service->createDataRevision(
            'Allocation',
            $shaId,
            $this->unitId,
            RevType::LINKED_CASCADE
        );

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(5, $comps);
        $this->assertTrue($comps->contains('rvc_action', 'alc_del'));
        $this->assertEquals(2, $comps->where('rvc_action', 'cmt_del')->count());
        $this->assertEquals(2, $comps->where('rvc_action', 'trf_del')->count());
    }

    public function test_funding_cascade_creation()
    {
        // 1. Create a parent commitment to satisfy foreign key
        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => 101,
            'cmt_type'      => 'Fn',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => 100000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        // 2. Create Transactions
        $trn1 = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => 100000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => 0,
            'trn_amount2'   => 100000,
            'trn_transtype' => 1,
        ], 'trn_id');

        $trn2 = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => -100000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => 0,
            'trn_amount2'   => -100000,
            'trn_transtype' => 1,
        ], 'trn_id');

        // 2. Create SharesInstall
        $shiId = DB::table('fin.sharesinstall')->insertGetId([
            'shi_hed_id'   => $this->headId,
            'shi_fitrn_id' => $trn1,
            'shi_fotrn_id' => $trn2,
        ], 'shi_id');

        $rev = $this->service->createDataRevision(
            'Funding',
            $shiId,
            $this->unitId,
            RevType::LINKED_CASCADE
        );

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(3, $comps);
        $this->assertTrue($comps->contains('rvc_action', 'fnd_del'));

        // Confirm legacy TableName for Payment MTSS is fin_commitments (Audit.bas:211)
        $mtssComp = $comps->where('rvc_action', 'trn_del')->where('rvc_table', 'fin_commitments')->first();
        $this->assertNotNull($mtssComp);
    }

    public function test_payment_cascade_creation()
    {
        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => 999,
            'cmt_type'      => 'Pt',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -5000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $this->headId,
            'cmt_effunt_id' => $this->unitId,
            'cmt_unt_id'    => $this->unitId,
        ], 'cmt_id');

        $trnId = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => -5000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => 0,
            'trn_amount2'   => -5000,
            'trn_transtype' => 1,
        ], 'trn_id');

        $rev = $this->service->createDataRevision(
            'Payment',
            $trnId,
            $this->unitId,
            RevType::LINKED_CASCADE
        );

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(2, $comps);
        $this->assertTrue($comps->contains('rvc_action', 'trn_del'));
        $this->assertTrue($comps->contains('rvc_action', 'cmt_rev'));
    }

    public function test_field_level_revision_creation_creates_aud_revdata()
    {
        $fieldDiffs = [
            [
                'table'      => 'hr_contracts',
                'rowid'      => '55',
                'attrib'     => 'ctr_salary',
                'oldvalue'   => '45000',
                'newvalue'   => '50000',
                'datatype'   => 'Number',
                'type'       => 1,
                'conversion' => null,
                'colname'    => 'ctr_salary',
                'alias'      => 'Contract Salary',
            ],
            [
                'table'      => 'hr_contracts',
                'rowid'      => '55',
                'attrib'     => 'ctr_jobtitle',
                'oldvalue'   => 'Software Engineer',
                'newvalue'   => 'Senior Software Engineer',
                'datatype'   => 'Text',
                'type'       => 1,
                'conversion' => null,
                'colname'    => 'ctr_jobtitle',
                'alias'      => 'Job Title',
            ],
        ];

        $rev = $this->service->createDataRevision(
            'Contract',
            55,
            $this->unitId,
            RevType::FIELD_LEVEL,
            'REV-CTR-55',
            null,
            null,
            'Salary and title correction',
            $fieldDiffs
        );

        $this->assertInstanceOf(AudRev::class, $rev);
        $this->assertEquals(RevType::FIELD_LEVEL, $rev->rev_type);

        $dataRows = AudRevData::where('rvd_rev_id', $rev->rev_id)->get();
        $this->assertCount(2, $dataRows);

        $salaryDiff = $dataRows->firstWhere('rvd_attrib', 'ctr_salary');
        $this->assertNotNull($salaryDiff);
        $this->assertEquals('45000', $salaryDiff->rvd_oldvalue);
        $this->assertEquals('50000', $salaryDiff->rvd_newvalue);
        $this->assertEquals('Number', $salaryDiff->rvd_datatype);
    }

    public function test_employee_cascade_creation()
    {
        $emp = DB::table('hr.emps')->first();
        $empId = $emp ? $emp->emp_id : '99-99-99-9999';

        $ctrId = DB::table('hr.contracts')->insertGetId([
            'ctr_unt_id'   => $this->unitId,
            'ctr_num'      => $empId,
            'ctr_startdt'  => now()->toDateString(),
            'ctr_enddt'    => now()->addYear()->toDateString(),
            'ctr_date'     => now()->toDateString(),
            'ctr_jobtitle' => 'Consultant',
            'ctr_grade'    => 'G-1',
            'ctr_salary'   => 80000,
            'ctr_type'     => 1,
        ], 'ctr_id');

        $rev = $this->service->createDataRevision(
            'Employee',
            $empId,
            $this->unitId,
            RevType::FULL_CASCADE
        );

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertTrue($comps->contains('rvc_action', 'emp_rev'));
        $this->assertTrue($comps->contains('rvc_action', 'ctr_rev'));

        // Verify emp_rev detail contains employee row data
        $empComp = $comps->firstWhere('rvc_action', 'emp_rev');
        $this->assertNotNull($empComp);
        $this->assertEquals((string) $empId, $empComp->rvc_rowid);
        $this->assertEquals('hr_emps', $empComp->rvc_table);
        $this->assertStringContainsString("emp_id: {$empId}", $empComp->rvc_detail);

        // Verify ctr_rev preserves legacy bug-for-bug parity (Audit.bas:228-229 passes emp_id to ctr_id)
        // Detail contains object header prefix with empty row data due to 22P02 type guard
        $ctrComp = $comps->firstWhere('rvc_action', 'ctr_rev');
        $this->assertNotNull($ctrComp);
        $this->assertEquals((string) $empId, $ctrComp->rvc_rowid);
        $this->assertEquals('hr_contracts', $ctrComp->rvc_table);
        $this->assertEquals("Employee\r\n", $ctrComp->rvc_detail);
    }

    public function test_contract_cascade_creation()
    {
        $emp = DB::table('hr.emps')->first();
        $empId = $emp ? $emp->emp_id : '99-99-99-9999';

        $ctrId = DB::table('hr.contracts')->insertGetId([
            'ctr_unt_id'   => $this->unitId,
            'ctr_num'      => $empId,
            'ctr_startdt'  => now()->toDateString(),
            'ctr_enddt'    => now()->addYear()->toDateString(),
            'ctr_date'     => now()->toDateString(),
            'ctr_jobtitle' => 'Advisor',
            'ctr_grade'    => 'G-2',
            'ctr_salary'   => 90000,
            'ctr_type'     => 1,
        ], 'ctr_id');

        $rev = $this->service->createDataRevision(
            'Contract',
            $ctrId,
            $this->unitId,
            RevType::FULL_CASCADE
        );

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(1, $comps);
        $this->assertEquals('ctr_del', $comps->first()->rvc_action);
        $this->assertEquals('hr_contracts', $comps->first()->rvc_table);
    }

    public function test_commitment_cascade_creation()
    {
        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => 555,
            'cmt_type'      => 'Pt',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -12000,
            'cmt_status'    => 'Awaited',
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

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(1, $comps);
        $this->assertEquals('cmt_rev', $comps->first()->rvc_action);
        $this->assertEquals('fin_commitments', $comps->first()->rvc_table);
    }

    public function test_task_cascade_creation()
    {
        $prj = DB::table('prj.projects')->first();
        $prjId = $prj ? $prj->prj_id : 1;

        $msnIdd = DB::table('prj.milestones')->insertGetId([
            'msn_id'      => 99,
            'msn_xprj_id' => $prjId,
            'msn_type'    => 'Activity',
            'msn_desc'    => 'Phase Milestone Test',
            'msn_status'  => 'Completed',
        ], 'msn_idd');

        $rev = $this->service->createDataRevision(
            'Task',
            $msnIdd,
            $this->unitId,
            RevType::FULL_CASCADE
        );

        $comps = AudRevComp::where('rvc_rev_id', $rev->rev_id)->get();
        $this->assertCount(1, $comps);
        $this->assertEquals('msn_idd', $comps->first()->rvc_action);
        $this->assertEquals('prj_milestones', $comps->first()->rvc_table);
    }
}
