<?php

namespace App\Services\Auth;

use App\Models\CenAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissionMap
{
    /**
     * Cache for database overrides loaded from cen.role_permissions.
     */
    protected static ?array $dbRolePermissions = null;

    /**
     * Default role-to-permission mapping derived from effective current controller behavior.
     * ZERO raw unit numbers; clean semantic slugs.
     */
    public static function defaultMatrix(): array
    {
        return [
            'SUPERADMIN' => PermissionRegistry::all(),

            'COMMAND_DG' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_DG_ACTION,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_DG_ACTION,
                PermissionRegistry::FINANCE_VIEW,
                PermissionRegistry::FINANCE_COMMITMENTS_VIEW,
                PermissionRegistry::SALARY_VIEW,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'COMMAND_MD' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_MD_ACTION,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_MD_ACTION,
                PermissionRegistry::FINANCE_VIEW,
                PermissionRegistry::FINANCE_COMMITMENTS_VIEW,
                PermissionRegistry::SALARY_VIEW,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'COMMAND_DDG' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_DDG_ACTION,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_DDG_ACTION,
                PermissionRegistry::FINANCE_VIEW,
                PermissionRegistry::FINANCE_COMMITMENTS_VIEW,
                PermissionRegistry::SALARY_VIEW,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'SORD' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::MPR_VIEW,
                PermissionRegistry::MPR_REVIEW,
                PermissionRegistry::MPR_RETURN,
                PermissionRegistry::MPR_FINALIZE,
                PermissionRegistry::MPR_COMPILE,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'DEPT_DIRECTOR_RD' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::PROJECT_CREATE,
                PermissionRegistry::PROJECT_EDIT,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_CREATE,
                PermissionRegistry::PURCHASE_EDIT,
                PermissionRegistry::PURCHASE_SUBMIT,
                PermissionRegistry::MPR_VIEW,
                PermissionRegistry::MPR_SUBMIT,
                PermissionRegistry::ATTENDANCE_VIEW,
                PermissionRegistry::ATTENDANCE_RECORD,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'DIV_DIRECTOR' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::PROJECT_CREATE,
                PermissionRegistry::PROJECT_EDIT,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_CREATE,
                PermissionRegistry::PURCHASE_EDIT,
                PermissionRegistry::PURCHASE_SUBMIT,
                PermissionRegistry::PURCHASE_CANCEL,
                PermissionRegistry::PURCHASE_GOODS_RECEIPT,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_CREATE,
                PermissionRegistry::CONTRACT_EDIT,
                PermissionRegistry::CONTRACT_SUBMIT,
                PermissionRegistry::MPR_VIEW,
                PermissionRegistry::MPR_SUBMIT,
                PermissionRegistry::ATTENDANCE_VIEW,
                PermissionRegistry::ATTENDANCE_RECORD,
                PermissionRegistry::SALARY_VIEW,
                PermissionRegistry::SALARY_GENERATE,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'DIV_OFFICER' => [
                PermissionRegistry::PROJECT_VIEW,
                PermissionRegistry::PROJECT_CREATE,
                PermissionRegistry::PROJECT_EDIT,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_CREATE,
                PermissionRegistry::PURCHASE_EDIT,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_CREATE,
                PermissionRegistry::CONTRACT_EDIT,
                PermissionRegistry::CONTRACT_SUBMIT,
                PermissionRegistry::MPR_VIEW,
                PermissionRegistry::MPR_SUBMIT,
                PermissionRegistry::ATTENDANCE_VIEW,
                PermissionRegistry::ATTENDANCE_RECORD,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'DEPT_DIRECTOR_FIN' => [
                PermissionRegistry::FINANCE_VIEW,
                PermissionRegistry::FINANCE_COMMITMENTS_VIEW,
                PermissionRegistry::FINANCE_COMMITMENTS_SETTLE,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_FINANCE_REVIEW,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::SALARY_VIEW,
                PermissionRegistry::SALARY_GENERATE,
                PermissionRegistry::SALARY_OVERRIDE,
                PermissionRegistry::SALARY_APPROVE,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'FIN_OFFICER' => [
                PermissionRegistry::FINANCE_VIEW,
                PermissionRegistry::FINANCE_COMMITMENTS_VIEW,
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_FINANCE_REVIEW,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::SALARY_VIEW,
                PermissionRegistry::SALARY_GENERATE,
                PermissionRegistry::SALARY_OVERRIDE,
                PermissionRegistry::SALARY_APPROVE,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'PROC_DIRECTOR' => [
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_PROCUREMENT_ACTION,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'PROC_OFFICER' => [
                PermissionRegistry::PURCHASE_VIEW,
                PermissionRegistry::PURCHASE_PROCUREMENT_ACTION,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'HR_MANAGER' => [
                PermissionRegistry::HR_EMPLOYEE_VIEW,
                PermissionRegistry::HR_EMPLOYEE_MANAGE,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_HR_REVIEW,
                PermissionRegistry::CONTRACT_HR_FORWARD,
                PermissionRegistry::ATTENDANCE_VIEW,
                PermissionRegistry::REPORTS_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'HR_OFFICER' => [
                PermissionRegistry::HR_EMPLOYEE_VIEW,
                PermissionRegistry::HR_EMPLOYEE_MANAGE,
                PermissionRegistry::CONTRACT_VIEW,
                PermissionRegistry::CONTRACT_HR_REVIEW,
                PermissionRegistry::CONTRACT_HR_FORWARD,
                PermissionRegistry::ATTENDANCE_VIEW,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'IT_ADMIN' => [
                PermissionRegistry::ADMIN_SETTINGS_MANAGE,
                PermissionRegistry::ADMIN_USERS_VIEW,
                PermissionRegistry::ADMIN_USERS_MANAGE,
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
                PermissionRegistry::SUPPORT_TICKET_MANAGE,
                PermissionRegistry::REPORTS_VIEW,
            ],

            'IT_OFFICER' => [
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
                PermissionRegistry::SUPPORT_TICKET_MANAGE,
            ],

            'ADMIN_DEPT' => [
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'IS_DEPT' => [
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'MTSS_DEPT' => [
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],

            'USER' => [
                PermissionRegistry::SUPPORT_TICKET_CREATE,
                PermissionRegistry::SUPPORT_TICKET_REPLY,
            ],
        ];
    }

    /**
     * Load additive database overrides from cen.role_permissions if table exists.
     */
    protected static function loadDbOverrides(): array
    {
        if (self::$dbRolePermissions !== null) {
            return self::$dbRolePermissions;
        }

        self::$dbRolePermissions = [];

        try {
            if (Schema::hasTable('cen.role_permissions')) {
                $rows = DB::table('cen.role_permissions')->get();
                foreach ($rows as $r) {
                    self::$dbRolePermissions[$r->role_slug][] = $r->permission;
                }
            }
        } catch (\Throwable $e) {
            // Silently fall back to default matrix
        }

        return self::$dbRolePermissions;
    }

    /**
     * Resolve all effective permissions for a given user account.
     * Incorporates role default, database overrides, and acc_auth restrictions (viewer).
     *
     * @param CenAccount $account
     * @return array<string>
     */
    public static function resolvePermissionsFor(CenAccount $account): array
    {
        $context = UserAccessContext::forUser($account);

        // Super Admin gets all permissions unconditionally
        if ($context->isSuperAdmin()) {
            return PermissionRegistry::all();
        }

        $roleSlug = $context->getRoleSlug();
        $defaults = self::defaultMatrix()[$roleSlug] ?? self::defaultMatrix()['USER'];
        $dbOverrides = self::loadDbOverrides()[$roleSlug] ?? [];

        $allPermissions = array_values(array_unique(array_merge($defaults, $dbOverrides)));

        // CRITICAL EFFECTIVE BEHAVIOR:
        // If acc_auth === 'viewer', strip core domain mutating permissions (projects, purchases, etc.).
        // Support tickets remain accessible to all active accounts for help requests.
        if ($context->isViewer()) {
            $allPermissions = array_values(array_filter($allPermissions, function ($perm) {
                if (str_starts_with($perm, 'support.ticket.')) {
                    return true;
                }

                return ! (
                    str_ends_with($perm, '.create') ||
                    str_ends_with($perm, '.edit') ||
                    str_ends_with($perm, '.delete') ||
                    str_ends_with($perm, '.submit') ||
                    str_ends_with($perm, '.manage') ||
                    str_ends_with($perm, '.override') ||
                    str_ends_with($perm, '.approve') ||
                    str_ends_with($perm, '.settle') ||
                    str_ends_with($perm, '.record') ||
                    str_ends_with($perm, '.cancel')
                );
            }));
        }

        return $allPermissions;
    }

    /**
     * Check if an account has a specific permission.
     */
    public static function hasPermission(CenAccount $account, string $permission): bool
    {
        $permissions = self::resolvePermissionsFor($account);
        return in_array($permission, $permissions, true);
    }
}
