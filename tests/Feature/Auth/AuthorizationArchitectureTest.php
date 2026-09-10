<?php

namespace Tests\Feature\Auth;

use App\Models\CenAccount;
use App\Models\Project;
use App\Models\Purchase;
use App\Models\Unit;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\OfficerReplacementService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test 1: Zero hardcoded current usernames in command detection.
     * Command officers (MD, DDG, DG) must be recognized by designation, not username.
     */
    public function test_zero_hardcoded_usernames_in_command_detection(): void
    {
        // Create an account with a completely unknown/random username
        $account = new CenAccount();
        $account->acc_username = 'new_random_officer_' . uniqid();
        $account->acc_desig = 'Director General NRDI';
        $account->acc_desigshort = 'DG NRDI';
        $account->acc_untarea = 'nrdi';
        $account->acc_auth = 'viewer';
        $account->acc_status = 'Active';

        $context = UserAccessContext::forUser($account);
        $this->assertTrue($context->isCommand(), 'DG must be recognized as Command officer by designation.');
        $this->assertTrue($context->isDg(), 'DG must be recognized by designation.');
        $this->assertEquals('COMMAND_DG', $context->getRoleSlug());

        // Test MD
        $account->acc_desig = 'Managing Director RDW';
        $account->acc_desigshort = 'MD RDW';
        $account->acc_untarea = 'rdw';
        $context = UserAccessContext::forUser($account);
        $this->assertTrue($context->isCommand(), 'MD must be recognized as Command officer by designation.');
        $this->assertTrue($context->isMd(), 'MD must be recognized by designation.');
        $this->assertEquals('COMMAND_MD', $context->getRoleSlug());
    }

    /**
     * Test 2: Dynamic SORD scope includes all project divisions without hardcoded unit arrays.
     */
    public function test_sord_dynamic_scope_includes_all_divisions(): void
    {
        $divisionUnits = DataScopeService::getDynamicDivisionUnitIds();
        $this->assertNotEmpty($divisionUnits, 'Dynamic division units must be queried from cen.units.');

        // Find actual SORD account
        $sord = CenAccount::where('acc_untarea', 'rdwprj')->first();
        if (! $sord) {
            $sord = new CenAccount();
            $sord->acc_username = 'test_sord';
            $sord->acc_desig = 'Staff Officer R&D';
            $sord->acc_desigshort = 'SO R&D';
            $sord->acc_untarea = 'rdwprj';
            $sord->acc_auth = 'approver';
            $sord->acc_status = 'Active';
            $sord->acc_lowers = 180000;
            $sord->acc_uppers = 179999; // Inverted range in DB
        }

        $scopeService = new DataScopeService();
        $scope = $scopeService->resolveScope($sord);

        $this->assertNotNull($scope['specific_units'], 'SORD scope must have specific division units resolved.');
        foreach ($divisionUnits as $divUnitId) {
            $this->assertTrue(
                $scopeService->canAccessUnit($sord, $divUnitId),
                "SORD must be able to access division unit {$divUnitId}."
            );
        }
    }

    /**
     * Test 3: Every active user in the database resolves a valid UserAccessContext, roleSlug, and permissions.
     */
    public function test_every_active_user_resolves_access_context(): void
    {
        $activeUsers = CenAccount::whereRaw("LOWER(acc_status) = 'active'")->get();
        $this->assertGreaterThan(0, $activeUsers->count(), 'There must be active users in the database.');

        foreach ($activeUsers as $user) {
            $context = UserAccessContext::forUser($user);
            $roleSlug = $context->getRoleSlug();
            $this->assertNotEmpty($roleSlug, "User {$user->acc_username} must resolve a non-empty role slug.");

            $permissions = RolePermissionMap::resolvePermissionsFor($user);
            $this->assertIsArray($permissions);
            $this->assertNotEmpty($permissions, "User {$user->acc_username} must have at least baseline permissions.");

            // Every user can view/create support tickets
            $this->assertTrue(
                in_array(PermissionRegistry::SUPPORT_TICKET_CREATE, $permissions, true),
                "User {$user->acc_username} should have support ticket creation capability."
            );
        }
    }

    /**
     * Test 4: Projects are primarily unit-owned. Decoupled officer replacement preserves unit data.
     */
    public function test_direct_unit_project_ownership_and_officer_replacement(): void
    {
        $unit = Unit::where('unt_area', 'prj')->where('unt_type', 'Division')->first();
        if (! $unit) {
            $this->markTestSkipped('No project division found.');
        }

        $service = new OfficerReplacementService();

        // Simulate incoming successor officer
        $successor = new CenAccount();
        $successor->acc_username = 'new_incoming_dir_' . uniqid();
        $successor->acc_unt_id = $unit->unt_id;
        $successor->acc_desig = 'Director ' . $unit->unt_name;
        $successor->acc_desigshort = 'Dir';
        $successor->acc_untarea = 'prj';
        $successor->acc_auth = 'approver';
        $successor->acc_status = 'Active';

        $report = $service->verifySuccessorInheritance($successor);

        $this->assertEquals($unit->unt_id, $report['unit_id']);
        $this->assertEquals(0, $report['rows_reassigned'], 'Successor receives access via unit without row reassignment.');
    }

    /**
     * Test 5: Viewer accounts have all mutating permissions stripped.
     */
    public function test_viewer_accounts_cannot_perform_mutating_actions(): void
    {
        $viewer = new CenAccount();
        $viewer->acc_username = 'viewer_test_user';
        $viewer->acc_untarea = 'prj';
        $viewer->acc_desig = 'Division Viewer';
        $viewer->acc_auth = 'viewer';
        $viewer->acc_status = 'Active';

        $permissions = RolePermissionMap::resolvePermissionsFor($viewer);

        // Verify viewer has view permission but no create/edit/approve/delete
        $this->assertFalse(in_array(PermissionRegistry::PROJECT_CREATE, $permissions, true));
        $this->assertFalse(in_array(PermissionRegistry::PROJECT_EDIT, $permissions, true));
        $this->assertFalse(in_array(PermissionRegistry::PURCHASE_CREATE, $permissions, true));
        $this->assertFalse(in_array(PermissionRegistry::SALARY_APPROVE, $permissions, true));
    }

    /**
     * Test 6: Multi-stage purchase policy enforces stage and authorized role.
     */
    public function test_purchase_workflow_policy_enforces_stage_and_role(): void
    {
        $scopeService = new DataScopeService();
        $policy = new \App\Policies\PurchaseCasePolicy($scopeService);

        // Division Officer attempting to approve at DG stage should be denied
        $divOfficer = new CenAccount();
        $divOfficer->acc_username = 'div_officer_test';
        $divOfficer->acc_untarea = 'prj';
        $divOfficer->acc_desig = 'Division Officer';
        $divOfficer->acc_auth = 'editor';
        $divOfficer->acc_status = 'Active';

        $caseAtDg = new Purchase();
        $caseAtDg->pcs_id = 99999;
        $caseAtDg->pcs_unt_id = 200000;
        $caseAtDg->pcs_status = 'Under Approval';
        $caseAtDg->setAttribute('current_stage_display', 'Director General');

        $this->assertFalse(
            $policy->processAction($divOfficer, $caseAtDg, 'approve'),
            'Division officer cannot process an action at the DG stage.'
        );
    }

    /**
     * Test 7: Direct URL mutation attacks by Viewer are rejected with 403.
     */
    public function test_viewer_direct_mutation_attacks_are_rejected_with_403(): void
    {
        $viewer = CenAccount::where('acc_auth', 'viewer')->whereRaw("LOWER(acc_status) = 'active'")->first();
        if (! $viewer) {
            $viewer = new CenAccount();
            $viewer->acc_username = 'temp_viewer_' . uniqid();
            $viewer->acc_unt_id = 200000;
            $viewer->acc_untarea = 'prj';
            $viewer->acc_desig = 'Viewer';
            $viewer->acc_level = 1;
            $viewer->acc_auth = 'viewer';
            $viewer->acc_status = 'Active';
            $viewer->save();
        }

        // 1. Attempt to create project
        $response1 = $this->actingAs($viewer)->post('/save-project', [
            'prj_title' => 'Unauthorized Project Attempt',
        ]);
        $response1->assertStatus(403);

        // 2. Attempt to create purchase
        $response2 = $this->actingAs($viewer)->post('/purchase/store', [
            'pcs_title' => 'Unauthorized Purchase Attempt',
        ]);
        $response2->assertStatus(403);

        // 3. Attempt to approve salary order
        $response3 = $this->actingAs($viewer)->post('/divhr/salary/orders/1/approve');
        $response3->assertStatus(403);

        // 4. Attempt to delete milestone
        $response4 = $this->actingAs($viewer)->get('/milestone/1/delete');
        $response4->assertStatus(403);
    }

    /**
     * Test 8: Cross-unit project update attack returns 403.
     */
    public function test_cross_unit_project_update_attack_returns_403(): void
    {
        // Find or create Sensors user (Unit 350000)
        $sensorsUser = CenAccount::where('acc_unt_id', 350000)->where('acc_auth', 'approver')->first();
        if (! $sensorsUser) {
            $sensorsUser = new CenAccount();
            $sensorsUser->acc_username = 'sensor_user_' . uniqid();
            $sensorsUser->acc_unt_id = 350000;
            $sensorsUser->acc_untarea = 'prj';
            $sensorsUser->acc_desig = 'Director Sensors';
            $sensorsUser->acc_level = 1;
            $sensorsUser->acc_auth = 'approver';
            $sensorsUser->acc_status = 'Active';
            $sensorsUser->acc_lowers = 350000;
            $sensorsUser->acc_uppers = 350000;
            $sensorsUser->save();
        }

        // Find or create Communication project (Unit 200000)
        $commProject = Project::where('prj_unt_id', 200000)->first();
        if (! $commProject) {
            $commProject = new Project();
            $commProject->prj_code = 'PRJ-COMM-' . uniqid();
            $commProject->prj_title = 'Communication Project';
            $commProject->prj_unt_id = 200000;
            $commProject->prj_status = 'In Progress';
            $commProject->save();
        }

        // Policy check directly
        $policy = new \App\Policies\ProjectPolicy(new DataScopeService());
        $this->assertFalse(
            $policy->update($sensorsUser, $commProject),
            'Sensors officer must not have permission to update Communication project.'
        );

        // Direct HTTP update attempt
        $response = $this->actingAs($sensorsUser)->post('/finalize-project/' . $commProject->prj_id);
        $response->assertStatus(403);
    }

    /**
     * Test 9: Financial threshold boundaries for delegated purchase approval.
     */
    public function test_purchase_financial_threshold_boundaries(): void
    {
        $approvalService = app(\App\Services\PurchaseApprovalService::class);

        // MD Threshold: 400,000
        $this->assertTrue($approvalService->canApprove('rdw', 399999.0), 'MD must approve below 400k threshold.');
        $this->assertTrue($approvalService->canApprove('rdw', 400000.0), 'MD must approve at exact 400k threshold.');
        $this->assertFalse($approvalService->canApprove('rdw', 400001.0), 'MD must NOT approve above 400k threshold.');

        // DDG Threshold: 1,000,000
        $this->assertTrue($approvalService->canApprove('hqs', 999999.0), 'DDG must approve below 1M threshold.');
        $this->assertTrue($approvalService->canApprove('hqs', 1000000.0), 'DDG must approve at exact 1M threshold.');
        $this->assertFalse($approvalService->canApprove('hqs', 1000001.0), 'DDG must NOT approve above 1M threshold.');

        // DG has unlimited approval authority
        $this->assertTrue($approvalService->canApprove('nrdi', 5000000.0), 'DG can approve unlimited amounts.');
    }

    /**
     * Test 10: Replacement officer lifecycle and historical audit user preservation.
     */
    public function test_replacement_officer_full_lifecycle_and_audit_preservation(): void
    {
        $unitId = 200000; // Communication division

        // Replicate an existing director account to maintain exact CEN column schema
        $template = CenAccount::where('acc_unt_id', $unitId)->first() 
            ?: CenAccount::where('acc_untarea', 'prj')->first();

        // Create outgoing Officer A
        $officerA = $template->replicate();
        $officerA->acc_username = 'officer_a_' . uniqid();
        $officerA->acc_unt_id = $unitId;
        $officerA->acc_status = 'Active';
        $officerA->acc_auth = 'approver';
        $officerA->save();

        // Create project owned by Unit
        $project = new Project();
        $project->prj_id = ((int) Project::max('prj_id')) + 1;
        $project->prj_code = 'R' . rand(100000, 999999);
        $project->prj_title = 'Replacement Test Project';
        $project->prj_unt_id = $unitId;
        $project->prj_status = 'Draft';
        $project->save();

        // Create document with Officer A as creator
        $doc = new \App\Models\Document();
        $doc->prj_id = $project->prj_id;
        $doc->doc_type = 'MPR';
        $doc->creator_id = $officerA->acc_id;
        $doc->current_owner_id = $officerA->acc_id;
        $doc->status = 'Draft';
        $doc->save();

        // Verify Officer A can see and update
        $policy = new \App\Policies\ProjectPolicy(new DataScopeService());
        $this->assertTrue($policy->view($officerA, $project));
        $this->assertTrue($policy->update($officerA, $project));

        // Officer A leaves: Closed account
        $replacementService = new OfficerReplacementService();
        $replacementService->closeOfficer($officerA);
        $this->assertEquals('Closed', $officerA->fresh()->acc_status);

        // Successor Officer B joins the same seat
        $officerB = $template->replicate();
        $officerB->acc_username = 'officer_b_' . uniqid();
        $officerB->acc_unt_id = $unitId;
        $officerB->acc_status = 'Active';
        $officerB->acc_auth = 'approver';
        $officerB->save();

        // Officer B can immediately view and update the unit project
        $this->assertTrue($policy->view($officerB, $project), 'Successor B must see unit project immediately.');
        $this->assertTrue($policy->update($officerB, $project), 'Successor B must have edit rights on unit project.');

        // Verify historical audit fields are completely UNCHANGED
        $project->refresh();
        $this->assertEquals($unitId, $project->prj_unt_id, 'Unit ownership must remain intact.');
        $doc->refresh();
        $this->assertEquals($officerA->acc_id, $doc->creator_id, 'Historical creator_id must remain Officer A.');
    }

    /**
     * Test 11: All 34 roles in cen.roles resolve deterministically without collision.
     */
    public function test_all_34_roles_resolve_deterministically(): void
    {
        $roles = \Illuminate\Support\Facades\DB::select("SELECT rol_unt_id, rol_level, rol_desig, rol_desigshort, rol_desigtype, rol_access, rol_auth FROM cen.roles");
        $this->assertCount(34, $roles, 'cen.roles must define exactly 34 roles.');

        foreach ($roles as $r) {
            $dummy = new CenAccount();
            $dummy->acc_unt_id = $r->rol_unt_id;
            $dummy->acc_desig = $r->rol_desig;
            $dummy->acc_desigshort = $r->rol_desigshort;
            $dummy->acc_desigtype = $r->rol_desigtype;
            $dummy->acc_auth = $r->rol_auth;
            $dummy->acc_access = $r->rol_access;
            $dummy->acc_level = $r->rol_level;

            $unit = \Illuminate\Support\Facades\DB::table('cen.units')->where('unt_id', $r->rol_unt_id)->first();
            $dummy->acc_untarea = $unit->unt_area ?? '';

            $context = UserAccessContext::forUser($dummy);
            $slug = $context->getRoleSlug();

            $this->assertNotEmpty($slug, "Role {$r->rol_desig} in unit {$r->rol_unt_id} must resolve a slug.");
            $this->assertNotEquals('UNKNOWN', $slug, "Role {$r->rol_desig} must not resolve to UNKNOWN.");

            // Verify procurement vs finance director distinction
            if ($r->rol_unt_id == 810000) {
                $this->assertEquals('PROC_DIRECTOR', $slug, 'Unit 810000 DProc must resolve to PROC_DIRECTOR.');
            }
            if ($r->rol_unt_id == 800000 && str_contains($r->rol_desig, 'Director')) {
                $this->assertEquals('DEPT_DIRECTOR_FIN', $slug, 'Unit 800000 DFin must resolve to DEPT_DIRECTOR_FIN.');
            }
        }
    }

    /**
     * Test 12: Normal users are blocked from God Mode route with 403.
     */
    public function test_normal_user_blocked_from_god_mode(): void
    {
        $normalUser = CenAccount::where('acc_auth', 'approver')
            ->where('acc_untarea', 'prj')
            ->whereRaw("LOWER(acc_status) = 'active'")
            ->first();

        if ($normalUser) {
            $response = $this->actingAs($normalUser)->get('/godmode/takeover/52');
            $response->assertStatus(403);
        }
    }
}
