@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        /* --- GLOBAL STYLES --- */
        .card-primary.card-outline {
            border-top: 3px solid #007bff;
        }

        .bg-light-blue {
            background-color: #f4f7fa;
        }

        /* --- HEADER & BADGES --- */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .milestone-box-compact {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 30px;
            padding: 8px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            min-width: 350px;
            height: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
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

        /* --- KEY DATES (2 COLUMN GRID) --- */
        .date-grid-item {
            position: relative;
            padding-left: 15px;
            margin-bottom: 15px;
            border-left: 3px solid #e9ecef;
        }

        .date-grid-item.active {
            border-left-color: #007bff;
        }

        .date-grid-item.done {
            border-left-color: #28a745;
        }

        .d-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .d-value {
            font-size: 0.85rem;
            color: #333;
            font-weight: 600;
        }

        /* --- DOCUMENTS LIST --- */
        .doc-scroll-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .doc-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-left: 3px solid #007bff;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .doc-card:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .doc-content {
            display: flex;
            align-items: center;
            overflow: hidden;
            margin-right: 10px;
        }

        .doc-icon {
            width: 30px;
            height: 30px;
            background: #f4f6f9;
            color: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .doc-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #444;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-input-hidden {
            display: none !important;
        }

        .doc-card.other {
            border-left-color: #6f42c1;
            cursor: pointer;
            background: #fbf9ff;
        }

        .doc-card.other:hover {
            background-color: #f3ebff;
        }

        .doc-icon.other {
            color: #6f42c1;
            background-color: #e9dff7;
        }

        /* --- GLASSMORPHISM MODAL --- */
        .glass-modal .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border-radius: 15px;
        }

        /* --- MILESTONE TABLE SCROLL & STYLE --- */
        .milestone-container {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .milestone-scroll-box {
            max-height: 320px;
            /* Shows approx 5 rows */
            overflow-y: auto;
        }

        .milestone-scroll-box::-webkit-scrollbar {
            width: 6px;
        }

        .milestone-scroll-box::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .milestone-scroll-box::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .milestone-scroll-box::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }

        .table-custom thead th {
            background: #f8f9fa;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.75rem;
            border-bottom: 2px solid #e9ecef;
            padding: 12px 15px;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .table-custom tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            color: #525f7f;
            font-size: 0.9rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-custom tr:hover {
            background-color: #fcfcfc;
        }

        /* --- PROGRESS BARS --- */
        .task-progress-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .prog-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #555;
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .progress-slim {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        /* --- MODAL IMAGE --- */
        .emp-modal-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f4f6f9;
            margin-bottom: 15px;
        }

        .emp-detail-row {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
        }

        /* --- CHART CONTAINERS --- */
        .chart-wrapper {
            position: relative;
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-wrapper canvas {
            max-width: 100%;
            max-height: 100%;
        }
    </style>

    @php
        // 1. Milestone Calculations
        $milestones = $project->milestones->sortBy('msn_targetdt');
        $nextMilestone = $milestones->where('msn_status', '!=', 'Completed')->first();

        // Header Status Badge Logic
        $daysDiff = 0;
        $isOverdue = false;
        $statusMsg = "All Done";
        $badgeClass = "badge-secondary";

        if ($nextMilestone && $nextMilestone->msn_targetdt) {
            $target = \Carbon\Carbon::parse($nextMilestone->msn_targetdt)->startOfDay();
            $today = \Carbon\Carbon::now()->startOfDay();
            $diff = $today->diffInDays($target, false);

            if ($diff < 0) {
                $isOverdue = true;
                $daysDiff = abs($diff);
                $statusMsg = $daysDiff . " Days Late";
                $badgeClass = "badge-danger";
            } else {
                $daysDiff = $diff;
                $statusMsg = $daysDiff . " Days Left";
                $badgeClass = "badge-success";
            }
        }

        // 2. Progress Bar Logic
        $totalMilestones = $milestones->count();
        $completedMilestones = $milestones->where('msn_status', 'Completed')->count();
        $overallProgress = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;

        $currentProgress = 0;
        $currentMsName = "No Active Task";

        if($nextMilestone) {
            $currentMsName = Str::limit($nextMilestone->msn_desc, 30);
            $startDate = \Carbon\Carbon::parse($project->prj_startdt);
            $prev = $milestones->where('msn_targetdt', '<', $nextMilestone->msn_targetdt)->last();

            if($prev) {
                $startDate = \Carbon\Carbon::parse($prev->msn_targetdt);
            }

            $targetDate = \Carbon\Carbon::parse($nextMilestone->msn_targetdt);
            $now = \Carbon\Carbon::now();
            $totalDuration = $startDate->diffInDays($targetDate);
            $elapsed = $startDate->diffInDays($now);

            if($totalDuration > 0 && $now > $startDate) {
                $currentProgress = round(($elapsed / $totalDuration) * 100);
            }
            if($currentProgress > 100) $currentProgress = 100;
            if($now < $startDate) $currentProgress = 0;

        } elseif ($completedMilestones == $totalMilestones && $totalMilestones > 0) {
            $currentProgress = 100;
            $currentMsName = "Project Complete";
        }

        // 3. Team & Docs
        $team = [
            ['id'=>1, 'name'=>'Ali Khan', 'role'=>'Project Manager', 'email'=>'ali@rdwis.com', 'phone'=>'0300-1234567', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>2, 'name'=>'Sara Ahmed', 'role'=>'Senior Architect', 'email'=>'sara@rdwis.com', 'phone'=>'0300-7654321', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>3, 'name'=>'Bilal Hameed', 'role'=>'Site Engineer', 'email'=>'bilal@rdwis.com', 'phone'=>'0333-1122334', 'img'=>asset('dist/img/profile-1.jfif')],
            ['id'=>4, 'name'=>'Usman Qureshi', 'role'=>'Surveyor', 'email'=>'usman@rdwis.com', 'phone'=>'0321-9988776', 'img'=>asset('dist/img/profile-1.jfif')],
        ];
        $displayLimit = 6;
        $remaining = count($team) - $displayLimit;

        $fixedDocs = ['PPF', 'Approval Letter', 'URD', 'Work Order'];
        $allAttachments = $project->attachments;
        $otherDocsCount = $allAttachments->whereNotIn('jat_type', $fixedDocs)->count();
    @endphp

    <div class="container-fluid">

        {{-- TOP CARD --}}
        <div class="card card-primary card-outline shadow-sm border-0">
            <div class="card-header p-3 bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge badge-light border mr-2">CODE: {{ $project->prj_code }}</span>
                            <span class="badge badge-danger px-2 shadow-sm">
                                <i class="fas fa-flag-checkered mr-1"></i>
                                EDC: {{ $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt)->format('d M, Y') : 'TBD' }}
                            </span>
                        </div>
                        <h4 class="text-dark font-weight-bold m-0 text-truncate" title="{{ $project->prj_title }}">{{ $project->prj_title }}</h4>
                    </div>
                    <div class="col-md-4 text-center">
                        @if($nextMilestone)
                            <div class="milestone-box-compact">
                                <div class="d-flex align-items-center pr-3 border-right mr-3">
                                    <span class="badge {{ $badgeClass }} px-3 py-2">
                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clock' }} mr-1"></i> {{ $statusMsg }}
                                    </span>
                                </div>
                                <div class="text-left flex-grow-1" style="min-width: 0;">
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $nextMilestone->msn_desc }}">
                                        {{ $nextMilestone->msn_desc }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Target: {{ \Carbon\Carbon::parse($nextMilestone->msn_targetdt)->format('d M, Y') }}</small>
                                </div>
                            </div>
                        @else
                            <span class="badge badge-success p-2 px-3 rounded-pill">All Milestones Completed</span>
                        @endif
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="header-controls justify-content-end">
                            <a href="{{ route('projects.spendings', $project->prj_id) }}" class="btn btn-warning btn-sm shadow-sm font-weight-bold text-white">
                                <i class="fas fa-coins mr-1"></i> Spendings
                            </a>
                            <a href="{{ route('projecthistory') }}" class="btn btn-outline-secondary btn-sm shadow-sm font-weight-bold">
                                <i class="fas fa-history mr-1"></i> History
                            </a>
                            <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold px-3">
                                <i class="fas fa-file-invoice mr-1"></i> CREATE MPR
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light-blue">

                {{-- INFO PANEL (Center Split with Pie Charts) --}}
                <div class="info-panel">
                    <div class="info-left-content">
                        <div class="row w-100 m-0 align-items-center h-100">

                            {{-- COLUMN 1: Scope & Sponsor --}}
                            <div class="col-md-4 d-flex flex-column justify-content-center pl-0 border-right">
                                <div class="mb-3">
                                    <span class="badge badge-light border px-3 py-2 rounded-pill text-uppercase shadow-sm">
                                        <i class="fas fa-handshake text-primary mr-1"></i> {{ $project->prj_sponsor ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="text-dark font-weight-bold mb-1" style="font-size: 0.9rem;">Scope of Work</h6>
                                    <p class="text-muted m-0" style="font-size: 0.8rem; line-height: 1.4;">{{ Str::limit($project->prj_scope, 100) ?? 'No scope defined.' }}</p>
                                </div>
                            </div>

                            {{-- COLUMN 2: FINANCIAL PIE CHARTS (UPDATED SIZE & VISIBILITY) --}}
                            <div class="col-md-8 px-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-left">
                                        <div class="mb-2">
                                            <span class="d-block text-muted text-uppercase" style="font-size: 0.7rem; font-weight:700;">Total Budget</span>
                                            <span class="d-block text-dark font-weight-bold" style="font-size: 1rem;">Rs. {{ number_format($project->prj_propcost / 1000000, 2) }} M</span>
                                        </div>
                                        <div>
                                            <span class="d-block text-muted text-uppercase" style="font-size: 0.7rem; font-weight:700;">Total Spent</span>
                                            <span class="d-block text-danger font-weight-bold" style="font-size: 1rem;">Rs. {{ number_format($totalSpent / 1000000, 2) }} M</span>
                                        </div>
                                    </div>
                                    <div class="col-md-8 d-flex justify-content-around align-items-center">
                                        <div class="text-center">
                                            <div class="chart-wrapper">
                                                <canvas id="chartEquip"></canvas>
                                            </div>
                                            <div class="mt-2 text-muted font-weight-bold" style="font-size: 0.75rem;">Equip</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="chart-wrapper">
                                                <canvas id="chartHR"></canvas>
                                            </div>
                                            <div class="mt-2 text-muted font-weight-bold" style="font-size: 0.75rem;">HR</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="chart-wrapper">
                                                <canvas id="chartMisc"></canvas>
                                            </div>
                                            <div class="mt-2 text-muted font-weight-bold" style="font-size: 0.75rem;">Misc</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- RIGHT: Team Section --}}
                    <div class="info-right-team">
                        <div class="d-flex justify-content-end mb-2">
                            <span class="info-label text-primary">Team ({{ count($team) }})</span>
                        </div>
                        <div class="team-section-container">
                            @foreach($team as $index => $member)
                                @if($index < $displayLimit)
                                    <div class="team-avatar-wrapper" onclick="openEmployeeModal('{{ $member['name'] }}', '{{ $member['role'] }}', '{{ $member['img'] }}', '{{ $member['email'] }}', '{{ $member['phone'] }}')">
                                        <img src="{{ $member['img'] }}">
                                    </div>
                                @endif
                            @endforeach
                            <button type="button" class="more-staff-btn bg-white text-primary" onclick="openAllStaffModal()"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>

                <div class="row">

                    {{-- LEFT COLUMN: Key Dates + Documents --}}
                    <div class="col-lg-3">
                        <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-calendar-alt text-primary mr-1"></i> Key Dates</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="date-grid-item {{ $project->prj_propdt ? 'done' : '' }}">
                                    <span class="d-title">Proposal</span>
                                    <span class="d-value">{{ $project->prj_propdt ? \Carbon\Carbon::parse($project->prj_propdt)->format('d M, Y') : '--' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="date-grid-item {{ $project->prj_assigndt ? 'done' : '' }}">
                                    <span class="d-title">Assigned</span>
                                    <span class="d-value">{{ $project->prj_assigndt ? \Carbon\Carbon::parse($project->prj_assigndt)->format('d M, Y') : '--' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="date-grid-item {{ $project->prj_aprvdt ? 'done' : '' }}">
                                    <span class="d-title">Approved</span>
                                    <span class="d-value">{{ $project->prj_aprvdt ? \Carbon\Carbon::parse($project->prj_aprvdt)->format('d M, Y') : '--' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="date-grid-item {{ $project->prj_startdt ? 'active' : '' }}">
                                    <span class="d-title">Started</span>
                                    <span class="d-value">{{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d M, Y') : '--' }}</span>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-folder-open text-primary mr-1"></i> Files</h6>
                        </div>

                        @foreach($fixedDocs as $index => $doc)
                            @php $existingFile = $allAttachments->where('jat_type', $doc)->first(); @endphp
                            <div class="doc-card shadow-sm" style="{{ $existingFile ? 'border-left-color: #28a745; background-color: #f8fff9;' : '' }}">
                                <div class="doc-content">
                                    <div class="doc-icon" style="{{ $existingFile ? 'color: #28a745; background-color: #e0fdf4;' : '' }}">
                                        <i class="fas {{ $existingFile ? 'fa-check-circle' : 'fa-file-alt' }}"></i>
                                    </div>
                                    <div class="doc-title" title="{{ $doc }}">{{ $doc }}</div>
                                </div>
                                <div class="d-flex align-items-center">
                                    @if($existingFile)
                                        <a href="{{ route('attachment.view', $existingFile->jat_id) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle mr-1">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('attachment.delete', $existingFile->jat_id) }}" class="btn btn-sm btn-outline-danger rounded-circle mr-1 text-danger" onclick="return confirm('Delete?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    @else
                                        <div style="width: 28px; height: 28px;">
                                            <form action="{{ route('projects.upload.single', $project->prj_id) }}" method="POST" enctype="multipart/form-data" id="form-{{$index}}">
                                                @csrf 
                                                <input type="hidden" name="doc_type" value="{{ $doc }}">
                                                    <label for="file-{{$index}}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px; cursor:pointer;" title="Upload">
                                                    <i class="fas fa-cloud-upload-alt" style="font-size: 0.85rem;"></i>
                                                    </label>
                                                <input type="file" id="file-{{$index}}" name="single_file" class="file-input-hidden" onchange="document.getElementById('form-{{$index}}').submit()">
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="doc-card other shadow-sm mt-2" onclick="openOtherDocsModal()">
                            <div class="d-flex align-items-center">
                                <div class="doc-icon other"><i class="fas fa-layer-group"></i></div>
                                <div>
                                    <div class="doc-title" style="color: #6f42c1;">Other Documents</div>
                                    <small class="text-muted">{{ $otherDocsCount }} Files uploaded</small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Milestones --}}
                    <div class="col-lg-9">

                     <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-tasks text-primary mr-1"></i> Milestones Progress</h6>
                        </div>

                        {{-- PROGRESS BARS --}}
                        <div class="task-progress-card shadow-sm">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <div class="prog-label">
                                        <span>Overall Progress</span> 
                                        <span class="text-success">{{ $completedMilestones }} / {{ $totalMilestones }} Completed</span>
                                    </div>
                                    <div class="progress progress-slim">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $overallProgress }}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-6 pl-3">
                                    <div class="prog-label">
                                        <span>Current: {{ $currentMsName }}</span> 
                                        <span class="text-primary">{{ $currentProgress }}% Time Lapsed</span>
                                    </div>
                                    <div class="progress progress-slim">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $currentProgress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold m-0 text-dark"><i class="fas fa-tasks text-primary mr-1"></i> Milestones Detail</h6>
                            <a href="{{ route('projects.add-milestone', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                                <i class="fas fa-plus mr-1"></i> Add Milestones
                            </a>
                        </div>

                        {{-- MILESTONE TABLE with SCROLL --}}
                        <div class="milestone-container shadow-sm">
                            <div class="milestone-scroll-box">
                                <table class="table table-custom w-100 m-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Target Date</th>
                                            <th>Status</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($milestones as $milestone)
                                            <tr>
                                                <td class="font-weight-bold text-muted">{{ $loop->iteration }}</td>
                                                <td><span class="badge badge-light border">{{ $milestone->msn_type }}</span></td>
                                                <td class="font-weight-bold text-dark">{{ Str::limit($milestone->msn_desc, 60) }}</td>
                                                <td>{{ \Carbon\Carbon::parse($milestone->msn_targetdt)->format('d M, Y') }}</td>
                                                <td>
                                                    @if(Str::lower($milestone->msn_status) == 'completed') 
                                                        <span class="badge badge-success px-2">Completed</span>
                                                    @else 
                                                        <span class="badge badge-warning text-white px-2">{{ $milestone->msn_status }}</span> 
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('milestone.edit', $milestone->msn_id) }}" class="btn btn-sm btn-outline-warning mr-1"><i class="fas fa-pen"></i></a>
                                                    <a href="{{ route('milestone.delete', $milestone->msn_id) }}" class="btn btn-sm btn-outline-danger text-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No milestones found.</td>
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
</div>

{{-- MODALS --}}
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
                            <div class="col-md-5">
                                <input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document Name" required>
                            </div>
                            <div class="col-md-5">
                                <input type="file" name="doc_file" class="form-control-file form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success btn-sm btn-block">Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
                <h6 class="font-weight-bold text-dark">Existing Files</h6>
                <table class="table table-bordered table-sm mt-2 bg-white">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Document Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
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
                            <tr>
                                <td colspan="3" class="text-center text-muted">No additional documents.</td>
                            </tr> 
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="employeeDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <img src="" id="empModalImg" class="emp-modal-img shadow-sm">
                <h4 id="empModalName" class="font-weight-bold mb-1"></h4>
                <p id="empModalRole" class="text-primary mb-4"></p>
                <div class="text-left mt-3">
                    <div class="emp-detail-row">
                        <span class="emp-label">Email</span>
                        <span id="empModalEmail" class="text-dark"></span>
                    </div>
                    <div class="emp-detail-row">
                        <span class="emp-label">Phone</span>
                        <span id="empModalPhone" class="text-dark"></span>
                    </div>
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
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
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

{{-- SCRIPTS --}}
<script>
    function openOtherDocsModal() {
        $('#otherDocsModal').modal('show');
    }

    function openEmployeeModal(name, role, img, email, phone) {
        document.getElementById('empModalName').innerText = name;
        document.getElementById('empModalRole').innerText = role;
        document.getElementById('empModalImg').src = img;
        document.getElementById('empModalEmail').innerText = email;
        document.getElementById('empModalPhone').innerText = phone;
        $('#employeeDetailModal').modal('show');
    }

    function openAllStaffModal() {
        $('#allStaffModal').modal('show');
    }
</script>

{{-- CHART SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const commonConfig = {
            type: 'doughnut',
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return label + ': Rs. ' + (value / 1000000).toFixed(2) + 'M';
                            }
                        }
                    }
                },
                elements: {
                    arc: {
                        borderWidth: 2,
                        borderColor: '#fff'
                    }
                },
                cutout: '60%'
            }
        };

        const spent = {{ $totalSpent ?? 0 }};
        const budget = {{ $project->prj_propcost ?? 0 }};

        const eVal = {{ $finData['equip'] ?? 0 }};
        const hVal = {{ $finData['hr'] ?? 0 }};
        const mVal = {{ $finData['misc'] ?? 0 }};

        const eLeft = Math.max(0, budget - eVal);
        const hLeft = Math.max(0, budget - hVal);
        const mLeft = Math.max(0, budget - mVal);

        // Equipment Chart
        new Chart(document.getElementById('chartEquip'), {
            ...commonConfig,
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [eVal, eLeft],
                    backgroundColor: ['#007bff', '#e9ecef']
                }]
            }
        });

        // HR Chart
        new Chart(document.getElementById('chartHR'), {
            ...commonConfig,
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [hVal, hLeft],
                    backgroundColor: ['#6f42c1', '#e9ecef']
                }]
            }
        });

        // Misc Chart
        new Chart(document.getElementById('chartMisc'), {
            ...commonConfig,
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [mVal, mLeft],
                    backgroundColor: ['#fd7e14', '#e9ecef']
                }]
            }
        });
    });
</script>
@endsection