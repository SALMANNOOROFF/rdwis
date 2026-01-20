@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        /* --- GLOBAL & CARD STYLES --- */
        .card-add-project {
            border-top: 4px solid #007bff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        .form-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        /* --- STEPPER --- */
        .stepper-box {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .step-pill {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            color: #aaa;
            background: #f8f9fa;
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .step-pill.active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
        }

        .step-pill.completed {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }

        .step-line {
            width: 30px;
            height: 2px;
            background: #eee;
        }

        .step-line.filled {
            background: #28a745;
        }

        /* --- DOC CARD --- */
        .doc-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-left: 3px solid #007bff;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .doc-card:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .doc-icon {
            width: 35px;
            height: 35px;
            background: #f4f6f9;
            color: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .doc-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #444;
        }

        .doc-desc {
            font-size: 0.75rem;
            color: #888;
        }

        .file-input-hidden {
            display: none;
        }

        /* --- TABLES --- */
        .table-custom thead th {
            background: #f8f9fa;
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }

        .table-custom tbody td {
            vertical-align: middle !important;
            font-size: 0.9rem;
        }
    </style>

    <div class="container-fluid">

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i> Form Error:</strong> Please check fields.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <div class="card card-outline card-add-project {{ $step == 1 ? 'card-primary' : 'card-success' }}">

            <div class="form-header">
                <h3 class="form-title">
                    @if($step == 1) <i class="fas fa-file-signature text-primary mr-2"></i> Project Initiation @endif
                    @if($step == 2) <i class="fas fa-cogs text-success mr-2"></i> Execution Planning @endif
                </h3>

                <div class="stepper-box">
                    <div class="step-pill {{ $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' }}">
                        <i class="fas {{ $step > 1 ? 'fa-check' : 'fa-1' }} mr-1"></i> Initiate
                    </div>
                    <div class="step-line {{ $step > 1 ? 'filled' : '' }}"></div>
                    <div class="step-pill {{ $step == 2 ? 'active' : '' }}">
                        <i class="fas fa-2 mr-1"></i> Work Order
                    </div>
                </div>
            </div>

            {{-- === PHASE 1 FORM: INITIATE === --}}
            @if($step == 1)
            <form action="{{ route('save-project') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    {{-- Row 1: Basic --}}
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Project Code <span class="text-danger">*</span></label>
                            <input type="text" name="prj_code" class="form-control" placeholder="e.g. P-2026-01" required>
                        </div>
                        <div class="col-md-9 form-group">
                            <label>Project Title <span class="text-danger">*</span></label>
                            <input type="text" name="prj_title" class="form-control" placeholder="Enter Full Project Title" required>
                        </div>
                    </div>

                    {{-- Row 2: Finance & Agency --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Sponsor / Agency</label>
                            <input type="text" name="prj_sponsor" class="form-control" placeholder="Funding Agency Name">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Proposed Cost</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">Rs.</span></div>
                                <input type="number" name="prj_propcost" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: IMPORTANT DATES --}}
                    <div class="row bg-light pt-2 pb-1 mb-3 rounded border">
                        <div class="col-md-4 form-group">
                            <label class="text-primary small text-uppercase">Proposal Date</label>
                            <input type="date" name="prj_propdt" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="text-primary small text-uppercase">Assigned Date</label>
                            <input type="date" name="prj_assigndt" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="text-danger small text-uppercase">Approval Date <span class="text-danger">*</span></label>
                            <input type="date" name="prj_aprvdt" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    {{-- Row 4: Scope --}}
                    <div class="form-group">
                        <label>Scope of Work / Description</label>
                        <textarea name="prj_scope" class="form-control" rows="3" placeholder="Brief details about the project scope..."></textarea>
                    </div>

                    <h6 class="text-muted font-weight-bold mt-4 mb-3"><i class="fas fa-paperclip mr-1"></i> Phase 1 Documents</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="doc-card shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="doc-icon"><i class="fas fa-file-invoice"></i></div>
                                    <div>
                                        <div class="doc-title">Project Proposal (PPF)</div>
                                    </div>
                                </div>
                                <div style="width: 32px; height: 32px;">
                                    <label for="file-ppf" class="btn btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center w-100 h-100" id="btn-ppf" style="cursor: pointer;">
                                        <i class="fas fa-upload small"></i>
                                    </label>
                                    <input type="file" id="file-ppf" name="doc_ppf" class="file-input-hidden" onchange="updateUploadUI(this, 'btn-ppf')">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="doc-card shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="doc-icon"><i class="fas fa-file-contract"></i></div>
                                    <div>
                                        <div class="doc-title">User Requirements (URD)</div>
                                    </div>
                                </div>
                                <div style="width: 32px; height: 32px;">
                                    <label for="file-urd" class="btn btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center w-100 h-100" id="btn-urd" style="cursor: pointer;">
                                        <i class="fas fa-upload small"></i>
                                    </label>
                                    <input type="file" id="file-urd" name="doc_urd" class="file-input-hidden" onchange="updateUploadUI(this, 'btn-urd')">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer text-right bg-white border-top">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold">
                        Save Draft & Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
            @endif

            {{-- === PHASE 2 FORM: EXECUTION === --}}
            @if($step == 2)
            <form action="{{ route('finalize-project', $project->prj_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    {{-- Summary Block --}}
                    <div class="alert alert-secondary d-flex justify-content-between align-items-center py-2 px-3 mb-4 rounded">
                        <div>
                            <span class="badge badge-warning mr-2">DRAFT</span>
                            <strong>{{ $project->prj_code }}</strong> - {{ $project->prj_title }}
                        </div>
                        <div>
                            <span class="badge badge-light text-dark mr-2">
                                Approval: {{ \Carbon\Carbon::parse($project->prj_aprvdt)->format('d M, Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Phase 1 Docs Status --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-muted font-weight-bold mb-2 small text-uppercase">Phase 1 Attachments (View)</h6>
                            <div class="d-flex gap-3">
                                @php $ppf = $project->attachments->where('jat_type', 'PPF')->first(); @endphp
                                @if($ppf)
                                <a href="{{ route('attachment.view', $ppf->jat_id) }}" target="_blank" class="badge badge-success p-2 text-white text-decoration-none">
                                    <i class="fas fa-check mr-1"></i> PPF Uploaded <i class="fas fa-eye ml-1"></i>
                                </a>
                                @else <span class="badge badge-secondary p-2">PPF Missing</span> @endif

                                @php $urd = $project->attachments->where('jat_type', 'URD')->first(); @endphp
                                @if($urd)
                                <a href="{{ route('attachment.view', $urd->jat_id) }}" target="_blank" class="badge badge-success p-2 text-white text-decoration-none">
                                    <i class="fas fa-check mr-1"></i> URD Uploaded <i class="fas fa-eye ml-1"></i>
                                </a>
                                @else <span class="badge badge-secondary p-2">URD Missing</span> @endif
                            </div>
                        </div>
                    </div>
                    <hr>

                    {{-- Execution Dates --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Project Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="prj_startdt" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Est. Completion (Target) <span class="text-danger">*</span></label>
                            <input type="date" name="prj_estenddt" class="form-control" required>
                        </div>
                    </div>

                    {{-- PHASE 2 DOCUMENTS (Split: Work Order & Approval Letter) --}}
                    <div class="row mt-2">
                        {{-- 1. Work Order --}}
                        <div class="col-md-6">
                            <div class="doc-card shadow-sm border-left-success" style="border-left-color: #28a745;">
                                <div class="d-flex align-items-center">
                                    <div class="doc-icon" style="color: #28a745; background-color: #e0fdf4;">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div>
                                        <div class="doc-title text-success">Work Order</div>
                                        <div class="doc-desc">Upload official work order</div>
                                    </div>
                                </div>
                                <div style="width: 32px; height: 32px;">
                                    <label for="file-wo" class="btn btn-outline-success rounded-circle p-0 d-flex align-items-center justify-content-center w-100 h-100" id="btn-wo" style="cursor: pointer;">
                                        <i class="fas fa-upload small"></i>
                                    </label>
                                    <input type="file" id="file-wo" name="doc_workorder" class="file-input-hidden" onchange="updateUploadUI(this, 'btn-wo')">
                                </div>
                            </div>
                        </div>

                        {{-- 2. Approval Letter --}}
                        <div class="col-md-6">
                            <div class="doc-card shadow-sm border-left-success" style="border-left-color: #28a745;">
                                <div class="d-flex align-items-center">
                                    <div class="doc-icon" style="color: #28a745; background-color: #e0fdf4;">
                                        <i class="fas fa-file-signature"></i>
                                    </div>
                                    <div>
                                        <div class="doc-title text-success">Approval Letter</div>
                                        <div class="doc-desc">Upload signed approval letter</div>
                                    </div>
                                </div>
                                <div style="width: 32px; height: 32px;">
                                    <label for="file-al" class="btn btn-outline-success rounded-circle p-0 d-flex align-items-center justify-content-center w-100 h-100" id="btn-al" style="cursor: pointer;">
                                        <i class="fas fa-upload small"></i>
                                    </label>
                                    <input type="file" id="file-al" name="doc_approval_letter" class="file-input-hidden" onchange="updateUploadUI(this, 'btn-al')">
                                </div>
                            </div>
                        </div>
                    </div>


                    <h6 class="text-primary font-weight-bold mt-4 mb-3"><i class="fas fa-list-ol mr-1"></i> Define Initial Milestones</h6>
                    <table class="table table-bordered table-custom" id="msTable">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">#</th>
                                <th>Milestone Description</th>
                                <th width="200">Target Date</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center font-weight-bold text-muted sn-cell">1</td>
                                <td><input type="text" name="milestones[0][desc]" class="form-control form-control-sm border-0 bg-light" placeholder="e.g. Requirement Analysis" required></td>
                                <td><input type="date" name="milestones[0][date]" class="form-control form-control-sm border-0 bg-light" required></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger disabled"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-secondary shadow-sm" onclick="addRow()">
                        <i class="fas fa-plus mr-1"></i> Add Another Row
                    </button>

                </div>
                <div class="card-footer text-right bg-white border-top">
                    <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-rocket mr-2"></i> Start Working on Project
                    </button>
                </div>
            </form>
            @endif

        </div>
    </div>
</div>

<script>
    let msCount = 1;

    function addRow() {
        const table = document.getElementById('msTable').getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();
        msCount++;
        newRow.innerHTML = `
            <td class="text-center font-weight-bold text-muted sn-cell">${msCount}</td>
            <td><input type="text" name="milestones[${msCount}][desc]" class="form-control form-control-sm border-0 bg-light" placeholder="Next Milestone..."></td>
            <td><input type="date" name="milestones[${msCount}][date]" class="form-control form-control-sm border-0 bg-light"></td>
            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
        `;
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        updateSerialNumbers();
    }

    function updateSerialNumbers() {
        const rows = document.querySelectorAll('#msTable tbody tr');
        rows.forEach((row, index) => {
            row.querySelector('.sn-cell').innerText = index + 1;
        });
        msCount = rows.length;
    }

    function updateUploadUI(input, btnId) {
        if (input.files && input.files[0]) {
            const labelBtn = document.getElementById(btnId);
            const parent = labelBtn.closest('.doc-card');
            labelBtn.classList.remove('btn-outline-primary', 'btn-outline-success');
            labelBtn.classList.add('btn-success');
            labelBtn.innerHTML = '<i class="fas fa-check"></i>';
            labelBtn.style.color = '#fff';
            parent.style.borderColor = '#28a745';
            parent.style.backgroundColor = '#f8fff9';
            parent.querySelector('.doc-icon').style.color = '#28a745';
            parent.querySelector('.doc-icon').style.backgroundColor = '#e0fdf4';
            parent.querySelector('.doc-desc').innerText = input.files[0].name;
            parent.querySelector('.doc-desc').style.color = '#28a745';
        }
    }
</script>
@endsection