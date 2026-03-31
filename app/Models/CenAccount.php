<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CenAccount extends Authenticatable
{
    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    protected $rememberTokenName = null;

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

    public function getAuthIdentifierName()
    {
        return 'acc_id';
    }

    public function isSORD()
    {
        $area = $this->normalizedArea();

        return in_array($area, ['rdwprj', 'prjrdw', 'rdw'], true);
    }

    public function isDivision()
    {
        return $this->normalizedArea() === 'prj';
    }

    public function isApprover(): bool
    {
        return $this->normalizedAuth() === 'approver';
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

    public function unitRange(): array
    {
        return ['lower' => $this->acc_lowers, 'upper' => $this->acc_uppers];
    }

    public function moduleRange(): array
    {
        return ['lower' => $this->acc_lowerm, 'upper' => $this->acc_upperm];
    }
}
