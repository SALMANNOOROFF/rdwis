<?php

namespace Tests\Feature\Auth;

use App\Exceptions\MissingScopeContextException;
use App\Exceptions\UnauthorizedScopeException;
use App\Models\CenAccount;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Auth\HorizonScopeContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExplicitUserScopingTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        HorizonScopeContext::reset();
        parent::tearDown();
    }

    /**
     * Test (a): An explicit-scope call correctly restricts results to the given user's
     * authorized division/unit, even when running in console context where horizon scope was formerly skipped.
     */
    public function test_explicit_scope_restricts_queries_to_user_authorized_unit_in_console(): void
    {
        // Setup two distinct division cases
        $unitA = 200000;
        $unitB = 350000;

        $caseA = new Purchase();
        $caseA->pcs_title = 'Division 200k Test Case';
        $caseA->pcs_date = now()->toDateString();
        $caseA->pcs_unt_id = $unitA;
        $caseA->pcs_effunt_id = $unitA;
        $caseA->pcs_intunt_id = $unitA;
        $caseA->pcs_effhed_id = 200001;
        $caseA->pcs_transtype = 1;
        $caseA->pcs_type = 'Ps';
        $caseA->pcs_status = 'Draft';
        $caseA->save();

        $caseB = new Purchase();
        $caseB->pcs_title = 'Division 350k Test Case';
        $caseB->pcs_date = now()->toDateString();
        $caseB->pcs_unt_id = $unitB;
        $caseB->pcs_effunt_id = $unitB;
        $caseB->pcs_intunt_id = $unitB;
        $caseB->pcs_effhed_id = 350001;
        $caseB->pcs_transtype = 1;
        $caseB->pcs_type = 'Ps';
        $caseB->pcs_status = 'Draft';
        $caseB->save();

        // Division User A: authorized ONLY for unit 200000
        $userA = new CenAccount();
        $userA->acc_username = 'div_user_200k_' . uniqid();
        $userA->acc_unt_id = $unitA;
        $userA->acc_lowerm = $unitA;
        $userA->acc_upperm = $unitA;
        $userA->acc_lowers = $unitA;
        $userA->acc_uppers = $unitA;
        $userA->acc_untarea = 'prj';
        $userA->acc_auth = 'editor';
        $userA->acc_status = 'Active';

        // Division User B: authorized ONLY for unit 350000
        $userB = new User();
        $userB->acc_username = 'div_user_350k_' . uniqid();
        $userB->acc_unt_id = $unitB;
        $userB->acc_lowerm = $unitB;
        $userB->acc_upperm = $unitB;
        $userB->acc_lowers = $unitB;
        $userB->acc_uppers = $unitB;
        $userB->acc_untarea = 'prj';
        $userB->acc_auth = 'editor';
        $userB->acc_status = 'Active';

        // Execute as User A inside explicit scope
        $resultA = HorizonScopeContext::runAs($userA, function () {
            return Purchase::all();
        });

        // Must see Case A and NOT see Case B
        $this->assertTrue($resultA->contains('pcs_id', $caseA->pcs_id), 'User A must see Case A in authorized unit 200000.');
        $this->assertFalse($resultA->contains('pcs_id', $caseB->pcs_id), 'User A must NOT see Case B in unit 350000.');

        // Execute as User B inside explicit scope
        $resultB = HorizonScopeContext::runAs($userB, function () {
            return Purchase::all();
        });

        // Must see Case B and NOT see Case A
        $this->assertTrue($resultB->contains('pcs_id', $caseB->pcs_id), 'User B must see Case B in authorized unit 350000.');
        $this->assertFalse($resultB->contains('pcs_id', $caseA->pcs_id), 'User B must NOT see Case A in unit 200000.');
    }

    /**
     * Test (b): A call made without valid scope context is rejected rather than silently
     * returning unscoped data when explicit scope enforcement is active.
     */
    public function test_query_rejected_when_explicit_scope_enforcement_active_without_scope(): void
    {
        $this->expectException(MissingScopeContextException::class);
        $this->expectExceptionMessage('Explicit scope context is required');

        HorizonScopeContext::requireExplicitScope(function () {
            // No runAs or withExplicitScope is active here
            Purchase::all();
        });
    }

    /**
     * Test (c): Passing null to runAs throws MissingScopeContextException immediately.
     */
    public function test_run_as_with_null_user_throws_missing_scope_exception(): void
    {
        $this->expectException(MissingScopeContextException::class);
        $this->expectExceptionMessage('Explicit user context is required but null was provided.');

        HorizonScopeContext::runAs(null, function () {
            return Purchase::all();
        });
    }

    /**
     * Test (d): Inactive user account is rejected with UnauthorizedScopeException.
     */
    public function test_run_as_with_inactive_user_throws_unauthorized_scope_exception(): void
    {
        $inactiveUser = new CenAccount();
        $inactiveUser->acc_username = 'deactivated_user';
        $inactiveUser->acc_status = 'Disabled';

        $this->expectException(UnauthorizedScopeException::class);
        $this->expectExceptionMessage("User account 'deactivated_user' is not active.");

        HorizonScopeContext::runAs($inactiveUser, function () {
            return Purchase::all();
        });
    }

    /**
     * Test (e): Scope stack cleanly restores previous state even when callback throws an exception.
     */
    public function test_scope_stack_restores_cleanly_on_exception(): void
    {
        $user = new CenAccount();
        $user->acc_username = 'test_user_' . uniqid();
        $user->acc_unt_id = 200000;
        $user->acc_lowerm = 200000;
        $user->acc_upperm = 200000;
        $user->acc_status = 'Active';

        $this->assertFalse(HorizonScopeContext::hasActiveScope());

        try {
            HorizonScopeContext::runAs($user, function () {
                $this->assertTrue(HorizonScopeContext::hasActiveScope());
                throw new \RuntimeException('Intentional test exception');
            });
        } catch (\RuntimeException $e) {
            $this->assertEquals('Intentional test exception', $e->getMessage());
        }

        $this->assertFalse(HorizonScopeContext::hasActiveScope(), 'Scope must be popped from stack despite exception.');
    }
}
