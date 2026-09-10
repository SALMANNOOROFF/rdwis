<?php

namespace App\Policies;

use App\Models\CenAccount;
use App\Models\FinSalOrder;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;

class SalaryOrderPolicy
{
    protected DataScopeService $scopeService;

    public function __construct(DataScopeService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    public function viewAny(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_VIEW);
    }

    public function view(CenAccount $user, FinSalOrder $order): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_VIEW)) {
            return false;
        }

        $context = UserAccessContext::forUser($user);
        $userArea = (string) ($user->acc_untarea ?? '');

        // Finance and Command have multi-unit visibility
        if (AreaDefinition::isFinance($userArea) || $context->isCommand()) {
            return true;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($order->sor_unt_id ?? 0))
            || ($order->sor_effunt_id && $this->scopeService->canAccessUnit($user, (int) $order->sor_effunt_id));
    }

    public function generate(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_GENERATE);
    }

    public function override(CenAccount $user, FinSalOrder $order): bool
    {
        $context = UserAccessContext::forUser($user);
        if ($context->isSuperAdmin()) {
            return true;
        }

        if (! $context->isApprover()) {
            return false;
        }

        return RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_OVERRIDE);
    }

    public function approve(CenAccount $user, FinSalOrder $order): bool
    {
        $context = UserAccessContext::forUser($user);
        if ($context->isSuperAdmin()) {
            return true;
        }

        if (! $context->isApprover()) {
            return false;
        }

        return RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_APPROVE);
    }
}
