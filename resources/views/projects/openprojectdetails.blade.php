@extends('welcome')
@section('content')
<div class="content-wrapper pt-3 {{ ($readOnly ?? false) ? 'command-view' : '' }}">
    <style>

        
        /* --- GLOBAL & UTILS --- */
        .card-primary.card-outline { border-top: 3px solid var(--rd-accent); }
        .bg-light-blue { background-color: var(--rd-bg); }
        
        /* --- HEADER & CONTROLS --- */
        .header-controls { display: flex; align-items: center; gap: 8px; }
        .milestone-box-compact {
            background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 30px;
            padding: 8px 15px; display: inline-flex; align-items: center; justify-content: space-between;
            min-width: 250px; max-width: 100%; height: auto; min-height: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin: 5px 0;
        }
        /* --- INFO PANEL --- */
        .info-panel { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08); overflow: visible; }
        .info-label { font-size: 0.82rem; text-transform: uppercase; color: var(--rd-text3); font-weight: 800; letter-spacing: 0.6px; display: block; margin-bottom: 6px; }
        .info-value { font-size: 0.95rem; color: var(--rd-text1); font-weight: 600; line-height: 1.4; }
        .cost-tag { background: var(--rd-success-soft); color: var(--rd-success); padding: 5px 12px; border-radius: 4px; font-weight: 700; border: 1px solid var(--rd-success); display: inline-block; }
        
        /* --- KEY DATES GRID (TOP PANEL) --- */
        .key-dates-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px 8px; }
        .key-dates-grid-2col { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 8px; }
        .kd-item { background: var(--rd-surface2, #f8fafc); border: 1px solid var(--rd-border, #e2e8f0); border-left: 3.5px solid var(--rd-border, #cbd5e1); border-radius: 7px; padding: 6px 10px; transition: all 0.2s; }
        .kd-item.done { border-left-color: var(--rd-success, #28a745); }
        .kd-item.active { border-left-color: var(--rd-accent, #5F7858); }
        .kd-item.text-danger { border-left-color: var(--rd-danger, #dc3545); }
        .kd-item.text-success { border-left-color: var(--rd-success, #28a745); }
        .kd-label { font-size: 0.70rem; font-weight: 700; text-transform: uppercase; color: var(--rd-text3, #64748b); display: block; line-height: 1.1; margin-bottom: 3px; }
        .kd-val { font-size: 0.90rem; font-weight: 800; color: var(--rd-text1, #1e293b); display: block; line-height: 1.1; white-space: nowrap; }
        @media (min-width: 992px) {
            .border-right-lg { border-right: 1px solid var(--rd-border, #e2e8f0) !important; }
            .border-left-lg { border-left: 1px solid var(--rd-border, #e2e8f0) !important; }
        }

        /* --- RADIAL PROGRESS RINGS (FINANCE KNOBS) --- */
        .radial-ring-wrap { position: relative; width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; }
        .ring-pct-val { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.80rem; font-weight: 800; line-height: 1; pointer-events: none; }
        .finance-bars-wrap { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .finance-box {
            width: 78px; height: 96px; background: var(--rd-surface2, #f8fafc); border: 1px solid var(--rd-border, #e2e8f0); border-radius: 8px; padding: 6px 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06); display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .finance-title { margin-top: 4px; font-size: 0.76rem; font-weight: 800; color: var(--rd-text3, #64748b); text-transform: uppercase; letter-spacing: 0.5px; }

        /* --- TEAM SECTION --- */
        .team-section-container { display: flex; align-items: center; justify-content: flex-start; }
        .team-avatar-wrapper { width: 38px; height: 38px; margin-right: -6px; position: relative; z-index: 10; cursor: pointer; transition: transform 0.2s; }
        .team-avatar-wrapper:hover { transform: scale(1.2); z-index: 100; margin: 0 4px; }
        .team-avatar-wrapper img { width: 38px; height: 38px; border-radius: 50%; border: 2px solid var(--rd-surface); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); object-fit: cover; background: var(--rd-surface); }
        .more-staff-btn { width: 38px; height: 38px; border-radius: 50%; background: var(--rd-surface); color: var(--rd-text2); border: 2px dashed var(--rd-border); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; margin-left: 8px; z-index: 0; }
        
        /* --- DATES & DOCS --- */
        .date-grid-item { position: relative; padding-left: 10px; margin-bottom: 14px; border-left: 2px solid var(--rd-border); }
        .date-grid-item.active { border-left-color: var(--rd-accent); }
        .date-grid-item.done { border-left-color: var(--rd-success); }
        .d-title { font-size: 0.65rem; font-weight: 700; color: var(--rd-text3); text-transform: uppercase; display: block; line-height: 1; margin-bottom: 3px; }
        .d-value { font-size: 0.8rem; color: var(--rd-text1); font-weight: 600; line-height: 1; }
        .doc-card { background: var(--rd-surface); border: 1px solid var(--rd-border); border-left: 3px solid var(--rd-accent); border-radius: 6px; padding: 8px 12px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; }
        .doc-card:hover { transform: translateX(3px); box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
        .doc-content { display: flex; align-items: center; overflow: hidden; margin-right: 10px; }
        .doc-icon { width: 28px; height: 28px; background: var(--rd-surface2); color: var(--rd-accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 0.75rem; flex-shrink: 0; }
        .doc-title { font-size: 0.8rem; font-weight: 600; color: var(--rd-text2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .file-input-hidden { display: none !important; }
        .doc-card.other { border-left-color: var(--rd-accent); cursor: pointer; background: var(--rd-surface); }
        .doc-card.other:hover { background-color: var(--rd-surface2); }
        .doc-icon.other { color: var(--rd-accent); background-color: var(--rd-accent-soft); }
        h6[data-toggle="collapse"] i.fa-chevron-down { transition: transform 0.3s ease; }
        h6[data-toggle="collapse"][aria-expanded="true"] i.fa-chevron-down { transform: rotate(180deg); }

        /* --- OVERALL STEPS WIZARD (COMPACT, NO FORCED SCROLL) --- */
        .steps-container {
            position: relative;
            height: 50px;
            margin: 48px 16px 64px;
            width: calc(100% - 32px);
            padding: 0;
        }
        .steps-wrapper {
            overflow: visible;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        @media (max-width: 767.98px) {
            /* Only allow horizontal scroll on genuinely small (mobile) screens */
            .steps-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .steps-container { min-width: 560px; }
            .steps-wrapper::-webkit-scrollbar { height: 4px; }
            .steps-wrapper::-webkit-scrollbar-thumb { background: var(--rd-border); border-radius: 10px; }
        }
        .steps-track {
            position: absolute; top: 50%; left: 0; width: 100%; height: 3px; background: var(--rd-border);
            transform: translateY(-50%); z-index: 1; border-radius: 2px;
        }
        .steps-fill { height: 100%; background: var(--rd-success); transition: width 0.4s ease; border-radius: 2px; }
        .step-item {
            position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 3;
            width: 26px; height: 26px;
            display: flex; justify-content: center; align-items: center; cursor: pointer;
        }
        .step-dot {
            width: 26px; height: 26px; border-radius: 50%; background: var(--rd-surface); border: 3px solid var(--rd-border);
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.6rem;
            color: var(--rd-text3); transition: all 0.3s; position: relative; z-index: 2;
        }
        .step-item.completed .step-dot { background: var(--rd-success); border-color: var(--rd-success); color: #fff; }
        .step-item.active .step-dot { background: var(--rd-accent); border-color: var(--rd-accent); color: #fff; transform: scale(1.2); box-shadow: 0 0 0 4px rgba(79, 140, 255, 0.15); }
        .step-label {
            position: absolute; top: -22px; left: 50%; transform: translateX(-50%);
            font-size: 0.6rem; font-weight: 700; color: var(--rd-text3); white-space: nowrap;
        }
        .step-item.active .step-label { color: var(--rd-accent); }
        .step-date {
            position: absolute; bottom: -26px; width: 90px; left: 50%; margin-left: -45px;
            text-align: center; font-size: 0.58rem; color: var(--rd-text3); white-space: nowrap; font-weight: 500;
        }
        .step-item.active .step-date { color: var(--rd-accent); font-weight: 700; }
        .step-tooltip {
            display: none; position: absolute; bottom: 38px; left: 50%; transform: translateX(-50%);
            background: var(--rd-surface3); color: var(--rd-text1); padding: 6px 10px; border-radius: 4px;
            font-size: 0.7rem; white-space: nowrap; z-index: 100; box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        }
        .step-tooltip::after {
            content: ""; position: absolute; top: 100%; left: 50%; margin-left: -5px;
            border-width: 5px; border-style: solid; border-color: var(--rd-surface3) transparent transparent transparent;
        }
        .step-item:hover .step-tooltip { display: block; }

        /* --- TODAY MARKER --- */
        .overall-today-marker {
            position: absolute; top: 50%; width: 2px; height: 35px; background: transparent; z-index: 5;
            transition: left 1s ease;
        }
        .overall-today-marker .status-bubble{
            position: absolute; top: 42px; left: 50%; transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            padding: 6px 10px; border-radius: 14px; font-size: .6rem; font-weight: 700;
            color: #fff; text-align: center; white-space: nowrap; box-shadow: 0 3px 8px rgba(0,0,0,.4);
        }
        .overall-today-marker .status-bubble::before{
            content: ""; position: absolute; top: -6px; left: 50%; transform: translateX(-50%);
            border-left: 6px solid transparent; border-right: 6px solid transparent;
            border-bottom: 6px solid var(--rd-danger);
        }
        .status-bubble.late { background: var(--rd-danger); box-shadow: 0 0 10px rgba(220,53,69,.3); }
        .status-bubble.ontrack { background: var(--rd-success); box-shadow: 0 0 10px rgba(40,167,69,.3); }

        /* --- MILESTONE TABLE --- */
        .milestone-container { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 10px; overflow: hidden; }
        .milestone-scroll-box { overflow-x: auto; }
        .table-custom thead th { background: var(--rd-surface2); color: var(--rd-text3); text-transform: uppercase; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.5px; border-bottom: 2px solid var(--rd-border); padding: 13px 18px; position: sticky; top: 0; z-index: 5; }
        .table-custom tbody td { padding: 13px 18px; vertical-align: middle; color: var(--rd-text2); font-size: 0.88rem; border-bottom: 1px solid var(--rd-border); }

        /* --- FINANCE KNOBS --- */
        .finance-bars-wrap { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .finance-box {
            width: 68px; height: 84px; background: var(--rd-surface2, #f8fafc); border: 1px solid var(--rd-border, #e2e8f0); border-radius: 8px; padding: 5px 2px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .finance-title { margin-top: 3px; font-size: 8px; font-weight: 800; color: var(--rd-text3, #64748b); text-transform: uppercase; letter-spacing: 0.5px; }

        .command-view a[href*="attachment/delete"] { display: none !important; }
        .command-view form[action*="upload-single"],
        .command-view form[action*="upload-other"],
        .command-view form[action*="mark-complete"] { display: none !important; }

      
        /* --- MODAL --- */
        .glass-modal .modal-content { background: var(--rd-surface); border-radius: 15px; border: 1px solid var(--rd-border2); }
        .emp-modal-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--rd-surface2); margin-bottom: 15px; }
        .emp-detail-row { border-bottom: 1px solid var(--rd-border); padding: 10px 0; display: flex; justify-content: space-between; }
    </style>

   @php
        $today = \Carbon\Carbon::today();

        // EDC logic
        $edc = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : null;
        $edcClass = $edc && $today->gt($edc) ? 'text-danger' : 'text-success';

        // Milestones sorted serial-number wise (by msn_id or msn_idd)
        $milestones = $project->milestones->sortBy(function($m) {
            return (int) ($m->msn_id ?: $m->msn_idd ?: 0);
        })->values();

        $totalMilestones = $milestones->count();
        $completedMilestones = $milestones->filter(fn($m) => strtolower(trim($m->msn_status ?? '')) === 'completed')->count();
        $nextMilestone = $milestones->first(fn($m) => strtolower(trim($m->msn_status ?? '')) !== 'completed');

        // Next milestone status
        $isOverdue = false;
        $statusMsg = "All Done";
        $statusClass = "text-secondary";
        if ($nextMilestone && $nextMilestone->msn_targetdt) {
            $target = \Carbon\Carbon::parse($nextMilestone->msn_targetdt)->startOfDay();
            $diff = $today->diffInDays($target, false);
            if ($diff < 0) {
                $isOverdue = true;
                $statusMsg = abs($diff) . " Days Late";
                $statusClass = "text-danger";
            } else {
                $statusMsg = $diff . " Days Left";
                $statusClass = "text-success";
            }
        }

        // Project Date Boundaries
        $firstMs  = $milestones->first();
        $lastMs   = $milestones->last();
        $prjStart = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : ($firstMs && $firstMs->msn_targetdt ? \Carbon\Carbon::parse($firstMs->msn_targetdt) : $today);
        $prjEnd   = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : ($lastMs && $lastMs->msn_targetdt ? \Carbon\Carbon::parse($lastMs->msn_targetdt) : $today);
        if ($prjEnd->lt($prjStart)) $prjEnd = $prjStart->copy()->addDay();

        // If the project has overrun (today, or an achieved milestone date, falls after
        // the official EDC), stretch the *layout* end date so every marker still has
        // room to breathe instead of all piling up at the 99-100% mark next to END.
        // The END box itself always keeps showing the real $edc / $prjEnd date below.
        $layoutEnd = $milestones->pluck('msn_achvdt')->filter()
            ->map(fn ($d) => \Carbon\Carbon::parse($d))
            ->push($prjEnd)
            ->push($today)
            ->max();
        if ($layoutEnd->gt($prjEnd)) {
            $prjEnd = $layoutEnd->copy()->addDays(15);
        }

        $totalDaysSpan   = $prjStart->diffInDays($prjEnd) ?: 1;
        $daysPassedTotal = $prjStart->diffInDays($today, false);
        $overallTimePercent = round(($daysPassedTotal / $totalDaysSpan) * 100, 1);
        $overallTimePercent = max(0, min(100, $overallTimePercent));

        // Progress calculation based on milestone completion
        $overallPercent = 0;
        if ($totalMilestones > 0) {
            if ($completedMilestones === $totalMilestones) {
                $overallPercent = 100;
            } else {
                $overallPercent = round(($completedMilestones / $totalMilestones) * 100, 1);
            }
        }

        // $team is passed from the controller
        $displayLimit = 6;

        $fixedDocs = ['PPF', 'Approval Letter', 'URD', 'Work Order'];
        $allAttachments = $project->attachments;
        $otherDocsCount = $allAttachments->whereNotIn('jat_type', $fixedDocs)->count();

        // ---- Milestone track inset so first/last diamonds never sit under START/END boxes ----
        $trackInsetStart = 9;   // % from left where milestone track begins
        $trackInsetRange = 82;  // % width available for milestones (9% -> 91%)
    @endphp

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        <div class="card card-primary card-outline shadow-sm border-0">
            <div class="card-header p-3 bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="d-flex align-items-center mb-1 flex-wrap" style="gap:5px;">
                            <span class="badge badge-light border">CODE: {{ $project->prj_code }}</span>
                            <span class="font-weight-bolder {{ $edcClass }} small">
                                <i class="fas fa-flag-checkered mr-1"></i> EDC: {{ $edc ? $edc->format('d M, Y') : 'TBD' }}
                            </span>
                          
                        </div>
                        <h4 class="text-dark font-weight-bold m-0 text-truncate" title="{{ $project->prj_title }}" style="font-family:'Rajdhani',sans-serif; letter-spacing:0.5px;">
                            {{ $project->prj_title }}
                        </h4>
                    </div>

                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        @if($nextMilestone)
                            <div class="milestone-box-compact">
                                <div class="d-flex align-items-center pr-3 border-right mr-3">
                                    <span class="font-weight-bold {{ $statusClass }}" style="font-size: 0.95rem;">
                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clock' }} mr-1"></i>
                                        {{ $statusMsg }}
                                    </span>
                                </div>
                                <div class="text-left flex-grow-1" style="min-width:0;">
                                    <div class="font-weight-bold text-dark text-truncate" style="max-width:160px;" title="{{ $nextMilestone->msn_desc }}">
                                        {{ $nextMilestone->msn_desc }}
                                    </div>
                                    <small class="text-muted">Target: {{ \Carbon\Carbon::parse($nextMilestone->msn_targetdt)->format('d M, Y') }}</small>
                                </div>
                            </div>
                        @else
                            <span class="text-success font-weight-bold">
                                <i class="fas fa-check-circle mr-1"></i> All Milestones Completed
                            </span>
                        @endif
                    </div>

                    <div class="col-md-4 text-center text-md-right">
                        <div class="header-controls justify-content-center justify-content-md-end flex-wrap">
                            {{-- LOG HISTORY BUTTON (NEW) --}}
                            <a href="{{ route('projecthistory', ['project_id' => $project->prj_id]) }}" class="btn btn-outline-info btn-sm shadow-sm font-weight-bold">
                                <i class="fas fa-history mr-1"></i> History
                            </a>

                            <a href="{{ route('projecthistory', $project->prj_id) }}" class="btn btn-outline-primary btn-sm shadow-sm font-weight-bold">
                                <i class="fas fa-list-alt mr-1"></i> MPRs
                            </a>
                            @if(!($readOnly ?? false))
                                <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold px-3">
                                    <i class="fas fa-plus-circle mr-1"></i> MPR
                                </a>
                                @can('update', $project)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm shadow-sm font-weight-bold"
                                            data-toggle="modal"
                                            data-target="#editProjectDetailsModal"
                                            title="Edit Project Details (Scope, Sponsor, Key Dates)">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light-blue">

            <!-- INFO PANEL (Sponsor + Key Dates + Budget + Knobs + Team) -->
            <div class="info-panel p-3.5" style="padding: 16px 20px;">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 20px;">
                    
                    {{-- 1. SPONSOR & SCOPE (Left Column) --}}
                    <div class="info-sec info-sec-scope" style="flex: 1 1 210px; max-width: 260px;">
                        <div class="mb-2">
                            <span class="badge badge-light border px-3 py-1.5 rounded-pill text-uppercase shadow-sm font-weight-bold" style="font-size:0.82rem;">
                                <i class="fas fa-handshake text-primary mr-1"></i> {{ $project->prj_sponsor ?? 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <h6 class="text-dark font-weight-bold mb-1" style="font-size:0.95rem;">Scope of Work</h6>
                            <p class="text-muted m-0 mb-2" style="font-size:0.86rem; line-height:1.4;">
                                {{ Str::limit($project->prj_scope ?? 'No scope defined.', 90) }}
                            </p>
                            @if($head)
                                <a href="{{ route('projects.financial_view', $project->prj_id) }}" class="btn btn-xs btn-outline-info rajdhani font-weight-bold" style="font-size:0.78rem; padding: 3px 8px;">
                                    <i class="fas fa-chart-line mr-1"></i> FINANCIAL VIEW
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- 2. KEY DATES (2 Columns Grid) --}}
                    <div class="info-sec info-sec-dates pl-lg-3 border-left-lg" style="flex: 1 1 240px; max-width: 300px;">
                        <span class="info-label text-primary font-weight-bold mb-2" style="font-size:0.82rem; letter-spacing:0.5px;">
                            <i class="fas fa-calendar-alt mr-1"></i> KEY DATES
                        </span>
                        <div class="key-dates-grid-2col">
                            <div class="kd-item {{ $project->prj_rcptdt ? 'done' : '' }}">
                                <span class="kd-label">Received</span>
                                <span class="kd-val">{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('d M y') : '—' }}</span>
                            </div>
                            <div class="kd-item {{ $project->prj_assigndt ? 'done' : '' }}">
                                <span class="kd-label">Assigned</span>
                                <span class="kd-val">{{ $project->prj_assigndt ? \Carbon\Carbon::parse($project->prj_assigndt)->format('d M y') : '—' }}</span>
                            </div>
                            <div class="kd-item {{ $project->prj_propdt ? 'done' : '' }}">
                                <span class="kd-label">Proposal</span>
                                <span class="kd-val">{{ $project->prj_propdt ? \Carbon\Carbon::parse($project->prj_propdt)->format('d M y') : '—' }}</span>
                            </div>
                            <div class="kd-item {{ $project->prj_aprvdt ? 'done' : '' }}">
                                <span class="kd-label">Approved</span>
                                <span class="kd-val">{{ $project->prj_aprvdt ? \Carbon\Carbon::parse($project->prj_aprvdt)->format('d M y') : '—' }}</span>
                            </div>
                            <div class="kd-item active">
                                <span class="kd-label">Start</span>
                                <span class="kd-val">{{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d M y') : '—' }}</span>
                            </div>
                            <div class="kd-item {{ $edcClass }}">
                                <span class="kd-label">EDC</span>
                                <span class="kd-val">{{ $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt)->format('d M y') : '—' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. FINANCIAL NUMBERS (Total Budget -> Received -> Total Spent) --}}
                    @php
                        $recAmount = $head ? (float)($head->received ?? 0) : 0;
                    @endphp
                    <div class="info-sec info-sec-budget pl-lg-3 border-left-lg" style="flex: 1 1 200px; max-width: 250px;">
                        <span class="info-label text-primary font-weight-bold mb-2" style="font-size:0.82rem; letter-spacing:0.5px;">
                            <i class="fas fa-coins mr-1"></i> BUDGET & SPENT
                        </span>
                        <div class="budget-items-stacked">
                            <div class="mb-2">
                                <span class="d-block text-muted text-uppercase fw-bold" style="font-size:0.74rem; line-height:1.1;">Total Budget</span>
                                <span class="d-block text-dark font-weight-bold" style="font-size: 1.12rem; line-height: 1.2;">
                                    Rs. {{ number_format(($project->prj_propcost ?? 0) / 1_000_000, 2) }} M
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="d-block text-muted text-uppercase fw-bold" style="font-size:0.74rem; line-height:1.1;">Received</span>
                                @if($recAmount > 0)
                                    <span class="d-block text-success font-weight-bold" style="font-size: 1.12rem; line-height: 1.2;">
                                        Rs. {{ number_format($recAmount / 1_000_000, 2) }} M
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 0.76rem; padding: 3px 8px; letter-spacing: 0.2px;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Amount Not Received Yet
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="d-block text-muted text-uppercase fw-bold" style="font-size:0.74rem; line-height:1.1;">Total Spent</span>
                                <span class="d-block text-danger font-weight-bold" style="font-size: 1.12rem; line-height: 1.2;">
                                    Rs. {{ number_format(($totalSpent ?? 0) / 1_000_000, 2) }} M
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 4. FINANCE KNOBS (EQUIP, HR, MISC with perfectly centered SVG circles) --}}
                    <div class="info-sec info-sec-knobs pl-lg-3 border-left-lg" style="flex: 0 0 auto;">
                        <span class="info-label text-primary font-weight-bold mb-2" style="font-size:0.82rem; letter-spacing:0.5px;">
                            <i class="fas fa-chart-pie mr-1"></i> SUBHEAD UTILIZATION
                        </span>
                        <div class="finance-bars-wrap d-flex align-items-center" style="gap: 10px;">
                            @php
                                $equipVal = min(100, max(0, (int)($finData['equip_pct'] ?? 0)));
                                $hrVal = min(100, max(0, (int)($finData['hr_pct'] ?? 0)));
                                $miscVal = min(100, max(0, (int)($finData['misc_pct'] ?? 0)));
                                $circ = 138.23; // 2 * pi * 22
                            @endphp
                            {{-- EQUIP --}}
                            <div class="finance-box">
                                <div class="radial-ring-wrap">
                                    <svg width="54" height="54" viewBox="0 0 54 54">
                                        <circle cx="27" cy="27" r="22" stroke="#e2e8f0" stroke-width="5" fill="none" />
                                        <circle cx="27" cy="27" r="22" stroke="#FC7A58" stroke-width="5" fill="none"
                                                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $equipVal / 100) }}"
                                                stroke-linecap="round" transform="rotate(-90 27 27)" />
                                    </svg>
                                    <span class="ring-pct-val" style="color:#FC7A58;">{{ $equipVal }}%</span>
                                </div>
                                <div class="finance-title">EQUIP</div>
                            </div>

                            {{-- HR --}}
                            <div class="finance-box">
                                <div class="radial-ring-wrap">
                                    <svg width="54" height="54" viewBox="0 0 54 54">
                                        <circle cx="27" cy="27" r="22" stroke="#e2e8f0" stroke-width="5" fill="none" />
                                        <circle cx="27" cy="27" r="22" stroke="#42e695" stroke-width="5" fill="none"
                                                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $hrVal / 100) }}"
                                                stroke-linecap="round" transform="rotate(-90 27 27)" />
                                    </svg>
                                    <span class="ring-pct-val" style="color:#20c997;">{{ $hrVal }}%</span>
                                </div>
                                <div class="finance-title">HR</div>
                            </div>

                            {{-- MISC --}}
                            <div class="finance-box">
                                <div class="radial-ring-wrap">
                                    <svg width="54" height="54" viewBox="0 0 54 54">
                                        <circle cx="27" cy="27" r="22" stroke="#e2e8f0" stroke-width="5" fill="none" />
                                        <circle cx="27" cy="27" r="22" stroke="#4f8cff" stroke-width="5" fill="none"
                                                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $miscVal / 100) }}"
                                                stroke-linecap="round" transform="rotate(-90 27 27)" />
                                    </svg>
                                    <span class="ring-pct-val" style="color:#4f8cff;">{{ $miscVal }}%</span>
                                </div>
                                <div class="finance-title">MISC</div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. EMPLOYEES / TEAM (Far Right Column) --}}
                    <div class="info-sec info-sec-team pl-lg-3 border-left-lg" style="flex: 0 0 auto; min-width: 150px;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="info-label text-primary font-weight-bold mb-0" style="font-size:0.82rem; letter-spacing:0.5px;">
                                <i class="fas fa-users mr-1"></i> EMPLOYEES
                            </span>
                            <span class="badge badge-pill badge-primary px-2.5 py-1 small font-weight-bold" style="font-size: 0.78rem;">
                                {{ count($team) }}
                            </span>
                        </div>
                        
                        <div class="team-section-container justify-content-start mt-1">
                            @forelse($team as $index => $member)
                                @php
                                    $img = is_object($member) && isset($member->emp_photodest) 
                                        ? (\App\Facades\FileStorage::url($member->emp_photodest) ?: asset('dist/img/profile-1.jfif'))
                                        : (is_array($member) && isset($member['img']) ? $member['img'] : asset('dist/img/profile-1.jfif'));
                                    $name = is_object($member) ? ($member->emp_name ?? '') : ($member['name'] ?? '');
                                    $role = is_object($member) ? ($member->emp_title ?: 'Project Staff') : ($member['role'] ?? 'Project Staff');
                                    $email = is_object($member) ? ($member->emp_email ?: 'N/A') : ($member['email'] ?? 'N/A');
                                    $phone = is_object($member) ? ($member->emp_mobile ?: 'N/A') : ($member['phone'] ?? 'N/A');
                                    $empId = is_object($member) ? ($member->emp_id ?? '') : ($member['emp_id'] ?? '');
                                @endphp
                                @if($index < 4)
                                    <div class="team-avatar-wrapper" style="width:38px; height:38px;"
                                         onclick="openEmployeeModal('{{ $name }}','{{ $role }}','{{ $img }}','{{ $email }}','{{ $phone }}','{{ $empId }}')"
                                         title="{{ $name }} ({{ $role }})">
                                        <img src="{{ $img }}" alt="{{ $name }}" style="width:38px; height:38px;" onerror="this.onerror=null; this.src='{{ asset('dist/img/profile-1.jfif') }}';">
                                    </div>
                                @endif
                            @empty
                                <span class="text-muted small" style="font-size:0.84rem;">No staff assigned</span>
                            @endforelse

                            @if(count($team) > 4)
                                <button type="button" class="more-staff-btn" style="width:38px; height:38px; font-size:11px;" onclick="openAllStaffModal()" title="View all staff">
                                    +{{ count($team) - 4 }}
                                </button>
                            @endif
                        </div>
                        @if(count($team) > 0)
                            <small class="text-muted d-block mt-1 font-weight-bold" style="font-size:0.74rem;">
                                {{ count($team) }} Active Personnel
                            </small>
                        @endif
                    </div>

                </div>
            </div>
<style>
/* ===== START & END RED SQUARES ===== */
.edge-box {
    position: absolute;
    top: 50%;
    width: 44px;
    height: 34px;
    background: #dc3545;
    color: #fff;
    font-size: 0.5rem;
    font-weight: 700;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 4;
}
/* Stay fully inside the track (no horizontal bleed past container edges) */
.edge-box.edge-start { left: 0; transform: translate(0, -50%); }
.edge-box.edge-end { right: 0; left: auto; transform: translate(0, -50%); }

/* ===== DIAMOND MILESTONE ===== */
.step-dot {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 2px solid #ffc107;
    background: transparent;
    transform: rotate(0deg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffc107;
    font-weight: bold;
    transition: all 0.3s ease;
}

.step-dot * {
    transform: rotate(0deg);
    transition: all 0.3s ease;
}

.step-item.completed .step-dot {
    background: #28a745;
    border-color: #28a745;
    color: #fff;
    transform: rotate(45deg);
}

.step-item.completed .step-dot * {
    transform: rotate(-45deg);
}

.step-item.active .step-dot {
    border-color: var(--rd-primary-600);
    color: var(--rd-primary-600);
}

/* ===== TODAY BUBBLE WITH GLOW CURSOR ===== */
.today-bubble {
    position: absolute;
    top: -40px;
    background: var(--rd-primary-600);
    color: #fff;
    padding: 4px 9px;
    border-radius: 14px;
    font-size: 0.58rem;
    font-weight: 700;
    white-space: nowrap;
}

.today-glow {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--rd-primary-600);
    box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
    animation: pulse-blue 1.5s infinite;
    position: absolute;
    top: 0;
    left: 50%;
    transform: translate(-50%, -50%);
}
@keyframes pulse-blue {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

/* ===== ACHIEVED DATE UNDER DIAMOND (FIXED: no longer overlaps target diamond/date) ===== */
.achieved-wrap {
    position: absolute;
    bottom: -42px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.58rem;
    font-weight: 700;
    color: #28a745;
    white-space: nowrap;
}

.achieved-wrap .flag {
    font-size: 11px;
    color: #28a745;

}

.achieved-marker{
    position: absolute;
    top: calc(50% + 36px);
    transform: translate(-50%, 0);
    z-index: 6;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}

/* Short connector line linking the diamond/target-date to the achieved marker below */
.achieved-marker::before{
    content: "";
    position: absolute;
    top: -33px;
    left: 50%;
    width: 1px;
    height: 28px;
    background: currentColor;
    opacity: 0.3;
}

.achieved-marker i{
    font-size: 10px;
}

.achieved-marker .achieved-date{
    font-size: 0.55rem;
    font-weight: 700;
    white-space: nowrap;
}

/* ON TIME / EARLY */
.achieved-marker.ontime i,
.achieved-marker.ontime .achieved-date{
    color: #28a745;
}

/* LATE */
.achieved-marker.late i,
.achieved-marker.late .achieved-date{
    color: #dc3545;
}


</style>
<!-- ================= PROGRESS ROW ================= -->
<div class="row mt-4">

    <div class="col-lg-12">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold m-0 text-dark">
                <i class="fas fa-chart-line text-primary mr-2"></i> Milestone Progress
            </h6>
            <span class="badge badge-light border">
                {{ $completedMilestones }} / {{ $totalMilestones }} Completed
            </span>
        </div>

        <div class="steps-wrapper">
            <div class="steps-container mb-4">

    <div class="steps-track">
        <div class="steps-fill" style="width: {{ $overallPercent }}%;"></div>
    </div>

    {{-- START BOX --}}
    <div class="edge-box edge-start">
        START
        <small>{{ $prjStart->format('d M Y') }}</small>
    </div>

    {{-- TODAY MARKER --}}
    <div style="position:absolute; left:{{ $overallTimePercent }}%; top:50%; transform:translate(-50%, -50%); z-index: 5;">
        <div class="today-bubble">{{ $today->format('d M Y') }}</div>
        <div class="today-glow"></div>
    </div>

    {{-- MILESTONES --}}
@foreach($milestones as $ms)
    @php
        $msStatus = strtolower(trim($ms->msn_status ?? ''));
        $isCompleted = ($msStatus === 'completed');
        $isActive = (!$isCompleted && optional($nextMilestone)->msn_id === $ms->msn_id);

        $stepClass = '';
        if ($isCompleted) {
            $stepClass = 'completed';
        } elseif ($isActive) {
            $stepClass = 'active';
        }

        $targetDate   = $ms->msn_targetdt ? \Carbon\Carbon::parse($ms->msn_targetdt) : null;
        $achievedDate = $ms->msn_achvdt ? \Carbon\Carbon::parse($ms->msn_achvdt) : null;

        $isLate = false;
        if ($achievedDate && $targetDate) {
            $isLate = $achievedDate->gt($targetDate);
        } elseif (!$achievedDate && $targetDate && $today->gt($targetDate) && !$isCompleted) {
            $isLate = true;
        }

        // Date-based milestone position on timeline track
        if ($targetDate && $totalDaysSpan > 0) {
            $daysFromStart = $prjStart->diffInDays($targetDate, false);
            $rawPercent = ($daysFromStart / max(1, $totalDaysSpan)) * 100;
            $rawPercent = max(0, min(100, $rawPercent));
            $milestonePercent = $trackInsetStart + ($rawPercent * $trackInsetRange / 100);
        } elseif ($totalMilestones > 1) {
            $milestonePercent = $trackInsetStart + ($loop->index / ($totalMilestones - 1)) * $trackInsetRange;
        } else {
            $milestonePercent = 50;
        }
        $milestonePercent = round(max($trackInsetStart, min($trackInsetStart + $trackInsetRange, $milestonePercent)), 1);

        if ($achievedDate && $targetDate) {
            $diffDays = $targetDate->diffInDays($achievedDate, false);
            $oneDayPercent = ($trackInsetRange / 100) * (100 / max(1, $totalDaysSpan));
            $achievedPercent = $milestonePercent + ($diffDays * $oneDayPercent);
            $achievedPercent = max(1, min(99, $achievedPercent));
        } else {
            $achievedPercent = $milestonePercent;
        }

        $msLabel = 'MS-' . ($ms->msn_id ?: $loop->iteration);
    @endphp

    <div class="step-item {{ $stepClass }}" style="left: {{ $milestonePercent }}%;"
     onclick="openMilestoneDetail(
        '{{ $msLabel }}',
        '{{ $targetDate ? $targetDate->format('d M Y') : 'TBD' }}',
        '{{ $achievedDate ? $achievedDate->format('d M Y') : 'Not achieved' }}',
        '{{ $isCompleted ? 'Completed' : ($isLate ? 'Late' : 'On Time') }}'
     )">

        {{-- MS LABEL --}}
        <div class="step-label">{{ $msLabel }}</div>

        {{-- DIAMOND --}}
        <div class="step-dot">
            @if($stepClass === 'completed')
                <i class="fas fa-check text-white" style="font-size:0.55rem"></i>
            @else
                {{ $ms->msn_id ?: $loop->iteration }}
            @endif
        </div>

        {{-- TARGET DATE (UNDER DIAMOND) --}}
        <div class="step-date">
            {{ $targetDate ? $targetDate->format('d M Y') : 'TBD' }}
        </div>
    </div>

    {{-- ACHIEVED FLAG --}}
   @if($achievedDate)
    <div class="achieved-marker {{ $isLate ? 'late' : 'ontime' }}"
         style="left: {{ $achievedPercent }}%;">
        <i class="fas fa-flag"></i>
        <span class="achieved-date">{{ $achievedDate->format('d M Y') }}</span>
    </div>
   @endif

@endforeach


    {{-- END BOX --}}
    <div class="edge-box edge-end">
        END
        <small>{{ $edc ? $edc->format('d M Y') : $prjEnd->format('d M Y') }}</small>
    </div>

</div>


    </div>

</div>
<div class="modal fade" id="milestoneDetailModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="msTitle"></h5>
        <button class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><strong>Target Date:</strong> <span id="msTarget"></span></p>
        <p><strong>Achieved Date:</strong> <span id="msAchieved"></span></p>
        <p><strong>Status:</strong> <span id="msStatus"></span></p>
      </div>
    </div>
  </div>
</div>


<!-- ================= SECOND ROW ================= -->
<div class="row mt-4">

    <!-- LEFT & MIDDLE (Milestones Detail - Wide & Open) -->
    <div class="col-xl-9 col-lg-8 col-md-12 mb-4" id="milestones">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold m-0 text-dark" style="font-size: 0.96rem;">
                <i class="fas fa-list-ol text-primary mr-2"></i> Milestones Detail
            </h6>
            @if(!($readOnly ?? false) && Auth::check() && Auth::user()->can('update', $project) && !in_array(strtolower(trim($project->prj_status ?? '')), ['completed', 'cancelled'], true))
                <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold px-3" onclick="openAddMilestoneModal()">
                    <i class="fas fa-plus mr-1"></i> Add Milestone
                </button>
            @endif
        </div>

        <div class="milestone-container shadow-sm">
            <div class="milestone-scroll-box">
                <div class="table-responsive">
                <table class="table table-custom w-100 m-0">
                    <thead>
                        <tr>
                            <th style="width: 85px;">#</th>
                            <th style="width: 120px;">Type</th>
                            <th>Description</th>
                            <th style="width: 140px;">Target</th>
                            <th style="width: 150px;" class="text-center">Achieved</th>
                            <th style="width: 150px;" class="text-center">Status</th>
                            <th style="width: 100px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($milestones as $milestone)
                            @php
                                $mStatus = strtolower(trim($milestone->msn_status ?? ''));
                                $targetDt = $milestone->msn_targetdt ? \Carbon\Carbon::parse($milestone->msn_targetdt) : null;
                                $achvDt = $milestone->msn_achvdt ? \Carbon\Carbon::parse($milestone->msn_achvdt) : null;
                                $startDt = $milestone->msn_startdt ? \Carbon\Carbon::parse($milestone->msn_startdt) : null;
                                $canManageMilestone = !($readOnly ?? false) && Auth::check() && Auth::user()->can('update', $project);
                                $canEditMilestone = $canManageMilestone && $mStatus !== 'completed';
                                $certUrl = !empty($milestone->msn_cc_path) ? route('milestone.certificate', $milestone->msn_idd) : '';
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-dark">MS-{{ $milestone->msn_id ?: $loop->iteration }}</td>
                                <td><span class="badge badge-light border text-dark font-weight-bold" style="font-size:0.75rem;">{{ $milestone->msn_type ?: 'Milestone' }}</span></td>
                                <td class="font-weight-bold text-dark" style="font-size:0.90rem;">{{ $milestone->msn_desc }}</td>
                                <td class="font-weight-bold text-muted" style="font-size:0.86rem;">{{ $targetDt ? $targetDt->format('d M, Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    @if($achvDt)
                                        <span class="text-success font-weight-bold" style="font-size:0.86rem;">
                                            <i class="fas fa-check-circle mr-1"></i> {{ $achvDt->format('d M, Y') }}
                                        </span>
                                    @else
                                        @if(!($readOnly ?? false))
                                            <button class="btn btn-xs btn-outline-primary rounded-pill px-2.5 font-weight-bold"
                                                    onclick="openAchieveModal('{{ $milestone->msn_idd }}')">
                                                <i class="fas fa-calendar-check mr-1"></i> Mark
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($mStatus === 'completed')
                                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-check mr-1"></i> Completed</span>
                                    @elseif($mStatus === 'in progress')
                                        <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-spinner fa-spin mr-1"></i> In Progress</span>
                                    @elseif($mStatus === 'awaited')
                                        <span class="badge badge-info px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-hourglass-half mr-1"></i> Awaited</span>
                                    @elseif($mStatus === 'held up')
                                        <span class="badge badge-danger px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-pause-circle mr-1"></i> Held Up</span>
                                    @elseif($mStatus === 'not started')
                                        <span class="badge badge-secondary px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-circle mr-1" style="font-size: 7px;"></i> Not Started</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold" style="font-size:0.76rem;">{{ $milestone->msn_status ?: 'Pending' }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-xs {{ $canEditMilestone ? 'btn-outline-primary' : 'btn-outline-secondary' }} rounded-pill px-2.5 font-weight-bold"
                                            onclick="openMilestonePopup(this)"
                                            data-editable="{{ $canEditMilestone ? '1' : '0' }}"
                                            data-msn-idd="{{ $milestone->msn_idd }}"
                                            data-msn-id="{{ $milestone->msn_id }}"
                                            data-msn-type="{{ $milestone->msn_type ?: 'Milestone' }}"
                                            data-msn-desc="{{ $milestone->msn_desc }}"
                                            data-msn-startdt="{{ $startDt ? $startDt->format('Y-m-d') : '' }}"
                                            data-msn-startdt-label="{{ $startDt ? $startDt->format('d M y') : '—' }}"
                                            data-msn-targetdt="{{ $targetDt ? $targetDt->format('Y-m-d') : '' }}"
                                            data-msn-targetdt-label="{{ $targetDt ? $targetDt->format('d M y') : 'N/A' }}"
                                            data-msn-achvdt="{{ $achvDt ? $achvDt->format('Y-m-d') : '' }}"
                                            data-msn-achvdt-label="{{ $achvDt ? $achvDt->format('d M y') : '—' }}"
                                            data-msn-comp="{{ $milestone->msn_comp ?? '' }}"
                                            data-msn-status="{{ $milestone->msn_status ?: 'Not started' }}"
                                            data-has-cert="{{ !empty($milestone->msn_cc_path) ? '1' : '0' }}"
                                            data-cert-editable="{{ $canManageMilestone ? '1' : '0' }}"
                                            data-cert-url="{{ $certUrl }}"
                                            title="{{ $canEditMilestone ? 'Edit Milestone Details' : ($canManageMilestone ? 'View Details / Upload Completion Certificate' : 'View Milestone Details') }}">
                                        <i class="fas {{ $canEditMilestone ? 'fa-edit' : 'fa-eye' }} mr-1"></i>{{ $canEditMilestone ? 'Edit' : 'View' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-flag-checkered fa-2x mb-2 text-muted" style="opacity: 0.3;"></i>
                                    <p class="font-weight-bold mb-0">No milestones defined yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

    </div>

    <!-- FAR RIGHT (Attachments Widget - Corner Pinned) -->
    <div class="col-xl-3 col-lg-4 col-md-12 mb-4" id="attachments">

        <div class="sticky-top" style="top:20px;">
            <div class="attachments-wrapper">
                @include('partials.attachments_widget', [
                    'module' => 'prj',
                    'objectId' => $project->prj_id,
                    'title' => 'Project Attachments',
                    'defaultSlots' => ['Project Proposal', 'URD', 'Work Order', 'PPF'],
                    'attachments' => $allAttachments ?? $project->attachments,
                    'canUpload' => Auth::check(),
                    'canDelete' => false,
                ])
            </div>
        </div>

    </div>

</div>

            </div> {{-- /.card-body --}}
        </div> {{-- /.card --}}
    </div> {{-- /.container-fluid --}}
</div> {{-- /.content-wrapper --}}


    <!-- MODALS -->
    <!-- Milestone View / Edit / Add Popup Modal (Legacy prj_milestones_details style) -->
    <div class="modal fade glass-modal" id="milestonePopupModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="msPopupModalTitle">
                        <i class="fas fa-flag-checkered text-primary mr-2"></i> Milestone Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                {{-- READ-ONLY VIEW (for Completed milestones or read-only mode) --}}
                <div id="msPopupReadOnly" style="display: none;">
                    <div class="modal-body px-4 py-3">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fas fa-lock mr-1"></i> Completed milestones are locked and cannot be edited directly.
                        </div>
                        <div class="row py-2 border-bottom">
                            <div class="col-4 text-muted font-weight-bold">Number</div>
                            <div class="col-8 font-weight-bold text-dark" id="ro_msn_id"></div>
                        </div>
                        <div class="row py-2 border-bottom">
                            <div class="col-4 text-muted font-weight-bold">Type</div>
                            <div class="col-8 text-dark" id="ro_msn_type"></div>
                        </div>
                        <div class="row py-2 border-bottom">
                            <div class="col-4 text-muted font-weight-bold">Description</div>
                            <div class="col-8 text-dark font-weight-bold" id="ro_msn_desc"></div>
                        </div>
                        <div class="row py-2 border-bottom" id="ro_activity_row" style="display: none;">
                            <div class="col-4 text-muted font-weight-bold">Start Date / %</div>
                            <div class="col-8 text-dark"><span id="ro_msn_startdt"></span> &nbsp;|&nbsp; <span id="ro_msn_comp"></span>%</div>
                        </div>
                        <div class="row py-2 border-bottom">
                            <div class="col-4 text-muted font-weight-bold">Target Date</div>
                            <div class="col-8 text-dark" id="ro_msn_targetdt"></div>
                        </div>
                        <div class="row py-2 border-bottom">
                            <div class="col-4 text-muted font-weight-bold">Achieved Date</div>
                            <div class="col-8 text-success font-weight-bold" id="ro_msn_achvdt"></div>
                        </div>
                        <div class="row py-2 align-items-center">
                            <div class="col-4 text-muted font-weight-bold">Status</div>
                            <div class="col-4"><span class="badge badge-success px-3 py-1" id="ro_msn_status"></span></div>
                            <div class="col-4 text-right small">
                                <span class="text-muted mr-1">Completion Certificate:</span>
                                <span id="ro_cert_box"></span>
                            </div>
                        </div>

                        {{-- Certificate-only upload: allowed even on locked (Completed) milestones --}}
                        <form id="msCertForm" method="POST" enctype="multipart/form-data" class="mt-2 pt-3 border-top" style="display: none;">
                            @csrf
                            <div class="form-group row align-items-center mb-1">
                                <label class="col-sm-4 col-form-label text-muted font-weight-bold" for="ro_cc_file">
                                    <i class="fas fa-file-upload mr-1"></i> <span id="ro_cert_upload_label">Upload Certificate</span>
                                </label>
                                <div class="col-sm-8 d-flex align-items-center flex-wrap" style="gap: 8px;">
                                    <input type="file" name="completion_certificate" id="ro_cc_file" class="form-control-file form-control-sm" style="width: auto;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
                                        <i class="fas fa-upload mr-1"></i> Save
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block">Only the completion certificate is updated &mdash; all other milestone fields remain locked. (PDF / JPG / PNG / DOC, max 10 MB)</small>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between bg-light">
                        <small class="text-muted">Record ID: <span id="ro_msn_idd"></span></small>
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Close</button>
                    </div>
                </div>

                {{-- EDITABLE / ADD FORM (for In Progress / Not Started / Awaited / Held Up / New) --}}
                <form id="msPopupForm" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="form-group row align-items-center mb-2">
                            <label class="col-sm-3 col-form-label text-muted font-weight-bold">Number</label>
                            <div class="col-sm-4">
                                <input type="text" id="ed_msn_id_display" class="form-control form-control-sm bg-light font-weight-bold" readonly>
                            </div>
                            <label class="col-sm-2 col-form-label text-muted font-weight-bold text-sm-right">Type</label>
                            <div class="col-sm-3">
                                <select name="msn_type" id="ed_msn_type" class="form-control form-control-sm" onchange="toggleActivityFields()">
                                    <option value="Milestone">Milestone</option>
                                    <option value="Activity">Activity</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <label class="col-sm-3 col-form-label text-muted font-weight-bold">Description <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea name="msn_desc" id="ed_msn_desc" rows="2" class="form-control form-control-sm" required maxlength="255"></textarea>
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-2" id="ed_activity_fields" style="display: none;">
                            <label class="col-sm-3 col-form-label text-muted font-weight-bold">Start Date <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="date" name="msn_startdt" id="ed_msn_startdt" class="form-control form-control-sm">
                            </div>
                            <label class="col-sm-2 col-form-label text-muted font-weight-bold text-sm-right">% Comp.</label>
                            <div class="col-sm-3">
                                <input type="number" name="msn_comp" id="ed_msn_comp" min="0" max="100" class="form-control form-control-sm" placeholder="0-100">
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-2">
                            <label class="col-sm-3 col-form-label text-muted font-weight-bold">Target Date</label>
                            <div class="col-sm-4">
                                <input type="date" name="msn_targetdt" id="ed_msn_targetdt" class="form-control form-control-sm">
                            </div>
                            <label class="col-sm-2 col-form-label text-muted font-weight-bold text-sm-right">Status</label>
                            <div class="col-sm-3">
                                <select name="msn_status" id="ed_msn_status" class="form-control form-control-sm" onchange="toggleAchievedField()">
                                    <option value="Not started">Not started</option>
                                    <option value="Awaited">Awaited</option>
                                    <option value="Held up">Held up</option>
                                    <option value="In progress">In progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-2">
                            <label class="col-sm-3 col-form-label text-muted font-weight-bold">
                                Achieved Date <span class="text-danger" id="ed_achv_req_star" style="display:none;">*</span>
                            </label>
                            <div class="col-sm-4">
                                <input type="date" name="msn_achvdt" id="ed_msn_achvdt" class="form-control form-control-sm" max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-sm-5">
                                <small class="text-warning font-weight-bold" id="ed_complete_lock_note" style="display: none;">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Completing this milestone will lock it from further edits.
                                </small>
                            </div>
                        </div>

                        <div class="form-group row align-items-center mb-0">
                            <label class="col-sm-3 col-form-label text-muted font-weight-bold">Completion Certificate</label>
                            <div class="col-sm-9 d-flex align-items-center flex-wrap" style="gap: 10px;">
                                <input type="file" name="completion_certificate" id="ed_cc_file" class="form-control-file form-control-sm" style="width: auto;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <span id="ed_existing_cert"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between bg-light">
                        <small class="text-muted">Record ID: <span id="ed_msn_idd_label">—</span></small>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Save Milestone
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Project Details Modal (triggered by Pencil Icon next to + MPR) -->
    @if(!($readOnly ?? false) && Auth::check() && Auth::user()->can('update', $project))
    <div class="modal fade glass-modal" id="editProjectDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-pencil-alt text-primary mr-2"></i> Edit Project Details ({{ $project->prj_code }})
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('projects.update-details', $project->prj_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 py-3">
                        @php
                            $pendingAmendmentCase = app(\App\Services\ProjectAmendmentService::class)->pendingCaseFor($project->prj_id);
                        @endphp
                        @if($pendingAmendmentCase)
                            <div class="alert alert-warning py-2 px-3 small mb-3">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                Amendment case <strong>{{ $pendingAmendmentCase->gcs_file_no }}</strong> for this project is already in process
                                (currently with {{ $pendingAmendmentCase->currentHolder->acc_name ?? 'the initiating division' }}).
                                <a href="{{ route('cases.show', $pendingAmendmentCase->gcs_id) }}" class="font-weight-bold">Open case file</a>
                                to revise the proposed values.
                            </div>
                        @else
                            <div class="alert alert-info py-2 px-3 small mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Changes made here are <strong>not saved directly</strong>. A case file is raised automatically with a
                                system generated justification; the project record is updated only after the case is approved.
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-8 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Project Title <span class="text-danger">*</span></label>
                                <input type="text" name="prj_title" class="form-control form-control-sm" value="{{ old('prj_title', $project->prj_title) }}" required maxlength="255">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Status <span class="text-danger">*</span></label>
                                @php
                                    $prjStatuses = [
                                        'Under assignment',
                                        'Under feasibility',
                                        'Approval awaited',
                                        'Funds awaited',
                                        'Work in progress',
                                        'Warranty in progress',
                                        'Final Acceptance Awaited',
                                        'Completed',
                                        'Cancelled',
                                    ];
                                    $currPrjStatus = $project->prj_status ?? '';
                                    if ($currPrjStatus !== '' && !collect($prjStatuses)->contains(fn($s) => strcasecmp($s, $currPrjStatus) === 0)) {
                                        array_unshift($prjStatuses, $currPrjStatus);
                                    }
                                @endphp
                                <select name="prj_status" class="form-control form-control-sm" required>
                                    @foreach($prjStatuses as $st)
                                        <option value="{{ $st }}" {{ strcasecmp($currPrjStatus, $st) === 0 ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Sponsor / Agency</label>
                                <input type="text" name="prj_sponsor" class="form-control form-control-sm" value="{{ old('prj_sponsor', $project->prj_sponsor) }}" maxlength="255">
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Proposed Cost (Rs.)</label>
                                <input type="number" name="prj_propcost" class="form-control form-control-sm" value="{{ old('prj_propcost', $project->prj_propcost) }}" min="0">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted mb-1">Scope of Work</label>
                            <textarea name="prj_scope" rows="2" class="form-control form-control-sm" maxlength="255">{{ old('prj_scope', $project->prj_scope) }}</textarea>
                        </div>

                        <h6 class="text-primary font-weight-bold small text-uppercase border-bottom pb-1 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i> Key Dates
                        </h6>
                        <div class="row">
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Received Date</label>
                                <input type="date" name="prj_rcptdt" class="form-control form-control-sm" value="{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Assigned Date</label>
                                <input type="date" name="prj_assigndt" class="form-control form-control-sm" value="{{ $project->prj_assigndt ? \Carbon\Carbon::parse($project->prj_assigndt)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Proposal Date</label>
                                <input type="date" name="prj_propdt" class="form-control form-control-sm" value="{{ $project->prj_propdt ? \Carbon\Carbon::parse($project->prj_propdt)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Approval Date</label>
                                <input type="date" name="prj_aprvdt" class="form-control form-control-sm" value="{{ $project->prj_aprvdt ? \Carbon\Carbon::parse($project->prj_aprvdt)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Start Date</label>
                                <input type="date" name="prj_startdt" class="form-control form-control-sm" value="{{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">EDC (Est. End Date)</label>
                                <input type="date" name="prj_estenddt" class="form-control form-control-sm" value="{{ $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Actual Completion Date</label>
                                <input type="date" name="prj_enddt" class="form-control form-control-sm" value="{{ $project->prj_enddt ? \Carbon\Carbon::parse($project->prj_enddt)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="form-group mb-0 mt-1">
                            <label class="small font-weight-bold text-muted mb-1">Remarks</label>
                            <textarea name="prj_rem" rows="2" class="form-control form-control-sm">{{ old('prj_rem', $project->prj_rem) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-between">
                        <small class="text-muted">
                            <i class="fas fa-route mr-1"></i> Routed as an e-office case file for approval.
                        </small>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold">
                                <i class="fas fa-folder-plus mr-1"></i> Raise Amendment Case
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Achieve Date Modal -->
    <div class="modal fade glass-modal" id="achieveDateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-check-circle mr-2"></i>Enter Achieved Date</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('milestone.complete') }}" method="POST">
                    @csrf
                    <input type="hidden" name="msn_id" id="modal_msn_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Date <span class="text-danger">*</span></label>
                            <input type="date" name="achieved_date" class="form-control" required max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark as Completed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Other Docs Modal -->
    <div class="modal fade glass-modal" id="otherDocsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-copy text-primary mr-2"></i>Other Attachments</h5>
                    <button class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="card card-body bg-light border mb-4">
                        <h6 class="text-primary font-weight-bold mb-3">Upload New Document</h6>
                        <form action="{{ route('projects.upload-other', $project->prj_id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-5"><input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document Name" required></div>
                                <div class="col-md-5"><input type="file" name="doc_file" class="form-control-file form-control-sm" required></div>
                                <div class="col-md-2"><button type="submit" class="btn btn-success btn-sm btn-block">Upload</button></div>
                            </div>
                        </form>
                    </div>
                    <h6 class="font-weight-bold text-dark">Existing Files</h6>
                    <div class="table-responsive">
                    <table class="table table-bordered table-sm mt-2 bg-white">
                        <thead class="bg-light"><tr><th>#</th><th>Document Name</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($allAttachments->whereNotIn('jat_type', $fixedDocs) as $index => $att)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><i class="fas fa-file-alt text-muted mr-2"></i> <strong>{{ $att->jat_type }}</strong></td>
                                    <td>
                                        <a href="{{ route('attachment.view', $att->jat_id) }}" target="_blank" class="btn btn-xs btn-info px-2 rd-live-file-view" data-file-url="{{ route('attachment.view', $att->jat_id) }}" data-file-title="{{ $att->jat_type ?? 'Document' }}">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No additional documents.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee & All Staff Modals (same as before) -->
    <div class="modal fade" id="employeeDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <img src="" id="empModalImg" class="emp-modal-img shadow-sm" onerror="this.onerror=null; this.src='{{ asset('dist/img/profile-1.jfif') }}';">
                    <h4 id="empModalName" class="font-weight-bold mb-1"></h4>
                    <p id="empModalRole" class="text-primary mb-4"></p>
                    <div class="text-left mt-3">
                        <div class="emp-detail-row"><span class="emp-label">Email</span><span id="empModalEmail" class="text-dark"></span></div>
                        <div class="emp-detail-row"><span class="emp-label">Phone</span><span id="empModalPhone" class="text-dark"></span></div>
                    </div>
                    <a href="#" id="empModalLink" class="btn btn-primary btn-block mt-4">More Details</a>
                    <button type="button" class="btn btn-secondary btn-block mt-2" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="allStaffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Project Team</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                    <table class="table table-striped m-0">
                        <thead class="bg-primary text-white"><tr><th>Image</th><th>Name</th><th>Role</th><th>Contact</th></tr></thead>
                        <tbody>
                            @foreach($team as $member)
                                @php
                                    $img = \App\Facades\FileStorage::url($member->emp_photodest) ?: asset('dist/img/profile-1.jfif');
                                    $name = $member->emp_name;
                                    $role = $member->emp_title ?: 'Project Staff';
                                    $phone = $member->emp_mobile ?: 'N/A';
                                @endphp
                                <tr>
                                    <td><img src="{{ $img }}" class="rounded-circle" width="35" onerror="this.onerror=null; this.src='{{ asset('dist/img/profile-1.jfif') }}';"></td>
                                    <td class="font-weight-bold">{{ $name }}</td>
                                    <td>{{ $role }}</td>
                                    <td>{{ $phone }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- ============ PREMIUM FINANCIAL INTELLIGENCE DASHBOARD MODAL ============ --}}
@if($head)
<div class="modal fade" id="financialIntelligenceModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: var(--rd-surface); border: 2px solid var(--rd-primary-600); border-radius: 12px; overflow: hidden; box-shadow: var(--rd-shadow-lg);">
            <div class="modal-header border-bottom border-dark py-2 px-4 d-flex align-items-center justify-content-between" style="background: var(--rd-neutral-50);">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="font-size: 24px; color: var(--rd-accent);"><i class="fas fa-chart-network"></i></div>
                    <div>
                        <h5 class="modal-title rajdhani font-weight-bold text-white mb-0" style="letter-spacing: 2px;">FINANCIAL INTELLIGENCE REPORT</h5>
                        <div class="small text-muted rajdhani">{{ $head->head_name }} | DATED {{ date('d M y') }}</div>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-50 hover-opacity-100" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body p-0" style="background: var(--rd-surface);">
                {{-- Top Summary bar --}}
                <div class="row no-gutters border-bottom border-dark" style="background: var(--rd-neutral-200);">
                    <div class="col-md-3 border-right border-dark p-3">
                        <div class="small text-muted rajdhani font-weight-bold">ALLOCATION</div>
                        <div class="h5 mb-0 text-white font-weight-bold rajdhani">{{ number_format($head->allocation) }}</div>
                    </div>
                    <div class="col-md-3 border-right border-dark p-3">
                        <div class="small text-muted rajdhani font-weight-bold">MTSS SHARE</div>
                        <div class="h5 mb-0 text-white font-weight-bold rajdhani">{{ number_format($head->mtss_share) }}</div>
                    </div>
                    <div class="col-md-3 border-right border-dark p-3">
                        <div class="small text-muted rajdhani font-weight-bold">RDW SHARE</div>
                        <div class="h5 mb-0 text-info font-weight-bold rajdhani">{{ number_format($head->rdw_share ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 p-3">
                        <div class="small text-muted rajdhani font-weight-bold">CSRF SHARE</div>
                        <div class="h5 mb-0 text-white font-weight-bold rajdhani">{{ number_format($head->csrf_share ?? 0) }}</div>
                    </div>
                </div>

                <div class="row no-gutters">
                    {{-- Left Pane: Detailed Metrics Table --}}
                    <div class="col-xl-5 border-right border-dark p-4" style="background: var(--rd-neutral-200);">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h6 class="rajdhani text-info font-weight-bold mb-0" style="letter-spacing: 1px;"><i class="fas fa-table mr-2"></i>PROJECT SNAPSHOT</h6>
                            <div class="small text-muted rajdhani">FIGURES IN PKR</div>
                        </div>

                        <div class="fin-table-modern table-responsive rounded border border-dark overflow-auto">
                            <table class="table table-sm table-dark mb-0 rajdhani" style="font-size: 13px;">
                                <thead style="background: var(--rd-neutral-50);">
                                    <tr class="text-muted">
                                        <th class="pl-3 border-0">METRIC</th>
                                        <th class="text-right border-0" style="color: #4da3ff;">PROJECT</th>
                                        <th class="text-right border-0" style="color: #ffb84d;">CSRF</th>
                                        @if($showProjectActualSection ?? true)
                                        <th class="text-right pr-3 border-0" style="color: #4dff88;">ACTUAL</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                     <tr style="background: rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255,255,255,0.08);">
                                         <td class="pl-3 font-weight-bold text-white"><i class="fas fa-coins text-warning mr-1"></i> Allocated</td>
                                         <td class="text-right font-weight-bold" style="color: #4da3ff;">{{ number_format($head->pcc_share ?? 0) }}</td>
                                         <td class="text-right font-weight-bold" style="color: #ffb84d;">{{ number_format($head->csrf_share ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3 font-weight-bold" style="color: #4dff88;">{{ number_format($head->allocation ?? 0) }}</td>
                                         @endif
                                     </tr>
                                     <tr>
                                         <td class="pl-3 text-muted">Received</td>
                                         <td class="text-right" style="color: #4da3ff;">{{ number_format($head->pcc_received ?? 0) }}</td>
                                         <td class="text-right" style="color: #ffb84d;">{{ number_format($head->cf_received ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3 text-muted">--</td>
                                         @endif
                                     </tr>
                                     <tr>
                                         <td class="pl-3 text-muted">Expenditure</td>
                                         <td class="text-right text-danger">{{ number_format($head->pcc_expenditure ?? 0) }}</td>
                                         <td class="text-right text-danger">{{ number_format($head->cf_expenditure ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->prj_expenditure ?? 0) }}</td>
                                         @endif
                                     </tr>
                                     <tr style="background: var(--rd-neutral-50);">
                                         <td class="pl-3 text-info font-weight-bold">Balance</td>
                                         <td class="text-right text-info font-weight-bold">{{ number_format($head->pcc_balance ?? 0) }}</td>
                                         <td class="text-right text-info font-weight-bold">{{ number_format($head->cf_balance ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3 text-muted">--</td>
                                         @endif
                                     </tr>
                                     <tr>
                                         <td class="pl-3 text-muted">Commitments</td>
                                         <td class="text-right text-warning">{{ number_format($head->pcc_commitments ?? 0) }}</td>
                                         <td class="text-right text-warning">{{ number_format($head->cf_commitments ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->prj_commitments ?? 0) }}</td>
                                         @endif
                                     </tr>
                                     <tr>
                                         <td class="pl-3 text-muted">In Process</td>
                                         <td class="text-right text-muted">{{ number_format($head->pcc_in_process ?? 0) }}</td>
                                         <td class="text-right text-muted">{{ number_format($head->cf_in_process ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->prj_in_process ?? 0) }}</td>
                                         @endif
                                     </tr>
                                     <tr style="background: rgba(0,255,100,0.05);">
                                         <td class="pl-3 font-weight-bold text-success">Available</td>
                                         <td class="text-right font-weight-bold text-success">{{ number_format($head->pcc_available ?? 0) }}</td>
                                         <td class="text-right font-weight-bold text-success">{{ number_format($head->cf_available ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3 text-muted">--</td>
                                         @endif
                                     </tr>
                                     <tr>
                                         <td class="pl-3 text-muted">Yet to be Rcvd.</td>
                                         <td class="text-right" style="color: #4da3ff;">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td>
                                         <td class="text-right" style="color: #ffb84d;">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3 text-muted">--</td>
                                         @endif
                                     </tr>
                                     <tr style="background: var(--rd-neutral-50);">
                                         <td class="pl-3 text-white font-weight-bold">Remaining</td>
                                         <td class="text-right text-white font-weight-bold">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td>
                                         <td class="text-right text-white font-weight-bold">{{ number_format($head->cf_can_be_spent ?? 0) }}</td>
                                         @if($showProjectActualSection ?? true)
                                         <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->prj_remaining ?? 0) }}</td>
                                         @endif
                                     </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($showProjectActualSection ?? true)
                        {{-- Loan details section --}}
                        <div class="mt-4 pt-4 border-top border-dark">
                            <h6 class="rajdhani text-muted mb-3" style="font-size: 12px; letter-spacing: 2px;">LOAN DETAILS</h6>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Project exp. from this account</span>
                                <span class="text-white rajdhani font-weight-bold">{{ number_format($head->pcc_own_exp ?? 0) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Project exp. from other accounts</span>
                                <span class="text-warning rajdhani font-weight-bold">{{ number_format($head->others_loans_taken ?? 0) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Other's exp. from this account</span>
                                <span class="text-info rajdhani font-weight-bold">{{ number_format($head->pcc_loans_given ?? 0) }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Receivables section --}}
                        <div class="mt-4 pt-4 border-top border-dark">
                            <h6 class="rajdhani text-muted mb-3" style="font-size: 12px; letter-spacing: 2px;">RECEIVABLES</h6>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Comp. Milestones</span>
                                <span class="text-white rajdhani font-weight-bold">{{ number_format($head->receivable_completed) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Current Milestone</span>
                                <span class="text-white rajdhani font-weight-bold">{{ number_format($head->receivable_current) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mt-3 p-2 rounded" style="background: rgba(23,162,184,0.1); border: 1px solid rgba(23,162,184,0.2);">
                                <span class="text-info small rajdhani font-weight-bold">Available after Rcv.</span>
                                <span class="text-info rajdhani font-weight-bold">{{ number_format($head->available_after_receivables) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Pane: Visual Analytics & Subheads --}}
                    <div class="col-xl-7 p-4">
                        <div class="row">
                            {{-- Mini Category Charts --}}
                            @foreach(array_slice($subheads, 0, 3) as $idx => $sh)
                            <div class="col-md-4 mb-4">
                                <div class="subhead-mini-card p-3 rounded border border-dark text-center h-100" style="background: var(--rd-neutral-50);">
                                    <div class="d-flex justify-content-center mb-2" style="height: 80px;">
                                        <canvas id="chartShMini{{ $idx }}"></canvas>
                                    </div>
                                    <h6 class="rajdhani font-weight-bold text-info mb-1">{{ $sh['name'] }}</h6>
                                    <div class="text-white rajdhani" style="font-size: 14px;">{{ number_format($sh['allocation']) }}</div>
                                    <div class="mt-2 small text-muted rajdhani">UTILIZED: {{ number_format($sh['expenditure']) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Subhead Detailed Breakdown --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="dg-sec-label mb-3 text-info"><i class="fas fa-th-list fa-xs"></i> Category Metrics Breakdown</div>
                                <div class="table-responsive rounded border border-dark">
                                    <table class="table table-sm table-dark table-hover mb-0 rajdhani" style="font-size: 12px;">
                                        <thead style="background: var(--rd-neutral-200);">
                                            <tr class="text-muted small">
                                                <th class="pl-3">SUBHEAD</th>
                                                <th class="text-right">EXPENDITURE</th>
                                                <th class="text-right">COMMITMENTS</th>
                                                <th class="text-right pr-3">REMAINING</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subheads as $sh)
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">{{ $sh['name'] }}</td>
                                                <td class="text-right">{{ number_format($sh['expenditure']) }}</td>
                                                <td class="text-right">{{ number_format($sh['commitments']) }}</td>
                                                <td class="text-right pr-3 font-weight-bold {{ $sh['remaining'] < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($sh['remaining']) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Large Comparison Chart --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="p-4 rounded border border-dark" style="background: var(--rd-neutral-200); height: 280px;">
                                    <canvas id="finDetailedChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<script src="{{ asset('plugins/chartjs4/chart.umd.js') }}"></script>
<script>
$(function () {
    $('.knob').knob({
        draw: function () {
            if (this.$.data('skin') == 'tron') {
                var a = this.angle(this.cv),
                    sa = this.startAngle,
                    sat = this.startAngle,
                    ea = sa + a,
                    eat = sat + a,
                    r = true;
                this.g.lineWidth = this.lineWidth;
                this.o.cursor && (sat = eat - 0.3) && (eat = eat + 0.3);
                if (this.o.displayPrevious) {
                    ea = this.startAngle + this.angle(this.value);
                    this.o.cursor && (sa = ea - 0.3) && (ea = ea + 0.3);
                    this.g.beginPath();
                    this.g.strokeStyle = this.previousColor;
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                    this.g.stroke();
                }
                this.g.beginPath();
                this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                this.g.stroke();
                this.g.lineWidth = 2;
                this.g.beginPath();
                this.g.strokeStyle = this.o.fgColor;
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                this.g.stroke();
                return false;
            }
        }
    });
});

function openMilestoneDetail(title, target, achieved, status) {
    document.getElementById('msTitle').innerText = title;
    document.getElementById('msTarget').innerText = target;
    document.getElementById('msAchieved').innerText = achieved;
    document.getElementById('msStatus').innerText = status;
    $('#milestoneDetailModal').modal('show');
}

function openOtherDocsModal() { $('#otherDocsModal').modal('show'); }
function openEmployeeModal(name, role, img, email, phone, empId) {
    document.getElementById('empModalName').innerText = name;
    document.getElementById('empModalRole').innerText = role;
    document.getElementById('empModalImg').src = img;
    document.getElementById('empModalEmail').innerText = email;
    document.getElementById('empModalPhone').innerText = phone;
    document.getElementById('empModalLink').href = '/divhr/employee/' + empId;
    $('#employeeDetailModal').modal('show');
}
function openAllStaffModal() { $('#allStaffModal').modal('show'); }
function openAchieveModal(msnId) {
    document.getElementById('modal_msn_id').value = msnId;
    $('#achieveDateModal').modal('show');
}

function selectOptionCaseInsensitive(selectEl, val) {
    const target = (val || '').trim().toLowerCase();
    for (let i = 0; i < selectEl.options.length; i++) {
        if (selectEl.options[i].value.trim().toLowerCase() === target) {
            selectEl.selectedIndex = i;
            return;
        }
    }
    selectEl.selectedIndex = 0;
}

function toggleActivityFields() {
    const typeVal = (document.getElementById('ed_msn_type').value || '').trim().toLowerCase();
    const actRow = document.getElementById('ed_activity_fields');
    const startInput = document.getElementById('ed_msn_startdt');
    if (typeVal === 'activity') {
        actRow.style.display = '';
        startInput.required = true;
    } else {
        actRow.style.display = 'none';
        startInput.required = false;
        startInput.value = '';
        document.getElementById('ed_msn_comp').value = '';
    }
}

function toggleAchievedField() {
    const statusVal = (document.getElementById('ed_msn_status').value || '').trim().toLowerCase();
    const achvInput = document.getElementById('ed_msn_achvdt');
    const star = document.getElementById('ed_achv_req_star');
    const note = document.getElementById('ed_complete_lock_note');
    const compInput = document.getElementById('ed_msn_comp');
    if (statusVal === 'completed') {
        achvInput.disabled = false;
        achvInput.required = true;
        star.style.display = 'inline';
        note.style.display = 'block';
        if ((document.getElementById('ed_msn_type').value || '').trim().toLowerCase() === 'activity') {
            compInput.value = 100;
        }
    } else {
        achvInput.value = '';
        achvInput.disabled = true;
        achvInput.required = false;
        star.style.display = 'none';
        note.style.display = 'none';
    }
}

function openMilestonePopup(btn) {
    const d = btn.dataset;
    const isEditable = d.editable === '1';
    const roBox = document.getElementById('msPopupReadOnly');
    const formBox = document.getElementById('msPopupForm');
    const titleEl = document.getElementById('msPopupModalTitle');

    if (!isEditable) {
        roBox.style.display = 'block';
        formBox.style.display = 'none';
        titleEl.innerHTML = '<i class="fas fa-lock text-success mr-2"></i> Milestone Details (Read-Only)';

        document.getElementById('ro_msn_id').innerText = d.msnId || '—';
        document.getElementById('ro_msn_type').innerText = d.msnType || 'Milestone';
        document.getElementById('ro_msn_desc').innerText = d.msnDesc || '—';
        document.getElementById('ro_msn_targetdt').innerText = d.msnTargetdtLabel || 'N/A';
        document.getElementById('ro_msn_achvdt').innerText = d.msnAchvdtLabel || '—';
        document.getElementById('ro_msn_status').innerText = d.msnStatus || 'Completed';
        document.getElementById('ro_msn_idd').innerText = d.msnIdd || '—';

        const roAct = document.getElementById('ro_activity_row');
        if ((d.msnType || '').trim().toLowerCase() === 'activity') {
            roAct.style.display = '';
            document.getElementById('ro_msn_startdt').innerText = d.msnStartdtLabel || '—';
            document.getElementById('ro_msn_comp').innerText = d.msnComp !== '' ? d.msnComp : '0';
        } else {
            roAct.style.display = 'none';
        }

        const certBox = document.getElementById('ro_cert_box');
        if (d.hasCert === '1' && d.certUrl) {
            certBox.innerHTML = '<a href="' + d.certUrl + '" target="_blank" class="badge badge-info px-2 py-1"><i class="fas fa-file-alt mr-1"></i> View Certificate</a>';
        } else {
            certBox.innerHTML = '<span class="badge badge-light border text-muted">Not Uploaded</span>';
        }

        // Certificate upload stays available on locked milestones (certificate column only)
        const certForm = document.getElementById('msCertForm');
        if (certForm) {
            if (d.certEditable === '1' && d.msnIdd) {
                certForm.action = "{{ url('/milestone') }}/" + encodeURIComponent(d.msnIdd) + "/certificate";
                certForm.style.display = 'block';
                document.getElementById('ro_cc_file').value = '';
                document.getElementById('ro_cert_upload_label').innerText =
                    (d.hasCert === '1' && d.certUrl) ? 'Replace Certificate' : 'Upload Certificate';
            } else {
                certForm.style.display = 'none';
            }
        }
    } else {
        roBox.style.display = 'none';
        formBox.style.display = 'block';
        titleEl.innerHTML = '<i class="fas fa-edit text-primary mr-2"></i> Edit Milestone (MS-' + (d.msnId || '') + ')';

        formBox.action = "{{ url('/milestone') }}/" + encodeURIComponent(d.msnIdd) + "/update";
        document.getElementById('ed_msn_id_display').value = d.msnId || '';
        document.getElementById('ed_msn_idd_label').innerText = d.msnIdd || '—';

        selectOptionCaseInsensitive(document.getElementById('ed_msn_type'), d.msnType || 'Milestone');
        document.getElementById('ed_msn_desc').value = d.msnDesc || '';
        document.getElementById('ed_msn_startdt').value = d.msnStartdt || '';
        document.getElementById('ed_msn_comp').value = d.msnComp || '';
        document.getElementById('ed_msn_targetdt').value = d.msnTargetdt || '';
        selectOptionCaseInsensitive(document.getElementById('ed_msn_status'), d.msnStatus || 'Not started');
        document.getElementById('ed_msn_achvdt').value = d.msnAchvdt || '';
        document.getElementById('ed_cc_file').value = '';

        const exCert = document.getElementById('ed_existing_cert');
        if (d.hasCert === '1' && d.certUrl) {
            exCert.innerHTML = '<a href="' + d.certUrl + '" target="_blank" class="badge badge-info px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Current File</a>';
        } else {
            exCert.innerHTML = '';
        }

        toggleActivityFields();
        toggleAchievedField();
    }

    $('#milestonePopupModal').modal('show');
}

function openAddMilestoneModal() {
    const roBox = document.getElementById('msPopupReadOnly');
    const formBox = document.getElementById('msPopupForm');
    const titleEl = document.getElementById('msPopupModalTitle');

    roBox.style.display = 'none';
    formBox.style.display = 'block';
    titleEl.innerHTML = '<i class="fas fa-plus-circle text-primary mr-2"></i> Add New Milestone';

    formBox.action = "{{ route('projects.store-milestone', $project->prj_id) }}";
    document.getElementById('ed_msn_id_display').value = "{{ ((int) ($milestones->max('msn_id') ?? 0)) + 1 }}";
    document.getElementById('ed_msn_idd_label').innerText = 'New';
    document.getElementById('ed_msn_type').value = 'Milestone';
    document.getElementById('ed_msn_desc').value = '';
    document.getElementById('ed_msn_startdt').value = '';
    document.getElementById('ed_msn_comp').value = '';
    document.getElementById('ed_msn_targetdt').value = '';
    document.getElementById('ed_msn_status').value = 'Not started';
    document.getElementById('ed_msn_achvdt').value = '';
    document.getElementById('ed_cc_file').value = '';
    document.getElementById('ed_existing_cert').innerHTML = '';

    toggleActivityFields();
    toggleAchievedField();
    $('#milestonePopupModal').modal('show');
}

function initFinancialIntelligenceCharts() {
    @if($head)
    const head = @json($head);
    const subheads = @json($subheads);
    
    const canvas = document.getElementById('finDetailedChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        if (window.finMainChart) window.finMainChart.destroy();
        window.finMainChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Received', 'Expenditure', 'Commitments', 'Remaining'],
                datasets: [{
                    label: 'PKR Value',
                    data: [head.received, head.expenditure, head.commitments, head.remaining],
                    backgroundColor: ['rgba(77, 163, 255, 0.2)', 'rgba(255, 50, 50, 0.2)', 'rgba(243, 156, 18, 0.2)', 'rgba(77, 255, 136, 0.2)'],
                    borderColor: ['#4da3ff', '#ff3232', '#f39c12', '#4dff88'],
                    borderWidth: 2, borderRadius: 4, barThickness: 40
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#aaa', font: { weight: 'bold' } } }
                }
            }
        });
    }

    subheads.slice(0, 3).forEach((sh, idx) => {
        const shCanvas = document.getElementById(`chartShMini${idx}`);
        if (shCanvas) {
            const shCtx = shCanvas.getContext('2d');
            if (window[`finShChart${idx}`]) window[`finShChart${idx}`].destroy();
            window[`finShChart${idx}`] = new Chart(shCtx, {
                type: 'pie',
                data: {
                    datasets: [{
                        data: [sh.expenditure + sh.commitments, sh.remaining > 0 ? sh.remaining : 0],
                        backgroundColor: ['rgba(77, 255, 136, 0.8)', 'rgba(255, 255, 255, 0.05)'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
            });
        }
    });
    @endif
}

$(document).on('shown.bs.modal', '#financialIntelligenceModal', function () {
    setTimeout(initFinancialIntelligenceCharts, 300);
});

$(document).ready(function() {
    if (window.location.hash) {
        const target = $(window.location.hash);
        if (target.length) {
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: target.offset().top - 70
                }, 500);
                target.css('outline', '3px solid rgba(37, 99, 235, 0.5)').css('border-radius', '8px');
                setTimeout(() => { target.css('outline', ''); }, 3000);
            }, 200);
        }
    }
});
</script>
@endsection