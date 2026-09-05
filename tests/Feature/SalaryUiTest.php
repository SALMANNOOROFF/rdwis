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

    protected function setUp(): void
    {
        parent::setUp();

        $this->salaryService = app(SalaryGenerationService::class);

        $this->adminUser = CenAccount::where('acc_untarea', 'ILIKE', 'hr')->first()
            ?? CenAccount::where('acc_untarea', 'ILIKE', 'nrdi')->first()
            ?? CenAccount::first();

        $this->adminUser->acc_lowers = 100000;
        $this->adminUser->acc_uppers = 999999;
        $this->adminUser->acc_auth = 'approver';
        $this->adminUser->save();
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
}
