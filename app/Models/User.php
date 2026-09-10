<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Unit;

class User extends Authenticatable
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

    public function isDivision()
    {
        if ($this->isSORD()) {
            return false;
        }

        return $this->normalizedArea() === 'prj';
    }

    public function isFinance(): bool
    {
        $area = $this->normalizedArea();
        return in_array($area, ['fin', 'finance'], true) || stripos((string) ($this->acc_desig ?? ''), 'finance') !== false;
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
        $areas = [$userArea];

        if (in_array($userArea, ['rdwprj', 'prjrdw'], true)) {
            $areas = ['rdw', 'prj', 'rdwprj', 'prjrdw'];
        }

        return in_array(strtolower(trim($area)), $areas, true);
    }

    public function canSeeRecord(int $unitId): bool
    {
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
     * Check if user is Managing Director (MD), Deputy Director General (DDG), or Director General (DG).
     */
    public function isMdDdgDg(): bool
    {
        $desig = strtoupper(trim((string) ($this->acc_desig ?? '')));
        $auth = strtolower(trim((string) ($this->acc_auth ?? '')));
        $username = strtolower(trim((string) ($this->acc_username ?? '')));

        if (
            str_contains($desig, 'MANAGING DIRECTOR') ||
            str_contains($desig, 'DIRECTOR GENERAL') ||
            str_contains($desig, 'DEPUTY DIRECTOR GENERAL') ||
            preg_match('/\b(MD|DG|DDG)\b/i', $desig)
        ) {
            return true;
        }

        if (in_array($auth, ['md', 'ddg', 'dg'], true)) {
            return true;
        }

        if (in_array($username, ['srehman', 'srrehman', 'surehman', 'sarshad', 'kmunir', 'mhussain', 'famir', 'jkhan', 'jhussain', 'aakhtar', 'mimran'], true)) {
            return true;
        }

        return false;
    }
}
