<?php

namespace App\Services\HrForms;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Availability gate for the HR Forms feature.
 *
 * The feature flag (config/hrforms.php) can be switched on in an environment
 * where the dedicated `hrforms` schema has not been migrated yet. Querying the
 * HrForms models there throws a QueryException ("relation hrforms.case_forms
 * does not exist") and takes down the whole page it is embedded in. This gate
 * combines the flag with a one-off probe of the schema so the feature simply
 * stays hidden until the migrations have run.
 */
class HrFormsSchema
{
    private static ?bool $available = null;

    public static function available(): bool
    {
        if (! config('hrforms.enabled', false)) {
            return false;
        }

        if (self::$available !== null) {
            return self::$available;
        }

        try {
            self::$available = DB::table('information_schema.tables')
                ->where('table_schema', config('hrforms.schema', 'hrforms'))
                ->where('table_name', 'case_forms')
                ->exists();
        } catch (Throwable $e) {
            self::$available = false;
        }

        return self::$available;
    }

    /** Reset the memoised probe (useful right after running the migrations). */
    public static function flush(): void
    {
        self::$available = null;
    }
}
