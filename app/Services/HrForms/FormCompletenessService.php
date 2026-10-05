<?php

namespace App\Services\HrForms;

class FormCompletenessService
{
    /**
     * Check if form data has any meaningful manual input provided by user.
     */
    public function hasManualData(array $formData): bool
    {
        $manual = $formData['manual'] ?? [];
        if (!is_array($manual) || empty($manual)) {
            return false;
        }

        return $this->arrayHasMeaningfulValues($manual);
    }

    private function arrayHasMeaningfulValues(array $arr): bool
    {
        foreach ($arr as $key => $val) {
            if ($val === null) {
                continue;
            }
            if (is_array($val)) {
                if ($this->arrayHasMeaningfulValues($val)) {
                    return true;
                }
            } elseif (is_string($val)) {
                $trimmed = trim($val);
                if ($trimmed !== '' && $trimmed !== '0' && $trimmed !== 'Recommended for Selection Board interview') {
                    return true;
                }
            } elseif (is_numeric($val) && $val > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Evaluate completeness and determine appropriate status.
     *
     * @param string $formCode
     * @param array $formData
     * @param string $currentStatus
     * @return array [is_complete => bool, missing_fields => array, status => string]
     */
    public function evaluateStatus(string $formCode, array $formData, string $currentStatus = 'Draft'): array
    {
        if ($currentStatus === 'Submitted') {
            return [
                'is_complete'    => true,
                'missing_fields' => [],
                'status'         => 'Submitted',
            ];
        }

        if ($currentStatus === 'Scheduled') {
            return [
                'is_complete'    => false,
                'missing_fields' => ['joining_not_triggered'],
                'status'         => 'Scheduled',
            ];
        }

        $missingFields = $formData['missing_fields'] ?? [];

        // Dynamic rule for Annex N (F-09): shift_amount & justification required if hr balance is insufficient
        if ($formCode === 'RDW/HR/F-09') {
            $hrBalanceSufficient = $formData['live']['hr_balance_sufficient'] ?? true;
            if (!$hrBalanceSufficient) {
                if (($formData['manual']['shift_amount'] ?? null) === null) {
                    if (!in_array('shift_amount', $missingFields)) {
                        $missingFields[] = 'shift_amount';
                    }
                } else {
                    $missingFields = array_diff($missingFields, ['shift_amount']);
                }

                if (empty($formData['manual']['shift_justification'] ?? null)) {
                    if (!in_array('shift_justification', $missingFields)) {
                        $missingFields[] = 'shift_justification';
                    }
                } else {
                    $missingFields = array_diff($missingFields, ['shift_justification']);
                }
            }
        }
        $missingFields = array_values($missingFields);
        $isComplete = empty($missingFields);

        if ($isComplete) {
            $status = 'Ready';
        } else {
            $hasManual = $this->hasManualData($formData);
            $status = $hasManual ? 'Pending Input' : 'Draft';
        }

        return [
            'is_complete'    => $isComplete,
            'missing_fields' => $missingFields,
            'status'         => $status,
        ];
    }
}
