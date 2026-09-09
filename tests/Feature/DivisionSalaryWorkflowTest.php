<?php

namespace Tests\Feature;

use App\Models\CenAccount;
use App\Models\FinSalOrder;
use App\Models\HrSalReq;
use App\Services\SalaryGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DivisionSalaryWorkflowTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected SalaryGenerationService $salaryService;
    protected CenAccount $divUser;
    protected CenAccount $finUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salaryService = app(SalaryGenerationService::class);

        // Division User (Single Unit scope: 350000)
        $this->divUser = CenAccount::where('acc_untarea', 'ILIKE', 'prj')->first()
            ?? new CenAccount();
        $this->divUser->acc_untarea = 'prj';
        $this->divUser->acc_access = 'single';
        $this->divUser->acc_lowers = 350000;
        $this->divUser->acc_uppers = 350000;
        $this->divUser->acc_auth = 'approver';
        $this->divUser->acc_status = 'Active';
        $this->divUser->save();

        // Finance User (Multi Unit scope: 100000 - 999999)
        $this->finUser = CenAccount::where('acc_untarea', 'ILIKE', 'fin')->first()
            ?? new CenAccount();
        $this->finUser->acc_untarea = 'fin';
        $this->finUser->acc_access = 'multiple';
        $this->finUser->acc_lowerm = 100000;
        $this->finUser->acc_upperm = 999999;
        $this->finUser->acc_auth = 'approver';
        $this->finUser->acc_status = 'Active';
        $this->finUser->save();
    }

    /**
     * Helper to create test employee with contract, plan, and verification in a specific unit.
     */
    protected function createEmployeeInUnit(int $unitId, string $prefix = 'DIV'): array
    {
        $empId = substr($prefix, 0, 3) . substr(uniqid(), -9);
        $headId = DB::table('cen.heads')->where('hed_unt_id', $unitId)->value('hed_id')
               ?? DB::table('cen.heads')->value('hed_id');

        DB::table('hr.emps')->insert([
            'emp_id'        => $empId,
            'emp_cnic'      => '35201-' . rand(1000000, 9999999) . '-1',
            'emp_name'      => "Test Emp {$prefix}",
            'emp_unt_id'    => $unitId,
            'emp_status'    => 'Active',
            'emp_joindt'    => '2023-01-01',
            'emp_hed_id'    => null,
        ]);

        $ctrId = DB::table('hr.contracts')->insertGetId([
            'ctr_num'        => $empId,
            'ctr_startdt'    => '2023-01-01',
            'ctr_enddt'      => '2028-12-31',
            'ctr_date'       => '2023-01-01',
            'ctr_salary'     => 100000,
            'ctr_unt_id'     => $unitId,
            'ctr_hed_id'     => $headId,
            'ctr_jobtitle'   => 'Engineer',
            'ctr_grade'      => 'B',
            'ctr_type'       => 1,
            'ctr_prob'       => 0,
            'ctr_probsal'    => 100000,
        ], 'ctr_id');

        DB::table('hr.contractplans')->insert([
            'cpn_ctr_id'    => $ctrId,
            'cpn_startdt'   => '2023-01-01',
            'cpn_enddt'     => '2028-12-31',
            'cpn_hed_id'    => $headId,
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
     * Helper to create valid HrSalReq for test setup.
     */
    protected function createRequisition(array $overrides = []): HrSalReq
    {
        $salMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();
        $unitId = $overrides['srq_unt_id'] ?? 350000;
        $empId = $overrides['srq_emp_id'] ?? null;

        if (!$empId) {
            $empId = 'T' . substr(uniqid(), -11);
            DB::table('hr.emps')->insert([
                'emp_id'        => $empId,
                'emp_cnic'      => '35201-' . rand(1000000, 9999999) . '-1',
                'emp_name'      => 'Test Workflow Emp',
                'emp_unt_id'    => $unitId,
                'emp_status'    => 'Active',
                'emp_joindt'    => '2023-01-01',
            ]);
        }

        return HrSalReq::create(array_merge([
            'srq_emp_id'       => $empId,
            'srq_empnamecomp'  => 'Test Division Workflow Emp',
            'srq_unt_id'       => $unitId,
            'srq_effunt_id'    => $unitId,
            'srq_effhed_id'    => 350000,
            'srq_month'        => $salMonth,
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
            'sor_month'        => '2024-11-01',
            'sor_ctrsalary'    => 100000,
            'sor_netsalary'    => 95000,
            'sor_salary'       => 95000,
            'sor_bnkacctitle'  => 'TEST EMPLOYEE',
            'sor_bnkaccdetail' => '(Pay by Cheque)',
            'sor_contracts'    => '1',
            'sor_status'       => 'Draft',
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
     * 1. Division user cannot preview or generate salary outside assigned unit horizon.
     */
    public function test_division_cannot_preview_or_generate_salary_outside_unit_horizon(): void
    {
        $salMonth = Carbon::now()->subMonth()->format('Y-m');

        // Emp A in scope (350000), Emp B out of scope (450000)
        [$empAId] = $this->createEmployeeInUnit(350000, 'IN');
        [$empBId] = $this->createEmployeeInUnit(450000, 'OUT');

        // Preview with out-of-scope unit parameter -> 403 Forbidden
        $outOfScopeResponse = $this->actingAs($this->divUser)
            ->getJson(route('divhr.salary.preview', ['month' => $salMonth, 'unit_id' => 450000]));
        $outOfScopeResponse->assertStatus(403);

        // Preview without unit parameter -> scoped to 350000, Emp B excluded
        $inScopeResponse = $this->actingAs($this->divUser)
            ->getJson(route('divhr.salary.preview', ['month' => $salMonth]));
        $inScopeResponse->assertStatus(200);

        $includedEmpIds = collect($inScopeResponse->json('included'))->pluck('employee.emp_id')->all();
        $this->assertContains($empAId, $includedEmpIds);
        $this->assertNotContains($empBId, $includedEmpIds);

        // Generate with out-of-scope employee ID -> 403 Forbidden
        $generateOutOfScope = $this->actingAs($this->divUser)
            ->postJson(route('divhr.salary.requisitions.generate'), [
                'month'   => $salMonth,
                'emp_ids' => [$empBId],
            ]);
        $generateOutOfScope->assertStatus(403);
    }

    /**
     * 2. Division user cannot call createOrders directly (Finance only endpoint).
     */
    public function test_division_cannot_call_create_orders_non_finance_forbidden(): void
    {
        $req = $this->createRequisition([
            'srq_unt_id' => 350000,
            'srq_status' => 'In Process',
        ]);

        $response = $this->actingAs($this->divUser)
            ->post(route('divhr.salary.requisitions.create_orders', $req->srq_id));

        $response->assertStatus(403);
    }

    /**
     * 3. Division releasing requisition transitions srq to In Process AND auto-creates Draft Orders for Finance.
     */
    public function test_division_release_requisition_automatically_creates_draft_salary_orders_for_finance(): void
    {
        $salMonth = Carbon::now()->subMonth()->format('Y-m');
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeInUnit(350000, 'REL');

        // Generate draft requisition
        $this->actingAs($this->divUser)
            ->postJson(route('divhr.salary.requisitions.generate'), [
                'month'   => $salMonth,
                'emp_ids' => [$empId],
            ])
            ->assertStatus(200);

        $req = HrSalReq::where('srq_emp_id', $empId)->first();
        $this->assertNotNull($req);
        $this->assertEquals('Draft', $req->srq_status);

        // Release the requisition as Division
        $releaseResponse = $this->actingAs($this->divUser)
            ->post(route('divhr.salary.requisitions.release', $req->srq_id));

        $releaseResponse->assertSessionHas('success');
        $this->assertStringContainsString('Draft Salary Order(s) created for Finance', session('success'));

        // Verify requisition is In Process
        $req->refresh();
        $this->assertEquals('In Process', $req->srq_status);
        $this->assertNotNull($req->srq_releasedtg);

        // Verify Draft Salary Order was created for Finance
        $order = FinSalOrder::where('sor_srq_id', $req->srq_id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('Draft', $order->sor_status);
        $this->assertEquals($req->srq_salary, $order->sor_salary);
    }

    /**
     * 4. Legacy Requisitions Dashboard status tabs (All, Draft, Open, Closed) and Attendance Navigation.
     */
    public function test_legacy_requisitions_dashboard_status_tabs_and_attendance_navigation(): void
    {
        $draftReq     = $this->createRequisition(['srq_status' => 'Draft', 'srq_salary' => 11000]);
        $inProcessReq = $this->createRequisition(['srq_status' => 'In Process', 'srq_salary' => 22000]);
        $fulfilledReq = $this->createRequisition(['srq_status' => 'Fulfilled', 'srq_salary' => 33000]);
        $cancelledReq = $this->createRequisition(['srq_status' => 'Cancelled', 'srq_salary' => 44000]);

        // Visit index as Division user
        $divResponse = $this->actingAs($this->divUser)
            ->get(route('divhr.salary.requisitions.index'));
        $divResponse->assertStatus(200);

        // Must show "Back to Attendance"
        $divResponse->assertSee('Back to Attendance');
        $divResponse->assertSee(route('divhr.attendance'));

        // Must NOT show Salary Orders or Audit Commitments buttons for Division
        $divResponse->assertDontSee(route('divhr.salary.orders.index'));
        $divResponse->assertDontSee(route('divhr.salary.commitments.verify'));

        // Verify "All" tab and "- All" title are removed, defaults to Draft
        $divResponse->assertSee('Salary Requisitions - Draft');
        $divResponse->assertDontSee('Salary Requisitions - All');

        // Default view opens on Draft tab (All tab removed)
        $divResponse->assertSee('#' . $draftReq->srq_id);
        $divResponse->assertDontSee('#' . $inProcessReq->srq_id);
        $divResponse->assertDontSee('Create Order');

        // Visit index as Finance user
        $finResponse = $this->actingAs($this->finUser)
            ->get(route('divhr.salary.requisitions.index'));
        $finResponse->assertStatus(200);
        $finResponse->assertSee(route('divhr.salary.orders.index'));
        $finResponse->assertSee(route('divhr.salary.commitments.verify'));

        // Visit Orders index as Finance user - defaults to Draft and has no All tab/title
        $ordersResponse = $this->actingAs($this->finUser)
            ->get(route('divhr.salary.orders.index'));
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Salary Orders - Draft');
        $ordersResponse->assertDontSee('Salary Orders - All');
        $ordersResponse->assertDontSee('Salary Orders - Dashboard');

        // Test Status Tabs: Draft
        $draftTabResponse = $this->actingAs($this->divUser)
            ->get(route('divhr.salary.requisitions.index', ['status' => 'Draft']));
        $draftTabResponse->assertStatus(200);
        $draftTabResponse->assertSee('#' . $draftReq->srq_id);
        $draftTabResponse->assertDontSee('#' . $inProcessReq->srq_id);
        $draftTabResponse->assertDontSee('#' . $fulfilledReq->srq_id);

        // Test Status Tabs: Open (maps to In Process)
        $openTabResponse = $this->actingAs($this->divUser)
            ->get(route('divhr.salary.requisitions.index', ['status' => 'Open']));
        $openTabResponse->assertStatus(200);
        $openTabResponse->assertSee('#' . $inProcessReq->srq_id);
        $openTabResponse->assertSee('Submitted to Finance');
        $openTabResponse->assertDontSee('Create Order');
        $openTabResponse->assertDontSee('#' . $draftReq->srq_id);

        // Test Status Tabs: Closed (maps to Fulfilled & Cancelled)
        $closedTabResponse = $this->actingAs($this->divUser)
            ->get(route('divhr.salary.requisitions.index', ['status' => 'Closed']));
        $closedTabResponse->assertStatus(200);
        $closedTabResponse->assertSee('#' . $fulfilledReq->srq_id);
        $closedTabResponse->assertSee('#' . $cancelledReq->srq_id);
        $closedTabResponse->assertDontSee('#' . $draftReq->srq_id);
    }

    /**
     * 5. Attendance view toolbar buttons and Generation modal rendering.
     */
    public function test_attendance_toolbar_buttons_and_generation_modal(): void
    {
        $response = $this->actingAs($this->divUser)
            ->get(route('divhr.attendance', ['month' => '2024-11']));

        $response->assertStatus(200);

        // Toolbar buttons
        $response->assertSee('Generate Salary');
        $response->assertSee('data-target="#generateSalaryModal"', false);
        $response->assertSee('Salary Requisitions');
        $response->assertSee(route('divhr.salary.requisitions.index'));

        // Modal elements
        $response->assertSee('id="generateSalaryModal"', false);
        $response->assertSee('id="att-check-all-emps"', false);
        $response->assertSee('id="att-eligible-tbody"', false);
        $response->assertSee('id="att-btn-submit-generate"', false);
        $response->assertSee(route('divhr.salary.preview'));
        $response->assertSee(route('divhr.salary.requisitions.generate'));
    }

    /**
     * 6. Sidebar navigation renders Salary Requisitions for Division/HR, and Salary Orders only for Finance.
     */
    public function test_sidebar_navigation_by_role(): void
    {
        // Division user sees Salary Requisitions, but NOT Salary Orders
        $divResponse = $this->actingAs($this->divUser)->get(route('divhr.attendance', ['month' => '2024-11']));
        $divResponse->assertStatus(200);
        $divResponse->assertSee(route('divhr.salary.requisitions.index'));
        $divResponse->assertDontSee(route('divhr.salary.orders.index'));

        // Finance user sees both Salary Requisitions and Salary Orders
        $finResponse = $this->actingAs($this->finUser)->get(route('divhr.salary.requisitions.index'));
        $finResponse->assertStatus(200);
        $finResponse->assertSee(route('divhr.salary.requisitions.index'));
        $finResponse->assertSee(route('divhr.salary.orders.index'));
    }

    /**
     * 7. Finance surfaces (Landing, Payments Index, Orders Index, Order Show) render Salary Requisitions buttons.
     */
    public function test_finance_surfaces_render_salary_requisitions_navigation(): void
    {
        // A. Commitments Hub Landing
        $landingResponse = $this->actingAs($this->finUser)->get(route('fin.commitments.landing'));
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee(route('divhr.salary.requisitions.index'));
        $landingResponse->assertSee('Salary Requisitions');

        // B. Commitments Index
        $paymentsResponse = $this->actingAs($this->finUser)->get(route('fin.payments.index'));
        $paymentsResponse->assertStatus(200);
        $paymentsResponse->assertSee(route('divhr.salary.requisitions.index'));
        $paymentsResponse->assertSee('Salary Requisitions');

        // C. Salary Orders Index
        $ordersResponse = $this->actingAs($this->finUser)->get(route('divhr.salary.orders.index'));
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee(route('divhr.salary.requisitions.index'));
        $ordersResponse->assertSee('Salary Requisitions');

        // D. Salary Orders Show
        $req = $this->createRequisition(['srq_status' => 'In Process']);
        $order = $this->createSalaryOrder([
            'sor_srq_id' => $req->srq_id,
            'sor_emp_id' => $req->srq_emp_id,
            'sor_status' => 'Draft',
        ]);

        $orderShowResponse = $this->actingAs($this->finUser)->get(route('divhr.salary.orders.show', $order->sor_id));
        $orderShowResponse->assertStatus(200);
        $orderShowResponse->assertSee(route('divhr.salary.requisitions.index'));
        $orderShowResponse->assertSee('Salary Requisitions');
    }

    /**
     * 8. End-to-End Salary Disbursement from fin_commitments_u_so:
     * Draft Requisition -> Release (creates Draft Order & In Process Req)
     * -> Finance Approve (creates Awaited Commitment & Approved Order)
     * -> fin_commitments_u_so view rendered with top Payment date & Paid button
     * -> Finance clicks Paid with payment_date
     * -> Commitment marked Paid, Order marked Fulfilled, Requisition marked Fulfilled with srq_fulfilment,
     *    and fin.transactions record inserted with negative amount.
     */
    public function test_salary_commitment_disbursement_lifecycle(): void
    {
        [$empId, $ctrId, $unitId, $headId] = $this->createEmployeeInUnit(350000, 'DISB');
        $salMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        // 1. Division generates requisition in Draft
        $req = $this->createRequisition([
            'srq_emp_id'       => $empId,
            'srq_empnamecomp'  => 'Disbursement Test Emp',
            'srq_unt_id'       => $unitId,
            'srq_effunt_id'    => $unitId,
            'srq_effhed_id'    => $headId,
            'srq_month'        => $salMonth,
            'srq_salary'       => 85000,
            'srq_netsalary'    => 85000,
            'srq_contracts'    => (string)$ctrId,
            'srq_status'       => 'Draft',
        ]);

        // 2. Division releases requisition -> creates Draft order and transitions req to In Process
        $releaseResponse = $this->actingAs($this->divUser)
            ->post(route('divhr.salary.requisitions.release', $req->srq_id));
        $releaseResponse->assertSessionHas('success');

        $req->refresh();
        $this->assertEquals('In Process', $req->srq_status);

        $order = FinSalOrder::where('sor_srq_id', $req->srq_id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('Draft', $order->sor_status);
        $this->assertEquals(85000.0, (float)$order->sor_salary);

        // 3. Finance approves order -> creates Awaited commitment and transitions order to Approved
        $approveResponse = $this->actingAs($this->finUser)
            ->post(route('divhr.salary.orders.approve', $order->sor_id));
        $approveResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Approved', $order->sor_status);

        $commitment = DB::table('fin.commitments')
            ->where('cmt_docid', $order->sor_id)
            ->where('cmt_type', 'Sa')
            ->first();
        $this->assertNotNull($commitment);
        $this->assertEquals('Awaited', $commitment->cmt_status);
        $this->assertEquals(-85000.0, (float)$commitment->cmt_amount);

        // 4. View Open Commitments - Salary Orders (fin_commitments_u_so)
        $soCommitmentsResponse = $this->actingAs($this->finUser)
            ->get(route('fin.payments.index', ['type' => 'salary']));
        $soCommitmentsResponse->assertStatus(200);
        $soCommitmentsResponse->assertSee('Open Commitments - Salary Orders');
        $soCommitmentsResponse->assertSee('Payment date:');
        $soCommitmentsResponse->assertSee('#' . $order->sor_id);
        $soCommitmentsResponse->assertSee('Disbursement Test Emp');
        $soCommitmentsResponse->assertSee('Paid');

        // 5. Finance submits 1-click Paid disbursement with payment date
        $paymentDate = Carbon::today()->toDateString();
        $paidResponse = $this->actingAs($this->finUser)
            ->post(route('fin.commitments.salary.pay', $commitment->cmt_id), [
                'payment_date' => $paymentDate,
            ]);
        $paidResponse->assertSessionHas('success');

        // 6. Verify Commitment, Order, and Requisition states
        $updatedCommitment = DB::table('fin.commitments')->where('cmt_id', $commitment->cmt_id)->first();
        $this->assertEquals('Paid', $updatedCommitment->cmt_status);

        $order->refresh();
        $this->assertEquals('Fulfilled', $order->sor_status);
        $this->assertNotNull($order->sor_closedtg);

        $req->refresh();
        $this->assertEquals('Fulfilled', $req->srq_status);
        $this->assertEquals(85000.0, (float)$req->srq_fulfilment);
        $this->assertNotNull($req->srq_closedtg);

        // 7. Verify Transaction entry in fin.transactions
        $transaction = DB::table('fin.transactions')
            ->where('trn_cmt_id', $commitment->cmt_id)
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals($paymentDate, Carbon::parse($transaction->trn_date)->toDateString());
        $this->assertEquals(-85000.0, (float)$transaction->trn_amount1);
        $this->assertEquals(-85000.0, (float)$transaction->trn_amount2);
        $this->assertEquals(0.0, (float)$transaction->trn_tax1);
        $this->assertEquals(0.0, (float)$transaction->trn_balance);
        $this->assertEquals(1, $transaction->trn_seq);
    }

    /**
     * 9. Verify Division vs HR attendance isolation:
     * - Division user accesses /division/attendance and sees only their own division employees.
     * - HR user can view all departments in multi-mode, but employees outside HR department are view-only.
     * - Attempt by HR user to modify another division's employee attendance is rejected with HTTP 403.
     */
    public function test_division_and_hr_attendance_separation_and_read_only_protection(): void
    {
        [$divEmpId, , $divUnit] = $this->createEmployeeInUnit(350000, 'DVE');
        [$hrEmpId, , $hrUnit] = $this->createEmployeeInUnit(820000, 'HRE');

        // Create HR User with own dept scope: 820000..839999 and multi-scope 100000..999999
        $hrUser = CenAccount::where('acc_untarea', 'ILIKE', 'hr')->first() ?? new CenAccount();
        $hrUser->acc_untarea = 'hr';
        $hrUser->acc_access = 'multiple';
        $hrUser->acc_unt_id = 820000;
        $hrUser->acc_lowers = 820000;
        $hrUser->acc_uppers = 839999;
        $hrUser->acc_lowerm = 100000;
        $hrUser->acc_upperm = 999999;
        $hrUser->acc_auth = 'approver';
        $hrUser->save();

        // 2. Division user visiting /division/attendance:
        $divResponse = $this->actingAs($this->divUser)->get(route('division.attendance', ['month' => '2024-11']));
        $divResponse->assertStatus(200);
        $divResponse->assertSee('Attendance');
        $divResponse->assertSee($divEmpId);
        $divResponse->assertDontSee($hrEmpId); // Division cannot see HR or other units

        // 3. HR user visiting /hr/attendance in multi-unit mode ('m'):
        $hrResponse = $this->actingAs($hrUser)->get(route('hr.attendance', ['month' => '2024-11', 'mode' => 'm']));
        $hrResponse->assertStatus(200);
        $hrResponse->assertSee('HR Directorate Attendance');
        $hrResponse->assertSee($divEmpId); // HR sees other division employees
        $hrResponse->assertSee($hrEmpId);  // HR sees own department employees
        $hrResponse->assertSee('View Only'); // Other division employee has "View Only" badge

        // 4. HR user tries to save attendance for division employee -> REJECTED with 403
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Services\AttendanceService::class)->saveAttendance($hrUser, '2024-11', [
            ['emp_id' => $divEmpId, 'day' => 5, 'val' => 'P'],
        ]);
    }
}


