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
    /**
     * Get default workflow configuration matrix
     */
    public function getDefaultWorkflowMatrix(): array
    {
        return [
            'forward_chain' => [
                'Division' => ['next' => 'DProc',    'area' => 'proc'],
                'DProc'    => ['next' => 'Division', 'area' => 'prj'],
                'DFinance' => ['next' => 'MD',       'area' => 'rdw'],
                'MD'       => ['next' => 'DDG',      'area' => 'hqs'],
                'DDG'      => ['next' => 'DG',       'area' => 'nrdi'],
                'DG'       => ['next' => 'Approved', 'area' => null],
            ],
            'return_chain' => [
                'DFinance' => 'Division',
                'MD'       => 'DFinance',
                'DDG'      => 'MD',
                'DG'       => 'DDG',
            ],
            'enabled_stages' => [
                'Division' => true,
                'DProc'    => true,
                'DFinance' => true,
                'MD'       => true,
                'DDG'      => true,
                'DG'       => true,
            ],
            'return_policy' => 'historical', // 'historical' = flexible trail, 'previous' = strict predecessor
        ];
    }

    /**
     * Get dynamic workflow configuration matrix for a specific case type or default
     */
    public function getWorkflowMatrix(?string $caseType = null): array
    {
        $raw = \App\Models\SystemSetting::get('pur_workflow_matrix', null);
        $defaults = $this->getDefaultWorkflowMatrix();

        if (empty($raw)) {
            return $defaults;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($decoded) || empty($decoded)) {
            return $defaults;
        }

        $rawType = strtolower(trim($caseType ?? 'ps'));
        $typeKey = strtoupper($rawType);

        // 1. Direct match by workflow key (PS, PT, RB, DEFAULT)
        if (isset($decoded[$typeKey]) && is_array($decoded[$typeKey])) {
            return array_replace_recursive($defaults, $decoded[$typeKey]);
        }

        // 2. Match by assigned case categories
        foreach (['PS', 'PT', 'RB'] as $flowKey) {
            if (isset($decoded[$flowKey]['assigned_types']) && is_array($decoded[$flowKey]['assigned_types'])) {
                $assigned = array_map('strtolower', $decoded[$flowKey]['assigned_types']);
                if (in_array($rawType, $assigned)) {
                    return array_replace_recursive($defaults, $decoded[$flowKey]);
                }
            }
        }

        // 3. Fallback to PS, then DEFAULT
        if (isset($decoded['PS']) && is_array($decoded['PS'])) {
            return array_replace_recursive($defaults, $decoded['PS']);
        }

        if (isset($decoded['DEFAULT']) && is_array($decoded['DEFAULT'])) {
            return array_replace_recursive($defaults, $decoded['DEFAULT']);
        }

        return $defaults;
    }

    /**
     * Get assigned case types for a specific workflow (PS, PT, RB)
     */
    public function getAssignedCaseTypes(string $workflowKey = 'PS'): array
    {
        $matrix = $this->getWorkflowMatrix($workflowKey);
        $assigned = $matrix['assigned_types'] ?? [];
        if (empty($assigned)) {
            $assigned = ($workflowKey === 'PS') 
                ? ['mat', 'lic', 'stat', 'book', 'cons', 'serv'] 
                : (($workflowKey === 'PT') ? ['stat', 'tran', 'tada', 'mat'] : ['civ', 'serv', 'net', 'lic']);
        }
        $assigned[] = strtolower($workflowKey);
        return array_values(array_unique(array_map('strtolower', (array)$assigned)));
    }

    /**
     * Check if a case type is routed to DProc collaborative loop (PS)
     */
    public function isProcurementCase(?string $pcsType): bool
    {
        $rawType = strtolower(trim((string)($pcsType ?? 'ps')));
        $psTypes = $this->getAssignedCaseTypes('PS');
        return in_array($rawType, $psTypes);
    }

    /**
     * Display names for stages (used in UI)
     */
    protected $displayNames = [
        'Division'  => 'Division (Initiator)',
        'DProc'     => 'Director Procurement',
        'DFinance'  => 'Director Finance',
        'MD'        => 'MD Office',
        'DDG'       => 'DDG Office',
        'DG'        => 'Director General',
        'IS'        => 'Information Systems',
        'IT'        => 'Information Technology',
        'Admin'     => 'Administration Department',
        'Approved'  => 'Final Approval (Success)',
    ];

    /**
     * Map stage → area code (for notifications)
     */
    protected $stageToArea = [
        'Division'  => 'prj',
        'DProc'     => 'proc',
        'DFinance'  => 'fin',
        'MD'        => 'rdw',
        'DDG'       => 'hqs',
        'DG'        => 'nrdi',
        'IS'        => 'is',
        'IT'        => 'it',
        'Admin'     => 'adm',
        'Approved'  => null,
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
        'prc'       => 'DProc',
        'fin'       => 'DFinance',
        'rdw'       => 'MD',
        'hqs'       => 'DDG',
        'nrdi'      => 'DG',
        'is'        => 'IS',
        'it'        => 'IT',
        'adm'       => 'Admin',
        'admin'     => 'Admin',
    ];

    /**
     * Get the name of the next authority based on current area and case value
     */
    public function getNextAuthorityName(Purchase $case, string $currentArea): ?string
    {
        $mapping = $this->resolveForwarding($case, $currentArea);

        if ($mapping['stage'] === 'Approved') return 'Final Approval (Success)';

        return $this->displayNames[$mapping['stage']] ?? $mapping['stage'];
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
     * Get available destination targets for sending a purchase case
     */
    public function getAvailableDestinations($currentUserOrRole = null): array
    {
        $currentUser = null;
        if ($currentUserOrRole instanceof \App\Models\CenAccount) {
            $currentUser = $currentUserOrRole;
        } elseif (\Illuminate\Support\Facades\Auth::check()) {
            $currentUser = \Illuminate\Support\Facades\Auth::user();
        }

        $currArea = strtolower(trim((string) ($currentUser?->acc_untarea ?? (is_string($currentUserOrRole) ? $currentUserOrRole : ''))));
        if (in_array($currArea, ['proc', 'prc'], true)) $currArea = 'proc';
        $currContext = $currentUser ? \App\Services\Auth\UserAccessContext::forUser($currentUser) : null;
        $isCommand = $currContext?->isCommand() ?? in_array($currArea, ['rdw', 'hqs', 'nrdi'], true);

        $accounts = \App\Models\CenAccount::whereRaw("LOWER(acc_status) = 'active'")
            ->when($currentUser, fn($q) => $q->where('acc_id', '!=', $currentUser->acc_id))
            ->orderBy('acc_level', 'asc')
            ->orderBy('acc_id', 'asc')
            ->get();

        $list = [];

        foreach ($accounts as $acc) {
            $area = strtolower(trim((string) ($acc->acc_untarea ?? '')));
            if (in_array($area, ['proc', 'prc'], true)) $area = 'proc';
            $accContext = \App\Services\Auth\UserAccessContext::forUser($acc);

            // Purchase cases rule: HR accounts excluded
            if ($area === 'hr') {
                continue;
            }

            // Hierarchy filter:
            // Division & departments only see up to MD (cannot see DDG or DG)
            $isTargetDg = $accContext->isDg() || $area === 'nrdi';
            $isTargetDdg = $accContext->isDdg() || $area === 'hqs';
            $isTargetMd = $accContext->isMd() || $area === 'rdw';

            if (! $isCommand) {
                if ($isTargetDg || $isTargetDdg) {
                    continue;
                }
            }

            // Determine canonical workflow stage & department display name
            $stage = 'Division';
            $deptName = $acc->acc_desig;
            if ($isTargetDg) {
                $stage = 'DG';
                $deptName = 'Director General (DG Office)';
            } elseif ($isTargetDdg) {
                $stage = 'DDG';
                $deptName = 'Deputy Director General (DDG Office)';
            } elseif ($isTargetMd) {
                $stage = 'MD';
                $deptName = 'Managing Director (MD Office)';
            } elseif ($area === 'proc') {
                $stage = 'DProc';
                $deptName = 'Procurement Department (DProc)';
            } elseif ($area === 'fin') {
                $stage = 'DFinance';
                $deptName = 'Finance Department';
            } elseif ($area === 'is') {
                $stage = 'IS';
                $deptName = 'Information System Department (IS)';
            } elseif ($area === 'it') {
                $stage = 'IT';
                $deptName = 'Information Technology Department (IT)';
            } elseif ($area === 'adm') {
                $stage = 'Admin';
                $deptName = 'Administration Department (Admin)';
            } elseif ($area === 'mtss') {
                $stage = 'MTSS';
                $deptName = 'MTSS Department';
            } elseif ($area === 'rdwprj') {
                $stage = 'Division';
                $deptName = 'Staff Officer R&D (SORD)';
            } elseif ($area === 'prj') {
                $stage = 'Division';
                $unitName = \App\Models\Unit::where('unt_id', $acc->acc_unt_id)->value('unt_name');
                $deptName = $unitName ?: $acc->acc_desig;
            }

            $code = 'acc_' . $acc->acc_id;

            $list[$code] = [
                'code'     => $code,
                'stage'    => $stage,
                'acc_id'   => $acc->acc_id,
                'name'     => $deptName . ' — ' . $acc->acc_name,
                'director' => $acc->acc_name,
                'desig'    => $acc->acc_desig,
                'badge'    => $acc->acc_desigshort ?: strtoupper($area),
                'area'     => $area,
            ];
        }

        return $list;
    }

    /**
     * Get all possible return targets based on the case's substatus history.
     */
    public function getReturnTargets(Purchase $case): array
    {
        $matrix = $this->getWorkflowMatrix($case->pcs_type);
        $returnPolicy = $matrix['return_policy'] ?? 'historical';
        $returnChain = $matrix['return_chain'] ?? $this->getDefaultWorkflowMatrix()['return_chain'];
        $currentStage = $case->currentSubstatus?->pss_stage;

        if ($returnPolicy === 'previous') {
            $prev = $returnChain[$currentStage] ?? 'Division';
            return [$prev => $this->displayNames[$prev] ?? $prev];
        }

        // Default 'historical' mode: Get all historical stages this case has passed through
        $historicalStages = PurCaseSubstatus::where('pss_pcs_id', $case->pcs_id)
            ->orderBy('pss_id', 'asc')
            ->pluck('pss_stage')
            ->unique()
            ->toArray();

        $targets = [];

        // Always allow returning to Division
        $targets['Division'] = $this->displayNames['Division'];

        foreach ($historicalStages as $stage) {
            // Exclude current stage, and DProc
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
    public function processDecision(Purchase $case, string $action, ?string $remarks = '', ?string $targetStage = null)
    {
        return DB::transaction(function () use ($case, $action, $remarks, $targetStage) {
            $user = Auth::user();
            $userArea = strtolower(trim($user?->acc_untarea ?? ''));
            $currentStage = $case->currentSubstatus?->pss_stage ?? 'Division';
            $remarks = !empty($remarks) ? trim($remarks) : ($action === 'approve' ? 'Approved.' : ($action === 'return' ? 'Returned for clarification.' : 'Case processed and forwarded.'));

            $fromStage = $currentStage;
            $toStage = $currentStage;
            $newPcsStatus = null; // Only set when pcs_status should change

            $targetAcc = null;
            $targetStageName = null;
            if (!empty($targetStage) && str_starts_with($targetStage, 'acc_')) {
                $targetAccId = (int) substr($targetStage, 4);
                $targetAcc = CenAccount::find($targetAccId);
                if ($targetAcc) {
                    $uArea = strtolower(trim($targetAcc->acc_untarea ?? ''));
                    $uRole = (int) ($targetAcc->acc_role ?? 0);
                    $desig = strtolower(trim($targetAcc->acc_desigshort ?? ''));

                    if ($uRole === 100000 || (str_contains($desig, 'dg') && !str_contains($desig, 'ddg'))) {
                        $resolvedStage = 'DG';
                    } elseif (str_contains($desig, 'ddg')) {
                        $resolvedStage = 'DDG';
                    } elseif ($uArea === 'rdw' || $uRole === 160000 || str_contains($desig, 'md')) {
                        $resolvedStage = 'MD';
                    } elseif (in_array($uArea, ['proc', 'prc'])) {
                        $resolvedStage = 'DProc';
                    } elseif (in_array($uArea, ['fin', 'finance'])) {
                        $resolvedStage = 'DFinance';
                    } elseif ($uArea === 'is') {
                        $resolvedStage = 'IS';
                    } elseif ($uArea === 'it') {
                        $resolvedStage = 'IT';
                    } elseif (in_array($uArea, ['admin', 'adm'])) {
                        $resolvedStage = 'Admin';
                    } else {
                        $resolvedStage = 'Division';
                    }

                    $targetStageName = trim($targetAcc->acc_name) . ' (' . ($targetAcc->acc_desigshort ?: $resolvedStage) . ')';
                    $targetStage = $resolvedStage;
                }
            }

            if ($action === 'return') {
                // Return to an explicitly provided target stage
                $toStage = $targetStage ?? ($this->returnChain[$currentStage] ?? 'Division');

                if ($toStage === 'Division') {
                    $newPcsStatus = 'Returned';
                }

                $this->transitionSubstatus($case, $toStage);
                if ($targetAcc) {
                    PurNotification::create([
                        'pnt_acc_id' => $targetAcc->acc_id,
                        'pnt_pcs_id' => $case->pcs_id,
                        'pnt_message' => "Purchase Case #{$case->pcs_id} has been returned to you by {$user->acc_name}.",
                    ]);
                } else {
                    $this->notifyReturn($case, $user, $remarks, $toStage);
                }

            } elseif ($action === 'not_approved' || $action === 'reject' || $action === 'cancel') {
                $newPcsStatus = 'Not Approved';
                $toStage = 'Not Approved';
                $this->closeSubstatus($case);
                $this->notifyReturn($case, $user, $remarks, 'Not Approved');

            } elseif ($action === 'approve') {
                // Terminal approval: verify authorization
                $newPcsStatus = 'Approved';
                $toStage = 'Approved';
                $this->closeSubstatus($case);

            } elseif ($action === 'forward' || $action === 'forward_negative') {
                if (!empty($targetStage)) {
                    $toStage = $targetStage;
                    if ($toStage === 'Approved') {
                        $newPcsStatus = 'Approved';
                        $this->closeSubstatus($case);
                    } elseif ($toStage === 'Division' || in_array($toStage, ['Enab', 'Comm', 'NWS', 'Sensors', 'Sys', 'SoSE'])) {
                        $toStage = 'Division';
                        $newPcsStatus = 'Returned';
                        $this->transitionSubstatus($case, 'Division');
                        if ($targetAcc) {
                            PurNotification::create([
                                'pnt_acc_id' => $targetAcc->acc_id,
                                'pnt_pcs_id' => $case->pcs_id,
                                'pnt_message' => "Purchase Case #{$case->pcs_id} forwarded to you by {$user->acc_name}.",
                            ]);
                        } else {
                            $this->notifyNext($case, $user, 'prj', $remarks);
                        }
                    } elseif ($toStage === 'DProc') {
                        $newPcsStatus = 'Under Approval';
                        $this->transitionSubstatus($case, 'DProc');
                        if ($targetAcc) {
                            PurNotification::create([
                                'pnt_acc_id' => $targetAcc->acc_id,
                                'pnt_pcs_id' => $case->pcs_id,
                                'pnt_message' => "New Purchase Case #{$case->pcs_id} forwarded to you by {$user->acc_name}.",
                            ]);
                        } else {
                            $this->notifyNext($case, $user, 'proc', $remarks);
                        }
                    } else {
                        $newPcsStatus = 'Under Approval';
                        $this->transitionSubstatus($case, $toStage);
                        if ($targetAcc) {
                            PurNotification::create([
                                'pnt_acc_id' => $targetAcc->acc_id,
                                'pnt_pcs_id' => $case->pcs_id,
                                'pnt_message' => "New Purchase Case #{$case->pcs_id} forwarded to you by {$user->acc_name}.",
                            ]);
                        } else {
                            $this->notifyNext($case, $user, $this->stageToArea[$toStage] ?? 'rdw', $remarks);
                        }
                    }
                } else {
                    $mapping = $this->resolveForwarding($case, $userArea);
                    $toStage = $mapping['stage'];

                    if ($toStage === 'Approved') {
                        $newPcsStatus = 'Approved';
                        $this->closeSubstatus($case);
                    } else {
                        $newPcsStatus = 'Under Approval';
                        $this->transitionSubstatus($case, $toStage);
                        $this->notifyNext($case, $user, $this->stageToArea[$toStage] ?? $mapping['area'], $remarks);
                    }
                }

            } elseif ($action === 'float_to_proc' || $action === 'reshare_to_proc') {
                // Division floats/reshares case to Procurement Dept for quotation collection/correction
                // Remove previous dproc_save so case becomes pending for DProc again
                PurDecision::where('pdec_pcs_id', $case->pcs_id)
                    ->where('pdec_action', 'dproc_save')
                    ->delete();

                $toStage = 'DProc';
                $newPcsStatus = 'Under Approval';
                $this->transitionSubstatus($case, 'DProc');
                $defaultNote = $action === 'reshare_to_proc' ? 'Case reshared to Procurement Department for quotation correction.' : 'Case floated to Procurement Department for quotation collection.';
                $this->notifyNext($case, $user, 'proc', $remarks ?: $defaultNote);

            } elseif ($action === 'dproc_save') {
                // DProc saves quotes & scrutiny remarks, handing action back to Division
                $toStage = 'Division';
                $newPcsStatus = 'Returned';
                $this->transitionSubstatus($case, 'Division');
                $this->notifyNext($case, $user, 'prj', $remarks ?: 'Quotes and scrutiny remarks saved by Director Procurement.');

            } elseif ($action === 'save_draft') {
                // Draft remarks save: NO substatus change, NO pcs_status change
                $toStage = $currentStage;
            }

            // Always delete any existing draft decision for this user on this case before logging
            PurDecision::where('pdec_pcs_id', $case->pcs_id)
                ->where('pdec_acc_id', $user->acc_id)
                ->where('pdec_action', 'save_draft')
                ->delete();

            // 1. Log Decision (always, for every action)
            PurDecision::create([
                'pdec_pcs_id'      => $case->pcs_id,
                'pdec_acc_id'      => $user->acc_id,
                'pdec_role'        => $user->acc_desigshort ?: $userArea,
                'pdec_action'      => $action,
                'pdec_from_status' => $fromStage,
                'pdec_to_status'   => $targetStageName ? substr($targetStageName, 0, 50) : $toStage,
                'pdec_remarks'     => $remarks,
                'pdec_amount'      => $case->pcs_price,
            ]);

            // 2. Update pcs_status (only when it actually changes and not draft)
            if ($newPcsStatus !== null && $action !== 'save_draft') {
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

            // 4. Notify Interested Parties (Feedback Loop) - only for real decisions
            if ($action !== 'save_draft') {
                $this->notifyInterestedParties($case, $action, $user, $remarks);
            }

            return $case;
        });
    }

    public function getMdThreshold(): float
    {
        return (float) \App\Models\SystemSetting::get('pur_md_threshold', 400000);
    }

    public function getDdgThreshold(): float
    {
        return (float) \App\Models\SystemSetting::get('pur_ddg_threshold', 1000000);
    }

    /**
     * Check if a role is authorized to approve the current case amount
     */
    public function canApprove(string $area, float $amount, ?Purchase $case = null): bool
    {
        $area = strtolower(trim($area));
        if ($area === 'nrdi') return true; // DG can always approve

        $evalPrice = $case ? $case->effective_evaluation_price : $amount;
        $mdLimit = $this->getMdThreshold();
        $ddgLimit = $this->getDdgThreshold();

        if ($area === 'hqs' && $evalPrice <= $ddgLimit) return true;
        if ($area === 'rdw' && $evalPrice <= $mdLimit) return true;

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
        $evalPrice = $case->effective_evaluation_price;
        $mdLimit = $this->getMdThreshold();
        $ddgLimit = $this->getDdgThreshold();

        $matrix = $this->getWorkflowMatrix($case->pcs_type);
        $forwardChain = $matrix['forward_chain'] ?? [];

        // 1. Division (prj, rdwprj, etc.)
        if (in_array($currentArea, ['prj', 'rdwprj', 'division', 'initiation'])) {
            $next = is_array($forwardChain['Division'] ?? null) ? ($forwardChain['Division']['next'] ?? 'DProc') : ($forwardChain['Division'] ?? 'DProc');
            if ($next === 'Approved') return ['stage' => 'Approved', 'area' => null];
            return ['stage' => $next, 'area' => $this->stageToArea[$next] ?? 'proc'];
        }

        // 2. DProc (collaborative forward / finalize)
        if (str_contains($currentArea, 'proc') || $currentArea === 'prc') {
            $next = is_array($forwardChain['DProc'] ?? null) ? ($forwardChain['DProc']['next'] ?? 'DFinance') : ($forwardChain['DProc'] ?? 'DFinance');
            if ($next === 'Approved') return ['stage' => 'Approved', 'area' => null];
            return ['stage' => $next, 'area' => $this->stageToArea[$next] ?? 'fin'];
        }

        // 3. DFinance
        if (str_contains($currentArea, 'fin')) {
            $next = is_array($forwardChain['DFinance'] ?? null) ? ($forwardChain['DFinance']['next'] ?? 'MD') : ($forwardChain['DFinance'] ?? 'MD');
            if ($next === 'Approved') return ['stage' => 'Approved', 'area' => null];
            return ['stage' => $next, 'area' => $this->stageToArea[$next] ?? 'rdw'];
        }

        // 4. MD
        if ($currentArea === 'rdw') {
            if ($evalPrice <= $mdLimit) {
                return ['stage' => 'Approved', 'area' => null];
            }
            $next = is_array($forwardChain['MD'] ?? null) ? ($forwardChain['MD']['next'] ?? 'DDG') : ($forwardChain['MD'] ?? 'DDG');
            if ($next === 'Approved') return ['stage' => 'Approved', 'area' => null];
            return ['stage' => $next, 'area' => $this->stageToArea[$next] ?? 'hqs'];
        }

        // 5. DDG
        if ($currentArea === 'hqs') {
            if ($evalPrice <= $ddgLimit) {
                return ['stage' => 'Approved', 'area' => null];
            }
            $next = is_array($forwardChain['DDG'] ?? null) ? ($forwardChain['DDG']['next'] ?? 'DG') : ($forwardChain['DDG'] ?? 'DG');
            if ($next === 'Approved') return ['stage' => 'Approved', 'area' => null];
            return ['stage' => $next, 'area' => $this->stageToArea[$next] ?? 'nrdi'];
        }

        // 6. DG (Terminal)
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
            'float_to_proc' => 'Floated to Procurement Department',
            'dproc_save' => 'Updated with Quotes & Remarks by Procurement',
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
