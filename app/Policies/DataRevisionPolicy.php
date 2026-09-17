<?php

namespace App\Policies;

use App\Models\AudRev;
use App\Models\CenAccount;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;

class DataRevisionPolicy
{
    protected DataScopeService $scopeService;

    public function __construct(DataScopeService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine whether the user can view any reversals.
     */
    public function viewAny(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_VIEW);
    }

    /**
     * Determine whether the user can view a specific reversal record.
     * Enforces: Permission + Record Scope / Unit Ownership / Functional Domain.
     */
    public function view(CenAccount $user, AudRev $rev): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_VIEW)) {
            return false;
        }

        $context = UserAccessContext::forUser($user);

        // 1. Super Admin has global visibility
        if ($context->isSuperAdmin()) {
            return true;
        }

        // 2. Command Officers (DG, MD, DDG) have global organization visibility
        if ($context->isCommand()) {
            return true;
        }

        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');

        // 3. Unit 860000 / IT Department staff have full cross-unit visibility
        if ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true)) {
            return true;
        }

        // 4. Record Unit Scope: user can access target unit or initiating unit
        $revTargetUnit = (int) ($rev->rev_unt_id ?? 0);
        $revInitiatingUnit = (int) ($rev->rev_intunt_id ?? 0);

        if ($revTargetUnit > 0 && $this->scopeService->canAccessUnit($user, $revTargetUnit)) {
            return true;
        }

        if ($revInitiatingUnit > 0 && $this->scopeService->canAccessUnit($user, $revInitiatingUnit)) {
            return true;
        }

        // 5. Central Functional Departments (Finance, HR, Procurement) can view reversals in their domain
        $revObj = trim((string) ($rev->rev_obj ?? ''));

        if (AreaDefinition::isFinance($userArea)) {
            $finObjects = ['Salary', 'Salary Order', 'Commitment', 'Transaction', 'Payment', 'Allocation', 'Funding', 'Transfer', 'FinContract'];
            if (in_array($revObj, $finObjects, true)) {
                return true;
            }
        }

        if (AreaDefinition::isHr($userArea)) {
            $hrObjects = ['Employee', 'Contract', 'Attendance', 'Salary Requisition'];
            if (in_array($revObj, $hrObjects, true)) {
                return true;
            }
        }

        if (AreaDefinition::isProcurement($userArea)) {
            $procObjects = ['Purchase Case', 'Purchase Receipt', 'Purchase Attachment', 'Quotation'];
            if (in_array($revObj, $procObjects, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can initiate / create a reversal.
     */
    public function create(CenAccount $user): bool
    {
        return $this->initiate($user);
    }

    /**
     * Determine whether the user can initiate a reversal.
     */
    public function initiate(CenAccount $user): bool
    {
        $context = UserAccessContext::forUser($user);
        if ($context->isViewer()) {
            return false;
        }

        return RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_INITIATE);
    }

    /**
     * Determine whether the user can release a reversal to IT for execution.
     * Guard: Approver in initiating unit (or SuperAdmin), and status must be Draft or Under Revision.
     */
    public function release(CenAccount $user, AudRev $rev): bool
    {
        $context = UserAccessContext::forUser($user);

        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_RELEASE)) {
            return false;
        }

        if (! $context->isApprover() && ! $context->isSuperAdmin()) {
            return false;
        }

        // Can only release if currently Draft or Under Revision
        if (! $rev->isDraft() && ! $rev->isUnderRevision()) {
            return false;
        }

        // Must belong to initiating unit (or SuperAdmin)
        if ($context->isSuperAdmin()) {
            return true;
        }

        $initUnitId = (int) ($rev->rev_intunt_id ?? 0);
        if ($initUnitId <= 0) {
            $initUnitId = (int) ($rev->rev_unt_id ?? 0);
        }

        return $this->scopeService->canAccessUnit($user, $initUnitId);
    }

    /**
     * Determine whether the user can execute a reversal in the database.
     * Guard: Strictly Unit 860000 / IT Department approver or SuperAdmin.
     * Status guard: Must be Released / In Process (or Draft if initiated directly by IT).
     */
    public function execute(CenAccount $user, AudRev $rev): bool
    {
        $context = UserAccessContext::forUser($user);

        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_EXECUTE)) {
            return false;
        }

        // Strictly Unit 860000 or IT role or SuperAdmin
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));

        if (! $context->isSuperAdmin() && ! $isItStaff) {
            return false;
        }

        // Must be approver / admin
        if (! $context->isApprover() && ! $context->isSuperAdmin()) {
            return false;
        }

        // Cannot execute fulfilled or cancelled revisions
        if ($rev->isFulfilled() || $rev->isCancelled()) {
            return false;
        }

        // Strictly requires In Process / Released status (matching legacy aud_revs_detail.bas:1395-1402)
        return $rev->isInProcess();
    }

    /**
     * Determine whether the user can return a reversal to the initiating unit.
     * Guard: Strictly Unit 860000 / IT Department staff or SuperAdmin.
     * Status guard: Revision must be In Process / Released.
     */
    public function return(CenAccount $user, AudRev $rev): bool
    {
        $context = UserAccessContext::forUser($user);

        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_RETURN)) {
            return false;
        }

        // Strictly Unit 860000 or IT role or SuperAdmin
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));

        if (! $context->isSuperAdmin() && ! $isItStaff) {
            return false;
        }

        // Can only return if currently In Process / Released
        if (! $rev->isInProcess()) {
            return false;
        }

        return true;
    }

    /**
     * Alias for return() when called via string actions.
     */
    public function returnRevision(CenAccount $user, AudRev $rev): bool
    {
        return $this->return($user, $rev);
    }

    /**
     * Determine whether the user can cancel a reversal.
     * Guard:
     * - Cannot cancel already fulfilled revisions.
     * - IT staff / SuperAdmin can cancel any non-fulfilled revision.
     * - Initiating unit can cancel while in Draft or Under Revision.
     */
    public function cancel(CenAccount $user, AudRev $rev): bool
    {
        $context = UserAccessContext::forUser($user);

        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_CANCEL)) {
            return false;
        }

        if ($rev->isFulfilled()) {
            return false;
        }

        // SuperAdmin can cancel
        if ($context->isSuperAdmin()) {
            return true;
        }

        // IT staff can cancel non-fulfilled revisions
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));

        if ($isItStaff) {
            return true;
        }

        // Initiating unit user can cancel while in Draft or Under Revision
        if ($rev->isDraft() || $rev->isUnderRevision()) {
            $initUnitId = (int) ($rev->rev_intunt_id ?? 0);
            if ($initUnitId <= 0) {
                $initUnitId = (int) ($rev->rev_unt_id ?? 0);
            }

            return $this->scopeService->canAccessUnit($user, $initUnitId);
        }

        return false;
    }
}
