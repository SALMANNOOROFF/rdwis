@extends('welcome')

@section('content')
<div class="content-wrapper" style="background:#f7f8fc; min-height:100vh;">

    {{-- Page Header --}}
    <div class="content-header" style="background:#fff; border-bottom:1px solid rgba(10,22,40,0.1); padding:0;">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="padding:14px 24px; gap: 15px;">
            <div>
                <h1 class="m-0 font-weight-bold" style="font-size:20px; color:#0a1628; font-family: 'Rajdhani', sans-serif;">
                    Edit Account: <span style="color:var(--rd-primary-600, #5F7858);">{{ $account->acc_username }}</span>
                </h1>
                <small style="font-family:monospace; font-size:11px; color:#8a9ab5; letter-spacing:.05em;">
                    Admin / <span style="color:#c9a84c;">cen.accounts</span> / edit / #{{ $account->acc_id }}
                </small>
            </div>
            <a href="{{ route('admin.accounts.index') }}" class="btn btn-sm"
               style="border:1px solid rgba(10,22,40,0.2); color:#4a5568; border-radius:8px; padding:6px 16px; font-size:13px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to Accounts
            </a>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid" style="padding:18px;">

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4 font-weight-bold" style="border-radius:10px; font-size:13.5px;">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert mb-4" style="background:#fef2f2; border:1px solid #fca5a5; border-left:3px solid #dc2626; border-radius:10px; color:#991b1b; font-size:13px;">
                    <ul class="mb-0 font-weight-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 overflow-hidden mb-4" style="border-radius:16px; box-shadow:0 4px 20px rgba(10,22,40,0.08);">

                {{-- Card Header --}}
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4"
                     style="background: var(--rd-surface, #1e293b); border-bottom: 2px solid var(--rd-primary-600, #5F7858);">
                    <div class="d-flex align-items-center">
                        <div class="mr-3" style="width:36px; height:36px; background:rgba(201,168,76,0.15); border:1px solid rgba(201,168,76,0.3); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas fa-user-edit" style="color:#c9a84c; font-size:14px;"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold text-white" style="font-size:14.5px;">
                                Account Settings &amp; Permissions
                            </div>
                            <small style="font-family:monospace; color:rgba(201,168,76,0.85); font-size:11px; letter-spacing:.05em;">
                                Account ID: #{{ $account->acc_id }} &bull; Username: {{ $account->acc_username }}
                            </small>
                        </div>
                    </div>
                    <div>
                        <span class="badge {{ $account->acc_status === 'Active' ? 'badge-success' : 'badge-danger' }} px-3 py-1 font-weight-bold" style="font-size:12px;">
                            {{ $account->acc_status }}
                        </span>
                    </div>
                </div>

                <form id="edit-account-form" method="POST" action="{{ route('admin.accounts.update', $account->acc_id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-4">

                        {{-- Section 1: User Identity --}}
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-uppercase rajdhani" style="color:var(--rd-primary-600, #5F7858); letter-spacing:1px; font-size:12.5px; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
                                <i class="fas fa-id-badge mr-1"></i> 1. Personal &amp; Account Identity
                            </h6>
                            <div class="row" style="row-gap: 12px;">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="username" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Username <span style="color:#dc2626;">*</span>
                                        </label>
                                        <input type="text" name="username" id="username" class="form-control"
                                               value="{{ old('username', $account->acc_username) }}" required
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; font-weight:600;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="name" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Full Name <span style="color:#dc2626;">*</span>
                                        </label>
                                        <input type="text" name="name" id="name" class="form-control"
                                               value="{{ old('name', $account->acc_name) }}" required
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; font-weight:600;">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="rank" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Rank</label>
                                        <input type="text" name="rank" id="rank" class="form-control"
                                               value="{{ old('rank', $account->acc_rank) }}" placeholder="e.g. Cdr, Capt"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        <label for="title" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Title</label>
                                        <input type="text" name="title" id="title" class="form-control"
                                               value="{{ old('title', $account->acc_title) }}" placeholder="Dr, Engr"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="acc_status" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Status <span style="color:#dc2626;">*</span>
                                        </label>
                                        <select name="acc_status" id="acc_status" class="form-control" required
                                                style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-weight:600;">
                                            <option value="Active" {{ old('acc_status', $account->acc_status) === 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Closed" {{ old('acc_status', $account->acc_status) === 'Closed' ? 'selected' : '' }}>Closed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Unit & Designation --}}
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-uppercase rajdhani" style="color:var(--rd-primary-600, #5F7858); letter-spacing:1px; font-size:12.5px; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
                                <i class="fas fa-building mr-1"></i> 2. Unit, Designation &amp; Organization
                            </h6>
                            <div class="row" style="row-gap: 12px;">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="unit_id" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Assigned Unit <span style="color:#dc2626;">*</span>
                                        </label>
                                        <select name="unit_id" id="unit_id" class="form-control" required
                                                style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                            @foreach($units as $u)
                                                <option value="{{ $u->unt_id }}" {{ (int)old('unit_id', $account->acc_unt_id) === (int)$u->unt_id ? 'selected' : '' }}>
                                                    {{ $u->unt_name }} ({{ $u->unt_namesh ?: 'Unit #'.$u->unt_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="acc_desig" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Designation / Role Title <span style="color:#dc2626;">*</span>
                                        </label>
                                        <input type="text" name="acc_desig" id="acc_desig" class="form-control"
                                               value="{{ old('acc_desig', $account->acc_desig) }}" required
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-weight:600;">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="acc_desigshort" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Short Designation</label>
                                        <input type="text" name="acc_desigshort" id="acc_desigshort" class="form-control"
                                               value="{{ old('acc_desigshort', $account->acc_desigshort) }}" placeholder="e.g. DDG, DLOG, SO"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="acc_desigtype" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Designation Type</label>
                                        <input type="text" name="acc_desigtype" id="acc_desigtype" class="form-control"
                                               value="{{ old('acc_desigtype', $account->acc_desigtype ?: 'specific') }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Authorization, Area & Access Scope --}}
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-uppercase rajdhani" style="color:var(--rd-primary-600, #5F7858); letter-spacing:1px; font-size:12.5px; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
                                <i class="fas fa-shield-alt mr-1"></i> 3. Authorization, Area &amp; Workflow Access
                            </h6>
                            <div class="row" style="row-gap: 12px;">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Authorization Level <span style="color:#dc2626;">*</span>
                                        </label>
                                        <div class="d-flex" style="gap:8px; flex-wrap:wrap; margin-top:4px;">
                                            @foreach(['viewer' => '#6b7280', 'editor' => '#2563eb', 'approver' => '#16a34a'] as $lvl => $color)
                                            <div>
                                                <input type="radio" name="auth_level" id="auth_{{ $lvl }}" value="{{ $lvl }}"
                                                       class="d-none auth-radio"
                                                       {{ strtolower(trim((string)old('auth_level', $account->acc_auth))) === $lvl ? 'checked' : '' }}>
                                                <label for="auth_{{ $lvl }}" class="auth-radio-label mb-0"
                                                       data-color="{{ $color }}"
                                                       style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; background:#fff; transition:all .15s; user-select:none;">
                                                    <span style="width:8px; height:8px; border-radius:50%; background:{{ $color }}; flex-shrink:0; display:inline-block;"></span>
                                                    {{ ucfirst($lvl) }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="acc_untarea" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                            Unit Area (Workflow Key) <span style="color:#dc2626;">*</span>
                                        </label>
                                        <input type="text" name="acc_untarea" id="acc_untarea" class="form-control"
                                               value="{{ old('acc_untarea', $account->acc_untarea) }}" required
                                               placeholder="e.g. hqs, prj, rdwprj, fin, hr, it, proc, log, nrdi"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-weight:600;">
                                        <small class="text-muted" style="font-size:10.5px;">Governs area middleware routing &amp; permissions</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="acc_access" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Access Scope</label>
                                        <select name="acc_access" id="acc_access" class="form-control"
                                                style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                            <option value="Single" {{ old('acc_access', $account->acc_access) === 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Multiple" {{ old('acc_access', $account->acc_access) === 'Multiple' ? 'selected' : '' }}>Multiple</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="acc_level" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Level</label>
                                        <input type="number" name="acc_level" id="acc_level" class="form-control"
                                               value="{{ old('acc_level', $account->acc_level) }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label for="acc_reportlevel" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Report Level</label>
                                        <input type="number" name="acc_reportlevel" id="acc_reportlevel" class="form-control"
                                               value="{{ old('acc_reportlevel', $account->acc_reportlevel) }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Approval / Bounds Limits --}}
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-uppercase rajdhani" style="color:var(--rd-primary-600, #5F7858); letter-spacing:1px; font-size:12.5px; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
                                <i class="fas fa-layer-group mr-1"></i> 4. Scope &amp; Unit Bounds Limits
                            </h6>
                            <div class="row" style="row-gap: 12px;">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="acc_lowers" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Lower Bounds (Single)</label>
                                        <input type="number" name="acc_lowers" id="acc_lowers" class="form-control font-monospace"
                                               value="{{ old('acc_lowers', $account->acc_lowers) }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="acc_uppers" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Upper Bounds (Single)</label>
                                        <input type="number" name="acc_uppers" id="acc_uppers" class="form-control font-monospace"
                                               value="{{ old('acc_uppers', $account->acc_uppers) }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="acc_lowerm" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Lower Bounds (Multiple)</label>
                                        <input type="number" name="acc_lowerm" id="acc_lowerm" class="form-control font-monospace"
                                               value="{{ old('acc_lowerm', $account->acc_lowerm) }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="acc_upperm" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Upper Bounds (Multiple)</label>
                                        <input type="number" name="acc_upperm" id="acc_upperm" class="form-control font-monospace"
                                               value="{{ old('acc_upperm', $account->acc_upperm) }}"
                                               style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: Password Management (Optional Override) --}}
                        <div class="p-3 rounded" style="background:#fffbeb; border:1px solid #fde68a;">
                            <h6 class="font-weight-bold text-uppercase rajdhani text-warning mb-2" style="font-size:12.5px; color:#b45309 !important;">
                                <i class="fas fa-key mr-1"></i> 5. Set New Password (Optional)
                            </h6>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <input type="password" name="password" id="password" class="form-control"
                                           placeholder="Leave blank to keep current password unchanged" autocomplete="new-password"
                                           style="height:38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">
                                        Only fill this field if you want to explicitly overwrite the user's password with a custom one. Otherwise leave it completely empty.
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end card-body --}}

                    {{-- Card Footer --}}
                    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap px-4 py-3"
                         style="background:#f8fafc; border-top:1px solid #e2e8f0; gap: 10px;">
                        <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary font-weight-bold px-3 py-1.5" style="border-radius:8px;">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm"
                                style="border-radius:8px; font-size:13.5px;">
                            <i class="fas fa-save mr-1.5"></i> Update Account
                        </button>
                    </div>

                </form>
            </div>{{-- end card --}}
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        function refreshAuthLabels() {
            $('.auth-radio').each(function () {
                const label = $('label[for="' + $(this).attr('id') + '"]');
                if ($(this).is(':checked')) {
                    const color = label.data('color');
                    label.css({
                        'background': color,
                        'border-color': color,
                        'color': '#fff'
                    });
                } else {
                    label.css({
                        'background': '#fff',
                        'border-color': '#cbd5e1',
                        'color': '#4a5568'
                    });
                }
            });
        }

        $('.auth-radio').on('change', function () {
            refreshAuthLabels();
        });

        refreshAuthLabels();
    });
</script>
@endpush
