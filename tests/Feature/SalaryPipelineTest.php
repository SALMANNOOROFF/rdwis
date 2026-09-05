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
}
