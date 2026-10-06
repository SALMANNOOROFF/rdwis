@extends('welcome')

@section('title', 'HR Forms Configuration - RDWIS 2.0')

@section('content')
<div class="container-fluid py-4" style="max-width: 1350px;">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(95, 120, 88, 0.15); display: flex; align-items: center; justify-content: center; color: #5F7858; font-size: 1.3rem;">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h4 class="font-weight-bold mb-0 text-dark" style="letter-spacing: -0.3px;">HR Policy Forms Configuration</h4>
                    <p class="text-muted small mb-0">Dynamic configuration of salary bands, approval tiers, and hiring type mappings (RDW/HR POLICY/2026)</p>
                </div>
            </div>
        </div>
        <div>
            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 12px; border-radius: 6px;">
                <i class="fas fa-check-circle mr-1"></i> POLICY 2026 ENGINE ACTIVE
            </span>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4" id="config-tabs" role="tablist" style="gap: 8px;">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold px-4 py-2" id="tab-bands-link" data-toggle="pill" href="#tab-bands" role="tab" style="border-radius: 8px;">
                <i class="fas fa-money-bill-wave mr-1.5"></i> Salary Bands (Annex K)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold px-4 py-2" id="tab-chains-link" data-toggle="pill" href="#tab-chains" role="tab" style="border-radius: 8px;">
                <i class="fas fa-project-diagram mr-1.5"></i> Approval Chains & Authorities
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold px-4 py-2" id="tab-mappings-link" data-toggle="pill" href="#tab-mappings" role="tab" style="border-radius: 8px;">
                <i class="fas fa-exchange-alt mr-1.5"></i> Hiring Type Mappings
            </a>
        </li>
    </ul>

    <div class="tab-content" id="config-tabs-content">
        {{-- 1. SALARY BANDS TAB --}}
        <div class="tab-pane fade show active" id="tab-bands" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="font-weight-bold text-dark mb-0">Annex K Salary Bands & Authority Levels</h6>
                        <small class="text-muted">Salary bands are neutral reference guidance (PKR/month) and do not block case processing.</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
                                <th class="pl-4">Designation</th>
                                <th>Min Salary (PKR)</th>
                                <th>Max Salary (PKR)</th>
                                <th>Approval Tier Level</th>
                                <th>Policy Basis</th>
                                <th class="pr-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryBands as $band)
                            <tr data-band-id="{{ $band->id }}">
                                <td class="pl-4 font-weight-bold text-dark">{{ $band->designation }}</td>
                                <td style="width: 170px;">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">Rs.</span></div>
                                        <input type="number" class="form-control band-min" value="{{ (int)$band->min_salary }}">
                                    </div>
                                </td>
                                <td style="width: 170px;">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">Rs.</span></div>
                                        <input type="number" class="form-control band-max" value="{{ (int)$band->max_salary }}">
                                    </div>
                                </td>
                                <td style="width: 180px;">
                                    <select class="form-control form-control-sm band-level">
                                        <option value="DG_NRDI" {{ $band->approval_level === 'DG_NRDI' ? 'selected' : '' }}>DG NRDI (RO & Above)</option>
                                        <option value="MD_RDW" {{ $band->approval_level === 'MD_RDW' ? 'selected' : '' }}>MD RDW (RT & Below)</option>
                                    </select>
                                </td>
                                <td><span class="text-muted small">{{ $band->basis_note }}</span></td>
                                <td class="pr-4 text-right">
                                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold px-3 py-1" onclick="saveBand({{ $band->id }}, this)">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 2. APPROVAL CHAINS TAB --}}
        <div class="tab-pane fade" id="tab-chains" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="font-weight-bold text-dark mb-0">Multi-Step Form Routing & Approval Steps</h6>
                    <small class="text-muted">Configures the workflow steps, titles, and terminal approving authorities per form and tier.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
                                <th class="pl-4">Form Code</th>
                                <th>Tier</th>
                                <th>Step #</th>
                                <th>Role Title</th>
                                <th>Action Type</th>
                                <th>Approver Role Code</th>
                                <th class="pr-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvalChains as $step)
                            <tr data-step-id="{{ $step->id }}">
                                <td class="pl-4 font-weight-bold text-primary">{{ $step->form_code }}</td>
                                <td><span class="badge badge-light border">{{ $step->grade_level }}</span></td>
                                <td class="font-weight-bold text-center" style="width: 50px;">{{ $step->sequence }}</td>
                                <td style="width: 250px;">
                                    <input type="text" class="form-control form-control-sm step-title" value="{{ $step->role_title }}">
                                </td>
                                <td style="width: 170px;">
                                    <select class="form-control form-control-sm step-action">
                                        <option value="Recommendation" {{ $step->action_type === 'Recommendation' ? 'selected' : '' }}>Recommendation</option>
                                        <option value="Approval" {{ $step->action_type === 'Approval' ? 'selected' : '' }}>Approval</option>
                                        <option value="Verification" {{ $step->action_type === 'Verification' ? 'selected' : '' }}>Verification</option>
                                    </select>
                                </td>
                                <td style="width: 170px;">
                                    <input type="text" class="form-control form-control-sm step-role" value="{{ $step->approver_role }}">
                                </td>
                                <td class="pr-4 text-right">
                                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold px-3 py-1" onclick="saveStep({{ $step->id }}, this)">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 3. HIRING TYPE MAPPINGS TAB --}}
        <div class="tab-pane fade" id="tab-mappings" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="font-weight-bold text-dark mb-0">Case CTC Type to Hiring Type Map</h6>
                    <small class="text-muted">Maps existing raw hr.ctrcases.ctc_type codes to policy hiring categories without touching existing DB rows.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
                                <th class="pl-4">Case ctc_type Code</th>
                                <th>Mapped Policy Category</th>
                                <th>Reference / Notes</th>
                                <th>Status</th>
                                <th class="pr-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hiringTypeMaps as $map)
                            <tr data-map-id="{{ $map->id }}">
                                <td class="pl-4 font-weight-bold text-primary" style="font-size: 15px;">{{ $map->ctc_type }}</td>
                                <td style="width: 220px;">
                                    <select class="form-control form-control-sm map-type font-weight-bold">
                                        <option value="Fresh" {{ $map->hiring_type === 'Fresh' ? 'selected' : '' }}>Fresh</option>
                                        <option value="Renewal" {{ $map->hiring_type === 'Renewal' ? 'selected' : '' }}>Renewal</option>
                                        <option value="Extension" {{ $map->hiring_type === 'Extension' ? 'selected' : '' }}>Extension</option>
                                        <option value="Rehiring" {{ $map->hiring_type === 'Rehiring' ? 'selected' : '' }}>Rehiring</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm map-note" value="{{ $map->note }}">
                                </td>
                                <td style="width: 120px;">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input map-active" id="active_{{ $map->id }}" {{ $map->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label small font-weight-bold" for="active_{{ $map->id }}">Active</label>
                                    </div>
                                </td>
                                <td class="pr-4 text-right">
                                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold px-3 py-1" onclick="saveMap({{ $map->id }}, this)">
                                        <i class="fas fa-save mr-1"></i> Save
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
</div>

@push('scripts')
<script>
function saveBand(id, btn) {
    const row = $(btn).closest('tr');
    const min = row.find('.band-min').val();
    const max = row.find('.band-max').val();
    const level = row.find('.band-level').val();

    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '{{ route("hrforms.settings.update") }}',
        method: 'POST',
        data: {
            setting_type: 'salary_band',
            id: id,
            min_salary: min,
            max_salary: max,
            approval_level: level,
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            $(btn).prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Saved');
            setTimeout(() => $(btn).html('<i class="fas fa-save mr-1"></i> Save'), 1500);
            if (window.Swal) Swal.fire({toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000});
        },
        error: function(err) {
            $(btn).prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
            alert(err.responseJSON?.message || 'Error saving band');
        }
    });
}

function saveStep(id, btn) {
    const row = $(btn).closest('tr');
    const title = row.find('.step-title').val();
    const action = row.find('.step-action').val();
    const role = row.find('.step-role').val();

    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '{{ route("hrforms.settings.update") }}',
        method: 'POST',
        data: {
            setting_type: 'approval_chain',
            id: id,
            role_title: title,
            action_type: action,
            approver_role: role,
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            $(btn).prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Saved');
            setTimeout(() => $(btn).html('<i class="fas fa-save mr-1"></i> Save'), 1500);
            if (window.Swal) Swal.fire({toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000});
        },
        error: function(err) {
            $(btn).prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
            alert(err.responseJSON?.message || 'Error saving step');
        }
    });
}

function saveMap(id, btn) {
    const row = $(btn).closest('tr');
    const type = row.find('.map-type').val();
    const note = row.find('.map-note').val();
    const active = row.find('.map-active').is(':checked');

    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '{{ route("hrforms.settings.update") }}',
        method: 'POST',
        data: {
            setting_type: 'hiring_type_map',
            id: id,
            hiring_type: type,
            note: note,
            is_active: active ? 1 : 0,
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            $(btn).prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Saved');
            setTimeout(() => $(btn).html('<i class="fas fa-save mr-1"></i> Save'), 1500);
            if (window.Swal) Swal.fire({toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000});
        },
        error: function(err) {
            $(btn).prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
            alert(err.responseJSON?.message || 'Error saving mapping');
        }
    });
}
</script>
@endpush
@endsection
