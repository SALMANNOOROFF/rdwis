<?php

namespace App\Policies;

use App\Models\CenAccount;
use App\Models\Project;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\PermissionRegistry;

class ProjectPolicy
{
    protected DataScopeService $scopeService;

    public function __construct(DataScopeService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::PROJECT_VIEW);
    }

    /**
     * Determine whether the user can view the specific project.
     * Enforces: Permission + Unit Scope.
     */
    public function view(CenAccount $user, Project $project): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PROJECT_VIEW)) {
            return false;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($project->prj_unt_id ?? 0));
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(CenAccount $user): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::PROJECT_CREATE);
    }

    /**
     * Determine whether the user can update the project.
     * Enforces: Permission + Unit Scope.
     */
    public function update(CenAccount $user, Project $project): bool
    {
        if (! RolePermissionMap::hasPermission($user, PermissionRegistry::PROJECT_EDIT)) {
            return false;
        }

        return $this->scopeService->canAccessUnit($user, (int) ($project->prj_unt_id ?? 0));
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(CenAccount $user, Project $project): bool
    {
        return RolePermissionMap::hasPermission($user, PermissionRegistry::PROJECT_DELETE);
    }
}
