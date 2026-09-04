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
        default => 'Managing Director (MD)'
    };
    $routePrefix = match($role) {
        'DDG' => 'ddg',
        'DG'  => 'dg',
        'Finance' => 'finance',
        'HR' => 'hr',
        default => 'md'
    };

    $currentStage = $case->current_stage ?? $case->currentSubstatus->css_stage ?? $role;
    $isCaseWithMe = ($currentStage === $role);
    $canActuallyApprove = ($canApprove ?? false) && ($role === 'DG' || ($authDetails['can_md_approve'] ?? false) || ($authDetails['can_ddg_approve'] ?? false));
    $nextForwardStage = match($role) {
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
    grid-template-columns: 64% 36%;
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
    font-size: 11.5px;
    border-collapse: collapse;
    margin-bottom: 0;
}
.spec-data-table th {
    padding: 9px 12px;
    color: #64748b;
    font-weight: 700;
    font-size: 9.5px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-family: 'Rajdhani', sans-serif;
    white-space: nowrap;
}
.spec-data-table td {
    padding: 10px 12px;
    border-top: 1px solid #f1f5f9;
    color: #0f172a;
    vertical-align: middle;
}
.spec-data-table tr:hover td {
    background: #fbfcfe;
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
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
}
.btn-action-send:hover {
    background: #15803d !important;
    box-shadow: 0 4px 10px rgba(21, 128, 61, 0.3);
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
}
.btn-action-approve:hover {
    flex: 0 0 115px;
    width: 115px;
    background: #15803d !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    transform: translateY(-1px);
}
.btn-action-approve:hover .btn-expand-text {
    max-width: 70px;
    opacity: 1;
    margin-left: 6px;
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
}
.btn-action-cancel:hover {
    flex: 0 0 105px;
    width: 105px;
    background: #b91c1c !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}
.btn-action-cancel:hover .btn-expand-text {
    max-width: 60px;
    opacity: 1;
    margin-left: 6px;
}

.cc-dest-option-item:hover {
    background: #f1f5f9;
}
.cc-dest-option-item.selected {
    background: #e2e8f0;
}
</style>

<div class="content-wrapper dg-page">
    <div class="p-3 pt-3">
        <div class="container-fluid">

            {{-- Top Header Bar --}}
            <div class="dg-hdr">
                <div class="d-flex align-items-center gap-2">
                    <span class="dg-sec-label mb-0"><i class="fas fa-file-contract mr-1"></i> CONTRACT CASE</span>
                    <span class="text-muted" style="font-size: 12px;">|</span>
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $empName }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1 rajdhani" style="font-size: 11.5px;">
                        CASE #CC-{{ $case->ctc_id }} &bull; {{ $caseTypeRaw }}
                    </span>
                    <a href="{{ route("{$routePrefix}.contract-cases.index") }}" class="dg-back-btn">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>

            {{-- 2-Column Grid (Left: Case Details & Financials | Right: Minute & Trail) --}}
            <div class="dg-grid">

                {{-- ========================================================= --}}
                {{-- LEFT PANE: CONSOLIDATED CONTRACT CASE                     --}}
                {{-- ========================================================= --}}
                <div class="dg-box">

                    {{-- Box Header --}}
                    <div class="dg-box-hdr">
                        <div class="dg-sec-label mb-0">
                            <i class="fas fa-file-contract mr-1"></i> Contract Case Information
                        </div>
                        <div>
                            @if($isHiring)
                                <span class="badge badge-success font-weight-bold px-2 py-1" style="font-size: 10px; border-radius: 5px;">
                                    <i class="fas fa-user-plus mr-1"></i> NEW HIRING (HG)
                                </span>
                            @elseif($isRenewal)
                                <span class="badge badge-primary font-weight-bold px-2 py-1" style="font-size: 10px; border-radius: 5px; background: #1e3a8a;">
                                    <i class="fas fa-sync-alt mr-1"></i> CONTRACT RENEWAL (CR)
                                </span>
                            @elseif($isExtension)
                                <span class="badge badge-info font-weight-bold px-2 py-1 text-white" style="font-size: 10px; border-radius: 5px; background: #0284c7;">
                                    <i class="fas fa-clock mr-1"></i> CONTRACT EXTENSION (CE)
                                </span>
                            @else
                                <span class="badge badge-secondary font-weight-bold px-2 py-1" style="font-size: 10px; border-radius: 5px;">
                                    <i class="fas fa-user-check mr-1"></i> {{ $caseTypeRaw }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4">

                        {{-- ===================================================== --}}
                        {{-- 1. CASE DETAILS & FINANCIAL PULSE                     --}}
                        {{-- ===================================================== --}}
                        <div class="row align-items-start mb-3">

                            {{-- Left Sub-Column: Metadata & Attachments --}}
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="font-weight-bold text-dark" style="font-size: 17px; color: #0f172a !important;">
                                        {{ $empName }}
                                    </span>
                                </div>

                                <div class="row g-2 mb-3" style="font-size: var(--dg-value-size);">
                                    <div class="col-6 mb-2">
                                        <span class="text-muted d-block" style="font-size: var(--dg-label-size); font-weight: 700; text-transform: uppercase;">Case ID & Date</span>
                                        <span class="text-dark font-weight-bold">#CC-{{ $case->ctc_id }}</span> &bull; <span class="text-muted">{{ \Carbon\Carbon::parse($case->ctc_date ?? now())->format('d M, Y') }}</span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <span class="text-muted d-block" style="font-size: var(--dg-label-size); font-weight: 700; text-transform: uppercase;">Division / Directorate</span>
                                        <span class="text-dark font-weight-bold">{{ $case->division_name }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: var(--dg-label-size); font-weight: 700; text-transform: uppercase;">Allocated Project</span>
                                        <span class="text-dark font-weight-bold">{{ $projectCode }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: var(--dg-label-size); font-weight: 700; text-transform: uppercase;">Case Status & Location</span>
                                        <span class="text-dark font-weight-bold" style="font-size: var(--dg-value-size);">
                                            {{ $case->ctc_status }} <span class="text-muted font-weight-normal">&bull;</span> <span class="text-muted font-weight-600">{{ $case->current_office_name }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Attached Documents (Project & Case Side-by-Side Micro-Cards) --}}
                                <div class="d-flex align-items-stretch" style="gap: 10px;">
                                    {{-- 1. PROJECT ATTACHMENTS --}}
                                    <div class="border rounded" style="flex: 1; border-color: #e2e8f0 !important; background: #ffffff; border-radius: 7px;">
                                        <div class="py-1.5 px-2.5 d-flex align-items-center justify-content-between" style="background: #f8fafc; border-bottom: 1px solid #f1f5f9; min-height: 26px;">
                                            <span class="font-weight-bold text-truncate" style="font-size: 9px; color: #475569; text-transform: uppercase;">
                                                <i class="fas fa-paperclip text-primary mr-1"></i> PROJECT ATTACHMENTS
                                            </span>
                                            <span class="badge badge-secondary badge-pill" style="font-size: 8px; padding: 2px 5px;">{{ $projectAttachments->count() }}</span>
                                        </div>
                                        <div class="px-2.5 py-1" style="font-size: 10px;">
                                            @forelse($projectAttachments as $pIdx => $pDoc)
                                                <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #f8fafc !important;">
                                                    <div class="d-flex align-items-center overflow-hidden mr-1" style="flex: 1; min-width: 0;">
                                                        <span class="text-muted font-weight-bold mr-1 flex-shrink-0" style="font-size: 9.5px; width: 14px;">{{ $pIdx + 1 }}.</span>
                                                        <span class="text-truncate font-weight-600 text-dark" style="font-size: 9.5px;" title="{{ $pDoc->jat_type }}">{{ $pDoc->jat_type }}</span>
                                                    </div>
                                                    <a href="{{ \App\Facades\FileStorage::url($pDoc->jat_path) }}" target="_blank" class="text-primary flex-shrink-0"><i class="fas fa-eye"></i></a>
                                                </div>
                                            @empty
                                                <div class="text-center py-1 text-muted" style="font-size: 9px;">No files.</div>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- 2. CASE ATTACHMENTS (WITH + UPLOAD BUTTON) --}}
                                    <div class="border rounded" style="flex: 1; border-color: #e2e8f0 !important; background: #ffffff; border-radius: 7px;">
                                        <div class="py-1.5 px-2.5 d-flex align-items-center justify-content-between" style="background: #f8fafc; border-bottom: 1px solid #f1f5f9; min-height: 26px;">
                                            <span class="font-weight-bold text-truncate" style="font-size: 9px; color: #475569; text-transform: uppercase;">
                                                <i class="fas fa-file-invoice text-primary mr-1"></i> CASE ATTACHMENTS
                                            </span>
                                            <div class="d-flex align-items-center gap-1">
                                                <span id="caseAttachmentsCount" class="badge badge-secondary badge-pill mr-1" style="font-size: 8px; padding: 2px 5px;">{{ $caseAttachments->count() }}</span>
                                                <button type="button" class="btn btn-xs btn-primary p-0 d-flex align-items-center justify-content-center" style="width: 17px; height: 17px; border-radius: 4px; background: var(--rd-accent, #5F7858); border: none; cursor: pointer;" data-toggle="modal" data-target="#modalAddCaseAttachment" title="Add Document">
                                                    <i class="fas fa-plus" style="font-size: 8px; color: #ffffff;"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="caseAttachmentsList" class="px-2.5 py-1" style="font-size: 10px;">
                                            @forelse($caseAttachments as $cIdx => $cDoc)
                                                <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #f8fafc !important;">
                                                    <div class="d-flex align-items-center overflow-hidden mr-1" style="flex: 1; min-width: 0;">
                                                        <span class="text-muted font-weight-bold mr-1 flex-shrink-0" style="font-size: 9.5px; width: 14px;">{{ $cIdx + 1 }}.</span>
                                                        <span class="text-truncate font-weight-600 text-dark" style="font-size: 9.5px;" title="{{ $cDoc->cat_type ?: 'Attachment' }}">{{ $cDoc->cat_type ?: 'Attachment' }}</span>
                                                    </div>
                                                    <a href="{{ \App\Facades\FileStorage::url($cDoc->cat_path) }}" target="_blank" class="text-primary flex-shrink-0"><i class="fas fa-eye"></i></a>
                                                </div>
                                            @empty
                                                <div id="noCaseAttPlaceholder" class="text-center py-1 text-muted" style="font-size: 9px;">No files.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Sub-Column: Financial Pulse Card --}}
                            <div class="col-md-5">
                                <div class="dg-fin-card">
                                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2" style="border-color: #d7dee6 !important;">
                                        <span class="rajdhani font-weight-bold text-dark" style="font-size: 12.5px; letter-spacing: 0.5px;">
                                            <i class="fas fa-coins text-primary mr-1"></i> FINANCIAL PULSE
                                        </span>
                                        <span class="badge badge-light border text-primary font-weight-bold rajdhani" style="font-size: 9.5px;">HR BUDGET IMPACT</span>
                                    </div>

                                    <div class="d-flex flex-column" style="gap: 6px; font-size: 11px;">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted font-weight-600">PROPOSED SALARY:</span>
                                            <strong class="text-dark rajdhani font-weight-bold" style="font-size: 13px;">Rs. {{ number_format($proposedSalary) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted font-weight-600">PREVIOUS SALARY:</span>
                                            <span class="text-muted rajdhani">{{ $previousSalary > 0 ? 'Rs. ' . number_format($previousSalary) : 'N/A (New Hire)' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted font-weight-600">SALARY INCREMENT:</span>
                                            <span class="text-success rajdhani font-weight-bold">{{ $previousSalary > 0 ? '+ Rs. ' . number_format($salaryDiff) . ' (' . ($incrementPct > 0 ? '+' . $incrementPct : $incrementPct) . '%)' : 'Full Proposed' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted font-weight-600">PROJECT SHARE:</span>
                                            <span class="text-primary font-weight-bold">{{ $projectPlan->ccp_budgetpercent ?? 100 }}%</span>
                                        </div>
                                    </div>

                                    {{-- Annual impact gets its own highlighted row --}}
                                    <div class="dg-fin-impact-row">
                                        <span class="text-dark font-weight-bold" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.3px;">Annual Impact</span>
                                        <span class="text-success rajdhani font-weight-bold" style="font-size: 16px;">Rs. {{ number_format($annualImpact) }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="dg-divider"></div>

                        {{-- ===================================================== --}}
                        {{-- 2. CANDIDATE DETAILS (CLEAN & FLAT)                   --}}
                        {{-- ===================================================== --}}
                        <div class="mb-3">
                            <div class="dg-sec-label">
                                <i class="fas fa-id-card fa-xs"></i> CANDIDATE DETAILS
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 py-1 px-1">
                                <div>
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;"><i class="fas fa-user text-primary mr-1"></i> CANDIDATE NAME</span>
                                    <span class="text-dark font-weight-bold" style="font-size: 13.5px;">{{ $empName }}</span>
                                </div>
                                <div>
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">FATHER'S NAME</span>
                                    <span class="text-dark font-weight-600" style="font-size: 13px;">{{ $case->father_name ?: 'N/A' }}</span>
                                </div>
                                <div>
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">CNIC #</span>
                                    <span class="rajdhani font-weight-bold text-dark" style="font-size: 13px;">{{ $case->candidate_cnic ?: 'N/A' }}</span>
                                </div>
                                <div>
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">CONTACT / MOBILE</span>
                                    <span class="text-dark font-weight-600" style="font-size: 13px;">{{ $case->candidate_mobile ?: 'N/A' }}</span>
                                </div>
                                <div>
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">EMPLOYEE / SYSTEM ID</span>
                                    <span class="text-primary font-weight-bold rajdhani" style="font-size: 13.5px;">{{ $case->ctc_emp_id ? '#' . $case->ctc_emp_id : '#New Candidate' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="dg-divider"></div>

                        {{-- ===================================================== --}}
                        {{-- 3. CONTRACT DETAILS (TYPE, PROBATION, TERMS TABLE)    --}}
                        {{-- ===================================================== --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0">
                                    <i class="fas fa-file-signature fa-xs"></i>
                                    {{ $isRenewalOrExt ? 'CONTRACT DETAILS & TERMS COMPARISON' : 'CONTRACT DETAILS & PROPOSED TERMS' }}
                                </div>
                            </div>

                            {{-- Contract Metadata Sub-Row (Flat, No box wrapper) --}}
                            <div class="d-flex align-items-center justify-content-start flex-wrap gap-4 py-1 px-1 mb-2">
                                <div style="min-width: 150px;">
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;"><i class="fas fa-briefcase text-primary mr-1"></i> EMPLOYMENT TYPE</span>
                                    <span class="text-dark font-weight-bold" style="font-size: 13px;">{{ $empType }}</span>
                                </div>
                                <div style="min-width: 150px;">
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;"><i class="fas fa-stopwatch text-warning mr-1"></i> PROBATION PERIOD</span>
                                    <span class="font-weight-bold {{ $probationMonths ? 'text-warning' : 'text-muted' }}" style="font-size: 13px;">
                                        {{ $probationMonths ? $probationMonths . ' Months' : 'N/A' }}
                                    </span>
                                </div>
                                <div style="min-width: 150px;">
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;"><i class="fas fa-money-bill-wave text-success mr-1"></i> PROBATION SALARY</span>
                                    <span class="rajdhani font-weight-bold {{ $probationSalary ? 'text-success' : 'text-muted' }}" style="font-size: 13.5px;">
                                        {{ $probationSalary ? 'Rs. ' . number_format($probationSalary) : 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Terms & Comparison Table --}}
                            <div class="table-responsive border rounded" style="background: #ffffff; border-color: #e2e8f0 !important; border-radius: 8px;">
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
                                                    <span class="text-muted font-weight-bold" style="font-size: 11px;">Previous Contract</span>
                                                </td>
                                                <td class="text-muted">
                                                    {{ $prevJobtitle }} <span class="text-muted">({{ $prevGrade }})</span>
                                                </td>
                                                <td class="rajdhani text-muted">
                                                    {{ $prevStart ? \Carbon\Carbon::parse($prevStart)->format('d M, Y') : 'N/A' }}
                                                    &mdash;
                                                    {{ $prevEnd ? \Carbon\Carbon::parse($prevEnd)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td class="text-right rajdhani text-muted font-weight-bold">
                                                    {{ $previousSalary > 0 ? 'Rs. ' . number_format($previousSalary) : 'N/A' }}
                                                </td>
                                                <td class="text-right rajdhani text-muted font-weight-bold">
                                                    {{ $previousSalary > 0 ? 'Rs. ' . number_format($previousSalary * 12) : 'N/A' }}
                                                </td>
                                            </tr>

                                            {{-- Row 2: Proposed / Renewed Terms --}}
                                            <tr style="background: #f6faf7;">
                                                <td>
                                                    <strong class="text-primary font-weight-bold" style="font-size: 11px;">Proposed Renewal</strong>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $empDesignation }} ({{ $empGrade }})</strong>
                                                </td>
                                                <td class="rajdhani font-weight-bold text-dark">
                                                    {{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M, Y') : 'N/A' }}
                                                    &mdash;
                                                    {{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 12.5px;">
                                                    Rs. {{ number_format($proposedSalary) }}
                                                </td>
                                                <td class="text-right font-weight-bold text-primary rajdhani" style="font-size: 12.5px;">
                                                    Rs. {{ number_format($annualImpact) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    {{-- 1-Line Increment Summary Strip --}}
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" style="background: #f0fdf4; border-color: #bbf7d0 !important; font-size: 11px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-success rajdhani font-weight-bold" style="font-size: 12px;">
                                                INCREMENT: +Rs. {{ number_format($salaryDiff) }} ({{ $incrementPct > 0 ? '+' . $incrementPct : $incrementPct }}%)
                                            </strong>
                                            <span class="text-muted ml-2">
                                                @if($prevGrade !== $empGrade)
                                                    Grade: <strong>{{ $prevGrade }}</strong> &rarr; <strong>{{ $empGrade }}</strong>
                                                @else
                                                    Grade: <strong>{{ $empGrade }}</strong>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="rajdhani font-weight-bold text-success" style="font-size: 12px;">
                                            Annual Delta: +Rs. {{ number_format($salaryDiff * 12) }}
                                        </div>
                                    </div>
                                @else
                                    {{-- Fresh Hiring Table --}}
                                    <table class="spec-data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;">S.NO</th>
                                                <th>PROPOSED POSITION</th>
                                                <th>PAY SCALE / GRADE</th>
                                                <th>TENURE (START & END)</th>
                                                <th class="text-right">MONTHLY SALARY</th>
                                                <th class="text-right">ANNUAL IMPACT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="font-weight-bold text-muted">1</td>
                                                <td class="font-weight-bold text-dark">{{ $empDesignation }}</td>
                                                <td class="font-weight-bold text-dark">{{ $empGrade }}</td>
                                                <td class="rajdhani font-weight-bold">
                                                    {{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M, Y') : 'N/A' }}
                                                    &mdash;
                                                    {{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 12.5px;">Rs. {{ number_format($proposedSalary) }}</td>
                                                <td class="text-right font-weight-bold text-primary rajdhani" style="font-size: 12.5px;">Rs. {{ number_format($annualImpact) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>

                        <div class="dg-divider"></div>

                        {{-- ===================================================== --}}
                        {{-- 4. PROJECT ALLOCATIONS (HR HEAD SPECIFIC)             --}}
                        {{-- ===================================================== --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0">
                                    <i class="fas fa-layer-group fa-xs"></i> PROJECT ALLOCATIONS (HR HEAD)
                                </div>
                                <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">
                                    {{ $allocatedGroups->count() }} {{ Str::plural('ALLOCATION', $allocatedGroups->count()) }}
                                </span>
                            </div>
                            <div class="table-responsive border rounded" style="background: #ffffff; border-color: #e2e8f0 !important; border-radius: 8px;">
                                <table class="spec-data-table" id="projectAllocationTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 35px;">#</th>
                                            <th>PROJECT CODE</th>
                                            <th>PERIOD / DURATION</th>
                                            <th>SUB HEAD</th>
                                            <th class="text-right">MONTHLY SALARY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allocatedGroups as $agIdx => $ag)
                                        @php
                                            $startFormatted = $ag['start_dt'] ? \Carbon\Carbon::parse($ag['start_dt'])->format('d M, Y') : '';
                                            $endFormatted = $ag['end_dt'] ? \Carbon\Carbon::parse($ag['end_dt'])->format('d M, Y') : '';
                                            $monthCount = $ag['month_count'] ?? 1;
                                            $durationStr = ($startFormatted && $endFormatted) 
                                                ? "{$startFormatted} – {$endFormatted} ({$monthCount} " . Str::plural('Month', $monthCount) . ")"
                                                : "{$monthCount} " . Str::plural('Month', $monthCount);
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold text-muted">{{ $agIdx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $ag['prj_code'] }}</td>
                                            <td class="rajdhani font-weight-600 text-dark">{{ $durationStr }}</td>
                                            <td class="text-muted font-weight-600">Pay & Allowances (HR)</td>
                                            <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 13px;">Rs. {{ number_format($proposedSalary) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Terms & Conditions Banner Note --}}
                        <div class="p-2.5 rounded border" style="background: #f8fafc; border-color: #e2e8f0 !important; font-size: 11px; color: #475569; line-height: 1.45; border-radius: 7px;">
                            <i class="fas fa-info-circle text-primary mr-1"></i> Salary and monthly allowances will be disbursed from project funds allocated under <strong>{{ $projectCode }}</strong> (Pay & Allowances HR Head). Contract renewal/extension is subject to executive approval and institutional rules.
                        </div>

                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- RIGHT PANE: MINUTE / DECISION, TRAIL & RECENT CASES        --}}
                {{-- ========================================================= --}}
                <div class="dg-right">

                    {{-- 1. Scrutiny & Minute Trail (Matches Purchase Cases Design) --}}
                    <div class="dg-panel-r">
                        <div class="dg-panel-r-hdr py-2 px-3 d-flex align-items-center justify-content-between">
                            <span class="dg-panel-r-title font-weight-bold rajdhani" style="font-size: 13px; color: #0f172a !important; letter-spacing: 0.5px;">
                                <i class="fas fa-file-signature text-primary mr-1"></i> SCRUTINY & MINUTE TRAIL
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-primary px-2 py-1 rajdhani" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px; border-radius: 4px; background: var(--rd-accent, #5F7858);">
                                    {{ $case->current_office_name ?? $case->ctc_status }}
                                </span>
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
                                                    <span class="btn-expand-text rajdhani font-weight-bold">APPROVE</span>
                                                </button>

                                                {{-- Right: Compact Cancel / Reject Button (Red Cross, expands on hover) --}}
                                                <button type="button" onclick="handleAction('cancel')" id="btnCancel" class="btn-action-cancel" title="Cancel / Reject Contract Case">
                                                    <i class="fas fa-times"></i>
                                                    <span class="btn-expand-text rajdhani font-weight-bold">REJECT</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @elseif($role === 'Division')
                                <div class="alert alert-light border py-2 px-2.5 mb-3" style="font-size: 11px; background: #f8fafc; border-radius: 6px;">
                                    <i class="fas fa-info-circle text-primary mr-1"></i> Case submitted from Division. Currently with <strong>{{ $case->current_office_name }}</strong>.
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
                                <i class="fas fa-list-alt text-success mr-1"></i> RECENT CONTRACT CASES
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
<div class="modal fade" id="modalAddCaseAttachment" tabindex="-1" role="dialog" aria-labelledby="modalAddCaseAttachmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header py-2.5 px-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h6 class="modal-title font-weight-bold rajdhani text-dark mb-0" id="modalAddCaseAttachmentLabel" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-file-upload text-primary mr-1.5"></i> ATTACH DOCUMENT TO CONTRACT CASE
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddCaseAttachment" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="form-group mb-2.5">
                        <label class="font-weight-bold text-dark small mb-1" style="font-size: 11px;">DOCUMENT TITLE / NAME <span class="text-danger">*</span></label>
                        <input type="text" name="doc_title" id="attDocTitle" class="form-control" placeholder="e.g., Justification Note, CNIC Copy, Degree" required style="font-size: 12px; border-radius: 6px; border-color: #cbd5e1;">
                    </div>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-dark small mb-1" style="font-size: 11px;">SELECT FILE <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="attFile" class="form-control-file border p-1.5 rounded w-100" required style="font-size: 11.5px; border-color: #cbd5e1 !important; background: #fafafa; border-radius: 6px;">
                        <small class="text-muted d-block mt-1" style="font-size: 10px;"><i class="fas fa-info-circle mr-1"></i> PDF, DOCX, XLSX, PNG, JPG (Max: 20MB)</small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" data-dismiss="modal" style="font-size: 11.5px; border-radius: 6px;">Cancel</button>
                    <button type="submit" id="btnUploadAttachment" class="btn btn-sm btn-primary font-weight-bold rajdhani px-3" style="font-size: 12px; border-radius: 6px; background-color: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;">
                        <i class="fas fa-upload mr-1"></i> UPLOAD ATTACHMENT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

                    const newRow = `
                        <div class="d-flex justify-content-between align-items-center py-1 border-top" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex align-items-center overflow-hidden mr-1" style="flex: 1; min-width: 0;">
                                <span class="text-muted font-weight-bold mr-1 flex-shrink-0" style="font-size: 9.5px; width: 14px;">${nextNum}.</span>
                                <span class="text-truncate font-weight-600 text-dark" style="font-size: 9.5px;" title="${resp.attachment.title}">${resp.attachment.title}</span>
                            </div>
                            <a href="${resp.attachment.url}" target="_blank" class="text-primary flex-shrink-0"><i class="fas fa-eye"></i></a>
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
</script>
@endpush
@endsection