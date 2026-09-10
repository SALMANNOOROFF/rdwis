@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 pb-5">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold" style="letter-spacing: -0.5px;">
                        <i class="fas fa-folder-plus text-primary mr-2"></i> Create New Account
                    </h1>
                    <p class="text-muted text-sm mb-0">Project Financial Head Opening & Budget Share Allocation</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 text-sm">
                        <li class="breadcrumb-item"><a href="{{ route('finance.accounts.index') }}"><i class="fas fa-wallet"></i> Accounts</a></li>
                        <li class="breadcrumb-item active">Create New Account</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- Flash Success Alert --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-left: 5px solid #28a745 !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x mr-3 text-success"></i>
                    <div>
                        <h5 class="alert-heading font-weight-bold mb-1">Account Created Successfully!</h5>
                        <p class="mb-0 text-sm">{{ session('success') }}</p>
                        <div class="mt-2">
                            <a href="{{ route('finance.accounts.index') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                                <i class="fas fa-list-ul mr-1"></i> View Accounts List
                            </a>
                        </div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            {{-- Flash Error Alerts --}}
            @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert" style="border-left: 5px solid #dc3545 !important;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3 text-danger mt-1"></i>
                    <div>
                        <h5 class="alert-heading font-weight-bold mb-1">Account Opening Blocked</h5>
                        <ul class="mb-0 pl-3 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <form action="{{ route('finance.accounts.store') }}" method="POST" id="createAccountForm" autocomplete="off">
                @csrf

                <div class="row">
                    {{-- Left Column: Project & Account Setup --}}
                    <div class="col-lg-7">
                        {{-- Card 1: Project & Identification --}}
                        <div class="card card-outline card-primary shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h3 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-project-diagram text-primary mr-2"></i> 1. Project & Account Identification
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Project Dropdown (All eligible projects without existing head) --}}
                                    <div class="col-md-12 mb-3">
                                        <label for="project_id" class="font-weight-bold text-dark text-sm">
                                            Project <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-primary"><i class="fas fa-tasks"></i></span>
                                            </div>
                                            <select name="project_id" id="project_id" class="form-control font-weight-semibold" required>
                                                <option value="" selected disabled>-- Select Project --</option>
                                                @foreach($projects as $p)
                                                    <option value="{{ $p->prj_id }}" {{ old('project_id') == $p->prj_id ? 'selected' : '' }}>
                                                        {{ $p->prj_code }} — {{ $p->prj_title }} ({{ $p->unt_namesh ?: ($p->unt_name ?: 'Division') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append d-none" id="projectLoadingSpinner">
                                                <span class="input-group-text bg-white"><i class="fas fa-spinner fa-spin text-primary"></i></span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Select an approved project to open its financial account.</small>
                                    </div>
                                </div>

                                {{-- Project Info Banner --}}
                                <div id="projectInfoBanner" class="p-3 mb-3 rounded border d-none" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge badge-primary px-2 py-1 mb-1" id="bannerPrjCode">CODE</span>
                                            <span class="badge badge-secondary px-2 py-1 mb-1 ml-1" id="bannerPrjStatus">Status</span>
                                            <h6 class="font-weight-bold text-dark mb-1" id="bannerPrjTitle">Project Title</h6>
                                            <p class="text-xs text-muted mb-0">Project ID: <span class="font-weight-bold text-dark" id="bannerPrjId">-</span></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-info px-2 py-1 text-xs" id="bannerMilestones">
                                                <i class="fas fa-flag mr-1"></i> <span id="milestoneCount">0</span> Milestones
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="row">
                                    {{-- Account ID --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="hed_id" class="font-weight-bold text-dark text-sm">
                                            Account ID <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-secondary"><i class="fas fa-hashtag"></i></span>
                                            </div>
                                            <input type="number" name="hed_id" id="hed_id" class="form-control font-weight-bold bg-light" 
                                                   value="{{ old('hed_id') }}" readonly required placeholder="Auto-assigned">
                                        </div>
                                        <small class="form-text text-muted" id="hedIdHelp">Assigned automatically based on project and division ID range.</small>
                                    </div>

                                    {{-- Account Code --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="hed_code" class="font-weight-bold text-dark text-sm">
                                            Account Code <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-secondary"><i class="fas fa-barcode"></i></span>
                                            </div>
                                            <input type="text" name="hed_code" id="hed_code" class="form-control font-weight-bold text-uppercase" 
                                                   value="{{ old('hed_code') }}" required placeholder="e.g. AUV">
                                        </div>
                                        <small class="form-text text-muted" id="hedCodeHelp">Unique alphanumeric code (must not end with "-" or "_").</small>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Account Opening Date --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="hed_opendt" class="font-weight-bold text-dark text-sm">
                                            Opening Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-secondary"><i class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date" name="hed_opendt" id="hed_opendt" class="form-control" 
                                                   value="{{ old('hed_opendt', date('Y-m-d')) }}" required>
                                        </div>
                                        <small class="form-text text-muted">Effective opening date for funding transfers & commitments.</small>
                                    </div>

                                    {{-- Transaction Type --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-dark text-sm d-block">
                                            Transaction Type <span class="text-danger">*</span>
                                        </label>
                                        <div class="custom-control custom-radio custom-control-inline mt-1">
                                            <input type="radio" id="transtype_1" name="hed_transtype" value="1" class="custom-control-input" 
                                                   {{ old('hed_transtype', '1') == '1' ? 'checked' : '' }}>
                                            <label class="custom-control-label text-sm font-weight-normal" for="transtype_1">
                                                <strong>1 — Without GST</strong> <span class="text-muted text-xs">(GST in MTSS Share)</span>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline mt-1">
                                            <input type="radio" id="transtype_2" name="hed_transtype" value="2" class="custom-control-input" 
                                                   {{ old('hed_transtype') == '2' ? 'checked' : '' }}>
                                            <label class="custom-control-label text-sm font-weight-normal" for="transtype_2">
                                                <strong>2 — With GST</strong> <span class="text-muted text-xs">(GST in R&D/CSRF Share)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Budget & Share Allocations --}}
                        <div class="card card-outline card-success shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h3 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-coins text-success mr-2"></i> 2. Budget & Funding Allocations (PKR)
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Total Allocation --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="alloc" class="font-weight-bold text-dark text-sm">
                                            Total Funding Allocation <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light font-weight-bold">PKR</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="alloc" id="alloc" class="form-control text-right font-weight-bold text-primary" 
                                                   value="{{ old('alloc', '0') }}" required>
                                        </div>
                                        <small class="form-text text-muted">Initial approved funding amount.</small>
                                    </div>

                                    {{-- MTSS Share --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="mtss_share" class="font-weight-bold text-dark text-sm">
                                            MTSS Share <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light font-weight-bold">PKR</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="mtss_share" id="mtss_share" class="form-control text-right font-weight-bold text-warning" 
                                                   value="{{ old('mtss_share', '0') }}" required>
                                        </div>
                                        <small class="form-text text-muted">Transfer allocation for MTSS pool.</small>
                                    </div>

                                    {{-- CSRF Share --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="sha_cf" class="font-weight-bold text-dark text-sm">
                                            CSRF Share <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light font-weight-bold">PKR</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="sha_cf" id="sha_cf" class="form-control text-right font-weight-bold text-info" 
                                                   value="{{ old('sha_cf', '0') }}" required>
                                        </div>
                                        <small class="form-text text-muted">Commercial / CSRF share allocation.</small>
                                    </div>
                                </div>

                                {{-- Live Calculated Share KPI Cards --}}
                                <div class="row mt-2">
                                    <div class="col-md-6 mb-2">
                                        <div class="p-3 rounded border text-center" style="background: #f1f5f9;">
                                            <div class="text-xs text-uppercase font-weight-bold text-muted mb-1">
                                                R&D Share <span class="text-xs font-weight-normal">(Funding - MTSS)</span>
                                            </div>
                                            <h4 class="font-weight-bold text-dark mb-0" id="cardRdwShare">PKR 0.00</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="p-3 rounded border text-center" style="background: #e0f2fe; border-color: #bae6fd !important;">
                                            <div class="text-xs text-uppercase font-weight-bold text-primary mb-1">
                                                Project Share (PCC) <span class="text-xs font-weight-normal">(R&D - CSRF)</span>
                                            </div>
                                            <h4 class="font-weight-bold text-primary mb-0" id="cardShaPcc">PKR 0.00</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted text-xs mt-1 text-center">
                                    <i class="fas fa-info-circle text-info"></i> The sum of subhead allocations below <strong>must exactly match</strong> the Project Share (PCC).
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Subhead Allocations & Confirmation --}}
                    <div class="col-lg-5">
                        {{-- Card 3: Subhead Allocations Repeater --}}
                        <div class="card card-outline card-info shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-layer-group text-info mr-2"></i> 3. Subheads (Max 5)
                                </h3>
                                <div>
                                    <button type="button" id="btnAddSubhead" class="btn btn-xs btn-outline-info font-weight-bold px-2 py-1 rounded">
                                        <i class="fas fa-plus mr-1"></i> Add Row
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                {{-- Quick Presets --}}
                                <div class="mb-2">
                                    <span class="text-xs font-weight-bold text-muted mr-1">Quick Add:</span>
                                    <button type="button" class="btn btn-xs btn-light border text-xs mr-1 mb-1 quick-subhead-btn" data-name="Equipment">+ Equipment</button>
                                    <button type="button" class="btn btn-xs btn-light border text-xs mr-1 mb-1 quick-subhead-btn" data-name="Construction">+ Construction</button>
                                    <button type="button" class="btn btn-xs btn-light border text-xs mr-1 mb-1 quick-subhead-btn" data-name="Training">+ Training</button>
                                    <button type="button" class="btn btn-xs btn-light border text-xs mr-1 mb-1 quick-subhead-btn" data-name="Software">+ Software</button>
                                    <button type="button" class="btn btn-xs btn-light border text-xs mr-1 mb-1 quick-subhead-btn" data-name="HR">+ HR</button>
                                    <button type="button" class="btn btn-xs btn-light border text-xs mr-1 mb-1 quick-subhead-btn" data-name="Misc">+ Misc</button>
                                </div>

                                {{-- Subheads Table --}}
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-2" id="subheadsTable">
                                        <thead class="bg-light text-xs text-muted text-uppercase">
                                            <tr>
                                                <th style="width: 10%;">#</th>
                                                <th style="width: 45%;">Subhead Name</th>
                                                <th style="width: 35%;">Allocation (PKR)</th>
                                                <th style="width: 10%; text-align: center;">Act</th>
                                            </tr>
                                        </thead>
                                        <tbody id="subheadsContainer">
                                            {{-- Rows dynamically populated by JS --}}
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Live Reconciliation Status Box --}}
                                <div id="reconciliationBox" class="p-3 rounded border mt-3 transition-all" style="background: #fff; border-color: #e2e8f0 !important;">
                                    <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                        <span>Subheads Total:</span>
                                        <strong class="text-dark" id="displaySubheadSum">PKR 0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between text-xs text-muted mb-2">
                                        <span>Target Project Share (PCC):</span>
                                        <strong class="text-primary" id="displayTargetPcc">PKR 0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between text-xs font-weight-bold pt-2 border-top">
                                        <span>Balance Difference:</span>
                                        <span id="displayBalanceDiff" class="text-danger">PKR 0.00</span>
                                    </div>
                                    <div class="mt-2 text-center" id="balanceStatusBadge">
                                        <span class="badge badge-warning px-3 py-1 text-xs">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Allocations not yet balanced
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: Milestones & Confirmation Action --}}
                        <div class="card shadow-sm border mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('finance.accounts.index') }}" class="btn btn-outline-secondary font-weight-bold">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                    <button type="submit" id="btnSubmitAccount" class="btn btn-primary font-weight-bold px-4 shadow-sm" disabled>
                                        <i class="fas fa-save mr-1"></i> Open New Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // Normalization mapping for standard subhead names (Rule E)
    const subheadNormMap = {
        'equipment': 'Equipment',
        'construction': 'Construction',
        'training': 'Training',
        'software': 'Software',
        'hr': 'HR',
        'misc': 'Misc'
    };

    function normalizeSubheadName(name) {
        const trimmed = (name || '').trim();
        const lower = trimmed.toLowerCase();
        return subheadNormMap[lower] || trimmed;
    }

    // Elements
    const projectSelect = document.getElementById('project_id');
    const projectSpinner = document.getElementById('projectLoadingSpinner');
    const projectBanner = document.getElementById('projectInfoBanner');
    const bannerPrjCode = document.getElementById('bannerPrjCode');
    const bannerPrjStatus = document.getElementById('bannerPrjStatus');
    const bannerPrjTitle = document.getElementById('bannerPrjTitle');
    const bannerPrjId = document.getElementById('bannerPrjId');
    const milestoneCount = document.getElementById('milestoneCount');

    const hedIdInput = document.getElementById('hed_id');
    const hedCodeInput = document.getElementById('hed_code');
    const hedCodeHelp = document.getElementById('hedCodeHelp');

    const allocInput = document.getElementById('alloc');
    const mtssShareInput = document.getElementById('mtss_share');
    const shaCfInput = document.getElementById('sha_cf');

    const cardRdwShare = document.getElementById('cardRdwShare');
    const cardShaPcc = document.getElementById('cardShaPcc');

    const subheadsContainer = document.getElementById('subheadsContainer');
    const btnAddSubhead = document.getElementById('btnAddSubhead');
    const btnSubmit = document.getElementById('btnSubmitAccount');

    const displaySubheadSum = document.getElementById('displaySubheadSum');
    const displayTargetPcc = document.getElementById('displayTargetPcc');
    const displayBalanceDiff = document.getElementById('displayBalanceDiff');
    const balanceStatusBadge = document.getElementById('balanceStatusBadge');

    let subheadRowIndex = 0;

    // Format Currency Helper
    function formatPKR(val) {
        return 'PKR ' + Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // 1. Project Selection -> Populate Details & Compute Tentative IDs
    projectSelect.addEventListener('change', function() {
        const prjId = this.value;
        if (!prjId) return;

        projectSpinner.classList.remove('d-none');
        hedIdInput.value = '';
        hedCodeInput.value = '';

        fetch(`{{ url('/finance/accounts/ajax/project-details') }}/${prjId}`)
            .then(res => res.json())
            .then(data => {
                projectSpinner.classList.add('d-none');
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Show Project Info Banner
                bannerPrjCode.textContent = data.prj_code;
                bannerPrjStatus.textContent = data.prj_status || 'Approved';
                bannerPrjTitle.textContent = data.prj_title;
                bannerPrjId.textContent = data.prj_id;
                milestoneCount.textContent = data.milestone_count || 0;
                projectBanner.classList.remove('d-none');

                // Fill Head ID and Head Code
                hedIdInput.value = data.tentative_hed_id;
                hedCodeInput.value = data.tentative_hed_code;

                if (data.code_collision) {
                    hedCodeHelp.innerHTML = '<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Account code collided with existing account. Trailing "-" appended. Please edit to a unique alphanumeric code.</span>';
                } else {
                    hedCodeHelp.innerHTML = 'Account code auto-filled from project code. Unique alphanumeric code required.';
                }

                recalculate();
            })
            .catch(err => {
                projectSpinner.classList.add('d-none');
                console.error(err);
            });
    });

    // Auto-trigger if a project is already selected on page load (e.g. back navigation or validation failure)
    if (projectSelect.value) {
        projectSelect.dispatchEvent(new Event('change'));
    }

    // 3. Add Subhead Row
    function addSubheadRow(defaultName = '', defaultAlloc = '') {
        const currentCount = subheadsContainer.querySelectorAll('tr').length;
        if (currentCount >= 5) {
            alert('Maximum of 5 subheads allowed per account.');
            return;
        }

        const idx = subheadRowIndex++;
        const normName = normalizeSubheadName(defaultName);

        const tr = document.createElement('tr');
        tr.id = `sbh_row_${idx}`;
        tr.innerHTML = `
            <td class="text-center font-weight-bold text-muted row-number">${currentCount + 1}</td>
            <td>
                <input type="text" name="subheads[${idx}][name]" class="form-control form-control-sm font-weight-semibold sbh-name-input" 
                       value="${normName}" placeholder="e.g. Equipment, Misc" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="subheads[${idx}][alloc]" class="form-control form-control-sm text-right font-weight-bold sbh-alloc-input text-info" 
                       value="${defaultAlloc}" placeholder="0.00" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-subhead" title="Remove subhead">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

        subheadsContainer.appendChild(tr);

        // Normalize on blur
        const nameInput = tr.querySelector('.sbh-name-input');
        nameInput.addEventListener('blur', function() {
            this.value = normalizeSubheadName(this.value);
            recalculate();
        });

        // Amount input listener
        const allocInp = tr.querySelector('.sbh-alloc-input');
        allocInp.addEventListener('input', recalculate);

        // Remove button listener
        tr.querySelector('.btn-remove-subhead').addEventListener('click', function() {
            tr.remove();
            updateRowNumbers();
            recalculate();
        });

        updateRowNumbers();
        recalculate();
    }

    function updateRowNumbers() {
        const rows = subheadsContainer.querySelectorAll('tr');
        rows.forEach((row, i) => {
            row.querySelector('.row-number').textContent = i + 1;
        });

        if (rows.length >= 5) {
            btnAddSubhead.disabled = true;
            btnAddSubhead.classList.add('disabled');
        } else {
            btnAddSubhead.disabled = false;
            btnAddSubhead.classList.remove('disabled');
        }
    }

    btnAddSubhead.addEventListener('click', function() {
        addSubheadRow();
    });

    // Quick Add Buttons
    document.querySelectorAll('.quick-subhead-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            // Check if already added
            const existingInputs = subheadsContainer.querySelectorAll('.sbh-name-input');
            for (let inp of existingInputs) {
                if (inp.value.toLowerCase() === name.toLowerCase()) {
                    inp.focus();
                    return;
                }
            }
            addSubheadRow(name, '');
        });
    });

    // 4. Recalculation Engine
    function recalculate() {
        const alloc = parseFloat(allocInput.value) || 0;
        const mtssShare = parseFloat(mtssShareInput.value) || 0;
        const shaCf = parseFloat(shaCfInput.value) || 0;

        const rdwShare = alloc - mtssShare;
        const shaPcc = rdwShare - shaCf;

        cardRdwShare.textContent = formatPKR(rdwShare);
        cardShaPcc.textContent = formatPKR(shaPcc);

        // Subheads Sum
        let subheadSum = 0;
        const allocInputs = subheadsContainer.querySelectorAll('.sbh-alloc-input');
        allocInputs.forEach(inp => {
            subheadSum += parseFloat(inp.value) || 0;
        });

        displaySubheadSum.textContent = formatPKR(subheadSum);
        displayTargetPcc.textContent = formatPKR(shaPcc);

        const diff = shaPcc - subheadSum;
        displayBalanceDiff.textContent = formatPKR(Math.abs(diff));

        const hedCode = (hedCodeInput.value || '').trim();
        const codeValid = hedCode.length > 0 && !hedCode.endsWith('-') && !hedCode.endsWith('_');
        const hasSubheads = allocInputs.length > 0;
        const isBalanced = shaPcc >= 0 && Math.abs(diff) < 0.01 && hasSubheads;

        if (isBalanced) {
            displayBalanceDiff.className = 'text-success font-weight-bold';
            displayBalanceDiff.textContent = 'PKR 0.00 (Balanced)';
            balanceStatusBadge.innerHTML = `
                <span class="badge badge-success px-3 py-1 text-xs font-weight-bold">
                    <i class="fas fa-check-circle mr-1"></i> Allocation Perfectly Balanced
                </span>
            `;
            if (codeValid && hedIdInput.value) {
                btnSubmit.disabled = false;
            } else {
                btnSubmit.disabled = true;
            }
        } else {
            displayBalanceDiff.className = 'text-danger font-weight-bold';
            if (diff > 0) {
                displayBalanceDiff.textContent = 'PKR ' + Number(diff).toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' Underallocated';
            } else {
                displayBalanceDiff.textContent = 'PKR ' + Number(Math.abs(diff)).toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' Overallocated';
            }
            balanceStatusBadge.innerHTML = `
                <span class="badge badge-warning px-3 py-1 text-xs">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Subheads must equal Project Share (PKR ${Number(shaPcc).toLocaleString('en-US', { minimumFractionDigits: 2 })})
                </span>
            `;
            btnSubmit.disabled = true;
        }
    }

    allocInput.addEventListener('input', recalculate);
    mtssShareInput.addEventListener('input', recalculate);
    shaCfInput.addEventListener('input', recalculate);
    hedCodeInput.addEventListener('input', recalculate);

    // Initial default subhead row
    addSubheadRow('Equipment', '');

    // Form submit guard
    document.getElementById('createAccountForm').addEventListener('submit', function(e) {
        const hedCode = (hedCodeInput.value || '').trim();
        if (hedCode.endsWith('-') || hedCode.endsWith('_')) {
            e.preventDefault();
            alert('Account code must not end with "-" or "_". Please edit it to a valid code.');
            hedCodeInput.focus();
            return false;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Opening Account...';
    });
})();
</script>
@endpush
