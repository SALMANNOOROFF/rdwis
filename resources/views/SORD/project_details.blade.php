@extends('welcome')
@section('content')
<div class="content-wrapper pt-3">
    <style>
        /* --- GLOBAL & UTILS --- */
        .card-primary.card-outline { border-top: 3px solid var(--rd-accent); }
        .bg-light-blue { background-color: var(--rd-bg); }
        
        /* --- HEADER & CONTROLS --- */
        .header-controls { display: flex; align-items: center; gap: 8px; }
        .milestone-box-compact {
            background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 30px;
            padding: 8px 15px; display: inline-flex; align-items: center; justify-content: space-between;
            min-width: 280px; width: 100%; max-width: 400px; height: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        /* --- INFO PANEL --- */
        .info-panel { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); display: flex; flex-direction: row; overflow: visible; }
        @media (max-width: 991px) { .info-panel { flex-direction: column; } .info-right-team { width: 100% !important; border-left: none !important; border-top: 1px solid var(--rd-border); padding: 20px 15px !important; } }
        .info-left-content { flex: 1; padding: 15px; display: flex; align-items: center; }
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
        .date-grid-item { position: relative; padding-left: 10px; margin-bottom: 12px; border-left: 2px solid var(--rd-border); }
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

        /* --- OVERALL STEPS WIZARD --- */
        .steps-container {
            position: relative;
            height: 60px;
            margin: 45px 40px;
            width: calc(100% - 80px);
            padding: 0;
        }
        .steps-track {
            position: absolute; top: 50%; left: 0; width: 100%; height: 3px; background: var(--rd-border);
            transform: translateY(-50%); z-index: 1; border-radius: 2px;
        }
        .steps-fill { height: 100%; background: var(--rd-success); transition: width 0.4s ease; border-radius: 2px; }
        .step-item {
            position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 3;
            width: 32px; height: 32px;
            display: flex; justify-content: center; align-items: center; cursor: pointer;
        }
        .step-dot {
            width: 32px; height: 32px; border-radius: 50%; background: var(--rd-surface); border: 3px solid var(--rd-border);
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.65rem;
            color: var(--rd-text3); transition: all 0.3s; position: relative; z-index: 2;
        }
        .step-item.completed .step-dot { background: var(--rd-success); border-color: var(--rd-success); color: #fff; }
        .step-item.active .step-dot { background: var(--rd-accent); border-color: var(--rd-accent); color: #fff; transform: scale(1.2); box-shadow: 0 0 0 4px rgba(79, 140, 255, 0.15); }
        .step-label {
            position: absolute; top: -25px; left: 50%; transform: translateX(-50%);
            font-size: 0.65rem; font-weight: 700; color: var(--rd-text3); white-space: nowrap;
        }
        .step-item.active .step-label { color: var(--rd-accent); }
        .step-date {
            position: absolute; bottom: -30px; width: 100px; left: 50%; margin-left: -50px;
            text-align: center; font-size: 0.6rem; color: var(--rd-text3); white-space: nowrap; font-weight: 500;
        }
        .step-item.active .step-date { color: var(--rd-accent); font-weight: 700; }
        .step-tooltip {
            display: none; position: absolute; bottom: 45px; left: 50%; transform: translateX(-50%);
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
            color: #fff; text-align: center; white-space: nowrap; box-shadow: 0 3px 8px rgba(0,0,0,0.4);
        }
        .overall-today-marker .status-bubble::before{
            content: ""; position: absolute; top: -6px; left: 50%; transform: translateX(-50%);
            border-left: 6px solid transparent; border-right: 6px solid transparent;
            border-bottom: 6px solid var(--rd-danger);
        }
        .status-bubble.late { background: var(--rd-danger); box-shadow: 0 0 10px rgba(220,53,69,0.3); }
        .status-bubble.ontrack { background: var(--rd-success); box-shadow: 0 0 10px rgba(40,167,69,0.3); }

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
        $overallPercent = round($overallPercent, 1);

        // Dummy team data (replace with real relation if available)
        $team = [
            ['id'=>1, 'name'=>'Ali Khan', 'role'=>'Project Manager', 'email'=>'ali@rdwis.com', 'phone'=>'0300-1234567', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>2, 'name'=>'Sara Ahmed', 'role'=>'Senior Architect', 'email'=>'sara@rdwis.com', 'phone'=>'0300-7654321', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>3, 'name'=>'Bilal Hameed', 'role'=>'Site Engineer', 'email'=>'bilal@rdwis.com', 'phone'=>'0333-1122334', 'img'=>asset('dist/img/profile-1.jfif')],
        ];
        $displayLimit = 6;

        $fixedDocs = ['PPF', 'Approval Letter', 'URD', 'Work Order'];
        $allAttachments = $project->attachments;
        $otherDocsCount = $allAttachments->whereNotIn('jat_type', $fixedDocs)->count();

        // ---- Milestone track inset so first/last diamonds never sit under START/END boxes ----
        $trackInsetStart = 9;   // % from left where milestone track begins
        $trackInsetRange = 82;  // % width available for milestones (9% -> 91%)
    @endphp

    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm border-0">
            <div class="card-header p-3 bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge badge-light border mr-2">CODE: {{ $project->prj_code }}</span>
                            <span class="font-weight-bolder {{ $edcClass }} small">
                                <i class="fas fa-flag-checkered mr-1"></i> EDC: {{ $edc ? $edc->format('d M, Y') : 'TBD' }}
                            </span>
                        </div>
                        <h4 class="text-white font-weight-bold m-0 text-truncate" title="{{ $project->prj_title }}">
                            {{ $project->prj_title }}
                        </h4>
                    </div>

                    <div class="col-md-4 text-center">
                        @if($nextMilestone)
                            <div class="milestone-box-compact">
                                <div class="d-flex align-items-center pr-3 border-right mr-3">
                                    <span class="font-weight-bold {{ $statusClass }}" style="font-size: 0.95rem;">
                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clock' }} mr-1"></i>
                                        {{ $statusMsg }}
                                    </span>
                                </div>
                                <div class="text-left flex-grow-1" style="min-width:0;">
                                    <div class="font-weight-bold text-white text-truncate" style="max-width:160px;" title="{{ $nextMilestone->msn_desc }}">
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

                    {{-- REMOVED ACTION BUTTONS FOR SORD (READ ONLY VIEW) --}}
                    <div class="col-md-4 text-right">
                        <div class="header-controls justify-content-end">
                            {{-- LOG HISTORY BUTTON --}}
                            <a href="{{ route('projecthistory', ['project_id' => $project->prj_id]) }}" class="btn btn-outline-info btn-sm shadow-sm font-weight-bold mr-2">
                                <i class="fas fa-history mr-1"></i> Log History
                            </a>

                            {{-- BACK BUTTON --}}
                            <a href="{{ route('sord.all_projects') }}" class="btn btn-secondary btn-sm shadow-sm font-weight-bold">
                                <i class="fas fa-arrow-left mr-1"></i> Back to List
                            </a>
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
                            <h6 class="text-white font-weight-bold mb-1" style="font-size:0.95rem;">Scope of Work</h6>
                            <p class="text-muted m-0 mb-2" style="font-size:0.86rem; line-height:1.4;">
                                {{ Str::limit($project->prj_scope ?? 'No scope defined.', 90) }}
                            </p>
                            @if($head ?? false)
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
                                <span class="d-block text-white font-weight-bold" style="font-size: 1.12rem; line-height: 1.2;">
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
    transform: translate(-50%, -50%);
    width: 56px;
    height: 44px;
    background: var(--rd-danger);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 3;
}

/* ===== DIAMOND MILESTONE ===== */
.step-dot {
    width: 26px;
    height: 26px;
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
    background: var(--rd-success);
    border-color: var(--rd-success);
    color: #fff;
    transform: rotate(45deg);
}

.step-item.completed .step-dot * {
    transform: rotate(-45deg);
}

.step-item.active .step-dot {
    border-color: var(--rd-accent);
    color: var(--rd-accent);
}

/* ===== TODAY BUBBLE WITH GLOW CURSOR ===== */
.today-bubble {
    position: absolute;
    top: -45px;
    background: var(--rd-accent);
    color: #fff;
    padding: 5px 10px;
    border-radius: 14px;
    font-size: 0.6rem;
    font-weight: 700;
    white-space: nowrap;
}

.today-glow {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--rd-accent);
    box-shadow: 0 0 0 rgba(79, 140, 255, 0.4);
    animation: pulse-accent 1.5s infinite;
    position: absolute;
    top: 0;
    left: 50%;
    transform: translate(-50%, -50%);
}
@keyframes pulse-accent {
    0% {
        box-shadow: 0 0 0 0 rgba(79, 140, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(79, 140, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(79, 140, 255, 0);
    }
}

/* ===== ACHIEVED DATE UNDER DIAMOND ===== */
.achieved-wrap {
    position: absolute;
    bottom: -42px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.6rem;
    font-weight: 700;
    color: var(--rd-success);
    white-space: nowrap;
}

.achieved-wrap .flag {
    font-size: 11px;
    color: var(--rd-success);

}.achieved-marker{
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
    color: var(--rd-success);
}

/* LATE */
.achieved-marker.late i,
.achieved-marker.late .achieved-date{
    color: var(--rd-danger);
}
</style>
<!-- ================= PROGRESS ROW ================= -->
<div class="row mt-4">

    <div class="col-lg-12">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold m-0 text-white">
                <i class="fas fa-chart-line text-primary mr-2"></i> Milestone Progress
            </h6>
            <span class="badge badge-light border">
                {{ $completedMilestones }} / {{ $totalMilestones }} Completed
            </span>
        </div>

        <div class="steps-container mb-4">

    <div class="steps-track">
        <div class="steps-fill" style="width: {{ $overallPercent }}%;"></div>
    </div>

    {{-- START BOX --}}
    <div class="edge-box" style="left: 0;">
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
                <i class="fas fa-check text-white" style="font-size:0.6rem"></i>
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
    <div class="edge-box" style="left: 100%;">
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
    <div class="col-xl-9 col-lg-8 col-md-12 mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold m-0 text-white" style="font-size: 0.96rem;">
                <i class="fas fa-list-ol text-primary mr-2"></i> Milestones Detail
            </h6>
        </div>

        <div class="milestone-container shadow-sm">
            <div class="rd-table-responsive milestone-scroll-box">
                <table class="table table-custom w-100 m-0">
                    <thead>
                        <tr>
                            <th style="width: 85px;">#</th>
                            <th style="width: 120px;">Type</th>
                            <th>Description</th>
                            <th style="width: 140px;">Target</th>
                            <th style="width: 150px;" class="text-center">Achieved</th>
                            <th style="width: 150px;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($milestones as $milestone)
                            @php
                                $mStatus = strtolower(trim($milestone->msn_status ?? ''));
                                $targetDt = $milestone->msn_targetdt ? \Carbon\Carbon::parse($milestone->msn_targetdt) : null;
                                $achvDt = $milestone->msn_achvdt ? \Carbon\Carbon::parse($milestone->msn_achvdt) : null;
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-white">MS-{{ $milestone->msn_id ?: $loop->iteration }}</td>
                                <td><span class="badge badge-light border text-dark font-weight-bold" style="font-size:0.75rem;">{{ $milestone->msn_type ?: 'Milestone' }}</span></td>
                                <td class="font-weight-bold text-white" style="font-size:0.90rem;">{{ $milestone->msn_desc }}</td>
                                <td class="font-weight-bold text-muted" style="font-size:0.86rem;">{{ $targetDt ? $targetDt->format('d M, Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    @if($achvDt)
                                        <span class="text-success font-weight-bold" style="font-size:0.86rem;">
                                            <i class="fas fa-check-circle mr-1"></i> {{ $achvDt->format('d M, Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($mStatus === 'completed')
                                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-check mr-1"></i> Completed</span>
                                    @elseif($mStatus === 'in progress')
                                        <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-spinner fa-spin mr-1"></i> In Progress</span>
                                    @elseif($mStatus === 'awaited')
                                        <span class="badge badge-info px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-hourglass-half mr-1"></i> Awaited</span>
                                    @elseif($mStatus === 'not started')
                                        <span class="badge badge-secondary px-3 py-1 font-weight-bold" style="font-size:0.76rem;"><i class="fas fa-circle mr-1" style="font-size: 7px;"></i> Not Started</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold" style="font-size:0.76rem;">{{ $milestone->msn_status ?: 'Pending' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No milestones defined yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- FAR RIGHT (Attachments Widget - Corner Pinned) -->
    <div class="col-xl-3 col-lg-4 col-md-12 mb-4">

        <div class="sticky-top" style="top:20px;">
            <div class="attachments-wrapper">
                @include('partials.attachments_widget', [
                    'module' => 'prj',
                    'objectId' => $project->prj_id,
                    'title' => 'Project Attachments',
                    'defaultSlots' => ['PPF', 'Approval Letter', 'URD', 'Work Order'],
                    'attachments' => $allAttachments ?? $project->attachments,
                    'canEdit' => false,
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
    <!-- Other Docs Modal -->
    <div class="modal fade glass-modal" id="otherDocsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-copy text-primary mr-2"></i>Other Attachments</h5>
                    <button class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 class="font-weight-bold text-white">Existing Files</h6>
                    <div class="table-responsive">
                    <table class="table table-bordered table-sm mt-2 bg-white">
                        <thead class="bg-light"><tr><th>#</th><th>Document Name</th><th>Action</th></tr></thead>
                        <tbody>
                             @foreach($allAttachments->whereNotIn('jat_type', $fixedDocs) as $att)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $att->jat_type }}</td>
                                    <td>
                                        <a href="{{ route('attachment.view', $att->jat_id) }}" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<script>
    $(function() {
        $(".knob").knob();
    });

    function openEmployeeModal(name, role, img, email, phone) {
        // Implement modal view logic if needed
    }

    function openMilestoneDetail(title, target, achieved, status) {
        $('#msTitle').text(title);
        $('#msTarget').text(target);
        $('#msAchieved').text(achieved);
        $('#msStatus').text(status);
        $('#milestoneDetailModal').modal('show');
    }

    function openOtherDocsModal() {
        $('#otherDocsModal').modal('show');
    }
</script>
@endsection
