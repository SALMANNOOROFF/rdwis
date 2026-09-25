<?php

namespace App\Traits;

use App\Exceptions\MissingScopeContextException;
use App\Services\Auth\HorizonScopeContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\View;

trait HorizonScoped
{
    /**
     * Boot the trait to attach the global scope universally.
     */
    protected static function bootHorizonScoped()
    {
        static::addGlobalScope('horizon', function (Builder $builder) {
            $model = $builder->getModel();

            // Determine which column this specific Model uses for Unit IDs.
            // By default, try to deduce it or fall back to 'unt_id'
            $unitColumn = method_exists($model, 'getHorizonColumn') 
                            ? $model->getHorizonColumn() 
                            : 'unt_id';
            $tableColumn = $model->getTable() . '.' . $unitColumn;

            // 1. Explicit Scope Context: Highest priority.
            // Enforces explicit scoping on non-web callers, queue jobs, and AI gateway tool calls.
            if (HorizonScopeContext::hasActiveScope()) {
                $scope = HorizonScopeContext::getActiveScope();

                // Wide organizational scope (e.g. SuperAdmin or Command officers)
                if (!empty($scope['is_wide'])) {
                    return;
                }

                // Specific unit IDs (e.g. SORD project division units)
                if (isset($scope['specific_units']) && is_array($scope['specific_units'])) {
                    if (empty($scope['specific_units'])) {
                        $builder->whereRaw('1 = 0');
                    } else {
                        $builder->whereIn($tableColumn, $scope['specific_units']);
                    }
                    return;
                }

                $lower = $scope['lower'] ?? null;
                $upper = $scope['upper'] ?? null;

                if ($lower !== null && $upper !== null) {
                    $builder->whereBetween($tableColumn, [$lower, $upper]);
                }
                return;
            }

            // 2. Reject if explicit scope enforcement is strictly required but absent
            if (HorizonScopeContext::isEnforcementRequired()) {
                throw new MissingScopeContextException(
                    "Explicit scope context is required for model [" . get_class($model) . "], but none was provided."
                );
            }

            // 3. Fallback to existing web request behavior (View::shared populated by DataHorizonMiddleware)
            if (!app()->runningInConsole()) {
                $lower = View::shared('global_lower');
                $upper = View::shared('global_upper');

                if ($lower !== null && $upper !== null) {
                    $builder->whereBetween($tableColumn, [$lower, $upper]);
                }
            }
        });
    }
}
