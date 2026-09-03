@extends('welcome')

@section('content')
<div class="content-wrapper pt-3" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid px-lg-4">

        {{-- MINIMALIST TOP HEADER BAR --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <h4 class="m-0 font-weight-bold text-dark" style="font-family:'Rajdhani',sans-serif; letter-spacing:0.3px; font-size: 22px;">
                    <i class="fas fa-headset text-primary mr-2"></i> HELPDESK & COMPLAINTS
                </h4>
                <p class="text-muted small mb-0">
                    System feedback, issue ticketing, and resolution tracker with SO IT.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                <span class="badge badge-light border text-dark font-weight-bold px-2.5 py-1.5 shadow-2xs" style="font-size: 11px; border-radius: 6px;">
                    <i class="fas fa-user-circle text-primary mr-1"></i> {{ $user->acc_username }} 
                    <span class="text-muted font-weight-normal">({{ $userRoleLabel }})</span>
                </span>
                <span class="badge badge-light border text-muted px-2 py-1.5 shadow-2xs" style="font-size: 11px; border-radius: 6px;">
                    <i class="far fa-building mr-1 text-secondary"></i> {{ $userUnitName }}
                </span>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-xs border-0 mb-3 py-2 px-3" role="alert" style="border-radius: 8px; background: #dcfce7; color: #15803d; font-size: 13px;">
                <i class="fas fa-check-circle mr-1.5"></i> <strong>Success:</strong> {{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-xs border-0 mb-3 py-2 px-3" role="alert" style="border-radius: 8px; background: #fee2e2; color: #b91c1c; font-size: 13px;">
                <i class="fas fa-exclamation-triangle mr-1.5"></i> <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- MAIN TABBED NAVIGATION BAR --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff;">
            <div class="card-body p-2">
                <ul class="nav nav-pills" id="helpdeskMainTabs" role="tablist" style="gap: 6px;">
                    {{-- TAB 1: RAISE TICKET --}}
                    <li class="nav-item">
                        <a class="nav-link {{ (!$isResolver && collect($activeTickets)->count() == 0) ? 'active' : ($isResolver ? '' : 'active') }} font-weight-bold px-3 py-1.5" id="pills-raise-tab" data-toggle="pill" href="#pills-raise" role="tab" style="border-radius: 6px; font-size: 12.5px;">
                            <i class="fas fa-plus-circle mr-1 text-primary"></i> 
                            @if($isApex)
                                Issue Directive / Ticket
                            @else
                                Raise New Ticket
                            @endif
                        </a>
                    </li>

                    {{-- TAB 2: ACTIVE TICKETS --}}
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold px-3 py-1.5" id="pills-active-tab" data-toggle="pill" href="#pills-active" role="tab" style="border-radius: 6px; font-size: 12.5px;">
                            <i class="fas fa-clock mr-1 text-warning"></i> My Active Tickets 
                            <span class="badge {{ collect($activeTickets)->count() > 0 ? 'badge-warning text-dark' : 'badge-light border' }} ml-1 font-weight-bold" style="font-size: 10px;">
                                {{ collect($activeTickets)->count() }}
                            </span>
                        </a>
                    </li>

                    {{-- TAB 3: RESOLVED TICKETS --}}
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold px-3 py-1.5" id="pills-resolved-tab" data-toggle="pill" href="#pills-resolved" role="tab" style="border-radius: 6px; font-size: 12.5px;">
                            <i class="fas fa-check-circle mr-1 text-success"></i> Solved & History 
                            <span class="badge {{ collect($resolvedTickets)->count() > 0 ? 'badge-success text-white' : 'badge-light border' }} ml-1 font-weight-bold" style="font-size: 10px;">
                                {{ collect($resolvedTickets)->count() }}
                            </span>
                        </a>
                    </li>

                    {{-- TAB 4: RESOLVER DESK (SO IT & GOMOE ONLY) --}}
                    @if($isResolver)
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold px-3 py-1.5" id="pills-resolver-tab" data-toggle="pill" href="#pills-resolver" role="tab" style="border-radius: 6px; font-size: 12.5px; background: #0284c7; color: #ffffff;">
                                <i class="fas fa-shield-alt mr-1"></i> SO IT Helpdesk Hub 
                                <span class="badge badge-light text-dark ml-1 font-weight-bold" style="font-size: 10px;">
                                    {{ $stats['open'] + $stats['in_progress'] + $stats['returned'] }}
                                </span>
                            </a>
                        </li>
                    @endif

                    {{-- TAB 5: APEX DIRECTORATE OVERSIGHT (MD, DDG, DG ONLY) --}}
                    @if($isApex)
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold px-3 py-1.5" id="pills-apex-tab" data-toggle="pill" href="#pills-apex" role="tab" style="border-radius: 6px; font-size: 12.5px;">
                                <i class="fas fa-chart-pie mr-1 text-info"></i> Department Overview
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- TAB CONTENT SECTIONS --}}
        <div class="tab-content" id="helpdeskMainTabsContent">

            {{-- ========================================================================= --}}
            {{-- TAB 1: RAISE NEW TICKET (MINIMALIST FORM)                                 --}}
            {{-- ========================================================================= --}}
            <div class="tab-pane fade {{ ($isResolver) ? '' : 'show active' }}" id="pills-raise" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff; max-width: 880px; margin: 0 auto;">
                    <div class="card-header bg-white py-2.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 14px;">
                            <i class="fas fa-edit text-primary mr-1.5"></i>
                            @if($isApex)
                                Issue Apex Directive / Feedback
                            @else
                                Submit Complaint / Suggestion
                            @endif
                        </h6>
                        <small class="text-muted">Tickets are routed directly to SO IT Desk</small>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('support.tickets.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Category Pills --}}
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex flex-wrap gap-2">
                                    <label class="btn btn-outline-danger btn-sm font-weight-bold px-3 py-1.5 m-0 active" style="border-radius: 6px; font-size: 12px; cursor: pointer;">
                                        <input type="radio" name="tkt_type" value="Complaint" checked class="mr-1"> Complaint / Bug
                                    </label>
                                    <label class="btn btn-outline-success btn-sm font-weight-bold px-3 py-1.5 m-0" style="border-radius: 6px; font-size: 12px; cursor: pointer;">
                                        <input type="radio" name="tkt_type" value="Suggestion" class="mr-1"> Suggestion / Improvement
                                    </label>
                                    <label class="btn btn-outline-warning btn-sm font-weight-bold px-3 py-1.5 m-0" style="border-radius: 6px; font-size: 12px; cursor: pointer;">
                                        <input type="radio" name="tkt_type" value="Bug Report" class="mr-1"> Error / Glitch
                                    </label>
                                    <label class="btn btn-outline-info btn-sm font-weight-bold px-3 py-1.5 m-0" style="border-radius: 6px; font-size: 12px; cursor: pointer;">
                                        <input type="radio" name="tkt_type" value="Feature Request" class="mr-1"> New Feature
                                    </label>
                                </div>
                            </div>

                            {{-- Module & Priority --}}
                            <div class="form-row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        Module <span class="text-danger">*</span>
                                    </label>
                                    <select name="tkt_module" class="form-control form-control-sm font-weight-bold" required style="border-radius: 6px; font-size: 12.5px; height: 38px;">
                                        <option value="" disabled selected>— Select Affected System Module —</option>
                                        <option value="Contract Cases">📜 Contract Cases</option>
                                        <option value="Purchase Cases">🛒 Purchase Cases</option>
                                        <option value="HR & Employees">👥 HR & Employees</option>
                                        <option value="Finance & Budget">💰 Finance & Budget</option>
                                        <option value="Projects / MPR">📁 Projects & MPR</option>
                                        <option value="Inventory & Assets">📦 Inventory & Assets</option>
                                        <option value="Suppliers & Firms">🏢 Suppliers & Firms</option>
                                        <option value="Accounts & Login">🔐 User Accounts & Access</option>
                                        <option value="General / Other">🌐 General / Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        Priority
                                    </label>
                                    @if($isApex)
                                        <input type="text" class="form-control form-control-sm font-weight-bold text-danger bg-light" value="URGENT (Apex Directive)" readonly style="border-radius: 6px; height: 38px;">
                                        <input type="hidden" name="tkt_priority" value="Urgent">
                                    @else
                                        <select name="tkt_priority" class="form-control form-control-sm font-weight-bold" style="border-radius: 6px; font-size: 12.5px; height: 38px;">
                                            <option value="Normal" selected>Normal</option>
                                            <option value="High">High</option>
                                            <option value="Urgent">Urgent / Blocker</option>
                                        </select>
                                    @endif
                                </div>
                            </div>

                            {{-- Subject Title --}}
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    Subject Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="tkt_subject" class="form-control form-control-sm font-weight-bold" placeholder="Short summary of the issue or feedback..." required style="border-radius: 6px; font-size: 13px; height: 38px;">
                            </div>

                            {{-- Details Textarea --}}
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    Detailed Message / Description <span class="text-danger">*</span>
                                </label>
                                <textarea name="tkt_description" rows="4" class="form-control" placeholder="Describe the issue or suggestion clearly..." required style="border-radius: 6px; font-size: 13px;"></textarea>
                            </div>

                            {{-- Attachment & Submit --}}
                            <div class="form-row align-items-center pt-2 border-top">
                                <div class="col-md-7 mb-2 mb-md-0">
                                    <label class="text-muted small mb-1" style="font-size: 11px;">
                                        <i class="fas fa-paperclip mr-1"></i> Optional Attachment (Screenshot / PDF / Doc)
                                    </label>
                                    <input type="file" name="attachment" class="form-control-file" style="font-size: 11.5px;">
                                </div>
                                <div class="col-md-5 text-md-right">
                                    <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-xs" style="border-radius: 6px; font-size: 13px; background: #0284c7; border: none;">
                                        <i class="fas fa-paper-plane mr-1.5"></i> Raise Ticket
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- TAB 2: MY ACTIVE TICKETS                                                  --}}
            {{-- ========================================================================= --}}
            <div class="tab-pane fade" id="pills-active" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-spinner fa-spin text-warning mr-1.5"></i> Current Active & Pending Tickets
                        </h6>
                        <span class="badge badge-light border text-muted" style="font-size: 11px;">
                            {{ collect($activeTickets)->count() }} Active
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if(collect($activeTickets)->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-nowrap" style="font-size: 12.5px;">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th class="pl-3">Ref #</th>
                                            <th>Module</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-right pr-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeTickets as $t)
                                            <tr>
                                                <td class="pl-3 align-middle font-weight-bold text-dark">
                                                    {{ $t->tkt_ref }}
                                                    @if($t->tkt_is_apex)
                                                        <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 9px;">APEX</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-light border font-weight-bold text-dark">{{ $t->tkt_module }}</span>
                                                </td>
                                                <td class="align-middle text-truncate" style="max-width: 320px;" title="{{ $t->tkt_subject }}">
                                                    <span class="font-weight-bold text-dark">{{ $t->tkt_subject }}</span>
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    {{ $t->tkt_created_at->format('d-M-Y H:i') }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    @php
                                                        $stClass = match($t->tkt_status) {
                                                            'Open' => 'badge-warning text-dark',
                                                            'In Progress' => 'badge-primary text-white',
                                                            'Returned' => 'badge-danger text-white',
                                                            default => 'badge-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $stClass }} font-weight-bold px-2 py-0.5" style="font-size: 11px;">
                                                        {{ $t->tkt_status }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-right pr-3">
                                                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold px-2.5 py-1" style="border-radius: 4px; font-size: 11px;" onclick="openTicketModal({{ $t->tkt_id }})">
                                                        <i class="fas fa-eye mr-1"></i> View & Reply
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 32px; opacity: 0.5;"></i>
                                <div class="font-weight-bold">No active tickets pending right now.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- TAB 3: RESOLVED & PREVIOUS HISTORY                                        --}}
            {{-- ========================================================================= --}}
            <div class="tab-pane fade" id="pills-resolved" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-history text-success mr-1.5"></i> Solved Tickets & Resolution History
                        </h6>
                        <span class="badge badge-light border text-muted" style="font-size: 11px;">
                            {{ collect($resolvedTickets)->count() }} Closed
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if(collect($resolvedTickets)->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-nowrap" style="font-size: 12.5px;">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th class="pl-3">Ref #</th>
                                            <th>Module & Subject</th>
                                            <th>Resolution Note</th>
                                            <th>Solved By & Date</th>
                                            <th class="text-center">Outcome</th>
                                            <th class="text-right pr-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resolvedTickets as $t)
                                            <tr>
                                                <td class="pl-3 align-middle font-weight-bold text-muted">
                                                    {{ $t->tkt_ref }}
                                                </td>
                                                <td class="align-middle text-truncate" style="max-width: 240px;">
                                                    <span class="badge badge-light border text-dark font-weight-bold mr-1">{{ $t->tkt_module }}</span>
                                                    <strong class="text-dark">{{ $t->tkt_subject }}</strong>
                                                </td>
                                                <td class="align-middle text-truncate" style="max-width: 320px;" title="{{ $t->tkt_resolution_note }}">
                                                    <span class="text-dark small"><i class="fas fa-comment-dots text-success mr-1"></i> {{ Str::limit($t->tkt_resolution_note ?? 'Resolved.', 60) }}</span>
                                                </td>
                                                <td class="align-middle small">
                                                    <span class="text-success font-weight-bold">{{ $t->tkt_solved_by_name ?? 'SO IT' }}</span>
                                                    <span class="text-muted d-block" style="font-size: 10.5px;">{{ $t->tkt_solved_at ? $t->tkt_solved_at->format('d-M-Y') : '' }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="badge badge-success font-weight-bold px-2 py-0.5" style="font-size: 11px;">
                                                        {{ $t->tkt_status }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-right pr-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold px-2.5 py-1" style="border-radius: 4px; font-size: 11px;" onclick="openTicketModal({{ $t->tkt_id }})">
                                                        <i class="fas fa-eye mr-1"></i> View Log
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-archive text-muted mb-2" style="font-size: 32px; opacity: 0.4;"></i>
                                <div class="font-weight-bold">No resolved ticket history yet.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- TAB 4: SO IT & GOMOE RESOLVER DESK (ALL DEPARTMENTS)                      --}}
            {{-- ========================================================================= --}}
            @if($isResolver)
                <div class="tab-pane fade show active" id="pills-resolver" role="tabpanel">
                    {{-- Minimalist Summary Metrics --}}
                    <div class="row mb-3">
                        <div class="col-md-2 col-4 mb-2">
                            <div class="card border-0 shadow-2xs text-center p-2" style="border-radius: 8px; background: #ffffff; border-left: 3px solid #0284c7 !important;">
                                <span class="text-muted text-uppercase" style="font-size: 9.5px; font-weight: 700;">Total</span>
                                <h5 class="font-weight-bold text-dark mb-0 mt-0.5">{{ $stats['total'] }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-2">
                            <div class="card border-0 shadow-2xs text-center p-2" style="border-radius: 8px; background: #ffffff; border-left: 3px solid #f59e0b !important;">
                                <span class="text-warning text-uppercase" style="font-size: 9.5px; font-weight: 700;">Open</span>
                                <h5 class="font-weight-bold text-warning mb-0 mt-0.5">{{ $stats['open'] }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-2">
                            <div class="card border-0 shadow-2xs text-center p-2" style="border-radius: 8px; background: #ffffff; border-left: 3px solid #3b82f6 !important;">
                                <span class="text-primary text-uppercase" style="font-size: 9.5px; font-weight: 700;">In Progress</span>
                                <h5 class="font-weight-bold text-primary mb-0 mt-0.5">{{ $stats['in_progress'] }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-2">
                            <div class="card border-0 shadow-2xs text-center p-2" style="border-radius: 8px; background: #ffffff; border-left: 3px solid #ea580c !important;">
                                <span class="text-danger text-uppercase" style="font-size: 9.5px; font-weight: 700;">Returned</span>
                                <h5 class="font-weight-bold text-danger mb-0 mt-0.5">{{ $stats['returned'] }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-2">
                            <div class="card border-0 shadow-2xs text-center p-2" style="border-radius: 8px; background: #ffffff; border-left: 3px solid #10b981 !important;">
                                <span class="text-success text-uppercase" style="font-size: 9.5px; font-weight: 700;">Resolved</span>
                                <h5 class="font-weight-bold text-success mb-0 mt-0.5">{{ $stats['resolved'] }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-2">
                            <div class="card border-0 shadow-2xs text-center p-2" style="border-radius: 8px; background: #fffbeb; border-left: 3px solid #d97706 !important;">
                                <span class="text-dark text-uppercase" style="font-size: 9.5px; font-weight: 700;">Apex Directives</span>
                                <h5 class="font-weight-bold text-dark mb-0 mt-0.5">{{ $stats['apex'] }}</h5>
                            </div>
                        </div>
                    </div>

                    {{-- Pinned Apex Directives Alert --}}
                    @if(isset($apexDirectives) && $apexDirectives->count() > 0)
                        <div class="alert alert-warning border shadow-2xs py-2 px-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 8px; background: #fef3c7; border-color: #fbbf24 !important;">
                            <span class="font-weight-bold text-dark small">
                                <i class="fas fa-crown text-warning mr-1.5"></i> {{ $apexDirectives->count() }} HIGH PRIORITY APEX DIRECTIVES PENDING ACTION
                            </span>
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-0.5" style="font-size: 10px;">URGENT</span>
                        </div>
                    @endif

                    {{-- All Tickets Table with Filter --}}
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                            <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13.5px;">
                                <i class="fas fa-list-alt text-primary mr-1.5"></i> Central Support Queue (All Departments)
                            </h6>
                            <form method="GET" action="{{ route('support.tickets.index') }}" class="form-inline d-flex flex-wrap gap-1 m-0">
                                <select name="unt_id" class="form-control form-control-sm" onchange="this.form.submit()" style="border-radius: 4px; font-size: 11.5px; height: 30px;">
                                    <option value="all">All Departments</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->unt_id }}" {{ request('unt_id') == $d->unt_id ? 'selected' : '' }}>{{ $d->unt_name }}</option>
                                    @endforeach
                                </select>
                                <select name="module" class="form-control form-control-sm" onchange="this.form.submit()" style="border-radius: 4px; font-size: 11.5px; height: 30px;">
                                    <option value="all">All Modules</option>
                                    <option value="Contract Cases" {{ request('module') == 'Contract Cases' ? 'selected' : '' }}>Contract Cases</option>
                                    <option value="Purchase Cases" {{ request('module') == 'Purchase Cases' ? 'selected' : '' }}>Purchase Cases</option>
                                    <option value="HR & Employees" {{ request('module') == 'HR & Employees' ? 'selected' : '' }}>HR & Employees</option>
                                    <option value="Finance & Budget" {{ request('module') == 'Finance & Budget' ? 'selected' : '' }}>Finance & Budget</option>
                                </select>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-nowrap" style="font-size: 12.5px;">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th class="pl-3">Ref #</th>
                                            <th>Initiator / Dept</th>
                                            <th>Module</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-right pr-3">Decision</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeTickets as $t)
                                            <tr style="{{ $t->tkt_is_apex ? 'background: #fffbeb;' : '' }}">
                                                <td class="pl-3 align-middle font-weight-bold">
                                                    {{ $t->tkt_ref }}
                                                    @if($t->tkt_is_apex)
                                                        <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 9px;">APEX</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <div class="font-weight-bold text-dark">{{ $t->tkt_user_name }}</div>
                                                    <small class="text-muted">{{ $t->tkt_unt_name ?? $t->tkt_user_role }}</small>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-light border text-dark font-weight-bold">{{ $t->tkt_module }}</span>
                                                </td>
                                                <td class="align-middle text-truncate" style="max-width: 280px;" title="{{ $t->tkt_subject }}">
                                                    <span class="font-weight-bold text-dark">{{ $t->tkt_subject }}</span>
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    {{ $t->tkt_created_at->format('d-M-Y H:i') }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    @php
                                                        $stClass = match($t->tkt_status) {
                                                            'Open' => 'badge-warning text-dark',
                                                            'In Progress' => 'badge-primary text-white',
                                                            'Returned' => 'badge-danger text-white',
                                                            default => 'badge-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $stClass }} font-weight-bold px-2 py-0.5" style="font-size: 11px;">
                                                        {{ $t->tkt_status }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-right pr-3">
                                                    <button type="button" class="btn btn-dark btn-sm font-weight-bold px-2.5 py-1" style="border-radius: 4px; font-size: 11px;" onclick="openTicketModal({{ $t->tkt_id }})">
                                                        <i class="fas fa-tasks mr-1"></i> Resolve Desk
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================================================= --}}
            {{-- TAB 5: APEX DIRECTORATE OVERSIGHT (MD, DDG, DG)                           --}}
            {{-- ========================================================================= --}}
            @if($isApex)
                <div class="tab-pane fade" id="pills-apex" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13.5px;">
                                <i class="fas fa-chart-pie text-primary mr-1.5"></i> Departmental Complaints & Resolution Overview
                            </h6>
                            <span class="badge badge-light border text-muted" style="font-size: 11px;">Apex Directorate Oversight</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-nowrap" style="font-size: 12.5px;">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th class="pl-3">Department / Directorate</th>
                                            <th class="text-center">Total Tickets</th>
                                            <th class="text-center">Active / Pending</th>
                                            <th class="text-center">Resolved</th>
                                            <th class="text-right pr-3">Resolution Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($deptStats ?? [] as $ds)
                                            @php
                                                $rate = ($ds->total_count > 0) ? round(($ds->resolved_count / $ds->total_count) * 100) : 0;
                                            @endphp
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-dark">
                                                    <i class="fas fa-building text-info mr-1.5"></i> {{ $ds->department_name }}
                                                </td>
                                                <td class="text-center font-weight-bold text-dark">{{ $ds->total_count }}</td>
                                                <td class="text-center">
                                                    @if($ds->active_count > 0)
                                                        <span class="badge badge-warning font-weight-bold px-2 py-0.5">{{ $ds->active_count }} Active</span>
                                                    @else
                                                        <span class="text-muted small">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success font-weight-bold px-2 py-0.5">{{ $ds->resolved_count }} Solved</span>
                                                </td>
                                                <td class="text-right pr-3">
                                                    <strong class="{{ $rate >= 75 ? 'text-success' : ($rate >= 50 ? 'text-warning' : 'text-danger') }}">{{ $rate }}%</strong>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No departmental complaints logged yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="ticketDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h6 class="modal-title font-weight-bold" id="modalTicketTitle" style="font-size: 13.5px;">
                    <i class="fas fa-ticket-alt text-info mr-1.5"></i> TICKET DETAILS
                </h6>
                <button type="button" class="close text-white py-2" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" style="background: #f8fafc; max-height: 75vh; overflow-y: auto;">
                
                {{-- Ticket Overview Card --}}
                <div class="card border shadow-2xs mb-3" style="border-radius: 8px; background: #ffffff;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <div>
                                <span class="badge badge-primary px-2 py-0.5 font-weight-bold" id="modalTicketRef" style="font-size: 11px; background: #0284c7;">-</span>
                                <span class="badge badge-light border text-dark font-weight-bold ml-1" id="modalTicketType">-</span>
                                <span class="badge badge-light border text-dark ml-1" id="modalTicketModule">-</span>
                            </div>
                            <div>
                                <span class="badge badge-warning px-2 py-0.5 font-weight-bold" id="modalTicketStatus">-</span>
                            </div>
                        </div>
                        <h6 class="font-weight-bold text-dark mb-1" id="modalTicketSubject" style="font-size: 14px;">-</h6>
                        <p class="text-muted small mb-2" id="modalTicketMeta" style="font-size: 11.5px;">-</p>
                        <div class="p-2.5 bg-light rounded border text-dark mb-2" id="modalTicketDescription" style="white-space: pre-wrap; font-size: 12.5px;">-</div>
                        <div id="modalAttachmentBox" class="d-none">
                            <a href="#" target="_blank" id="modalAttachmentLink" class="btn btn-xs btn-outline-primary font-weight-bold" style="border-radius: 4px; font-size: 11px;">
                                <i class="fas fa-paperclip mr-1"></i> View / Download Attachment
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Activity & Audit Timeline --}}
                <div class="card border shadow-2xs mb-3" style="border-radius: 8px; background: #ffffff;">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 12.5px;">
                            <i class="fas fa-history text-primary mr-1"></i> Responses & Audit Trail
                        </h6>
                    </div>
                    <div class="card-body p-3" id="modalActivityTrail">
                        <div class="text-center text-muted py-2 small">Loading activity trail...</div>
                    </div>
                </div>

                {{-- User Clarification Form (If Open or Returned) --}}
                <div id="userReplySection" class="card border shadow-2xs mb-3 d-none" style="border-radius: 8px; background: #ffffff;">
                    <div class="card-header bg-light py-2 px-3 border-bottom">
                        <span class="font-weight-bold text-dark" style="font-size: 12px;">
                            <i class="fas fa-reply text-primary mr-1"></i> Post Clarification / Reply
                        </span>
                    </div>
                    <div class="card-body p-2.5">
                        <form action="" method="POST" enctype="multipart/form-data" id="modalReplyForm">
                            @csrf
                            <div class="form-group mb-2">
                                <textarea name="message" rows="2" class="form-control form-control-sm" placeholder="Type your reply for SO IT..." required style="border-radius: 6px; font-size: 12px;"></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="file" name="attachment" style="font-size: 11px;">
                                <button type="submit" class="btn btn-xs btn-primary font-weight-bold px-3 py-1" style="border-radius: 4px; font-size: 11.5px; background: #0284c7;">
                                    <i class="fas fa-paper-plane mr-1"></i> Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- SO IT / Resolver Action Desk Form --}}
                @if($isResolver)
                    <div id="resolverActionSection" class="card border shadow-2xs" style="border-radius: 8px; background: #ffffff; border-color: #0284c7 !important;">
                        <div class="card-header bg-primary text-white py-2 px-3">
                            <span class="font-weight-bold" style="font-size: 12px;">
                                <i class="fas fa-shield-alt mr-1"></i> SO IT / Resolver Decision Desk
                            </span>
                        </div>
                        <div class="card-body p-2.5">
                            <form action="" method="POST" enctype="multipart/form-data" id="modalResolverForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="small text-muted font-weight-bold text-uppercase" style="font-size: 10.5px;">Update Status</label>
                                        <select name="status" class="form-control form-control-sm font-weight-bold" required style="border-radius: 4px; font-size: 11.5px; height: 32px;">
                                            <option value="In Progress">⏳ Mark In Progress</option>
                                            <option value="Returned">⚠️ Return to User (Needs Clarification)</option>
                                            <option value="Resolved" selected>✅ Resolve Ticket (Completed)</option>
                                            <option value="Rejected">❌ Reject / Close</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small text-muted font-weight-bold text-uppercase" style="font-size: 10.5px;">Attach Solution File (Optional)</label>
                                        <input type="file" name="attachment" class="form-control-file" style="font-size: 11px;">
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small text-muted font-weight-bold text-uppercase" style="font-size: 10.5px;">Resolution Remarks / Decision Note</label>
                                    <textarea name="resolution_note" rows="2" class="form-control form-control-sm text-dark" placeholder="State how this issue was resolved..." required style="border-radius: 6px; font-size: 12px;"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block font-weight-bold py-1.5 shadow-2xs" style="border-radius: 6px; font-size: 12px;">
                                    <i class="fas fa-check-circle mr-1"></i> Submit Resolution
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script>
function openTicketModal(ticketId) {
    $('#modalActivityTrail').html('<div class="text-center text-muted py-2 small"><i class="fas fa-spinner fa-spin mr-1"></i> Loading details...</div>');
    $('#ticketDetailModal').modal('show');

    $.ajax({
        url: '/support/tickets/' + ticketId,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            let t = res.ticket;
            $('#modalTicketRef').text(t.tkt_ref);
            $('#modalTicketType').text(t.tkt_type);
            $('#modalTicketModule').text(t.tkt_module);
            $('#modalTicketStatus').text(t.tkt_status);
            $('#modalTicketSubject').text(t.tkt_subject);
            $('#modalTicketDescription').text(t.tkt_description);
            $('#modalTicketMeta').html('Filed by <strong>' + t.tkt_user_name + '</strong> (' + (t.tkt_unt_name || t.tkt_user_role) + ') on ' + (new Date(t.tkt_created_at)).toLocaleString());

            // Attachment link
            if (res.downloadUrl) {
                $('#modalAttachmentBox').removeClass('d-none');
                $('#modalAttachmentLink').attr('href', res.downloadUrl);
            } else {
                $('#modalAttachmentBox').addClass('d-none');
            }

            // Timeline
            let trailHtml = '';
            if (res.activities && res.activities.length > 0) {
                res.activities.forEach(function(act) {
                    let isResolverAct = act.act_action === 'Resolved' || act.act_action === 'In Progress' || act.act_action === 'Returned';
                    let actBg = isResolverAct ? 'background:#ecfdf5; border-left:3px solid #10b981;' : 'background:#f8fafc; border-left:3px solid #0284c7;';
                    trailHtml += `
                        <div class="p-2 rounded mb-2 border" style="${actBg}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-weight-bold text-dark" style="font-size:11.5px;">
                                    <i class="fas fa-user-circle mr-1"></i> ${act.act_user_name} <span class="text-muted">(${act.act_user_role})</span>
                                </span>
                                <span class="badge badge-light border text-muted" style="font-size:9.5px;">${act.act_action} • ${(new Date(act.act_created_at)).toLocaleDateString()}</span>
                            </div>
                            <div class="text-dark small" style="white-space:pre-wrap; font-size:11.5px;">${act.act_message}</div>
                        </div>
                    `;
                });
            } else {
                trailHtml = '<div class="text-muted small">No responses yet.</div>';
            }
            $('#modalActivityTrail').html(trailHtml);

            // Form actions
            $('#modalReplyForm').attr('action', '/support/tickets/' + t.tkt_id + '/reply');
            $('#userReplySection').removeClass('d-none');

            @if($isResolver)
                $('#modalResolverForm').attr('action', '/support/tickets/' + t.tkt_id + '/status');
            @endif
        },
        error: function(err) {
            $('#modalActivityTrail').html('<div class="text-danger small py-2"><i class="fas fa-exclamation-triangle mr-1"></i> Could not load ticket details.</div>');
        }
    });
}
</script>

@endsection
