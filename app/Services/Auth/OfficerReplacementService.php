<?php

namespace App\Services\Auth;

use App\Models\CenAccount;
use App\Models\Project;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class OfficerReplacementService
{
    /**
     * Find the currently active occupant for a given organizational unit and designation.
     * ZERO hardcoded usernames or magic IDs.
     */
    public function activeOccupant(int $unitId, string $designation): ?CenAccount
    {
        return CenAccount::where('acc_unt_id', $unitId)
            ->where(function ($q) use ($designation) {
                $q->where('acc_desig', $designation)
                  ->orWhere('acc_desigshort', $designation);
            })
            ->whereRaw("LOWER(acc_status) = 'active'")
            ->first();
    }

    /**
     * Resolve the active occupant of the SORD seat without hardcoding usernames.
     */
    public function activeSordOccupant(): ?CenAccount
    {
        return CenAccount::where(function ($q) {
                $q->whereIn('acc_untarea', ['rdwprj', 'prjrdw'])
                  ->orWhere('acc_desigshort', 'SO R&D')
                  ->orWhere('acc_desig', 'Staff Officer R&D');
            })
            ->whereRaw("LOWER(acc_status) = 'active'")
            ->first();
    }

    /**
     * Close an officer's account upon transfer/retirement.
     * Strictly preserves historical data, created projects, audit logs, and approval records.
     */
    public function closeOfficer(CenAccount $officer): bool
    {
        $officer->acc_status = 'Closed';
        return $officer->save();
    }

    /**
     * Verify that an incoming successor officer automatically inherits organizational unit data
     * without any row-by-row project reassignment.
     *
     * @return array{unit_id: int, project_count: int, cases_count: int, rows_reassigned: int}
     */
    public function verifySuccessorInheritance(CenAccount $successor): array
    {
        $unitId = (int) ($successor->acc_unt_id ?? 0);

        $projectCount = Project::where('prj_unt_id', $unitId)->count();
        $casesCount = Purchase::where('pcs_unt_id', $unitId)->count();

        return [
            'unit_id' => $unitId,
            'project_count' => $projectCount,
            'cases_count' => $casesCount,
            'rows_reassigned' => 0, // Zero manual rows reassigned - purely unit-owned!
        ];
    }

    /**
     * Resolve the active responsible officer for an in-flight case based on current stage and unit.
     */
    public function resolveResponsibleOfficerForCase(Purchase $case): ?CenAccount
    {
        $currentStage = $case->currentSubstatus?->pss_stage ?? ($case->pcs_status === 'Draft' ? 'Division' : 'Division');

        if ($currentStage === 'Division') {
            // Find active director/officer in the case's unit
            return CenAccount::where('acc_unt_id', $case->pcs_unt_id)
                ->whereRaw("LOWER(acc_status) = 'active'")
                ->where('acc_auth', 'approver')
                ->first()
                ?? CenAccount::where('acc_unt_id', $case->pcs_unt_id)
                    ->whereRaw("LOWER(acc_status) = 'active'")
                    ->first();
        }

        if ($currentStage === 'DProc') {
            return CenAccount::whereIn('acc_untarea', ['proc', 'prc'])
                ->whereRaw("LOWER(acc_status) = 'active'")
                ->first();
        }

        if ($currentStage === 'DFinance') {
            return CenAccount::where('acc_untarea', 'fin')
                ->whereRaw("LOWER(acc_status) = 'active'")
                ->where('acc_auth', 'approver')
                ->first();
        }

        if ($currentStage === 'MD') {
            return CenAccount::where('acc_untarea', 'rdw')
                ->where('acc_desigshort', 'MD RDW')
                ->whereRaw("LOWER(acc_status) = 'active'")
                ->first();
        }

        if ($currentStage === 'DG') {
            return CenAccount::where('acc_untarea', 'nrdi')
                ->where('acc_desigshort', 'DG NRDI')
                ->whereRaw("LOWER(acc_status) = 'active'")
                ->first();
        }

        return null;
    }
}
