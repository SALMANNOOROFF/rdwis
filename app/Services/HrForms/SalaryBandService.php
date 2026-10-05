<?php

namespace App\Services\HrForms;

use App\Models\HrForms\SalaryBand;
use Illuminate\Support\Facades\DB;

class SalaryBandService
{
    /**
     * Resolve parent salary band for a designation or subgrade (e.g. RO-I -> RO)
     */
    public function resolveBand(?string $gradeOrDesignation): ?SalaryBand
    {
        if (empty($gradeOrDesignation)) {
            return null;
        }

        $term = strtoupper(trim($gradeOrDesignation));

        // 1. Direct match on designation name
        $band = SalaryBand::where('is_active', true)
            ->whereRaw('UPPER(designation) = ?', [$term])
            ->first();

        if ($band) {
            return $band;
        }

        // 2. Match within sub_grades JSON array
        $bands = SalaryBand::where('is_active', true)->get();
        foreach ($bands as $b) {
            $subGrades = $b->sub_grades ?? [];
            if (is_array($subGrades)) {
                foreach ($subGrades as $sg) {
                    if (strtoupper(trim($sg)) === $term) {
                        return $b;
                    }
                }
            }
        }

        // 3. Fallback prefix / pattern matching (e.g. 'RO-1', 'RO - I', 'RESEARCH OFFICER')
        foreach ($bands as $b) {
            $dUpper = strtoupper($b->designation);
            if (str_starts_with($term, $dUpper . '-') || str_starts_with($term, $dUpper . ' ') || str_starts_with($term, $dUpper . '_')) {
                return $b;
            }
            if (str_contains($term, $dUpper)) {
                return $b;
            }
        }

        return null;
    }

    /**
     * Alias for resolveBand
     */
    public function findBandForGrade(?string $gradeOrDesignation): ?SalaryBand
    {
        return $this->resolveBand($gradeOrDesignation);
    }

    /**
     * Validate salary against band. Returns array with [isValid, warningMessage, band]
     */
    public function evaluateSalary(?string $gradeOrDesignation, float $salary): array
    {
        $band = $this->resolveBand($gradeOrDesignation);

        if (!$band) {
            return [
                'has_band' => false,
                'in_band'  => true,
                'warning'  => null,
                'min'      => null,
                'max'      => null,
                'band_name'=> null,
            ];
        }

        $min = (float) $band->min_salary;
        $max = (float) $band->max_salary;

        $inBand = ($salary >= $min && $salary <= $max);
        $warning = null;

        if (!$inBand) {
            if ($salary < $min) {
                $diff = number_format($min - $salary);
                $warning = "Proposed salary (Rs. " . number_format($salary) . ") is below the Annex K starting salary band for {$band->designation} (Rs. " . number_format($min) . " - Rs. " . number_format($max) . ") by Rs. {$diff}. Policy allows warning only.";
            } else {
                $diff = number_format($salary - $max);
                $warning = "Proposed salary (Rs. " . number_format($salary) . ") exceeds the Annex K starting salary band for {$band->designation} (Rs. " . number_format($min) . " - Rs. " . number_format($max) . ") by Rs. {$diff}. Requires Board justification.";
            }
        }

        return [
            'has_band'  => true,
            'in_band'   => $inBand,
            'warning'   => $warning,
            'min'       => $min,
            'max'       => $max,
            'band_name' => $band->designation,
        ];
    }
}
