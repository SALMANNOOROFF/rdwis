@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .hr-hub {
        font-family: 'Inter', sans-serif;
        background: #080b0f !important;
        min-height: 100vh;
        color: #cbd5e0;
        padding-top: 15px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    /* Cyber Glass Panels */
    .card-cyber {
        background: rgba(18, 26, 34, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-cyan {
        box-shadow: 0 0 0 1px rgba(0, 191, 255, 0.06), 0 0 24px rgba(0, 191, 255, 0.04);
        border-left: 3px solid rgba(0, 191, 255, 0.5);
    }
    .card-cyan:hover {
        border-color: rgba(0, 191, 255, 0.3);
        box-shadow: 0 0 0 1px rgba(0, 191, 255, 0.15), 0 0 32px rgba(0, 191, 255, 0.1);
        transform: translateY(-2px);
    }

    .card-green {
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.06), 0 0 24px rgba(34, 197, 94, 0.04);
        border-left: 3px solid rgba(34, 197, 94, 0.5);
    }
    .card-green:hover {
        border-color: rgba(34, 197, 94, 0.3);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.15), 0 0 32px rgba(34, 197, 94, 0.1);
        transform: translateY(-2px);
    }

    .card-amber {
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.06), 0 0 24px rgba(245, 158, 11, 0.03);
        border-left: 3px solid rgba(245, 158, 11, 0.5);
    }
    .card-amber:hover {
        border-color: rgba(245, 158, 11, 0.3);
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.15), 0 0 32px rgba(245, 158, 11, 0.08);
        transform: translateY(-2px);
    }

    .card-purple {
        box-shadow: 0 0 0 1px rgba(168, 85, 247, 0.06), 0 0 24px rgba(168, 85, 247, 0.03);
        border-left: 3px solid rgba(168, 85, 247, 0.5);
    }
    .card-purple:hover {
        border-color: rgba(168, 85, 247, 0.3);
        box-shadow: 0 0 0 1px rgba(168, 85, 247, 0.15), 0 0 32px rgba(168, 85, 247, 0.08);
        transform: translateY(-2px);
    }

    /* Metric Numbers */
    .metric-value {
        font-family: 'Rajdhani', sans-serif;
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -0.5px;
    }

    .metric-label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #718096;
    }

    /* Quick Action Buttons */
    .btn-quick {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 0.85rem;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-quick:hover {
        background: rgba(0, 191, 255, 0.12);
        border-color: rgba(0, 191, 255, 0.4);
        color: #00d2ff;
        transform: translateY(-1px);
    }

    /* Dark Table */
    .table-cyber {
        width: 100%;
        margin-bottom: 0;
        color: #e2e8f0;
    }
    .table-cyber thead th {
        background: rgba(0, 0, 0, 0.35);
        color: #a0aec0;
        font-family: 'Rajdhani', sans-serif;
        font-size: 0.75rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 10px 14px;
    }
    .table-cyber tbody td {
        padding: 11px 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.04);
        vertical-align: middle;
        font-size: 0.85rem;
    }
    .table-cyber tbody tr:hover {
        background: rgba(255, 255, 255, 0.025);
    }
</style>

<div class="content-wrapper hr-hub px-3 pb-5">
    <div class="container-fluid">
        
        {{-- Header Bar --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 15px;">
            <div>
                <h1 class="m-0 rajdhani text-white font-weight-bold" style="font-size: 1.8rem;">
                    <i class="fas fa-id-card-alt text-info mr-2"></i> Human Resources Directorate
                </h1>
                <p class="text-muted small m-0 rajdhani" style="letter-spacing: 0.5px;">
                    HR Operational Intelligence, Deployment & Contract Scrutiny Hub
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Mode Switcher (All Dept vs My Dept) --}}
                <div class="btn-group btn-group-sm shadow-sm mr-2" role="group" style="border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; overflow: hidden;">
                    <a href="{{ route('hr.dashboard', ['mode' => 'm']) }}" 
                       class="btn font-weight-bold rajdhani px-3 py-1" 
                       style="{{ $mode === 'm' ? 'background: linear-gradient(135deg, #00d2ff, #007bff); color: #fff; box-shadow: 0 0 10px rgba(0,210,255,0.4);' : 'background: rgba(255,255,255,0.03); color: #a0aec0;' }}">
                        <i class="fas fa-globe mr-1"></i> ALL DEPT
                    </a>
                    <a href="{{ route('hr.dashboard', ['mode' => 's']) }}" 
                       class="btn font-weight-bold rajdhani px-3 py-1" 
                       style="{{ $mode === 's' ? 'background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 0 10px rgba(16,185,129,0.4);' : 'background: rgba(255,255,255,0.03); color: #a0aec0;' }}">
                        <i class="fas fa-sitemap mr-1"></i> MY DEPT
                    </a>
                </div>

                <a href="{{ route('divhr.employelist', ['mode' => $mode]) }}" class="btn-quick">
                    <i class="fas fa-users text-primary"></i> Staff Directory
                </a>
                <a href="{{ route('divhr.attendance') }}" class="btn-quick">
                    <i class="fas fa-calendar-check text-success"></i> Attendance
                </a>
                <a href="{{ route('hr.contract-cases.index') }}" class="btn-quick">
                    <i class="fas fa-file-signature text-warning"></i> Contract Cases
                </a>
                <a href="{{ route('hr.reports.index') }}" class="btn-quick">
                    <i class="fas fa-chart-pie text-cyan"></i> HR Reports
                </a>
            </div>
        </div>

        {{-- 4 Metric Cards --}}
        <div class="row mb-4">
            {{-- 1. Active Employees --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card card-cyber card-cyan p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">ACTIVE EMPLOYEES</div>
                            <div class="metric-value text-white mt-1">{{ number_format($activeCount) }}</div>
                            <div class="small text-muted mt-2">
                                <span class="text-info font-weight-bold">{{ number_format($totalEmployees) }}</span> Total Recorded Staff
                            </div>
                        </div>
                        <div class="p-2 rounded-circle" style="background: rgba(0,191,255,0.1); color: #00d2ff;">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Previous / Inactive --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card card-cyber card-amber p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">PREVIOUS / RETIRED</div>
                            <div class="metric-value text-warning mt-1">{{ number_format($previousCount) }}</div>
                            <div class="small text-muted mt-2">
                                Historical & Relieved Personnel
                            </div>
                        </div>
                        <div class="p-2 rounded-circle" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                            <i class="fas fa-user-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Total Contracts --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card card-cyber card-green p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">TOTAL CONTRACTS</div>
                            <div class="metric-value text-success mt-1">{{ number_format($totalContracts) }}</div>
                            <div class="small text-muted mt-2">
                                Active & Historical Contract Plans
                            </div>
                        </div>
                        <div class="p-2 rounded-circle" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                            <i class="fas fa-file-contract fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Contract Cases In Pipeline --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card card-cyber card-purple p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label">CONTRACT CASES</div>
                            <div class="metric-value text-purple mt-1" style="color: #c084fc;">
                                {{ number_format($pendingCasesCount + $inApprovalCasesCount) }}
                            </div>
                            <div class="small text-muted mt-2">
                                <span class="text-warning font-weight-bold">{{ $pendingCasesCount }}</span> Pending HR Scrutiny
                            </div>
                        </div>
                        <div class="p-2 rounded-circle" style="background: rgba(168,85,247,0.1); color: #a855f7;">
                            <i class="fas fa-tasks fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Deployment By Division & Recent Joiners --}}
        <div class="row mb-4">
            {{-- Division-wise Deployment --}}
            <div class="col-xl-7 mb-4 mb-xl-0">
                <div class="card card-cyber h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 rajdhani text-white font-weight-bold" style="font-size: 1.1rem;">
                            <i class="fas fa-sitemap text-info mr-2"></i> Division & Directorate Staff Deployment
                        </h6>
                        <span class="badge badge-dark rajdhani px-2 py-1" style="background: rgba(255,255,255,0.06); color: #a0aec0;">
                            {{ count($unitBreakdown) }} UNITS
                        </span>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-cyber table-hover">
                            <thead>
                                <tr>
                                    <th>Unit / Division</th>
                                    <th class="text-center">Active Staff</th>
                                    <th class="text-center">Total Staff</th>
                                    <th>Deployment Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unitBreakdown as $unit)
                                @php
                                    $pct = $activeCount > 0 ? round(($unit->active_count / $activeCount) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="font-weight-bold text-white">
                                        <i class="fas fa-building text-muted mr-1"></i> {{ $unit->unt_name }}
                                        @if($unit->unt_namesh)
                                            <span class="text-muted small ml-1">({{ $unit->unt_namesh }})</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-info">
                                        {{ $unit->active_count }}
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ $unit->total_count }}
                                    </td>
                                    <td style="width: 28%;">
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <div class="progress flex-grow-1" style="height: 6px; background: rgba(255,255,255,0.06); border-radius: 4px;">
                                                <div class="progress-bar bg-info" style="width: {{ $pct }}%; border-radius: 4px;"></div>
                                            </div>
                                            <span class="small text-muted rajdhani" style="min-width: 38px;">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Staff / Quick Details --}}
            <div class="col-xl-5">
                <div class="card card-cyber h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 rajdhani text-white font-weight-bold" style="font-size: 1.1rem;">
                            <i class="fas fa-user-plus text-success mr-2"></i> Recent Joiners & Active Personnel
                        </h6>
                        <a href="{{ route('divhr.employelist') }}" class="small text-info rajdhani font-weight-bold">
                            VIEW ALL <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-cyber table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Unit</th>
                                    <th>Joined Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentEmployees as $emp)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-white">{{ $emp->emp_name }}</div>
                                        <div class="small text-muted">ID: {{ $emp->emp_id }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-dark" style="background: rgba(255,255,255,0.08); color: #cbd5e0;">
                                            {{ $emp->unt_namesh ?? ($emp->unt_name ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="text-muted small rajdhani">
                                        {{ $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d M, Y') : '—' }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="btn btn-xs btn-outline-info" style="border-radius: 4px;" title="View Profile">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
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
</div>
@endsection
