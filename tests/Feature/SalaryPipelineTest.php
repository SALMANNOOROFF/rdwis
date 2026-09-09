<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\FinCommitment;
use App\Models\FinSalOrder;
use App\Models\FinSalOrderShd;
use App\Models\HrSalReq;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\FinancialIntelligenceService;
use App\Services\SalaryGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalaryPipelineTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected SalaryGenerationService $salaryService;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salaryService = app(SalaryGenerationService::class);

        // Find test finance user
        $this->adminUser = CenAccount::where('acc_untarea', 'ILIKE', 'fin')->first()
            ?? CenAccount::where('acc_username', 'superadminrdw')->first()
            ?? CenAccount::first();
    }

    /**
     * Helper to create a valid HrSalReq for test setup.
     */
    protected function createRequisition(array $overrides = []): HrSalReq
    {
        return HrSalReq::create(array_merge([
            'srq_emp_id'       => 'T-EMP-1',
            'srq_empnamecomp'  => 'Test Employee',
            'srq_unt_id'       => 350000,
            'srq_effunt_id'    => 350000,
            'srq_effhed_id'    => 350000,
            'srq_month'        => '2024-01-31',
            'srq_unpaiddays'   => 0,
            'srq_paidholidays' => 0,
            'srq_salary'       => 100000,
            'srq_status'       => 'Draft',
            'srq_ctrsalary'    => 100000,
            'srq_grosalary'    => 100000,
            'srq_netsalary'    => 100000,
            'srq_bnkaccdetail' => '(Pay by Cheque)',
            'srq_bnkacctitle'  => 'TEST EMPLOYEE',
            'srq_contracts'    => '1',
            'srq_arrears'      => 0,
            'srq_dues'         => 0,
            'srq_overwork'     => 0,
            'srq_underwork'    => 0,
            'srq_loaned'       => 0,
            'srq_withheld'     => 0,
            'srq_award'        => 0,
            'srq_penalty'      => 0,
            'srq_paidalready'  => 0,
        ], $overrides));
    }

    /**
     * Helper to create a valid FinSalOrder for test setup.
     */
    protected function createSalaryOrder(array $overrides = []): FinSalOrder
    {
        return FinSalOrder::create(array_merge([
            'sor_srq_id'       => 1,
            'sor_type'         => 'Sa',
            'sor_emp_id'       => 'T-EMP-1',
            'sor_empnamecomp'  => 'Test Employee',
            'sor_hed_id'       => null,
            'sor_unt_id'       => 350000,
            'sor_effhed_id'    => 350000,
            'sor_effunt_id'    => 350000,
            'sor_month'        => '2024-01-31',
            'sor_ctrsalary'    => 100000,
            'sor_netsalary'    => 95000,
            'sor_salary'       => 95000,
            'sor_bnkacctitle'  => 'TEST EMPLOYEE',
            'sor_bnkaccdetail' => '(Pay by Cheque)',
            'sor_contracts'    => '1',
            'sor_status'       => 'Approved',
            'sor_transtype'    => 1,
            'sor_grosalary'    => 100000,
            'sor_arrears'      => 0,
            'sor_dues'         => 0,
            'sor_overwork'     => 0,
            'sor_underwork'    => 0,
            'sor_loaned'       => 0,
            'sor_withheld'     => 0,
            'sor_award'        => 0,
            'sor_penalty'      => 0,
            'sor_paidalready'  => 0,
        ], $overrides));
    }

    /**
     * Helper to create an employee with active contract and verified state.
     */
    protected function createEmployeeWithContract(array $empOverrides = [], array $ctrOverrides = []): array
    {
        $empId = 'T' . substr(uniqid(), -11);
        $unitId = 350000;
        $headId = DB::table('cen.heads')->where('hed_unt_id', $unitId)->value('hed_id')
               ?? DB::table('cen.heads')->value('hed_id');

        DB::table('hr.emps')->insert(array_merge([
            'emp_id'        => $empId,
            'emp_cnic'      => '35201-' . rand(1000000, 9999999) . '-1',
            'emp_name'      => 'Test Pipeline Emp',
            'emp_unt_id'    => $unitId,
            'emp_status'    => 'Active',
            'emp_joindt'    => '2023-01-01',
            'emp_hed_id'    => null, // Central employee by default
        ], $empOverrides));

        $ctrId = DB::table('hr.contracts')->insertGetId(array_merge([
            'ctr_num'        => $empId,
            'ctr_startdt'    => '2023-01-01',
            'ctr_enddt'      => '2025-12-31',
            'ctr_date'       => '2023-01-01',
            'ctr_salary'     => 100000,
            'ctr_unt_id'     => $unitId,
            'ctr_hed_id'     => $headId,
            'ctr_jobtitle'   => 'Software Engineer',
            'ctr_grade'      => 'A',
            'ctr_type'       => 1,
            'ctr_prob'       => 0,
            'ctr_probsal'    => 100000,
        ], $ctrOverrides), 'ctr_id');

        // Contract plan in hr.contractplans
        DB::table('hr.contractplans')->insert([
            'cpn_ctr_id'  => $ctrId,
            'cpn_startdt' => '2023-01-01',
            'cpn_enddt'   => '2025-12-31',
            'cpn_hed_id'  => $headId,
        ]);

        // Verification in fin.contractsverif
        DB::table('fin.contractsverif')->insert([
            'cvf_ctr_id' => $ctrId,
            'cvf_verif'  => true,
        ]);

        // Effective head in fin.empeffheads
        DB::table('fin.empeffheads')->insert([
            'eeh_emp_id'    => $empId,
            'eeh_emphed_id' => $headId,
            'eeh_status'    => 'Open',
        ]);

        return [$empId, $ctrId, $unitId, $headId];
    }

    /**
     * 1. Test Future Month Guard: Previewing future month excludes employee with 'Future Month'.
     */
    public function test_preview_salary_future_month_guard(): void
    {
        [$empId] = $this->createEmployeeWithContract();
        $futureMonth = Carbon::now()->addMonths(2)->format('Y-m-d');

        $preview = $this->salaryService->previewSalary($futureMonth, 350000, $this->adminUser);

        $this->assertNotEmpty($preview['excluded']);
        $excludedEmp = collect($preview['excluded'])->firstWhere('employee.emp_id', $empId);
        $this->assertNotNull($excludedEmp);
        $this->assertEquals('Future Month', $excludedEmp['reason']);
    }

    /**
     * 2. Test Already Generated Duplicate Guard: Catches Draft and In Process (and ignores Cancelled).
     */
    public function test_preview_salary_already_generated_guard(): void
    {
        [$empDraft, $ctr1, $unit1, $head1] = $this->createEmployeeWithContract();
        [$empInProcess, $ctr2, $unit2, $head2] = $this->createEmployeeWithContract();
        [$empCancelled, $ctr3, $unit3, $head3] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        // 1. Employee with 'Draft' requisition
        $this->createRequisition([
            'srq_emp_id'    => $empDraft,
            'srq_unt_id'    => $unit1,
            'srq_effhed_id' => $head1,
            'srq_effunt_id' => $unit1,
            'srq_month'     => $salMonth,
            'srq_status'    => 'Draft',
            'srq_contracts' => (string)$ctr1,
        ]);

        // 2. Employee with 'In Process' requisition (Released)
        $this->createRequisition([
            'srq_emp_id'    => $empInProcess,
            'srq_unt_id'    => $unit2,
            'srq_effhed_id' => $head2,
            'srq_effunt_id' => $unit2,
            'srq_month'     => $salMonth,
            'srq_status'    => 'In Process',
            'srq_contracts' => (string)$ctr2,
        ]);

        // 3. Employee with 'Cancelled' requisition (Must NOT be excluded)
        $this->createRequisition([
            'srq_emp_id'    => $empCancelled,
            'srq_unt_id'    => $unit3,
            'srq_effhed_id' => $head3,
            'srq_effunt_id' => $unit3,
            'srq_month'     => $salMonth,
            'srq_status'    => 'Cancelled',
            'srq_contracts' => (string)$ctr3,
        ]);

        $preview = $this->salaryService->previewSalary($salMonth, $unit1, $this->adminUser);

        // Draft employee is excluded
        $exDraft = collect($preview['excluded'])->firstWhere('employee.emp_id', $empDraft);
        $this->assertNotNull($exDraft);
        $this->assertEquals('Already Generated', $exDraft['reason']);

        // In Process employee is excluded
        $exInProcess = collect($preview['excluded'])->firstWhere('employee.emp_id', $empInProcess);
        $this->assertNotNull($exInProcess);
        $this->assertEquals('Already Generated', $exInProcess['reason']);

        // Cancelled employee is NOT excluded (candidate for new requisition)
        $exCancelled = collect($preview['excluded'])->firstWhere('employee.emp_id', $empCancelled);
        $this->assertNull($exCancelled, 'Cancelled requisition must not block preview/generation');
    }

    /**
     * 3. Test No Contract/Plan Guard.
     */
    public function test_preview_salary_no_contract_plan_guard(): void
    {
        $empId = 'T-NC-' . substr(uniqid(), -7);
        DB::table('hr.emps')->insert([
            'emp_id'        => $empId,
            'emp_cnic'      => '35201-' . rand(1000000, 9999999) . '-1',
            'emp_name'      => 'Emp Without Contract',
            'emp_unt_id'    => 350000,
            'emp_status'    => 'Active',
            'emp_joindt'    => '2023-01-01',
        ]);

        $salMonth = '2024-01-31';
        $preview = $this->salaryService->previewSalary($salMonth, 350000, $this->adminUser);

        $excludedEmp = collect($preview['excluded'])->firstWhere('employee.emp_id', $empId);
        $this->assertNotNull($excludedEmp);
        $this->assertEquals('No Contract/Plan', $excludedEmp['reason']);
    }

    /**
     * 4. Test Contract Not Verified Guard.
     */
    public function test_preview_salary_contract_not_verified_guard(): void
    {
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract();

        // Mark verification as false
        DB::table('fin.contractsverif')->where('cvf_ctr_id', $ctrId)->update(['cvf_verif' => false]);

        $salMonth = '2024-01-31';
        $preview = $this->salaryService->previewSalary($salMonth, $unitId, $this->adminUser);

        $excludedEmp = collect($preview['excluded'])->firstWhere('employee.emp_id', $empId);
        $this->assertNotNull($excludedEmp);
        $this->assertEquals('Contract Not Verified', $excludedEmp['reason']);
    }

    /**
     * 5. Test Multiple Bank Accounts Guard.
     */
    public function test_preview_salary_multiple_bank_accounts_guard(): void
    {
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract();

        // Insert 2 accounts marked bac_selforpay = true
        DB::table('hr.bnkaccounts')->insert([
            [
                'bac_emp_id'     => $empId,
                'bac_bnkname'    => 'Meezan Bank Ltd',
                'bac_bchname'    => 'Main Branch',
                'bac_bchcity'    => 'Islamabad',
                'bac_bchcode'    => '0101',
                'bac_accnum'     => '1234567890',
                'bac_acctitle'   => 'Test Emp Account 1',
                'bac_selforpay'  => true,
            ],
            [
                'bac_emp_id'     => $empId,
                'bac_bnkname'    => 'Habib Bank Ltd',
                'bac_bchname'    => 'Main Branch',
                'bac_bchcity'    => 'Islamabad',
                'bac_bchcode'    => '0202',
                'bac_accnum'     => '9876543210',
                'bac_acctitle'   => 'Test Emp Account 2',
                'bac_selforpay'  => true,
            ]
        ]);

        $salMonth = '2024-01-31';
        $preview = $this->salaryService->previewSalary($salMonth, $unitId, $this->adminUser);

        $excludedEmp = collect($preview['excluded'])->firstWhere('employee.emp_id', $empId);
        $this->assertNotNull($excludedEmp);
        $this->assertEquals('Multiple Bank Accounts', $excludedEmp['reason']);
    }

    /**
     * 6. Test Meezan Bank Exact Match vs Cheque Fallback.
     */
    public function test_meezan_bank_exact_match_rule(): void
    {
        [$empId1] = $this->createEmployeeWithContract();
        [$empId2] = $this->createEmployeeWithContract();

        // Emp 1: Exact "Meezan Bank Ltd"
        DB::table('hr.bnkaccounts')->insert([
            'bac_emp_id'     => $empId1,
            'bac_bnkname'    => 'Meezan Bank Ltd',
            'bac_bchname'    => 'Main Branch',
            'bac_bchcity'    => 'Islamabad',
            'bac_bchcode'    => '0107',
            'bac_accnum'     => '01070100290194',
            'bac_acctitle'   => 'EXACT MEEZAN TITLE',
            'bac_selforpay'  => true,
        ]);

        // Emp 2: Variation "Meezan Bank Limited" (Falls back to cheque in legacy exact match)
        DB::table('hr.bnkaccounts')->insert([
            'bac_emp_id'     => $empId2,
            'bac_bnkname'    => 'Meezan Bank Limited',
            'bac_bchname'    => 'Main Branch',
            'bac_bchcity'    => 'Islamabad',
            'bac_bchcode'    => '0107',
            'bac_accnum'     => '01070100290195',
            'bac_acctitle'   => 'LIMITED MEEZAN TITLE',
            'bac_selforpay'  => true,
        ]);

        $salMonth = '2024-01-31';
        $preview = $this->salaryService->previewSalary($salMonth, 350000, $this->adminUser);

        $cand1 = collect($preview['included'])->firstWhere('employee.emp_id', $empId1);
        $cand2 = collect($preview['included'])->firstWhere('employee.emp_id', $empId2);

        $this->assertNotNull($cand1);
        $this->assertNotNull($cand2);

        // Cand 1 gets Meezan branch formatting
        $this->assertEquals('01070100290194 (0107)', $cand1['breakdown'][0]['bnkaccdetail']);
        $this->assertEquals('EXACT MEEZAN TITLE', $cand1['breakdown'][0]['bnkacctitle']);

        // Cand 2 falls back to (Pay by Cheque)
        $this->assertEquals('(Pay by Cheque)', $cand2['breakdown'][0]['bnkaccdetail']);
        $this->assertEquals('LIMITED MEEZAN TITLE', $cand2['breakdown'][0]['bnkacctitle']);
    }

    /**
     * 7. Test Full Pipeline: Central Employee (Requisition -> Release -> Order -> Approve -> Commitment).
     */
    public function test_full_pipeline_central_employee(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        // Stage 1: Generate Requisitions
        $genResult = $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $this->assertEquals(1, $genResult['generated']);

        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();
        $this->assertNotNull($req);
        $this->assertEquals('Draft', $req->srq_status);
        $this->assertNull($req->srq_parent);
        $this->assertNull($req->srq_fulfilment);

        // Stage 2: Release Requisition -> In Process
        $releasedCount = $this->salaryService->releaseRequisitions($req->srq_id);
        $this->assertEquals(1, $releasedCount);
        $req->refresh();
        $this->assertEquals('In Process', $req->srq_status);
        $this->assertNotNull($req->srq_releasedtg);

        // Stage 3: Create Salary Order -> Draft Order & srq_fulfilment = 0
        $orders = $this->salaryService->createSalaryOrders($req->srq_id);
        $this->assertCount(1, $orders);
        $order = $orders[0];
        $this->assertEquals('Draft', $order->sor_status);
        $this->assertTrue((bool)$order->sor_noloan); // Central employee has sor_noloan = true
        $req->refresh();
        $this->assertEquals(0, $req->srq_fulfilment); // Marked with 0

        // Central employee must NOT have fin.salorders_shd row
        $shdCount = FinSalOrderShd::where('sod_sor_id', $order->sor_id)->count();
        $this->assertEquals(0, $shdCount);

        // Stage 4: Approve Order -> Creates fin.commitments row (cmt_type = Sa, status = Awaited, negative amount)
        $approveResult = $this->salaryService->approveSalaryOrders($order->sor_id, $this->adminUser);
        $order->refresh();
        $this->assertEquals('Approved', $order->sor_status);

        $commitment = FinCommitment::where('cmt_docid', $order->sor_id)
            ->where('cmt_type', 'Sa')
            ->first();
        $this->assertNotNull($commitment);
        $this->assertEquals('Awaited', $commitment->cmt_status);
        $this->assertLessThan(0, $commitment->cmt_amount); // Strictly negative amount
        $this->assertEquals(-1 * abs($order->sor_salary), (float)$commitment->cmt_amount);
    }

    /**
     * 8. Test Full Pipeline: Project Unit Employee (Generates fin.salorders_shd row).
     */
    public function test_full_pipeline_project_unit_employee(): void
    {
        $projectHeadId = DB::table('cen.heads')->value('hed_id');
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract([
            'emp_hed_id' => $projectHeadId, // Project employee has emp_hed_id set
        ]);
        $salMonth = '2024-01-31';

        $genResult = $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();

        // Release and Create Order
        $this->salaryService->releaseRequisitions($req->srq_id);
        $orders = $this->salaryService->createSalaryOrders($req->srq_id);
        $order = $orders[0];

        $this->assertFalse((bool)$order->sor_noloan); // Project employee has sor_noloan = false

        // Project employee MUST have fin.salorders_shd row (subhead HR, ratio 1.0, type Sa)
        $shd = FinSalOrderShd::where('sod_sor_id', $order->sor_id)->first();
        $this->assertNotNull($shd);
        $this->assertEquals('HR', $shd->sod_subhead);
        $this->assertEquals(1.0, (float)$shd->sod_ratio);
        $this->assertEquals('Sa', $shd->sod_type);
    }

    /**
     * 9. Test Requisition and Draft Order Cancellation.
     */
    public function test_cancellation_pipeline(): void
    {
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();

        // Create order
        $orders = $this->salaryService->createSalaryOrders($req->srq_id);
        $order = $orders[0];
        $this->assertEquals('Draft', $order->sor_status);

        // Cancel order -> sets sor_status = Cancelled and resets srq_fulfilment = null
        $this->salaryService->cancelOrder($order->sor_id);
        $order->refresh();
        $req->refresh();

        $this->assertEquals('Cancelled', $order->sor_status);
        $this->assertNotNull($order->sor_closedtg);
        $this->assertNull($req->srq_fulfilment);

        // Cancel requisition directly
        $this->salaryService->cancelRequisition($req->srq_id);
        $req->refresh();
        $this->assertEquals('Cancelled', $req->srq_status);
        $this->assertNotNull($req->srq_closedtg);
    }

    /**
     * 10. DELIBERATE ENHANCEMENT TEST:
     * test_cancel_approved_order_is_new_capability_not_in_legacy
     *
     * In legacy MS Access, CancelSalOrderGroup never cancelled fin.commitments, leaving
     * dangling Awaited commitments. In Laravel RDWIS 2.0, cancelOrder() safely cancels
     * active Awaited commitments upon order cancellation.
     */
    public function test_cancel_approved_order_is_new_capability_not_in_legacy(): void
    {
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();
        $this->salaryService->releaseRequisitions($req->srq_id);
        $orders = $this->salaryService->createSalaryOrders($req->srq_id);
        $order = $orders[0];

        // Approve order -> commitment created with cmt_status = 'Awaited'
        $this->salaryService->approveSalaryOrders($order->sor_id, $this->adminUser);
        $commitment = FinCommitment::where('cmt_docid', $order->sor_id)->where('cmt_type', 'Sa')->first();
        $this->assertEquals('Awaited', $commitment->cmt_status);

        // Cancel Approved order
        $cancelResult = $this->salaryService->cancelOrder($order->sor_id);
        $order->refresh();
        $commitment->refresh();

        $this->assertEquals('Cancelled', $order->sor_status);
        $this->assertEquals('Cancelled', $commitment->cmt_status);
        $this->assertCount(1, $cancelResult['cancelled_commitments']);
        $this->assertEquals($commitment->cmt_id, $cancelResult['cancelled_commitments'][0]->cmt_id);
    }

    /**
     * 11. Test VerifySalaryCommitmentsCommand in Dry-Run and Fix mode.
     */
    public function test_verify_salary_commitments_command(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();

        // Insert requisition first
        $srq = $this->createRequisition([
            'srq_emp_id'       => $empId,
            'srq_unt_id'       => $unitId,
            'srq_effhed_id'    => $headId,
            'srq_effunt_id'    => $unitId,
            'srq_month'        => '2024-02-28',
            'srq_status'       => 'In Process',
            'srq_salary'       => 95000,
            'srq_empnamecomp'  => 'Orphan Test Order',
            'srq_contracts'    => (string)$ctrId,
        ]);

        // Insert orphan Approved order with no commitment
        $order = $this->createSalaryOrder([
            'sor_srq_id'      => $srq->srq_id,
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Orphan Test Order',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => '2024-02-28',
            'sor_salary'      => 95000,
            'sor_status'      => 'Approved',
            'sor_type'        => 'Sa',
        ]);
        $sorId = $order->sor_id;

        // Step 1: Dry-run mode does NOT insert commitment
        Artisan::call('salary:verify-commitments', ['--dry-run' => true]);
        $exists = FinCommitment::where('cmt_docid', $sorId)->where('cmt_type', 'Sa')->exists();
        $this->assertFalse($exists, 'Dry run must not insert commitments');

        // Step 2: Fix mode inserts missing commitment
        Artisan::call('salary:verify-commitments', ['--fix' => true]);
        $commitment = FinCommitment::where('cmt_docid', $sorId)->where('cmt_type', 'Sa')->first();
        $this->assertNotNull($commitment, 'Fix mode must insert missing commitment');
        $this->assertEquals('Awaited', $commitment->cmt_status);
        $this->assertEquals(-95000, (float)$commitment->cmt_amount);
    }

    /**
     * 12. Test PaymentController payment settlement for salary commitment (fin_commitments_u_so.bas).
     */
    public function test_payment_controller_settles_salary_commitment(): void
    {
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();
        $this->salaryService->releaseRequisitions($req->srq_id);
        $orders = $this->salaryService->createSalaryOrders($req->srq_id);
        $order = $orders[0];
        $this->salaryService->approveSalaryOrders($order->sor_id, $this->adminUser);

        $commitment = FinCommitment::where('cmt_docid', $order->sor_id)->where('cmt_type', 'Sa')->first();

        // Settle salary commitment via PaymentController
        $response = $this->actingAs($this->adminUser)->post(
            route('fin.payments.store_transaction', $commitment->cmt_id),
            [
                'trn_date'    => now()->toDateString(),
                'amount'      => (float)$order->sor_salary,
                'tax'         => 0,
                'is_complete' => true,
            ]
        );

        $response->assertSessionHasNoErrors();
        $commitment->refresh();
        $order->refresh();
        $req->refresh();

        // Commitment, order, and requisition are all Fulfilled/Paid
        $this->assertEquals('Paid', $commitment->cmt_status);
        $this->assertEquals('Fulfilled', $order->sor_status);
        $this->assertEquals('Fulfilled', $req->srq_status);
        $this->assertEquals($order->sor_salary, $req->srq_fulfilment);
    }

    /**
     * 13. Test releaseRequisitions() explicitly sets srq_status = 'In Process' (Salary.bas:14).
     */
    public function test_release_requisitions_sets_exact_in_process_status(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();
        $this->assertSame('Draft', $req->srq_status);

        $releasedCount = $this->salaryService->releaseRequisitions($req->srq_id);
        $this->assertEquals(1, $releasedCount);

        $req->refresh();
        // Explicit regression assert for exact legacy string 'In Process'
        $this->assertSame('In Process', $req->srq_status);
        $this->assertNotNull($req->srq_releasedtg);
    }

    /**
     * 14. FIX 1: Test cancelling a Fulfilled or Paid salary order is rejected with HTTP 422.
     * Also verifies cancelRequisition guard on Fulfilled requisitions and requisitions with approved orders.
     */
    public function test_cancel_fulfilled_or_paid_order_is_rejected(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $this->salaryService->generateSalary($salMonth, [$empId], $this->adminUser);
        $req = HrSalReq::where('srq_emp_id', $empId)->where('srq_month', $salMonth)->first();
        $this->salaryService->releaseRequisitions($req->srq_id);
        $orders = $this->salaryService->createSalaryOrders($req->srq_id);
        $order = $orders[0];

        $this->salaryService->approveSalaryOrders($order->sor_id, $this->adminUser);
        $order->refresh();
        $commitment = FinCommitment::where('cmt_docid', $order->sor_id)->where('cmt_type', 'Sa')->first();

        // 1. Mark commitment as Paid and order as Fulfilled
        $order->sor_status = 'Fulfilled';
        $order->save();
        $commitment->cmt_status = 'Paid';
        $commitment->save();
        $req->srq_fulfilment = $order->sor_salary;
        $req->save();

        // A. Attempt to cancel via HTTP POST -> must return 422
        $httpResponse = $this->actingAs($this->adminUser)->post(route('divhr.salary.orders.cancel', $order->sor_id), [
            'reason' => 'Unauthorized attempt to cancel fulfilled order',
        ]);
        $httpResponse->assertStatus(422);

        // Verify state is untouched
        $order->refresh();
        $commitment->refresh();
        $req->refresh();
        $this->assertSame('Fulfilled', $order->sor_status, 'Order status must remain Fulfilled');
        $this->assertSame('Paid', $commitment->cmt_status, 'Commitment status must remain Paid');
        $this->assertEquals($order->sor_salary, $req->srq_fulfilment, 'srq_fulfilment must not be nulled');

        // B. Attempt to cancel via Service directly -> must throw HttpException 422
        try {
            $this->salaryService->cancelOrder($order->sor_id);
            $this->fail('Expected HttpException 422 was not thrown when cancelling fulfilled order');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Cannot cancel a fulfilled salary order', $e->getMessage());
        }

        // C. Test requisition cancellation guard on Fulfilled requisition
        $req->srq_status = 'Fulfilled';
        $req->save();

        $reqCancelResponse = $this->actingAs($this->adminUser)->post(route('divhr.salary.requisitions.cancel', $req->srq_id), [
            'reason' => 'Unauthorized attempt to cancel fulfilled requisition',
        ]);
        $reqCancelResponse->assertStatus(422);

        $req->refresh();
        $this->assertSame('Fulfilled', $req->srq_status);

        // D. Test requisition cancellation guard when linked order is Approved
        $order->sor_status = 'Approved';
        $order->save();
        $req->srq_status = 'In Process';
        $req->save();

        try {
            $this->salaryService->cancelRequisition($req->srq_id);
            $this->fail('Expected HttpException 422 was not thrown when cancelling requisition with approved order');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Cannot cancel requisition with approved or fulfilled salary orders', $e->getMessage());
        }
    }

    /**
     * 15. FIX 2: Commitments Hub preserves type=salary across tab and filter interactions.
     */
    public function test_commitments_index_preserves_salary_type_parameter(): void
    {
        // Finance user requests Closed tab with type=salary
        $response = $this->actingAs($this->adminUser)->get(route('fin.payments.index', [
            'type' => 'salary',
            'tab'  => 'Closed',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('finance.payments.index');
        $response->assertViewHas('typeFilter', 'salary');
        $response->assertViewHas('tab', 'Closed');

        // Assert dynamic heading
        $response->assertSee('Salary Order Commitments');
        $response->assertDontSee('Purchase Case Commitments');

        // Assert hidden input in filter form
        $response->assertSee('<input type="hidden" name="type" value="salary">', false);

        // Assert Open tab link preserves type=salary
        $response->assertSee('tab=Open', false);
        $response->assertSee('type=salary', false);
    }

    /**
     * 16. FIX ITEM 4: Action on child order processes entire parent-child group (true bidirectional cascade).
     */
    public function test_action_on_child_order_processes_entire_parent_child_group(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        // 1. Requisitions parent-child bidirectional test
        $pReq = $this->createRequisition([
            'srq_emp_id'    => $empId,
            'srq_unt_id'    => $unitId,
            'srq_effhed_id' => $headId,
            'srq_month'     => $salMonth,
            'srq_status'    => 'Draft',
            'srq_parent'    => 0,
        ]);
        $cReq = $this->createRequisition([
            'srq_emp_id'    => $empId,
            'srq_unt_id'    => $unitId,
            'srq_effhed_id' => $headId,
            'srq_month'     => $salMonth,
            'srq_status'    => 'Draft',
            'srq_parent'    => $pReq->srq_id,
        ]);

        // Release initiated from child requisition
        $this->salaryService->releaseRequisitions($cReq->srq_id);
        $pReq->refresh();
        $cReq->refresh();
        $this->assertEquals('In Process', $pReq->srq_status);
        $this->assertEquals('In Process', $cReq->srq_status);

        // Cancel initiated from child requisition
        $this->salaryService->cancelRequisition($cReq->srq_id);
        $pReq->refresh();
        $cReq->refresh();
        $this->assertEquals('Cancelled', $pReq->srq_status);
        $this->assertEquals('Cancelled', $cReq->srq_status);

        // 2. Orders parent + 2 children bidirectional test
        $ctr2Id = DB::table('hr.contracts')->insertGetId([
            'ctr_num'      => $empId,
            'ctr_startdt'  => '2023-07-01',
            'ctr_enddt'    => '2025-12-31',
            'ctr_date'     => '2023-07-01',
            'ctr_salary'   => 50000,
            'ctr_unt_id'   => $unitId,
            'ctr_hed_id'   => $headId,
            'ctr_type'     => 1,
            'ctr_jobtitle' => 'Software Engineer',
            'ctr_grade'    => 'A',
            'ctr_prob'     => 0,
            'ctr_probsal'  => 50000,
        ], 'ctr_id');
        $ctr3Id = DB::table('hr.contracts')->insertGetId([
            'ctr_num'      => $empId,
            'ctr_startdt'  => '2024-01-01',
            'ctr_enddt'    => '2025-12-31',
            'ctr_date'     => '2024-01-01',
            'ctr_salary'   => 30000,
            'ctr_unt_id'   => $unitId,
            'ctr_hed_id'   => $headId,
            'ctr_type'     => 1,
            'ctr_jobtitle' => 'Software Engineer',
            'ctr_grade'    => 'A',
            'ctr_prob'     => 0,
            'ctr_probsal'  => 30000,
        ], 'ctr_id');
        DB::table('fin.contractsverif')->insert([
            ['cvf_ctr_id' => $ctr2Id, 'cvf_verif' => true],
            ['cvf_ctr_id' => $ctr3Id, 'cvf_verif' => true],
        ]);

        $parentOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 60000,
            'sor_netsalary' => 60000,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
            'sor_contracts' => (string)$ctrId,
        ]);

        $childOrder1 = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 25000,
            'sor_netsalary' => 25000,
            'sor_status'    => 'Draft',
            'sor_parent'    => $parentOrder->sor_id,
            'sor_contracts' => (string)$ctr2Id,
        ]);

        $childOrder2 = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 15000,
            'sor_netsalary' => 15000,
            'sor_status'    => 'Draft',
            'sor_parent'    => $parentOrder->sor_id,
            'sor_contracts' => (string)$ctr3Id,
        ]);

        // A. Call approveSalaryOrders on childOrder1 (child entry point)
        $this->salaryService->approveSalaryOrders($childOrder1->sor_id, $this->adminUser);

        $parentOrder->refresh();
        $childOrder1->refresh();
        $childOrder2->refresh();

        // Assert ALL THREE are 'Approved'
        $this->assertEquals('Approved', $parentOrder->sor_status);
        $this->assertEquals('Approved', $childOrder1->sor_status);
        $this->assertEquals('Approved', $childOrder2->sor_status);

        // Assert ALL THREE have negative liability commitments created
        $cmtParent = FinCommitment::where('cmt_docid', $parentOrder->sor_id)->where('cmt_type', 'Sa')->first();
        $cmtChild1 = FinCommitment::where('cmt_docid', $childOrder1->sor_id)->where('cmt_type', 'Sa')->first();
        $cmtChild2 = FinCommitment::where('cmt_docid', $childOrder2->sor_id)->where('cmt_type', 'Sa')->first();

        $this->assertNotNull($cmtParent);
        $this->assertNotNull($cmtChild1);
        $this->assertNotNull($cmtChild2);
        $this->assertEquals('Awaited', $cmtParent->cmt_status);
        $this->assertEquals('Awaited', $cmtChild1->cmt_status);
        $this->assertEquals('Awaited', $cmtChild2->cmt_status);

        // B. Call cancelOrder on childOrder2 (sibling child entry point)
        $this->salaryService->cancelOrder($childOrder2->sor_id);

        $parentOrder->refresh();
        $childOrder1->refresh();
        $childOrder2->refresh();
        $cmtParent->refresh();
        $cmtChild1->refresh();
        $cmtChild2->refresh();

        // Assert ALL THREE are 'Cancelled' and ALL THREE commitments are 'Cancelled'
        $this->assertEquals('Cancelled', $parentOrder->sor_status);
        $this->assertEquals('Cancelled', $childOrder1->sor_status);
        $this->assertEquals('Cancelled', $childOrder2->sor_status);
        $this->assertEquals('Cancelled', $cmtParent->cmt_status);
        $this->assertEquals('Cancelled', $cmtChild1->cmt_status);
        $this->assertEquals('Cancelled', $cmtChild2->cmt_status);
    }

    /**
     * 17. CRITICAL GAP TEST: Cancel is rejected for entire group if ANY member is fulfilled or paid (all-or-nothing).
     */
    public function test_cancel_blocked_for_entire_group_if_any_member_is_paid(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $parentOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 50000,
            'sor_netsalary' => 50000,
            'sor_status'    => 'Approved',
            'sor_parent'    => 0,
            'sor_contracts' => (string)$ctrId,
        ]);

        $child1 = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 20000,
            'sor_netsalary' => 20000,
            'sor_status'    => 'Approved',
            'sor_parent'    => $parentOrder->sor_id,
            'sor_contracts' => (string)$ctrId,
        ]);

        // Child 2 is already Fulfilled with Paid commitment
        $child2 = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 10000,
            'sor_netsalary' => 10000,
            'sor_status'    => 'Fulfilled',
            'sor_parent'    => $parentOrder->sor_id,
            'sor_contracts' => (string)$ctrId,
        ]);

        FinCommitment::create([
            'cmt_docid'     => $parentOrder->sor_id,
            'cmt_type'      => 'Sa',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -50000,
            'cmt_status'    => 'Awaited',
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
            'cmt_unt_id'    => $unitId,
        ]);
        FinCommitment::create([
            'cmt_docid'     => $child1->sor_id,
            'cmt_type'      => 'Sa',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -20000,
            'cmt_status'    => 'Awaited',
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
            'cmt_unt_id'    => $unitId,
        ]);
        FinCommitment::create([
            'cmt_docid'     => $child2->sor_id,
            'cmt_type'      => 'Sa',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -10000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
            'cmt_unt_id'    => $unitId,
        ]);

        // A. Attempt cancel from the parent order
        try {
            $this->salaryService->cancelOrder($parentOrder->sor_id);
            $this->fail('Expected 422 when cancelling group with paid/fulfilled child from parent');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Cannot cancel salary order group', $e->getMessage());
        }

        // Assert ZERO orders in the group changed status
        $parentOrder->refresh();
        $child1->refresh();
        $child2->refresh();
        $this->assertEquals('Approved', $parentOrder->sor_status);
        $this->assertEquals('Approved', $child1->sor_status);
        $this->assertEquals('Fulfilled', $child2->sor_status);

        // B. Attempt cancel from the sibling child order
        try {
            $this->salaryService->cancelOrder($child1->sor_id);
            $this->fail('Expected 422 when cancelling group with paid/fulfilled child from sibling');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Cannot cancel salary order group', $e->getMessage());
        }

        // Assert ZERO orders changed status
        $parentOrder->refresh();
        $child1->refresh();
        $child2->refresh();
        $this->assertEquals('Approved', $parentOrder->sor_status);
        $this->assertEquals('Approved', $child1->sor_status);
        $this->assertEquals('Fulfilled', $child2->sor_status);

        // C. Attempt cancel from the FULFILLED child order itself as entry point
        try {
            $this->salaryService->cancelOrder($child2->sor_id);
            $this->fail('Expected 422 when cancelling group directly from fulfilled child');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Cannot cancel salary order group', $e->getMessage());
        }

        // Assert ZERO orders changed status
        $parentOrder->refresh();
        $child1->refresh();
        $child2->refresh();
        $this->assertEquals('Approved', $parentOrder->sor_status);
        $this->assertEquals('Approved', $child1->sor_status);
        $this->assertEquals('Fulfilled', $child2->sor_status);
    }

    /**
     * FIX ITEM 4: Attempting cancelOrder() with the fulfilled child's own sor_id as entry point.
     * Must resolve to root parent, check whole family, reject with 422, and leave group unchanged.
     */
    public function test_cancel_blocked_when_initiated_from_fulfilled_child_order(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $parentOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_status'    => 'Approved',
            'sor_parent'    => 0,
        ]);

        $child1 = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_status'    => 'Approved',
            'sor_parent'    => $parentOrder->sor_id,
        ]);

        $child2 = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_status'    => 'Fulfilled',
            'sor_parent'    => $parentOrder->sor_id,
        ]);

        FinCommitment::create([
            'cmt_docid'     => $child2->sor_id,
            'cmt_type'      => 'Sa',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -10000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
            'cmt_unt_id'    => $unitId,
        ]);

        try {
            $this->salaryService->cancelOrder($child2->sor_id);
            $this->fail('Expected 422 when cancelling group directly from fulfilled child');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Cannot cancel salary order group', $e->getMessage());
        }

        $parentOrder->refresh();
        $child1->refresh();
        $child2->refresh();
        $this->assertEquals('Approved', $parentOrder->sor_status);
        $this->assertEquals('Approved', $child1->sor_status);
        $this->assertEquals('Fulfilled', $child2->sor_status);
    }

    /**
     * 18. FIX ITEM 3: Approval blocked when contract verification is missing or false.
     */
    public function test_approval_blocked_when_contract_verification_missing_or_false(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        // 1. Contract with cvf_verif = false
        $unverifiedCtrId = DB::table('hr.contracts')->insertGetId([
            'ctr_num'      => $empId,
            'ctr_startdt'  => '2024-01-01',
            'ctr_enddt'    => '2025-12-31',
            'ctr_date'     => '2024-01-01',
            'ctr_salary'   => 80000,
            'ctr_unt_id'   => $unitId,
            'ctr_hed_id'   => $headId,
            'ctr_type'     => 1,
            'ctr_jobtitle' => 'Software Engineer',
            'ctr_grade'    => 'A',
            'ctr_prob'     => 0,
            'ctr_probsal'  => 80000,
        ], 'ctr_id');
        DB::table('fin.contractsverif')->insert([
            'cvf_ctr_id' => $unverifiedCtrId,
            'cvf_verif'  => false,
        ]);

        $orderUnverified = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 75000,
            'sor_netsalary' => 75000,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
            'sor_contracts' => (string)$unverifiedCtrId,
        ]);

        try {
            $this->salaryService->approveSalaryOrders($orderUnverified->sor_id, $this->adminUser);
            $this->fail('Expected 422 for unverified contract');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('contract verification required', $e->getMessage());
        }

        $orderUnverified->refresh();
        $this->assertEquals('Draft', $orderUnverified->sor_status);
        $this->assertNull(FinCommitment::where('cmt_docid', $orderUnverified->sor_id)->first());

        // 2. Contract ID missing entirely from fin.contractsverif (legacy EOF check)
        $missingCtrId = 999998;
        $orderMissingVerif = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 75000,
            'sor_netsalary' => 75000,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
            'sor_contracts' => (string)$missingCtrId,
        ]);

        try {
            $this->salaryService->approveSalaryOrders($orderMissingVerif->sor_id, $this->adminUser);
            $this->fail('Expected 422 for missing contract verification record');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('contract verification required', $e->getMessage());
        }

        // 3. Empty sor_contracts is also rejected
        $orderEmptyContracts = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 75000,
            'sor_netsalary' => 75000,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
            'sor_contracts' => '',
        ]);

        try {
            $this->salaryService->approveSalaryOrders($orderEmptyContracts->sor_id, $this->adminUser);
            $this->fail('Expected 422 for empty contracts string');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // 4. Positive case: Verified contract approves normally
        $orderVerified = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 95000,
            'sor_netsalary' => 95000,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
            'sor_contracts' => (string)$ctrId,
        ]);

        $res = $this->salaryService->approveSalaryOrders($orderVerified->sor_id, $this->adminUser);
        $orderVerified->refresh();
        $this->assertEquals('Approved', $orderVerified->sor_status);
        $this->assertCount(1, $res['commitments']);
    }

    /**
     * FIX ITEM 3: Multi-contract comma-separated sor_contracts verification gate.
     * All contract IDs must be verified; if even one is unverified, approval is blocked.
     */
    public function test_multi_contract_sor_contracts_all_verified_required(): void
    {
        [$empId, $ctr1, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        // Create second contract with distinct start date
        $ctr2 = DB::table('hr.contracts')->insertGetId([
            'ctr_num'      => $empId,
            'ctr_startdt'  => '2023-06-01',
            'ctr_enddt'    => '2025-12-31',
            'ctr_date'     => '2023-06-01',
            'ctr_salary'   => 50000,
            'ctr_unt_id'   => $unitId,
            'ctr_hed_id'   => $headId,
            'ctr_type'     => 1,
            'ctr_jobtitle' => 'Software Engineer',
            'ctr_grade'    => 'A',
            'ctr_prob'     => 0,
            'ctr_probsal'  => 50000,
        ], 'ctr_id');

        // Case A: ctr1 is verified (by createEmployeeWithContract), ctr2 is unverified
        DB::table('fin.contractsverif')->insert([
            'cvf_ctr_id' => $ctr2,
            'cvf_verif'  => false,
        ]);

        $orderMixed = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_salary'    => 95000,
            'sor_netsalary' => 95000,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
            'sor_contracts' => "{$ctr1}, {$ctr2}", // Comma-separated with whitespace
        ]);

        try {
            $this->salaryService->approveSalaryOrders($orderMixed->sor_id, $this->adminUser);
            $this->fail('Expected 422 when multi-contract list contains unverified contract');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('contract verification required', $e->getMessage());
        }

        // Case B: Mark ctr2 as verified -> Now ALL verified -> Approval succeeds
        DB::table('fin.contractsverif')->where('cvf_ctr_id', $ctr2)->update(['cvf_verif' => true]);

        $res = $this->salaryService->approveSalaryOrders($orderMixed->sor_id, $this->adminUser);
        $orderMixed->refresh();
        $this->assertEquals('Approved', $orderMixed->sor_status);
        $this->assertCount(1, $res['commitments']);
    }

    /**
     * 19. FIX ITEM 1: Manual salary decrease appends pending remark matching legacy string format.
     */
    public function test_manual_salary_decrease_appends_pending_remark(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_netsalary' => 95000,
            'sor_salary'    => 95000,
            'sor_status'    => 'Draft',
            'sor_remarks'   => 'Base remark',
        ]);

        // 1. Decrease from 95,000 to 80,000 -> Diff = 15,000
        $updated = $this->salaryService->adjustOrderSalary($order->sor_id, 80000, $this->adminUser);
        $this->assertEquals(80000, $updated->sor_salary);
        $this->assertEquals('Base remark Pending - 15000.', $updated->sor_remarks);

        // 2. Decrease further from 80,000 to 70,000 -> Old pending stripped, new diff = 25,000
        $updated2 = $this->salaryService->adjustOrderSalary($order->sor_id, 70000, $this->adminUser);
        $this->assertEquals(70000, $updated2->sor_salary);
        $this->assertEquals('Base remark Pending - 25000.', $updated2->sor_remarks);

        // 3. Reset back to full netsalary 95,000 -> Pending stripped completely
        $updated3 = $this->salaryService->adjustOrderSalary($order->sor_id, 95000, $this->adminUser);
        $this->assertEquals(95000, $updated3->sor_salary);
        $this->assertEquals('Base remark', $updated3->sor_remarks);
    }

    /**
     * FIX ITEM 1: Resetting salary back to exact sor_netsalary removes Pending remark entirely.
     */
    public function test_manual_salary_reset_to_netsalary_removes_pending_remark_completely(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_netsalary' => 95000,
            'sor_salary'    => 95000,
            'sor_status'    => 'Draft',
            'sor_remarks'   => 'Prior approval note',
        ]);

        // Step 1: Reduce salary from 95,000 to 80,000 -> Pending appended
        $reduced = $this->salaryService->adjustOrderSalary($order->sor_id, 80000, $this->adminUser);
        $this->assertEquals(80000, $reduced->sor_salary);
        $this->assertEquals('Prior approval note Pending - 15000.', $reduced->sor_remarks);

        // Step 2: Reset salary back to exact sor_netsalary (95,000) -> Pending stripped completely
        $reset = $this->salaryService->adjustOrderSalary($order->sor_id, 95000, $this->adminUser);
        $this->assertEquals(95000, $reset->sor_salary);
        $this->assertEquals('Prior approval note', $reset->sor_remarks);
        $this->assertStringNotContainsString('Pending -', $reset->sor_remarks);
    }

    /**
     * 20. FIX ITEM 1: Manual salary increase is rejected with 422 (sor_salary_BeforeUpdate).
     */
    public function test_manual_salary_increase_is_rejected(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_netsalary' => 95000,
            'sor_salary'    => 95000,
            'sor_status'    => 'Draft',
        ]);

        try {
            $this->salaryService->adjustOrderSalary($order->sor_id, 100000, $this->adminUser);
            $this->fail('Expected 422 when increasing salary above sor_netsalary');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Salary cannot be increased manually', $e->getMessage());
        }

        $order->refresh();
        $this->assertEquals(95000, $order->sor_salary);
    }

    /**
     * 21. FIX ITEM 1: Salary override blocked on non-draft orders.
     */
    public function test_salary_override_blocked_on_non_draft_order(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_netsalary' => 95000,
            'sor_salary'    => 95000,
            'sor_status'    => 'Approved',
        ]);

        try {
            $this->salaryService->adjustOrderSalary($order->sor_id, 80000, $this->adminUser);
            $this->fail('Expected 422 when adjusting non-draft order');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('Only \'Draft\' orders can be adjusted', $e->getMessage());
        }
    }

    /**
     * 22. FIX ITEM 1: Salary override requires finance approver role.
     */
    public function test_salary_override_requires_finance_approver(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_month'     => $salMonth,
            'sor_netsalary' => 95000,
            'sor_salary'    => 95000,
            'sor_status'    => 'Draft',
        ]);

        // Non-approver finance user
        $nonApprover = CenAccount::where('acc_untarea', 'ILIKE', 'fin')
            ->where('acc_auth', '!=', 'approver')
            ->first();

        if ($nonApprover) {
            try {
                $this->salaryService->adjustOrderSalary($order->sor_id, 80000, $nonApprover);
                $this->fail('Expected 403 for non-approver');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertEquals(403, $e->getStatusCode());
            }
        }

        // HR user
        $hrUser = CenAccount::where('acc_untarea', 'ILIKE', 'hr')->first();
        if ($hrUser) {
            try {
                $this->salaryService->adjustOrderSalary($order->sor_id, 80000, $hrUser);
                $this->fail('Expected 403 for HR user');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertEquals(403, $e->getStatusCode());
            }
        }
    }
}
