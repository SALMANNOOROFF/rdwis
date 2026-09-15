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

        if (AreaDefinition::isHr($userArea) || AreaDefinition::isFinance($userArea) || $context->isSord()) {
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

    public function processAction(CenAccount $user, HrCtrCase $case, string $action): bool
    {
        $context = UserAccessContext::forUser($user);

        if ($context->isSuperAdmin()) {
            return true;
        }

        if ($context->isViewer()) {
            return false;
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
