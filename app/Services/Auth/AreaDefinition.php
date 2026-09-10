<?php

namespace App\Services\Auth;

class AreaDefinition
{
    // Logical constants (not stored in DB, used for code semantics)
    public const PROJECTS = 'prj';
    public const SORD = 'sord';
    public const COMMAND = 'cmd';
    public const PROCUREMENT = 'proc';
    public const FINANCE = 'fin';
    public const HR = 'hr';
    public const IT = 'it';
    public const ADMIN = 'adm';
    public const IS = 'is';
    public const MTSS = 'mtss';

    /**
     * Stored database area values in cen.accounts and cen.units:
     * 'prj', 'rdwprj', 'prjrdw', 'rdw', 'nrdi', 'hqs', 'fin', 'proc', 'prc', 'hr', 'adm', 'it', 'is', 'mtss'
     */

    /**
     * Normalize area strings to a standard representation without modifying DB values.
     */
    public static function normalize(?string $area): string
    {
        $cleaned = strtolower(trim((string) $area));

        if (in_array($cleaned, ['proc', 'prc', 'procurement'], true)) {
            return self::PROCUREMENT;
        }

        if (in_array($cleaned, ['rdwprj', 'prjrdw', 'sord'], true)) {
            return self::SORD;
        }

        if (in_array($cleaned, ['rdw', 'nrdi', 'hqs'], true)) {
            return $cleaned; // Keep specific command wing/institute area
        }

        return $cleaned;
    }

    /**
     * Determine if an area string corresponds to Procurement (proc or prc).
     */
    public static function isProcurement(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['proc', 'prc', 'procurement'], true);
    }

    /**
     * Determine if an area string corresponds to SORD (rdwprj or prjrdw).
     */
    public static function isSord(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['rdwprj', 'prjrdw', 'sord'], true);
    }

    /**
     * Determine if an area string corresponds to Command (rdw, nrdi, hqs).
     */
    public static function isCommand(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['rdw', 'nrdi', 'hqs', 'cmd'], true);
    }

    /**
     * Determine if an area string corresponds to Division / Projects.
     */
    public static function isProjects(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['prj', 'project', 'projects'], true);
    }

    /**
     * Determine if an area string corresponds to Finance.
     */
    public static function isFinance(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['fin', 'finance'], true);
    }

    /**
     * Determine if an area string corresponds to HR.
     */
    public static function isHr(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['hr', 'humanresources'], true);
    }

    /**
     * Determine if an area string corresponds to IT.
     */
    public static function isIt(?string $area): bool
    {
        $cleaned = strtolower(trim((string) $area));
        return in_array($cleaned, ['it', 'informationtechnology'], true);
    }

    /**
     * Get all route area aliases allowed for a given user account area.
     * Preserves exact current CheckArea middleware mappings without hardcoded usernames.
     *
     * @param string|null $userArea
     * @return array<string>
     */
    public static function getAllowedRouteAreas(?string $userArea): array
    {
        $userArea = strtolower(trim((string) $userArea));
        $allowed = [$userArea];

        // RDW / SORD Mapping
        if (in_array($userArea, ['rdw', 'rdwprj', 'prjrdw'], true)) {
            $allowed = ['rdw', 'prj', 'rdwprj', 'prjrdw'];
        }

        // DG / NRDI Mapping (Full Access across organizational wings)
        if ($userArea === 'nrdi') {
            $allowed = ['nrdi', 'prj', 'hr', 'fin', 'rdw', 'rdwprj', 'prjrdw', 'proc', 'prc', 'hqs'];
        }

        // Procurement Department Mapping (both proc and prc)
        if (in_array($userArea, ['proc', 'prc'], true)) {
            $allowed = ['proc', 'prc', 'prj'];
        }

        // Finance Department Mapping
        if ($userArea === 'fin') {
            $allowed = ['fin', 'prj'];
        }

        // HQs / DDG Mapping
        if ($userArea === 'hqs') {
            $allowed = ['hqs', 'prj', 'rdw'];
        }

        // HR Department Mapping
        if ($userArea === 'hr') {
            $allowed = ['hr', 'prj'];
        }

        // IT Department Mapping
        if ($userArea === 'it') {
            $allowed = ['it', 'admin'];
        }

        return array_values(array_unique($allowed));
    }
}
