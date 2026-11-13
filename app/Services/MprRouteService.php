<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The legacy MPR route, exactly as the Access system read it.
 *
 * cen.globalvars.mpr_route holds something like "-19-(25)-": a dash separated
 * list of role levels, the one in brackets being the junction / command stop.
 * Adding MD to the route is therefore a settings change ("-19-25-(29)-"), not a
 * code change.
 */
class MprRouteService
{
    private ?array $stops = null;
    private ?array $junctions = null;
    private ?array $levelDesig = null;

    public function raw(): string
    {
        $value = DB::table('cen.globalvars')->where('gvar_name', 'mpr_route')->value('gvar_value');
        return trim((string) ($value ?: '-19-(25)-'));
    }

    /** @return int[] every stop in order */
    public function stops(): array
    {
        if ($this->stops !== null) {
            return $this->stops;
        }

        $this->stops = [];
        $this->junctions = [];

        foreach (explode('-', $this->raw()) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $isJunction = str_starts_with($part, '(') && str_ends_with($part, ')');
            $level = (int) trim($part, '()');
            if ($level > 0) {
                $this->stops[] = $level;
                if ($isJunction) {
                    $this->junctions[] = $level;
                }
            }
        }

        if (empty($this->stops)) {
            $this->stops = [19, 25];
            $this->junctions = [25];
        }

        return $this->stops;
    }

    public function first(): int
    {
        $s = $this->stops();
        return $s[0];
    }

    public function last(): int
    {
        $s = $this->stops();
        return $s[count($s) - 1];
    }

    /** The bracketed stop (legacy JunctionStop). */
    public function junction(): int
    {
        $this->stops();
        return $this->junctions[0] ?? $this->last();
    }

    public function next(int $level): ?int
    {
        $s = $this->stops();
        $i = array_search($level, $s, true);
        if ($i === false || $i === count($s) - 1) {
            return null;
        }
        return $s[$i + 1];
    }

    public function prev(int $level): ?int
    {
        $s = $this->stops();
        $i = array_search($level, $s, true);
        if ($i === false || $i === 0) {
            return null;
        }
        return $s[$i - 1];
    }

    public function isOnRoute(int $level): bool
    {
        return in_array($level, $this->stops(), true);
    }

    public function isFirst(int $level): bool
    {
        return $level === $this->first();
    }

    public function isLast(int $level): bool
    {
        return $level === $this->last();
    }

    /** Legacy DesigInStop(): the designation sitting at a level. */
    public function desigForLevel(?int $level): ?string
    {
        if (! $level) {
            return null;
        }

        if ($this->levelDesig === null) {
            // Legacy DesigInStop() looked at the Projects directorate (unit 180000);
            // a level that only exists in the divisions is simply "Division".
            $this->levelDesig = [];
            foreach (DB::table('cen.roles')->where('rol_unt_id', 180000)->get(['rol_level', 'rol_desigshort']) as $role) {
                $lvl = (int) $role->rol_level;
                if (! isset($this->levelDesig[$lvl]) && ! empty($role->rol_desigshort)) {
                    $this->levelDesig[$lvl] = $role->rol_desigshort;
                }
            }
        }

        if (isset($this->levelDesig[$level])) {
            return $this->levelDesig[$level];
        }

        return $level === $this->first() ? 'Division' : null;
    }

    /** Legacy GetCurrentMprGroup(): the running monthly cycle, e.g. "Nov 26". */
    public function currentGroup(): string
    {
        return Carbon::now()->format('M y');
    }

    /** A readable description of the route for the UI. */
    public function describe(): string
    {
        return implode(' → ', array_map(
            fn ($lvl) => ($this->desigForLevel($lvl) ?: ('L' . $lvl)),
            $this->stops()
        ));
    }
}
