@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
:root { --rd-text-primary: #007bff; --rd-text-warning: #f39c12; --rd-text-success: #28a745; --rd-surface: #0d1218; --rd-border: rgba(255,255,255,0.08); }
.hub-page { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; }
.hub-header { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.metric-card { background: #0d1218; border: 1px solid var(--rd-border); border-radius: 12px; transition: transform 0.2s; }
.metric-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.15); }
.metric-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #8a96a3; }
.metric-value { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; color: #fff; line-height: 1; margin-top: 5px; }
.hub-tab-btn { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.8px; border-radius: 30px !important; padding: 10px 24px !important; transition: all 0.2s; margin-right: 10px; border: 1px solid var(--rd-border) !important; background: transparent !important; color: #8a96a3 !important; }
.hub-tab-btn.active { background: var(--rd-text-primary) !important; color: #fff !important; border-color: var(--rd-text-primary) !important; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
.dg-case-table thead th { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #8a96a3; background: #0f161e; border: none !important; padding: 14px; }
.dg-case-table td { padding: 14px; color: #cbd5e0; vertical-align: middle; border-top: 1px solid var(--rd-border); border-bottom: none; }
.dg-case-table tr:hover { background: rgba(255,255,255,0.02); }
.case-title { font-weight: 600; color: #fff; font-size: 14px; }
.case-value { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; color: var(--rd-text-primary); }
.status-badge { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 12px; border-radius: 6px; }
</style>

<div class="content-wrapper hub-page">
    <div class="content-header py-4 bg-transparent mb-2">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="hub-header">
                        <span class="badge badge-primary px-3 py-1 mb-2" style="font-size: 10px; letter-spacing: 1px;">DIVISION OPERATIONS</span>
                        <h1 class="m-0 font-weight-bold text-white" style="font-size: 2.2rem;">Contract Case Hub</h1>
                        <p class="text-muted mb-0" style="font-size: 14px; opacity: 0.7;">Central specialized portal for creating and tracking contract workflows.</p>
                    </div>
                </div>
            </div>
            
            <!-- Initiative Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Hg']) }}" class="text-decoration-none">
                        <div class="metric-card p-3 text-center" style="border-top: 3px solid #007bff;">
                            <i class="fas fa-user-plus fa-2x mb-2 text-primary"></i>
                            <h6 class="font-weight-bold text-white mb-0 rajdhani">New Hiring</h6>
                            <small class="text-muted">Fresh Candidate</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Ce']) }}" class="text-decoration-none">
                        <div class="metric-card p-3 text-center" style="border-top: 3px solid #28a745;">
                            <i class="fas fa-user-clock fa-2x mb-2 text-success"></i>
                            <h6 class="font-weight-bold text-white mb-0 rajdhani">Extension</h6>
                            <small class="text-muted">Same Terms</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Cr']) }}" class="text-decoration-none">
                        <div class="metric-card p-3 text-center" style="border-top: 3px solid #f39c12;">
                            <i class="fas fa-sync-alt fa-2x mb-2 text-warning"></i>
                            <h6 class="font-weight-bold text-white mb-0 rajdhani">Renewal</h6>
                            <small class="text-muted">New Terms</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Rh']) }}" class="text-decoration-none">
                        <div class="metric-card p-3 text-center" style="border-top: 3px solid #17a2b8;">
                            <i class="fas fa-undo fa-2x mb-2 text-info"></i>
                            <h6 class="font-weight-bold text-white mb-0 rajdhani">Rehiring</h6>
                            <small class="text-muted">Past Employee</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Main Hub Table -->
            <div class="card bg-transparent border-0">
                <div class="card-header bg-transparent border-0 p-0 mb-3">
                    <ul class="nav nav-pills" id="hubTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn active" data-toggle="tab" href="#action-req">
                                <i class="fas fa-folder-open mr-2"></i> ACTION REQUIRED ({{ collect($actionReqCases)->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#initiated">
                                <i class="fas fa-stream mr-2"></i> OPEN ({{ collect($initiatedCases)->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#completed">
                                <i class="fas fa-history mr-2"></i> CLOSE ({{ collect($completedCases)->count() }})
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        
                        {{-- TAB 1: ACTION REQUIRED --}}
                        <div class="tab-pane fade show active" id="action-req">
                            <div class="metric-card overflow-hidden">
                                @if(collect($actionReqCases)->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Candidate / Designation</th>
                                                    <th class="text-right">Salary</th>
                                                    <th class="text-center">Current Status</th>
                                                    <th class="text-right pr-4">Commands</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($actionReqCases as $p)
                                                <tr data-id="{{ $p->ctc_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                                                    <td>
                                                        <div class="case-title">{{ $p->ctc_empnamecomp }} ({{ $p->ctc_newjobtitle }})</div>
                                                        <div class="text-muted small"><i class="fas fa-project-diagram mr-1"></i> {{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value">PKR {{ number_format((float) ($p->ctc_newsalary ?? 0)) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge bg-warning-soft text-warning" style="background: rgba(243,156,18,0.1); border: 1px solid rgba(243,156,18,0.2);">
                                                            <i class="fas fa-pen-nib mr-1"></i> {{ $p->ctc_status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="btn btn-warning btn-sm rounded-lg px-3 font-weight-bold hub-header" style="font-size: 11px;">
                                                            <i class="fas fa-external-link-alt mr-1"></i> OPEN & RELEASE
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5" style="background: rgba(255,255,255,0.01);">
                                        <h6 class="text-muted font-weight-bold">Queue Empty</h6>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 2: OPEN --}}
                        <div class="tab-pane fade" id="initiated">
                            <div class="metric-card overflow-hidden">
                                @if(collect($initiatedCases)->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Candidate / Designation</th>
                                                    <th class="text-right">Salary</th>
                                                    <th class="text-center">Current Status</th>
                                                    <th class="text-right pr-4">Commands</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($initiatedCases as $p)
                                                @php $b = 'info'; $i = 'hourglass-half'; @endphp
                                                <tr data-id="{{ $p->ctc_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                                                    <td>
                                                        <div class="case-title">{{ $p->ctc_empnamecomp }} ({{ $p->ctc_newjobtitle }})</div>
                                                        <div class="text-muted small"><i class="fas fa-project-diagram mr-1"></i> {{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value">PKR {{ number_format((float) ($p->ctc_newsalary ?? 0)) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge" style="background: rgba(var(--rd-{{$b}}-rgb), 0.1); color: var(--rd-text-{{$b}}); border: 1px solid rgba(var(--rd-{{$b}}-rgb), 0.2);">
                                                            <i class="fas fa-{{$i}} mr-1"></i> {{ $p->ctc_status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="btn btn-outline-primary btn-sm rounded-lg px-3 hub-header" style="font-size: 11px; border: 1px solid rgba(0, 123, 255, 0.3);">
                                                            <i class="fas fa-search-plus mr-1"></i> VIEW TRAIL
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <h6 class="text-muted">No active cases moving through HQ.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 3: CLOSE --}}
                        <div class="tab-pane fade" id="completed">
                            <div class="metric-card overflow-hidden">
                                @if(collect($completedCases)->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Candidate / Designation</th>
                                                    <th class="text-right">Salary</th>
                                                    <th class="text-center">Outcome</th>
                                                    <th class="text-right pr-4">Archive</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($completedCases as $p)
                                                @php
                                                    $s = $p->ctc_status;
                                                    $b = ($s == 'Approved') ? 'success' : 'danger';
                                                    $i = ($s == 'Approved') ? 'check-double' : 'times-circle';
                                                @endphp
                                                <tr data-id="{{ $p->ctc_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                                                    <td>
                                                        <div class="case-title text-muted">{{ $p->ctc_empnamecomp }} ({{ $p->ctc_newjobtitle }})</div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value text-muted">PKR {{ number_format((float) ($p->ctc_newsalary ?? 0)) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge" style="background: rgba(var(--rd-{{$b}}-rgb), 0.1); color: var(--rd-text-{{$b}}); border: 1px solid rgba(var(--rd-{{$b}}-rgb), 0.2);">
                                                            <i class="fas fa-{{$i}} mr-1"></i> {{ strtoupper($s) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="btn btn-link text-muted rajdhani" style="font-size: 11px;">
                                                            VIEW LOG
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <h6 class="text-muted">No completed cases in archive.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
