@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        .ap-card { border-radius: 10px; border: 1px solid #e5e7eb; }
        .ap-card > .card-header { background: #fff; border-bottom: 1px solid #eef0f3; }
        .ap-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
        .ap-sec { display: flex; align-items: center; gap: 8px; margin: 20px 0 12px; }
        .ap-sec:first-of-type { margin-top: 0; }
        .ap-sec .n { width: 20px; height: 20px; border-radius: 50%; background: #2563eb; color: #fff;
                     font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .ap-sec .t { font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: .4px; }
        .ap-sec .line { flex: 1; height: 1px; background: #eef0f3; }
        .ap-doc { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 9px 11px; background: #f8fafc; height: 100%; }
        .ap-doc.req { border-color: #f59e0b; background: #fffbeb; }
        .ap-doc.done { border-style: solid; border-color: #86efac; background: #f0fdf4; }
        .ap-doc .t { font-size: 12px; font-weight: 700; color: #334155; }
        .ap-list { max-height: 420px; overflow-y: auto; }
        .ap-list table { font-size: 12.5px; }
        .ap-list thead th { position: sticky; top: 0; background: #f1f5f9; z-index: 2; border-top: 0; font-size: 11px;
                            text-transform: uppercase; letter-spacing: .3px; color: #475569; }
        .form-control-sm { font-size: 12.5px; }
        .ap-foot { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #eef0f3; border-radius: 0 0 10px 10px; z-index: 3; }
    </style>

    <div class="container-fluid">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 10px;">
            <h1 class="m-0 font-weight-bold text-primary" style="font-size: 1.35rem; font-family: 'Rajdhani', sans-serif; letter-spacing: 1px;">
                <i class="fas fa-folder-plus mr-1"></i> PROJECTS
                <small class="text-muted font-weight-normal" style="font-size: .78rem;">&mdash; raised by SORD / DDPMO</small>
            </h1>

            @if($showForm)
                <a href="{{ route('addnewproject') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back to list
                </a>
            @else
                <a href="{{ route('addnewproject', ['new' => 1]) }}" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm font-weight-bold">
                    <i class="fas fa-plus-circle mr-1"></i> Add New Project
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small mb-3">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Please correct:</strong>
                <ul class="mb-0 mt-1 pl-3">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- LIST VIEW (default)                                       --}}
        {{-- ========================================================= --}}
        @if(! $showForm)
            <div class="card ap-card shadow-sm">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tabDrafts" role="tab" style="font-size: 12.5px;">
                                <i class="fas fa-pen text-warning mr-1"></i> Drafts
                                <span class="badge badge-warning ml-1">{{ $drafts->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tabPublished" role="tab" style="font-size: 12.5px;">
                                <i class="fas fa-check-circle text-success mr-1"></i> Published
                                <span class="badge badge-success ml-1">{{ $publishedProjects->count() }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0 tab-content">
                    {{-- DRAFTS --}}
                    <div class="tab-pane fade show active" id="tabDrafts" role="tabpanel">
                        <div class="ap-list">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Code</th>
                                        <th>Title</th>
                                        <th style="width: 140px;">Division</th>
                                        <th style="width: 115px;">Received</th>
                                        <th style="width: 95px;" class="text-center">Milestones</th>
                                        <th style="width: 100px;" class="text-center">Work Order</th>
                                        <th style="width: 95px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($drafts as $d)
                                        @php
                                            $dWo = $d->attachments->where('jat_type', 'Work Order')->isNotEmpty();
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold text-dark">{{ $d->prj_code }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($d->prj_title, 70) }}</td>
                                            <td class="text-muted">{{ optional($d->unit)->unt_namesh }}</td>
                                            <td class="text-muted">{{ $d->prj_rcptdt ? \Carbon\Carbon::parse($d->prj_rcptdt)->format('d M, Y') : '—' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-light border">{{ $d->milestones->count() }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($dWo)
                                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                                @else
                                                    <span class="badge badge-warning text-dark" title="Required before publishing">Missing</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('addnewproject', ['draft_id' => $d->prj_id]) }}"
                                                   class="btn btn-xs btn-outline-primary font-weight-bold px-2">
                                                    <i class="fas fa-pen mr-1"></i> Open
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="fas fa-folder-open fa-2x mb-2 d-block" style="opacity: .3;"></i>
                                                No drafts yet. Use <strong>Add New Project</strong> to raise one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PUBLISHED --}}
                    <div class="tab-pane fade" id="tabPublished" role="tabpanel">
                        <div class="px-3 py-2 border-bottom">
                            <input type="text" id="pubSearch" class="form-control form-control-sm" style="max-width: 340px;"
                                   placeholder="Filter by code, title, division or status..." onkeyup="filterPublished()">
                        </div>
                        <div class="ap-list">
                            <table class="table table-sm table-hover mb-0" id="pubTable">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Code</th>
                                        <th>Title</th>
                                        <th style="width: 140px;">Division</th>
                                        <th style="width: 170px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($publishedProjects as $p)
                                        <tr>
                                            <td class="font-weight-bold text-dark">{{ $p->prj_code }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($p->prj_title, 70) }}</td>
                                            <td class="text-muted">{{ optional($p->unit)->unt_namesh }}</td>
                                            <td><span class="badge badge-light border text-dark">{{ $p->prj_status }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-5">No published projects.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- FORM VIEW (Add New / Open draft)                          --}}
        {{-- ========================================================= --}}
        @if($showForm)
        <form id="projectForm" method="POST" enctype="multipart/form-data"
              action="{{ $project ? route('projects.save-draft', $project->prj_id) : route('save-project') }}">
            @csrf
            <input type="hidden" name="action" id="formAction" value="draft">
            <input type="hidden" name="prj_status" id="publishStatusInput" value="Under assignment">

            <div class="card ap-card shadow-sm mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <h3 class="card-title font-weight-bold m-0" style="font-size: 14px;">
                        @if($project)
                            <span class="badge badge-warning mr-1">DRAFT</span>
                            {{ $project->prj_code }} &mdash; ID {{ $project->prj_id }}
                            <span class="text-muted font-weight-normal ml-1">({{ optional($project->unit)->unt_namesh }})</span>
                        @else
                            <i class="fas fa-file-signature text-primary mr-1"></i> New Project
                        @endif
                    </h3>
                    <small class="text-muted"><i class="fas fa-eye-slash mr-1"></i> Nothing is mandatory to save a draft &mdash; <strong>*</strong> fields are needed to publish.</small>
                </div>

                <div class="card-body py-3">

                    {{-- 1. PARTICULARS --}}
                    <div class="ap-sec"><span class="n">1</span><span class="t">Particulars</span><span class="line"></span></div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-2">
                            <div class="ap-label">Division <span class="text-danger">*</span></div>
                            @if($project)
                                <input type="text" class="form-control form-control-sm bg-light font-weight-bold"
                                       value="{{ optional($project->unit)->unt_namesh ?: optional($project->unit)->unt_name }}" readonly>
                                <small class="text-muted" style="font-size: 10.5px;">Fixed &mdash; the project number comes from this block.</small>
                            @else
                                <select name="prj_unt_id" class="form-control form-control-sm" required>
                                    <option value="">Select division...</option>
                                    @foreach(($divisions ?? collect()) as $div)
                                        <option value="{{ $div->unt_id }}" {{ (string) old('prj_unt_id') === (string) $div->unt_id ? 'selected' : '' }}>
                                            {{ $div->unt_namesh ?: $div->unt_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="ap-label">Code <span class="text-danger">*</span></div>
                            <input type="text" name="prj_code" class="form-control form-control-sm font-weight-bold"
                                   style="text-transform: uppercase;" maxlength="9"
                                   value="{{ old('prj_code', $project->prj_code ?? '') }}" placeholder="e.g. HAYSTACK">
                        </div>
                        <div class="col-md-7 form-group mb-2">
                            <div class="ap-label">Title <span class="text-danger">*</span></div>
                            <input type="text" name="prj_title" class="form-control form-control-sm"
                                   value="{{ old('prj_title', $project->prj_title ?? '') }}" placeholder="Full project title">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-2">
                            <div class="ap-label">Sponsor / Agency <span class="text-danger">*</span></div>
                            <input type="text" name="prj_sponsor" class="form-control form-control-sm"
                                   value="{{ old('prj_sponsor', $project->prj_sponsor ?? '') }}" placeholder="Funding agency">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <div class="ap-label">Received <span class="text-danger">*</span></div>
                            <input type="date" name="prj_rcptdt" class="form-control form-control-sm" max="{{ date('Y-m-d') }}"
                                   value="{{ old('prj_rcptdt', isset($project->prj_rcptdt) && $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <div class="ap-label">Proposed Cost (Rs.)</div>
                            <input type="number" name="prj_propcost" min="0" class="form-control form-control-sm"
                                   value="{{ old('prj_propcost', $project->prj_propcost ?? '') }}" placeholder="0">
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <div class="ap-label">Scope of Work</div>
                            <input type="text" name="prj_scope" maxlength="255" class="form-control form-control-sm"
                                   value="{{ old('prj_scope', $project->prj_scope ?? '') }}" placeholder="Short scope">
                        </div>
                    </div>

                    {{-- 2. DATES --}}
                    <div class="ap-sec"><span class="n">2</span><span class="t">Key Dates</span><span class="line"></span></div>
                    <div class="row">
                        @php
                            $dateFields = [
                                'prj_propdt'   => 'Proposal',
                                'prj_assigndt' => 'Assigned',
                                'prj_aprvdt'   => 'Approval',
                                'prj_startdt'  => 'Start',
                                'prj_estenddt' => 'EDC (Est. End)',
                            ];
                        @endphp
                        @foreach($dateFields as $field => $label)
                            <div class="col-md-2 col-6 form-group mb-2">
                                <div class="ap-label">{{ $label }}</div>
                                <input type="date" name="{{ $field }}" class="form-control form-control-sm"
                                       value="{{ old($field, isset($project->$field) && $project->$field ? \Carbon\Carbon::parse($project->$field)->format('Y-m-d') : '') }}">
                            </div>
                        @endforeach
                        <div class="col-md-2 col-6 form-group mb-2">
                            <div class="ap-label">Status on publish</div>
                            <input type="text" class="form-control form-control-sm bg-light font-weight-bold" id="statusPreview"
                                   value="Under assignment" readonly>
                        </div>
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Status needs its dates &mdash; Under feasibility: assigned &middot; Approval awaited: + proposal &middot;
                        Funds awaited: + approval &middot; Work in progress: + start.
                    </small>

                    {{-- 3. DOCUMENTS --}}
                    <div class="ap-sec"><span class="n">3</span><span class="t">Documents</span><span class="line"></span></div>
                    <div class="row">
                        @php
                            $docs = [
                                ['doc_workorder', 'Work Order', 'fa-briefcase', true],
                                ['doc_ppf', 'PPF', 'fa-file-invoice', false],
                                ['doc_urd', 'URD', 'fa-file-alt', false],
                                ['doc_approval_letter', 'Approval Letter', 'fa-file-signature', false],
                            ];
                        @endphp
                        @foreach($docs as [$input, $type, $icon, $required])
                            @php
                                $existing = $project ? $project->attachments->where('jat_type', $type)->first() : null;
                            @endphp
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="ap-doc {{ $existing ? 'done' : ($required ? 'req' : '') }}">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="t">
                                            <i class="fas {{ $icon }} {{ $existing ? 'text-success' : ($required ? 'text-warning' : 'text-primary') }} mr-1"></i>
                                            {{ $type }}@if($required) <span class="text-danger">*</span>@endif
                                        </span>
                                        @if($existing)
                                            <a href="{{ route('attachment.view', $existing->jat_id) }}" target="_blank"
                                               class="badge badge-success"><i class="fas fa-eye mr-1"></i> View</a>
                                        @endif
                                    </div>
                                    <input type="file" name="{{ $input }}" class="form-control-file" style="font-size: 10.5px; width: 100%;"
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    @if($required && ! $existing)
                                        <small class="text-warning font-weight-bold d-block mt-1" style="font-size: 10.5px;">
                                            Mandatory to publish
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        <i class="fas fa-paperclip mr-1"></i> Uploaded documents stay with the project and show on its page everywhere.
                    </small>

                    {{-- 4. MILESTONES --}}
                    <div class="ap-sec"><span class="n">4</span><span class="t">Milestones</span><span class="line"></span></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-2" id="msTable" style="font-size: 12.5px;">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>Description</th>
                                    <th style="width: 180px;">Target Date</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="msBody">
                                @php
                                    $msRows = old('milestones');
                                    if (!is_array($msRows)) {
                                        $msRows = collect($draftMilestones ?? [])->map(fn ($m) => [
                                            'desc' => $m->msn_desc,
                                            'date' => $m->msn_targetdt ? \Carbon\Carbon::parse($m->msn_targetdt)->format('Y-m-d') : '',
                                        ])->all();
                                    }
                                    if (empty($msRows)) { $msRows = [['desc' => '', 'date' => '']]; }
                                @endphp
                                @foreach($msRows as $i => $row)
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold sn">{{ $i + 1 }}</td>
                                        <td><input type="text" name="milestones[{{ $i }}][desc]" class="form-control form-control-sm border-0 bg-light"
                                                   value="{{ $row['desc'] ?? '' }}" placeholder="e.g. Procurement of hardware"></td>
                                        <td><input type="date" name="milestones[{{ $i }}][date]" class="form-control form-control-sm border-0 bg-light"
                                                   value="{{ $row['date'] ?? '' }}"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="msRemove(this)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="msAdd()">
                        <i class="fas fa-plus mr-1"></i> Add milestone
                    </button>
                    <small class="text-muted ml-2" style="font-size: 11px;">Saved with the draft; more can be added on the project page after publishing.</small>
                </div>

                <div class="card-footer ap-foot d-flex justify-content-between align-items-center flex-wrap py-2" style="gap: 8px;">
                    <div>
                        @if($project)
                            <button type="button" class="btn btn-sm btn-outline-danger px-3" data-toggle="modal" data-target="#discardDraftModal">
                                <i class="fas fa-trash mr-1"></i> Discard draft
                            </button>
                        @endif
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn btn-sm btn-primary px-4 font-weight-bold" onclick="document.getElementById('formAction').value='draft';">
                            <i class="fas fa-save mr-1"></i> Save as Draft
                        </button>
                        <button type="button" class="btn btn-sm btn-success px-4 font-weight-bold" data-toggle="modal" data-target="#publishModal">
                            <i class="fas fa-paper-plane mr-1"></i> Publish
                        </button>
                    </div>
                </div>
            </div>
        </form>
        @endif
    </div>

    {{-- ================= PUBLISH MODAL ================= --}}
    @if($showForm)
    <div class="modal fade" id="publishModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header py-2">
                    <h5 class="modal-title font-weight-bold" style="font-size: 15px;">
                        <i class="fas fa-paper-plane text-success mr-2"></i> Publish to Division
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div id="woWarning" class="alert alert-danger py-2 px-3 small mb-3" style="display: none;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Work Order is mandatory.</strong> Upload it in the Documents section before publishing.
                    </div>
                    <p class="small text-muted mb-3">
                        The project will be saved and handed over to the division. From then on it appears in their
                        project list and every change goes through an amendment case.
                    </p>
                    <div class="form-group mb-2">
                        <div class="ap-label">Publish with status</div>
                        <select id="publishStatusSelect" class="form-control form-control-sm">
                            @foreach(['Under assignment', 'Under feasibility', 'Approval awaited', 'Funds awaited', 'Work in progress'] as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <small class="text-muted">If the required dates or the Work Order are missing, the publish is refused and nothing changes.</small>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success px-4 font-weight-bold" onclick="confirmPublish()">
                        <i class="fas fa-check mr-1"></i> Yes, Publish
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ================= DISCARD MODAL ================= --}}
    @if($showForm && $project)
    <div class="modal fade" id="discardDraftModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-body text-center px-4 py-4">
                    <i class="fas fa-trash fa-2x text-danger mb-3"></i>
                    <h6 class="font-weight-bold">Discard draft {{ $project->prj_code }}?</h6>
                    <p class="text-muted small mb-3">The draft and its milestones are deleted. This cannot be undone.</p>
                    <form method="POST" action="{{ route('projects.delete-draft', $project->prj_id) }}">
                        @csrf
                        <button type="button" class="btn btn-sm btn-secondary px-3 mr-1" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Discard</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
@if($showForm)
const HAS_WORK_ORDER = {{ $hasWorkOrder ? 'true' : 'false' }};

function msAdd() {
    const body = document.getElementById('msBody');
    const i = body.querySelectorAll('tr').length;
    const tr = document.createElement('tr');
    tr.innerHTML =
        '<td class="text-center text-muted font-weight-bold sn">' + (i + 1) + '</td>' +
        '<td><input type="text" name="milestones[' + i + '][desc]" class="form-control form-control-sm border-0 bg-light" placeholder="Milestone description"></td>' +
        '<td><input type="date" name="milestones[' + i + '][date]" class="form-control form-control-sm border-0 bg-light"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" onclick="msRemove(this)"><i class="fas fa-trash"></i></button></td>';
    body.appendChild(tr);
}

function msRemove(btn) {
    const body = document.getElementById('msBody');
    btn.closest('tr').remove();
    if (body.querySelectorAll('tr').length === 0) { msAdd(); }
    renumber();
}

function renumber() {
    document.querySelectorAll('#msBody tr').forEach(function (tr, i) {
        tr.querySelector('.sn').innerText = i + 1;
        const d = tr.querySelector('input[type="text"]');
        const t = tr.querySelector('input[type="date"]');
        if (d) d.name = 'milestones[' + i + '][desc]';
        if (t) t.name = 'milestones[' + i + '][date]';
    });
}

function workOrderReady() {
    if (HAS_WORK_ORDER) return true;
    const input = document.querySelector('input[name="doc_workorder"]');
    return !!(input && input.files && input.files.length > 0);
}

function confirmPublish() {
    if (!workOrderReady()) {
        document.getElementById('woWarning').style.display = 'block';
        return;
    }
    document.getElementById('publishStatusInput').value = document.getElementById('publishStatusSelect').value;
    document.getElementById('formAction').value = 'publish';
    document.getElementById('projectForm').submit();
}

document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('publishStatusSelect');
    const preview = document.getElementById('statusPreview');
    if (sel && preview) {
        sel.addEventListener('change', function () {
            preview.value = sel.value;
            document.getElementById('publishStatusInput').value = sel.value;
        });
    }
    $('#publishModal').on('show.bs.modal', function () {
        document.getElementById('woWarning').style.display = workOrderReady() ? 'none' : 'block';
    });
});
@else
function filterPublished() {
    const q = (document.getElementById('pubSearch').value || '').toLowerCase();
    document.querySelectorAll('#pubTable tbody tr').forEach(function (tr) {
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}
@endif
</script>
@endsection
