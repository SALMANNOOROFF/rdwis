<?php

namespace Tests\Feature;

use App\Models\AudRev;
use App\Models\CenAccount;
use App\Models\Unit;
use App\Policies\DataRevisionPolicy;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DataRevisionPolicyTest extends TestCase
{
    use DatabaseTransactions;

    protected DataScopeService $scopeService;
    protected DataRevisionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeService = new DataScopeService();
        $this->policy = new DataRevisionPolicy($this->scopeService);
    }

    /**
     * Helper to build a transient CenAccount for testing.
     */
    protected function makeAccount(
        string $username,
        int $unitId,
        string $area,
        string $desig,
        string $desigShort,
        string $desigType = 'lead',
        string $auth = 'approver',
        string $status = 'Active'
    ): CenAccount {
        $acc = new CenAccount();
        $acc->acc_username = $username;
        $acc->acc_unt_id = $unitId;
        $acc->acc_untarea = $area;
        $acc->acc_desig = $desig;
        $acc->acc_desigshort = $desigShort;
        $acc->acc_desigtype = $desigType;
        $acc->acc_auth = $auth;
        $acc->acc_status = $status;
        $acc->acc_lowers = $unitId;
        $acc->acc_uppers = $unitId;
        return $acc;
    }

    /**
     * Test 1: Reversal permissions are correctly registered in PermissionRegistry.
     */
    public function test_reversal_permissions_registered_in_permission_registry(): void
    {
        $this->assertSame('reversal.view', PermissionRegistry::REVERSAL_VIEW);
        $this->assertSame('reversal.initiate', PermissionRegistry::REVERSAL_INITIATE);
        $this->assertSame('reversal.release', PermissionRegistry::REVERSAL_RELEASE);
        $this->assertSame('reversal.execute', PermissionRegistry::REVERSAL_EXECUTE);
        $this->assertSame('reversal.return', PermissionRegistry::REVERSAL_RETURN);
        $this->assertSame('reversal.cancel', PermissionRegistry::REVERSAL_CANCEL);

        $all = PermissionRegistry::all();
        $this->assertContains(PermissionRegistry::REVERSAL_VIEW, $all);
        $this->assertContains(PermissionRegistry::REVERSAL_INITIATE, $all);
        $this->assertContains(PermissionRegistry::REVERSAL_RELEASE, $all);
        $this->assertContains(PermissionRegistry::REVERSAL_EXECUTE, $all);
        $this->assertContains(PermissionRegistry::REVERSAL_RETURN, $all);
        $this->assertContains(PermissionRegistry::REVERSAL_CANCEL, $all);
    }

    /**
     * Test 2: RolePermissionMap correctly maps reversal permissions to semantic roles.
     */
    public function test_role_permission_mappings_for_reversal_system(): void
    {
        // IT Admin (unit 860000, lead -> IT_ADMIN)
        $itAdmin = $this->makeAccount('test_itadmin', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');
        $this->assertSame('IT_ADMIN', UserAccessContext::forUser($itAdmin)->getRoleSlug());
        $this->assertTrue(RolePermissionMap::hasPermission($itAdmin, PermissionRegistry::REVERSAL_VIEW));
        $this->assertTrue(RolePermissionMap::hasPermission($itAdmin, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertTrue(RolePermissionMap::hasPermission($itAdmin, PermissionRegistry::REVERSAL_RELEASE));
        $this->assertTrue(RolePermissionMap::hasPermission($itAdmin, PermissionRegistry::REVERSAL_EXECUTE));
        $this->assertTrue(RolePermissionMap::hasPermission($itAdmin, PermissionRegistry::REVERSAL_RETURN));
        $this->assertTrue(RolePermissionMap::hasPermission($itAdmin, PermissionRegistry::REVERSAL_CANCEL));

        // IT Officer (unit 860000, staff -> IT_OFFICER)
        $itOfficer = $this->makeAccount('test_itofficer', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'staff', 'approver');
        $this->assertSame('IT_OFFICER', UserAccessContext::forUser($itOfficer)->getRoleSlug());
        $this->assertTrue(RolePermissionMap::hasPermission($itOfficer, PermissionRegistry::REVERSAL_VIEW));
        $this->assertTrue(RolePermissionMap::hasPermission($itOfficer, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertTrue(RolePermissionMap::hasPermission($itOfficer, PermissionRegistry::REVERSAL_RELEASE));
        $this->assertTrue(RolePermissionMap::hasPermission($itOfficer, PermissionRegistry::REVERSAL_EXECUTE));
        $this->assertTrue(RolePermissionMap::hasPermission($itOfficer, PermissionRegistry::REVERSAL_RETURN));
        $this->assertTrue(RolePermissionMap::hasPermission($itOfficer, PermissionRegistry::REVERSAL_CANCEL));

        // Division Director (unit 200000)
        $divDir = $this->makeAccount('test_divdir', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');
        $this->assertTrue(RolePermissionMap::hasPermission($divDir, PermissionRegistry::REVERSAL_VIEW));
        $this->assertTrue(RolePermissionMap::hasPermission($divDir, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertTrue(RolePermissionMap::hasPermission($divDir, PermissionRegistry::REVERSAL_RELEASE));
        $this->assertTrue(RolePermissionMap::hasPermission($divDir, PermissionRegistry::REVERSAL_CANCEL));
        $this->assertFalse(RolePermissionMap::hasPermission($divDir, PermissionRegistry::REVERSAL_EXECUTE));
        $this->assertFalse(RolePermissionMap::hasPermission($divDir, PermissionRegistry::REVERSAL_RETURN));

        // Division Officer (unit 200000, staff)
        $divOfficer = $this->makeAccount('test_divoff', 200000, 'prj', 'Communication Officer', 'OCom', 'staff', 'editor');
        $this->assertTrue(RolePermissionMap::hasPermission($divOfficer, PermissionRegistry::REVERSAL_VIEW));
        $this->assertTrue(RolePermissionMap::hasPermission($divOfficer, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertFalse(RolePermissionMap::hasPermission($divOfficer, PermissionRegistry::REVERSAL_RELEASE));
        $this->assertFalse(RolePermissionMap::hasPermission($divOfficer, PermissionRegistry::REVERSAL_EXECUTE));
        $this->assertFalse(RolePermissionMap::hasPermission($divOfficer, PermissionRegistry::REVERSAL_RETURN));

        // Finance Director (unit 800000)
        $finDir = $this->makeAccount('test_findir', 800000, 'fin', 'Director Finance', 'DFin', 'lead', 'approver');
        $this->assertTrue(RolePermissionMap::hasPermission($finDir, PermissionRegistry::REVERSAL_VIEW));
        $this->assertTrue(RolePermissionMap::hasPermission($finDir, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertTrue(RolePermissionMap::hasPermission($finDir, PermissionRegistry::REVERSAL_RELEASE));
        $this->assertTrue(RolePermissionMap::hasPermission($finDir, PermissionRegistry::REVERSAL_CANCEL));
        $this->assertFalse(RolePermissionMap::hasPermission($finDir, PermissionRegistry::REVERSAL_EXECUTE));
        $this->assertFalse(RolePermissionMap::hasPermission($finDir, PermissionRegistry::REVERSAL_RETURN));

        // Procurement Director (unit 810000)
        $procDir = $this->makeAccount('test_procdir', 810000, 'prc', 'Director Procurement', 'DProc', 'lead', 'approver');
        $this->assertTrue(RolePermissionMap::hasPermission($procDir, PermissionRegistry::REVERSAL_VIEW));
        $this->assertTrue(RolePermissionMap::hasPermission($procDir, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertTrue(RolePermissionMap::hasPermission($procDir, PermissionRegistry::REVERSAL_RELEASE));
        $this->assertTrue(RolePermissionMap::hasPermission($procDir, PermissionRegistry::REVERSAL_CANCEL));
        $this->assertFalse(RolePermissionMap::hasPermission($procDir, PermissionRegistry::REVERSAL_EXECUTE));

        // Command Officer (DG)
        $dg = $this->makeAccount('test_dg', 100000, 'nrdi', 'Director General NRDI', 'DG NRDI', 'head', 'viewer');
        $this->assertTrue(RolePermissionMap::hasPermission($dg, PermissionRegistry::REVERSAL_VIEW));
        $this->assertFalse(RolePermissionMap::hasPermission($dg, PermissionRegistry::REVERSAL_INITIATE));
        $this->assertFalse(RolePermissionMap::hasPermission($dg, PermissionRegistry::REVERSAL_EXECUTE));
    }

    /**
     * Test 3: Viewer accounts have all mutating reversal permissions stripped.
     */
    public function test_viewer_accounts_have_reversal_mutations_stripped(): void
    {
        $viewer = $this->makeAccount('test_viewer', 200000, 'prj', 'Division Viewer', 'Viewer', 'staff', 'viewer');
        $permissions = RolePermissionMap::resolvePermissionsFor($viewer);

        $this->assertContains(PermissionRegistry::REVERSAL_VIEW, $permissions);
        $this->assertNotContains(PermissionRegistry::REVERSAL_INITIATE, $permissions);
        $this->assertNotContains(PermissionRegistry::REVERSAL_RELEASE, $permissions);
        $this->assertNotContains(PermissionRegistry::REVERSAL_EXECUTE, $permissions);
        $this->assertNotContains(PermissionRegistry::REVERSAL_RETURN, $permissions);
        $this->assertNotContains(PermissionRegistry::REVERSAL_CANCEL, $permissions);

        // Policy initiate returns false for viewer
        $this->assertFalse($this->policy->initiate($viewer));
        $this->assertFalse($this->policy->create($viewer));
    }

    /**
     * Test 4: View policy enforces unit scope and functional domain visibility.
     */
    public function test_policy_view_scope_and_domain_guards(): void
    {
        $revUnit200 = new AudRev();
        $revUnit200->rev_id = 9001;
        $revUnit200->rev_unt_id = 200000;
        $revUnit200->rev_intunt_id = 200000;
        $revUnit200->rev_obj = 'Purchase Case';
        $revUnit200->rev_status = 'In Process';

        // 1. IT user (unit 860000) can view any revision
        $itUser = $this->makeAccount('test_it', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');
        $this->assertTrue($this->policy->viewAny($itUser));
        $this->assertTrue($this->policy->view($itUser, $revUnit200));

        // 2. Division user belonging to unit 200000 CAN view
        $div200User = $this->makeAccount('test_div200', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');
        $this->assertTrue($this->policy->view($div200User, $revUnit200));

        // 3. Division user belonging to unit 300000 CANNOT view unit 200000 Purchase Case
        $div300User = $this->makeAccount('test_div300', 300000, 'prj', 'Director Sensors', 'DSens', 'lead', 'approver');
        $this->assertFalse($this->policy->view($div300User, $revUnit200));

        // 4. Central Procurement user CAN view Purchase Case even if from unit 200000
        $procUser = $this->makeAccount('test_proc', 810000, 'prc', 'Director Procurement', 'DProc', 'lead', 'approver');
        $this->assertTrue($this->policy->view($procUser, $revUnit200));

        // 5. Central Finance user CAN view Salary / Commitment revision from unit 200000
        $salRev = new AudRev();
        $salRev->rev_id = 9002;
        $salRev->rev_unt_id = 200000;
        $salRev->rev_intunt_id = 200000;
        $salRev->rev_obj = 'Salary Order';
        $salRev->rev_status = 'In Process';

        $finUser = $this->makeAccount('test_fin', 800000, 'fin', 'Director Finance', 'DFin', 'lead', 'approver');
        $this->assertTrue($this->policy->view($finUser, $salRev));

        // 6. Central HR user CAN view Employee revision from unit 200000
        $empRev = new AudRev();
        $empRev->rev_id = 9003;
        $empRev->rev_unt_id = 200000;
        $empRev->rev_intunt_id = 200000;
        $empRev->rev_obj = 'Employee';
        $empRev->rev_status = 'Draft';

        $hrUser = $this->makeAccount('test_hr', 820000, 'hr', 'Manager HR', 'MHR', 'lead', 'approver');
        $this->assertTrue($this->policy->view($hrUser, $empRev));
    }

    /**
     * Test 5: Release requires approver role, initiating unit ownership, and draft/under-revision status.
     */
    public function test_policy_release_guards(): void
    {
        $revDraft = new AudRev();
        $revDraft->rev_id = 9101;
        $revDraft->rev_unt_id = 200000;
        $revDraft->rev_intunt_id = 200000;
        $revDraft->rev_status = 'Draft';

        $divDir200 = $this->makeAccount('test_divdir200', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');
        $divOff200 = $this->makeAccount('test_divoff200', 200000, 'prj', 'Officer Communication', 'OCom', 'staff', 'editor');
        $divDir300 = $this->makeAccount('test_divdir300', 300000, 'prj', 'Director Sensors', 'DSens', 'lead', 'approver');

        // Approver in unit 200000 CAN release Draft
        $this->assertTrue($this->policy->release($divDir200, $revDraft));

        // Editor in unit 200000 CANNOT release (must be approver)
        $this->assertFalse($this->policy->release($divOff200, $revDraft));

        // Approver in unit 300000 CANNOT release unit 200000 revision
        $this->assertFalse($this->policy->release($divDir300, $revDraft));

        // Under Revision status CAN be released (re-released after return from IT)
        $revDraft->rev_status = 'Under Revision';
        $this->assertTrue($this->policy->release($divDir200, $revDraft));

        // Already In Process / Released CANNOT be released again
        $revDraft->rev_status = 'In Process';
        $this->assertFalse($this->policy->release($divDir200, $revDraft));

        // Already Fulfilled CANNOT be released
        $revDraft->rev_status = 'Fulfilled';
        $this->assertFalse($this->policy->release($divDir200, $revDraft));
    }

    /**
     * Test 6: Execute is strictly restricted to Unit 860000 / IT approver or SuperAdmin.
     */
    public function test_policy_execute_strictly_restricted_to_it_approver(): void
    {
        $rev = new AudRev();
        $rev->rev_id = 9201;
        $rev->rev_unt_id = 200000;
        $rev->rev_intunt_id = 200000;
        $rev->rev_status = 'In Process';

        $itApprover = $this->makeAccount('test_itappr', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');
        $itEditor = $this->makeAccount('test_itedit', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'staff', 'editor');
        $divApprover = $this->makeAccount('test_divappr', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');
        $finApprover = $this->makeAccount('test_finappr', 800000, 'fin', 'Director Finance', 'DFin', 'lead', 'approver');

        // IT Approver CAN execute
        $this->assertTrue($this->policy->execute($itApprover, $rev));

        // IT Editor CANNOT execute (requires approver)
        $this->assertFalse($this->policy->execute($itEditor, $rev));

        // Non-IT Approvers (Division, Finance) CANNOT execute
        $this->assertFalse($this->policy->execute($divApprover, $rev));
        $this->assertFalse($this->policy->execute($finApprover, $rev));

        // SuperAdmin CAN execute
        $superadmin = $this->makeAccount('superadminrdw', 860000, 'it', 'System Administrator', 'SysAdmin', 'superadmin', 'approver');
        $this->assertTrue($this->policy->execute($superadmin, $rev));
    }

    /**
     * Test 7: Execute blocks execution of unreleased external drafts or already fulfilled/cancelled revisions.
     */
    public function test_policy_execute_status_guards(): void
    {
        $itApprover = $this->makeAccount('test_itappr', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');

        $rev = new AudRev();
        $rev->rev_id = 9301;
        $rev->rev_unt_id = 200000;
        $rev->rev_intunt_id = 200000;

        // Cannot execute Draft from external division (not yet released)
        $rev->rev_status = 'Draft';
        $this->assertFalse($this->policy->execute($itApprover, $rev));

        // Cannot execute Draft even if initiated directly by Unit 860000 (strictly requires In Process / Released)
        $rev->rev_intunt_id = 860000;
        $this->assertFalse($this->policy->execute($itApprover, $rev));

        // Cannot execute already Fulfilled revision
        $rev->rev_status = 'Fulfilled';
        $this->assertFalse($this->policy->execute($itApprover, $rev));

        // Cannot execute Cancelled revision
        $rev->rev_status = 'Cancelled';
        $this->assertFalse($this->policy->execute($itApprover, $rev));

        // CAN execute In Process revision
        $rev->rev_status = 'In Process';
        $this->assertTrue($this->policy->execute($itApprover, $rev));
    }

    /**
     * Test 8: Return is strictly restricted to Unit 860000 and requires In Process status.
     */
    public function test_policy_return_guards(): void
    {
        $itApprover = $this->makeAccount('test_itappr', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');
        $divApprover = $this->makeAccount('test_divappr', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');

        $rev = new AudRev();
        $rev->rev_id = 9401;
        $rev->rev_unt_id = 200000;
        $rev->rev_intunt_id = 200000;

        // IT user CAN return In Process revision
        $rev->rev_status = 'In Process';
        $this->assertTrue($this->policy->return($itApprover, $rev));
        $this->assertTrue($this->policy->returnRevision($itApprover, $rev));

        // Non-IT user CANNOT return
        $this->assertFalse($this->policy->return($divApprover, $rev));

        // Cannot return Draft (was never released)
        $rev->rev_status = 'Draft';
        $this->assertFalse($this->policy->return($itApprover, $rev));

        // Cannot return Fulfilled or Cancelled
        $rev->rev_status = 'Fulfilled';
        $this->assertFalse($this->policy->return($itApprover, $rev));
        $rev->rev_status = 'Cancelled';
        $this->assertFalse($this->policy->return($itApprover, $rev));
    }

    /**
     * Test 9: Cancel guards allow initiator cancel during Draft and IT cancel for non-fulfilled.
     */
    public function test_policy_cancel_guards(): void
    {
        $itAdmin = $this->makeAccount('test_itadmin', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');
        $divDir200 = $this->makeAccount('test_divdir200', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');
        $divDir300 = $this->makeAccount('test_divdir300', 300000, 'prj', 'Director Sensors', 'DSens', 'lead', 'approver');

        $rev = new AudRev();
        $rev->rev_id = 9501;
        $rev->rev_unt_id = 200000;
        $rev->rev_intunt_id = 200000;

        // Draft: Initiator CAN cancel
        $rev->rev_status = 'Draft';
        $this->assertTrue($this->policy->cancel($divDir200, $rev));
        // Other division CANNOT cancel
        $this->assertFalse($this->policy->cancel($divDir300, $rev));
        // IT CAN cancel
        $this->assertTrue($this->policy->cancel($itAdmin, $rev));

        // Under Revision: Initiator CAN cancel
        $rev->rev_status = 'Under Revision';
        $this->assertTrue($this->policy->cancel($divDir200, $rev));

        // In Process (Released): Initiator CANNOT cancel directly; only IT can cancel
        $rev->rev_status = 'In Process';
        $this->assertFalse($this->policy->cancel($divDir200, $rev));
        $this->assertTrue($this->policy->cancel($itAdmin, $rev));

        // Fulfilled: Terminal state. NO ONE can cancel
        $rev->rev_status = 'Fulfilled';
        $this->assertFalse($this->policy->cancel($divDir200, $rev));
        $this->assertFalse($this->policy->cancel($itAdmin, $rev));
    }

    /**
     * Test 10: Laravel Gate facade automatically integrates with AudRev model policy.
     */
    public function test_laravel_gate_integration_with_aud_rev_model(): void
    {
        $rev = new AudRev();
        $rev->rev_id = 9601;
        $rev->rev_unt_id = 200000;
        $rev->rev_intunt_id = 200000;
        $rev->rev_status = 'In Process';

        $itApprover = $this->makeAccount('test_itappr', 860000, 'it', 'Staff Officer IT&CYS', 'SO IT&CYS', 'lead', 'approver');
        $divApprover = $this->makeAccount('test_divappr', 200000, 'prj', 'Director Communication', 'DCom', 'lead', 'approver');

        // Gate::forUser checks using model policy
        $this->assertTrue(Gate::forUser($itApprover)->allows('execute', $rev));
        $this->assertTrue(Gate::forUser($itApprover)->allows('return', $rev));
        $this->assertTrue(Gate::forUser($itApprover)->allows('view', $rev));

        $this->assertFalse(Gate::forUser($divApprover)->allows('execute', $rev));
        $this->assertFalse(Gate::forUser($divApprover)->allows('return', $rev));
        $this->assertTrue(Gate::forUser($divApprover)->allows('view', $rev));
    }
}
