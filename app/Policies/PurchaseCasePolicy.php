<?php

namespace App\Policies;

use App\Models\CenAccount;
use App\Models\Purchase;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;

class PurchaseCasePolicy
{
    protected DataScopeService $scopeService;

    public function __construct(DataScopeService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine whether the user can view any purchase cases.
     */
    public function viewAny(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_VIEW);
    }

    /**
     * Determine whether the user can view the specific purchase case.
     * Enforces: Permission + Scope.
     */
    public function view(CenAccount $user, Purchase $case): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_VIEW)) {
            return false;
        }

        $context = UserAccessContext::forUser($user);

        // Procurement, Finance, and Command officers have case visibility across divisions
        $userArea = (string) ($user->acc_untarea ?? '');
        if (
            AreaDefinition::isProcurement($userArea) ||
            AreaDefinition::isFinance($userArea) ||
            $context->isCommand() ||
            $context->isSord()
        ) {
            return true;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->pcs_unt_id ?? 0));
    }

    /**
     * Determine whether the user can create a purchase case.
     */
    public function create(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_CREATE);
    }

    /**
     * Determine whether the user can update a purchase case.
     */
    public function update(CenAccount $user, Purchase $case): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_EDIT)) {
            return false;
        }

        // Must be in draft or returned status to be edited by division
        $status = strtolower(trim((string) ($case->pcs_status ?? '')));
        if (! in_array($status, ['draft', 'returned'], true)) {
            $userArea = (string) ($user->acc_untarea ?? '');
            if (! AreaDefinition::isProcurement($userArea)) {
                return false;
            }
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->pcs_unt_id ?? 0));
    }

    /**
     * Determine whether the user can cancel a purchase case.
     */
    public function cancel(CenAccount $user, Purchase $case): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_CANCEL)) {
            return false;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->pcs_unt_id ?? 0));
    }

    /**
     * Determine whether the user can process an action at the current workflow stage.
     * Evaluates: Permission + Record Scope + Workflow Stage.
     */
    public function processAction(CenAccount $user, Purchase $case, string $action): bool
    {
        $context = UserAccessContext::forUser($user);

        if ($context->isSuperAdmin()) {
            return true;
        }

        if ($context->isViewer()) {
            return false;
        }

        if ($action === 'cancel') {
            return $this->cancel($user, $case);
        }

        if ($action === 'save_draft') {
            return $this->update($user, $case);
        }

        // Determine current workflow stage
        $currentStage = $case->currentSubstatus?->pss_stage ?? ($case->pcs_status === 'Draft' ? 'Division' : 'Division');
        $userArea = (string) ($user->acc_untarea ?? '');

        // 1. Division Stage
        if ($currentStage === 'Division') {
            if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_SUBMIT)) {
                return false;
            }
            return $this->scopeService->canAccessUnit($user, (int) ($case->pcs_unt_id ?? 0));
        }

        // 2. DProc / Procurement Stage
        if ($currentStage === 'DProc') {
            if (! AreaDefinition::isProcurement($userArea)) {
                return false;
            }
            return RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_PROCUREMENT_ACTION);
        }

        // 3. DFinance / Finance Stage
        if ($currentStage === 'DFinance') {
            if (! AreaDefinition::isFinance($userArea)) {
                return false;
            }
            return RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_FINANCE_REVIEW);
        }

        // 4. MD Stage
        if ($currentStage === 'MD') {
            return ($context->isMd() || $context->isCommand()) && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_MD_ACTION);
        }

        // 5. DDG Stage
        if ($currentStage === 'DDG') {
            return ($context->isDdg() || $context->isCommand()) && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DDG_ACTION);
        }

        // 6. DG Stage
        if ($currentStage === 'DG') {
            return ($context->isDg() || $context->isCommand()) && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DG_ACTION);
        }

        return false;
    }
}
