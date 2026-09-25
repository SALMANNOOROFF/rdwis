<?php

namespace Tests\Feature\Ai;

use App\Exceptions\AiToolRecordNotFoundException;
use App\Exceptions\MissingScopeContextException;
use App\Exceptions\UnauthorizedScopeException;
use App\Models\CenAccount;
use App\Models\PurCaseSubstatus;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AiToolRegistry;
use App\Services\Auth\HorizonScopeContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class AiToolRegistryTest extends TestCase
{
    use DatabaseTransactions;

    protected AiToolRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new AiToolRegistry();
    }

    protected function tearDown(): void
    {
        HorizonScopeContext::reset();
        parent::tearDown();
    }

    /**
     * Test 1: Tool Map registers Category 1A, 3A, 5A tools with JSON Schema parameters.
     */
    public function test_tool_map_contains_safe_registry_and_schema(): void
    {
        $toolMap = AiToolRegistry::getToolMap();

        $this->assertArrayHasKey('getPurchaseCaseStatus', $toolMap);
        $this->assertArrayHasKey('getChequeDetails', $toolMap);
        $this->assertArrayHasKey('getAttendanceSummary', $toolMap);

        $this->assertEquals('1A', $toolMap['getPurchaseCaseStatus']['category']);
        $this->assertTrue($toolMap['getPurchaseCaseStatus']['read_only']);
        $this->assertArrayHasKey('caseId', $toolMap['getPurchaseCaseStatus']['parameters']['properties']);

        $this->assertEquals('3A', $toolMap['getChequeDetails']['category']);
        $this->assertTrue($toolMap['getChequeDetails']['read_only']);
        $this->assertArrayHasKey('chequeNumber', $toolMap['getChequeDetails']['parameters']['properties']);

        $this->assertEquals('5A', $toolMap['getAttendanceSummary']['category']);
        $this->assertTrue($toolMap['getAttendanceSummary']['read_only']);
        $this->assertArrayHasKey('employeeId', $toolMap['getAttendanceSummary']['parameters']['properties']);
        $this->assertArrayHasKey('month', $toolMap['getAttendanceSummary']['parameters']['properties']);

        $this->assertEquals(['getPurchaseCaseStatus', 'getChequeDetails', 'getAttendanceSummary'], AiToolRegistry::getAllowedTools());
    }

    /**
     * Helper to create a division user.
     */
    protected function createDivisionUser(int $unitId, string $area = 'prj'): CenAccount
    {
        $existing = CenAccount::where('acc_unt_id', $unitId)
            ->where('acc_untarea', $area)
            ->whereRaw("LOWER(acc_status) = 'active'")
            ->first();

        if ($existing) {
            return $existing;
        }

        $user = new CenAccount();
        $user->acc_username = 'div_' . $unitId . '_' . uniqid();
        $user->acc_name = 'Test User ' . $unitId;
        $user->acc_pass = 'test_hash';
        $user->acc_level = 1;
        $user->acc_type = 'User';
        $user->acc_startdt = now();
        $user->acc_desig = 'Director';
        $user->acc_desigshort = 'DIR';
        $user->acc_desigtype = 'lead';
        $user->acc_access = 'single';
        $user->acc_untname = 'Unit ' . $unitId;
        $user->acc_untnamesh = 'U' . $unitId;
        $user->acc_unttype = 'Division';
        $user->acc_unt_id = $unitId;
        $user->acc_lowerm = $unitId;
        $user->acc_upperm = $unitId;
        $user->acc_lowers = $unitId;
        $user->acc_uppers = $unitId;
        $user->acc_untarea = $area;
        $user->acc_auth = 'editor';
        $user->acc_status = 'Active';
        $user->save();

        return $user;
    }

    /**
     * Helper to create a purchase case in a specific unit.
     */
    protected function createPurchaseCase(int $unitId, string $stage = 'DFinance'): Purchase
    {
        $case = new Purchase();
        $case->pcs_title = 'AI Tool Test Case ' . uniqid();
        $case->pcs_date = now()->toDateString();
        $case->pcs_unt_id = $unitId;
        $case->pcs_effunt_id = $unitId;
        $case->pcs_intunt_id = $unitId;
        $case->pcs_effhed_id = ($unitId === 200000) ? 200001 : 350001;
        $case->pcs_transtype = 1;
        $case->pcs_type = 'Ps';
        $case->pcs_status = 'Under Approval';
        $case->pcs_price = 150000;
        $case->save();

        // Create substatus
        $sub = new PurCaseSubstatus();
        $sub->pss_pcs_id = $case->pcs_id;
        $sub->pss_stage = $stage;
        $sub->pss_is_current = true;
        $sub->pss_since = now();
        $sub->save();

        return $case;
    }

    // =========================================================================
    // Category 1A: getPurchaseCaseStatus
    // =========================================================================

    /**
     * Test 2: Authorized user retrieves purchase case status successfully.
     */
    public function test_get_purchase_case_status_for_authorized_user(): void
    {
        $user = $this->createDivisionUser(200000);
        $case = $this->createPurchaseCase(200000, 'DFinance');

        $result = $this->registry->getPurchaseCaseStatus($case->pcs_id, $user);

        $this->assertIsArray($result);
        $this->assertEquals($case->pcs_id, $result['case_id']);
        $this->assertEquals('Under Approval', $result['status']);
        $this->assertEquals('DFinance', $result['current_stage']);
        $this->assertEquals('Director Finance', $result['stage_display']);
        $this->assertEquals(200000, $result['unit_id']);
        $this->assertEquals(150000.0, $result['price']);
    }

    /**
     * Test 3: Cross-division user is rejected with UnauthorizedScopeException.
     */
    public function test_get_purchase_case_status_throws_for_cross_division_user(): void
    {
        $userUnit350 = $this->createDivisionUser(350000);
        $caseUnit200 = $this->createPurchaseCase(200000, 'DFinance');

        $this->expectException(UnauthorizedScopeException::class);
        $this->expectExceptionMessage("not authorized to access purchase case #{$caseUnit200->pcs_id} in unit 200000");

        $this->registry->getPurchaseCaseStatus($caseUnit200->pcs_id, $userUnit350);
    }

    /**
     * Test 4: Non-existent purchase case throws AiToolRecordNotFoundException.
     */
    public function test_get_purchase_case_status_throws_for_non_existent_case(): void
    {
        $user = $this->createDivisionUser(200000);

        $this->expectException(AiToolRecordNotFoundException::class);
        $this->expectExceptionMessage('Purchase case #99999999 does not exist.');

        $this->registry->getPurchaseCaseStatus(99999999, $user);
    }

    // =========================================================================
    // Category 3A: getChequeDetails
    // =========================================================================

    /**
     * Helper to create commitment and transaction for cheque tests.
     */
    protected function createPaymentRecord(int $unitId, string $chequeRemark): array
    {
        $headId = ($unitId === 200000) ? 200001 : 350001;
        $maxDocId = (int) DB::table('fin.commitments')->where('cmt_type', 'Ps')->max('cmt_docid');
        $docId = $maxDocId + rand(100, 9999);

        $cmtId = DB::table('fin.commitments')->insertGetId([
            'cmt_docid'     => $docId,
            'cmt_type'      => 'Ps',
            'cmt_date'      => now()->toDateString(),
            'cmt_amount'    => -85000,
            'cmt_status'    => 'Paid',
            'cmt_effhed_id' => $headId,
            'cmt_effunt_id' => $unitId,
            'cmt_unt_id'    => $unitId,
            'cmt_remarks'   => $chequeRemark,
        ], 'cmt_id');

        $trnId = DB::table('fin.transactions')->insertGetId([
            'trn_cmt_id'    => $cmtId,
            'trn_date'      => now()->toDateString(),
            'trn_amount1'   => -80000,
            'trn_balance'   => 0,
            'trn_seq'       => 1,
            'trn_tax1'      => -5000,
            'trn_amount2'   => -85000,
            'trn_transtype' => 1,
        ], 'trn_id');

        return ['cmt_id' => $cmtId, 'trn_id' => $trnId];
    }

    /**
     * Test 5: Authorized user retrieves cheque and transaction details successfully.
     */
    public function test_get_cheque_details_for_authorized_user(): void
    {
        $chqNum = 'CHQ-' . uniqid();
        $this->createPaymentRecord(200000, "Paid by cheque {$chqNum}");
        $user = $this->createDivisionUser(200000);

        $result = $this->registry->getChequeDetails($chqNum, $user);

        $this->assertIsArray($result);
        $this->assertEquals($chqNum, $result['cheque_number']);
        $this->assertEquals(85000.0, $result['amount']);
        $this->assertEquals(80000.0, $result['net_amount']);
        $this->assertEquals(5000.0, $result['tax']);
        $this->assertEquals('Paid', $result['status']);
        $this->assertEquals(200000, $result['unit_id']);
        $this->assertStringContainsString($chqNum, $result['remarks']);
    }

    /**
     * Test 6: Cross-division user looking at another division's payment is rejected.
     */
    public function test_get_cheque_details_throws_for_cross_division_user(): void
    {
        $chqNum = 'CHQ-CROSS-' . uniqid();
        $this->createPaymentRecord(200000, "Cheque reference {$chqNum}");

        // User belongs to Unit 350000, not 200000
        $userUnit350 = $this->createDivisionUser(350000);

        $this->expectException(UnauthorizedScopeException::class);
        $this->expectExceptionMessage('not authorized to access payment/cheque record outside their division scope');

        $this->registry->getChequeDetails($chqNum, $userUnit350);
    }

    /**
     * Test 7: Non-existent cheque reference throws AiToolRecordNotFoundException.
     */
    public function test_get_cheque_details_throws_for_non_existent_cheque(): void
    {
        $user = $this->createDivisionUser(200000);

        $this->expectException(AiToolRecordNotFoundException::class);
        $this->expectExceptionMessage("No cheque or payment transaction found matching 'NON_EXISTENT_999'");

        $this->registry->getChequeDetails('NON_EXISTENT_999', $user);
    }

    // =========================================================================
    // Category 5A: getAttendanceSummary
    // =========================================================================

    /**
     * Helper to create test employee and attendance records.
     */
    protected function createEmployeeWithAttendance(int $unitId): string
    {
        $empId = '99-' . rand(10, 99) . '-' . rand(10, 99) . '-' . rand(1000, 9999);

        DB::table('hr.emps')->insert([
            'emp_id'     => $empId,
            'emp_cnic'   => '42101-' . rand(1000000, 9999999) . '-1',
            'emp_name'   => 'AI Test Employee ' . uniqid(),
            'emp_joindt' => '2024-01-01',
            'emp_status' => 'Active',
            'emp_unt_id' => $unitId,
        ]);

        $days = [];
        for ($i = 1; $i <= 31; $i++) {
            $days['att_' . $i] = ($i % 7 === 0) ? 'Z' : 'P';
        }

        DB::table('hr.attendance')->insert(array_merge([
            'att_emp_id'      => $empId,
            'att_empnamecomp' => 'AI Test Employee',
            'att_unt_id'      => $unitId,
            'att_startdt'     => '2024-08-01',
            'att_enddt'       => '2024-08-31',
            'att_locked1'     => false,
            'att_locked2'     => false,
        ], $days));

        return $empId;
    }

    /**
     * Test 8: Authorized supervisor or HR user retrieves attendance summary.
     */
    public function test_get_attendance_summary_for_authorized_user(): void
    {
        $empId = $this->createEmployeeWithAttendance(200000);
        $supervisor = $this->createDivisionUser(200000);

        $result = $this->registry->getAttendanceSummary($empId, '2024-08', $supervisor);

        $this->assertIsArray($result);
        $this->assertEquals($empId, $result['employee_id']);
        $this->assertEquals(200000, $result['unit_id']);
        $this->assertEquals('2024-08', $result['month']);
        $this->assertEquals('2024-08-01', $result['start_date']);
        $this->assertEquals('2024-08-31', $result['end_date']);
        $this->assertGreaterThan(0, $result['present']);
        $this->assertArrayHasKey('counts', $result);
    }

    /**
     * Test 9: Employee can query their own attendance.
     */
    public function test_employee_can_query_own_attendance(): void
    {
        $empId = $this->createEmployeeWithAttendance(200000);

        // User account whose username matches empId
        $selfUser = new CenAccount();
        $selfUser->acc_username = $empId;
        $selfUser->acc_name = 'Self Employee';
        $selfUser->acc_unt_id = 200000;
        $selfUser->acc_untarea = 'prj';
        $selfUser->acc_auth = 'viewer';
        $selfUser->acc_status = 'Active';

        $result = $this->registry->getAttendanceSummary($empId, '2024-08', $selfUser);

        $this->assertEquals($empId, $result['employee_id']);
    }

    /**
     * Test 10: Unauthorized cross-division non-HR user is rejected.
     */
    public function test_get_attendance_summary_throws_for_unauthorized_cross_division_user(): void
    {
        $empId = $this->createEmployeeWithAttendance(200000);
        $crossUser = $this->createDivisionUser(350000);

        $this->expectException(UnauthorizedScopeException::class);
        $this->expectExceptionMessage("not authorized to query attendance for employee '{$empId}'");

        $this->registry->getAttendanceSummary($empId, '2024-08', $crossUser);
    }

    /**
     * Test 11: Non-existent employee throws AiToolRecordNotFoundException.
     */
    public function test_get_attendance_summary_throws_for_non_existent_employee(): void
    {
        $user = $this->createDivisionUser(200000);

        $this->expectException(AiToolRecordNotFoundException::class);
        $this->expectExceptionMessage("Employee 'INVALID-99-99-9999' does not exist");

        $this->registry->getAttendanceSummary('INVALID-99-99-9999', '2024-08', $user);
    }

    // =========================================================================
    // Security & Bypass Prevention: Calling without valid User/Scope context
    // =========================================================================

    /**
     * Test 12: Tool methods cannot be bypassed by calling directly with null acting user.
     */
    public function test_tool_methods_reject_calls_with_null_acting_user(): void
    {
        $methods = [
            fn() => $this->registry->getPurchaseCaseStatus(1, null),
            fn() => $this->registry->getChequeDetails('CHQ-123', null),
            fn() => $this->registry->getAttendanceSummary('101', '2024-08', null),
            fn() => $this->registry->invoke('getPurchaseCaseStatus', ['caseId' => 1], null),
        ];

        foreach ($methods as $index => $callable) {
            try {
                $callable();
                $this->fail("Method call #{$index} must throw MissingScopeContextException when acting user is null.");
            } catch (MissingScopeContextException $e) {
                $this->assertStringContainsString('acting user context is required', $e->getMessage());
            }
        }
    }

    /**
     * Test 13: Inactive acting user is rejected across all tools.
     */
    public function test_tool_methods_reject_inactive_acting_user(): void
    {
        $inactive = new CenAccount();
        $inactive->acc_username = 'disabled_officer';
        $inactive->acc_status = 'Disabled';

        $this->expectException(UnauthorizedScopeException::class);
        $this->expectExceptionMessage("Acting user account 'disabled_officer' is not active.");

        $this->registry->getPurchaseCaseStatus(1, $inactive);
    }

    /**
     * Test 14: Dynamic invoke dispatches registered tool and respects scoping.
     */
    public function test_invoke_dispatches_registered_tool_correctly(): void
    {
        $user = $this->createDivisionUser(200000);
        $case = $this->createPurchaseCase(200000, 'MD');

        $result = $this->registry->invoke('getPurchaseCaseStatus', ['caseId' => $case->pcs_id], $user);

        $this->assertEquals($case->pcs_id, $result['case_id']);
        $this->assertEquals('MD', $result['current_stage']);
    }

    /**
     * Test 15: invoke rejects unknown tool names.
     */
    public function test_invoke_rejects_unregistered_tool(): void
    {
        $user = $this->createDivisionUser(200000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("AI tool 'unregisteredTool' is not registered");

        $this->registry->invoke('unregisteredTool', [], $user);
    }
}
