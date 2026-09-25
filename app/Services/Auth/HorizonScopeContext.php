<?php

namespace App\Services\Auth;

use App\Exceptions\MissingScopeContextException;
use App\Exceptions\UnauthorizedScopeException;
use App\Models\CenAccount;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Manages explicit user scoping for non-web execution contexts
 * (CLI commands, queue workers, background jobs, and AI gateway tool calls).
 *
 * Guarantees that any service or query run on behalf of a specific user has
 * their authorized division/unit boundaries explicitly enforced without relying
 * on View::shared or being bypassed by app()->runningInConsole().
 */
class HorizonScopeContext
{
    /**
     * Stack of active explicit scopes for nesting support.
     *
     * @var array<int, array{
     *     lower?: int,
     *     upper?: int,
     *     is_wide?: bool,
     *     is_single?: bool,
     *     specific_units?: ?array<int>,
     *     user?: Authenticatable|CenAccount|User,
     *     user_id?: ?int,
     *     mode?: string
     * }>
     */
    protected static array $scopeStack = [];

    /**
     * Counter for required explicit scope enforcement blocks.
     */
    protected static int $enforcementCount = 0;

    /**
     * Execute a callback within the explicit scope of a specific user.
     *
     * @template T
     * @param Authenticatable|CenAccount|User|null $user
     * @param Closure(): T $callback
     * @return T
     *
     * @throws MissingScopeContextException If user is null
     * @throws UnauthorizedScopeException If user account is inactive
     */
    public static function runAs(Authenticatable|CenAccount|User|null $user, Closure $callback)
    {
        if ($user === null) {
            throw new MissingScopeContextException('Explicit user context is required but null was provided.');
        }

        $status = strtolower(trim((string) ($user->acc_status ?? 'active')));
        if ($status !== 'active') {
            throw new UnauthorizedScopeException("User account '{$user->acc_username}' is not active.");
        }

        /** @var DataScopeService $scopeService */
        $scopeService = app(DataScopeService::class);
        $scope = $scopeService->resolveScope($user);
        $scope['user'] = $user;
        $scope['user_id'] = $user->acc_id ?? null;
        $scope['mode'] = 'user';

        self::$scopeStack[] = $scope;

        try {
            return $callback($user, $scope);
        } finally {
            array_pop(self::$scopeStack);
        }
    }

    /**
     * Execute a callback within explicit numeric scope boundaries.
     *
     * @template T
     * @param array{
     *     lower?: int,
     *     upper?: int,
     *     is_wide?: bool,
     *     is_single?: bool,
     *     specific_units?: ?array<int>,
     *     user?: Authenticatable|CenAccount|User
     * } $scope
     * @param Closure(): T $callback
     * @return T
     *
     * @throws MissingScopeContextException If scope boundaries are invalid
     */
    public static function withExplicitScope(array $scope, Closure $callback)
    {
        if (
            empty($scope['is_wide']) &&
            (!isset($scope['lower'], $scope['upper'])) &&
            (!isset($scope['specific_units']) || !is_array($scope['specific_units']))
        ) {
            throw new MissingScopeContextException('Invalid explicit scope boundaries provided.');
        }

        self::$scopeStack[] = $scope;

        try {
            return $callback($scope);
        } finally {
            array_pop(self::$scopeStack);
        }
    }

    /**
     * Execute a callback requiring that an explicit scope context MUST be present.
     * If any HorizonScoped model is queried without an active scope, it will be rejected.
     *
     * @template T
     * @param Closure(): T $callback
     * @return T
     */
    public static function requireExplicitScope(Closure $callback)
    {
        self::$enforcementCount++;

        try {
            return $callback();
        } finally {
            self::$enforcementCount--;
        }
    }

    /**
     * Determine if an explicit scope is currently active.
     */
    public static function hasActiveScope(): bool
    {
        return !empty(self::$scopeStack);
    }

    /**
     * Retrieve the currently active explicit scope array, or null if none.
     *
     * @return array{
     *     lower?: int,
     *     upper?: int,
     *     is_wide?: bool,
     *     is_single?: bool,
     *     specific_units?: ?array<int>,
     *     user?: Authenticatable|CenAccount|User,
     *     user_id?: ?int,
     *     mode?: string
     * }|null
     */
    public static function getActiveScope(): ?array
    {
        if (empty(self::$scopeStack)) {
            return null;
        }

        return self::$scopeStack[count(self::$scopeStack) - 1];
    }

    /**
     * Retrieve the currently scoped user, if any.
     */
    public static function getActiveUser(): Authenticatable|CenAccount|User|null
    {
        $scope = self::getActiveScope();
        return $scope['user'] ?? null;
    }

    /**
     * Determine if explicit scope enforcement is strictly required.
     */
    public static function isEnforcementRequired(): bool
    {
        return self::$enforcementCount > 0;
    }

    /**
     * Check if a unit ID is permitted under the current active explicit scope.
     */
    public static function canAccessUnit(int $unitId): bool
    {
        $scope = self::getActiveScope();
        if ($scope === null) {
            return false;
        }

        if (!empty($scope['is_wide'])) {
            return true;
        }

        if (isset($scope['user']) && $scope['user'] instanceof Authenticatable) {
            return app(DataScopeService::class)->canAccessUnit($scope['user'], $unitId);
        }

        if (isset($scope['specific_units']) && is_array($scope['specific_units'])) {
            return in_array($unitId, $scope['specific_units'], true);
        }

        if (isset($scope['lower'], $scope['upper'])) {
            return $unitId >= $scope['lower'] && $unitId <= $scope['upper'];
        }

        return false;
    }

    /**
     * Reset all active scopes and enforcement counters (primarily for testing cleanup).
     */
    public static function reset(): void
    {
        self::$scopeStack = [];
        self::$enforcementCount = 0;
    }
}
