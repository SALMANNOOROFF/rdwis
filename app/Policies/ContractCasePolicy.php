<?php

namespace App\Policies;

use App\Models\CenAccount;
use App\Models\HrCtrCase;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;

class ContractCasePolicy
{
    protected DataScopeService $scopeService;

    public function __construct(DataScopeService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    public function viewAny(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_VIEW);
    }

    public function view(CenAccount $user, HrCtrCase $case): bool
    {
        $context = UserAccessContext::forUser($user);

        if ($context->isSuperAdmin() || $context->isCommand()) {
            return true;
        }

        $userArea = (string) ($user->acc_untarea ?? '');

        if (AreaDefinition::isHr($userArea) || AreaDefinition::isFinance($userArea) || AreaDefinition::isIt($userArea) || $context->isSord()) {
            return true;
        }

        $unitId = (int) ($user->acc_unt_id ?? 0);
        if ($unitId > 0 && ($case->ctc_unt_id == $unitId || $case->ctc_divisionid == $unitId || $case->ctc_approvedunt_id == $unitId)) {
            return true;
        }

        if ($this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0)) ||
            $this->scopeService->canAccessUnit($user, (int) ($case->ctc_divisionid ?? 0))) {
            return true;
        }

        return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_VIEW);
    }

    public function create(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_CREATE);
    }

    public function update(CenAccount $user, HrCtrCase $case): bool
    {
        $context = UserAccessContext::forUser($user);

        if ($context->isSuperAdmin() || $context->isCommand()) {
            return true;
        }

        $userArea = (string) ($user->acc_untarea ?? '');
        if (AreaDefinition::isHr($userArea) || $context->isSord()) {
            return true;
        }

        $unitId = (int) ($user->acc_unt_id ?? 0);
        if ($unitId > 0 && ($case->ctc_unt_id == $unitId || $case->ctc_divisionid == $unitId)) {
            return true;
        }

        $currentStage = $case->currentSubstatus->css_stage ?? $case->current_stage ?? 'Division';
        if ($currentStage === 'Division' && ($context->isDivision() || in_array(strtolower($userArea), ['prj', 'rdwprj'], true))) {
            return true;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0)) ||
               $this->scopeService->canAccessUnit($user, (int) ($case->ctc_divisionid ?? 0));
    }

    public function cancel(CenAccount $user, HrCtrCase $case): bool
    {
        // 1. Leadership (MD, DDG, DG, DLOG) can NEVER cancel hiring/contract cases
        if ((method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg()) || (method_exists($user, 'isDlog') && $user->isDlog())) {
            return false;
        }

        // 2. Hiring/Contract case can ONLY be cancelled when in Draft or Under Revision (pending action)
        $status = strtolower(trim((string) ($case->ctc_status ?? '')));
        $stage = strtolower(trim((string) ($case->current_stage ?? '')));
        $isDraftOrPending = in_array($status, ['draft', 'under revision', 'returned', 'action required'], true)
            || in_array($stage, ['division', 'draft'])
            || str_contains($status, 'draft')
            || str_contains($status, 'revision');

        if (! $isDraftOrPending) {
            return false;
        }

        // 3. Only Division, Finance, or HR (or SuperAdmin) can cancel
        $context = UserAccessContext::forUser($user);
        if ($context->isSuperAdmin()) {
            return true;
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHr = AreaDefinition::isHr($userArea) || in_array($userArea, ['hr'], true) || (method_exists($user, 'isHr') && $user->isHr());
        $isFin = AreaDefinition::isFinance($userArea) || in_array($userArea, ['fin', 'finance'], true) || (method_exists($user, 'isFinance') && $user->isFinance());
        $isDiv = $context->isDivision() || in_array($userArea, ['prj', 'rdwprj'], true) || (method_exists($user, 'isDivision') && $user->isDivision());

        if ($isHr || $isFin) {
            return true;
        }

        if ($isDiv) {
            $unitId = (int) ($user->acc_unt_id ?? 0);
            if ($unitId > 0 && ($case->ctc_unt_id == $unitId || $case->ctc_divisionid == $unitId || $case->ctc_approvedunt_id == $unitId)) {
                return true;
            }
            return $this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0)) ||
                   $this->scopeService->canAccessUnit($user, (int) ($case->ctc_divisionid ?? 0));
        }

        return false;
    }

    public function processAction(CenAccount $user, HrCtrCase $case, string $action): bool
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

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));

        // Command Stages (MD, DDG, DG can send to anyone)
        if ($context->isCommand() || in_array($userArea, ['rdw', 'hqs', 'nrdi'], true)) {
            return true;
        }

        // HR Stage (HR can send to anyone or return to division)
        if (AreaDefinition::isHr($userArea) || in_array($userArea, ['hr'], true)) {
            return true;
        }

        // Finance Stage (Finance can send to anyone)
        if (AreaDefinition::isFinance($userArea) || in_array($userArea, ['fin', 'finance'], true)) {
            return true;
        }

        // IT Stage (SO IT can review and send case with remarks to anyone)
        if (AreaDefinition::isIt($userArea) || in_array($userArea, ['it'], true)) {
            return true;
        }

        // SORD
        if ($context->isSord()) {
            return true;
        }

        // Division / Projects / Other Departments
        $unitId = (int) ($user->acc_unt_id ?? 0);
        if ($unitId > 0 && ($case->ctc_unt_id == $unitId || $case->ctc_divisionid == $unitId || $case->ctc_approvedunt_id == $unitId)) {
            return true;
        }

        $currentStage = $case->currentSubstatus->css_stage ?? $case->current_stage ?? 'Division';
        if ($currentStage === 'Division') {
            return true;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0)) ||
               $this->scopeService->canAccessUnit($user, (int) ($case->ctc_divisionid ?? 0));
    }
}
