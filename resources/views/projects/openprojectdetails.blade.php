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
        .info-panel {
            background: #fff;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            display: flex;
            overflow: visible;
        }

        .info-left-content {
            flex: 1;
            padding: 15px;
            display: flex;
            align-items: center;
        }

        .info-right-team {
            width: 350px;
            background: #fff;
            border-left: 1px solid #e9ecef;
            padding: 10px 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #8898aa;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 0.9rem;
            color: #32325d;
            font-weight: 600;
            line-height: 1.4;
        }

        .cost-tag {
            background: #e0fdf4;
            color: #0f5132;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 700;
            border: 1px solid #b7eb8f;
            display: inline-block;
        }

        /* --- TEAM SECTION --- */
        .team-section-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 5px;
        }

        .team-avatar-wrapper {
            width: 42px;
            height: 42px;
            margin-left: -10px;
            position: relative;
            z-index: 10;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .team-avatar-wrapper:hover {
            transform: scale(1.2);
            z-index: 100;
            margin: 0 5px;
        }

        .team-avatar-wrapper img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11);
            object-fit: cover;
            background: #fff;
        }

        .more-staff-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
            color: #525f7f;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            margin-left: 5px;
            z-index: 0;
        }
        
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

        /* --- WIZARD & MILESTONES --- */
        .steps-container { 
            position: relative; display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 50px; 
            margin-top: 35px;
            padding: 0 15px; 
        }
        .steps-track { 
            position: absolute; top: 50%; left: 0; width: 100%; height: 4px; 
            background: #e9ecef; transform: translateY(-50%); z-index: 1; border-radius: 2px; 
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
            position: absolute; bottom: -25px; width: 100px; left: 50%; margin-left: -50px; 
            text-align: center; font-size: 0.55rem; color: #adb5bd; white-space: nowrap; 
        }
        .step-item.active .step-date { color: #007bff; font-weight: 700; }

        .step-tooltip {
            display: none; position: absolute; bottom: 35px; left: 50%; transform: translateX(-50%);
            background: #343a40; color: #fff; padding: 6px 10px; border-radius: 4px; 
            font-size: 0.7rem; white-space: nowrap; z-index: 100; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .step-tooltip::after {
            content: ""; position: absolute; top: 100%; left: 50%; margin-left: -5px;
            border-width: 5px; border-style: solid; border-color: #343a40 transparent transparent transparent;
        }
        .step-item:hover .step-tooltip { display: block; }

        .overall-today-marker {
            position: absolute; top: -14px; width: 2px; height: 32px;
            background: #dc3545; z-index: 3; transition: left 1s ease;
        }
        .overall-today-marker::before {
            content: attr(data-date); position: absolute; top: -16px; left: 50%; transform: translateX(-50%);
            background: #dc3545; color: #fff; font-size: 0.55rem; padding: 1px 4px; border-radius: 3px; font-weight: bold; white-space: nowrap;
        }
        .overall-today-marker::after {
            content: ''; position: absolute; bottom: -4px; left: -2px; width: 6px; height: 6px; border-radius: 50%; background: #dc3545;
        }

        .current-ms-card { background: #f8f9fa; border: 1px solid #e1e4e8; border-radius: 8px; padding: 20px; position: relative; }
        .ms-dates { display: flex; justify-content: space-between; font-size: 0.75rem; color: #6c757d; margin-bottom: 5px; font-weight: 600; }
        .ms-progress-bg { height: 8px; background: #e2e6ea; border-radius: 4px; position: relative; margin: 10px 0; }
        .ms-progress-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #007bff, #00c6ff); }
        .ms-today-marker { position: absolute; top: -6px; width: 2px; height: 20px; background: #dc3545; z-index: 5; transition: left 1s ease; }
        .ms-today-label { position: absolute; top: -20px; left: 50%; transform: translateX(-50%); background: #dc3545; color: #fff; font-size: 0.6rem; padding: 1px 5px; border-radius: 3px; white-space: nowrap; }
        .ms-today-marker::after { content: ''; position: absolute; bottom: -3px; left: -2px; width: 6px; height: 6px; border-radius: 50%; background: #dc3545; }

        .milestone-container { background: #fff; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; }
        .milestone-scroll-box { max-height: 280px; overflow-y: auto; }
        .table-custom thead th { background: #f8f9fa; color: #6c757d; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #e9ecef; padding: 12px 15px; position: sticky; top: 0; z-index: 5; }
        .table-custom tbody td { padding: 10px 15px; vertical-align: middle; color: #525f7f; font-size: 0.85rem; border-bottom: 1px solid #f0f0f0; }
        .milestone-scroll-box::-webkit-scrollbar { width: 6px; }
        .milestone-scroll-box::-webkit-scrollbar-track { background: #f1f1f1; }
        .milestone-scroll-box::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

        /* --- UPDATED MINIMAL CHART STYLE --- */
        .finance-mini-chart-wrapper {
            width: 65px; 
            height: 65px; 
            position: relative;
        }

        .finance-bars-wrap{
    display:flex;
    gap:14px;
    align-items:center;
}

.finance-box canvas{
    width:100%!important;
    height:70px!important;
}


.finance-title{
    font-size:11px;
    font-weight:700;
    margin-top:4px;
    color:#555;
}


        /* --- MODAL --- */
        .glass-modal .modal-content { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; border: 1px solid rgba(255,255,255,0.3); }
        .emp-modal-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f4f6f9; margin-bottom: 15px; }
        .emp-detail-row { border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; }
    </style>

    @php
        // 1. DATES & EDC LOGIC
        $today = \Carbon\Carbon::now()->startOfDay();
        $edc = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : null;
        $edcClass = 'text-success';
        if ($edc && $today->greaterThan($edc)) {
            $edcClass = 'text-danger'; 
        }

        // 2. MILESTONE LOGIC
        $milestones = $project->milestones->sortBy('msn_targetdt')->values(); 
        
        $nextMilestone = $milestones->where('msn_status', '!=', 'Completed')->first();
        $totalMilestones = $milestones->count();
        $completedMilestones = $milestones->where('msn_status', 'Completed')->count();
        
        // Header Status Logic
        $isOverdue = false;
        $statusMsg = "All Done";
        $statusClass = "text-secondary";
        
        if ($nextMilestone && $nextMilestone->msn_targetdt) {
            $target = \Carbon\Carbon::parse($nextMilestone->msn_targetdt)->startOfDay();
            $diff = $today->diffInDays($target, false);
            if ($diff < 0) { 
                $isOverdue = true; $statusMsg = abs($diff) . " Days Late"; $statusClass = "text-danger"; 
            } else { 
                $statusMsg = $diff . " Days Left"; $statusClass = "text-success"; 
            }
        }

        // 3. PROGRESS LOGIC (Overall)
        $overallPercent = $totalMilestones > 1 ? ($completedMilestones / ($totalMilestones - 1)) * 100 : 0;
        if($overallPercent > 100) $overallPercent = 100;
        if($totalMilestones == 1 && $completedMilestones == 1) $overallPercent = 100;

        // B. Overall Time Progress (Red Marker)
        $firstMs = $milestones->first();
        $lastMs = $milestones->last();
        $prjStart = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : ($firstMs ? \Carbon\Carbon::parse($firstMs->msn_targetdt) : $today);
        $prjEnd = $lastMs ? \Carbon\Carbon::parse($lastMs->msn_targetdt) : $today;
        
        $totalDaysSpan = $prjStart->diffInDays($prjEnd);
        if($totalDaysSpan == 0) $totalDaysSpan = 1;

        $daysPassedTotal = $prjStart->diffInDays($today, false);
        $overallTimePercent = ($daysPassedTotal / $totalDaysSpan) * 100;
        if($overallTimePercent < 0) $overallTimePercent = 0;
        if($overallTimePercent > 100) $overallTimePercent = 100;

        // 4. CURRENT MILESTONE DEEP DIVE LOGIC
        $currStart = null;
        $currEnd = null;
        $currProgress = 0;
        $currDaysTotal = 0;
        $currDaysPassed = 0;
        $isLate = false;
        $activeMsIndex = 0; 

        if ($nextMilestone) {
            $activeMsIndex = $milestones->search(function($ms) use ($nextMilestone) {
                return $ms->msn_id === $nextMilestone->msn_id;
            }) + 1;

            $currEnd = \Carbon\Carbon::parse($nextMilestone->msn_targetdt);
            $currStart = \Carbon\Carbon::parse($project->prj_startdt); 
            $prev = $milestones->where('msn_targetdt', '<', $nextMilestone->msn_targetdt)->last();
            if ($prev) {
                $currStart = \Carbon\Carbon::parse($prev->msn_targetdt);
            }

            $currDaysTotal = $currStart->diffInDays($currEnd);
            if($currDaysTotal == 0) $currDaysTotal = 1;

            $currDaysPassed = $currStart->diffInDays($today, false); 
            
            if ($today->greaterThan($currStart)) {
                $currProgress = ($currDaysPassed / $currDaysTotal) * 100;
            }
            if ($currProgress < 0) $currProgress = 0;
            if ($currProgress > 100) { $currProgress = 100; $isLate = true; }
            if ($today->greaterThan($currEnd)) $isLate = true;

        } elseif ($completedMilestones == $totalMilestones && $totalMilestones > 0) {
            $currProgress = 100;
        }

        // 5. DATA
        $team = [
            ['id'=>1, 'name'=>'Ali Khan', 'role'=>'Project Manager', 'email'=>'ali@rdwis.com', 'phone'=>'0300-1234567', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>2, 'name'=>'Sara Ahmed', 'role'=>'Senior Architect', 'email'=>'sara@rdwis.com', 'phone'=>'0300-7654321', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>3, 'name'=>'Bilal Hameed', 'role'=>'Site Engineer', 'email'=>'bilal@rdwis.com', 'phone'=>'0333-1122334', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>4, 'name'=>'Usman Qureshi', 'role'=>'Surveyor', 'email'=>'usman@rdwis.com', 'phone'=>'0321-9988776', 'img'=>asset('dist/img/profile-1.jfif')],
        ];
        $displayLimit = 6;
        $fixedDocs = ['PPF', 'Approval Letter', 'URD', 'Work Order'];
        $allAttachments = $project->attachments;
        $otherDocsCount = $allAttachments->whereNotIn('jat_type', $fixedDocs)->count();
    @endphp

    <div class="container-fluid">
        
        {{-- TOP CARD --}}
        <div class="card card-primary card-outline shadow-sm border-0">
            <div class="card-header p-3 bg-white border-bottom">
                <div class="row align-items-center">
                    
                    {{-- LEFT --}}
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge badge-light border mr-2">CODE: {{ $project->prj_code }}</span>
                            <span class="font-weight-bolder {{ $edcClass }} small">
                                <i class="fas fa-flag-checkered mr-1"></i> 
                                EDC: {{ $edc ? $edc->format('d M, Y') : 'TBD' }}
                            </span>
                        </div>
                        <h4 class="text-dark font-weight-bold m-0 text-truncate" title="{{ $project->prj_title }}">{{ $project->prj_title }}</h4>
                    </div>

                    {{-- CENTER --}}
                    <div class="col-md-4 text-center">
                        @if($nextMilestone)
                            <div class="milestone-box-compact">
                                <div class="d-flex align-items-center pr-3 border-right mr-3">
                                    <span class="font-weight-bold {{ $statusClass }}" style="font-size: 0.9rem;">
                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clock' }} mr-1"></i> {{ $statusMsg }}
                                    </span>
                                </div>
                                <div class="text-left flex-grow-1" style="min-width: 0;">
                                    <div class="font-weight-bold text-dark text-truncate" style="max-width: 150px;">{{ $nextMilestone->msn_desc }}</div>
                                    <small class="text-muted">Target: {{ \Carbon\Carbon::parse($nextMilestone->msn_targetdt)->format('d M, Y') }}</small>
                                </div>
                            </div>
                        @else
                            <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> All Milestones Completed</span>
                        @endif
                    </div>

                    {{-- RIGHT --}}
                    <div class="col-md-4 text-right">
                        <div class="header-controls justify-content-end">
                            <a href="{{ route('projecthistory') }}" class="btn btn-outline-primary btn-sm shadow-sm font-weight-bold"><i class="fas fa-list-alt mr-1"></i> View MPRs</a>
                            <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold px-3"><i class="fas fa-plus-circle mr-1"></i> Create MPR</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light-blue">
                
                {{-- INFO PANEL (UPDATED LAYOUT) --}}
                <div class="info-panel">
                    <div class="info-left-content">
                        <div class="row w-100 m-0 align-items-center h-100">
                            
                            {{-- Scope & Sponsor (Given more space now) --}}
                            <div class="col-md-7 d-flex flex-column justify-content-center pl-0 border-right">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-light border px-3 py-1 rounded-pill text-uppercase shadow-sm mr-2">
                                        <i class="fas fa-handshake text-primary mr-1"></i> {{ $project->prj_sponsor ?? 'N/A' }}
                                    </span>
                                    <span class="text-muted small"> | Scope of Work</span>
                                </div>
                                <div>
                                    <p class="text-dark m-0 font-weight-normal" style="font-size: 0.9rem; line-height: 1.5;">{{ Str::limit($project->prj_scope, 160) ?? 'No scope defined.' }}</p>
                                </div>
                            </div>

                            {{-- Financials (Compact & Minimized to Right) --}}
                            <div class="col-md-5 px-3">
                                <div class="d-flex align-items-center justify-content-end">
                                    
                                    {{-- Text Stats --}}
                                    <div class="text-right mr-3">
                                        <div class="mb-1">
                                            <span class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight:700;">Total Budget</span><br>
                                            <span class="text-dark font-weight-bold" style="font-size: 0.95rem;">Rs. {{ number_format($project->prj_propcost / 1000000, 2) }} M</span>
                                        </div>
                                        <div>
                                            <span class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight:700;">Spent</span><br>
                                            <span class="text-danger font-weight-bold" style="font-size: 0.95rem;">Rs. {{ number_format($totalSpent / 1000000, 2) }} M</span>
                                        </div>
                                    </div>

                                    
                                   <div class="finance-bars-wrap">

    <div class="finance-box">
        <canvas id="equipChart"></canvas>
        <div class="finance-title">Equip</div>
    </div>

    <div class="finance-box">
        <canvas id="hrChart"></canvas>
        <div class="finance-title">HR</div>
    </div>

    <div class="finance-box">
        <canvas id="miscChart"></canvas>
        <div class="finance-title">Misc</div>
    </div>

</div>


                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Team --}}
                    <div class="info-right-team">
                        <div class="d-flex justify-content-end mb-2"><span class="info-label text-primary">Team ({{ count($team) }})</span></div>
                        <div class="team-section-container">
                            @foreach($team as $index => $member)
                                @if($index < $displayLimit)
                                    <div class="team-avatar-wrapper" onclick="openEmployeeModal('{{ $member['name'] }}', '{{ $member['role'] }}', '{{ $member['img'] }}', '{{ $member['email'] }}', '{{ $member['phone'] }}')"><img src="{{ $member['img'] }}"></div>
                                @endif
                            @endforeach
                            <button type="button" class="more-staff-btn bg-white text-primary" onclick="openAllStaffModal()"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    
                    {{-- LEFT COLUMN --}}
                    <div class="col-lg-2">
                        <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-calendar-alt text-primary mr-1"></i> Key Dates</h6>
                        <div class="row">
                            <div class="col-6"><div class="date-grid-item {{ $project->prj_rcptdt ? 'done' : '' }}"><span class="d-title">Received</span><span class="d-value">{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('d M y') : '--' }}</span></div></div>
                            <div class="col-6"><div class="date-grid-item {{ $project->prj_assigndt ? 'done' : '' }}"><span class="d-title">Assigned</span><span class="d-value">{{ $project->prj_assigndt ? \Carbon\Carbon::parse($project->prj_assigndt)->format('d M y') : '--' }}</span></div></div>
                            <div class="col-6"><div class="date-grid-item {{ $project->prj_propdt ? 'done' : '' }}"><span class="d-title">Proposal</span><span class="d-value">{{ $project->prj_propdt ? \Carbon\Carbon::parse($project->prj_propdt)->format('d M y') : '--' }}</span></div></div>
                            <div class="col-6"><div class="date-grid-item {{ $project->prj_aprvdt ? 'done' : '' }}"><span class="d-title">Approved</span><span class="d-value">{{ $project->prj_aprvdt ? \Carbon\Carbon::parse($project->prj_aprvdt)->format('d M y') : '--' }}</span></div></div>
                            <div class="col-12"><div class="date-grid-item {{ $project->prj_startdt ? 'active' : '' }}"><span class="d-title">Project Start</span><span class="d-value">{{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d M y') : '--' }}</span></div></div>
                        </div>

                        <hr class="my-4">
                        
                        {{-- COLLAPSIBLE FILES --}}
                        <div class="d-flex justify-content-between align-items-center mb-2" data-toggle="collapse" data-target="#filesCollapse" style="cursor:pointer;">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-folder-open text-primary mr-1"></i> Files</h6><i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="collapse show" id="filesCollapse">
                            @foreach($fixedDocs as $index => $doc)
                                @php $existingFile = $allAttachments->where('jat_type', $doc)->first(); @endphp
                                <div class="doc-card shadow-sm" style="{{ $existingFile ? 'border-left-color: #28a745; background-color: #f8fff9;' : '' }}">
                                    <div class="doc-content"><div class="doc-icon" style="{{ $existingFile ? 'color: #28a745; background-color: #e0fdf4;' : '' }}"><i class="fas {{ $existingFile ? 'fa-check-circle' : 'fa-file-alt' }}"></i></div><div class="doc-title" title="{{ $doc }}">{{ $doc }}</div></div>
                                    <div class="d-flex align-items-center">
                                        @if($existingFile)
                                            <a href="{{ route('attachment.view', $existingFile->jat_id) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle mr-1"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('attachment.delete', $existingFile->jat_id) }}" class="btn btn-sm btn-outline-danger rounded-circle mr-1 text-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                        @else
                                            <div style="width: 28px; height: 28px;">
                                                <form action="{{ route('projects.upload.single', $project->prj_id) }}" method="POST" enctype="multipart/form-data" id="form-{{$index}}">
                                                    @csrf <input type="hidden" name="doc_type" value="{{ $doc }}">
                                   <label for="file-{{$index}}" 
       class="btn rounded-circle d-flex align-items-center justify-content-center" 
       style="width:28px; height:28px; cursor:pointer; padding:0; background-color:#007BFF; color:#fff; border:none;">
    <i class="fas fa-upload" style="font-size: 0.9rem;"></i>
</label>


                                                    <input type="file" id="file-{{$index}}" name="single_file" class="file-input-hidden" onchange="document.getElementById('form-{{$index}}').submit()">
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div class="doc-card other shadow-sm mt-2" onclick="openOtherDocsModal()">
                                <div class="d-flex align-items-center"><div class="doc-icon other"><i class="fas fa-layer-group"></i></div><div><div class="doc-title" style="color: #6f42c1;">Other Documents</div><small class="text-muted">{{ $otherDocsCount }} Files uploaded</small></div></div><i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Milestones --}}
                    <div class="col-lg-10">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-chart-line text-primary mr-1"></i> Milestone Progress</h6>
                        </div>

                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-body p-3">
                                
                                {{-- 1. OVERALL JOURNEY (Stepped Wizard with Tooltips & Marker) --}}
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-uppercase text-muted font-weight-bold">Overall Journey</small>
                                        <span class="badge badge-light border">{{ $completedMilestones }} / {{ $totalMilestones }} Completed</span>
                                    </div>
                                    <div class="steps-container">
                                        <div class="steps-track">
                                            <div class="steps-fill" style="width: {{ $overallPercent }}%;"></div>
                                            {{-- Overall Time Marker --}}
                                            <div class="overall-today-marker" style="left: {{ $overallTimePercent }}%;" data-date="{{ \Carbon\Carbon::now()->format('d M') }}"></div>
                                        </div>
                                        @foreach($milestones as $ms)
                                            @php 
                                                $stepClass = '';
                                                if(Str::lower($ms->msn_status) == 'completed') $stepClass = 'completed';
                                                elseif($ms->msn_id == optional($nextMilestone)->msn_id) $stepClass = 'active';
                                                
                                                // Date with Year
                                                $msDate = \Carbon\Carbon::parse($ms->msn_targetdt)->format('d M Y'); 
                                            @endphp
                                            <div class="step-item {{ $stepClass }}">
                                                {{-- LABEL UPAR --}}
                                                <div class="step-label">MS-{{ $loop->iteration }}</div>
                                                
                                                {{-- DOT CENTER --}}
                                                <div class="step-dot">
                                                    @if($stepClass == 'completed') <i class="fas fa-check text-white" style="font-size:0.5rem"></i> @else {{ $loop->iteration }} @endif
                                                </div>
                                                
                                                {{-- DATE NEECHE --}}
                                                <div class="step-date">{{ $msDate }}</div>
                                                
                                                {{-- Tooltip --}}
                                                <div class="step-tooltip">
                                                    <strong>{{ Str::limit($ms->msn_desc, 30) }}</strong><br>
                                                    <span class="text-xs text-light">Target: {{ $msDate }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- 2. CURRENT MILESTONE DEEP DIVE --}}
                                @if($nextMilestone)
                                <div class="current-ms-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="badge badge-primary mb-1">Active: MS-{{ $activeMsIndex }}</span>
                                            <h6 class="font-weight-bold text-dark m-0" style="font-size: 0.9rem;">{{ Str::limit($nextMilestone->msn_desc, 60) }}</h6>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-muted d-block">Status</small>
                                            @if($isLate) <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-circle"></i> Late</span> @else <span class="text-success font-weight-bold"><i class="fas fa-running"></i> On Track</span> @endif
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="ms-dates"><span><i class="far fa-calendar-check mr-1"></i> Start: {{ $currStart->format('d M Y') }}</span><span>Target: {{ $currEnd->format('d M Y') }} <i class="far fa-flag ml-1"></i></span></div>
                                        <div class="ms-progress-bg">
                                            <div class="ms-progress-fill" style="width: {{ $currProgress }}%;"></div>
                                            <div class="ms-today-marker" style="left: {{ $currProgress }}%;"><div class="ms-today-label">{{ \Carbon\Carbon::now()->format('d M Y') }}</div></div>
                                        </div>
                                        <div class="text-center mt-2"><small class="text-muted">{{ $currDaysPassed }} days passed out of {{ $currDaysTotal }}</small></div>
                                    </div>
                                </div>
                                @elseif($totalMilestones > 0)
                                    <div class="alert alert-success text-center m-0"><i class="fas fa-glass-cheers fa-2x mb-2"></i><br>All Project Milestones Successfully Completed!</div>
                                @else
                                    <div class="alert alert-light border text-center m-0">No milestones defined yet.</div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-list-ol text-primary mr-1"></i> Milestones Detail</h6>
                            <!-- <a href="{{ route('projects.add-milestone', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold"><i class="fas fa-plus mr-1"></i> Add Milestones</a> -->
                        </div>
                        
                        <div class="milestone-container shadow-sm">
                            <div class="milestone-scroll-box">
                                <table class="table table-custom w-100 m-0">
                                    <thead><tr><th style="width: 50px;">#</th><th>Type</th><th>Description</th><th>Target Date</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                                    <tbody>
                                        @forelse($milestones as $milestone)
                                        <tr>
                                            <td class="font-weight-bold text-muted">{{ $loop->iteration }}</td>
                                            <td><span class="badge badge-light border">{{ $milestone->msn_type }}</span></td>
                                            <td class="font-weight-bold text-dark">{{ Str::limit($milestone->msn_desc, 60) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($milestone->msn_targetdt)->format('d M, Y') }}</td>
                                            <td>@if(Str::lower($milestone->msn_status) == 'completed') <span class="badge badge-success px-2">Completed</span> @else <span class="badge badge-warning text-white px-2">{{ $milestone->msn_status }}</span> @endif</td>
                                            <td class="text-right">
                                                <a href="{{ route('milestone.edit', $milestone->msn_id) }}" class="btn btn-sm btn-outline-warning mr-1"><i class="fas fa-pen"></i></a>
                                                <!-- <a href="{{ route('milestone.delete', $milestone->msn_id) }}" class="btn btn-sm btn-outline-danger text-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash small"></i></a> -->
                                            </td>
                                        </tr>
                                        @empty <tr><td colspan="6" class="text-center py-4 text-muted">No milestones found.</td></tr> @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS & SCRIPTS (Same) --}}
<div class="modal fade glass-modal" id="otherDocsModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title font-weight-bold"><i class="fas fa-copy text-primary mr-2"></i>Other Attachments</h5><button class="close" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><div class="card card-body bg-light border mb-4"><h6 class="text-primary font-weight-bold mb-3">Upload New Document</h6><form action="{{ route('projects.upload-other', $project->prj_id) }}" method="POST" enctype="multipart/form-data">@csrf <div class="row"><div class="col-md-5"><input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document Name" required></div><div class="col-md-5"><input type="file" name="doc_file" class="form-control-file form-control-sm" required></div><div class="col-md-2"><button type="submit" class="btn btn-success btn-sm btn-block">Upload</button></div></div></form></div><h6 class="font-weight-bold text-dark">Existing Files</h6><table class="table table-bordered table-sm mt-2 bg-white"><thead class="bg-light"><tr><th>#</th><th>Document Name</th><th>Action</th></tr></thead><tbody>@forelse($allAttachments->whereNotIn('jat_type', $fixedDocs) as $index => $att)<tr><td>{{ $loop->iteration }}</td><td><i class="fas fa-file-alt"></i> <strong>{{ $att->jat_type }}</strong></td><td><a href="{{ route('attachment.view', $att->jat_id) }}" target="_blank" class="btn btn-xs btn-info px-2">View</a> <a href="{{ route('attachment.delete', $att->jat_id) }}" class="btn btn-xs btn-danger px-2" onclick="return confirm('Delete?')">Delete</a></td></tr>@empty <tr><td colspan="3" class="text-center text-muted">No additional documents.</td></tr> @endforelse</tbody></table></div></div></div></div>
<div class="modal fade" id="employeeDetailModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center p-4"><img src="" id="empModalImg" class="emp-modal-img shadow-sm"><h4 id="empModalName" class="font-weight-bold mb-1"></h4><p id="empModalRole" class="text-primary mb-4"></p><div class="text-left mt-3"><div class="emp-detail-row"><span class="emp-label">Email</span><span id="empModalEmail" class="text-dark"></span></div><div class="emp-detail-row"><span class="emp-label">Phone</span><span id="empModalPhone" class="text-dark"></span></div></div><button type="button" class="btn btn-secondary btn-block mt-4" data-dismiss="modal">Close</button></div></div></div></div>
<div class="modal fade" id="allStaffModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-light"><h5 class="modal-title">Project Team</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body p-0"><table class="table table-striped m-0"><thead class="bg-primary text-white"><tr><th>Image</th><th>Name</th><th>Role</th><th>Contact</th></tr></thead><tbody>@foreach($team as $member)<tr><td><img src="{{ $member['img'] }}" class="rounded-circle" width="35"></td><td class="font-weight-bold">{{ $member['name'] }}</td><td>{{ $member['role'] }}</td><td>{{ $member['phone'] }}</td></tr>@endforeach</tbody></table></div></div></div></div>

<script>
    function openOtherDocsModal() { $('#otherDocsModal').modal('show'); }
    function openEmployeeModal(name, role, img, email, phone) { document.getElementById('empModalName').innerText = name; document.getElementById('empModalRole').innerText = role; document.getElementById('empModalImg').src = img; document.getElementById('empModalEmail').innerText = email; document.getElementById('empModalPhone').innerText = phone; $('#employeeDetailModal').modal('show'); }
    function openAllStaffModal() { $('#allStaffModal').modal('show'); }
</script>



<script>
document.addEventListener("DOMContentLoaded", function () {

    // Sample data — tumhare actual data ke hisaab se replace karo
    const equipSpent = {{ $equipSpent ?? 65 }};
    const equipTotal = {{ $equipTotal ?? 100 }};

    const hrSpent = {{ $hrSpent ?? 40 }};
    const hrTotal = {{ $hrTotal ?? 80 }};

    const miscSpent = {{ $miscSpent ?? 20 }};
    const miscTotal = {{ $miscTotal ?? 60 }};

    // Function to create pie chart
    function makePie(id, spent, total){
        const canvas = document.getElementById(id);
        if(!canvas) return;

        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Spent','Remaining'],
                datasets: [{
                    data: [spent, total - spent],
                    backgroundColor: ['#FC7A58','#0AB4EF'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins:{
                    legend:{ display:false },
                    tooltip:{ enabled:true }
                }
            }
        });
    }

    // Initialize all charts
    makePie('equipChart', equipSpent, equipTotal);
    makePie('hrChart', hrSpent, hrTotal);
    makePie('miscChart', miscSpent, miscTotal);
});
</script>



@endsection