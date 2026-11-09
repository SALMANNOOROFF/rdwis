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
        if (RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_VIEW)) {
            return true;
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $unitId = (int) ($user->acc_unt_id ?? 0);
        return in_array($area, ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi'], true) || $unitId >= 800000;
    }

    public function view(CenAccount $user, FinSalOrder $order): bool
    {
        $context = UserAccessContext::forUser($user);
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $unitId = (int) ($user->acc_unt_id ?? 0);

        // HR, Finance, and Command have multi-unit visibility
        if (AreaDefinition::isFinance($userArea) || in_array($userArea, ['hr', 'fin', 'adm', 'admin', 'is', 'it'], true) || $unitId >= 800000 || $context->isCommand()) {
            return true;
        }

        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_VIEW)) {
            return false;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($order->sor_unt_id ?? 0))
            || ($order->sor_effunt_id && $this->scopeService->canAccessUnit($user, (int) $order->sor_effunt_id));
    }

    public function generate(CenAccount $user): bool
    {
        if (RolePermissionMap::hasPermission($user, PermissionRegistry::SALARY_GENERATE)) {
            return true;
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $unitId = (int) ($user->acc_unt_id ?? 0);

        // HR and Central department users with approver authority can generate salary
        if (in_array($area, ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi'], true) || $unitId >= 800000) {
            $context = UserAccessContext::forUser($user);
            return $context->isApprover() || $context->isEditor() || $context->isSuperAdmin();
        }

        return false;
    }

    public function override(CenAccount $user, FinSalOrder $order): bool
    {
        $context = UserAccessContext::forUser($user);
        if ($context->isSuperAdmin() || $context->isCommand()) {
            return true;
        }

        if (! $context->isApprover()) {
            return false;
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if (in_array($area, ['fin', 'finance', 'hr', 'adm', 'admin', 'is', 'it'], true) || ((int)($user->acc_unt_id ?? 0) >= 800000)) {
            return true;
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
