<?php

namespace App\Services;

use App\Models\AudRev;
use App\Models\AudRevData;
use App\Models\CenAccount;
use App\Models\FinSubhead;
use App\Models\FundShift;
use App\Models\FundShiftDecision;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FundShiftingService
{
    public function __construct(
        protected FinancialIntelligenceService $financialService
    ) {}

    /**
     * Get accessible projects with associated financial heads.
     */
    public function getAvailableProjects(CenAccount|User $user): Collection
    {
        $query = DB::table('prj.projects as p')
            ->join('cen.heads as h', 'h.hed_prj_id', '=', 'p.prj_id')
            ->select(
                'p.prj_id',
                'p.prj_title',
                'p.prj_code',
                'p.prj_status',
                'p.prj_unt_id',
                'h.hed_id',
                'h.hed_name',
                'h.hed_code'
            )
            ->orderBy('p.prj_title');

        if ($user->isDivision() && !$user->isMyDepartmentMode() && !$user->isCentralDepartment()) {
            if ($user->isSingleUnitAccess() && $user->acc_unt_id) {
                $query->where('p.prj_unt_id', $user->acc_unt_id);
            } elseif ($user->isMultiUnitAccess()) {
                $range = $user->unitRange();
                if ($range['lower'] > 0 && $range['upper'] > 0) {
                    $query->whereBetween('p.prj_unt_id', [$range['lower'], $range['upper']]);
                }
            }
        }

        return $query->get();
    }

    /**
     * Get detailed breakdown of subheads for a head including remaining / shiftable funds.
     */
    public function getSubheadsForHead(int $headId): array
    {
        $breakdown = $this->financialService->getSubheadBreakdown($headId);
        $dbSubheads = FinSubhead::where('sbh_hed_id', $headId)->get()->keyBy('sbh_name');

        $result = [];
        foreach ($breakdown as $item) {
            $name = $item['name'];
            $dbRecord = $dbSubheads->get($name);

            $result[] = [
                'sbh_id' => $dbRecord?->sbh_id,
                'name' => $name,
                'allocation' => (float) $item['allocation'],
                'expenditure' => (float) $item['expenditure'],
                'commitments' => (float) $item['commitments'],
                'in_process' => (float) $item['in_process'],
                'remaining' => (float) $item['remaining'],
                'can_be_spent' => (float) $item['can_be_spent'],
                'max_shiftable' => max(0, (float) $item['can_be_spent']),
            ];
        }

        return $result;
    }

    /**
     * Create a new draft fund shifting case.
     */
    public function createDraft(CenAccount|User $user, array $data): FundShift
    {
        $headId = (int) $data['hed_id'];
        $prjId = (int) $data['prj_id'];
        $fromSbhId = (int) $data['from_sbh_id'];
        $toSbhId = (int) $data['to_sbh_id'];
        $amount = round((float) $data['proposed_amount'], 2);
        $purpose = trim($data['purpose'] ?? '');

        if ($fromSbhId === $toSbhId) {
            throw new InvalidArgumentException('Source and Destination subheads must be different.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Proposed shifting amount must be greater than zero.');
        }

        $fromSbh = FinSubhead::where('sbh_id', $fromSbhId)->where('sbh_hed_id', $headId)->firstOrFail();
        $toSbh = FinSubhead::where('sbh_id', $toSbhId)->where('sbh_hed_id', $headId)->firstOrFail();

        // Check availability
        $subheads = $this->getSubheadsForHead($headId);
        $fromMeta = collect($subheads)->firstWhere('sbh_id', $fromSbhId);
        $maxAvailable = $fromMeta['max_shiftable'] ?? (float) $fromSbh->sbh_alloc;

        if ($amount > $maxAvailable) {
            throw new InvalidArgumentException("Proposed amount (Rs. " . number_format($amount, 2) . ") exceeds maximum shiftable balance (Rs. " . number_format($maxAvailable, 2) . ") of {$fromSbh->sbh_name}.");
        }

        return DB::transaction(function () use ($user, $prjId, $headId, $fromSbh, $toSbh, $amount, $purpose) {
            $unitId = (int) ($user->acc_unt_id ?? 100000);
            $project = DB::table('prj.projects')->where('prj_id', $prjId)->first();
            $shaId = DB::table('fin.sharesalloc')->where('sha_hed_id', $headId)->value('sha_id') ?: $headId;

            $fromAllocBefore = (float) $fromSbh->sbh_alloc;
            $fromAllocAfter = round($fromAllocBefore - $amount, 2);
            $toAllocBefore = (float) $toSbh->sbh_alloc;
            $toAllocAfter = round($toAllocBefore + $amount, 2);

            $revReason = $purpose ?: ("Rs. " . number_format($amount, 2) . "/- is being shifted from {$fromSbh->sbh_name} head to {$toSbh->sbh_name} head.");

            // 1. Create aud.revs header record with legacy parity: rev_obj = 'Allocation', rev_objid = sha_id
            $rev = AudRev::create([
                'rev_date' => now()->toDateString(),
                'rev_type' => 2, // FIELD_LEVEL
                'rev_intunt_id' => $unitId,
                'rev_obj' => 'Allocation',
                'rev_objid' => (string) $shaId,
                'rev_unt_id' => $unitId,
                'rev_status' => 'Under Revision',
                'rev_reason' => $revReason,
                'rev_ref' => $project->prj_code ?? 'Allocation',
            ]);

            // 2. Immediately populate aud.revdata field changes so Data Revision view displays diffs
            AudRevData::create([
                'rvd_rev_id' => $rev->rev_id,
                'rvd_table' => 'fin_subheads',
                'rvd_rowid' => (string) $fromSbh->sbh_id,
                'rvd_attrib' => 'sbh_alloc',
                'rvd_oldvalue' => (string) $fromAllocBefore,
                'rvd_newvalue' => (string) $fromAllocAfter,
                'rvd_datatype' => 'numeric',
                'rvd_type' => 2,
                'rvd_colname' => 'sbh_id',
                'rvd_alias' => "Subhead - {$fromSbh->sbh_name}",
            ]);

            AudRevData::create([
                'rvd_rev_id' => $rev->rev_id,
                'rvd_table' => 'fin_subheads',
                'rvd_rowid' => (string) $toSbh->sbh_id,
                'rvd_attrib' => 'sbh_alloc',
                'rvd_oldvalue' => (string) $toAllocBefore,
                'rvd_newvalue' => (string) $toAllocAfter,
                'rvd_datatype' => 'numeric',
                'rvd_type' => 2,
                'rvd_colname' => 'sbh_id',
                'rvd_alias' => "Subhead - {$toSbh->sbh_name}",
            ]);

            // 3. Create FundShift record linked to rev_id
            $shift = FundShift::create([
                'fsh_rev_id' => $rev->rev_id,
                'fsh_prj_id' => $prjId,
                'fsh_hed_id' => $headId,
                'fsh_unt_id' => $unitId,
                'fsh_from_sbh_id' => $fromSbh->sbh_id,
                'fsh_from_sbh_name' => $fromSbh->sbh_name,
                'fsh_to_sbh_id' => $toSbh->sbh_id,
                'fsh_to_sbh_name' => $toSbh->sbh_name,
                'fsh_proposed_amount' => $amount,
                'fsh_from_alloc_before' => $fromAllocBefore,
                'fsh_from_alloc_after' => $fromAllocAfter,
                'fsh_to_alloc_before' => $toAllocBefore,
                'fsh_to_alloc_after' => $toAllocAfter,
                'fsh_current_stage' => 'Division',
                'fsh_status' => 'Draft',
                'fsh_purpose' => $purpose,
                'fsh_created_by' => $user->acc_id,
                'fsh_created_name' => $user->acc_username,
                'fsh_created_rank' => $user->acc_rank ?? '',
                'fsh_created_desig' => $user->acc_desig ?? '',
            ]);

            return $shift;
        });
    }

    /**
     * Get list of authorities this user can forward this fund shift case to.
     * Uses PurchaseApprovalService to list all real departments/accounts.
     * Rule: Only MD can view DDG, DG, and DLOG accounts in destinations.
     * Non-MD users (Divisions, Finance, etc.) see other departments but not upper command.
     */
    public function getAvailableDestinations(CenAccount|User $user, FundShift $shift): array
    {
        $dests = app(\App\Services\PurchaseApprovalService::class)->getAvailableDestinations($user);

        // Remove current holder if set
        if ($shift->fsh_current_acc_id) {
            unset($dests['acc_' . $shift->fsh_current_acc_id]);
        }

        $isMd = (method_exists($user, 'isMd') && $user->isMd()) || strtolower(trim((string) ($user->acc_untarea ?? ''))) === 'rdw';
        $isDlog = (method_exists($user, 'isDlog') && $user->isDlog()) || in_array(strtolower(trim((string) ($user->acc_untarea ?? ''))), ['log', 'dlog', 'logistics'], true);
        $isCommand = (method_exists($user, 'isCommand') && $user->isCommand()) || in_array(strtolower(trim((string) ($user->acc_untarea ?? ''))), ['rdw', 'hqs', 'nrdi'], true);
        $isFinance = (method_exists($user, 'isFinance') && $user->isFinance()) || in_array(strtolower(trim((string) ($user->acc_untarea ?? ''))), ['fin', 'finance'], true);
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        $canSeeCommand = $isMd || $isDlog || $isCommand || $isFinance || $isSuperAdmin;

        // Filter out upper command (DDG, DG, DLOG) for non-authorized users (like ordinary division users)
        if (! $canSeeCommand) {
            foreach ($dests as $key => $d) {
                $st = strtoupper($d['stage'] ?? '');
                $badge = strtoupper($d['badge'] ?? '');
                if (in_array($st, ['DDG', 'DG', 'DLOG'], true) || in_array($badge, ['DDG', 'DG', 'DLOG', 'NRDI', 'HQS', 'LOG'], true)) {
                    unset($dests[$key]);
                }
            }
        }

        return $dests;
    }

    /**
     * Forward fund shifting case along workflow pipeline.
     */
    public function forward(FundShift $shift, CenAccount|User $user, string $remarks, ?string $targetStage = null, ?float $editedAmount = null): FundShift
    {
        $currentStage = $shift->fsh_current_stage;
        $targetAccId = null;
        $targetHolder = null;
        $nextStage = 'Finance';

        if ($targetStage && str_starts_with($targetStage, 'acc_')) {
            $targetAccId = (int) substr($targetStage, 4);
            $targetAcc = CenAccount::find($targetAccId);
            if ($targetAcc) {
                $targetHolder = $targetAcc->acc_name . ' (' . ($targetAcc->acc_desigshort ?: $targetAcc->acc_untarea) . ')';
                $area = strtolower(trim((string) $targetAcc->acc_untarea));
                if (in_array($area, ['fin', 'finance'], true)) {
                    $nextStage = 'Finance';
                } elseif (in_array($area, ['it'], true)) {
                    $nextStage = 'IT';
                } elseif (in_array($area, ['rdw', 'md'], true)) {
                    $nextStage = 'MD';
                } elseif (in_array($area, ['hqs', 'ddg'], true)) {
                    $nextStage = 'DDG';
                } elseif (in_array($area, ['nrdi', 'dg'], true)) {
                    $nextStage = 'DG';
                } elseif (in_array($area, ['log', 'dlog'], true)) {
                    $nextStage = 'DLOG';
                } else {
                    $nextStage = 'Division';
                }
            }
        } elseif ($targetStage) {
            $nextStage = $targetStage;
            $targetHolder = $targetStage;
        }

        // Restriction: Division cannot send directly to DDG, DG, or DLOG
        if ($user->isDivision() && !$user->isCentralDepartment()) {
            if (in_array(strtoupper($nextStage), ['DDG', 'DG', 'DLOG'], true)) {
                throw new InvalidArgumentException('Division cannot send directly to DDG, DG, or DLOG. Please route through Finance or MD.');
            }
        }

        // Support Finance editing the proposed amount
        $currentProposed = (float) $shift->fsh_proposed_amount;
        $finalAmount = $currentProposed;
        if ($editedAmount !== null && $editedAmount > 0 && abs($editedAmount - $currentProposed) > 0.009) {
            $finalAmount = round($editedAmount, 2);
            $diffText = "Proposed: Rs. " . number_format($currentProposed, 2) . ", Edited by " . ($user->acc_name ?: $user->acc_username) . " (" . ($user->acc_desig ?: 'Finance') . ") to: Rs. " . number_format($finalAmount, 2) . ". ";
            if (str_contains($remarks, '<li>')) {
                $remarks = preg_replace('/<li>/i', '<li>' . e($diffText), $remarks, 1);
            } else {
                $remarks = $diffText . $remarks;
            }
        }

        $fromAllocBefore = (float) $shift->fsh_from_alloc_before;
        $toAllocBefore = (float) $shift->fsh_to_alloc_before;
        $fromAllocAfter = round($fromAllocBefore - $finalAmount, 2);
        $toAllocAfter = round($toAllocBefore + $finalAmount, 2);

        // Determine status & action based on destination stage
        if (in_array(strtoupper($nextStage), ['MD', 'DDG', 'DG', 'DLOG'], true)) {
            $nextStatus = 'Under Approval';
            $action = "Forwarded to " . ($targetHolder ?: $nextStage) . " for Approval";
        } elseif ($nextStage === 'Finance') {
            $nextStatus = ($shift->fsh_status === 'Approved') ? 'Approved' : 'Under Review';
            $action = ($currentStage === 'Division') ? 'Released to Finance' : ("Forwarded to " . ($targetHolder ?: 'Finance'));
        } elseif ($nextStage === 'IT') {
            $nextStatus = ($shift->fsh_status === 'Approved') ? 'Approved' : 'Under Review';
            $action = "Forwarded to " . ($targetHolder ?: 'IT Department');
        } elseif ($nextStage === 'Division') {
            $nextStatus = 'Returned';
            $action = "Returned to " . ($targetHolder ?: 'Division');
        } else {
            $nextStatus = 'Under Review';
            $action = "Forwarded to " . ($targetHolder ?: $nextStage);
        }

        DB::transaction(function () use ($shift, $user, $remarks, $currentStage, $nextStage, $nextStatus, $action, $targetAccId, $targetHolder, $finalAmount, $fromAllocAfter, $toAllocAfter) {
            $shift->update([
                'fsh_current_stage' => $nextStage,
                'fsh_current_acc_id' => $targetAccId,
                'fsh_current_holder' => $targetHolder,
                'fsh_status' => $nextStatus,
                'fsh_proposed_amount' => $finalAmount,
                'fsh_from_alloc_after' => $fromAllocAfter,
                'fsh_to_alloc_after' => $toAllocAfter,
            ]);

            if ($shift->fsh_rev_id) {
                AudRev::where('rev_id', $shift->fsh_rev_id)->update([
                    'rev_status' => 'Under Revision',
                ]);

                AudRevData::where('rvd_rev_id', $shift->fsh_rev_id)
                    ->where('rvd_rowid', (string) $shift->fsh_from_sbh_id)
                    ->update(['rvd_newvalue' => (string) $fromAllocAfter]);

                AudRevData::where('rvd_rev_id', $shift->fsh_rev_id)
                    ->where('rvd_rowid', (string) $shift->fsh_to_sbh_id)
                    ->update(['rvd_newvalue' => (string) $toAllocAfter]);
            }

            FundShiftDecision::create([
                'fsd_fsh_id' => $shift->fsh_id,
                'fsd_acc_id' => $user->acc_id,
                'fsd_user_name' => $user->acc_username,
                'fsd_user_rank' => $user->acc_rank ?? '',
                'fsd_user_desig' => $user->acc_desig ?? '',
                'fsd_role' => $user->acc_desig ?? 'Reviewer',
                'fsd_action' => $action,
                'fsd_from_stage' => $currentStage,
                'fsd_to_stage' => $nextStage,
                'fsd_remarks' => $remarks,
                'fsd_dtg' => now(),
            ]);
        });

        return $shift->fresh();
    }

    /**
     * Approve fund shifting case by Finance or Command.
     * Once approved, the case is routed to IT for implementation.
     */
    public function approve(FundShift $shift, CenAccount|User $user, ?string $remarks = null, ?float $editedAmount = null): FundShift
    {
        $currentProposed = (float) $shift->fsh_proposed_amount;
        $finalAmount = $currentProposed;
        $cleanRemarks = trim((string) $remarks);

        if ($editedAmount !== null && $editedAmount > 0 && abs($editedAmount - $currentProposed) > 0.009) {
            $finalAmount = round($editedAmount, 2);
            $diffText = "Proposed: Rs. " . number_format($currentProposed, 2) . ", Edited by " . ($user->acc_name ?: $user->acc_username) . " (" . ($user->acc_desig ?: 'Finance') . ") to: Rs. " . number_format($finalAmount, 2) . ". ";
            if (str_contains($cleanRemarks, '<li>')) {
                $cleanRemarks = preg_replace('/<li>/i', '<li>' . e($diffText), $cleanRemarks, 1);
            } else {
                $cleanRemarks = $diffText . ($cleanRemarks ?: 'Approved with revised amount.');
            }
        } else {
            $cleanRemarks = $cleanRemarks ?: 'Fund shifting request approved as proposed.';
        }

        $fromAllocBefore = (float) $shift->fsh_from_alloc_before;
        $toAllocBefore = (float) $shift->fsh_to_alloc_before;
        $fromAllocAfter = round($fromAllocBefore - $finalAmount, 2);
        $toAllocAfter = round($toAllocBefore + $finalAmount, 2);

        // Find an IT user to set as current holder
        $itUser = CenAccount::whereRaw("LOWER(acc_untarea) = 'it'")->whereRaw("LOWER(acc_status) = 'active'")->first();
        $itHolder = $itUser ? ($itUser->acc_name . ' (IT)') : 'IT Directorate';

        DB::transaction(function () use ($shift, $user, $cleanRemarks, $finalAmount, $fromAllocAfter, $toAllocAfter, $itUser, $itHolder) {
            $shift->update([
                'fsh_status' => 'Approved',
                'fsh_current_stage' => 'IT',
                'fsh_current_acc_id' => $itUser?->acc_id,
                'fsh_current_holder' => $itHolder,
                'fsh_approved_amount' => $finalAmount,
                'fsh_from_alloc_after' => $fromAllocAfter,
                'fsh_to_alloc_after' => $toAllocAfter,
            ]);

            if ($shift->fsh_rev_id) {
                AudRev::where('rev_id', $shift->fsh_rev_id)->update([
                    'rev_status' => 'In Process',
                ]);

                AudRevData::where('rvd_rev_id', $shift->fsh_rev_id)
                    ->where('rvd_rowid', (string) $shift->fsh_from_sbh_id)
                    ->update(['rvd_newvalue' => (string) $fromAllocAfter]);

                AudRevData::where('rvd_rev_id', $shift->fsh_rev_id)
                    ->where('rvd_rowid', (string) $shift->fsh_to_sbh_id)
                    ->update(['rvd_newvalue' => (string) $toAllocAfter]);
            }

            FundShiftDecision::create([
                'fsd_fsh_id' => $shift->fsh_id,
                'fsd_acc_id' => $user->acc_id,
                'fsd_user_name' => $user->acc_username,
                'fsd_user_rank' => $user->acc_rank ?? '',
                'fsd_user_desig' => $user->acc_desig ?? '',
                'fsd_role' => $user->acc_desig ?? 'Finance / Approver',
                'fsd_action' => 'Approved and routed to IT',
                'fsd_from_stage' => $shift->fsh_current_stage,
                'fsd_to_stage' => 'IT',
                'fsd_remarks' => $cleanRemarks,
                'fsd_dtg' => now(),
            ]);
        });

        return $shift->fresh();
    }

    /**
     * Return fund shifting case back to a prior stage.
     */
    public function return(FundShift $shift, CenAccount|User $user, string $remarks, ?string $targetStage = null): FundShift
    {
        $currentStage = $shift->fsh_current_stage;
        $nextStage = $targetStage;

        if (!$nextStage) {
            if (in_array($currentStage, ['MD', 'DDG', 'DG'], true)) {
                $nextStage = 'Finance';
            } else {
                $nextStage = 'Division';
            }
        }

        DB::transaction(function () use ($shift, $user, $remarks, $currentStage, $nextStage) {
            $shift->update([
                'fsh_current_stage' => $nextStage,
                'fsh_status' => 'Returned',
            ]);

            FundShiftDecision::create([
                'fsd_fsh_id' => $shift->fsh_id,
                'fsd_acc_id' => $user->acc_id,
                'fsd_user_name' => $user->acc_username,
                'fsd_user_rank' => $user->acc_rank ?? '',
                'fsd_user_desig' => $user->acc_desig ?? '',
                'fsd_role' => $user->acc_desig ?? 'Reviewer',
                'fsd_action' => "Returned to {$nextStage}",
                'fsd_from_stage' => $currentStage,
                'fsd_to_stage' => $nextStage,
                'fsd_remarks' => $remarks,
                'fsd_dtg' => now(),
            ]);
        });

        return $shift->fresh();
    }

    /**
     * Cancel fund shifting case.
     */
    public function cancel(FundShift $shift, CenAccount|User $user, ?string $remarks = null): FundShift
    {
        $currentStage = $shift->fsh_current_stage;
        $cleanRemarks = trim((string) $remarks) ?: 'Fund shifting request cancelled.';

        DB::transaction(function () use ($shift, $user, $currentStage, $cleanRemarks) {
            $shift->update([
                'fsh_status' => 'Cancelled',
                'fsh_current_stage' => 'Cancelled',
            ]);

            if ($shift->fsh_rev_id) {
                AudRev::where('rev_id', $shift->fsh_rev_id)->update([
                    'rev_status' => 'Cancelled',
                    'rev_closedtg' => now(),
                ]);
            }

            FundShiftDecision::create([
                'fsd_fsh_id' => $shift->fsh_id,
                'fsd_acc_id' => $user->acc_id,
                'fsd_user_name' => $user->acc_username,
                'fsd_user_rank' => $user->acc_rank ?? '',
                'fsd_user_desig' => $user->acc_desig ?? '',
                'fsd_role' => $user->acc_desig ?? 'User',
                'fsd_action' => 'Cancelled',
                'fsd_from_stage' => $currentStage,
                'fsd_to_stage' => 'Cancelled',
                'fsd_remarks' => $cleanRemarks,
                'fsd_dtg' => now(),
            ]);
        });

        return $shift->fresh();
    }

    /**
     * Implement fund shifting by IT (or authorized admin).
     * Updates subhead allocations, records aud.revdata diffs, fulfills aud.revs.
     */
    public function implement(FundShift $shift, CenAccount|User $user, ?float $finalAmount = null, ?string $userRemarks = null): FundShift
    {
        $amount = (float) ($finalAmount ?: ($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount));
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Implemented shifting amount must be greater than zero.');
        }

        $cleanRemarks = trim((string) $userRemarks) ?: 'Fund shifting implemented into live subhead databases.';
        $formattedRemarks = "Fund shifting implemented into official subheads (PKR " . number_format($amount, 2) . "). Remarks: " . $cleanRemarks;

        return DB::transaction(function () use ($shift, $user, $amount, $formattedRemarks) {
            // Lock subheads for update
            $fromSbh = FinSubhead::where('sbh_id', $shift->fsh_from_sbh_id)->lockForUpdate()->firstOrFail();
            $toSbh = FinSubhead::where('sbh_id', $shift->fsh_to_sbh_id)->lockForUpdate()->firstOrFail();

            $fromAllocBefore = (float) $fromSbh->sbh_alloc;
            $toAllocBefore = (float) $toSbh->sbh_alloc;

            $fromAllocAfter = round($fromAllocBefore - $amount, 2);
            $toAllocAfter = round($toAllocBefore + $amount, 2);

            // Execute budget update
            $fromSbh->update(['sbh_alloc' => $fromAllocAfter]);
            $toSbh->update(['sbh_alloc' => $toAllocAfter]);

            // Record / update field diffs in aud.revdata for both subheads
            if ($shift->fsh_rev_id) {
                // From Subhead revision record
                $fromData = AudRevData::where('rvd_rev_id', $shift->fsh_rev_id)
                    ->where('rvd_rowid', (string) $fromSbh->sbh_id)
                    ->first();
                if ($fromData) {
                    $fromData->update([
                        'rvd_oldvalue' => (string) $fromAllocBefore,
                        'rvd_newvalue' => (string) $fromAllocAfter,
                    ]);
                } else {
                    AudRevData::create([
                        'rvd_rev_id' => $shift->fsh_rev_id,
                        'rvd_table' => 'fin_subheads',
                        'rvd_rowid' => (string) $fromSbh->sbh_id,
                        'rvd_attrib' => 'sbh_alloc',
                        'rvd_oldvalue' => (string) $fromAllocBefore,
                        'rvd_newvalue' => (string) $fromAllocAfter,
                        'rvd_datatype' => 'numeric',
                        'rvd_type' => 2,
                        'rvd_colname' => 'sbh_id',
                        'rvd_alias' => "Subhead - {$fromSbh->sbh_name}",
                    ]);
                }

                // To Subhead revision record
                $toData = AudRevData::where('rvd_rev_id', $shift->fsh_rev_id)
                    ->where('rvd_rowid', (string) $toSbh->sbh_id)
                    ->first();
                if ($toData) {
                    $toData->update([
                        'rvd_oldvalue' => (string) $toAllocBefore,
                        'rvd_newvalue' => (string) $toAllocAfter,
                    ]);
                } else {
                    AudRevData::create([
                        'rvd_rev_id' => $shift->fsh_rev_id,
                        'rvd_table' => 'fin_subheads',
                        'rvd_rowid' => (string) $toSbh->sbh_id,
                        'rvd_attrib' => 'sbh_alloc',
                        'rvd_oldvalue' => (string) $toAllocBefore,
                        'rvd_newvalue' => (string) $toAllocAfter,
                        'rvd_datatype' => 'numeric',
                        'rvd_type' => 2,
                        'rvd_colname' => 'sbh_id',
                        'rvd_alias' => "Subhead - {$toSbh->sbh_name}",
                    ]);
                }

                // Update aud.revs to Fulfilled
                AudRev::where('rev_id', $shift->fsh_rev_id)->update([
                    'rev_status' => 'Fulfilled',
                    'rev_closedtg' => now(),
                    'rev_reason' => $formattedRemarks,
                ]);
            }

            // Update FundShift record
            $shift->update([
                'fsh_approved_amount' => $amount,
                'fsh_from_alloc_before' => $fromAllocBefore,
                'fsh_from_alloc_after' => $fromAllocAfter,
                'fsh_to_alloc_before' => $toAllocBefore,
                'fsh_to_alloc_after' => $toAllocAfter,
                'fsh_status' => 'Implemented',
                'fsh_current_stage' => 'Completed',
                'fsh_current_holder' => 'Completed (Fulfilled)',
                'fsh_implemented_by' => $user->acc_id,
                'fsh_implemented_at' => now(),
                'fsh_implementation_notes' => $formattedRemarks,
            ]);

            // Log decision
            FundShiftDecision::create([
                'fsd_fsh_id' => $shift->fsh_id,
                'fsd_acc_id' => $user->acc_id,
                'fsd_user_name' => $user->acc_username,
                'fsd_user_rank' => $user->acc_rank ?? '',
                'fsd_user_desig' => $user->acc_desig ?? '',
                'fsd_role' => $user->acc_desig ?? 'IT Directorate',
                'fsd_action' => 'Implemented by IT',
                'fsd_from_stage' => $shift->fsh_current_stage,
                'fsd_to_stage' => 'Completed',
                'fsd_remarks' => $formattedRemarks,
                'fsd_dtg' => now(),
            ]);

            return $shift->fresh();
        });
    }

    /**
     * Get past fund shifts / revisions for a project.
     */
    public function getPastFundShifts(int $projectId): Collection
    {
        return FundShift::where('fsh_prj_id', $projectId)
            ->with(['decisions', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
