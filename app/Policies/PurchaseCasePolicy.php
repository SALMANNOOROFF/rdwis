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

        // Procurement, Finance, IT, and Command officers have case visibility across divisions
        $userArea = (string) ($user->acc_untarea ?? '');
        if (
            AreaDefinition::isProcurement($userArea) ||
            AreaDefinition::isFinance($userArea) ||
            AreaDefinition::isIt($userArea) ||
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
        $userArea = (string) ($user->acc_untarea ?? '');

        // Procurement Officers, users with PURCHASE_PROCUREMENT_ACTION, and SuperAdmin can update/manage case quotes across divisions
        if (
            AreaDefinition::isProcurement($userArea) ||
            RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_PROCUREMENT_ACTION) ||
            $user->acc_username === 'superadminrdw'
        ) {
            return true;
        }

        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_EDIT)) {
            return false;
        }

        // Must be in draft or returned status to be edited by division
        $status = strtolower(trim((string) ($case->pcs_status ?? '')));
        if (! in_array($status, ['draft', 'returned'], true)) {
            return false;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->pcs_unt_id ?? 0));
    }

    /**
     * Determine whether the user can cancel a purchase case.
     */
    public function cancel(CenAccount $user, Purchase $case): bool
    {
        // 1. Leadership (MD, DDG, DG, DLOG) can NEVER cancel cases
        if ((method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg()) || (method_exists($user, 'isDlog') && $user->isDlog())) {
            return false;
        }

        // 2. Cases can ONLY be cancelled when in Draft or Returned (pending action) status
        $status = strtolower(trim((string) ($case->pcs_status ?? '')));
        if (! in_array($status, ['draft', 'returned', 'action required'], true)) {
            return false;
        }

        // 3. Only Division or Finance (or SuperAdmin) can cancel
        $context = UserAccessContext::forUser($user);
        if ($context->isSuperAdmin()) {
            return true;
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if (in_array($userArea, ['fin', 'finance'], true) || (method_exists($user, 'isFinance') && $user->isFinance())) {
            return true;
        }

        $isDivision = in_array($userArea, ['prj', 'rdwprj'], true) || (method_exists($user, 'isDivision') && $user->isDivision());
        if ($isDivision) {
            $unitId = (int) ($user->acc_unt_id ?? 0);
            $caseUnit = (int) ($case->pcs_unt_id ?? 0);
            $caseIntUnit = (int) ($case->pcs_intunt_id ?? 0);
            if ($unitId > 0 && ($unitId === $caseUnit || $unitId === $caseIntUnit)) {
                return true;
            }

            if ($this->scopeService->canAccessUnit($user, $caseUnit)) {
                return true;
            }
        }

        return false;
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

        if ($action === 'approve') {
            // Approval is strictly restricted to Finance and HR (or SuperAdmin)
            if ($context->isSuperAdmin()) {
                return true;
            }

            $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
            return in_array($userArea, ['fin', 'finance', 'hr'], true);
        }

        if ($action === 'cancel') {
            return $this->cancel($user, $case);
        }

        if (in_array($action, ['reject', 'not_approved'], true)) {
            if ($context->isSuperAdmin()) {
                return true;
            }

            $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
            if (in_array($userArea, ['fin', 'finance', 'hr'], true)) {
                return true;
            }

            return $context->isCommand() || RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_CANCEL);
        }

        if ($action === 'save_draft') {
            return $this->update($user, $case);
        }

        // Disallow actions on already finalized cases
        $isFinalized = in_array(strtolower(trim((string) $case->pcs_status)), ['approved', 'rejected', 'cancelled', 'not approved', 'fulfilled', 'completed']);
        if ($isFinalized) {
            return false;
        }

        // SENDER LOCKING: If this user took the latest forwarding/returning decision on this case,
        // it is locked for them until the recipient acts!
        $latestDecision = $case->latestDecision ?? $case->decisions->sortByDesc('pdec_id')->first();
        if ($latestDecision 
            && (int) $latestDecision->pdec_acc_id === (int) $user->acc_id 
            && in_array($latestDecision->pdec_action, ['forward', 'forward_negative', 'return', 'float_to_proc', 'reshare_to_proc'])
        ) {
            return false;
        }

        // Determine current workflow stage
        $currentStage = $case->currentSubstatus?->pss_stage ?? ($case->pcs_status === 'Draft' ? 'Division' : 'Division');
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));

        // 1. Division Stage
        if ($currentStage === 'Division') {
            // Allow Procurement to act on collaborative draft/returned cases
            if (AreaDefinition::isProcurement($userArea) && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_PROCUREMENT_ACTION)) {
                return true;
            }

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
            return ($context->isMd() && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_MD_ACTION))
                || ($context->isDg() && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DG_ACTION))
                || $context->isSuperAdmin();
        }

        // 5. DDG Stage
        if ($currentStage === 'DDG') {
            return ($context->isDdg() && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DDG_ACTION))
                || ($context->isDg() && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DG_ACTION))
                || $context->isSuperAdmin();
        }

        // 6. DG Stage
        if ($currentStage === 'DG') {
            return ($context->isDg() && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DG_ACTION))
                || $context->isSuperAdmin();
        }

        // 7. DLOG Stage
        if ($currentStage === 'DLOG') {
            return ($context->isDlog() && RolePermissionMap::hasPermission($user, PermissionRegistry::PURCHASE_DLOG_ACTION))
                || $context->isSuperAdmin()
                || $context->isMd()
                || $context->isDg();
        }

        // 8. Other Autonomous Depts (IS, IT, Admin)
        if ($currentStage === 'IS') {
            return $userArea === 'is' || $context->isCommand();
        }
        if ($currentStage === 'IT') {
            return $userArea === 'it' || $context->isCommand();
        }
        if ($currentStage === 'Admin') {
            return in_array($userArea, ['admin', 'adm']) || $context->isCommand();
        }

        return false;
    }
}
