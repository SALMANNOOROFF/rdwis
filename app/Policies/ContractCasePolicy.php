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
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_VIEW)) {
            return false;
        }

        $context = UserAccessContext::forUser($user);
        $userArea = (string) ($user->acc_untarea ?? '');

        if (
            AreaDefinition::isHr($userArea) ||
            AreaDefinition::isFinance($userArea) ||
            $context->isCommand()
        ) {
            return true;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0));
    }

    public function create(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_CREATE);
    }

    public function update(CenAccount $user, HrCtrCase $case): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_EDIT)) {
            return false;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0));
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

        $userArea = (string) ($user->acc_untarea ?? '');

        // HR Stage
        if (AreaDefinition::isHr($userArea)) {
            return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_HR_FORWARD);
        }

        // Finance Stage
        if (AreaDefinition::isFinance($userArea)) {
            return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_VIEW);
        }

        // Command Stages
        if ($context->isMd()) {
            return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_MD_ACTION);
        }

        if ($context->isDdg()) {
            return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_DDG_ACTION);
        }

        if ($context->isDg()) {
            return RolePermissionMap::hasPermission($user, PermissionRegistry::CONTRACT_DG_ACTION);
        }

        // Division Stage
        return $this->scopeService->canAccessUnit($user, (int) ($case->ctc_unt_id ?? 0));
    }
}
