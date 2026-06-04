<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HorizonScoped
{
    /**
     * Boot the trait to attach the global scope universally.
     */
    protected static function bootHorizonScoped()
    {
        static::addGlobalScope('horizon', function (Builder $builder) {
            
            // To prevent this applying in CLI/Tinker unless explicitly mocked
            if (!app()->runningInConsole()) {
                
                // Retrieve the active horizon boundaries shared by the DataHorizonMiddleware
                $lower = \Illuminate\Support\Facades\View::shared('global_lower');
                $upper = \Illuminate\Support\Facades\View::shared('global_upper');
                
                if ($lower !== null && $upper !== null) {
                    $model = $builder->getModel();
                    
                    // Determine which column this specific Model uses for Unit IDs.
                    // By default, try to deduce it or fall back to 'unt_id'
                    $unitColumn = method_exists($model, 'getHorizonColumn') 
                                    ? $model->getHorizonColumn() 
                                    : 'unt_id';
                    
                    $builder->whereBetween($model->getTable() . '.' . $unitColumn, [$lower, $upper]);
                }
            }
        });
    }
}
