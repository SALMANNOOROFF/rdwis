@extends('welcome')
@section('content')
<div class="content-wrapper pt-3">
    <style>
        /* --- GLOBAL & UTILS --- */
        .card-primary.card-outline { border-top: 3px solid #007bff; }
        .bg-light-blue { background-color: #f4f7fa; }
        
        /* --- HEADER & CONTROLS --- */
        .header-controls { display: flex; align-items: center; gap: 8px; }
        .milestone-box-compact {
            background: #ffffff; border: 1px solid #e9ecef; border-radius: 30px;
            padding: 8px 20px; display: inline-flex; align-items: center; justify-content: space-between;
            min-width: 380px; height: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        /* --- INFO PANEL --- */
        .info-panel { background: #fff; border: 1px solid #e1e4e8; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02); display: flex; overflow: visible; }
        .info-left-content { flex: 1; padding: 15px; display: flex; align-items: center; }
        .info-right-team { width: 350px; background: #fff; border-left: 1px solid #e9ecef; padding: 10px 15px; display: flex; flex-direction: column; justify-content: center; }
        .info-label { font-size: 0.7rem; text-transform: uppercase; color: #8898aa; font-weight: 700; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
        .info-value { font-size: 0.9rem; color: #32325d; font-weight: 600; line-height: 1.4; }
        .cost-tag { background: #e0fdf4; color: #0f5132; padding: 4px 10px; border-radius: 4px; font-weight: 700; border: 1px solid #b7eb8f; display: inline-block; }
        /* --- TEAM SECTION --- */
        .team-section-container { display: flex; align-items: center; justify-content: flex-end; padding-right: 5px; }
        .team-avatar-wrapper { width: 42px; height: 42px; margin-left: -10px; position: relative; z-index: 10; cursor: pointer; transition: transform 0.2s; }
        .team-avatar-wrapper:hover { transform: scale(1.2); z-index: 100; margin: 0 5px; }
        .team-avatar-wrapper img { width: 42px; height: 42px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); object-fit: cover; background: #fff; }
        .more-staff-btn { width: 42px; height: 42px; border-radius: 50%; background: #fff; color: #525f7f; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; margin-left: 5px; z-index: 0; }
        
        /* --- DATES & DOCS --- */
        .date-grid-item { position: relative; padding-left: 10px; margin-bottom: 12px; border-left: 2px solid #e9ecef; }
        .date-grid-item.active { border-left-color: #007bff; }
        .date-grid-item.done { border-left-color: #28a745; }
        .d-title { font-size: 0.65rem; font-weight: 700; color: #6c757d; text-transform: uppercase; display: block; line-height: 1; margin-bottom: 3px; }
        .d-value { font-size: 0.8rem; color: #343a40; font-weight: 600; line-height: 1; }
        .doc-card { background: #fff; border: 1px solid #e9ecef; border-left: 3px solid #007bff; border-radius: 6px; padding: 8px 12px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; }
        .doc-card:hover { transform: translateX(3px); box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .doc-content { display: flex; align-items: center; overflow: hidden; margin-right: 10px; }
        .doc-icon { width: 28px; height: 28px; background: #f4f6f9; color: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 0.75rem; flex-shrink: 0; }
        .doc-title { font-size: 0.8rem; font-weight: 600; color: #444; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .file-input-hidden { display: none !important; }
        .doc-card.other { border-left-color: #6f42c1; cursor: pointer; background: #fbf9ff; }
        .doc-card.other:hover { background-color: #f3ebff; }
        .doc-icon.other { color: #6f42c1; background-color: #e9dff7; }
        h6[data-toggle="collapse"] i.fa-chevron-down { transition: transform 0.3s ease; }
        h6[data-toggle="collapse"][aria-expanded="true"] i.fa-chevron-down { transform: rotate(180deg); }

        /* --- OVERALL STEPS WIZARD --- */
        .steps-container {
            position: relative; display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 35px; margin-top: 35px; padding: 0 10px;
        }
        .steps-track {
            position: absolute; top: 50%; left: 0; width: 100%; height: 3px; background: #e9ecef;
            transform: translateY(-50%); z-index: 1; border-radius: 2px;
        }
        .steps-fill { height: 100%; background: #28a745; transition: width 0.4s ease; border-radius: 2px; }
        .step-item {
            position: relative; z-index: 2; width: 32px; height: 32px;
            display: flex; justify-content: center; align-items: center; cursor: pointer;
        }
        .step-dot {
            width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 3px solid #e9ecef;
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.65rem;
            color: #adb5bd; transition: all 0.3s; position: relative; z-index: 2;
        }
        .step-item.completed .step-dot { background: #28a745; border-color: #28a745; color: #fff; }
        .step-item.active .step-dot { background: #007bff; border-color: #007bff; color: #fff; transform: scale(1.2); box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15); }
        .step-label {
            position: absolute; top: -25px; left: 50%; transform: translateX(-50%);
            font-size: 0.65rem; font-weight: 700; color: #888; white-space: nowrap;
        }
        .step-item.active .step-label { color: #007bff; }
        .step-date {
            position: absolute; bottom: -30px; width: 100px; left: 50%; margin-left: -50px;
            text-align: center; font-size: 0.6rem; color: #6c757d; white-space: nowrap; font-weight: 500;
        }
        .step-item.active .step-date { color: #007bff; font-weight: 700; }
        .step-tooltip {
            display: none; position: absolute; bottom: 45px; left: 50%; transform: translateX(-50%);
            background: #343a40; color: #fff; padding: 6px 10px; border-radius: 4px;
            font-size: 0.7rem; white-space: nowrap; z-index: 100; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .step-tooltip::after {
            content: ""; position: absolute; top: 100%; left: 50%; margin-left: -5px;
            border-width: 5px; border-style: solid; border-color: #343a40 transparent transparent transparent;
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
            color: #fff; text-align: center; white-space: nowrap; box-shadow: 0 3px 8px rgba(0,0,0,.2);
        }
        .overall-today-marker .status-bubble::before{
            content: ""; position: absolute; top: -6px; left: 50%; transform: translateX(-50%);
            border-left: 6px solid transparent; border-right: 6px solid transparent;
            border-bottom: 6px solid red;
        }
        .status-bubble.late { background: #dc3545; box-shadow: 0 0 10px rgba(220,53,69,.5); }
        .status-bubble.ontrack { background: #28a745; box-shadow: 0 0 10px rgba(40,167,69,.5); }

        /* --- MILESTONE TABLE --- */
        .milestone-container { background: #fff; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; }
        .milestone-scroll-box { max-height: 450px; overflow-y: auto; }
        .table-custom thead th { background: #f8f9fa; color: #6c757d; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #e9ecef; padding: 12px 15px; position: sticky; top: 0; z-index: 5; }
        .table-custom tbody td { padding: 10px 15px; vertical-align: middle; color: #525f7f; font-size: 0.85rem; border-bottom: 1px solid #f0f0f0; }

        /* --- FINANCE KNobs --- */
        .finance-bars-wrap { display: flex; justify-content: space-between; align-items: center; gap: 24px; }
        .finance-box {
            width: 120px; height: 140px; background: #fff; border-radius: 14px; padding: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06); display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .finance-box canvas { width: 90px; height: 90px; }
        .finance-title { margin-top: 10px; font-size: 11px; font-weight: 800; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }

      
        /* --- MODAL --- */
        .glass-modal .modal-content { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; border: 1px solid rgba(255,255,255,0.3); }
        .emp-modal-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f4f6f9; margin-bottom: 15px; }
        .emp-detail-row { border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; }
    </style>

   @php
        $today = \Carbon\Carbon::today();

        // EDC logic
        $edc = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : null;
        $edcClass = $edc && $today->gt($edc) ? 'text-danger' : 'text-success';

        // Milestones
        $milestones = $project->milestones->sortBy('msn_targetdt')->values();
        $nextMilestone = $milestones->firstWhere('msn_status', '!=', 'Completed');
        $totalMilestones = $milestones->count();
        $completedMilestones = $milestones->where('msn_status', 'Completed')->count();

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

        // Progress calculation
        $overallPercent = $totalMilestones > 0 ? ($completedMilestones / max(1, $totalMilestones)) * 100 : 0;
        $overallPercent = round(max(0, min(100, $overallPercent)), 1);

        $firstMs  = $milestones->first();
        $lastMs   = $milestones->last();
        $prjStart = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : ($firstMs ? \Carbon\Carbon::parse($firstMs->msn_targetdt) : $today);
        $prjEnd   = $lastMs ? \Carbon\Carbon::parse($lastMs->msn_targetdt) : $today;
        if ($prjEnd->lt($prjStart)) $prjEnd = $prjStart->copy()->addDay();

        $totalDaysSpan   = $prjStart->diffInDays($prjEnd) ?: 1;
        $daysPassedTotal = $prjStart->diffInDays($today, false);
        $overallTimePercent = round(($daysPassedTotal / $totalDaysSpan) * 100, 1);
        $overallTimePercent = max(0, min(100, $overallTimePercent));

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
                        <h4 class="text-dark font-weight-bold m-0 text-truncate" title="{{ $project->prj_title }}">
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

                    <div class="col-md-4 text-right">
                        <div class="header-controls justify-content-end">
                            <a href="{{ route('projecthistory', $project->prj_id) }}" class="btn btn-outline-primary btn-sm shadow-sm font-weight-bold">
                                <i class="fas fa-list-alt mr-1"></i> View MPRs
                            </a>
                            <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold px-3">
                                <i class="fas fa-plus-circle mr-1"></i> Create MPR
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light-blue">

            <!-- INFO PANEL (Sponsor + Budget + Team) -->
<div class="info-panel">
    <div class="info-left-content">
        <div class="row w-100 m-0 align-items-center">
            <div class="col-md-4 pl-0 border-right">
                <div class="mb-4">
                    <span class="badge badge-light border px-3 py-2 rounded-pill text-uppercase shadow-sm">
                        <i class="fas fa-handshake text-primary mr-1"></i> {{ $project->prj_sponsor ?? 'N/A' }}
                    </span>
                </div>
                <div>
                    <h6 class="text-dark font-weight-bold mb-1" style="font-size:0.95rem;">Scope of Work</h6>
                    <p class="text-muted m-0" style="font-size:0.85rem; line-height:1.45;">
                        {{ Str::limit($project->prj_scope ?? 'No scope defined.', 110) }}
                    </p>
                </div>
            </div>

            <div class="col-md-8 px-4">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <span class="d-block text-muted text-uppercase small fw-bold">Total Budget</span>
                            <span class="d-block text-dark fw-bold fs-5">
                                Rs. {{ number_format($project->prj_propcost / 1_000_000, 2) }} M
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted text-uppercase small fw-bold">Total Spent</span>
                            <span class="d-block text-danger fw-bold fs-5">
                                Rs. {{ number_format(($totalSpent ?? 0) / 1_000_000, 2) }} M
                            </span>
                        </div>
                    </div>

                    <div class="col-md-8 d-flex justify-content-around">
                        <div class="finance-bars-wrap">
                            <div class="finance-box">
                                <input type="text" class="knob" value="30" data-skin="tron" data-thickness="0.2"
                                       data-width="90" data-height="90" data-fgColor="#FC7A58" data-readonly="true">
                                <div class="finance-title mt-2">Equip</div>
                            </div>
                            <div class="finance-box">
                                <input type="text" class="knob" value="50" data-skin="tron" data-thickness="0.2"
                                       data-width="90" data-height="90" data-fgColor="#42e695" data-readonly="true">
                                <div class="finance-title mt-2">HR</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="info-right-team">
        <div class="d-flex justify-content-end mb-3">
            <span class="info-label text-primary">Team ({{ count($team) }})</span>
        </div>
        <div class="team-section-container">
            @foreach($team as $index => $member)
                @if($index < $displayLimit)
                    <div class="team-avatar-wrapper"
                         onclick="openEmployeeModal('{{ $member['name'] }}','{{ $member['role'] }}','{{ $member['img'] }}','{{ $member['email'] }}','{{ $member['phone'] }}')">
                        <img src="{{ $member['img'] }}" alt="{{ $member['name'] }}">
                    </div>
                @endif
            @endforeach

            @if(count($team) > $displayLimit)
                <button type="button" class="more-staff-btn" onclick="openAllStaffModal()">
                    <i class="fas fa-plus"></i>
                </button>
            @endif
        </div>
    </div>
</div>
<style>
/* ===== START & END RED SQUARES ===== */
.edge-box {
    width: 44px;
    height: 44px;
    background: #dc3545;
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
    width: 30px;
    height: 30px;
    transform: rotate(45deg);
    border-radius: 4px;
}

.step-dot * {
    transform: rotate(-45deg);
}

.step-item.completed .step-dot {
    background: #28a745;
    border-color: #28a745;
}

.step-item.active .step-dot {
    background: #007bff;
    border-color: #007bff;
}

/* ===== TODAY BUBBLE WITH FLAG ===== */
.today-bubble {
    position: absolute;
    top: -45px;
    background: #dc3545;
    color: #fff;
    padding: 5px 10px;
    border-radius: 14px;
    font-size: 0.6rem;
    font-weight: 700;
    white-space: nowrap;
}

.today-flag {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    color: #dc3545;
    font-size: 14px;
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
    color: #28a745;
    white-space: nowrap;
}

.achieved-wrap .flag {
    font-size: 11px;
    color: #28a745;

}.achieved-marker{
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 6;
    pointer-events: none;
}

.achieved-marker i{
    font-size: 14px;
}

.achieved-marker .achieved-ms{
    font-size: 0.6rem;
    font-weight: 800;
    margin-top: 2px;
}

.achieved-marker .achieved-date{
    font-size: 0.55rem;
    font-weight: 700;
    white-space: nowrap;
}

/* ON TIME / EARLY */
.achieved-marker.ontime i,
.achieved-marker.ontime .achieved-ms,
.achieved-marker.ontime .achieved-date{
    color: #28a745;
}

/* LATE */
.achieved-marker.late i,
.achieved-marker.late .achieved-ms,
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

        <div class="steps-container mb-4">

    <div class="steps-track">
        <div class="steps-fill" style="width: {{ $overallPercent }}%;"></div>
    </div>

    {{-- START BOX --}}
    <div class="edge-box">
        START
        <small>{{ $prjStart->format('d M Y') }}</small>

    </div>

    {{-- TODAY MARKER --}}
    <div style="position:absolute; left:{{ $overallTimePercent }}%; top:50%; transform:translateX(-50%);">
        <div class="today-bubble">{{ $today->format('d M Y') }}</div>
        <div class="today-flag"><i class="fas fa-flag"></i></div>
    </div>

   {{-- MILESTONES --}}
@foreach($milestones as $ms)
    @php
        $stepClass = '';
        if (strtolower($ms->msn_status) === 'completed') {
            $stepClass = 'completed';
        } elseif (optional($nextMilestone)->msn_id === $ms->msn_id) {
            $stepClass = 'active';
        }

        $targetDate   = \Carbon\Carbon::parse($ms->msn_targetdt);
        $achievedDate = $ms->msn_achvdt ? \Carbon\Carbon::parse($ms->msn_achvdt) : null;

        $isLate = $achievedDate && $achievedDate->gt($targetDate);

        if ($achievedDate) {
          // milestone ka percent on progress bar
$milestoneIndex = $loop->index;
$milestonePercent = ($milestoneIndex / max(1, $totalMilestones - 1)) * 100;

// achieved vs target difference
$diffDays = $achievedDate->diffInDays($targetDate, false);

// 1 day = kitna percent shift kare
$oneDayPercent = 100 / max(1, $totalDaysSpan);

// final achieved position
$achievedPercent = $milestonePercent + ($diffDays * $oneDayPercent);

// clamp 0–100
$achievedPercent = max(0, min(100, $achievedPercent));

        }
    @endphp
{{-- ACHIEVED FLAG (ON PROGRESS LINE) --}}


    <div class="step-item {{ $stepClass }}"
     onclick="openMilestoneDetail(
        'MS-{{ $loop->iteration }}',
        '{{ $targetDate->format('d M Y') }}',
        '{{ $achievedDate ? $achievedDate->format('d M Y') : 'Not achieved' }}',
        '{{ $isLate ? 'Late' : 'On Time' }}'
     )">


        {{-- MS LABEL --}}
        <div class="step-label">MS-{{ $loop->iteration }}</div>

        {{-- DIAMOND --}}
        <div class="step-dot">
            @if($stepClass === 'completed')
                <i class="fas fa-check text-white" style="font-size:0.6rem"></i>
            @else
                {{ $loop->iteration }}
            @endif
        </div>

        {{-- TARGET DATE (UNDER DIAMOND) --}}
        <div class="step-date">
            {{ $targetDate->format('d M Y') }}
        </div>
    </div>

    {{-- ACHIEVED FLAG (ON PROGRESS LINE) --}}
   @if($achievedDate)
    <div class="achieved-marker {{ $isLate ? 'late' : 'ontime' }}"
         style="left: {{ $achievedPercent }}%;">
         
        <i class="fas fa-flag"></i>

        {{-- 🔹 MILESTONE LABEL --}}
        <div class="achieved-ms">
            MS-{{ $loop->iteration }}
        </div>

        {{-- 🔹 ACHIEVED DATE --}}
        <div class="achieved-date">
            {{ $achievedDate->format('d M Y') }}
        </div>
    </div>
@endif

@endforeach


    {{-- END BOX --}}
    <div class="edge-box">
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

    <!-- LEFT -->
    <div class="col-lg-10 col-md-9">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold m-0 text-dark">
                <i class="fas fa-list-ol text-primary mr-2"></i> Milestones Detail
            </h6>
            <!-- <a href="{{ route('projects.add-milestone', $project->prj_id) }}"
               class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus mr-1"></i> Add Milestone
            </a> -->
        </div>

        <div class="milestone-container shadow-sm">
            <div class="milestone-scroll-box">
                <table class="table table-custom w-100 m-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Target</th>
                            <th>Achieved</th>
                            <th>Status</th>
                            <th class="text-right"></th>
                            <!-- <th class="text-right">Action</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($milestones as $milestone)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge badge-light border">{{ $milestone->msn_type }}</span></td>
                                <td class="fw-bold">{{ $milestone->msn_desc }}</td>
                                <td>{{ \Carbon\Carbon::parse($milestone->msn_targetdt)->format('d M') }}</td>
                                <td class="text-center">
                                    @if($milestone->msn_achvdt)
                                        <span class="text-success fw-bold">
                                            {{ \Carbon\Carbon::parse($milestone->msn_achvdt)->format('d M, Y') }}
                                        </span>
                                    @else
                                        <button class="btn btn-sm btn-outline-primary rounded-circle"
                                                onclick="openAchieveModal('{{ $milestone->msn_id }}')">
                                            <i class="fas fa-calendar-check"></i>
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    @if(strtolower($milestone->msn_status) === 'completed')
                                        <span class="badge badge-success px-3">Completed</span>
                                    @else
                                        <span class="badge badge-warning text-white px-3">{{ $milestone->msn_status }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <!-- <a href="{{ route('milestone.edit', $milestone->msn_id) }}" class="text-warning">
                                        <i class="fas fa-pen"></i>
                                    </a> -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No milestones defined yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- RIGHT -->
    <div class="col-lg-2 col-md-3">

        <div class="sticky-top" style="top:20px;">

            <h6 class="font-weight-bold text-dark mb-3">
                <i class="fas fa-calendar-alt text-primary mr-2"></i> Key Dates
            </h6>

            <div class="row gx-2">
                <div class="col-6">
                    <div class="date-grid-item {{ $project->prj_rcptdt ? 'done' : '' }}">
                        <span class="d-title">Received</span>
                        <span class="d-value">{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('d M y') : '—' }}</span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="date-grid-item {{ $project->prj_assigndt ? 'done' : '' }}">
                        <span class="d-title">Assigned</span>
                        <span class="d-value">{{ $project->prj_assigndt ? \Carbon\Carbon::parse($project->prj_assigndt)->format('d M y') : '—' }}</span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="date-grid-item {{ $project->prj_propdt ? 'done' : '' }}">
                        <span class="d-title">Proposal</span>
                        <span class="d-value">{{ $project->prj_propdt ? \Carbon\Carbon::parse($project->prj_propdt)->format('d M y') : '—' }}</span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="date-grid-item {{ $project->prj_aprvdt ? 'done' : '' }}">
                        <span class="d-title">Approved</span>
                        <span class="d-value">{{ $project->prj_aprvdt ? \Carbon\Carbon::parse($project->prj_aprvdt)->format('d M y') : '—' }}</span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="date-grid-item active">
                        <span class="d-title">Project Start</span>
                        <span class="d-value">{{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d M y') : '—' }}</span>
                    </div>
                </div>
            </div>

            <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center mb-2" data-toggle="collapse" data-target="#filesCollapse" aria-expanded="false" style="cursor:pointer;">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-folder-open text-primary mr-1"></i> Documents </h6><i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="collapse" id="filesCollapse">

                            @foreach($fixedDocs as $index => $doc)
                                @php $existingFile = $allAttachments->where('jat_type', $doc)->first(); @endphp
                                <div class="doc-card shadow-sm" style="{{ $existingFile ? 'border-left-color: #28a745; background-color: #f8fff9;' : '' }}">
                                    <div class="doc-content"><div class="doc-icon" style="{{ $existingFile ? 'color: #28a745; background-color: #e0fdf4;' : '' }}"><i class="fas {{ $existingFile ? 'fa-check-circle' : 'fa-file-alt' }}"></i></div><div class="doc-title" title="{{ $doc }}">{{ $doc }}</div></div>
                                    <div class="d-flex align-items-center">
                                        @if($existingFile)
                                            <a href="{{ route('attachment.view', $existingFile->jat_id) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle mr-1"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('attachment.delete', $existingFile->jat_id) }}" class="btn btn-sm btn-outline-danger rounded-circle mr-1 text-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                        @else
                                            <div style="width: 28px; height: 28px;"><form action="{{ route('projects.upload.single', $project->prj_id) }}" method="POST" enctype="multipart/form-data" id="form-{{$index}}">@csrf <input type="hidden" name="doc_type" value="{{ $doc }}">
                                            <label for="file-{{$index}}" class="btn rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px; cursor:pointer; padding:0; background-color:#007BFF; color:#fff; border:none;"> 
                                                <i class="fas fa-upload" style="font-size: 0.9rem;"></i>
                                            </label>
                                <input type="file" id="file-{{$index}}" name="single_file" class="file-input-hidden" onchange="document.getElementById('form-{{$index}}').submit()"></form></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div class="doc-card other shadow-sm mt-2" onclick="openOtherDocsModal()"><div class="d-flex align-items-center"><div class="doc-icon other"><i class="fas fa-layer-group"></i></div><div><div class="doc-title" style="color: #6f42c1;">Other Documents</div><small class="text-muted">{{ $otherDocsCount }} Files uploaded</small></div></div><i class="fas fa-chevron-right"></i></div>
                        </div>
                    </div>
            </div>

        </div>

    </div>

</div>
    </div>


    <!-- MODALS -->
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
                    <table class="table table-bordered table-sm mt-2 bg-white">
                        <thead class="bg-light"><tr><th>#</th><th>Document Name</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($allAttachments->whereNotIn('jat_type', $fixedDocs) as $index => $att)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><i class="fas fa-file-alt text-muted mr-2"></i> <strong>{{ $att->jat_type }}</strong></td>
                                    <td>
                                        <a href="{{ route('attachment.view', $att->jat_id) }}" target="_blank" class="btn btn-xs btn-info px-2">View</a>
                                        <a href="{{ route('attachment.delete', $att->jat_id) }}" class="btn btn-xs btn-danger px-2" onclick="return confirm('Delete?')">Delete</a>
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

    <!-- Employee & All Staff Modals (same as before) -->
    <div class="modal fade" id="employeeDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <img src="" id="empModalImg" class="emp-modal-img shadow-sm">
                    <h4 id="empModalName" class="font-weight-bold mb-1"></h4>
                    <p id="empModalRole" class="text-primary mb-4"></p>
                    <div class="text-left mt-3">
                        <div class="emp-detail-row"><span class="emp-label">Email</span><span id="empModalEmail" class="text-dark"></span></div>
                        <div class="emp-detail-row"><span class="emp-label">Phone</span><span id="empModalPhone" class="text-dark"></span></div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-block mt-4" data-dismiss="modal">Close</button>
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
                    <table class="table table-striped m-0">
                        <thead class="bg-primary text-white"><tr><th>Image</th><th>Name</th><th>Role</th><th>Contact</th></tr></thead>
                        <tbody>
                            @foreach($team as $member)
                                <tr>
                                    <td><img src="{{ $member['img'] }}" class="rounded-circle" width="35"></td>
                                    <td class="font-weight-bold">{{ $member['name'] }}</td>
                                    <td>{{ $member['role'] }}</td>
                                    <td>{{ $member['phone'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>

        function openMilestoneDetail(title, target, achieved, status){
    document.getElementById('msTitle').innerText = title;
    document.getElementById('msTarget').innerText = target;
    document.getElementById('msAchieved').innerText = achieved;
    document.getElementById('msStatus').innerText = status;
    $('#milestoneDetailModal').modal('show');
}

        function openOtherDocsModal() { $('#otherDocsModal').modal('show'); }
        function openEmployeeModal(name, role, img, email, phone) {
            document.getElementById('empModalName').innerText = name;
            document.getElementById('empModalRole').innerText = role;
            document.getElementById('empModalImg').src = img;
            document.getElementById('empModalEmail').innerText = email;
            document.getElementById('empModalPhone').innerText = phone;
            $('#employeeDetailModal').modal('show');
        }
        function openAllStaffModal() { $('#allStaffModal').modal('show'); }

        function openAchieveModal(msnId) {
            document.getElementById('modal_msn_id').value = msnId;
            $('#achieveDateModal').modal('show');
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Knob/1.2.13/jquery.knob.min.js"></script>
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
    </script>
</div>
@endsection