@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

.hub-page {
    font-family: 'Outfit', 'Inter', sans-serif;
    background: var(--rd-bg) !important;
    min-height: 100vh;
    padding-bottom: 3rem;
}

.metric-action-card {
    background: #FFFFFF !important;
    border: 1.5px solid var(--rd-border) !important;
    border-radius: 12px;
    padding: 1.25rem 1rem;
    transition: all 0.25s ease;
    box-shadow: 0 2px 8px rgba(41, 40, 36, 0.04);
}
.metric-action-card:hover {
    transform: translateY(-4px);
    border-color: var(--rd-primary-600) !important;
    box-shadow: 0 8px 24px rgba(41, 40, 36, 0.08);
}

.hub-tab-btn {
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.3px;
    border-radius: 30px !important;
    padding: 9px 22px !important;
    transition: all 0.2s ease;
    margin-right: 8px;
    border: 1.5px solid var(--rd-border) !important;
    background: #FFFFFF !important;
    color: var(--rd-text2) !important;
}
.hub-tab-btn.active {
    background: var(--rd-primary-600) !important;
    color: #FFFFFF !important;
    border-color: var(--rd-primary-600) !important;
    box-shadow: 0 4px 14px rgba(95, 120, 88, 0.25);
}

.clean-hub-table thead th {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--rd-text3);
    background: var(--rd-neutral-50) !important;
    border: none !important;
    padding: 14px 16px;
}
.clean-hub-table td {
    padding: 14px 16px;
    color: var(--rd-text1);
    vertical-align: middle;
    border-top: 1px solid var(--rd-neutral-200);
}
.clean-hub-table tr:hover {
    background: var(--rd-neutral-50);
}

.case-title-text {
    font-weight: 700;
    color: var(--rd-text1);
    font-size: 0.95rem;
}
.case-salary-text {
    font-size: 1rem;
    font-weight: 700;
    color: var(--rd-primary-600);
}

.status-badge-chip {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
</style>

<div class="content-wrapper hub-page">
    <div class="content-header py-4 bg-transparent mb-2">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <span class="badge badge-primary px-3 py-1 mb-2 font-weight-bold" style="border-radius: 20px; font-size: 10px; letter-spacing: 0.8px;">
                        DIVISION OPERATIONS
                    </span>
                    <h1 class="m-0 font-weight-bold text-dark" style="font-size: 2.2rem; letter-spacing: -0.5px;">
                        Contract Case Hub
                    </h1>
                    <p class="text-muted mb-0 font-weight-500" style="font-size: 0.95rem;">
                        Central portal for initiating, customizing, and tracking departmental contract cases.
                    </p>
                </div>
            </div>
            
            <!-- Quick Initiation Cards -->
            <div class="row mt-4">
                <div class="col-md-3 mb-2">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Hg']) }}" class="text-decoration-none">
                        <div class="metric-action-card text-center" style="border-top: 4px solid #3B82F6 !important;">
                            <div class="d-inline-flex p-3 rounded-circle mb-2" style="background: #EFF6FF;">
                                <i class="fas fa-user-plus fa-lg text-primary"></i>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-0">New Hiring</h6>
                            <small class="text-muted font-weight-500">Fresh Candidate (Hg)</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Ce']) }}" class="text-decoration-none">
                        <div class="metric-action-card text-center" style="border-top: 4px solid #10B981 !important;">
                            <div class="d-inline-flex p-3 rounded-circle mb-2" style="background: #ECFDF5;">
                                <i class="fas fa-user-clock fa-lg text-success"></i>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-0">Extension</h6>
                            <small class="text-muted font-weight-500">Same Terms (Ce)</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Cr']) }}" class="text-decoration-none">
                        <div class="metric-action-card text-center" style="border-top: 4px solid #F59E0B !important;">
                            <div class="d-inline-flex p-3 rounded-circle mb-2" style="background: #FFFBEB;">
                                <i class="fas fa-sync-alt fa-lg text-warning"></i>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-0">Renewal</h6>
                            <small class="text-muted font-weight-500">Updated Terms (Cr)</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="{{ route('division.contract-cases.create', ['type' => 'Rh']) }}" class="text-decoration-none">
                        <div class="metric-action-card text-center" style="border-top: 4px solid #06B6D4 !important;">
                            <div class="d-inline-flex p-3 rounded-circle mb-2" style="background: #ECFEFF;">
                                <i class="fas fa-undo fa-lg text-info"></i>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-0">Rehiring</h6>
                            <small class="text-muted font-weight-500">Past Employee (Rh)</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid px-4">
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
                                <i class="fas fa-stream mr-2"></i> UNDER REVIEW ({{ collect($initiatedCases)->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#completed">
                                <i class="fas fa-check-circle mr-2"></i> CLOSED / ARCHIVE ({{ collect($completedCases)->count() }})
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        
                        {{-- TAB 1: ACTION REQUIRED --}}
                        <div class="tab-pane fade show active" id="action-req">
                            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                                @if(collect($actionReqCases)->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table clean-hub-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Candidate / Designation</th>
                                                    <th class="text-right">Salary</th>
                                                    <th class="text-center">Current Status</th>
                                                    <th class="text-right pr-4">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($actionReqCases as $p)
                                                <tr data-id="{{ $p->ctc_id }}">
                                                    <td class="pl-4">
                                                        <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                                            CC-{{ $p->ctc_id }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="case-title-text">{{ $p->ctc_empnamecomp }}</div>
                                                        <div class="text-muted small">
                                                            <span class="badge badge-primary mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                                            {{ $p->ctc_newjobtitle }} &bull; {{ $p->casePlans->first()->project->prj_code ?? 'Core' }}
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="case-salary-text">PKR {{ number_format((float) ($p->ctc_newsalary ?? 0)) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-column align-items-center gap-1">
                                                            <span class="badge badge-info text-white font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px; background: #0284c7;">
                                                                <i class="fas fa-user-clock mr-1"></i> Holder: {{ $p->current_stage ?? $p->currentSubstatus->css_stage ?? 'Division' }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        @if($p->ctc_status === 'Under Revision' || $p->ctc_status === 'Draft')
                                                            <a href="{{ route('division.contract-cases.edit', $p->ctc_id) }}" class="btn btn-outline-warning btn-sm font-weight-bold mr-1" style="border-radius: 6px; font-size: 11px;">
                                                                <i class="fas fa-edit mr-1"></i> REVISE
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="btn btn-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                                            <i class="fas fa-external-link-alt mr-1"></i> OPEN & RELEASE
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle fa-2x text-muted mb-2 opacity-50"></i>
                                        <h6 class="text-muted font-weight-bold">No draft or revision cases requiring action.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 2: OPEN / UNDER REVIEW --}}
                        <div class="tab-pane fade" id="initiated">
                            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                                @if(collect($initiatedCases)->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table clean-hub-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Candidate / Designation</th>
                                                    <th class="text-right">Salary</th>
                                                    <th class="text-center">Current Status & Holder</th>
                                                    <th class="text-right pr-4">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($initiatedCases as $p)
                                                <tr data-id="{{ $p->ctc_id }}">
                                                    <td class="pl-4">
                                                        <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                                            CC-{{ $p->ctc_id }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="case-title-text">{{ $p->ctc_empnamecomp }}</div>
                                                        <div class="text-muted small">
                                                            <span class="badge badge-secondary mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                                            {{ $p->ctc_newjobtitle }} &bull; {{ $p->casePlans->first()->project->prj_code ?? 'Core' }}
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="case-salary-text">PKR {{ number_format((float) ($p->ctc_newsalary ?? 0)) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-column align-items-center gap-1">
                                                            <span class="badge badge-info text-white font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px; background: #0284c7;">
                                                                <i class="fas fa-user-clock mr-1"></i> Holder: {{ $p->current_stage ?? $p->currentSubstatus->css_stage ?? 'In Review' }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="btn btn-outline-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                                            <i class="fas fa-search mr-1"></i> VIEW TRAIL
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <h6 class="text-muted">No active cases currently undergoing review.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 3: CLOSE / ARCHIVE --}}
                        <div class="tab-pane fade" id="completed">
                            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                                @if(collect($completedCases)->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table clean-hub-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Candidate / Designation</th>
                                                    <th class="text-right">Salary</th>
                                                    <th class="text-center">Final Outcome & Holder</th>
                                                    <th class="text-right pr-4">Archive</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($completedCases as $p)
                                                @php
                                                    $s = $p->ctc_status;
                                                    $isApproved = in_array($s, ['Approved', 'Fulfilled']);
                                                @endphp
                                                <tr data-id="{{ $p->ctc_id }}">
                                                    <td class="pl-4">
                                                        <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                                            CC-{{ $p->ctc_id }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="case-title-text text-muted">{{ $p->ctc_empnamecomp }} ({{ $p->ctc_newjobtitle }})</div>
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="text-muted font-weight-bold">PKR {{ number_format((float) ($p->ctc_newsalary ?? 0)) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-column align-items-center gap-1">
                                                            <span class="badge {{ $isApproved ? 'badge-success' : 'badge-danger' }} text-white font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                                                                <i class="fas {{ $isApproved ? 'fa-check-double' : 'fa-times-circle' }} mr-1"></i> Stage: {{ $p->current_stage ?? $p->currentSubstatus->css_stage ?? $p->ctc_status }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="btn btn-link text-muted font-weight-bold" style="font-size: 11px;">
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
