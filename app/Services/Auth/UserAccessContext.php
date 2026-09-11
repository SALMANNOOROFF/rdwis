<?php

namespace App\Services\Auth;

use App\Models\CenAccount;
use Illuminate\Support\Facades\Auth;

class UserAccessContext
{
    protected ?CenAccount $user = null;
    protected ?string $roleSlug = null;
    protected array $permissions = [];

    public function __construct(?CenAccount $user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    public static function forUser(?CenAccount $user): self
    {
        return new self($user);
    }

    public static function current(): self
    {
        return app(self::class);
    }

    public function user(): ?CenAccount
    {
        return $this->user;
    }

    public function isAuthenticated(): bool
    {
        return $this->user !== null && strtolower(trim((string) $this->user->acc_status)) === 'active';
    }

    /**
     * Centralized Super Admin / God Mode check.
     * Uses configuration and desigtype, avoiding hardcoded scattered strings.
     */
    public function isSuperAdmin(): bool
    {
        if (! $this->user) {
            return false;
        }

        $superadminUsername = config('auth.superadmin_username', 'superadminrdw');
        if (strcasecmp($this->user->acc_username, $superadminUsername) === 0) {
            return true;
        }

        if (strtolower(trim((string) ($this->user->acc_desigtype ?? ''))) === 'superadmin') {
            return true;
        }

        if (session('impersonated_by_god')) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is a Command Officer (MD, DDG, DG) via semantic designation/role.
     * ZERO hardcoded personal usernames.
     */
    public function isCommand(): bool
    {
        if (! $this->user) {
            return false;
        }

        $desig = strtoupper(trim((string) ($this->user->acc_desig ?? '')));
        $desigShort = strtoupper(trim((string) ($this->user->acc_desigshort ?? '')));
        $auth = strtolower(trim((string) ($this->user->acc_auth ?? '')));

        if (
            str_contains($desig, 'MANAGING DIRECTOR') ||
            str_contains($desig, 'DIRECTOR GENERAL') ||
            str_contains($desig, 'DEPUTY DIRECTOR GENERAL') ||
            ($desig === 'DIRECTOR NRD' && strtolower(trim((string)($this->user->acc_untarea ?? ''))) === 'hqs') ||
            in_array($desigShort, ['MD', 'DG', 'DDG', 'DG NRDI', 'MD RDW', 'DDG NRD', 'DNRD'], true) ||
            preg_match('/\b(MD|DG|DDG)\b/i', $desig)
        ) {
            return true;
        }

        if (in_array($auth, ['md', 'ddg', 'dg'], true)) {
            return true;
        }

        return false;
    }

    public function isDg(): bool
    {
        if (! $this->user) return false;
        $desig = strtoupper(trim((string) ($this->user->acc_desig ?? '')));
        $desigShort = strtoupper(trim((string) ($this->user->acc_desigshort ?? '')));
        return str_contains($desig, 'DIRECTOR GENERAL') && !str_contains($desig, 'DEPUTY')
            || in_array($desigShort, ['DG', 'DG NRDI'], true);
    }

    public function isMd(): bool
    {
        if (! $this->user) return false;
        $desig = strtoupper(trim((string) ($this->user->acc_desig ?? '')));
        $desigShort = strtoupper(trim((string) ($this->user->acc_desigshort ?? '')));
        return str_contains($desig, 'MANAGING DIRECTOR') || in_array($desigShort, ['MD', 'MD RDW'], true);
    }

    public function isDdg(): bool
    {
        if (! $this->user) return false;
        $desig = strtoupper(trim((string) ($this->user->acc_desig ?? '')));
        $desigShort = strtoupper(trim((string) ($this->user->acc_desigshort ?? '')));
        $area = strtolower(trim((string) ($this->user->acc_untarea ?? '')));
        return str_contains($desig, 'DEPUTY DIRECTOR GENERAL')
            || in_array($desigShort, ['DDG', 'DDG NRD', 'DNRD'], true)
            || ($desig === 'DIRECTOR NRD' && $area === 'hqs');
    }

    /**
     * Check if user is Staff Officer R&D (SORD).
     * Semantic detection: designation 'SO R&D' or area 'rdwprj'/'prjrdw'.
     */
    public function isSord(): bool
    {
        if (! $this->user) {
            return false;
        }

        $desigShort = strtoupper(trim((string) ($this->user->acc_desigshort ?? '')));
        $desig = strtoupper(trim((string) ($this->user->acc_desig ?? '')));
        $area = strtolower(trim((string) ($this->user->acc_untarea ?? '')));

        return in_array($desigShort, ['SO R&D', 'SORD', 'SO(R&D)'], true)
            || str_contains($desig, 'STAFF OFFICER R&D')
            || in_array($area, ['rdwprj', 'prjrdw'], true);
    }

    /**
     * Check if user belongs to a Division in Projects area.
     */
    public function isDivision(): bool
    {
        if (! $this->user) {
            return false;
        }

        if ($this->isSord() || $this->isCommand()) {
            return false;
        }

        $area = strtolower(trim((string) ($this->user->acc_untarea ?? '')));
        return $area === AreaDefinition::PROJECTS;
    }

    /**
     * Check if user is an approver.
     */
    public function isApprover(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->user) {
            return false;
        }

        return strtolower(trim((string) ($this->user->acc_auth ?? ''))) === 'approver';
    }

    /**
     * Check if user is an editor (or approver).
     */
    public function isEditor(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->user) {
            return false;
        }

        $auth = strtolower(trim((string) ($this->user->acc_auth ?? '')));
        return in_array($auth, ['editor', 'approver'], true);
    }

    /**
     * Check if user is strictly a viewer.
     */
    public function isViewer(): bool
    {
        if ($this->isSuperAdmin() || $this->isCommand()) {
            return false;
        }

        if (! $this->user) {
            return true;
        }

        return strtolower(trim((string) ($this->user->acc_auth ?? ''))) === 'viewer';
    }

    /**
     * Check if user has single unit access.
     */
    public function isSingleAccess(): bool
    {
        if (! $this->user) return true;
        return strtolower(trim((string) ($this->user->acc_access ?? ''))) === 'single';
    }

    /**
     * Check if user has multiple unit access.
     */
    public function isMultipleAccess(): bool
    {
        if (! $this->user) return false;
        return strtolower(trim((string) ($this->user->acc_access ?? ''))) === 'multiple';
    }

    /**
     * Resolve semantic role slug for the user without raw unit numbers or IDs.
     */
    public function getRoleSlug(): string
    {
        if ($this->roleSlug !== null) {
            return $this->roleSlug;
        }

        if (! $this->user) {
            return $this->roleSlug = 'GUEST';
        }

        if ($this->isSuperAdmin()) {
            return $this->roleSlug = 'SUPERADMIN';
        }

        if ($this->isDg()) {
            return $this->roleSlug = 'COMMAND_DG';
        }

        if ($this->isMd()) {
            return $this->roleSlug = 'COMMAND_MD';
        }

        if ($this->isDdg()) {
            return $this->roleSlug = 'COMMAND_DDG';
        }

        if ($this->isSord()) {
            return $this->roleSlug = 'SORD';
        }

        $desig = strtoupper(trim((string) ($this->user->acc_desig ?? '')));
        $desigShort = strtoupper(trim((string) ($this->user->acc_desigshort ?? '')));
        $desigType = strtolower(trim((string) ($this->user->acc_desigtype ?? '')));
        $area = strtolower(trim((string) ($this->user->acc_untarea ?? '')));

        // Director R&D
        if (str_contains($desig, 'DIRECTOR R&D') || in_array($desigShort, ['DR&D', 'DIR R&D'], true)) {
            return $this->roleSlug = 'DEPT_DIRECTOR_RD';
        }

        // Procurement (Evaluated before Finance because Unit 810000 historically carried unt_area='fin')
        if (AreaDefinition::isProcurement($area) || str_contains($desig, 'PROCUREMENT') || in_array($desigShort, ['DPROC', 'DIR PROC'], true)) {
            if ($desigType === 'lead' || str_contains($desig, 'DIRECTOR') || $desigShort === 'DPROC') {
                return $this->roleSlug = 'PROC_DIRECTOR';
            }
            return $this->roleSlug = 'PROC_OFFICER';
        }

        // Finance
        if (AreaDefinition::isFinance($area) || str_contains($desig, 'FINANCE')) {
            if ($desigType === 'lead' || str_contains($desig, 'DIRECTOR')) {
                return $this->roleSlug = 'DEPT_DIRECTOR_FIN';
            }
            return $this->roleSlug = 'FIN_OFFICER';
        }

        // HR
        if (AreaDefinition::isHr($area) || str_contains($desig, 'HR')) {
            if ($desigType === 'lead' || str_contains($desig, 'MANAGER') || str_contains($desig, 'DIRECTOR')) {
                return $this->roleSlug = 'HR_MANAGER';
            }
            return $this->roleSlug = 'HR_OFFICER';
        }

        // IT
        if (AreaDefinition::isIt($area) || str_contains($desig, 'IT')) {
            if ($desigType === 'lead' || str_contains($desig, 'MANAGER') || str_contains($desig, 'ADMIN')) {
                return $this->roleSlug = 'IT_ADMIN';
            }
            return $this->roleSlug = 'IT_OFFICER';
        }

        // Division in Projects area
        if ($area === AreaDefinition::PROJECTS) {
            if ($desigType === 'lead' || str_contains($desig, 'DIRECTOR')) {
                return $this->roleSlug = 'DIV_DIRECTOR';
            }
            return $this->roleSlug = 'DIV_OFFICER';
        }

        // Physical Administration / IS / MTSS / General fallback
        if ($area === AreaDefinition::ADMIN) {
            return $this->roleSlug = 'ADMIN_DEPT';
        }

        if ($area === AreaDefinition::IS) {
            return $this->roleSlug = 'IS_DEPT';
        }

        if ($area === AreaDefinition::MTSS) {
            return $this->roleSlug = 'MTSS_DEPT';
        }

        return $this->roleSlug = 'USER';
    }

    /**
     * Get user's primary unit ID safely.
     */
    public function getUnitId(): int
    {
        return (int) ($this->user->acc_unt_id ?? 0);
    }

    /**
     * Get normalized user area.
     */
    public function getArea(): string
    {
        return AreaDefinition::normalize($this->user->acc_untarea ?? '');
    }
}
