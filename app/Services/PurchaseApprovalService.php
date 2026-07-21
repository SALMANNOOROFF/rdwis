<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurDecision;
use App\Models\PurNotification;
use App\Models\PurCaseSubstatus;
use App\Models\CenAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseApprovalService
{
    const THRESHOLD_MD  = 399999;   // 4 Lakh
    const THRESHOLD_DDG = 999999;   // 10 Lakh

    /**
     * Forward chain: stage → next stage (area code)
     * Division skips DProc (DProc is collaborative, not a gate)
     */
    protected $forwardChain = [
        'Division'  => ['stage' => 'DFinance', 'area' => 'fin'],
        'DFinance'  => ['stage' => 'MD',       'area' => 'rdw'],
        'MD'        => ['stage' => 'DDG',      'area' => 'hqs'],   // Only if amount > threshold
        'DDG'       => ['stage' => 'DG',       'area' => 'nrdi'],  // Only if amount > threshold
    ];

    /**
     * Return chain: stage → previous stage
     * Returning from DFinance goes back to Division (sets pcs_status='Returned')
     */
    protected $returnChain = [
        'DFinance'  => 'Division',
        'MD'        => 'DFinance',
        'DDG'       => 'MD',
        'DG'        => 'DDG',
    ];

    /**
     * Display names for stages (used in UI)
     */
    protected $displayNames = [
        'Division'  => 'Division (Initiator)',
        'DFinance'  => 'Director Finance',
        'MD'        => 'MD Office',
        'DDG'       => 'DDG Office',
        'DG'        => 'Director General',
    ];

    /**
     * Map stage → area code (for notifications)
     */
    protected $stageToArea = [
        'Division'  => 'prj',
        'DFinance'  => 'fin',
        'MD'        => 'rdw',
        'DDG'       => 'hqs',
        'DG'        => 'nrdi',
    ];

    /**
     * Map area code → stage (for resolving current user's stage)
     */
    protected $areaToStage = [
        'prj'       => 'Division',
        'rdwprj'    => 'Division',
        'division'  => 'Division',
        'initiation'=> 'Division',
        'proc'      => 'DProc',     // Special: collaborative, not in forward chain
        'fin'       => 'DFinance',
        'rdw'       => 'MD',
        'hqs'       => 'DDG',
        'nrdi'      => 'DG',
    ];

    /**
     * Get the name of the next authority based on current area and case value
     */
    public function getNextAuthorityName(Purchase $case, string $currentArea): ?string
    {
        $mapping = $this->resolveForwarding($case, $currentArea);

        if ($mapping['stage'] === 'Approved') return 'Final Approval (Success)';

        return $this->displayNames[$mapping['stage']] ?? null;
    }

    /**
     * Get the display name of a stage or legacy pcs_status
     */
    public function getStatusDisplayName(string $statusOrStage): string
    {
        // First check if it's a stage name
        if (isset($this->displayNames[$statusOrStage])) {
            return $this->displayNames[$statusOrStage];
        }

        // Fallback for legacy pcs_status values shown in old views
        return match($statusOrStage) {
            'Under Scrutiny' => 'Director Procurement',
            'Under Approval' => 'HQ Processing',
            'Returned'       => 'Division (Initiator)',
            default          => $statusOrStage,
        };
    }

    /**
     * Get all possible return targets based on the case's substatus history.
     * Uses purcase_substatus (uniform stage names) — NOT purdecisions (fix #4).
     */
    public function getReturnTargets(Purchase $case): array
    {
        // Get all historical stages this case has passed through
        $historicalStages = PurCaseSubstatus::where('pss_pcs_id', $case->pcs_id)
            ->orderBy('pss_id', 'asc')
            ->pluck('pss_stage')
            ->unique()
            ->toArray();

        $currentStage = $case->currentSubstatus?->pss_stage;
        $targets = [];

        // Always allow returning to Division
        $targets['Division'] = $this->displayNames['Division'];

        foreach ($historicalStages as $stage) {
            // Exclude current stage, and DProc (never a return target)
            if ($stage !== $currentStage && $stage !== 'Division' && isset($this->displayNames[$stage])) {
                $targets[$stage] = $this->displayNames[$stage];
            }
        }

        return $targets;
    }

    /**
     * Process a decision and move the case to the next stage.
     *
     * Actions: forward, forward_negative, return, approve, reject/not_approved, dproc_save
     */
    public function processDecision(Purchase $case, string $action, string $remarks, ?string $targetStage = null)
    {
        return DB::transaction(function () use ($case, $action, $remarks, $targetStage) {
            $user = Auth::user();
            $userArea = strtolower(trim($user->acc_untarea));
            $currentStage = $case->currentSubstatus?->pss_stage ?? 'Division';

            $fromStage = $currentStage;
            $toStage = $currentStage;
            $newPcsStatus = null; // Only set when pcs_status should change

            if ($action === 'return') {
                // Return to an explicitly provided target stage
                $toStage = $targetStage ?? ($this->returnChain[$currentStage] ?? 'Division');

                if ($toStage === 'Division') {
                    $newPcsStatus = 'Returned';
                }

                $this->transitionSubstatus($case, $toStage);
                $this->notifyReturn($case, $user, $remarks, $toStage);

            } elseif ($action === 'not_approved' || $action === 'reject') {
                // One step back via returnChain (preserving existing behavior)
                $toStage = $this->returnChain[$currentStage] ?? 'Division';

                if ($toStage === 'Division') {
                    $newPcsStatus = 'Returned';
                }

                $this->transitionSubstatus($case, $toStage);
                $this->notifyReturn($case, $user, $remarks, $toStage);

            } elseif ($action === 'approve') {
                // Terminal approval: verify authorization
                if ($this->canApprove($userArea, $case->pcs_price)) {
                    $newPcsStatus = 'Approved';
                    $toStage = 'Approved';
                    $this->closeSubstatus($case); // fix #1: no new row for terminal
                } else {
                    throw new \Exception("Unauthorized: This role cannot approve this amount.");
                }

            } elseif ($action === 'forward' || $action === 'forward_negative') {
                $mapping = $this->resolveForwarding($case, $userArea);
                $toStage = $mapping['stage'];

                if ($toStage === 'Approved') {
                    // Forward resolves to approval (within threshold)
                    $newPcsStatus = 'Approved';
                    $this->closeSubstatus($case); // fix #1: no new row for terminal
                } else {
                    // Move to next stage
                    if ($case->pcs_status === 'Draft' || $case->pcs_status === 'Returned') {
                        $newPcsStatus = 'Under Approval'; // First release from Division
                    }
                    $this->transitionSubstatus($case, $toStage);
                    $this->notifyNext($case, $user, $this->stageToArea[$toStage] ?? $mapping['area'], $remarks);
                }

            } elseif ($action === 'dproc_save') {
                // DProc collaborative save: NO substatus change (fix #2), NO pcs_status change
                $toStage = $currentStage; // stays the same
            }

            // 1. Log Decision (always, for every action)
            PurDecision::create([
                'pdec_pcs_id'      => $case->pcs_id,
                'pdec_acc_id'      => $user->acc_id,
                'pdec_role'        => $user->acc_desigshort ?: $userArea,
                'pdec_action'      => $action,
                'pdec_from_status' => $fromStage,
                'pdec_to_status'   => $toStage,
                'pdec_remarks'     => $remarks,
                'pdec_amount'      => $case->pcs_price,
            ]);

            // 2. Update pcs_status (only when it actually changes)
            if ($newPcsStatus !== null) {
                $case->pcs_status = $newPcsStatus;
                $case->save();
            }

            // 3. Insert Fund Commitment on Approval (idempotency guarded)
            if ($newPcsStatus === 'Approved') {
                $exists = DB::table('fin.commitments')
                    ->where('cmt_docid', $case->pcs_id)
                    ->whereIn('cmt_type', ['Ps', 'Pt', 'Rb'])
                    ->exists();
                if (!$exists) {
                    DB::table('fin.commitments')->insert([
                        'cmt_docid'     => $case->pcs_id,
                        'cmt_type'      => $this->mapToLegacyType($case->pcs_type ?? 'mat'),
                        'cmt_date'      => now()->toDateString(),
                        'cmt_amount'    => -1 * ($case->pcs_transtype == 1 ? ($case->pcs_midprice ?? 0) : ($case->pcs_price ?? 0)),
                        'cmt_status'    => 'Awaited',
                        'cmt_effhed_id' => $case->pcs_effhed_id,
                        'cmt_effunt_id' => $case->pcs_effunt_id,
                        'cmt_hed_id'    => $case->pcs_hed_id,
                        'cmt_unt_id'    => $case->pcs_unt_id,
                        'cmt_sudohed'   => $case->pcs_sudohed,
                    ]);
                }
            }

            // 4. Notify Interested Parties (Feedback Loop)
            $this->notifyInterestedParties($case, $action, $user, $remarks);

            return $case;
        });
    }

    /**
     * Check if a role is authorized to approve the current case amount
     */
    public function canApprove(string $area, float $amount): bool
    {
        $area = strtolower($area);
        if ($area === 'nrdi') return true; // DG can always approve
        if ($area === 'hqs' && $amount <= self::THRESHOLD_DDG) return true; // DDG < 10L
        if ($area === 'rdw' && $amount <= self::THRESHOLD_MD) return true; // MD < 4L

        return false;
    }

    // ── Sub-Status Transition Helpers ─────────────────────────

    /**
     * Transition substatus: close the current row, open a new one.
     */
    protected function transitionSubstatus(Purchase $case, string $newStage): void
    {
        // Close current row
        PurCaseSubstatus::where('pss_pcs_id', $case->pcs_id)
            ->where('pss_is_current', true)
            ->update(['pss_is_current' => false, 'pss_until' => now()]);

        // Open new row
        PurCaseSubstatus::create([
            'pss_pcs_id'    => $case->pcs_id,
            'pss_stage'     => $newStage,
            'pss_is_current'=> true,
            'pss_since'     => now(),
        ]);
    }

    /**
     * Close substatus without opening a new row (terminal states: Approved, Rejected, etc.)
     * Fix #1: Terminal states have no current substatus row.
     */
    protected function closeSubstatus(Purchase $case): void
    {
        PurCaseSubstatus::where('pss_pcs_id', $case->pcs_id)
            ->where('pss_is_current', true)
            ->update(['pss_is_current' => false, 'pss_until' => now()]);
    }

    // ── Forwarding Resolution ─────────────────────────────────

    /**
     * Resolve where the case goes next when forwarded.
     * Returns ['stage' => '<stage name or Approved>', 'area' => '<area code>']
     */
    protected function resolveForwarding(Purchase $case, string $currentArea)
    {
        $currentArea = strtolower(trim($currentArea));

        // Division (prj, rdwprj, etc.) → DFinance
        if (in_array($currentArea, ['prj', 'rdwprj', 'division', 'initiation'])) {
            return ['stage' => 'DFinance', 'area' => 'fin'];
        }

        // DProc → DFinance (collaborative forward)
        if (str_contains($currentArea, 'proc')) {
            return ['stage' => 'DFinance', 'area' => 'fin'];
        }

        // DFinance → MD
        if (str_contains($currentArea, 'fin')) {
            return ['stage' => 'MD', 'area' => 'rdw'];
        }

        // MD → DDG (if >= 4L) or Approve
        if ($currentArea === 'rdw') {
            if ($case->pcs_price <= self::THRESHOLD_MD) {
                return ['stage' => 'Approved', 'area' => null];
            }
            return ['stage' => 'DDG', 'area' => 'hqs'];
        }

        // DDG → DG (if >= 10L) or Approve
        if ($currentArea === 'hqs') {
            if ($case->pcs_price <= self::THRESHOLD_DDG) {
                return ['stage' => 'Approved', 'area' => null];
            }
            return ['stage' => 'DG', 'area' => 'nrdi'];
        }

        // DG forward = Approve (DG is always the end)
        if ($currentArea === 'nrdi') {
            return ['stage' => 'Approved', 'area' => null];
        }

        return ['stage' => $case->currentSubstatus?->pss_stage ?? 'Division', 'area' => null];
    }

    // ── Notifications ─────────────────────────────────────────

    /**
     * Notify everyone who has a stake in this case about the new action
     */
    protected function notifyInterestedParties($case, string $action, $actor, string $remarks)
    {
        $actionPast = match($action) {
            'forward' => 'Recommended & Forwarded',
            'forward_negative' => 'Not Recommended but Forwarded',
            'return'  => 'Returned',
            'approve' => 'Approved',
            'not_approved' => 'Not Recommended',
            'reject'  => 'Rejected & Closed',
            'hold'    => 'Reverted',
            'dproc_save' => 'Updated with Quotations',
            default   => $action
        };

        $stageDisplay = $case->current_stage_display ?? $case->pcs_status;
        $message = "Case #{$case->pcs_id} has been {$actionPast} by {$actor->acc_name}. Status: {$case->pcs_status} — Currently with: {$stageDisplay}.";
        if ($action == 'return' || $action == 'not_approved' || $action == 'reject' || $action == 'hold') $message .= " Reason: {$remarks}";

        // Identify Recipients
        $previousActorIds = DB::table('pur.purdecisions')
            ->where('pdec_pcs_id', $case->pcs_id)
            ->distinct()
            ->pluck('pdec_acc_id')
            ->toArray();

        $initiatorIds = CenAccount::where('acc_unt_id', $case->pcs_unt_id)
            ->where('acc_untarea', 'prj')
            ->pluck('acc_id')
            ->toArray();

        $allRecipients = array_unique(array_merge($previousActorIds, $initiatorIds));

        foreach ($allRecipients as $recipientId) {
            if ($recipientId == $actor->acc_id) continue;

            PurNotification::create([
                'pnt_acc_id' => $recipientId,
                'pnt_pcs_id' => $case->pcs_id,
                'pnt_message' => $message,
            ]);
        }
    }

    protected function notifyNext(Purchase $case, $actor, string $area, string $remarks)
    {
        $recipients = CenAccount::where('acc_untarea', 'ILIKE', $area)->where('acc_status', 'Active')->get();
        foreach ($recipients as $recipient) {
            PurNotification::create([
                'pnt_acc_id' => $recipient->acc_id,
                'pnt_pcs_id' => $case->pcs_id,
                'pnt_message' => "New Purchase Case #{$case->pcs_id} forwarded to you by {$actor->acc_name}.",
            ]);
        }
    }

    protected function notifyReturn(Purchase $case, $actor, string $remarks, string $toStage)
    {
        $recipientIds = [];

        if ($toStage === 'Division') {
            // Back to Division initiator
            $initiator = CenAccount::where('acc_unt_id', $case->pcs_unt_id)
                                   ->where('acc_untarea', 'prj')
                                   ->first();
            if ($initiator) {
                $recipientIds[] = $initiator->acc_id;
            }
        } else {
            // Back to an HQ authority by stage
            $targetArea = $this->stageToArea[$toStage] ?? null;
            if ($targetArea) {
                $recipients = CenAccount::where('acc_untarea', 'ILIKE', $targetArea)
                                        ->where('acc_status', 'Active')
                                        ->get();
                foreach ($recipients as $recipient) {
                    $recipientIds[] = $recipient->acc_id;
                }
            }
        }

        $message = "Your Case #{$case->pcs_id} has been returned by {$actor->acc_name}. Reason: {$remarks}";

        foreach ($recipientIds as $recipientId) {
            PurNotification::create([
                'pnt_acc_id' => $recipientId,
                'pnt_pcs_id' => $case->pcs_id,
                'pnt_message' => $message,
            ]);
        }
    }

    protected function notifyInitiator(Purchase $case, $actor, string $message)
    {
        PurNotification::create([
            'pnt_acc_id' => $this->getInitiatorId($case),
            'pnt_pcs_id' => $case->pcs_id,
            'pnt_message' => $message,
        ]);
    }

    protected function getInitiatorId(Purchase $case)
    {
        $initiator = CenAccount::where('acc_unt_id', $case->pcs_unt_id)
                      ->where('acc_untarea', 'prj')
                      ->first();
        return $initiator ? $initiator->acc_id : 1; // Fallback
    }

    /**
     * Map RDWIS purchase type code to legacy cmt_type code
     */
    protected function mapToLegacyType(string $pcsType): string
    {
        return match(strtolower(trim($pcsType))) {
            'mat', 'civ', 'tran', 'book', 'lic', 'net', 'pub', 'stat' => 'Ps',
            'cons', 'serv' => 'Rb',
            'tada', 'trn'  => 'Pt',
            default => 'Ps',
        };
    }
}
