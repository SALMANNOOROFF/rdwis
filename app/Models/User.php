<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Unit;

class User extends CenAccount
{
    use HasFactory, Notifiable;

    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';
    public $timestamps = false; 
    protected $rememberTokenName = null;

    protected $fillable = [
        'acc_username', 'acc_pass', 'acc_unt_id', 'acc_desig', 'acc_level',
    ];

    protected $hidden = ['acc_pass'];

    private function normalizedArea(): string
    {
        return strtolower(trim((string) ($this->acc_untarea ?? '')));
    }

    private function normalizedAuth(): string
    {
        return strtolower(trim((string) ($this->acc_auth ?? '')));
    }

    public function getAuthPassword()
    {
        return $this->acc_pass;
    }

    public function getAuthPasswordName()
    {
        return 'acc_pass';
    }

    // --- RELATIONSHIPS ---
    public function role()
    {
        return $this->belongsTo(Role::class, 'acc_desig', 'rol_desig');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'acc_unt_id', 'unt_id');
    }

    // --- ROLE & AREA HELPERS ---

    public function isSORD()
    {
        $area = $this->normalizedArea();

        return in_array($area, ['rdwprj', 'prjrdw', 'rdw'], true);
    }

    public function isCentralDepartment(): bool
    {
        $untId = (int) ($this->acc_unt_id ?? 0);
        $area = $this->normalizedArea();

        return in_array($untId, [800000, 810000, 820000, 840000, 860000, 880000], true)
            || in_array($area, ['proc', 'prc', 'fin', 'finance', 'hr', 'is', 'it', 'admin', 'adm'], true);
    }

    public function isMyDepartmentMode(): bool
    {
        if (in_array($this->normalizedArea(), ['adm', 'admin'], true)) {
            return true;
        }

        return $this->isCentralDepartment() && session('active_dept_scope') === 'my';
    }

    public function isAllDepartmentsMode(): bool
    {
        return ! $this->isMyDepartmentMode();
    }

    public function isDivision()
    {
        if ($this->isMyDepartmentMode()) {
            return true;
        }

        if ($this->isSORD()) {
            return false;
        }

        return in_array($this->normalizedArea(), ['prj', 'adm', 'admin'], true);
    }

    public function isFinance(): bool
    {
        if ($this->isMyDepartmentMode()) {
            return false;
        }

        $area = $this->normalizedArea();
        return in_array($area, ['fin', 'finance'], true) || stripos((string) ($this->acc_desig ?? ''), 'finance') !== false;
    }

    public function isProcurement(): bool
    {
        if ($this->isMyDepartmentMode()) {
            return false;
        }

        $area = $this->normalizedArea();
        return in_array($area, ['proc', 'prc'], true) || stripos((string) ($this->acc_desig ?? ''), 'procurement') !== false;
    }

    public function isHr(): bool
    {
        if ($this->isMyDepartmentMode()) {
            return false;
        }

        $area = $this->normalizedArea();
        return in_array($area, ['hr'], true) || stripos((string) ($this->acc_desig ?? ''), 'hr') !== false;
    }

    public function isApprover(): bool
    {
        return in_array($this->normalizedAuth(), ['approver', 'editor'], true);
    }

    public function isViewer(): bool
    {
        return $this->normalizedAuth() === 'viewer';
    }

    public function canAccessArea(string $area): bool
    {
        $userArea = $this->normalizedArea();
        $areas = \App\Services\Auth\AreaDefinition::getAllowedRouteAreas($userArea);

        return in_array(strtolower(trim($area)), $areas, true);
    }

    public function canSeeRecord(int $unitId): bool
    {
        if ($this->isMyDepartmentMode()) {
            return $unitId === (int) ($this->acc_unt_id ?? 0);
        }

        if ($this->acc_lowers == 0) {
            return $unitId >= $this->acc_lowerm && $unitId <= $this->acc_upperm;
        }

        return $unitId >= $this->acc_lowers && $unitId <= $this->acc_uppers;
    }

    public function isSingleUnitAccess(): bool
    {
        return $this->acc_access === 'single';
    }

    public function isMultiUnitAccess(): bool
    {
        return $this->acc_access === 'multiple';
    }

    public function unitRange(): array
    {
        return ['lower' => $this->acc_lowers, 'upper' => $this->acc_uppers];
    }

    public function moduleRange(): array
    {
        return ['lower' => $this->acc_lowerm, 'upper' => $this->acc_upperm];
    }

    /**
     * Check if user is Director Logistics (DLOG).
     */
    public function isDlog(): bool
    {
        $desig = strtoupper(trim((string) ($this->acc_desig ?? '')));
        $auth = strtolower(trim((string) ($this->acc_auth ?? '')));
        $username = strtolower(trim((string) ($this->acc_username ?? '')));
        $desigShort = strtoupper(trim((string) ($this->acc_desigshort ?? '')));
        $area = strtolower(trim((string) ($this->acc_untarea ?? '')));

        if (
            str_contains($desig, 'DIRECTOR LOGISTICS') ||
            str_contains($desig, 'DIRECTOR LOG') ||
            in_array($desigShort, ['DLOG', 'DIR LOG', 'D LOG', 'DIR LOGISTICS'], true) ||
            preg_match('/\b(DLOG)\b/i', $desig) ||
            in_array($auth, ['dlog', 'log'], true) ||
            in_array($area, ['log', 'dlog', 'logistics'], true) ||
            in_array($username, ['dlog', 'logistics'], true)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is Managing Director (MD), Deputy Director General (DDG), or Director General (DG), or Director Logistics (DLOG).
     */
    public function isMdDdgDg(): bool
    {
        if ($this->isDlog()) {
            return true;
        }

        $desig = strtoupper(trim((string) ($this->acc_desig ?? '')));
        $auth = strtolower(trim((string) ($this->acc_auth ?? '')));
        $username = strtolower(trim((string) ($this->acc_username ?? '')));

        $desigShort = strtoupper(trim((string) ($this->acc_desigshort ?? '')));

        if (
            str_contains($desig, 'MANAGING DIRECTOR') ||
            str_contains($desig, 'DIRECTOR GENERAL') ||
            str_contains($desig, 'DEPUTY DIRECTOR GENERAL') ||
            in_array($desigShort, ['MD', 'DG', 'DDG', 'MD RDW', 'DG NRDI', 'DDG NRD'], true) ||
            preg_match('/\b(MD|DG|DDG)\b/i', $desig)
        ) {
            return true;
        }

        if (in_array($auth, ['md', 'ddg', 'dg'], true)) {
            return true;
        }

        return false;
    }
}
