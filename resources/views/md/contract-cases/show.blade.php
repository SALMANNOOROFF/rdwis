@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

@php
    $role = $authorityRole ?? 'MD';
    $roleTitle = match($role) {
        'DDG' => 'Deputy Director General (DDG)',
        'DG'  => 'Director General (DG)',
        'Finance' => 'Director Finance',
        'HR' => 'Director HR',
        'Division' => 'Division Officer',
        default => 'Managing Director (MD)'
    };
    $routePrefix = match($role) {
        'DDG' => 'ddg',
        'DG'  => 'dg',
        'Finance' => 'finance',
        'HR' => 'hr',
        'Division' => 'division',
        default => 'md'
    };

    $currentStage = $case->current_stage ?? $case->currentSubstatus->css_stage ?? $role;
    $isCaseWithMe = ($currentStage === $role);
    $canActuallyApprove = ($canApprove ?? false) && ($role === 'DG' || ($authDetails['can_md_approve'] ?? false) || ($authDetails['can_ddg_approve'] ?? false));
    $nextForwardStage = match($role) {
        'Division' => 'HR',
        'MD' => 'DDG',
        'DDG' => 'DG',
        'Finance' => 'MD',
        'HR' => 'Finance',
        default => 'DG'
    };

    $caseTypeRaw = strtoupper(trim($case->ctc_type ?? 'CR'));
    $isHiring = in_array($caseTypeRaw, ['HG']);
    $isRenewal = in_array($caseTypeRaw, ['CR']);
    $isExtension = in_array($caseTypeRaw, ['CE']);
    $isRehiring = in_array($caseTypeRaw, ['RH']);
    $isRenewalOrExt = $isRenewal || $isExtension || $isRehiring;

    $proposedSalary = (float)($case->ctc_newsalary ?? 0);
    $previousSalary = (float)($case->previous_salary ?? 0);
    $salaryDiff = $proposedSalary - $previousSalary;
    $incrementPct = $previousSalary > 0 ? round(($salaryDiff / $previousSalary) * 100, 1) : 0;
    $annualImpact = $proposedSalary * 12;

    $prevGrade = $case->previous_grade ?: 'N/A';
    $prevJobtitle = $case->previous_jobtitle ?: 'N/A';
    $prevStart = $case->previous_startdt;
    $prevEnd = $case->previous_enddt;

    // Employment Type & Probation details
    $empType = $case->ctc_emp_type ?: ($case->ctc_newctrtype == 2 ? 'Part Time' : 'Full Time');
    $probationMonths = (!empty($case->ctc_newprob) && (int)$case->ctc_newprob > 0) ? (int)$case->ctc_newprob : null;
    $probationSalary = (!empty($case->ctc_newprobsal) && (float)$case->ctc_newprobsal > 0) ? (float)$case->ctc_newprobsal : null;

    $isHrIsAdmin = $case->is_hr_admin;

    $projectPlan = $case->casePlans->first();
    if ($isHrIsAdmin) {
        $projectCode = 'CSRF';
        $projectName = 'Center Special Research Fund (CSRF)';
    } else {
        $projectCode = $projectPlan?->project?->prj_code 
            ?? ($projectPlan?->ccp_hed_id ? \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $projectPlan->ccp_hed_id)->value('hed_code') : null)
            ?? 'Core';

        $projectName = $projectPlan?->project?->prj_name 
            ?? ($projectPlan?->ccp_hed_id ? \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $projectPlan->ccp_hed_id)->value('hed_name') : null)
            ?? 'Institutional Core Budget';
    }

    $empName = $case->ctc_empnamecomp ?: ($case->employee->emp_name ?? 'Candidate Name');
    $empDesignation = $case->ctc_newjobtitle ?: ($case->employee->emp_desig ?? 'N/A');
    $empGrade = $case->ctc_newgrade ?: ($case->employee->emp_grade ?? 'N/A');

    // Attachments
    $prjId = $projectPlan?->project?->prj_id ?? ($projectPlan?->ccp_prj_id ?? null);
    $projectAttachments = $prjId 
        ? \Illuminate\Support\Facades\DB::table('prj.prjattachments')
            ->where('jat_objid', $prjId)
            ->whereIn('jat_objtype', ['prj', 'Project'])
            ->whereNotNull('jat_path')
            ->where('jat_path', '<>', '')
            ->get()
        : collect();

    $caseAttachments = $case->attachments ?? collect();

    // Recent Cases
    $recentCases = $recentCases ?? \App\Models\HrCtrCase::with(['employee', 'unit', 'casePlans.project'])
        ->where('ctc_id', '!=', $case->ctc_id)
        ->whereNotIn('ctc_status', ['Draft'])
        ->orderBy('ctc_id', 'desc')
        ->take(5)
        ->get();

    // Project Allocation Grouping (Groups contiguous months under the same project head)
    $allocatedGroups = collect();
    $sortedPlans = $case->casePlans->sortBy('ccp_startdt')->values();

    if ($isHrIsAdmin) {
        // HR, Admin, and IS hires are by default strictly allocated to CSRF
        $monthCount = $case->casePlans->count() > 0 ? $case->casePlans->count() : 12;
        $allocatedGroups->push([
            'hed_id' => null,
            'prj_code' => 'CSRF',
            'prj_name' => 'Center Special Research Fund (CSRF)',
            'start_dt' => $case->ctc_newstartdt,
            'end_dt' => $case->ctc_newenddt,
            'month_count' => $monthCount,
        ]);
    } elseif ($sortedPlans->isNotEmpty()) {
        $currentGroup = null;
        foreach ($sortedPlans as $p) {
            $hedId = $p->ccp_hed_id;
            $prjCode = $p->project->prj_code ?? (\Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $hedId)->value('hed_code') ?? $projectCode);
            $prjName = $p->project->prj_name ?? (\Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $hedId)->value('hed_name') ?? $projectName);

            if ($currentGroup === null) {
                $currentGroup = [
                    'hed_id' => $hedId,
                    'prj_code' => $prjCode,
                    'prj_name' => $prjName,
                    'start_dt' => $p->ccp_startdt,
                    'end_dt' => $p->ccp_enddt,
                    'month_count' => 1,
                ];
            } elseif ($currentGroup['hed_id'] == $hedId && $currentGroup['prj_code'] == $prjCode) {
                $currentGroup['end_dt'] = $p->ccp_enddt;
                $currentGroup['month_count']++;
            } else {
                $allocatedGroups->push($currentGroup);
                $currentGroup = [
                    'hed_id' => $hedId,
                    'prj_code' => $prjCode,
                    'prj_name' => $prjName,
                    'start_dt' => $p->ccp_startdt,
                    'end_dt' => $p->ccp_enddt,
                    'month_count' => 1,
                ];
            }
        }
        if ($currentGroup !== null) {
            $allocatedGroups->push($currentGroup);
        }
    } else {
        $allocatedGroups->push([
            'hed_id' => null,
            'prj_code' => $projectCode,
            'prj_name' => $projectName,
            'start_dt' => $case->ctc_newstartdt,
            'end_dt' => $case->ctc_newenddt,
            'month_count' => 12,
        ]);
    }

    // Build full Project Cards with Financial Review metrics & drilldown links
    $finService = app(\App\Services\FinancialIntelligenceService::class);
    $allocatedProjects = $allocatedProjects ?? collect();
    $projectHiredCounts = $projectHiredCounts ?? collect();
    $projectCards = [];

    foreach ($allocatedGroups as $agIdx => $ag) {
        $hId = $ag['hed_id'];
        $alloc = $hId ? $allocatedProjects->firstWhere('hed_id', $hId) : null;
        $headRecord = $alloc ?: ($hId ? \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $hId)->first() : null);
        $prjId = $alloc->hed_prj_id ?? ($headRecord->hed_prj_id ?? null);
        $prjCode = $ag['prj_code'] ?? ($alloc->prj_code ?? ($headRecord->hed_code ?? 'PRJ'));
        $prjName = $ag['prj_name'] ?? ($alloc->prj_name ?? ($headRecord->hed_name ?? 'Project'));
        $hiredCount = $hId ? (int)($projectHiredCounts->get($hId, 0)) : 0;

        $fin = $hId ? $finService->getHeadStatus($hId) : null;
        $fAlloc = (float)($fin->pcc_share ?? ($fin->prj_share ?? ($fin->allocation ?? 0)));
        $fRec = (float)($fin->pcc_received ?? ($fin->received ?? 0));
        $fExp = (float)($fin->pcc_expenditure ?? ($fin->expenditure ?? 0));
        $fBal = (float)($fin->pcc_balance ?? ($fRec - $fExp));
        $fCmt = (float)($fin->pcc_commitments ?? ($fin->commitments ?? 0));
        $fInp = (float)($fin->pcc_in_process ?? ($fin->in_process ?? 0));
        $fAvail = (float)($fin->pcc_available ?? ($fBal - $fCmt - $fInp));
        $fSpent = (float)($fin->pcc_can_be_spent ?? ($fAlloc - $fExp - $fCmt - $fInp));

        $startFmt = $ag['start_dt'] ? \Carbon\Carbon::parse($ag['start_dt'])->format('d M, Y') : '';
        $endFmt = $ag['end_dt'] ? \Carbon\Carbon::parse($ag['end_dt'])->format('d M, Y') : '';
        $mCount = $ag['month_count'] ?? 1;
        $tenureDisplay = ($startFmt && $endFmt)
            ? "{$startFmt} – {$endFmt} ({$mCount} " . Str::plural('Month', $mCount) . ")"
            : "{$mCount} " . Str::plural('Month', $mCount);

        $cardKey = $hId ? (string)$hId : 'alloc_' . $agIdx;

        $projectCards[] = [
            'card_key' => $cardKey,
            'hed_id' => $hId,
            'prj_id' => $prjId,
            'prj_code' => $prjCode,
            'prj_name' => $prjName,
            'start_dt' => $ag['start_dt'],
            'end_dt' => $ag['end_dt'],
            'month_count' => $mCount,
            'tenure_display' => $tenureDisplay,
            'hired_count' => $hiredCount,
            'subhead' => 'HR',
            'allocation' => $fAlloc,
            'received' => $fRec,
            'expenditure' => $fExp,
            'balance' => $fBal,
            'commitments' => $fCmt,
            'in_process' => $fInp,
            'available' => $fAvail,
            'can_be_spent' => $fSpent,
            'expenditure_drilldown' => $hId ? route('division.finance-of-project.drilldown', [$hId, 'pcc', 'expenditure']) : '#',
            'commitments_drilldown' => $hId ? route('division.finance-of-project.drilldown', [$hId, 'pcc', 'commitments']) : '#',
            'in_process_drilldown' => $hId ? route('division.finance-of-project.drilldown', [$hId, 'pcc', 'in-process']) : '#',
            'subhead_drilldown' => $hId ? route('division.finance-of-project.drilldown', [$hId, 'subhead', 'expenditure', 'HR']) : '#',
            'full_report_url' => $prjId ? route('projects.financial_view', $hId) : ($hId ? route('projects.financial_view', $hId) : '#'),
            'project_details_url' => $prjId ? route('projects.show', $prjId) : '#',
            'attachments_url' => $hId ? route('projects.financial_view', $hId) . '#tab-docs' : '#',
            'milestones_url' => $hId ? route('projects.financial_view', $hId) . '#tab-milestones' : '#',
        ];
    }

    $activeProjectCard = $projectCards[0] ?? null;
    $totalContractMonths = $case->casePlans->count() ?: 12;
    $totalContractValue = (float)($case->ctc_price ?: ($proposedSalary * $totalContractMonths));

    // Prepare Financial Intelligence Data & Subheads Breakdown for project modals
    $projectModalsData = [];
    foreach ($projectCards as $pCard) {
        $pHeadId = $pCard['hed_id'] ?? null;
        if (!empty($pHeadId)) {
            $pHeadStatus = $finService->getHeadStatus($pHeadId);
            $pSubheadList = $finService->getSubheadBreakdown($pHeadId);
            $projectModalsData[$pCard['card_key']] = [
                'head' => $pHeadStatus,
                'subheads' => $pSubheadList,
                'pCard' => $pCard,
            ];
        }
    }
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

.dg-page {
    font-family: 'Inter', sans-serif;
    background: var(--rd-bg, #f4f6f9) !important;
    min-height: 100vh;
    color: var(--rd-text1, #0f172a);
    padding-bottom: 2rem;
}
.rajdhani {
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}

:root {
    --dg-label-size: 9.5px;
    --dg-value-size: 12.5px;
}

/* Page Header */
.dg-hdr {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 10px;
    padding-bottom: 4px;
}
.dg-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: var(--rd-text2, #475569);
    background: #ffffff;
    border: 1px solid var(--rd-border, #cbd5e1);
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none !important;
    transition: all .2s ease;
    font-weight: 600;
}
.dg-back-btn:hover {
    border-color: var(--rd-accent, #5F7858);
    color: var(--rd-accent, #5F7858);
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

/* 2-Column Grid Layout */
.dg-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}
@media(max-width: 1200px) {
    .dg-grid { grid-template-columns: 1fr; }
}

/* Section Labels */
.dg-sec-label {
    font-family: 'Rajdhani', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: var(--rd-accent, #5F7858);
    text-transform: uppercase;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.dg-sec-label::before {
    content: '';
    width: 3px;
    height: 12px;
    background: var(--rd-accent, #5F7858);
    border-radius: 2px;
    display: inline-block;
}

/* Main Container Boxes */
.dg-box {
    background: #ffffff;
    border: 1px solid var(--rd-border, #e2e8f0);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(15,23,42,0.04);
}
.dg-box-hdr {
    background: #f8fafc;
    padding: 12px 18px;
    border-bottom: 1px solid var(--rd-border, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Right Panels */
.dg-right {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.dg-panel-r {
    background: #ffffff;
    border: 1px solid var(--rd-border, #e2e8f0);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(15,23,42,0.04);
}
.dg-panel-r-hdr {
    background: #f8fafc;
    padding: 10px 14px;
    border-bottom: 1px solid var(--rd-border, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dg-panel-r-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 12px;
    font-weight: 700;
    color: var(--rd-accent, #5F7858);
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

/* Minute Section & Remarks Prominent Scrollbars */
#conversational-comments-box {
    scrollbar-width: thin !important;
    scrollbar-color: #64748b #f1f5f9 !important;
}
#conversational-comments-box::-webkit-scrollbar {
    width: 10px !important;
    height: 10px !important;
}
#conversational-comments-box::-webkit-scrollbar-track {
    background: #f1f5f9 !important;
    border-radius: 6px !important;
    border: 1px solid #e2e8f0 !important;
}
#conversational-comments-box::-webkit-scrollbar-thumb {
    background: #64748b !important;
    border-radius: 6px !important;
    border: 2px solid #f1f5f9 !important;
}
#conversational-comments-box::-webkit-scrollbar-thumb:hover {
    background: #334155 !important;
}

#inlineRemarks,
textarea.form-control,
textarea {
    scrollbar-width: thin !important;
    scrollbar-color: #64748b #f1f5f9 !important;
}
#inlineRemarks::-webkit-scrollbar,
textarea.form-control::-webkit-scrollbar,
textarea::-webkit-scrollbar {
    width: 10px !important;
    height: 10px !important;
}
#inlineRemarks::-webkit-scrollbar-track,
textarea.form-control::-webkit-scrollbar-track,
textarea::-webkit-scrollbar-track {
    background: #f8fafc !important;
    border-radius: 6px !important;
    border: 1px solid #e2e8f0 !important;
}
#inlineRemarks::-webkit-scrollbar-thumb,
textarea.form-control::-webkit-scrollbar-thumb,
textarea::-webkit-scrollbar-thumb {
    background: #64748b !important;
    border-radius: 6px !important;
    border: 2px solid #f8fafc !important;
}
#inlineRemarks::-webkit-scrollbar-thumb:hover,
textarea.form-control::-webkit-scrollbar-thumb:hover,
textarea::-webkit-scrollbar-thumb:hover {
    background: #334155 !important;
}

.table-responsive::-webkit-scrollbar,
.rd-table-responsive::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}
.table-responsive::-webkit-scrollbar-track,
.rd-table-responsive::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}
.table-responsive::-webkit-scrollbar-thumb,
.rd-table-responsive::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 6px;
    border: 2px solid #f1f5f9;
}
.table-responsive::-webkit-scrollbar-thumb:hover,
.rd-table-responsive::-webkit-scrollbar-thumb:hover {
    background: #334155;
}

/* Action Buttons (Matches Purchase Cases) */
.dg-btn-action {
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.6px;
    padding: 10px 14px;
    border-radius: 6px;
    border: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    text-transform: uppercase;
}
.dg-btn-success { background: #16a34a !important; color: #ffffff !important; }
.dg-btn-success:hover:not(:disabled) { background: #15803d !important; }
.dg-btn-success:disabled { opacity: 0.45; cursor: not-allowed; }

.dg-btn-info { background: var(--rd-accent, #5F7858) !important; color: #ffffff !important; }
.dg-btn-info:hover:not(:disabled) { background: #4d6247 !important; }

.dg-btn-danger { background: #dc2626 !important; color: #ffffff !important; }
.dg-btn-danger:hover:not(:disabled) { background: #b91c1c !important; }

.dg-btn-return { background: #fee2e2 !important; color: #dc2626 !important; border: 1.5px solid #fca5a5 !important; }
.dg-btn-return:hover:not(:disabled) { background: #dc2626 !important; color: #ffffff !important; }
.dg-btn-return:disabled { opacity: 0.4; cursor: not-allowed; }

/* Clean Spec & Data Tables */
.spec-data-table {
    width: 100%;
    font-size: 13.5px;
    border-collapse: collapse;
    margin-bottom: 0;
}
.spec-data-table th {
    padding: 10px 14px;
    color: #334155;
    font-weight: 800;
    font-size: 11.5px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: #f1f5f9;
    border-bottom: 1.5px solid #cbd5e1;
    font-family: 'Rajdhani', sans-serif;
    white-space: nowrap;
}
.spec-data-table td {
    padding: 11px 14px;
    border-top: 1px solid #e2e8f0;
    color: #0f172a;
    font-weight: 600;
    vertical-align: middle;
}
.spec-data-table tr:hover td {
    background: #f8fafc;
}

/* Clean Minimal Horizontal Info Strip (No redundant nested badge boxes) */
.clean-info-strip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 14px;
    align-items: center;
}
.clean-info-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.clean-info-label {
    font-size: var(--dg-label-size);
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.clean-info-val {
    font-size: var(--dg-value-size);
    font-weight: 600;
    color: #0f172a;
    word-break: break-word;
}

/* Divider */
.dg-divider {
    height: 1px;
    background: #eef1f5;
    margin: 18px 0;
}

/* Timeline remarks */
.dg-trail-body {
    padding: 12px 14px;
    max-height: 260px;
    min-height: 90px;
    overflow-y: auto;
}
.dg-tl-item {
    display: flex;
    gap: 9px;
    margin-bottom: 12px;
}
.dg-tl-node {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(95, 120, 88, 0.12);
    color: var(--rd-accent, #5F7858);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    flex-shrink: 0;
    margin-top: 2px;
}
.dg-tl-actor {
    font-family: 'Rajdhani', sans-serif;
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
}
.dg-tl-time {
    font-size: 10px;
    color: #64748b;
}
.dg-tl-comment {
    font-size: 11px;
    color: #475569;
    border-left: 2px solid var(--rd-accent, #5F7858);
    padding: 3px 8px;
    border-radius: 0 4px 4px 0;
    margin-top: 4px;
    background: #f8fafc;
    line-height: 1.4;
}

/* Quick Remarks Chips */
.quick-remark-chip {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    transition: all .2s;
}
.quick-remark-chip:hover {
    background: var(--rd-accent, #5F7858);
    color: #ffffff;
    border-color: var(--rd-accent, #5F7858);
}

/* Financial Pulse Card */
.dg-fin-card {
    border: 1px solid #d7dee6;
    border-radius: 10px;
    padding: 14px 16px;
    background: linear-gradient(180deg, #f8fafc 0%, #f4f7fa 100%);
}
.dg-fin-impact-row {
    background: #ffffff;
    border: 1px solid #c9e2cd;
    border-radius: 8px;
    padding: 8px 10px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dg-btn-approve {
    font-size: 13.5px !important;
    padding: 11px 14px !important;
    border-radius: 7px !important;
    box-shadow: 0 2px 6px rgba(5,150,105,0.25);
    letter-spacing: 0.4px;
}

/* Send Button: wide on the left */
.btn-action-send {
    background: #2563eb !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
}
.btn-action-send:hover {
    background: #1d4ed8 !important;
    box-shadow: 0 4px 10px rgba(29, 78, 216, 0.35);
    transform: translateY(-1px);
}

/* Compact Approve Button: green icon on right, expands on hover */
.btn-action-approve {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-approve .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
    white-space: nowrap;
}
.btn-action-approve:hover {
    flex: 0 0 auto !important;
    width: auto !important;
    min-width: 145px;
    padding: 0 14px !important;
    background: #15803d !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    transform: translateY(-1px);
}
.btn-action-approve:hover .btn-expand-text {
    max-width: 130px;
    opacity: 1;
    margin-left: 8px;
}

/* Compact Cancel Button: red icon on right, expands on hover */
.btn-action-cancel {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #dc2626 !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-cancel .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
    white-space: nowrap;
}
.btn-action-cancel:hover {
    flex: 0 0 auto !important;
    width: auto !important;
    min-width: 135px;
    padding: 0 14px !important;
    background: #b91c1c !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}
.btn-action-cancel:hover .btn-expand-text {
    max-width: 130px;
    opacity: 1;
    margin-left: 8px;
}

.cc-dest-option-item:hover {
    background: #f1f5f9;
}
.cc-dest-option-item.selected {
    background: #e2e8f0;
}

/* Project Allocation Badges */
.project-badge-btn {
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13.5px;
    letter-spacing: 0.5px;
    padding: 4px 12px;
    border-radius: 6px;
    cursor: pointer;
    background: #f8fafc;
    color: #0f172a;
    border: 1.5px solid #cbd5e1;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.project-badge-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
    border-color: #94a3b8;
    transform: translateY(-1px);
}
.project-badge-btn.active {
    background: var(--rd-accent, #5F7858) !important;
    color: #ffffff !important;
    border-color: #4d6247 !important;
    box-shadow: 0 2px 6px rgba(95, 120, 88, 0.35);
}

/* Drilldown Buttons */
.btn-drill-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 4px;
    font-size: 0.65rem;
    margin-left: 6px;
    transition: all 0.2s ease;
    text-decoration: none !important;
}
.btn-drill-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.btn-drill-red { background: #fee2e2; color: #dc2626 !important; border: 1px solid #fca5a5; }
.btn-drill-red:hover { background: #dc2626; color: #fff !important; }
.btn-drill-amber { background: #fef3c7; color: #d97706 !important; border: 1px solid #fcd34d; }
.btn-drill-amber:hover { background: #d97706; color: #fff !important; }
.btn-drill-gray { background: #f1f5f9; color: #64748b !important; border: 1px solid #cbd5e1; }
.btn-drill-gray:hover { background: #64748b; color: #fff !important; }
</style>

<div class="content-wrapper dg-page">
    <div class="p-3 pt-3">
        <div class="container-fluid">

            {{-- 2-Column Grid (Left: Case Details & Financials | Right: Minute & Trail) --}}
            <div class="dg-grid">

                {{-- ========================================================= --}}
                {{-- LEFT PANE: CONSOLIDATED CONTRACT CASE                     --}}
                {{-- ========================================================= --}}
                <div class="dg-box">

                    {{-- Box Header --}}
                    <div class="dg-box-hdr">
                        <div class="d-flex align-items-center gap-2" style="margin-bottom:0;">
                            <span class="dg-sec-label mb-0" style="font-size: 13.5px; font-weight: 800; letter-spacing: 0.8px;">
                                <i class="fas fa-file-signature text-primary mr-1.5"></i> HIRING CASE
                            </span>
                            <span class="text-muted mx-1" style="font-size: 14px; font-weight: 400;">|</span>
                            <span class="font-weight-bold text-dark rajdhani" style="font-size: 16.5px; letter-spacing: 0.5px;">{{ $empName }}</span>
                        </div>
                        <div class="dg-box-hdr-right d-flex align-items-center gap-2">
                            <span class="badge border font-weight-bold px-3 py-1.5 rajdhani" style="font-size: 13px; letter-spacing: 0.5px; background: #ffffff; color: #0f172a; border-color: #cbd5e1 !important;">
                                <i class="fas fa-tags text-primary mr-1"></i>
                                @if($isHiring)
                                    Hg — New Hiring
                                @elseif($isRenewal)
                                    Cr — Contract Renewal
                                @elseif($isExtension)
                                    Ce — Contract Extension
                                @elseif($isRehiring)
                                    Rh — Re-Hiring
                                @else
                                    {{ $caseTypeRaw }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="p-4" style="flex: 1; overflow-y: auto;">
                        
                        {{-- Top Header Section: Case Metadata on Left & Financial Review + Case Financials on Right (Exact Purchase Case Layout) --}}
                        <div class="mb-4 d-flex align-items-start gap-4">
                            <div style="flex: 1;">
                                <div class="d-flex flex-column" style="gap: 8px; font-size: 13px;">
                                    <div><strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;"><i class="fas fa-hashtag text-primary mr-2"></i>CASE ID:</strong> <span class="text-dark font-weight-bold rajdhani" style="font-size: 16px; color: #0f172a !important; font-weight: 800;">#CC-{{ $case->ctc_id }}</span></div>
                                    <div><strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;"><i class="far fa-calendar-alt text-primary mr-2"></i>DATE:</strong> <span class="text-dark font-weight-bold rajdhani" style="font-size: 14.5px; color: #0f172a !important; font-weight: 700;">{{ $case->ctc_date ? \Carbon\Carbon::parse($case->ctc_date)->format('d M, Y') : '—' }}</span></div>

                                    <div><strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;"><i class="fas fa-building text-primary mr-2"></i>DIVISION:</strong> <span class="text-dark font-weight-bold" style="font-size: 14px; color: #0f172a !important; font-weight: 700;">{{ $case->division_name }}</span></div>

                                    @php
                                        $statusClass = match(strtolower(trim($case->ctc_status))) {
                                            'approved'  => 'badge-success',
                                            'returned'  => 'badge-danger',
                                            'cancelled' => 'badge-danger',
                                            'draft'     => 'badge-secondary',
                                            default     => 'badge-primary',
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;"><i class="fas fa-info-circle text-primary mr-2"></i>CASE STATUS:</strong> 
                                        <span class="badge {{ $statusClass }} font-weight-bold px-3 py-1.5 rajdhani" style="font-size: 13px; letter-spacing: 0.5px; border-radius: 6px;">
                                            {{ $case->ctc_status }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;"><i class="fas fa-map-marker-alt text-primary mr-2"></i>LOCATION:</strong> 
                                        <span class="badge font-weight-bold px-3 py-1.5 rajdhani" style="background: #e0f2fe; color: #0369a1 !important; border: 1.5px solid #bae6fd; font-size: 13px; letter-spacing: 0.4px; border-radius: 6px;">
                                            <i class="fas fa-building mr-1.5 text-primary"></i> Currently with: {{ $case->current_office_name ?? $currentStage }}
                                        </span>
                                    </div>

                                    {{-- Allocated Projects: Badges (Comma-separated) --}}
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;">
                                            <i class="fas fa-project-diagram text-primary mr-2"></i>ALLOCATED PROJECTS:
                                        </strong>
                                        <div class="d-inline-flex align-items-center flex-wrap" style="gap: 5px;">
                                            @forelse($projectCards as $idx => $pCard)
                                                <span class="badge project-badge-btn {{ $idx === 0 ? 'active' : '' }}" 
                                                      onclick="selectProject('{{ $pCard['card_key'] }}')"
                                                      data-card-key="{{ $pCard['card_key'] }}"
                                                      title="Click to view details for {{ $pCard['prj_code'] }}">
                                                    <i class="fas fa-folder-open mr-1.5"></i>{{ $pCard['prj_code'] }}
                                                </span>
                                                @if(!$loop->last)
                                                    <span class="text-dark font-weight-bold mr-1" style="font-size: 14px;">,</span>
                                                @endif
                                            @empty
                                                <span class="text-muted small">None</span>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- Dynamic Vertical Details of Selected Project --}}
                                    <div>
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;">
                                            <i class="far fa-calendar-alt text-primary mr-2"></i>DATE (FROM - TO):
                                        </strong>
                                        <span class="text-dark font-weight-bold rajdhani" id="activeProjectTenure" style="color: #0f172a !important; font-size: 14.5px; font-weight: 700;">
                                            {{ $activeProjectCard['tenure_display'] ?? ($activeProjectCard['period_formatted'] ?? '—') }}
                                        </span>
                                    </div>

                                    <div>
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;">
                                            <i class="fas fa-users text-primary mr-2"></i>ALREADY HIRED STAFF:
                                        </strong>
                                        <span class="badge badge-light border text-dark font-weight-bold rajdhani px-2.5 py-1" id="activeProjectHiredStaff" style="font-size: 13.5px; color: #0f172a !important; background: #f8fafc; border-color: #cbd5e1 !important; border-radius: 6px;">
                                            <i class="fas fa-users text-primary mr-1"></i> {{ $activeProjectCard['hired_count'] ?? 0 }} Staff
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;">
                                            <i class="fas fa-layer-group text-primary mr-2"></i>SUBHEAD:
                                        </strong>
                                        <div class="d-inline-flex align-items-center border bg-white shadow-sm px-3 py-1" style="border: 1.5px solid #cbd5e1 !important; height: 30px; gap: 8px; border-radius: 6px;">
                                            <span class="font-weight-bold text-dark rajdhani" id="activeProjectSubhead" style="font-size: 13.5px; font-weight: 800; letter-spacing: 0.5px;">HR</span>
                                            <a id="activeSubheadDrilldownLink" 
                                               href="{{ $activeProjectCard['subhead_drilldown'] ?? '#' }}" 
                                               target="_blank" 
                                               class="btn btn-xs btn-primary p-0 d-inline-flex align-items-center justify-content-center" 
                                               style="width: 22px; height: 22px; font-size: 11px; border-radius: 4px; background: #2563eb; border: none; box-shadow: 0 1px 3px rgba(37,99,235,0.3);" 
                                               title="View HR Subhead Breakdown">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <strong style="color: #0f172a; width: 185px; display:inline-block; font-weight: 800; font-size: 13.5px; letter-spacing: 0.3px;">
                                            <i class="fas fa-paperclip text-primary mr-2"></i>PROJECT ATTACHMENTS:
                                        </strong>
                                        <div class="d-inline-flex align-items-center flex-wrap" style="gap: 8px;">
                                            <a id="activeProjectDocsLink" 
                                               href="{{ $activeProjectCard['attachments_url'] ?? '#' }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-success font-weight-bold rajdhani px-3 py-1 d-inline-flex align-items-center shadow-sm" 
                                               style="font-size: 12.5px; height: 30px; border-radius: 6px; gap: 6px; border-width: 1.5px;" 
                                               title="Files & Attachments">
                                                <i class="fas fa-paperclip"></i> Files & Attachments
                                            </a>
                                            <a id="activeProjectMilestonesLink" 
                                               href="{{ $activeProjectCard['milestones_url'] ?? '#' }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-warning font-weight-bold rajdhani px-3 py-1 d-inline-flex align-items-center shadow-sm" 
                                               style="font-size: 12.5px; height: 30px; border-radius: 6px; gap: 6px; color: #b45309; border-color: #f59e0b; border-width: 1.5px;" 
                                               title="Milestone Costs">
                                                <i class="fas fa-coins"></i> Milestone Costs
                                            </a>
                                            <a id="activeProjectDetailsLink" 
                                               href="{{ !empty($activeProjectCard['prj_id']) ? route('projects.show', $activeProjectCard['prj_id']) : '#' }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary font-weight-bold rajdhani px-3 py-1 align-items-center shadow-sm" 
                                               style="font-size: 12.5px; height: 30px; border-radius: 6px; gap: 6px; border-width: 1.5px; {{ !empty($activeProjectCard['prj_id']) ? 'display: inline-flex;' : 'display: none;' }}" 
                                               title="Project Details">
                                                <i class="fas fa-project-diagram"></i> Project Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Financial Overview & Case Cost Summary (Enlarged) --}}
                            <div class="text-right d-flex flex-column align-items-end" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 18px 24px; font-size: 14px; min-width: 370px; max-width: 410px; box-shadow: 0 4px 14px rgba(0,0,0,0.06);">
                                <div class="d-flex justify-content-between align-items-center w-100 mb-2.5 pb-2" style="border-bottom: 1.5px solid #e2e8f0;">
                                    <h6 class="rajdhani text-primary font-weight-bold mb-0" style="font-size: 15px; font-weight: 800; letter-spacing: 1px;">
                                        <i class="fas fa-chart-pie mr-1"></i> FINANCIAL REVIEW
                                    </h6>
                                    @if(!empty($activeProjectCard['hed_id']))
                                    <button id="finReviewFullReportBtn" type="button" class="btn btn-xs btn-outline-primary rajdhani font-weight-bold py-0.5 px-2" data-toggle="modal" data-target="#financialIntelligenceModal_{{ $activeProjectCard['card_key'] }}" style="font-size: 11px; border-radius: 4px; font-weight: 800; letter-spacing: 0.5px;">
                                        <i class="fas fa-expand-arrows-alt mr-1"></i> FULL REPORT
                                    </button>
                                    @else
                                    <button id="finReviewFullReportBtn" type="button" class="btn btn-xs btn-outline-primary rajdhani font-weight-bold py-0.5 px-2" style="font-size: 11px; border-radius: 4px; display: none;">
                                        <i class="fas fa-expand-arrows-alt mr-1"></i> FULL REPORT
                                    </button>
                                    @endif
                                </div>
                                
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 6px 28px; text-align: left;">
                                    <div class="text-muted font-weight-bold" style="font-size: 13px; letter-spacing: 0.6px;">ALLOCATED</div>
                                    <div class="text-dark font-weight-bold text-right" id="finReviewAllocated" style="font-size: 17px; color: #0f172a !important;">{{ number_format($activeProjectCard['allocation'] ?? 0) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 13px; letter-spacing: 0.6px;">RECEIVED</div>
                                    <div class="text-dark font-weight-bold text-right" id="finReviewReceived" style="font-size: 17px; color: #0f172a !important;">{{ number_format($activeProjectCard['received'] ?? 0) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 13px; letter-spacing: 0.6px;">EXPENDITURE</div>
                                    <div class="text-right d-flex justify-content-end align-items-center">
                                        <a id="finReviewExpenditure" href="{{ $activeProjectCard['expenditure_drilldown'] ?? '#' }}" target="_blank" class="text-danger font-weight-bold text-decoration-none" style="font-size: 17px; color: #dc2626 !important;" title="View Project Expenditure Breakdown">
                                            {{ number_format($activeProjectCard['expenditure'] ?? 0) }}
                                        </a>
                                        <a id="finReviewExpDrillLink" href="{{ $activeProjectCard['expenditure_drilldown'] ?? '#' }}" target="_blank" class="btn-drill-link btn-drill-red" title="View Project Expenditure Breakdown">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 13px; letter-spacing: 0.6px;">BALANCE</div>
                                    <div class="text-primary font-weight-bold text-right" id="finReviewBalance" style="font-size: 17px; color: #2563eb !important;">{{ number_format($activeProjectCard['balance'] ?? 0) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 13px; letter-spacing: 0.6px;">COMMITMENTS</div>
                                    <div class="text-right d-flex justify-content-end align-items-center">
                                        <a id="finReviewCommitments" href="{{ $activeProjectCard['commitments_drilldown'] ?? '#' }}" target="_blank" class="text-warning font-weight-bold text-decoration-none" style="font-size: 17px; color: #d97706 !important;" title="View Project Commitments Breakdown">
                                            {{ number_format($activeProjectCard['commitments'] ?? 0) }}
                                        </a>
                                        <a id="finReviewCmtDrillLink" href="{{ $activeProjectCard['commitments_drilldown'] ?? '#' }}" target="_blank" class="btn-drill-link btn-drill-amber" title="View Project Commitments Breakdown">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 13px; letter-spacing: 0.6px;">IN PROCESS</div>
                                    <div class="text-right d-flex justify-content-end align-items-center">
                                        <a id="finReviewInProcess" href="{{ $activeProjectCard['in_process_drilldown'] ?? '#' }}" target="_blank" class="text-muted font-weight-bold text-decoration-none" style="font-size: 17px; color: #64748b !important;" title="View Project In-Process Cases">
                                            {{ number_format($activeProjectCard['in_process'] ?? 0) }}
                                        </a>
                                        <a id="finReviewInpDrillLink" href="{{ $activeProjectCard['in_process_drilldown'] ?? '#' }}" target="_blank" class="btn-drill-link btn-drill-gray" title="View Project In-Process Cases">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    
                                    {{-- Full-width clean divider --}}
                                    <div style="grid-column: 1 / -1; border-top: 1.5px solid #cbd5e1; margin: 3px 0 2px 0;"></div>

                                    <div class="text-success font-weight-bold" style="font-size: 14.5px; color: #16a34a !important; letter-spacing: 0.6px;">AVAILABLE</div>
                                    <div class="text-success font-weight-bold text-right" id="finReviewAvailable" style="font-size: 18px; color: #16a34a !important;">{{ number_format($activeProjectCard['available'] ?? 0) }}</div>
                                    
                                    <div class="text-warning font-weight-bold" style="font-size: 14.5px; color: #d97706 !important; letter-spacing: 0.6px;">CAN BE SPENT</div>
                                    <div class="text-warning font-weight-bold text-right" id="finReviewCanBeSpent" style="font-size: 19px; font-weight: 900; color: #d97706 !important;">{{ number_format($activeProjectCard['can_be_spent'] ?? 0) }}</div>
                                </div>

                                {{-- Separator --}}
                                <div class="w-100 my-2.5" style="border-top: 1.5px dashed #cbd5e1;"></div>

                                {{-- Case Cost Summary Header --}}
                                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                                    <h6 class="rajdhani text-primary font-weight-bold mb-0" style="font-size: 14px; font-weight: 800; letter-spacing: 0.8px;">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> CASE FINANCIALS
                                    </h6>
                                </div>

                                {{-- Structured Case Cost Grid --}}
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 5px 24px; text-align: left;">
                                    <div class="text-muted font-weight-bold" style="font-size: 13px;">Monthly Salary</div>
                                    <div class="text-dark font-weight-bold text-right" style="font-size: 15px; color: #0f172a !important;">{{ number_format($proposedSalary, 2) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 13px;">Probation Period</div>
                                    <div class="font-weight-bold text-right {{ $probationMonths ? 'text-warning' : 'text-muted' }}" style="font-size: 14px;">
                                        {{ $probationMonths ? $probationMonths . ' ' . Str::plural('Month', $probationMonths) : 'N/A' }}
                                    </div>

                                    <div class="text-muted font-weight-bold" style="font-size: 13px;">Probation Salary</div>
                                    <div class="font-weight-bold text-right {{ $probationSalary ? 'text-dark' : 'text-muted' }}" style="font-size: 14px; {{ $probationSalary ? 'color: #0f172a !important;' : '' }}">
                                        {{ $probationSalary ? number_format($probationSalary, 2) : 'N/A' }}
                                    </div>

                                    <div class="text-muted font-weight-bold" style="font-size: 13px;">Hiring Tenure</div>
                                    <div class="text-dark font-weight-bold text-right" style="font-size: 14.5px; color: #0f172a !important;">
                                        {{ $totalContractMonths }} {{ Str::plural('Month', $totalContractMonths) }}
                                    </div>

                                    <div class="text-muted font-weight-bold" style="font-size: 13px;">Increment</div>
                                    <div class="font-weight-bold text-right" style="font-size: 14px;">
                                        @if($salaryDiff > 0)
                                            <span class="text-success font-weight-bold">+{{ $incrementPct }}% (+Rs. {{ number_format($salaryDiff) }})</span>
                                        @else
                                            <span class="text-muted font-weight-bold">0%</span>
                                        @endif
                                    </div>

                                    {{-- Full-width clean divider for TOTAL PACKAGE --}}
                                    <div style="grid-column: 1 / -1; border-top: 1.5px solid #cbd5e1; margin: 4px 0 2px 0;"></div>

                                    <div class="text-success font-weight-bold" style="font-size: 14.5px; color: #16a34a !important; letter-spacing: 0.6px;">TOTAL PACKAGE</div>
                                    <div class="text-success font-weight-bold text-right" style="font-size: 18px; font-weight: 900; color: #16a34a !important;">{{ number_format($totalContractValue, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="dg-divider mb-4 mt-2" style="background: #e2e8f0;"></div>

                        {{-- ===================================================== --}}
                        {{-- CANDIDATE DETAILS (PROMINENT & BOLD)                  --}}
                        {{-- ===================================================== --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2" style="font-size: 13px; font-weight: 800; color: #1e293b; letter-spacing: 0.7px;">
                                <i class="fas fa-id-card text-primary mr-1.5"></i> CANDIDATE DETAILS
                            </div>
                            <div class="p-3 rounded border" style="background: #ffffff; border-color: #e2e8f0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;"><i class="fas fa-user text-primary mr-1"></i> CANDIDATE NAME</span>
                                        <span class="text-dark font-weight-bold rajdhani" style="font-size: 16.5px; letter-spacing: 0.3px;">{{ $empName }}</span>
                                    </div>
                                    <div>
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;">FATHER'S NAME</span>
                                        <span class="text-dark font-weight-bold" style="font-size: 15px;">{{ $case->father_name ?: 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;">CNIC #</span>
                                        <span class="rajdhani font-weight-bold text-dark" style="font-size: 16px; letter-spacing: 0.5px;">{{ $case->candidate_cnic ?: 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;">CONTACT / MOBILE</span>
                                        <span class="text-dark font-weight-bold rajdhani" style="font-size: 15px; letter-spacing: 0.3px;">{{ $case->candidate_mobile ?: 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;">EMPLOYEE / SYSTEM ID</span>
                                        <span class="text-primary font-weight-bold rajdhani" style="font-size: 16px; letter-spacing: 0.5px;">{{ $case->ctc_emp_id ? '#' . $case->ctc_emp_id : '#New Candidate' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dg-divider mb-4 mt-2" style="background: #e2e8f0;"></div>

                        {{-- ===================================================== --}}
                        {{-- 3. CONTRACT DETAILS (TYPE, PROBATION, TERMS TABLE)    --}}
                        {{-- ===================================================== --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0" style="font-size: 13px; font-weight: 800; color: #1e293b; letter-spacing: 0.7px;">
                                    <i class="fas fa-file-contract text-primary mr-1.5"></i>
                                    {{ $isRenewalOrExt ? 'CONTRACT DETAILS & TERMS COMPARISON' : 'CONTRACT DETAILS & PROPOSED TERMS' }}
                                </div>
                            </div>

                            {{-- Contract Metadata Sub-Row (Prominent & Bold) --}}
                            <div class="p-3 rounded border mb-3" style="background: #ffffff; border-color: #e2e8f0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                <div class="d-flex align-items-center justify-content-start flex-wrap gap-5">
                                    <div style="min-width: 170px;">
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;"><i class="fas fa-briefcase text-primary mr-1"></i> EMPLOYMENT TYPE</span>
                                        <span class="text-dark font-weight-bold" style="font-size: 15px;">{{ $empType }}</span>
                                    </div>
                                    <div style="min-width: 170px;">
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;"><i class="fas fa-stopwatch text-warning mr-1"></i> PROBATION PERIOD</span>
                                        <span class="font-weight-bold {{ $probationMonths ? 'text-dark' : 'text-muted' }}" style="font-size: 15px;">
                                            {{ $probationMonths ? $probationMonths . ' Months' : 'N/A' }}
                                        </span>
                                    </div>
                                    <div style="min-width: 170px;">
                                        <span style="font-size: 11.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 3px;"><i class="fas fa-money-bill-wave text-success mr-1"></i> PROBATION SALARY</span>
                                        <span class="rajdhani font-weight-bold {{ $probationSalary ? 'text-success' : 'text-muted' }}" style="font-size: 16px;">
                                            {{ $probationSalary ? 'Rs. ' . number_format($probationSalary) : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Terms & Comparison Table --}}
                            <div class="table-responsive border rounded" style="background: #ffffff; border-color: #cbd5e1 !important; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                @if($isRenewalOrExt)
                                    {{-- Renewal / Extension Comparative View --}}
                                    <table class="spec-data-table">
                                        <thead>
                                            <tr>
                                                <th>TERMS STAGE</th>
                                                <th>POSITION & GRADE</th>
                                                <th>TENURE (START & END)</th>
                                                <th class="text-right">MONTHLY SALARY</th>
                                                <th class="text-right">ANNUAL IMPACT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Row 1: Previous Terms --}}
                                            <tr style="background: #ffffff;">
                                                <td>
                                                    <span class="text-muted font-weight-bold" style="font-size: 12.5px;">Previous Contract</span>
                                                </td>
                                                <td>
                                                    <span class="text-muted font-weight-bold" style="font-size: 13.5px;">{{ $prevJobtitle }} ({{ $prevGrade }})</span>
                                                </td>
                                                <td>
                                                    <span class="rajdhani text-muted font-weight-bold" style="font-size: 14px;">
                                                        {{ $prevStart ? \Carbon\Carbon::parse($prevStart)->format('d M, Y') : 'N/A' }}
                                                        &mdash;
                                                        {{ $prevEnd ? \Carbon\Carbon::parse($prevEnd)->format('d M, Y') : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="text-right rajdhani text-muted font-weight-bold" style="font-size: 15px;">
                                                    {{ $previousSalary > 0 ? 'Rs. ' . number_format($previousSalary) : 'N/A' }}
                                                </td>
                                                <td class="text-right rajdhani text-muted font-weight-bold" style="font-size: 15px;">
                                                    {{ $previousSalary > 0 ? 'Rs. ' . number_format($previousSalary * 12) : 'N/A' }}
                                                </td>
                                            </tr>

                                            {{-- Row 2: Proposed / Renewed Terms --}}
                                            <tr style="background: #f6faf7;">
                                                <td>
                                                    <strong class="text-primary font-weight-bold" style="font-size: 13px;">Proposed Renewal</strong>
                                                </td>
                                                <td>
                                                    <strong class="text-dark font-weight-bold" style="font-size: 15px;">{{ $empDesignation }} ({{ $empGrade }})</strong>
                                                </td>
                                                <td class="rajdhani font-weight-bold text-dark" style="font-size: 15px;">
                                                    {{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M, Y') : 'N/A' }}
                                                    &mdash;
                                                    {{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 16px;">
                                                    Rs. {{ number_format($proposedSalary) }}
                                                </td>
                                                <td class="text-right font-weight-bold text-primary rajdhani" style="font-size: 16px;">
                                                    Rs. {{ number_format($annualImpact) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    {{-- 1-Line Increment Summary Strip --}}
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" style="background: #f0fdf4; border-color: #86efac !important; font-size: 12px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-success rajdhani font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">
                                                INCREMENT: +Rs. {{ number_format($salaryDiff) }} ({{ $incrementPct > 0 ? '+' . $incrementPct : $incrementPct }}%)
                                            </strong>
                                            <span class="text-dark font-weight-bold ml-3" style="font-size: 13px;">
                                                @if($prevGrade !== $empGrade)
                                                    Grade: <span class="text-muted">{{ $prevGrade }}</span> &rarr; <span class="text-primary">{{ $empGrade }}</span>
                                                @else
                                                    Grade: <span class="text-dark">{{ $empGrade }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="rajdhani font-weight-bold text-success" style="font-size: 14px;">
                                            Annual Delta: +Rs. {{ number_format($salaryDiff * 12) }}
                                        </div>
                                    </div>
                                @else
                                    {{-- Fresh Hiring Table --}}
                                    <table class="spec-data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">S.NO</th>
                                                <th>PROPOSED POSITION</th>
                                                <th>PAY SCALE / GRADE</th>
                                                <th>TENURE (START & END)</th>
                                                <th class="text-right">MONTHLY SALARY</th>
                                                <th class="text-right">ANNUAL IMPACT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="font-weight-bold text-dark" style="font-size: 14px;">1</td>
                                                <td class="font-weight-bold text-dark" style="font-size: 15px;">{{ $empDesignation }}</td>
                                                <td class="font-weight-bold text-dark" style="font-size: 15px;">{{ $empGrade }}</td>
                                                <td class="rajdhani font-weight-bold text-dark" style="font-size: 15px;">
                                                    {{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M, Y') : 'N/A' }}
                                                    &mdash;
                                                    {{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 16px;">Rs. {{ number_format($proposedSalary) }}</td>
                                                <td class="text-right font-weight-bold text-primary rajdhani" style="font-size: 16px;">Rs. {{ number_format($annualImpact) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                        <div class="dg-divider mb-3 mt-2" style="background: #e2e8f0;"></div>

                        {{-- Terms & Conditions Banner Note --}}
                        <div class="p-3 rounded border" style="background: #f8fafc; border-color: #e2e8f0 !important; font-size: 12.5px; font-weight: 600; color: #334155; line-height: 1.5; border-radius: 7px;">
                            <i class="fas fa-info-circle text-primary mr-1"></i> Salary and monthly allowances will be disbursed from project funds allocated under <strong>{{ $activeProjectCard['prj_code'] ?? $projectCode }}</strong> (Pay & Allowances HR Head). Contract renewal/extension is subject to executive approval and institutional rules.
                        </div>

                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- RIGHT PANE: MINUTE / DECISION, TRAIL & RECENT CASES        --}}
                {{-- ========================================================= --}}
                <div class="dg-right">

                    {{-- 1. Scrutiny & Minute Trail (Matches Purchase Cases Design) --}}
                    @php
                        $caseAttachments = $case->attachments ?? collect();
                    @endphp
                    <div class="dg-panel-r" style="overflow: visible;">
                        <div class="dg-panel-r-hdr py-2 px-3 d-flex align-items-center justify-content-between" style="position: relative; border-top-left-radius: 9px; border-top-right-radius: 9px;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-file-alt text-primary" style="font-size: 13px;"></i>
                                <span class="dg-panel-r-title font-weight-bold rajdhani" style="font-size: 12px; font-weight: 700; color: #0f172a !important; letter-spacing: 0.5px; text-transform: uppercase;">Minute</span>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                {{-- Case Attachments Dropdown Trigger on Far Right of Minute Header (Matches Purchase Cases Design) --}}
                                <div class="dropdown" id="ctcCaseAttachmentsDropdownWrap">
                                    <button type="button" class="btn btn-xs font-weight-bold rajdhani px-2 py-1 d-flex align-items-center dropdown-toggle shadow-none" id="btnCaseAttachmentsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 11px; height: 26px; border-radius: 6px; gap: 5px; background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; cursor: pointer;" title="View or Add Case Attachments">
                                        <i class="fas fa-paperclip text-primary" style="font-size: 11.5px;"></i>
                                        <span>CASE ATTACHMENTS</span>
                                        <span class="badge badge-primary badge-pill ml-1" id="ctcCaseAttCountBadge" style="font-size: 9.5px; padding: 2px 6px;">{{ $caseAttachments->count() }}</span>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right shadow-lg p-0" aria-labelledby="btnCaseAttachmentsDropdown" style="width: 380px; max-width: 92vw; border-radius: 8px; border: 1.5px solid #cbd5e1; z-index: 1060; margin-top: 5px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;">
                                        {{-- Dropdown Header with Add Button --}}
                                        <div class="d-flex justify-content-between align-items-center py-2 px-3 border-bottom" style="background: #f8fafc;">
                                            <div class="d-flex align-items-center font-weight-bold text-dark rajdhani" style="font-size: 12px; gap: 6px;">
                                                <i class="fas fa-paperclip text-primary"></i>
                                                <span>ATTACHED CASE FILES</span>
                                            </div>
                                            <button type="button" class="btn btn-xs btn-primary font-weight-bold rajdhani px-2 py-0.5 d-flex align-items-center" data-toggle="modal" data-target="#modalAddContractCaseAttachment" style="font-size: 11px; height: 23px; border-radius: 4px; background: var(--rd-accent, #5F7858) !important; border: none; gap: 4px;" title="Upload New Case Attachment">
                                                <i class="fas fa-plus"></i> <span>ADD</span>
                                            </button>
                                        </div>

                                        {{-- Dropdown Body: Attachments List --}}
                                        <div class="px-3 py-2" id="ctcCaseAttachmentsList" style="font-size: 11.5px; max-height: 250px; overflow-y: auto;">
                                            @if($caseAttachments->count() > 0)
                                                @foreach($caseAttachments as $cIdx => $cDoc)
                                                    @php
                                                        $cName = trim((string)($cDoc->cat_type ?: ''));
                                                        if (empty($cName)) {
                                                            $cName = basename(str_replace('\\', '/', $cDoc->cat_path));
                                                        }
                                                        $ext = strtolower(pathinfo($cDoc->cat_path, PATHINFO_EXTENSION));
                                                    @endphp
                                                    <div class="d-flex justify-content-between align-items-center py-1.5 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #f1f5f9 !important;">
                                                        <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0; gap: 6px;">
                                                            <span class="text-muted font-weight-bold flex-shrink-0" style="font-size: 10px; width: 16px;">{{ $cIdx + 1 }}.</span>
                                                            @if(in_array($ext, ['pdf']))
                                                                <i class="far fa-file-pdf text-danger flex-shrink-0" style="font-size: 12px;"></i>
                                                            @elseif(in_array($ext, ['doc', 'docx']))
                                                                <i class="far fa-file-word text-primary flex-shrink-0" style="font-size: 12px;"></i>
                                                            @elseif(in_array($ext, ['xls', 'xlsx']))
                                                                <i class="far fa-file-excel text-success flex-shrink-0" style="font-size: 12px;"></i>
                                                            @elseif(in_array($ext, ['png', 'jpg', 'jpeg']))
                                                                <i class="far fa-file-image text-info flex-shrink-0" style="font-size: 12px;"></i>
                                                            @else
                                                                <i class="far fa-file-alt text-secondary flex-shrink-0" style="font-size: 12px;"></i>
                                                            @endif
                                                            <span class="text-truncate font-weight-bold text-dark" style="font-size: 11.5px;" title="{{ $cName }}">
                                                                {{ $cName }}
                                                            </span>
                                                        </div>
                                                        <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                                                            <a href="{{ route('universal.attachment.view', ['module' => 'ctc', 'id' => $cDoc->cat_id]) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 font-weight-bold d-inline-flex align-items-center" style="font-size: 11px; height: 22px; border-radius: 4px; gap: 4px;" title="View {{ $cName }}">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-3 text-muted" id="ctcNoAttachmentsMsg" style="font-size: 11px;">
                                                    <i class="fas fa-folder-open text-muted mr-1"></i> No case attachments uploaded yet.
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Dropdown Footer: Quick Action to Attach Document --}}
                                        <div class="p-2 border-top bg-light text-center" style="border-color: #e2e8f0 !important;">
                                            <button type="button" class="btn btn-xs btn-outline-success font-weight-bold w-100 py-1 d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalAddContractCaseAttachment" style="font-size: 11px; border-radius: 4px; gap: 5px;">
                                                <i class="fas fa-plus"></i> <span>ATTACH NEW DOCUMENT</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3" id="conversational-comments-box" style="max-height: 620px; overflow-y: auto;">
                            
                            @php
                                $approvalService = app(\App\Services\ContractCaseApprovalService::class);
                                $destinations = $approvalService->getAvailableDestinations($role);
                                $u = Auth::user();

                                // Calculate sequential numbering for next remark
                                $prevRemarkCount = 0;
                                foreach($case->remarksHistory->sortBy('crr_dtg') as $rem) {
                                    $raw = $rem->crr_remarks ?? '';
                                    if (strpos($raw, '<li') !== false) {
                                        $prevRemarkCount += max(1, substr_count($raw, '<li'));
                                    } elseif (!empty(trim(strip_tags($raw)))) {
                                        $lines = explode("\n", trim(strip_tags($raw)));
                                        $c = 0;
                                        foreach ($lines as $l) { if (!empty(trim($l))) $c++; }
                                        $prevRemarkCount += max(1, $c);
                                    }
                                }
                                $nextRemarkNumber = $prevRemarkCount + 1;
                                $isDivisionInitiation = ($role === 'Division' && ($case->ctc_status === 'Draft' || $currentStage === 'Division'));
                            @endphp

                            {{-- Decision Panel (Integrated as a Minute Entry / Action Box) --}}
                            @if($isDivisionInitiation || $isCaseWithMe)
                                <div class="mb-4 pb-3 border-bottom" style="border-bottom: 1px dashed #cbd5e1 !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="font-weight-bold rajdhani text-dark" style="font-size: 14px;">
                                            <i class="fas fa-user-circle text-primary mr-1"></i> {{ $u->acc_name }} 
                                            <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($role) }})</span>
                                            <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-accent, #5F7858); letter-spacing: 0.5px;">
                                                <i class="fas fa-pen-nib mr-1"></i> SCRUTINY & ACTION
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="text-dark small rajdhani font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                                                <i class="fas fa-pen-nib mr-1 text-primary"></i> REMARKS & SCRUTINY NOTES
                                            </span>
                                            <span class="text-muted font-italic" style="font-size: 10.5px;">
                                                <i class="fas fa-arrows-alt-v mr-0.5"></i> Drag corner to resize
                                            </span>
                                        </div>
                                        <textarea id="decisionRemarks" class="form-control" placeholder="Type your remarks or scrutiny observations here..." style="background: #ffffff; color: #0f172a; font-family: 'Arial', sans-serif; font-size: 13px; min-height: 110px; height: 110px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; outline: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); resize: vertical; width: 100%;"></textarea>
                                        
                                        {{-- User Quick Remarks (Custom shortcuts) --}}
                                        <div class="mt-2">
                                            @include('partials._user_quick_remarks', ['targetTextarea' => '#decisionRemarks'])
                                        </div>
                                    </div>

                                    {{-- Send / Forward To Destination Dropdown (Opens Downwards with Real-time Search) --}}
                                    <div class="form-group mb-3 position-relative" id="ccDestDropdownContainer">
                                        <label class="font-weight-bold text-muted small mb-1 d-flex justify-content-between" style="font-size: 10px; text-transform: uppercase;">
                                            <span><i class="fas fa-paper-plane text-primary mr-1"></i> Send / Forward To Destination: <span class="text-danger">*</span></span>
                                            <span class="text-muted font-italic" style="font-size: 9px; text-transform: none;">Search department, division, or director</span>
                                        </label>
                                        
                                        <input type="hidden" name="target_destination" id="ccTargetDestinationInput" value="">

                                        {{-- Display Toggle Box --}}
                                        <div id="ccDestDropdownToggle" class="form-control form-control-sm d-flex align-items-center justify-content-between" style="font-size: 12px; font-weight: 600; border-radius: 6px; border-color: #cbd5e1; height: 38px; cursor: pointer; background: #ffffff; user-select: none;">
                                            <span id="ccDestSelectedLabel" class="text-muted text-truncate font-weight-normal">
                                                <i class="fas fa-search mr-1.5 text-muted"></i> -- Select Destination Department / Authority --
                                            </span>
                                            <i class="fas fa-chevron-down text-muted ml-2" id="ccDestDropdownChevron" style="font-size: 11px; transition: transform 0.2s;"></i>
                                        </div>

                                        {{-- Downward Dropdown Menu --}}
                                        <div id="ccDestDropdownMenu" class="shadow-lg border bg-white" style="display: none; position: absolute; top: 100% !important; bottom: auto !important; left: 0; right: 0; z-index: 1050; margin-top: 3px; border-radius: 8px; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;">
                                            {{-- Sticky Search Input --}}
                                            <div class="p-2 border-bottom bg-light">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                                    </div>
                                                    <input type="text" id="ccDestSearchInput" class="form-control border-left-0" placeholder="Type department, division, or director name..." style="font-size: 12px; border-color: #cbd5e1;" autocomplete="off">
                                                </div>
                                            </div>

                                            {{-- Scrollable Destination Items --}}
                                            <div id="ccDestItemsList" style="max-height: 230px; overflow-y: auto; padding: 4px 0;">
                                                @foreach($destinations as $destCode => $dest)
                                                    @php
                                                        $searchKeywords = strtolower($dest['name'] . ' ' . ($dest['director'] ?? '') . ' ' . ($dest['desig'] ?? '') . ' ' . $destCode);
                                                    @endphp
                                                    <div class="cc-dest-option-item px-3 py-2" data-code="{{ $destCode }}" data-name="{{ $dest['name'] }}" data-search="{{ $searchKeywords }}" style="cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="text-dark font-weight-bold" style="font-size: 12.5px;">{{ $dest['name'] }}</span>
                                                            <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $dest['badge'] ?? $destCode }}</span>
                                                        </div>
                                                        @if(!empty($dest['director']))
                                                            <div class="text-muted text-truncate" style="font-size: 11px; margin-top: 1px;">
                                                                <i class="fas fa-user-tie text-secondary mr-1" style="font-size: 9.5px;"></i> {{ $dest['director'] }}
                                                                @if(!empty($dest['desig']))
                                                                    <span class="text-muted font-weight-normal">&bull; {{ $dest['desig'] }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                <div id="ccDestNoResults" class="p-3 text-center text-muted small" style="display: none;">
                                                    <i class="fas fa-info-circle mr-1"></i> No matching department, division, or director found.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action Buttons Row: Left = SEND CASE (Prominent), Right = Compact Approve & Compact Cancel (Expanding on hover, ONLY for Approving Authority) --}}
                                    <div class="d-flex align-items-center" style="gap: 8px; width: 100%;">
                                        @if($role === 'HR' && $case->ctc_status === 'Approved' && $case->ctc_status !== 'Fulfilled')
                                            <button type="button" onclick="handleAction('fulfill')" class="dg-btn-action dg-btn-success w-100 font-weight-bold" style="height: 40px; font-size: 13px; letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-check-double mr-1.5"></i> FULFILL CONTRACT CASE
                                            </button>
                                        @else
                                            {{-- Left: Prominent Send Case Button --}}
                                            <button type="button" onclick="handleAction('forward')" id="btnForward" class="btn-action-send flex-grow-1" style="height: 40px; font-size: 13.5px; letter-spacing: 0.6px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-paper-plane mr-2"></i>
                                                <span class="font-weight-bold rajdhani">SEND CASE</span>
                                            </button>

                                            @if($canApprove)
                                                {{-- Right: Compact Approve Button (Green Tick, expands on hover) --}}
                                                <button type="button" onclick="handleAction('approve')" id="btnApprove" class="btn-action-approve" title="Approve Contract Case">
                                                    <i class="fas fa-check"></i>
                                                    <span class="btn-expand-text rajdhani font-weight-bold">APPROVE CASE</span>
                                                </button>

                                                {{-- Right: Compact Cancel / Reject Button (Red Cross, expands on hover) --}}
                                                <button type="button" onclick="handleAction('cancel')" id="btnCancel" class="btn-action-cancel" title="Cancel / Reject Contract Case">
                                                    <i class="fas fa-times"></i>
                                                    <span class="btn-expand-text rajdhani font-weight-bold">REJECT CASE</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @elseif(!in_array($case->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']))
                                <div class="mb-4 p-3 border rounded shadow-sm" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-left: 4px solid #64748b !important; border-radius: 8px;">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 text-muted">
                                            <i class="fas fa-lock" style="font-size: 24px; color: #64748b;"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark rajdhani" style="font-size: 13.5px; letter-spacing: 0.5px;">
                                                CASE CURRENTLY LOCKED
                                            </div>
                                            <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                                This case is currently held by <strong>{{ $currentStage }}</strong> awaiting action. You cannot submit new decisions until the case is returned or forwarded back to your seat.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light border py-2 px-2.5 mb-3" style="font-size: 11px; background: #f8fafc; border-radius: 6px;">
                                    <i class="fas fa-info-circle text-primary mr-1"></i> Held by <strong>{{ $case->current_office_name }}</strong>. Action box is active only when case is in your queue.
                                </div>
                            @endif

                            {{-- Sequential Running Numbered Remarks Trail (1, 2, 3...) --}}
                            <div>
                                @php
                                    // Calculate global running sequential numbering for conversation trail (1, 2, 3...)
                                    $trailRunningNumberMap = [];
                                    $currSeq = 1;
                                    foreach($case->remarksHistory->sortBy('crr_dtg') as $rem) {
                                        $trailRunningNumberMap[$rem->crr_id] = $currSeq;
                                        $count = 1;
                                        if (!empty($rem->crr_remarks) && strpos($rem->crr_remarks, '<li') !== false) {
                                            $count = max(1, substr_count($rem->crr_remarks, '<li'));
                                        } elseif (!empty($rem->crr_remarks)) {
                                            $lines = explode("\n", trim(strip_tags($rem->crr_remarks)));
                                            $cleanedCount = 0;
                                            foreach ($lines as $l) {
                                                if (!empty(trim($l))) $cleanedCount++;
                                            }
                                            $count = max(1, $cleanedCount);
                                        }
                                        $currSeq += $count;
                                    }
                                @endphp

                                @forelse($case->remarksHistory->sortByDesc('crr_dtg') as $rem)
                                    @php
                                        $startNumber = $trailRunningNumberMap[$rem->crr_id] ?? 1;
                                        $rawRemarks = $rem->crr_remarks;
                                        $hasHtmlLi = !empty($rawRemarks) && strpos($rawRemarks, '<li') !== false;
                                        $hasRemarks = !empty(trim(strip_tags($rawRemarks)));

                                        $color = 'primary';
                                        $statusUpper = strtoupper($rem->crr_status);
                                        if (str_contains($statusUpper, 'APPROV') || str_contains($statusUpper, 'FULFILL')) {
                                            $color = 'success';
                                        } elseif (str_contains($statusUpper, 'RETURN') || str_contains($statusUpper, 'REVIS')) {
                                            $color = 'warning';
                                        } elseif (str_contains($statusUpper, 'REJECT') || str_contains($statusUpper, 'NOT APPROVED') || str_contains($statusUpper, 'CANCEL')) {
                                            $color = 'danger';
                                        }

                                        if ($hasHtmlLi) {
                                            $innerLis = preg_replace('/<\/?(ol|ul)[^>]*>/i', '', $rawRemarks);
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;">' . $innerLis . '</ol>';
                                        } elseif ($hasRemarks) {
                                            $lines = explode("\n", trim(strip_tags($rawRemarks)));
                                            $cleanLis = [];
                                            foreach ($lines as $line) {
                                                $cleanText = trim(preg_replace('/^\d+\.\s*/', '', $line));
                                                if (!empty($cleanText)) {
                                                    $cleanLis[] = '<li>' . e($cleanText) . '</li>';
                                                }
                                            }
                                            if (empty($cleanLis)) {
                                                $cleanLis[] = '<li>' . e(trim(strip_tags($rawRemarks))) . '</li>';
                                            }
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;">' . implode('', $cleanLis) . '</ol>';
                                        } else {
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;"><li>Case moved to ' . e($rem->crr_status) . ' without additional remarks.</li></ol>';
                                        }
                                    @endphp
                                    <div class="mb-4 pb-2" id="user-comment-{{ $rem->crr_id }}">
                                        <div class="d-flex align-items-center justify-content-between mb-1 border-bottom pb-1" style="border-bottom: 1px dashed #cbd5e1 !important;">
                                            <div class="font-weight-bold rajdhani text-dark" style="font-size: 14px; color: #0f172a !important;">
                                                <i class="fas fa-user-circle text-primary mr-1"></i> {{ $rem->crr_username }} 
                                                @if(!empty($rem->crr_user_desig))
                                                    <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($rem->crr_user_desig) }})</span>
                                                @endif
                                                <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 11px; color: var(--rd-{{$color}}); letter-spacing: 0.5px;">
                                                    <i class="fas fa-caret-right mr-1"></i>{{ strtoupper($rem->crr_status) }}
                                                </span>
                                            </div>
                                            <span class="text-muted" style="font-size:10px; font-weight: 600;">
                                                {{ \Carbon\Carbon::parse($rem->crr_dtg)->format('d M, h:i A') }}
                                            </span>
                                        </div>
                                        <div class="mt-2" style="line-height: 1.5; font-size:13px; color: #1e293b !important; padding-left: 5px;">
                                            {!! $trailHtml !!}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted small py-3">No remarks yet.</div>
                                @endforelse
                            </div>

                        </div>
                    </div>

                    {{-- 2. Recent Contract Cases (Matches Purchase Cases Hub) --}}
                    <div class="dg-panel-r">
                        <div class="dg-panel-r-hdr py-2 px-3">
                            <span class="dg-panel-r-title" style="font-size: 12px; color: #0f172a !important;">
                                <i class="fas fa-list-alt text-success mr-1"></i> RECENT HIRING CASES
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="spec-data-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>CANDIDATE</th>
                                        <th class="text-right">SALARY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCases as $rIdx => $rc)
                                        <tr>
                                            <td class="font-weight-bold text-muted">{{ $rIdx + 1 }}</td>
                                            <td>
                                                <a href="{{ route("{$routePrefix}.contract-cases.show", $rc->ctc_id) }}" class="text-dark font-weight-bold text-truncate d-block" style="max-width: 140px; font-size: 11px;" title="{{ $rc->ctc_empnamecomp }}">
                                                    {{ $rc->ctc_empnamecomp }}
                                                </a>
                                                <small class="text-muted d-block" style="font-size: 9.5px;">{{ $rc->division_short ?: $rc->division_name }}</small>
                                            </td>
                                            <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 11.5px;">
                                                Rs. {{ number_format($rc->ctc_newsalary) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-2 text-muted" style="font-size: 10px;">No other cases.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

{{-- MODAL: ADD CASE ATTACHMENT --}}
<div class="modal fade" id="modalAddContractCaseAttachment" tabindex="-1" role="dialog" aria-labelledby="modalAddContractCaseAttachmentLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header py-2.5 px-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h6 class="modal-title font-weight-bold rajdhani text-dark mb-0" id="modalAddContractCaseAttachmentLabel" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-file-upload text-success mr-1.5" style="color: var(--rd-accent, #5F7858) !important;"></i> ATTACH DOCUMENT TO CASE
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddContractCaseAttachment" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="form-group mb-2.5">
                        <label class="font-weight-bold text-dark small mb-1" style="font-size: 11px;">DOCUMENT TITLE / NAME <span class="text-danger">*</span></label>
                        <input type="text" name="doc_title" id="ctcAttDocTitle" class="form-control" placeholder="e.g., Justification Note, CNIC Copy, Degree" required style="font-size: 12px; border-radius: 6px; border-color: #cbd5e1;">
                    </div>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-dark small mb-1" style="font-size: 11px;">SELECT FILE <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="ctcAttFile" class="form-control-file border p-1.5 rounded w-100" required style="font-size: 11.5px; border-color: #cbd5e1 !important; background: #fafafa; border-radius: 6px;">
                        <small class="text-muted d-block mt-1" style="font-size: 10px;"><i class="fas fa-info-circle mr-1"></i> PDF, DOCX, XLSX, PNG, JPG (Max: 20MB)</small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" data-dismiss="modal" style="font-size: 11.5px; border-radius: 6px;">Cancel</button>
                    <button type="submit" id="btnUploadCtcAttachment" class="btn btn-sm font-weight-bold rajdhani px-3 text-white" style="font-size: 12px; border-radius: 6px; background-color: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;">
                        <i class="fas fa-upload mr-1"></i> UPLOAD ATTACHMENT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Financial Intelligence Report Modals for Allocated Projects --}}
@foreach($projectModalsData as $mKey => $mData)
@php
    $mHead = $mData['head'] ?? null;
    $mSubheads = $mData['subheads'] ?? [];
    $mCard = $mData['pCard'] ?? [];
    $mPrjId = $mCard['prj_id'] ?? null;
    $mHedId = $mCard['hed_id'] ?? null;
@endphp
<div class="modal fade" id="financialIntelligenceModal_{{ $mKey }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95%; width: 1420px;">
        <div class="modal-content shadow-2xl" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.18);">
            <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="font-size: 28px; color: var(--rd-accent, #5F7858);"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h4 class="modal-title rajdhani font-weight-bold text-dark mb-0" style="letter-spacing: 1.5px; font-size: 19px; font-weight: 800;">FINANCIAL INTELLIGENCE REPORT</h4>
                        <div class="text-muted rajdhani font-weight-bold mt-0.5" style="font-size: 13px;">{{ $mHead->head_name ?? ($mHead->hed_name ?? ($mCard['prj_code'] ?? 'N/A')) }} | DATED {{ date('d M Y') }} <span class="ml-2 font-weight-bold text-primary">{{ ($mHead->trans_type ?? 1) == 1 ? '(Million PKR without GST)' : '(PKR with GST)' }}</span></div>
                    </div>
                    <div class="ml-auto d-flex align-items-center mr-4" style="gap: 8px;">
                        @if(!empty($mPrjId))
                        <a href="{{ route('projects.show', $mPrjId) }}" target="_blank" class="btn btn-sm rajdhani font-weight-bold d-inline-flex align-items-center shadow-sm" style="font-size: 12px; border-radius: 6px; gap: 6px; padding: 6px 14px; letter-spacing: 0.5px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border: none;">
                            <i class="fas fa-project-diagram"></i> Project Details
                        </a>
                        @endif
                        @if(!empty($mHedId))
                        <a href="{{ route('projects.financial_view', $mHedId) }}#tab-docs" target="_blank" class="btn btn-sm rajdhani font-weight-bold d-inline-flex align-items-center shadow-sm" style="font-size: 12px; border-radius: 6px; gap: 6px; padding: 6px 14px; letter-spacing: 0.5px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; border: none;">
                            <i class="fas fa-paperclip"></i> Files & Attachments
                        </a>
                        <a href="{{ route('projects.financial_view', $mHedId) }}#tab-milestones" target="_blank" class="btn btn-sm rajdhani font-weight-bold d-inline-flex align-items-center shadow-sm" style="font-size: 12px; border-radius: 6px; gap: 6px; padding: 6px 14px; letter-spacing: 0.5px; background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #fff; border: none;">
                            <i class="fas fa-coins"></i> Milestone Costs
                        </a>
                        @endif
                    </div>
                </div>
                <button type="button" class="close text-dark opacity-60 hover-opacity-100" data-dismiss="modal" style="font-size: 26px;">&times;</button>
            </div>
            
            <div class="modal-body p-0" style="background: #ffffff;">
                {{-- Top Summary bar --}}
                <div class="row no-gutters border-bottom" style="background: #f1f5f9; border-color: #e2e8f0 !important;">
                    <div class="col-md-3 border-right p-3.5" style="border-color: #cbd5e1 !important;">
                        <div class="rajdhani font-weight-bold" style="font-size: 13px; color: #475569; letter-spacing: 0.8px;">ALLOCATION</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="color: #0f172a; font-weight: 900; font-size: 22px;">{{ number_format($mHead->allocation ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 border-right p-3.5" style="border-color: #cbd5e1 !important;">
                        <div class="rajdhani font-weight-bold" style="font-size: 13px; color: #475569; letter-spacing: 0.8px;">MTSS SHARE</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="color: #0f172a; font-weight: 900; font-size: 22px;">{{ number_format($mHead->mtss_share ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 border-right p-3.5" style="border-color: #cbd5e1 !important;">
                        <div class="rajdhani font-weight-bold" style="font-size: 13px; color: #1d4ed8; letter-spacing: 0.8px;">RDW SHARE</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani text-primary" style="font-weight: 900; font-size: 22px;">{{ number_format($mHead->rdw_share ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 p-3.5">
                        <div class="rajdhani font-weight-bold" style="font-size: 13px; color: #475569; letter-spacing: 0.8px;">CSRF SHARE</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="color: #0f172a; font-weight: 900; font-size: 22px;">{{ number_format($mHead->csrf_share ?? 0) }}</div>
                    </div>
                </div>

                <div class="row no-gutters">
                    {{-- Left Pane: Detailed Metrics Table --}}
                    <div class="col-xl-4 col-lg-5 border-right p-4" style="background: #fbfcfe; border-color: #e2e8f0 !important;">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h5 class="rajdhani text-primary font-weight-bold mb-0" style="letter-spacing: 1px; font-size: 16px; font-weight: 800;"><i class="fas fa-table mr-2"></i>PROJECT SNAPSHOT</h5>
                            <div class="rajdhani font-weight-bold text-muted" style="font-size: 12px; letter-spacing: 0.5px;">FIGURES IN PKR</div>
                        </div>

                        <div class="fin-table-modern table-responsive rounded border overflow-auto shadow-sm" style="border-color: #cbd5e1 !important; background: #ffffff;">
                            <table class="table table-sm mb-0 rajdhani" style="font-size: 14.5px; font-weight: 700;">
                                <thead style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                    <tr style="color: #334155; font-size: 13.5px; font-weight: 800;">
                                        <th class="pl-3 py-2 border-0">METRIC</th>
                                        <th class="text-right py-2 border-0" style="color: #1d4ed8;">PROJECT</th>
                                        <th class="text-right py-2 border-0" style="color: #b45309;">CSRF</th>
                                        <th class="text-right pr-3 py-2 border-0" style="color: #15803d;">ACTUAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background: rgba(37,99,235,0.05); border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-dark"><i class="fas fa-coins text-warning mr-1"></i> Allocated</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #1d4ed8; font-size: 15px;">{{ number_format($mHead->pcc_share ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #b45309; font-size: 15px;">{{ number_format($mHead->csrf_share ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 15px;">{{ number_format($mHead->allocation ?? 0) }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-dark font-weight-bold">Received</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #1d4ed8; font-size: 15px;">{{ number_format($mHead->pcc_received ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #b45309; font-size: 15px;">{{ number_format($mHead->cf_received ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-danger font-weight-bold">Expenditure</td>
                                        <td class="text-right py-2 text-danger font-weight-bold" style="font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'pcc', 'expenditure']) }}" target="_blank" class="text-danger text-decoration-none font-weight-bold" title="View Project Expenditure Breakdown">
                                                {{ number_format($mHead->pcc_expenditure ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'pcc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" title="View Project Expenditure Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right py-2 text-danger font-weight-bold" style="font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'csrf', 'expenditure']) }}" target="_blank" class="text-danger text-decoration-none font-weight-bold" title="View CSRF Expenditure Breakdown">
                                                {{ number_format($mHead->cf_expenditure ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'csrf', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" title="View CSRF Expenditure Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'acc', 'expenditure']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #15803d;" title="View Total Expenditure Breakdown">
                                                {{ number_format($mHead->prj_expenditure ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'acc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-green" title="View Total Expenditure Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="background: rgba(37,99,235,0.05); border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-primary font-weight-bold">Balance</td>
                                        <td class="text-right py-2 text-primary font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->pcc_balance ?? 0) }}</td>
                                        <td class="text-right py-2 text-primary font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->cf_balance ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-dark font-weight-bold">Commitments</td>
                                        <td class="text-right py-2 text-warning font-weight-bold" style="color: #b45309 !important; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'pcc', 'commitments']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #b45309;" title="View Project Commitments Breakdown">
                                                {{ number_format($mHead->pcc_commitments ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'pcc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="View Project Commitments Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right py-2 text-warning font-weight-bold" style="color: #b45309 !important; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'csrf', 'commitments']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #b45309;" title="View CSRF Commitments Breakdown">
                                                {{ number_format($mHead->cf_commitments ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'csrf', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="View CSRF Commitments Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'acc', 'commitments']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #15803d;" title="View Total Commitments Breakdown">
                                                {{ number_format($mHead->prj_commitments ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'acc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-green" title="View Total Commitments Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-dark font-weight-bold">In Process</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #475569; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'pcc', 'in-process']) }}" target="_blank" class="text-muted text-decoration-none font-weight-bold" title="View Project In-Process Cases">
                                                {{ number_format($mHead->pcc_in_process ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'pcc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="View Project In-Process Cases">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #475569; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'csrf', 'in-process']) }}" target="_blank" class="text-muted text-decoration-none font-weight-bold" title="View CSRF In-Process Cases">
                                                {{ number_format($mHead->cf_in_process ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'csrf', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="View CSRF In-Process Cases">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 15px;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'acc', 'in-process']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #15803d;" title="View Total In-Process Cases">
                                                {{ number_format($mHead->prj_in_process ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'acc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-green" title="View Total In-Process Cases">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="background: rgba(22,163,74,0.07); border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-success" style="font-size: 15px;">Available</td>
                                        <td class="text-right py-2 font-weight-bold text-success" style="font-size: 16px;">{{ number_format($mHead->pcc_available ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold text-success" style="font-size: 16px;">{{ number_format($mHead->cf_available ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-muted font-weight-bold">Yet to be Rec</td>
                                        <td class="text-right py-2 text-dark font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->pcc_yet_to_be_received ?? 0) }}</td>
                                        <td class="text-right py-2 text-dark font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->cf_yet_to_be_received ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="background: rgba(220,38,38,0.07);">
                                        <td class="pl-3 py-2 text-danger font-weight-bold" style="font-size: 15px;">Remaining</td>
                                        <td class="text-right py-2 text-danger font-weight-bold" style="font-size: 16px;">{{ number_format($mHead->pcc_can_be_spent ?? 0) }}</td>
                                        <td class="text-right py-2 text-danger font-weight-bold" style="font-size: 16px;">{{ number_format($mHead->cf_can_be_spent ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 16px;">{{ number_format($mHead->prj_remaining ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Receivables section --}}
                        <div class="mt-4 pt-3 border-top" style="border-color: #cbd5e1 !important;">
                            <h6 class="rajdhani font-weight-bold mb-3" style="font-size: 13.5px; letter-spacing: 1px; color: #475569;">RECEIVABLES</h6>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="font-weight-bold rajdhani" style="font-size: 13.5px; color: #475569;">Comp. Milestones</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->receivable_completed ?? 0) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="font-weight-bold rajdhani" style="font-size: 13.5px; color: #475569;">Current Milestone</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->receivable_current ?? 0) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mt-3 p-2.5 rounded shadow-sm" style="background: rgba(37,99,235,0.08); border: 1.5px solid rgba(37,99,235,0.3);">
                                <span class="text-primary rajdhani font-weight-bold" style="font-size: 14px;">Available after Rcv.</span>
                                <span class="text-primary rajdhani font-weight-bold" style="font-size: 17px; font-weight: 900;">{{ number_format($mHead->available_after_receivables ?? 0) }}</span>
                            </div>
                        </div>

                        {{-- Exp Sources --}}
                        <div class="mt-4 pt-3 border-top" style="border-color: #cbd5e1 !important;">
                            <h6 class="rajdhani font-weight-bold mb-3" style="font-size: 13.5px; letter-spacing: 1px; color: #475569;">EXP. SOURCES</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="rajdhani font-weight-bold" style="font-size: 13.5px; color: #475569;">From this account</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->exp_this_account ?? 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="rajdhani font-weight-bold" style="font-size: 13.5px; color: #475569;">From other accounts</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->exp_other_accounts ?? 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="rajdhani font-weight-bold" style="font-size: 13.5px; color: #475569;">Other's exp. this acc.</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 15px;">{{ number_format($mHead->others_exp_this_account ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Pane: Full Subheads Breakdown (With Live Drilldown) --}}
                    <div class="col-xl-8 col-lg-7 p-4" style="background: #ffffff;">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                            <div>
                                <h5 class="rajdhani text-primary font-weight-bold mb-0" style="letter-spacing: 1px; font-size: 17.5px; font-weight: 800;">
                                    <i class="fas fa-layer-group mr-2"></i> SUBHEAD FINANCIAL BREAKDOWN
                                </h5>
                                <div class="text-muted rajdhani font-weight-bold mt-1" style="font-size: 12.5px;">DETAILED ALLOCATION, EXPENDITURE, COMMITMENTS, IN PROCESS & REMAINING</div>
                            </div>
                            <span class="badge badge-primary px-3 py-1.5 rajdhani font-weight-bold" style="font-size: 12.5px; background: rgba(37,99,235,0.12); color: #1d4ed8; border: 1.5px solid rgba(37,99,235,0.3); border-radius: 6px;">
                                {{ count($mSubheads ?? []) }} SUBHEADS
                            </span>
                        </div>

                        <div class="table-responsive rounded border shadow-sm" style="border-color: #cbd5e1 !important;">
                            <table class="table table-sm table-hover mb-0 rajdhani" style="font-size: 14px; background: #ffffff;">
                                <thead style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                    <tr style="color: #0f172a; font-size: 13.5px; font-weight: 800;">
                                        <th class="pl-3 py-2.5" style="white-space: nowrap;">SUBHEAD</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">ALLOCATED</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">EXPENDITURE</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">COMMITMENTS</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">IN PROCESS</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">REMAINING</th>
                                        <th class="text-center pr-3 py-2.5" style="width: 100px; white-space: nowrap;">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totAlloc = 0;
                                        $totExp = 0;
                                        $totCmt = 0;
                                        $totIpc = 0;
                                        $totRem = 0;
                                    @endphp
                                    @forelse($mSubheads ?? [] as $sh)
                                    @php
                                        $sName = is_array($sh) ? ($sh['name'] ?? '') : ($sh->name ?? '');
                                        $sAlloc = (float)(is_array($sh) ? ($sh['allocation'] ?? 0) : ($sh->allocation ?? 0));
                                        $sExp = (float)(is_array($sh) ? ($sh['expenditure'] ?? 0) : ($sh->expenditure ?? 0));
                                        $sCmt = (float)(is_array($sh) ? ($sh['commitments'] ?? 0) : ($sh->commitments ?? 0));
                                        $sIpc = (float)(is_array($sh) ? ($sh['in_process'] ?? 0) : ($sh->in_process ?? 0));
                                        $sRem = (float)(is_array($sh) ? ($sh['remaining'] ?? ($sh['can_be_spent'] ?? 0)) : ($sh->remaining ?? ($sh->can_be_spent ?? 0)));

                                        $totAlloc += $sAlloc;
                                        $totExp += $sExp;
                                        $totCmt += $sCmt;
                                        $totIpc += $sIpc;
                                        $totRem += $sRem;

                                        $isCaseSubhead = strcasecmp(trim($sName), 'HR') === 0;
                                    @endphp
                                    <tr style="{{ $isCaseSubhead ? 'background: #fef9c3 !important; border-left: 5px solid #eab308 !important;' : '' }}; border-bottom: 1px solid #f1f5f9;">
                                        <td class="pl-3 py-2.5 font-weight-bold text-dark align-middle" style="white-space: nowrap; font-size: 14.5px;">
                                            <i class="fas fa-folder-open text-primary mr-1.5"></i> {{ $sName }}
                                            @if($isCaseSubhead)
                                                <span class="badge badge-warning text-dark ml-2 px-2 py-0.5 rajdhani font-weight-bold" style="font-size: 11px; background: #facc15; color: #713f12 !important; border: 1px solid #eab308; border-radius: 4px;">
                                                    <i class="fas fa-check-circle mr-1"></i> ACTIVE HIRING SUBHEAD
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-right py-2.5 font-weight-bold align-middle" style="color: #0f172a; white-space: nowrap; font-size: 15px;">
                                            {{ number_format($sAlloc) }}
                                        </td>
                                        <td class="text-right py-2.5 font-weight-bold text-danger align-middle" style="white-space: nowrap; font-size: 15px;">
                                            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 5px; white-space: nowrap;">
                                                <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'expenditure', $sName]) }}" target="_blank" class="text-danger text-decoration-none" title="Drilldown {{ $sName }} Expenditure">
                                                    {{ number_format($sExp) }}
                                                </a>
                                                <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'expenditure', $sName]) }}" target="_blank" class="btn-drill-link btn-drill-red" title="Drilldown {{ $sName }} Expenditure">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right py-2.5 font-weight-bold align-middle" style="color: #b45309; white-space: nowrap; font-size: 15px;">
                                            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 5px; white-space: nowrap;">
                                                <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'commitments', $sName]) }}" target="_blank" class="text-decoration-none" style="color: #b45309;" title="Drilldown {{ $sName }} Commitments">
                                                    {{ number_format($sCmt) }}
                                                </a>
                                                <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'commitments', $sName]) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Drilldown {{ $sName }} Commitments">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right py-2.5 font-weight-bold align-middle" style="color: #475569; white-space: nowrap; font-size: 15px;">
                                            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 5px; white-space: nowrap;">
                                                <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'in-process', $sName]) }}" target="_blank" class="text-decoration-none" style="color: #475569;" title="Drilldown {{ $sName }} In-Process">
                                                    {{ number_format($sIpc) }}
                                                </a>
                                                <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'in-process', $sName]) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="Drilldown {{ $sName }} In-Process">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right py-2.5 font-weight-bold align-middle {{ $sRem < 0 ? 'text-danger' : 'text-success' }}" style="white-space: nowrap; font-size: 15px;">
                                            {{ number_format($sRem) }}
                                        </td>
                                        <td class="text-center pr-3 py-2.5 align-middle" style="white-space: nowrap;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$mHedId, 'subhead', 'expenditure', $sName]) }}" target="_blank" class="btn btn-xs btn-outline-primary rajdhani font-weight-bold py-1 px-2.5 shadow-sm" style="font-size: 12px; border-radius: 4px; border-width: 1.5px;" title="View Full {{ $sName }} Breakdown">
                                                <i class="fas fa-external-link-alt mr-1"></i> VIEW
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted font-weight-bold" style="font-size: 14px;">No subheads available.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if(count($mSubheads ?? []) > 0)
                                <tfoot style="background: rgba(37,99,235,0.08); font-weight: 900; border-top: 2.5px solid #cbd5e1; font-size: 15.5px;">
                                    <tr>
                                        <td class="pl-3 py-2.5 font-weight-bold text-dark" style="white-space: nowrap;">TOTAL</td>
                                        <td class="text-right py-2.5 font-weight-bold" style="color: #0f172a; white-space: nowrap;">{{ number_format($totAlloc) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold text-danger" style="white-space: nowrap;">{{ number_format($totExp) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold" style="color: #b45309; white-space: nowrap;">{{ number_format($totCmt) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold" style="color: #475569; white-space: nowrap;">{{ number_format($totIpc) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold {{ $totRem < 0 ? 'text-danger' : 'text-success' }}" style="white-space: nowrap;">{{ number_format($totRem) }}</td>
                                        <td class="text-center pr-3 py-2.5 text-muted" style="white-space: nowrap;">--</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                <div class="rajdhani font-weight-bold" style="font-size: 13px; color: #475569;"><i class="fas fa-shield-alt text-success mr-1.5"></i> RDWIS FINANCIAL AUDIT ENGINE ACTIVE</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold px-4" data-dismiss="modal" style="font-size: 12.5px; border-radius: 6px;">CLOSE REPORT</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
$(document).ready(function() {
    const inlineRemarks = document.getElementById('decisionRemarks');
    const btnReturn = document.getElementById('btnReturn');
    const btnForward = document.getElementById('btnForward');

    function getNextLocalNumber() {
        if (!inlineRemarks) return 2;
        const matches = inlineRemarks.value.match(/^\d+(?=\.)/gm);
        if (matches && matches.length > 0) {
            return Math.max(...matches.map(Number)) + 1;
        }
        return 2;
    }

    if (inlineRemarks) {
        inlineRemarks.addEventListener('focus', function() {
            if (this.value.trim() === '') {
                this.value = "1. ";
                updateButtonState();
            }
        });

        inlineRemarks.addEventListener('keydown', function(e) {
            const selectionStart = this.selectionStart;
            const text = this.value;
            const lastNewline = text.lastIndexOf('\n', selectionStart - 1);
            const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
            const currentLine = text.substring(lineStart, selectionStart);
            const match = currentLine.match(/^\d+\. /);

            if (match && selectionStart < lineStart + match[0].length) {
                if (e.key === 'Backspace' || e.key === 'Delete' || (e.key.length === 1 && e.key !== 'Enter')) {
                    e.preventDefault();
                    return;
                }
            }
            
            if (e.key === 'Enter') {
                e.preventDefault();
                if (currentLine.trim().length > (match ? match[0].trim().length : 0)) {
                    const nextNumLocal = getNextLocalNumber();
                    const newNumber = "\n" + nextNumLocal + ". ";
                    const before = text.substring(0, selectionStart);
                    const after = text.substring(selectionStart);
                    this.value = before + newNumber + after;
                    this.selectionStart = this.selectionEnd = before.length + newNumber.length;
                    updateButtonState();
                }
            }
            
            if (e.key === 'Backspace' && match && selectionStart === lineStart + match[0].length) {
                e.preventDefault();
            }
        });

        inlineRemarks.addEventListener('input', function() {
            const prefix = "1. ";
            if (!this.value.startsWith(prefix) && this.value.trim() !== '') {
                const currentVal = this.value;
                if (currentVal.length < prefix.length) {
                    this.value = prefix;
                } else {
                    this.value = prefix + currentVal.replace(/^\d+\.?\s*/, '');
                }
                this.selectionStart = this.selectionEnd = prefix.length;
            }
            updateButtonState();
        });
    }

    function updateButtonState() {
        if (!inlineRemarks) return;
        const currentVal = inlineRemarks.value.trim();
        const prefix = "1. ";
        const hasContent = currentVal.length > 0 && currentVal !== prefix.trim() && currentVal !== "1.";
        if (btnReturn) btnReturn.disabled = !hasContent;
    }

    // Initialize button state
    updateButtonState();

    // Downward Searchable Dropdown Logic for Contract Cases
    const ccDestContainer = document.getElementById('ccDestDropdownContainer');
    const ccDestToggle = document.getElementById('ccDestDropdownToggle');
    const ccDestMenu = document.getElementById('ccDestDropdownMenu');
    const ccDestSearch = document.getElementById('ccDestSearchInput');
    const ccDestHiddenInput = document.getElementById('ccTargetDestinationInput');
    const ccDestLabel = document.getElementById('ccDestSelectedLabel');
    const ccDestChevron = document.getElementById('ccDestDropdownChevron');
    const ccDestItems = document.querySelectorAll('.cc-dest-option-item');
    const ccDestNoResults = document.getElementById('ccDestNoResults');

    window.openCcDestDropdown = function() {
        if (!ccDestMenu) return;
        ccDestMenu.style.display = 'block';
        if (ccDestChevron) ccDestChevron.style.transform = 'rotate(180deg)';
        if (ccDestSearch) {
            ccDestSearch.value = '';
            filterCcDestItems('');
            setTimeout(() => ccDestSearch.focus(), 50);
        }
    };

    window.closeCcDestDropdown = function() {
        if (!ccDestMenu) return;
        ccDestMenu.style.display = 'none';
        if (ccDestChevron) ccDestChevron.style.transform = 'rotate(0deg)';
    };

    function filterCcDestItems(q) {
        let matchCount = 0;
        const query = (q || '').toLowerCase().trim();
        ccDestItems.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (query === '' || searchData.includes(query)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });
        if (ccDestNoResults) {
            ccDestNoResults.style.display = matchCount === 0 ? 'block' : 'none';
        }
    }

    if (ccDestToggle) {
        ccDestToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (ccDestMenu.style.display === 'block') {
                closeCcDestDropdown();
            } else {
                openCcDestDropdown();
            }
        });
    }

    if (ccDestSearch) {
        ccDestSearch.addEventListener('input', function() {
            filterCcDestItems(this.value);
        });
        ccDestSearch.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    ccDestItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            if (ccDestHiddenInput) ccDestHiddenInput.value = code;
            if (ccDestLabel) {
                ccDestLabel.innerHTML = `<span class="text-dark font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> ${name}</span>`;
            }
            ccDestItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            closeCcDestDropdown();
        });
    });

    document.addEventListener('click', function(e) {
        if (ccDestContainer && !ccDestContainer.contains(e.target)) {
            closeCcDestDropdown();
        }
    });

    // Upload Attachment Form Submission
    $('#formAddCaseAttachment').on('submit', function(e) {
        e.preventDefault();

        const title = $('#attDocTitle').val().trim();
        const fileInput = $('#attFile')[0];

        if (!title) {
            Swal.fire({ icon: 'warning', title: 'Document Title Required', text: 'Please enter a name for this document.', confirmButtonColor: '#5F7858' });
            return;
        }
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire({ icon: 'warning', title: 'File Required', text: 'Please select a file to upload.', confirmButtonColor: '#5F7858' });
            return;
        }

        const formData = new FormData(this);
        const $btn = $('#btnUploadAttachment');
        const origHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Uploading...');

        $.ajax({
            url: "{{ route('contract-cases.attachments.store', $case->ctc_id) }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                $btn.prop('disabled', false).html(origHtml);
                $('#modalAddCaseAttachment').modal('hide');
                $('#formAddCaseAttachment')[0].reset();

                if (resp.success) {
                    $('#noCaseAttPlaceholder').remove();
                    const curCount = parseInt($('#caseAttachmentsCount').text() || '0');
                    const nextNum = curCount + 1;
                    $('#caseAttachmentsCount').text(nextNum);

                    const safeTitle = (resp.attachment.title || 'Attachment').replace(/'/g, "\\'");
                    const newRow = `
                        <div class="d-flex justify-content-between align-items-center py-1 border-top" style="border-color: #f1f5f9 !important;">
                            <a href="${resp.attachment.url}" onclick="window.openLiveDocument('${resp.attachment.url}', '${safeTitle}'); return false;" class="d-flex align-items-center overflow-hidden mr-1 text-decoration-none rd-live-file-view" style="flex: 1; min-width: 0; cursor: pointer;" title="View ${resp.attachment.title} Live">
                                <span class="text-muted font-weight-bold mr-1 flex-shrink-0" style="font-size: 9.5px; width: 14px;">${nextNum}.</span>
                                <span class="text-truncate font-weight-600 text-dark" style="font-size: 9.5px;">${resp.attachment.title}</span>
                            </a>
                            <a href="${resp.attachment.url}" onclick="window.openLiveDocument('${resp.attachment.url}', '${safeTitle}'); return false;" class="rd-live-file-view text-primary flex-shrink-0" title="View Document Live" style="cursor: pointer;"><i class="fas fa-eye"></i></a>
                        </div>
                    `;
                    $('#caseAttachmentsList').append(newRow);

                    Swal.fire({
                        icon: 'success',
                        title: 'Uploaded Successfully',
                        text: resp.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Upload Failed', text: resp.message || 'Could not upload attachment.', confirmButtonColor: '#5F7858' });
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                const err = xhr.responseJSON?.message || 'Failed to upload attachment.';
                Swal.fire({ icon: 'error', title: 'Upload Failed', text: err, confirmButtonColor: '#5F7858' });
            }
        });
    });
});

window.confirmReturn = function(targetStatus, targetName) {
    const inlineRemarks = document.getElementById('decisionRemarks');
    let remarks = inlineRemarks ? inlineRemarks.value.trim() : '';
    let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
    let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

    if (cleanedLines.length === 0) {
        Swal.fire({ title: 'Remarks Required!', text: 'Remarks are compulsory for returning a case.', icon: 'warning', confirmButtonColor: '#5F7858' });
        return;
    }

    Swal.fire({
        title: 'Confirm Return?',
        text: `Return this contract case to ${targetName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Return',
        confirmButtonColor: '#f59e0b',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            handleAction('return', targetStatus);
        }
    });
};

window.handleAction = function(actionType, targetStage = null) {
    let rawRemarks = $('#decisionRemarks').val() || '';
    let lines = rawRemarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
    let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

    // Strict validation: user must enter remarks for any action
    if (cleanedLines.length === 0) {
        Swal.fire({
            title: 'Remarks Required!',
            text: 'You must enter scrutiny remarks before performing this action.',
            icon: 'warning',
            confirmButtonColor: '#5F7858'
        });
        const rEl = document.getElementById('decisionRemarks');
        if (rEl) rEl.focus();
        return;
    }

    const ccDestHiddenInput = document.getElementById('ccTargetDestinationInput');
    const targetDest = (ccDestHiddenInput && ccDestHiddenInput.value) ? ccDestHiddenInput.value : targetStage;

    // Strict validation: user must select a destination when sending case
    if (actionType === 'forward' && !targetDest) {
        Swal.fire({
            title: 'Destination Required!',
            text: 'Please select a destination department or authority to send the case.',
            icon: 'warning',
            confirmButtonColor: '#5F7858'
        });
        if (typeof openCcDestDropdown === 'function') openCcDestDropdown();
        return;
    }

    const startNum = {{ $nextRemarkNumber ?? 1 }};
    let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
    let finalHtml = `<ol start="${startNum}">${liItems}</ol>`;

    const routePrefix = "{{ $routePrefix }}";
    const caseId = "{{ $case->ctc_id }}";
    const isDivision = "{{ $role }}" === 'Division';

    let url = "";
    let confirmTitle = "";
    let confirmText = "";
    let confirmBtnColor = "#16a34a";

    if (actionType === 'cancel') {
        url = isDivision ? `/division/contract-cases/${caseId}/cancel` : `/${routePrefix}/contract-cases/${caseId}/reject`;
        confirmTitle = 'Cancel / Reject Case?';
        confirmText = 'Are you sure you want to reject/cancel this contract case?';
        confirmBtnColor = '#dc2626';
    } else if (actionType === 'fulfill') {
        url = `/hr/contract-cases/${caseId}/fulfill`;
        confirmTitle = 'Fulfill Contract Case?';
        confirmText = 'This will mark the approved contract case as completed/fulfilled.';
        confirmBtnColor = '#16a34a';
    } else if (actionType === 'approve') {
        url = `/${routePrefix}/contract-cases/${caseId}/approve`;
        confirmTitle = 'Confirm Contract Approval?';
        confirmText = 'Are you sure you want to approve this contract case?';
        confirmBtnColor = '#16a34a';
    } else if (actionType === 'forward') {
        const selectedItem = document.querySelector(`.cc-dest-option-item[data-code="${targetDest}"]`);
        const destText = selectedItem ? selectedItem.getAttribute('data-name') : (targetDest || 'selected destination');
        if (isDivision && targetDest === 'HR') {
            url = `/division/contract-cases/${caseId}/release`;
        } else if (isDivision) {
            url = `/division/contract-cases/${caseId}/forward`;
        } else {
            url = `/${routePrefix}/contract-cases/${caseId}/forward`;
        }
        confirmTitle = 'Send Case?';
        confirmText = `Send this case to ${destText}?`;
        confirmBtnColor = '#16a34a';
    }

    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        confirmButtonColor: confirmBtnColor,
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing Decision...',
                text: 'Please wait while we update the case records.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    remarks: finalHtml,
                    target_stage: targetDest,
                    target_destination: targetDest
                },
                success: function(resp) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Decision Processed!',
                        text: resp.message || 'Case updated successfully.',
                        confirmButtonColor: '#5F7858'
                    }).then(() => {
                        window.location.href = `/${routePrefix}/contract-cases`;
                    });
                },
                error: function(xhr) {
                    const err = xhr.responseJSON?.message || 'An error occurred while processing the decision.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Action Failed',
                        text: err,
                        confirmButtonColor: '#5F7858'
                    });
                }
            });
        }
    });
};

// Interactive Allocated Project Switcher
const projectCardsMap = @json(collect($projectCards)->keyBy('card_key'));

window.selectProject = function(cardKey) {
    const data = projectCardsMap[cardKey];
    if (!data) return;

    // Update active badge visual highlight
    document.querySelectorAll('.project-badge-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-card-key') === String(cardKey)) {
            btn.classList.add('active');
        }
    });

    // Update dynamic vertical fields
    const prjTenureEl = document.getElementById('activeProjectTenure');
    if (prjTenureEl) prjTenureEl.textContent = data.tenure_display || data.period_formatted || '—';

    const prjHiredEl = document.getElementById('activeProjectHiredStaff');
    if (prjHiredEl) prjHiredEl.innerHTML = '<i class="fas fa-users text-primary mr-1"></i> ' + (data.hired_count || 0) + ' Staff';

    const subheadEl = document.getElementById('activeProjectSubhead');
    if (subheadEl) subheadEl.textContent = data.subhead || 'HR';

    const subheadDrill = document.getElementById('activeSubheadDrilldownLink');
    if (subheadDrill) subheadDrill.href = data.subhead_drilldown || '#';

    const prjDocs = document.getElementById('activeProjectDocsLink');
    if (prjDocs) prjDocs.href = data.attachments_url || '#';

    const prjMilestones = document.getElementById('activeProjectMilestonesLink');
    if (prjMilestones) prjMilestones.href = data.milestones_url || '#';

    const prjDetails = document.getElementById('activeProjectDetailsLink');
    if (prjDetails) {
        prjDetails.href = data.project_details_url || '#';
        prjDetails.style.display = data.prj_id ? 'inline-flex' : 'none';
    }

    // Update FULL REPORT button in FINANCIAL REVIEW card
    const fullReportBtn = document.getElementById('finReviewFullReportBtn');
    if (fullReportBtn) {
        if (data.hed_id) {
            fullReportBtn.setAttribute('data-target', '#financialIntelligenceModal_' + data.card_key);
            fullReportBtn.style.display = 'inline-block';
        } else {
            fullReportBtn.style.display = 'none';
        }
    }

    // Update FINANCIAL REVIEW values
    const allocEl = document.getElementById('finReviewAllocated');
    if (allocEl) allocEl.textContent = Number(data.allocation).toLocaleString('en-US');

    const recEl = document.getElementById('finReviewReceived');
    if (recEl) recEl.textContent = Number(data.received).toLocaleString('en-US');
    
    const expEl = document.getElementById('finReviewExpenditure');
    if (expEl) {
        expEl.textContent = Number(data.expenditure).toLocaleString('en-US');
        expEl.href = data.expenditure_drilldown;
    }
    const expLink = document.getElementById('finReviewExpDrillLink');
    if (expLink) expLink.href = data.expenditure_drilldown;

    const balEl = document.getElementById('finReviewBalance');
    if (balEl) balEl.textContent = Number(data.balance).toLocaleString('en-US');
    
    const cmtEl = document.getElementById('finReviewCommitments');
    if (cmtEl) {
        cmtEl.textContent = Number(data.commitments).toLocaleString('en-US');
        cmtEl.href = data.commitments_drilldown;
    }
    const cmtLink = document.getElementById('finReviewCmtDrillLink');
    if (cmtLink) cmtLink.href = data.commitments_drilldown;

    const inpEl = document.getElementById('finReviewInProcess');
    if (inpEl) {
        inpEl.textContent = Number(data.in_process).toLocaleString('en-US');
        inpEl.href = data.in_process_drilldown;
    }
    const inpLink = document.getElementById('finReviewInpDrillLink');
    if (inpLink) inpLink.href = data.in_process_drilldown;

    const availEl = document.getElementById('finReviewAvailable');
    if (availEl) availEl.textContent = Number(data.available).toLocaleString('en-US');

    const spentEl = document.getElementById('finReviewCanBeSpent');
    if (spentEl) spentEl.textContent = Number(data.can_be_spent).toLocaleString('en-US');
};

// Contract Case Attachment Upload Handler
$(document).on('submit', '#formAddContractCaseAttachment', async function(e) {
    e.preventDefault();
    const $btn = $('#btnUploadCtcAttachment');
    const origHtml = $btn.html();
    const fileInput = document.getElementById('ctcAttFile');
    if (!fileInput.files || !fileInput.files.length) {
        alert('Please select a file to upload.');
        return;
    }

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Uploading...');

    const fd = new FormData(this);

    try {
        const res = await fetch("{{ route('contract-cases.attachments.store', $case->ctc_id) }}", {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        $btn.prop('disabled', false).html(origHtml);

        if (data.success) {
            $('#modalAddContractCaseAttachment').modal('hide');
            this.reset();

            // Increment badge count
            const $badge = $('#ctcCaseAttCountBadge');
            const currentCount = parseInt($badge.text().trim(), 10) || 0;
            $badge.text(currentCount + 1);

            // Remove empty state placeholder if present
            $('#ctcNoAttachmentsMsg').remove();

            // Append new item to dropdown list
            const att = data.attachment;
            const viewUrl = "{{ url('/universal-attachment/ctc') }}/" + att.id + "/view";
            const ext = (att.filename.split('.').pop() || '').toLowerCase();
            let iconClass = 'far fa-file-alt text-secondary';
            if (ext === 'pdf') iconClass = 'far fa-file-pdf text-danger';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'far fa-file-word text-primary';
            else if (['xls', 'xlsx'].includes(ext)) iconClass = 'far fa-file-excel text-success';
            else if (['png', 'jpg', 'jpeg'].includes(ext)) iconClass = 'far fa-file-image text-info';

            const newIndex = currentCount + 1;
            const itemHtml = `
                <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0; gap: 6px;">
                        <span class="text-muted font-weight-bold flex-shrink-0" style="font-size: 10px; width: 16px;">${newIndex}.</span>
                        <i class="${iconClass} flex-shrink-0" style="font-size: 12px;"></i>
                        <span class="text-truncate font-weight-bold text-dark" style="font-size: 11.5px;" title="${att.title}">
                            ${att.title}
                        </span>
                    </div>
                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                        <a href="${viewUrl}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 font-weight-bold d-inline-flex align-items-center" style="font-size: 11px; height: 22px; border-radius: 4px; gap: 4px;" title="View ${att.title}">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            `;
            $('#ctcCaseAttachmentsList').append(itemHtml);

            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message || 'Attachment uploaded successfully!',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(data.message || 'Attachment uploaded successfully!');
            }
        } else {
            throw new Error(data.message || 'Failed to upload attachment.');
        }
    } catch (err) {
        $btn.prop('disabled', false).html(origHtml);
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Upload Failed', text: err.message || 'Could not upload attachment.' });
        } else {
            alert(err.message || 'Error uploading file');
        }
    }
});
</script>
@endpush
@endsection