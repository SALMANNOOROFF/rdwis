<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurDecision;
use App\Models\PurNotification;
use App\Models\CenAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseApprovalService
{
    const THRESHOLD_MD  = 399999;   // 4 Lakh
    const THRESHOLD_DDG = 999999;   // 10 Lakh

    /**
     * Define the strict serial order of areas
     */
    protected $serialFlow = [
        'prj'   => 'Proc',  // Division -> DProc
        'Proc'  => 'fin',   // DProc -> DFinance
        'fin'   => 'rdw',   // DFinance -> MD
        'rdw'   => 'Hqs',   // MD -> DDG (if amt >= 4L)
        'Hqs'   => 'nrdi',  // DDG -> DG (if amt >= 10L)
    ];

    protected $displayNames = [
        'Under Scrutiny' => 'Director Procurement',
        'With DFinance'  => 'Director Finance',
        'With MD'        => 'MD Office',
        'With DDG'       => 'DDG Office',
        'With DG'        => 'Director General',
        'Returned'       => 'Division (Initiator)'
    ];

    protected $returnChain = [
        'Under Scrutiny' => 'Returned',
        'With DFinance'  => 'Under Scrutiny',
        'With MD'        => 'With DFinance',
        'With DDG'       => 'With MD',
        'With DG'        => 'With DDG'
    ];

    protected $statusToArea = [
        'Under Scrutiny' => 'Proc',
        'With DFinance'  => 'fin',
        'With MD'        => 'rdw',
        'With DDG'       => 'Hqs',
        'With DG'        => 'nrdi',
    ];

    /**
     * Get the name of the next authority based on current area and case value
     */
    public function getNextAuthorityName(Purchase $case, string $currentArea): ?string
    {
        $mapping = $this->resolveForwarding($case, $currentArea);
        $nextArea = strtolower(trim($mapping['area'] ?? ''));
        
        if ($mapping['status'] === 'Approved') return 'Final Approval (Success)';

        return match($nextArea) {
            'proc' => 'Director Procurement',
            'fin'  => 'Director Finance',
            'rdw'  => 'MD Office',
            'hqs'  => 'DDG Office',
            'nrdi' => 'Director General',
            default => null
        };
    }

    /**
     * Get the display name of a status
     */
    public function getStatusDisplayName(string $status): string
    {
        return $this->displayNames[$status] ?? $status;
    }

    /**
     * Get all possible return targets based on the case's journey
     */
    public function getReturnTargets(Purchase $case): array
    {
        $trailStatuses = $case->decisions()
            ->orderBy('pdec_id', 'asc')
            ->pluck('pdec_from_status')
            ->unique()
            ->toArray();

        $targets = [];
        
        // Always allow returning to Division
        $targets['Returned'] = $this->displayNames['Returned'];

        foreach ($trailStatuses as $status) {
            if ($status !== $case->pcs_status && isset($this->displayNames[$status])) {
                $targets[$status] = $this->displayNames[$status];
            }
        }

        return $targets;
    }

    /**
     * Process a decision and move the case to the next stage
     */
    public function processDecision(Purchase $case, string $action, string $remarks, ?string $targetStatus = null)
    {
        return DB::transaction(function () use ($case, $action, $remarks, $targetStatus) {
            $user = Auth::user();
            $userArea = strtolower(trim($user->acc_untarea));
            
            $fromStatus = $case->pcs_status;
            $toStatus = $fromStatus;
            $nextArea = null;

            if ($action === 'return') {
                $toStatus = $targetStatus; // Use the explicitly provided targetStatus
                // Determine the recipient for the return notification
                $this->notifyReturn($case, $user, $remarks, $toStatus);
            } elseif ($action === 'not_approved' || $action === 'reject') { 
                $toStatus = $this->returnChain[$fromStatus] ?? 'Returned';
                $this->notifyReturn($case, $user, $remarks, $toStatus);
            } elseif ($action === 'approve') {
                // Verification: Can this user approve this amount?
                if ($this->canApprove($userArea, $case->pcs_price)) {
                    $toStatus = 'Approved';
                } else {
                    throw new \Exception("Unauthorized: This role cannot approve this amount.");
                }
            } elseif ($action === 'forward' || $action === 'forward_negative') {
                $statusMapping = $this->resolveForwarding($case, $userArea);
                $toStatus = $statusMapping['status'];
                $nextArea = $statusMapping['area'];
                $this->notifyNext($case, $user, $nextArea, $remarks);
            }

            // 1. Log Decision
            PurDecision::create([
                'pdec_pcs_id' => $case->pcs_id,
                'pdec_acc_id' => $user->acc_id,
                'pdec_role' => $user->acc_desigshort ?: $userArea,
                'pdec_action' => $action,
                'pdec_from_status' => $fromStatus,
                'pdec_to_status' => $toStatus,
                'pdec_remarks' => $remarks,
                'pdec_amount' => $case->pcs_price,
            ]);

            // 2. Update Case
            $case->pcs_status = $toStatus;
            $case->save();

            // 3. Notify Interested Parties (Feedback Loop)
            $this->notifyInterestedParties($case, $action, $user, $remarks);

            return $case;
        });
    }

    /**
     * Notify everyone who has a stake in this case about the new action
    */
    protected function notifyInterestedParties($case, string $action, $actor, string $remarks)
    {
        // 1. Get the message based on action
        $actionPast = match($action) {
            'forward' => 'Recommended & Forwarded',
            'forward_negative' => 'Not Recommended but Forwarded',
            'return'  => 'Returned',
            'approve' => 'Approved',
            'not_approved' => 'Not Recommended',
            'reject'  => 'Rejected & Closed',
            'hold'    => 'Reverted',
            default   => $action
        };

        $message = "Case #{$case->pcs_id} has been {$actionPast} by {$actor->acc_name}. Status: {$case->pcs_status}.";
        if ($action == 'return' || $action == 'not_approved' || $action == 'reject' || $action == 'hold') $message .= " Reason: {$remarks}";

        // 2. Identify Recipients
        // a. All previous actors from the decision trail
        $previousActorIds = DB::table('pur.purdecisions')
            ->where('pdec_pcs_id', $case->pcs_id)
            ->distinct()
            ->pluck('pdec_acc_id')
            ->toArray();

        // b. All 'prj' (Division) users in the initiating unit
        $initiatorIds = CenAccount::where('acc_unt_id', $case->pcs_unt_id)
            ->where('acc_untarea', 'prj')
            ->pluck('acc_id')
            ->toArray();

        // Combine and filter out the current actor
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

    /**
     * Resolve where the case goes next when forwarded
     */
    protected function resolveForwarding(Purchase $case, string $currentArea)
    {
        $currentArea = strtolower(trim($currentArea));
        
        // Initiator Level (Division / Projects / Initiation)
        if (in_array($currentArea, ['prj', 'rdwprj', 'division', 'initiation'])) {
            return ['status' => 'Under Scrutiny', 'area' => 'proc'];
        }

        // Forward from Procurement to Finance
        if (str_contains($currentArea, 'proc')) {
            return ['status' => 'With DFinance', 'area' => 'fin'];
        }

        // Forward from Finance to MD
        if (str_contains($currentArea, 'fin')) {
            return ['status' => 'With MD', 'area' => 'rdw'];
        }
        
        // Forward from MD to DDG (if >= 4L) or Approve
        if ($currentArea === 'rdw') {
            if ($case->pcs_price <= self::THRESHOLD_MD) {
                 return ['status' => 'Approved', 'area' => null];
            }
            return ['status' => 'With DDG', 'area' => 'hqs'];
        }

        // Forward from DDG to DG (if >= 10L) or Approve
        if ($currentArea === 'hqs') {
            if ($case->pcs_price <= self::THRESHOLD_DDG) {
                 return ['status' => 'Approved', 'area' => null];
            }
            return ['status' => 'With DG', 'area' => 'nrdi'];
        }

        return ['status' => $case->pcs_status, 'area' => null];
    }

    /**
     * Notifications
     */
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

    protected function notifyReturn(Purchase $case, $actor, string $remarks, string $toStatus)
    {
        $recipientIds = [];

        if ($toStatus === 'Returned') { // Back to Division
            $initiator = CenAccount::where('acc_unt_id', $case->pcs_unt_id)
                                   ->where('acc_untarea', 'prj')
                                   ->first();
            if ($initiator) {
                $recipientIds[] = $initiator->acc_id;
            }
        } else { // Back to an HQ authority
            $targetArea = $this->statusToArea[$toStatus] ?? null;
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
        // Assuming the first person in the unit who created the case OR any active prj user in that unit
        $initiator = CenAccount::where('acc_unt_id', $case->pcs_unt_id)
                      ->where('acc_untarea', 'prj')
                      ->first();
        return $initiator ? $initiator->acc_id : 1; // Fallback
    }
}
