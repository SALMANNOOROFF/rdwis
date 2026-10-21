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
                <div class="card-header bg-transparent border-0 p-0 mb-3 d-flex flex-wrap justify-content-between align-items-center">
                    <ul class="nav nav-pills mb-2 mb-md-0" id="hubTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn active" data-toggle="tab" href="#action-req">
                                <i class="fas fa-folder-open mr-2"></i> ACTION REQUIRED <span class="tab-count-badge">({{ collect($actionReqCases)->count() }})</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#initiated">
                                <i class="fas fa-stream mr-2"></i> UNDER REVIEW <span class="tab-count-badge">({{ collect($initiatedCases)->count() }})</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#completed">
                                <i class="fas fa-check-circle mr-2"></i> CLOSED / ARCHIVE <span class="tab-count-badge">({{ collect($completedCases)->count() }})</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Compact Inline Live Search Bar --}}
                    <div class="search-box-inline" style="min-width: 260px; max-width: 340px;">
                        <div class="input-group shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1.5px solid var(--rd-border, #cbd5e1); background: #ffffff;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0 text-muted pl-3 pr-2 py-1">
                                    <i class="fas fa-search text-primary" style="font-size: 13px;"></i>
                                </span>
                            </div>
                            <input type="text" id="globalHubSearch" class="form-control border-0 py-1 font-weight-500" style="font-size: 13px; box-shadow: none;" placeholder="Search ID, name, project..." autocomplete="off">
                            <div class="input-group-append" id="clearSearchGroup" style="display: none;">
                                <button class="btn btn-white border-0 text-muted px-2 py-1" type="button" id="clearGlobalSearch" title="Clear Search">
                                    <i class="fas fa-times-circle text-danger" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
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
                                                        <div class="case-title-text">
                                                            <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="rd-entity-title-link font-weight-bold" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                                                {{ $p->ctc_empnamecomp }}
                                                            </a>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <span class="badge badge-primary mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                                            {{ $p->ctc_newjobtitle }} &bull; 
                                                            @if($p->has_assigned_project)
                                                                <span class="badge badge-light border text-dark">{{ $p->project_code }}</span>
                                                            @else
                                                                <span class="badge badge-danger text-white font-weight-bold" style="font-size: 10px; border-radius: 4px;">
                                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Project will be allocated on next pay
                                                                </span>
                                                            @endif
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
                                                        @php
                                                            $loggedUser = auth()->user();
                                                            $isLeader = $loggedUser && ((method_exists($loggedUser, 'isMdDdgDg') && $loggedUser->isMdDdgDg()) || (method_exists($loggedUser, 'isDlog') && $loggedUser->isDlog()));
                                                            $canCancelCtc = !$isLeader && ($p->ctc_status === 'Under Revision' || $p->ctc_status === 'Draft');
                                                        @endphp
                                                        @if($p->ctc_status === 'Under Revision' || $p->ctc_status === 'Draft')
                                                            <a href="{{ route('division.contract-cases.edit', $p->ctc_id) }}" class="btn btn-outline-warning btn-sm font-weight-bold mr-1" style="border-radius: 6px; font-size: 11px;">
                                                                <i class="fas fa-edit mr-1"></i> REVISE
                                                            </a>
                                                            @if($canCancelCtc)
                                                            <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold mr-1 btn-cancel-row-case" data-id="{{ $p->ctc_id }}" style="border-radius: 6px; font-size: 11px;">
                                                                <i class="fas fa-ban mr-1"></i> CANCEL
                                                            </button>
                                                            @endif
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
                                                        <div class="case-title-text">
                                                            <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="rd-entity-title-link font-weight-bold" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                                                {{ $p->ctc_empnamecomp }}
                                                            </a>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <span class="badge badge-secondary mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                                            {{ $p->ctc_newjobtitle }} &bull; 
                                                            @if($p->has_assigned_project)
                                                                <span class="badge badge-light border text-dark">{{ $p->project_code }}</span>
                                                            @else
                                                                <span class="badge badge-danger text-white font-weight-bold" style="font-size: 10px; border-radius: 4px;">
                                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Project will be allocated on next pay
                                                                </span>
                                                            @endif
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
                                                        <div class="case-title-text">
                                                            <a href="{{ route('division.contract-cases.show', $p->ctc_id) }}" class="rd-entity-title-link font-weight-bold" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                                                {{ $p->ctc_empnamecomp }} ({{ $p->ctc_newjobtitle }})
                                                            </a>
                                                        </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
$(document).ready(function() {
    $('.btn-cancel-row-case').click(function(e) {
        e.preventDefault();
        const caseId = $(this).data('id');

        Swal.fire({
            title: `Cancel Contract Case #CC-${caseId}`,
            input: 'textarea',
            inputLabel: 'Reason for Cancellation (Required)',
            inputPlaceholder: 'Specify formal remarks for cancelling this case...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Case',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Cancellation remarks are mandatory!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('division/contract-cases') }}/${caseId}/cancel`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value
                    },
                    success: function(res) {
                        Swal.fire('Cancelled', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to cancel contract case.', 'error');
                    }
                });
            }
        });
    // ── Live Universal Search across all tabs and contract cases ───────
    const $search = $('#globalHubSearch');
    const $clearBtn = $('#clearGlobalSearch');
    const $clearGroup = $('#clearSearchGroup');

    const tabOrigCounts = {
        'action-req': {{ collect($actionReqCases)->count() }},
        'initiated': {{ collect($initiatedCases)->count() }},
        'completed': {{ collect($completedCases)->count() }}
    };

    function performSearch() {
        const query = $search.val().trim().toLowerCase();
        
        if (!query) {
            $clearGroup.hide();

            // Restore all rows in tabs
            $('#action-req tbody tr').show();
            $('#initiated tbody tr').show();
            $('#completed tbody tr').show();

            // Restore tab pill labels
            $('#hubTabs a[href="#action-req"] .tab-count-badge').text(`(${tabOrigCounts['action-req']})`);
            $('#hubTabs a[href="#initiated"] .tab-count-badge').text(`(${tabOrigCounts['initiated']})`);
            $('#hubTabs a[href="#completed"] .tab-count-badge').text(`(${tabOrigCounts['completed']})`);
            return;
        }

        $clearGroup.show();

        let tabMatches = { 'action-req': 0, 'initiated': 0, 'completed': 0 };
        const tabConfigs = [
            { id: 'action-req' },
            { id: 'initiated' },
            { id: 'completed' }
        ];

        tabConfigs.forEach(tab => {
            const $tabPane = $(`#${tab.id}`);
            const $rows = $tabPane.find('tbody tr');

            $rows.each(function() {
                const $row = $(this);
                const caseId = ($row.attr('data-id') || '').toLowerCase();
                const rowText = $row.text().toLowerCase();

                const isMatch = caseId === query || 
                                `cc-${caseId}` === query || 
                                caseId.includes(query) || 
                                rowText.includes(query);

                if (isMatch) {
                    $row.show();
                    tabMatches[tab.id]++;
                } else {
                    $row.hide();
                }
            });
        });

        // Update tab link badges
        $('#hubTabs a[href="#action-req"] .tab-count-badge').text(`(${tabMatches['action-req']})`);
        $('#hubTabs a[href="#initiated"] .tab-count-badge').text(`(${tabMatches['initiated']})`);
        $('#hubTabs a[href="#completed"] .tab-count-badge').text(`(${tabMatches['completed']})`);

        // If active tab has 0 matches, auto switch to the tab that has matches
        const activeTabHref = $('#hubTabs .nav-link.active').attr('href');
        const activeTabId = activeTabHref ? activeTabHref.replace('#', '') : '';
        if (tabMatches[activeTabId] === 0) {
            for (let t of ['action-req', 'initiated', 'completed']) {
                if (tabMatches[t] > 0) {
                    $(`#hubTabs a[href="#${t}"]`).tab('show');
                    break;
                }
            }
        }
    }

    $search.on('input', performSearch);
    $clearBtn.on('click', function() {
        $search.val('').focus();
        performSearch();
    });
});
</script>
@endpush
