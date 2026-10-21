@extends('welcome')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<style>
    .fsh-create-wrapper {
        background-color: var(--rd-bg);
        min-height: calc(100vh - 60px);
        padding: 1.75rem 2rem;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    
    .fsh-card {
        background: #FFFFFF;
        border: 1px solid var(--rd-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(41, 40, 36, 0.04);
        overflow: hidden;
    }

    .fsh-card-header {
        background: #FFFFFF;
        padding: 1.2rem 1.5rem;
        border-bottom: 1.5px solid var(--rd-neutral-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .smart-indicator-pill {
        background: var(--rd-neutral-50);
        border: 1px solid var(--rd-neutral-200);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .smart-indicator-pill:hover {
        border-color: var(--rd-neutral-300);
    }
    .indicator-val {
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: -0.3px;
    }

    .preview-box {
        background: #F8FAFC;
        border: 1.5px dashed var(--rd-neutral-300);
        border-radius: 10px;
        padding: 1.25rem;
    }
</style>

<div class="content-wrapper" style="background-color: var(--rd-bg);">
    <section class="content pt-3">
        <div class="container-fluid fsh-create-wrapper">

            {{-- Back Link & Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('fund_shifting.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Hub
                </a>
                <span class="badge badge-light border text-muted font-weight-bold px-3 py-1">New Request Draft</span>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="fsh-card">
                        <div class="fsh-card-header">
                            <div>
                                <h4 class="font-weight-800 text-dark mb-1" style="font-size: 1.25rem;">
                                    <i class="fas fa-random text-primary mr-2"></i> Initiate Fund Shifting Request
                                </h4>
                                <p class="text-muted small mb-0 font-weight-500">
                                    Select project and source/destination subheads to view real-time smart fund availability and before/after impact.
                                </p>
                            </div>
                        </div>

                        <div class="p-4">
                            @if(session('error'))
                                <div class="alert alert-danger font-weight-600 mb-4" style="border-radius: 8px;">
                                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                                </div>
                            @endif

                            <form id="fundShiftForm" method="POST" action="{{ route('fund_shifting.store') }}">
                                @csrf

                                <input type="hidden" name="hed_id" id="hidden_hed_id" value="{{ old('hed_id') }}">

                                {{-- Step 1: Select Project --}}
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                        Select Project <span class="text-danger">*</span>
                                    </label>
                                    <select name="prj_id" id="select_prj_id" class="form-control form-control-lg font-weight-600" style="border-radius: 8px;" required>
                                        <option value="">-- Choose Project --</option>
                                        @foreach($projects as $p)
                                            <option value="{{ $p->prj_id }}" data-hed-id="{{ $p->hed_id }}" data-hed-name="{{ $p->hed_name }}" {{ old('prj_id') == $p->prj_id ? 'selected' : '' }}>
                                                [{{ $p->prj_code }}] {{ $p->prj_title }} (Head Code: {{ $p->hed_code ?: $p->prj_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Only active projects with configured financial allocation heads are selectable.</small>
                                </div>

                                {{-- Loading Spinner --}}
                                <div id="subheadsLoading" class="text-center py-4 d-none">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                                    <div class="font-weight-600 text-muted">Fetching real-time subhead breakdown & available funds...</div>
                                </div>

                                {{-- Step 2: Source & Destination Subheads Container --}}
                                <div id="subheadsContainer" class="{{ old('prj_id') ? '' : 'd-none' }}">
                                    <div class="row">
                                        {{-- Source Subhead --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold text-danger" style="font-size: 0.9rem;">
                                                    <i class="fas fa-minus-circle mr-1"></i> Shift FROM Subhead (Source) <span class="text-danger">*</span>
                                                </label>
                                                <select name="from_sbh_id" id="select_from_sbh" class="form-control font-weight-600" style="border-radius: 8px;" required>
                                                    <option value="">-- Select Source Subhead --</option>
                                                </select>
                                            </div>

                                            {{-- Source Smart Indicators --}}
                                            <div id="fromIndicators" class="d-none mt-2">
                                                <div class="smart-indicator-pill border-danger" style="background: #FEF2F2;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted small font-weight-700">CURRENT ALLOCATION</span>
                                                        <span class="indicator-val text-dark" id="from_alloc_val">Rs. 0</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted small font-weight-600">Committed / In Process</span>
                                                        <span class="text-muted font-weight-600" id="from_cmt_val">Rs. 0</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center border-top pt-1 mt-1">
                                                        <span class="text-success small font-weight-bold">
                                                            <i class="fas fa-check-circle mr-1"></i> MAX AVAILABLE TO SHIFT
                                                        </span>
                                                        <span class="indicator-val text-success" id="from_avail_val">Rs. 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Destination Subhead --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold text-success" style="font-size: 0.9rem;">
                                                    <i class="fas fa-plus-circle mr-1"></i> Shift TO Subhead (Destination) <span class="text-danger">*</span>
                                                </label>
                                                <select name="to_sbh_id" id="select_to_sbh" class="form-control font-weight-600" style="border-radius: 8px;" required>
                                                    <option value="">-- Select Destination Subhead --</option>
                                                </select>
                                            </div>

                                            {{-- Destination Smart Indicators --}}
                                            <div id="toIndicators" class="d-none mt-2">
                                                <div class="smart-indicator-pill border-success" style="background: #F0FDF4;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted small font-weight-700">CURRENT ALLOCATION</span>
                                                        <span class="indicator-val text-dark" id="to_alloc_val">Rs. 0</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted small font-weight-600">Committed / In Process</span>
                                                        <span class="text-muted font-weight-600" id="to_cmt_val">Rs. 0</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center border-top pt-1 mt-1">
                                                        <span class="text-muted small font-weight-bold">Remaining Balance</span>
                                                        <span class="indicator-val text-dark" id="to_rem_val">Rs. 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 3: Shifting Amount Input --}}
                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                            Amount to Shift (PKR) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold bg-light">Rs.</span>
                                            </div>
                                            <input type="number" step="0.01" min="1" name="proposed_amount" id="input_proposed_amount" class="form-control font-weight-bold text-dark" placeholder="Enter shifting amount" value="{{ old('proposed_amount') }}" required style="border-radius: 0 8px 8px 0; font-size: 1.25rem;">
                                        </div>
                                        <div id="amountAlert" class="alert alert-danger font-weight-600 p-2 mt-2 d-none" style="font-size: 12px; border-radius: 6px;">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Amount exceeds maximum available shiftable funds (<span id="alertMaxSpan">Rs. 0</span>).
                                        </div>
                                    </div>

                                    {{-- Step 4: Live Before & After Preview Box --}}
                                    <div id="livePreviewContainer" class="preview-box mb-4 d-none">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="font-weight-800 text-dark" style="font-size: 0.95rem;">
                                                <i class="fas fa-balance-scale text-primary mr-1"></i> Live Allocation Impact (Before vs After Approval)
                                            </span>
                                            <span class="badge badge-success px-2 py-1 font-weight-bold">Net Budget Preserved</span>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered bg-white mb-0" style="font-size: 0.88rem; border-radius: 8px; overflow: hidden;">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Subhead</th>
                                                        <th class="text-right">Current Allocation</th>
                                                        <th class="text-center">Shift Impact</th>
                                                        <th class="text-right">Projected After Approval</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="font-weight-700 text-danger">
                                                            <i class="fas fa-minus-circle mr-1"></i> <span id="prev_from_name">Source Subhead</span>
                                                        </td>
                                                        <td class="text-right font-weight-600" id="prev_from_before">Rs. 0</td>
                                                        <td class="text-center text-danger font-weight-bold" id="prev_from_shift">-Rs. 0</td>
                                                        <td class="text-right font-weight-800 text-dark" id="prev_from_after">Rs. 0</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-700 text-success">
                                                            <i class="fas fa-plus-circle mr-1"></i> <span id="prev_to_name">Destination Subhead</span>
                                                        </td>
                                                        <td class="text-right font-weight-600" id="prev_to_before">Rs. 0</td>
                                                        <td class="text-center text-success font-weight-bold" id="prev_to_shift">+Rs. 0</td>
                                                        <td class="text-right font-weight-800 text-dark" id="prev_to_after">Rs. 0</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Step 5: Purpose / Justification --}}
                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                            Purpose / Justification for Fund Shifting <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="purpose" class="form-control" rows="3" placeholder="Provide justification for shifting funds between subheads..." required style="border-radius: 8px;">{{ old('purpose') }}</textarea>
                                    </div>

                                    {{-- Submit Buttons --}}
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                        <a href="{{ route('fund_shifting.index') }}" class="btn btn-light font-weight-bold px-4" style="border-radius: 8px;">
                                            Cancel
                                        </a>
                                        <button type="submit" id="btnSubmitForm" class="btn btn-primary font-weight-bold px-4 py-2.5 shadow-sm" style="border-radius: 8px;">
                                            <i class="fas fa-save mr-1"></i> Save as Draft & Create Case File
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let subheadsData = [];
    let currentFromMeta = null;
    let currentToMeta = null;

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ── Project Selection ─────────────────────────────────────
    $('#select_prj_id').on('change', function() {
        const prjId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const hedId = selectedOption.data('hed-id');

        $('#hidden_hed_id').val(hedId);
        $('#fromIndicators').addClass('d-none');
        $('#toIndicators').addClass('d-none');
        $('#livePreviewContainer').addClass('d-none');
        $('#input_proposed_amount').val('');
        currentFromMeta = null;
        currentToMeta = null;

        if (!hedId) {
            $('#subheadsContainer').addClass('d-none');
            return;
        }

        $('#subheadsLoading').removeClass('d-none');
        $('#subheadsContainer').addClass('d-none');

        $.ajax({
            url: "{{ url('/fund-shifting/subheads') }}/" + hedId,
            method: 'GET',
            success: function(res) {
                $('#subheadsLoading').addClass('d-none');
                if (res.success && res.subheads) {
                    subheadsData = res.subheads;
                    populateSubheadDropdowns();
                    $('#subheadsContainer').removeClass('d-none');
                } else {
                    Swal.fire('Error', res.message || 'Failed to fetch subheads.', 'error');
                }
            },
            error: function(err) {
                $('#subheadsLoading').addClass('d-none');
                Swal.fire('Error', 'Unable to retrieve project subheads.', 'error');
            }
        });
    });

    function populateSubheadDropdowns() {
        let fromOptions = '<option value="">-- Select Source Subhead --</option>';
        let toOptions = '<option value="">-- Select Destination Subhead --</option>';

        subheadsData.forEach(function(sh) {
            fromOptions += `<option value="${sh.sbh_id}" data-name="${sh.name}">${sh.name} (Available: Rs. ${formatNumber(sh.max_shiftable)})</option>`;
            toOptions += `<option value="${sh.sbh_id}" data-name="${sh.name}">${sh.name} (Current: Rs. ${formatNumber(sh.allocation)})</option>`;
        });

        $('#select_from_sbh').html(fromOptions);
        $('#select_to_sbh').html(toOptions);
    }

    // ── Source Subhead Selection ──────────────────────────────
    $('#select_from_sbh').on('change', function() {
        const sbhId = parseInt($(this).val());
        currentFromMeta = subheadsData.find(s => s.sbh_id === sbhId) || null;

        if (currentFromMeta) {
            $('#from_alloc_val').text('Rs. ' + formatNumber(currentFromMeta.allocation));
            $('#from_cmt_val').text('Rs. ' + formatNumber(currentFromMeta.commitments + currentFromMeta.in_process));
            $('#from_avail_val').text('Rs. ' + formatNumber(currentFromMeta.max_shiftable));
            $('#fromIndicators').removeClass('d-none');
        } else {
            $('#fromIndicators').addClass('d-none');
        }

        updateLivePreview();
    });

    // ── Destination Subhead Selection ─────────────────────────
    $('#select_to_sbh').on('change', function() {
        const sbhId = parseInt($(this).val());
        currentToMeta = subheadsData.find(s => s.sbh_id === sbhId) || null;

        if (currentToMeta) {
            $('#to_alloc_val').text('Rs. ' + formatNumber(currentToMeta.allocation));
            $('#to_cmt_val').text('Rs. ' + formatNumber(currentToMeta.commitments + currentToMeta.in_process));
            $('#to_rem_val').text('Rs. ' + formatNumber(currentToMeta.remaining));
            $('#toIndicators').removeClass('d-none');
        } else {
            $('#toIndicators').addClass('d-none');
        }

        updateLivePreview();
    });

    // ── Amount Input ──────────────────────────────────────────
    $('#input_proposed_amount').on('input', function() {
        updateLivePreview();
    });

    // ── Update Live Before & After Preview ────────────────────
    function updateLivePreview() {
        const amount = parseFloat($('#input_proposed_amount').val()) || 0;

        if (!currentFromMeta || !currentToMeta || amount <= 0) {
            $('#livePreviewContainer').addClass('d-none');
            $('#amountAlert').addClass('d-none');
            return;
        }

        if (currentFromMeta.sbh_id === currentToMeta.sbh_id) {
            Swal.fire('Invalid Selection', 'Source and Destination subheads cannot be identical.', 'warning');
            $('#select_to_sbh').val('');
            currentToMeta = null;
            $('#toIndicators').addClass('d-none');
            $('#livePreviewContainer').addClass('d-none');
            return;
        }

        const maxAvail = currentFromMeta.max_shiftable || 0;
        if (amount > maxAvail) {
            $('#alertMaxSpan').text('Rs. ' + formatNumber(maxAvail));
            $('#amountAlert').removeClass('d-none');
            $('#btnSubmitForm').prop('disabled', true);
        } else {
            $('#amountAlert').addClass('d-none');
            $('#btnSubmitForm').prop('disabled', false);
        }

        // Calculate After
        const fromBefore = currentFromMeta.allocation || 0;
        const fromAfter = fromBefore - amount;

        const toBefore = currentToMeta.allocation || 0;
        const toAfter = toBefore + amount;

        $('#prev_from_name').text(currentFromMeta.name);
        $('#prev_from_before').text('Rs. ' + formatNumber(fromBefore));
        $('#prev_from_shift').text('-Rs. ' + formatNumber(amount));
        $('#prev_from_after').text('Rs. ' + formatNumber(fromAfter));

        $('#prev_to_name').text(currentToMeta.name);
        $('#prev_to_before').text('Rs. ' + formatNumber(toBefore));
        $('#prev_to_shift').text('+Rs. ' + formatNumber(amount));
        $('#prev_to_after').text('Rs. ' + formatNumber(toAfter));

        $('#livePreviewContainer').removeClass('d-none');
    }

    // Trigger change if prj was old selected
    if ($('#select_prj_id').val()) {
        $('#select_prj_id').trigger('change');
    }
});
</script>
@endpush

@endsection
