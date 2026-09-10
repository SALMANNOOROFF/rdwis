<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$u = App\Models\CenAccount::where('acc_untarea', 'ILIKE', 'fin')->first();
$context = App\Services\Auth\UserAccessContext::forUser($u);
$perms = App\Services\Auth\RolePermissionMap::resolvePermissionsFor($u);
var_dump([
    'username' => $u->acc_username,
    'slug' => $context->getRoleSlug(),
    'isApprover' => $context->isApprover(),
    'hasOverride' => App\Services\Auth\RolePermissionMap::hasPermission($u, App\Services\Auth\PermissionRegistry::SALARY_OVERRIDE),
    'hasApprove' => App\Services\Auth\RolePermissionMap::hasPermission($u, App\Services\Auth\PermissionRegistry::SALARY_APPROVE),
]);
