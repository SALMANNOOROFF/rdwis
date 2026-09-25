<?php

namespace App\Services\Auth;

use App\Models\CenAccount;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DataScopeService
{
    /**
     * Cache for dynamic division unit IDs to avoid repeated queries within request.
     */
    protected static ?array $cachedDivisionUnitIds = null;

    /**
     * Get all project and division unit IDs dynamically from cen.units.
     * ZERO hardcoded unit IDs.
     *
     * @return array<int>
     */
    public static function getDynamicDivisionUnitIds(): array
    {
        if (self::$cachedDivisionUnitIds !== null) {
            return self::$cachedDivisionUnitIds;
        }

        try {
            self::$cachedDivisionUnitIds = Unit::where('unt_area', AreaDefinition::PROJECTS)
                ->pluck('unt_id')
                ->map(fn($id) => (int) $id)
                ->all();
        } catch (\Throwable $e) {
            self::$cachedDivisionUnitIds = [];
        }

        return self::$cachedDivisionUnitIds;
    }

    /**
     * Resolve effective numeric bounds for an account.
     * Handles single access, multiple access, wide command scope, and inverted SORD range safely.
     *
     * @param Authenticatable|CenAccount|User $account
     * @return array{lower: int, upper: int, is_wide: bool, is_single: bool, specific_units: ?array<int>}
     */
    public function resolveScope(Authenticatable|CenAccount|User $account): array
    {
        $context = UserAccessContext::forUser($account);

        // 1. Super Admin has unrestricted scope
        if ($context->isSuperAdmin()) {
            return [
                'lower' => 0,
                'upper' => 99999999,
                'is_wide' => true,
                'is_single' => false,
                'specific_units' => null,
            ];
        }

        // 2. Command Officers (MD, DDG, DG) have organization-wide visibility
        if ($context->isCommand()) {
            return [
                'lower' => 0,
                'upper' => 99999999,
                'is_wide' => true,
                'is_single' => false,
                'specific_units' => null,
            ];
        }

        // 3. SORD (Staff Officer R&D): dynamically scoped to all project divisions
        if ($context->isSord()) {
            $divisionIds = self::getDynamicDivisionUnitIds();
            return [
                'lower' => !empty($divisionIds) ? min($divisionIds) : 0,
                'upper' => !empty($divisionIds) ? max($divisionIds) : 99999999,
                'is_wide' => false,
                'is_single' => false,
                'specific_units' => $divisionIds,
            ];
        }

        // 4. Evaluate single vs multiple access bounds
        $isSingle = strtolower(trim((string) ($account->acc_access ?? ''))) === 'single';
        $userUnitId = (int) ($account->acc_unt_id ?? 0);
        $lowers = (int) ($account->acc_lowers ?? 0);
        $uppers = (int) ($account->acc_uppers ?? 0);
        $lowerm = (int) ($account->acc_lowerm ?? 0);
        $upperm = (int) ($account->acc_upperm ?? 0);

        if ($isSingle) {
            if ($lowers > 0 && $uppers >= $lowers) {
                $lower = $lowers;
                $upper = $uppers;
            } else {
                $lower = $userUnitId;
                $upper = $userUnitId;
            }
        } else {
            // Multiple Unit Access: prefer lowerm/upperm, fallback to lowers/uppers
            if ($lowerm > 0 && $upperm >= $lowerm) {
                $lower = $lowerm;
                $upper = $upperm;
            } elseif ($lowers > 0 && $uppers >= $lowers) {
                $lower = $lowers;
                $upper = $uppers;
            } else {
                $lower = $userUnitId;
                $upper = $userUnitId;
            }
        }

        $isSingleUnit = ($lower === $upper && $lower === $userUnitId);

        return [
            'lower' => $lower,
            'upper' => $upper,
            'is_wide' => ($lower <= 0 && $upper >= 9999999),
            'is_single' => $isSingleUnit,
            'specific_units' => $isSingleUnit ? [$userUnitId] : null,
        ];
    }

    /**
     * Check if a specific unit ID is accessible to the account.
     */
    public function canAccessUnit(Authenticatable|CenAccount|User $account, int $unitId): bool
    {
        $scope = $this->resolveScope($account);

        if ($scope['is_wide']) {
            return true;
        }

        if ($scope['specific_units'] !== null) {
            return in_array($unitId, $scope['specific_units'], true);
        }

        return $unitId >= $scope['lower'] && $unitId <= $scope['upper'];
    }

    /**
     * Apply data scope boundaries to an Eloquent/Query Builder.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param Authenticatable|CenAccount|User $account
     * @param string $unitColumn e.g. 'prj_unt_id', 'pcs_unt_id', 'unt_id'
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function applyScope($query, Authenticatable|CenAccount|User $account, string $unitColumn = 'unt_id')
    {
        $scope = $this->resolveScope($account);

        // If wide scope, no unit filter needed
        if ($scope['is_wide']) {
            return $query;
        }

        // If explicitly resolved specific unit IDs (e.g. SORD division list)
        if ($scope['specific_units'] !== null) {
            if (empty($scope['specific_units'])) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereIn($unitColumn, $scope['specific_units']);
        }

        // If single unit
        if ($scope['is_single']) {
            return $query->where($unitColumn, $scope['lower']);
        }

        // Between bounds
        return $query->whereBetween($unitColumn, [$scope['lower'], $scope['upper']]);
    }

    /**
     * Apply scope to Project queries.
     */
    public function scopeProjects($query, Authenticatable|CenAccount|User $account, string $unitColumn = 'prj_unt_id')
    {
        return $this->applyScope($query, $account, $unitColumn);
    }

    /**
     * Apply scope to Purchase Case queries.
     */
    public function scopePurchases($query, Authenticatable|CenAccount|User $account, string $unitColumn = 'pcs_unt_id')
    {
        return $this->applyScope($query, $account, $unitColumn);
    }
}
