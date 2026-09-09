<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\FinCommitment;
use App\Models\FinSalOrder;
use App\Models\FinSalOrderShd;
use App\Models\HrSalReq;
use App\Services\SalaryGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalaryUiTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected SalaryGenerationService $salaryService;
    protected $adminUser;
    protected $hrUser;
    protected $prjUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salaryService = app(SalaryGenerationService::class);

        // Finance Approver user for order actions and general admin tests
        $this->adminUser = CenAccount::where('acc_untarea', 'ILIKE', 'fin')->first()
            ?? CenAccount::where('acc_untarea', 'ILIKE', 'it')->first()
            ?? CenAccount::first();

        $this->adminUser->acc_untarea = 'fin';
        $this->adminUser->acc_lowers = 100000;
        $this->adminUser->acc_uppers = 999999;
        $this->adminUser->acc_lowerm = 100000;
        $this->adminUser->acc_upperm = 999999;
        $this->adminUser->acc_auth = 'approver';
        $this->adminUser->save();

        // Dedicated HR user for asserting HR queue boundaries
        $this->hrUser = CenAccount::where('acc_untarea', 'ILIKE', 'hr')->first();
        if ($this->hrUser) {
            $this->hrUser->acc_lowers = 100000;
            $this->hrUser->acc_uppers = 999999;
            $this->hrUser->save();
        }

        // Dedicated PRJ (Division) user for asserting Division queue boundaries
        $this->prjUser = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first();
        if ($this->prjUser) {
            $this->prjUser->acc_lowers = 100000;
            $this->prjUser->acc_uppers = 999999;
            $this->prjUser->acc_auth = 'approver';
            $this->prjUser->save();
        }
    }

    /**
     * Helper to create test employee with contract, plan, and verification.
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
            'emp_name'      => 'Test UI Emp',
            'emp_unt_id'    => $unitId,
            'emp_status'    => 'Active',
            'emp_joindt'    => '2023-01-01',
            'emp_hed_id'    => null, // Central employee by default
        ], $empOverrides));

        $ctrId = DB::table('hr.contracts')->insertGetId(array_merge([
            'ctr_num'        => $empId,
            'ctr_startdt'    => '2023-01-01',
            'ctr_enddt'      => '2028-12-31',
            'ctr_date'       => '2023-01-01',
            'ctr_salary'     => 120000,
            'ctr_unt_id'     => $unitId,
            'ctr_hed_id'     => $headId,
            'ctr_jobtitle'   => 'Engineer',
            'ctr_grade'      => 'A',
            'ctr_type'       => 1,
            'ctr_prob'       => 0,
            'ctr_probsal'    => 120000,
        ], $ctrOverrides), 'ctr_id');

        DB::table('hr.contractplans')->insert([
            'cpn_ctr_id'  => $ctrId,
            'cpn_startdt' => '2023-01-01',
            'cpn_enddt'   => '2028-12-31',
            'cpn_hed_id'  => $headId,
        ]);

        DB::table('fin.contractsverif')->insert([
            'cvf_ctr_id' => $ctrId,
            'cvf_verif'  => true,
        ]);

        DB::table('fin.empeffheads')->insert([
            'eeh_emp_id'    => $empId,
            'eeh_emphed_id' => $headId,
            'eeh_status'    => 'Open',
        ]);

        return [$empId, $ctrId, $unitId, $headId];
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
     * 1. Test Requisitions Dashboard renders with exact srq_status vocabulary
     * and zero mixing of sor_status strings.
     */
    public function test_requisitions_dashboard_renders_with_exact_srq_status_vocabulary(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $req = $this->createRequisition([
            'srq_emp_id'       => $empId,
            'srq_unt_id'       => $unitId,
            'srq_hed_id'       => $headId,
            'srq_effhed_id'    => $headId,
            'srq_effunt_id'    => $unitId,
            'srq_month'        => $salMonth,
            'srq_status'       => 'In Process',
            'srq_salary'       => 120000,
            'srq_empnamecomp'  => 'Test Requisition Vocabulary Emp',
            'srq_ctrsalary'    => 120000,
            'srq_grosalary'    => 120000,
            'srq_netsalary'    => 120000,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('divhr.salary.requisitions.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Exact srq_status vocabulary must be present
        $this->assertStringContainsString('In Process', $content);
        $this->assertStringContainsString('Draft', $content);
        $this->assertStringContainsString('Fulfilled', $content);
        $this->assertStringContainsString('Cancelled', $content);
        $this->assertStringContainsString('Status (srq_status)', $content);

        // sor_status vocabulary must NOT be mixed into requisition column headers
        $this->assertStringNotContainsString('Order Status (sor_status)', $content);
    }

    /**
     * 2. Test Duplicate Rejection surfaces exact conflicting employee ID, name, and period.
     */
    public function test_duplicate_rejection_surfaces_conflicting_employee_and_period(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        // Pre-create active requisition in Draft
        $this->createRequisition([
            'srq_emp_id'       => $empId,
            'srq_unt_id'       => $unitId,
            'srq_effhed_id'    => $headId,
            'srq_effunt_id'    => $unitId,
            'srq_month'        => $salMonth,
            'srq_status'       => 'Draft',
            'srq_salary'       => 120000,
            'srq_empnamecomp'  => 'Conflicting Emp Name',
        ]);

        // Attempt duplicate generation
        $response = $this->actingAs($this->adminUser)->postJson(route('divhr.salary.requisitions.generate'), [
            'month'   => '2024-01',
            'emp_ids' => [$empId],
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'error',
            'conflicts' => [
                '*' => ['emp_id', 'name', 'period', 'reason']
            ]
        ]);

        $conflicts = $response->json('conflicts');
        $this->assertCount(1, $conflicts);
        $this->assertEquals($empId, $conflicts[0]['emp_id']);
        $this->assertStringContainsString('2024-01', $conflicts[0]['period']);
    }

    /**
     * 3. Test Preview endpoint returns candidate breakdown and individual 7 exclusion reasons.
     */
    public function test_preview_modal_displays_7_individual_exclusion_reasons(): void
    {
        [$empId] = $this->createEmployeeWithContract();

        // 1. Employee without contract (fails Check 3: No Contract/Plan)
        $noCtrEmp = 'TNC' . substr(uniqid(), -9);
        DB::table('hr.emps')->insert([
            'emp_id'     => $noCtrEmp,
            'emp_name'   => 'No Contract Emp',
            'emp_cnic'   => '37405' . rand(10000000, 99999999),
            'emp_unt_id' => 350000,
            'emp_status' => 'Active',
            'emp_joindt' => '2023-01-01',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('divhr.salary.preview', [
            'month' => '2024-01',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'month',
            'included',
            'excluded',
            'counts' => ['total_candidates', 'eligible', 'excluded']
        ]);

        $data = $response->json();
        $excluded = collect($data['excluded']);

        // Assert Check 3: No Contract/Plan is individually surfaced
        $failedCheck3 = $excluded->firstWhere('employee.emp_id', $noCtrEmp);
        $this->assertNotNull($failedCheck3);
        $this->assertEquals('No Contract/Plan', $failedCheck3['reason']);
    }

    /**
     * 4. Test Order Detail renders exact single subhead row (HR / 1.0) without multi-row proportional split.
     */
    public function test_order_detail_renders_exact_single_subhead_row_without_split(): void
    {
        $projectHeadId = DB::table('cen.heads')->value('hed_id');
        [$empId, $ctrId, $unitId] = $this->createEmployeeWithContract([
            'emp_hed_id' => $projectHeadId, // Project Unit employee
        ]);
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_type'         => 'Sa',
            'sor_emp_id'       => $empId,
            'sor_empnamecomp'  => 'Project Unit Order Detail Emp',
            'sor_hed_id'       => $projectHeadId,
            'sor_unt_id'       => $unitId,
            'sor_effhed_id'    => $projectHeadId,
            'sor_effunt_id'    => $unitId,
            'sor_month'        => $salMonth,
            'sor_salary'       => 150000,
            'sor_status'       => 'Draft',
        ]);

        FinSalOrderShd::create([
            'sod_sor_id'  => $order->sor_id,
            'sod_type'    => 'Sa',
            'sod_subhead' => 'HR',
            'sod_ratio'   => 1.0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Exactly single HR / 1.0 row rendered
        $this->assertStringContainsString('Subhead Allocation (fin.salorders_shd)', $content);
        $this->assertStringContainsString('HR', $content);
        $this->assertStringContainsString('1.00 (100%)', $content);

        // Subhead count assertion
        $this->assertEquals(1, $order->subheads()->count());
    }

    /**
     * 5. Test High-Friction Order Cancellation requires mandatory reason.
     */
    public function test_high_friction_order_cancellation_requires_reason(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_type'        => 'Sa',
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Order To Cancel',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => $salMonth,
            'sor_salary'      => 100000,
            'sor_status'      => 'Approved',
        ]);

        // Awaited commitment
        FinCommitment::create([
            'cmt_docid'     => $order->sor_id,
            'cmt_type'      => 'Sa',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -100000,
            'cmt_status'    => 'Awaited',
            'cmt_unt_id'    => $unitId,
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
        ]);

        // 1. Missing reason must fail validation
        $failResponse = $this->actingAs($this->adminUser)->post(route('divhr.salary.orders.cancel', $order->sor_id), []);
        $failResponse->assertSessionHasErrors('reason');

        // 2. Valid cancellation with reason
        $reason = 'Administrative error: Employee was transferred to another unit.';
        $successResponse = $this->actingAs($this->adminUser)->post(route('divhr.salary.orders.cancel', $order->sor_id), [
            'reason' => $reason,
        ]);

        $successResponse->assertRedirect(route('divhr.salary.orders.index'));
        $successResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Cancelled', $order->sor_status);

        $commitment = FinCommitment::where('cmt_docid', $order->sor_id)->where('cmt_type', 'Sa')->first();
        $this->assertNotNull($commitment);
        $this->assertEquals('Cancelled', $commitment->cmt_status);
    }

    /**
     * 6. Test Commitment Verification View matches VerifySalaryCommitmentsCommand output.
     */
    public function test_commitment_verification_view_matches_command_output(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('divhr.salary.commitments.verify'));
        $response->assertStatus(200);
        $response->assertViewIs('hr.salary.commitments.verify');
        $response->assertViewHas(['audit', 'month']);

        $audit = $response->viewData('audit');
        $this->assertArrayHasKey('total_approved', $audit);
        $this->assertArrayHasKey('verified_count', $audit);
        $this->assertArrayHasKey('missing_count', $audit);
        $this->assertEquals($audit['total_approved'], $audit['verified_count'] + $audit['missing_count']);
    }

    /**
     * 7. Test Payment Status reflects settled commitment (cmt_status = Paid).
     */
    public function test_payment_status_reflects_settled_commitment(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_type'        => 'Sa',
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Settled Order Emp',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => $salMonth,
            'sor_salary'      => 110000,
            'sor_status'      => 'Approved',
        ]);

        FinCommitment::create([
            'cmt_docid'     => $order->sor_id,
            'cmt_type'      => 'Sa',
            'cmt_date'      => '2024-01-28',
            'cmt_amount'    => -110000,
            'cmt_status'    => 'Paid', // Settled via PaymentController
            'cmt_unt_id'    => $unitId,
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('cmt_status', $content);
        $this->assertStringContainsString('Paid', $content);
    }

    /**
     * 8. Test Cancel Requisition transitions srq_status to Cancelled (Dedicated Test for Item 1).
     */
    public function test_cancel_requisition_transitions_srq_status_to_cancelled(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $req = $this->createRequisition([
            'srq_emp_id'       => $empId,
            'srq_unt_id'       => $unitId,
            'srq_effhed_id'    => $headId,
            'srq_effunt_id'    => $unitId,
            'srq_month'        => $salMonth,
            'srq_status'       => 'Draft',
            'srq_salary'       => 120000,
            'srq_empnamecomp'  => 'Requisition To Cancel',
        ]);

        $reason = 'Mistaken duplicate entry for test employee.';
        $response = $this->actingAs($this->adminUser)->post(route('divhr.salary.requisitions.cancel', $req->srq_id), [
            'reason' => $reason,
        ]);

        $response->assertSessionHas('success');

        $req->refresh();
        $this->assertEquals('Cancelled', $req->srq_status);
        $this->assertNotNull($req->srq_closedtg);
    }

    /**
     * 9. Test Approve Salary Order transitions sor_status and creates negative commitment (Dedicated Test for Item 2).
     */
    public function test_approve_salary_order_transitions_sor_status_and_creates_awaited_negative_commitment(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_type'        => 'Sa',
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Order To Approve',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => $salMonth,
            'sor_salary'      => 135000,
            'sor_status'      => 'Draft',
        ]);

        // FIX 3: Assert confirmation prompt includes exact formatted commitment amount
        $showResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee("Approve salary order #{$order->sor_id}? Approving will create a financial commitment of Rs. " . number_format($order->sor_salary) . " in fin.commitments.", false);

        $response = $this->actingAs($this->adminUser)->post(route('divhr.salary.orders.approve', $order->sor_id));
        $response->assertRedirect(route('divhr.salary.orders.show', $order->sor_id));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Approved', $order->sor_status);

        $commitment = FinCommitment::where('cmt_docid', $order->sor_id)->where('cmt_type', 'Sa')->first();
        $this->assertNotNull($commitment);
        $this->assertEquals('Awaited', $commitment->cmt_status);
        $this->assertEquals(-135000.0, (float)$commitment->cmt_amount);
    }

    /**
     * 10. FIX 4: Test Salary Slip view renders real data and printable layout.
     * Also verifies "Print Pay Slip" button on orders.show and HR 403 access control.
     */
    public function test_salary_slip_view_renders_real_data_and_print_layout(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $salMonth = '2024-01-31';

        $order = $this->createSalaryOrder([
            'sor_type'        => 'Sa',
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Pay Slip Employee',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => $salMonth,
            'sor_salary'      => 145000,
            'sor_ctrsalary'   => 150000,
            'sor_grosalary'   => 150000,
            'sor_underwork'   => 5000,
            'sor_status'      => 'Approved',
        ]);

        // A. Verify "Print Pay Slip" button is present on Order Detail view for Approved order
        $showResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Print Pay Slip');
        $showResponse->assertSee(route('divhr.salary.orders.slip', $order->sor_id));

        // B. Render the slip itself
        $slipResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.slip', $order->sor_id));
        $slipResponse->assertStatus(200);
        $slipResponse->assertViewIs('hr.salary.slip');

        // C. Assert legacy title and structure
        $slipResponse->assertSee('M/S MTSS PAY SLIP');
        $slipResponse->assertSee('Pay Slip Employee');
        $slipResponse->assertSee($empId);
        $slipResponse->assertSee('Rs. 150,000'); // Base salary
        $slipResponse->assertSee('Rs. 5,000');   // Underwork deduction
        $slipResponse->assertSee('Rs. 145,000'); // Net payable
        $slipResponse->assertSee('@media print', false);

        // D. Verify HR role is blocked from viewing Salary Order and Slip (routing rule)
        if ($this->hrUser) {
            $hrOrderResponse = $this->actingAs($this->hrUser)->get(route('divhr.salary.orders.show', $order->sor_id));
            $hrOrderResponse->assertStatus(403);

            $hrSlipResponse = $this->actingAs($this->hrUser)->get(route('divhr.salary.orders.slip', $order->sor_id));
            $hrSlipResponse->assertStatus(403);
        }
    }

    /**
     * 11. Permanent routing test: HR role cannot view or manage Salary Orders.
     */
    public function test_hr_role_cannot_view_or_manage_salary_orders(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $order = $this->createSalaryOrder([
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Routing Guard Emp',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => '2024-01-31',
            'sor_status'      => 'Draft',
        ]);

        $hrUser = $this->hrUser ?? CenAccount::where('acc_untarea', 'hr')->first();
        $this->assertNotNull($hrUser, 'HR test user must exist.');

        // HR hits GET on salary orders index route -> assert 403
        $indexResponse = $this->actingAs($hrUser)->get(route('divhr.salary.orders.index'));
        $indexResponse->assertStatus(403);

        // HR hits GET on specific order show route -> assert 403
        $showResponse = $this->actingAs($hrUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $showResponse->assertStatus(403);
    }

    /**
     * 12. Permanent routing test: HR and Division cannot access Commitments Hub.
     * Guarded by area:fin middleware on /fin/payments.
     */
    public function test_hr_and_division_cannot_access_commitments_hub(): void
    {
        $hrUser = $this->hrUser ?? CenAccount::where('acc_untarea', 'hr')->first();
        $prjUser = $this->prjUser ?? CenAccount::where('acc_untarea', 'prj')->first();
        $finUser = $this->adminUser;

        $this->assertNotNull($hrUser, 'HR user must exist.');
        $this->assertNotNull($prjUser, 'Division/PRJ user must exist.');
        $this->assertNotNull($finUser, 'Finance user must exist.');

        // Re-enable CheckArea middleware specifically to test route gating
        $middleware = [\App\Http\Middleware\CheckArea::class];

        // HR-role test user hits GET /fin/payments -> assert 403
        $hrResponse = $this->withMiddleware($middleware)->actingAs($hrUser)->get(route('fin.payments.index'));
        $hrResponse->assertStatus(403);

        // Division/prj-role test user hits GET /fin/payments -> assert 403
        $prjResponse = $this->withMiddleware($middleware)->actingAs($prjUser)->get(route('fin.payments.index'));
        $prjResponse->assertStatus(403);

        // Finance/fin-role test user hits GET /fin/payments -> assert 200 (positive control)
        $finResponse = $this->withMiddleware($middleware)->actingAs($finUser)->get(route('fin.payments.index'));
        $finResponse->assertStatus(200);
    }

    /**
     * 13. Permanent routing test: Division can view/monitor orders in their horizon,
     * but attempting POST to approve returns 403 (Finance-only approver role).
     */
    public function test_division_can_view_but_not_approve_salary_orders(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $order = $this->createSalaryOrder([
            'sor_emp_id'      => $empId,
            'sor_empnamecomp' => 'Division Monitor Emp',
            'sor_unt_id'      => $unitId,
            'sor_effhed_id'   => $headId,
            'sor_effunt_id'   => $unitId,
            'sor_month'       => '2024-01-31',
            'sor_salary'      => 100000,
            'sor_status'      => 'Draft',
        ]);

        $prjUser = $this->prjUser ?? CenAccount::where('acc_untarea', 'prj')->first();
        $this->assertNotNull($prjUser, 'Division/PRJ user must exist.');

        // Division can GET orders index (returns 200)
        $indexResponse = $this->actingAs($prjUser)->get(route('divhr.salary.orders.index'));
        $indexResponse->assertStatus(200);

        // Division can GET order show (returns 200, view/monitor)
        $showResponse = $this->actingAs($prjUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Approve Order'); // Button must not render for Division

        // Attempting POST to approve returns 403
        $approveResponse = $this->actingAs($prjUser)->post(route('divhr.salary.orders.approve', $order->sor_id));
        $approveResponse->assertStatus(403);

        // Order remains in Draft status
        $this->assertEquals('Draft', $order->fresh()->sor_status);
    }

    /**
     * 14. UI Test: Child-order view shows linked-group banner, parent-order view does not.
     */
    public function test_linked_group_banner_rendered_on_child_order_and_absent_on_parent_order(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();
        $parentOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_status'    => 'Draft',
            'sor_parent'    => 0,
        ]);

        $childOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_status'    => 'Draft',
            'sor_parent'    => $parentOrder->sor_id,
        ]);

        // 1. Parent order view: linked-group banner is absent
        $parentResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $parentOrder->sor_id));
        $parentResponse->assertStatus(200);
        $parentResponse->assertDontSee('Linked Order Group:');

        // 2. Child order view: linked-group banner is present
        $childResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $childOrder->sor_id));
        $childResponse->assertStatus(200);
        $childResponse->assertSee('Linked Order Group:');
        $childResponse->assertSee("Parent Order <strong>#{$parentOrder->sor_id}</strong>", false);
    }

    /**
     * 15. UI Test: Adjust Salary button and modal visible only for Draft orders viewed by Finance approvers.
     */
    public function test_adjust_salary_button_and_modal_visibility_rules(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeWithContract();

        $draftOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_status'    => 'Draft',
        ]);

        $approvedOrder = $this->createSalaryOrder([
            'sor_emp_id'    => $empId,
            'sor_unt_id'    => $unitId,
            'sor_effhed_id' => $headId,
            'sor_status'    => 'Approved',
        ]);

        // Case 1: Draft order viewed by Finance Approver -> Visible
        $finApproverResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $draftOrder->sor_id));
        $finApproverResponse->assertStatus(200);
        $finApproverResponse->assertSee('Adjust Salary');
        $finApproverResponse->assertSee('id="adjustSalaryModal"', false);

        // Case 2: Approved order viewed by Finance Approver -> Absent
        $approvedResponse = $this->actingAs($this->adminUser)->get(route('divhr.salary.orders.show', $approvedOrder->sor_id));
        $approvedResponse->assertStatus(200);
        $approvedResponse->assertDontSee('Adjust Salary');
        $approvedResponse->assertDontSee('id="adjustSalaryModal"', false);

        // Case 3: Draft order viewed by Division/PRJ monitor -> Absent
        $prjUser = $this->prjUser ?? CenAccount::where('acc_untarea', 'prj')->first();
        if ($prjUser) {
            $prjResponse = $this->actingAs($prjUser)->get(route('divhr.salary.orders.show', $draftOrder->sor_id));
            $prjResponse->assertStatus(200);
            $prjResponse->assertDontSee('Adjust Salary');
            $prjResponse->assertDontSee('id="adjustSalaryModal"', false);
        }

        // Case 4: Draft order viewed by Finance non-approver (e.g. monitor) -> Absent
        $finNonApprover = CenAccount::where('acc_untarea', 'ILIKE', 'fin')
            ->where('acc_auth', '!=', 'approver')
            ->first();
        if ($finNonApprover) {
            $finNonApproverResponse = $this->actingAs($finNonApprover)->get(route('divhr.salary.orders.show', $draftOrder->sor_id));
            $finNonApproverResponse->assertStatus(200);
            $finNonApproverResponse->assertDontSee('Adjust Salary');
            $finNonApproverResponse->assertDontSee('id="adjustSalaryModal"', false);
        }
    }
}
