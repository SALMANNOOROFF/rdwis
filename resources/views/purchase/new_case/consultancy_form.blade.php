@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color:var(--rd-text1); letter-spacing: -0.5px;">
                        <i class="fas fa-user-tie mr-2 text-primary"></i> {{ in_array($type, ['consultancy', 'consultation']) ? 'Consultancy Service' : 'Outsourced Services' }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('purchase.select') }}" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius:6px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Selection
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="mx-auto" style="max-width: 1300px;">
                <form id="consultancyForm" action="{{ route('purchase.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pcs_type" value="pt">
                
                <div class="row">
                    {{-- Left Column (5/12): Part 1 & 3 --}}
                    <div class="col-md-5">
                        {{-- 1. General Info & Budget Head --}}
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold text-primary" style="font-size: 0.9rem;">1. General Information</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase">Case ID</label>
                                        <input type="text" class="form-control form-control-sm bg-secondary text-white border-0 font-weight-bold" value="#{{ $nextId }}" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase">Date</label>
                                        <input type="date" name="pcs_date" class="form-control form-control-sm bg-dark border-secondary text-white" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase">Minute No.</label>
                                        <input type="number" name="pcs_minute" id="pcs_minute" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="e.g. 1" required>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase">Case Title / Subject</label>
                                        <input type="text" name="pcs_title" class="form-control form-control-sm bg-dark border-secondary text-white font-weight-bold" placeholder="e.g. Hiring of Consultant for IT Infrastructure" required>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase">Project / Budget Head</label>
                                        <select name="pcs_hed_id" id="pcs_hed_id" class="form-control form-control-sm bg-dark border-secondary text-white" required>
                                            <option value="" selected disabled>Select Budget Head...</option>
                                            @foreach($heads as $head)
                                                <option value="{{ $head->hed_id }}">{{ $head->hed_code }} - {{ $head->hed_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Regulatory References & Docs --}}
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold text-white" style="font-size: 0.9rem;">3. Legal Compliance & Reference</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                {{-- Regulatory Reference Block --}}
                                <div class="bg-dark p-2 border border-secondary mb-3" style="border-radius:8px; background: rgba(0,0,0,0.2) !important;">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-primary mr-2">PPRA Rule 3C</span>
                                        <span class="extra-small text-white font-weight-bold">CONSULTANCY SERVICES</span>
                                    </div>
                                    <p class="mb-0 text-muted" style="font-size: 0.75rem; line-height: 1.3;">
                                        "Quality-Based Selection (QBS) shall be the preferred method for complex consultancy where quality is the primary objective." 
                                        <br><span class="text-info font-italic">— Source: Punjab Procurement Rules 2014 & Federal Rules 2004.</span>
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase">Reference Documentation (PDF)</label>
                                        <div class="custom-file">
                                            <input type="file" name="attachment" class="custom-file-input" id="customFile">
                                            <label class="custom-file-label form-control-sm bg-dark border-secondary text-white" for="customFile">Choose file</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase">Internal Remarks (Optional)</label>
                                        <textarea name="remarks_JSON[internal_notes]" class="form-control form-control-sm bg-dark border-secondary text-white" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 text-right pb-3">
                                <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius:6px;">
                                    <i class="fas fa-paper-plane mr-2"></i> Initialize Case
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column (7/12): Part 2 --}}
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold text-primary" style="font-size: 0.9rem;">2. Service Details & Regulatory Choice</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                {{-- Regulatory Selection --}}
                                <div class="row mb-3 bg-dark p-3 border-left border-info mx-0" style="border-radius:4px;">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label extra-small text-uppercase text-info font-weight-bold">Selection Method (PPRA)</label>
                                        <select name="remarks_JSON[selection_method]" id="selection_method" class="form-control form-control-sm bg-dark border-info text-white font-weight-bold" required>
                                            <option value="qbs">Quality Based Selection (QBS)</option>
                                            <option value="qcbs">Quality & Cost Based (QCBS)</option>
                                            <option value="lcs">Least Cost Selection (LCS)</option>
                                            <option value="sss">Single Source Selection (SSS)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label extra-small text-uppercase text-info font-weight-bold">Expected Duration</label>
                                        <input type="text" name="remarks_JSON[duration]" class="form-control form-control-sm bg-dark border-info text-white" placeholder="e.g. 6 Months" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label extra-small text-uppercase text-info font-weight-bold">Hiring Justification</label>
                                        <textarea name="remarks_JSON[justification]" class="form-control form-control-sm bg-dark border-info text-white" rows="3" placeholder="Explain why this consultant/service is required as per the chosen method..." required></textarea>
                                    </div>
                                </div>

                                <hr class="border-secondary my-3">

                                {{-- Scope --}}
                                <div class="col-md-12 mb-3 px-0">
                                    <label class="form-label extra-small text-uppercase font-weight-bold">Detailed Scope of work</label>
                                    <textarea name="remarks_JSON[scope]" class="form-control form-control-sm bg-dark border-secondary text-white" rows="4" placeholder="Describe the comprehensive scope of services required..."></textarea>
                                </div>

                                {{-- Milestones --}}
                                <div class="section-title text-uppercase font-weight-bold text-muted extra-small mb-3">
                                    <i class="fas fa-flag-checkered mr-1"></i> Milestones & Payment Schedule
                                </div>
                                <div id="mp-list" class="mp-list">
                                    <div class="mp-row d-flex gap-3 mb-2" data-idx="0">
                                        <div class="flex-grow-1">
                                            <label class="extra-small text-muted">Milestone Name</label>
                                            <input type="text" name="remarks_JSON[milestones][0][name]" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="e.g. Technical Feasibility" required>
                                        </div>
                                        <div style="width: 150px;">
                                            <label class="extra-small text-muted">Payment (%)</label>
                                            <input type="number" name="remarks_JSON[milestones][0][payment]" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="e.g. 25" min="0" max="100">
                                        </div>
                                        <div class="pt-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-rm" style="visibility:hidden"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="btn-add-mp" class="btn btn-xs btn-outline-primary mt-2">
                                    <i class="fas fa-plus mr-1"></i> Add Another Milestone
                                </button>

                                <hr class="border-secondary my-4">

                                {{-- Hardware Inclusion --}}
                                <div class="card bg-dark border-secondary" style="border-radius:10px;">
                                    <div class="card-body py-2 px-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="chk-hw" name="remarks_JSON[include_hardware]">
                                            <label class="custom-control-label text-white small font-weight-bold" for="chk-hw">Include Hardware / Infrastructure Requirements</label>
                                        </div>
                                        <div id="hw-types" class="mt-2 pl-4" style="display:none">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="hw_net" name="remarks_JSON[hw_type][]" value="network">
                                                        <label class="custom-control-label text-muted extra-small" for="hw_net">Network Gear</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="hw_end" name="remarks_JSON[hw_type][]" value="enduser">
                                                        <label class="custom-control-label text-muted extra-small" for="hw_end">Workstations</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="hw_serv" name="remarks_JSON[hw_type][]" value="servers">
                                                        <label class="custom-control-label text-muted extra-small" for="hw_serv">Servers</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                </form>
            </div>
        </div>
    </section>
</div>

@endsection

@section('scripts')
<style>
    :root {
        --rd-surface: #1e1e2d;
        --rd-text1: #ffffff;
        --rd-text2: #a2a3b7;
    }
    .extra-small { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
    .form-control-sm { border-radius: 6px; }
    .bg-dark { background-color: rgba(0,0,0,0.2) !important; }
    .mp-row { border-bottom: 1px dashed rgba(255,255,255,0.05); padding-bottom: 10px; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; }
</style>

<script>
$(document).ready(function() {
    // Custom File Label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Hardware Toggle
    $('#chk-hw').change(function() {
        $('#hw-types').toggle(this.checked);
    });

    // Minute Number AJAX
    $('#pcs_hed_id').on('change', function() {
        const headId = $(this).val();
        if(headId) {
            $.ajax({
                url: '/get-next-minute/' + headId,
                type: "GET",
                success: function(data) {
                    $('#pcs_minute').val(data.next_minute);
                }
            });
        }
    });

    // Milestone Dynamic List
    let mpIdx = 1;
    $('#btn-add-mp').click(function() {
        const row = $(`
            <div class="mp-row d-flex gap-3 mb-2" data-idx="${mpIdx}">
                <div class="flex-grow-1">
                    <input type="text" name="remarks_JSON[milestones][${mpIdx}][name]" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Milestone Name" required>
                </div>
                <div style="width: 150px;">
                    <input type="number" name="remarks_JSON[milestones][${mpIdx}][payment]" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Payment %">
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-rm"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `);
        $('#mp-list').append(row);
        mpIdx++;
        checkRmBtn();
    });

    $(document).on('click', '.btn-rm', function() {
        $(this).closest('.mp-row').remove();
        checkRmBtn();
    });

    function checkRmBtn() {
        const rows = $('.mp-row').length;
        $('.btn-rm').css('visibility', rows > 1 ? 'visible' : 'hidden');
    }
});
</script>
@endsection

