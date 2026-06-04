@extends('welcome')

@section('content')
<div class="content-wrapper" style="background:#f7f8fc; min-height:100vh;">

    {{-- Page Header --}}
    <div class="content-header" style="background:#fff; border-bottom:1px solid rgba(10,22,40,0.1); padding:0;">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="padding:14px 24px; gap: 15px;">
            <div>
                <h1 class="m-0 font-weight-bold" style="font-size:20px; color:#0a1628; font-family: 'Rajdhani', sans-serif;">Create New Account</h1>
                <small style="font-family:monospace; font-size:11px; color:#8a9ab5; letter-spacing:.05em;">
                    Admin / <span style="color:#c9a84c;">cen.accounts</span> / create
                </small>
            </div>
            <a href="{{ route('admin.accounts.index') }}" class="btn btn-sm"
               style="border:1px solid rgba(10,22,40,0.2); color:#4a5568; border-radius:8px; padding:6px 16px; font-size:13px;">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid" style="padding:18px;">

            @if ($errors->any())
                <div class="alert mb-4" style="background:#fef2f2; border:1px solid #fca5a5; border-left:3px solid #dc2626; border-radius:10px; color:#991b1b; font-size:13px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 overflow-hidden" style="border-radius:16px; box-shadow:0 4px 20px rgba(10,22,40,0.1);">

                {{-- Card Header --}}
                <div class="card-header d-flex align-items-center py-3 px-4"
                     style="background:#0a1628; border-bottom:2px solid #c9a84c;">
                    <div class="mr-3" style="width:34px; height:34px; background:rgba(201,168,76,0.15); border:1px solid rgba(201,168,76,0.3); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-user-plus" style="color:#c9a84c; font-size:13px;"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-white" style="font-size:14px;">New Account Registration</div>
                        <small style="font-family:monospace; color:rgba(201,168,76,0.7); font-size:11px; letter-spacing:.05em;">cen.accounts — system admin</small>
                    </div>
                </div>

                <form id="create-account-form" method="POST" action="{{ route('admin.accounts.store') }}">
                    @csrf

                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="unit_group" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                        Unit Type <span style="color:#c9a84c;">*</span>
                                    </label>
                                    <select name="unit_group" id="unit_group" class="form-control" required
                                            style="height:36px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px; color:#0a1628;">
                                        <option value="">Select type...</option>
                                        <option value="division" {{ old('unit_group') === 'division' ? 'selected' : '' }}>Division Units</option>
                                        <option value="support" {{ old('unit_group') === 'support' ? 'selected' : '' }}>Supporting Units</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="unit_id" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                        Unit <span style="color:#c9a84c;">*</span>
                                    </label>
                                    <select name="unit_id" id="unit_id" class="form-control" required
                                            style="height:36px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px; color:#0a1628;">
                                        <option value="">Select type first...</option>
                                    </select>
                                    <small class="text-muted" id="unit_help" style="font-size:11px;"></small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="role_desig" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                        Role <span style="color:#c9a84c;">*</span>
                                    </label>
                                    <select name="role_desig" id="role_desig" class="form-control" required
                                            style="height:36px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px; color:#0a1628;">
                                        <option value="">Select unit first...</option>
                                    </select>
                                    <small class="text-muted" id="role_help" style="font-size:11px;"></small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                        Authorization <span style="color:#c9a84c;">*</span>
                                    </label>
                                    <div class="d-flex" style="gap:8px; flex-wrap:wrap;">
                                        @foreach(['viewer' => '#6b7280', 'editor' => '#2563eb', 'approver' => '#16a34a'] as $lvl => $color)
                                        <div>
                                            <input type="radio" name="auth_level" id="auth_{{ $lvl }}" value="{{ $lvl }}"
                                                   class="d-none auth-radio"
                                                   {{ old('auth_level', 'viewer') === $lvl ? 'checked' : '' }}>
                                            <label for="auth_{{ $lvl }}" class="auth-radio-label mb-0"
                                                   data-color="{{ $color }}"
                                                   style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border:1px solid rgba(10,22,40,0.2); border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; color:#4a5568; background:#fff; transition:all .15s; user-select:none;">
                                                <span style="width:7px; height:7px; border-radius:50%; background:{{ $color }}; flex-shrink:0; display:inline-block;"></span>
                                                {{ ucfirst($lvl) }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mt-1" id="auth_help" style="font-size:11px;"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="username" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                        Username <span style="color:#c9a84c;">*</span>
                                    </label>
                                    <input type="text" name="username" id="username"
                                           class="form-control" value="{{ old('username') }}" required
                                           placeholder="e.g. cdr_khan"
                                           style="height:34px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">
                                        Full Name <span style="color:#c9a84c;">*</span>
                                    </label>
                                    <input type="text" name="name" id="name"
                                           class="form-control" value="{{ old('name') }}" required
                                           placeholder="e.g. Muhammad Ali Khan"
                                           style="height:34px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rank" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Rank</label>
                                    <input type="text" name="rank" id="rank"
                                           class="form-control" value="{{ old('rank') }}"
                                           placeholder="e.g. Cdr, Capt"
                                           style="height:34px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#4a5568;">Title</label>
                                    <input type="text" name="title" id="title"
                                           class="form-control" value="{{ old('title') }}"
                                           placeholder="e.g. Dr, Engr"
                                           style="height:34px; border:1px solid rgba(10,22,40,0.2); border-radius:10px; font-size:13px;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:-8px;">
                            <span style="font-size:11px; color:#7a5a10; background:#fef9ec; border:1px solid #e8c97a; border-radius:10px; padding:6px 10px; display:inline-block;">
                                Default password: <strong style="font-family:monospace;">12345</strong> (force change on first login)
                            </span>
                        </div>

                    </div>{{-- end card-body --}}

                    {{-- Card Footer --}}
                    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2"
                         style="background:#f7f8fc; border-top:1px solid rgba(10,22,40,0.08); gap: 10px;">
                        <small style="color:#8a9ab5; font-size:12px;">
                            Fields marked <strong style="color:#c9a84c;">*</strong> are required.
                        </small>
                        <button type="submit" class="btn px-4"
                                style="background:#0a1628; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:600; height:36px; border-bottom:2px solid #c9a84c; transition:all .18s;">
                            <i class="fas fa-save mr-1" style="color:#c9a84c;"></i> Create Account
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
        const allUnits = @json($unitsJson);

        let lastRoles = [];

        function normalize(s) {
            return String(s || '').trim().toLowerCase();
        }

        // ─── Auth radio styling ───────────────────────────────────────────
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
                        'border-color': 'rgba(10,22,40,0.2)',
                        'color': '#4a5568'
                    });
                }
            });
        }

        $('.auth-radio').on('change', function () {
            refreshAuthLabels();
        });

        refreshAuthLabels();

        // ─── Unit population ──────────────────────────────────────────────
        function populateUnits() {
            const group = normalize($('#unit_group').val());
            const unitSelect = $('#unit_id');
            const unitHelp  = $('#unit_help');
            const roleSelect = $('#role_desig');
            const roleHelp  = $('#role_help');

            unitSelect.empty();
            roleSelect.empty();
            roleHelp.text('');
            $('#auth_help').text('');

            if (!group) {
                unitSelect.append('<option value="">Select type first...</option>');
                roleSelect.append('<option value="">Select unit first...</option>');
                unitHelp.text('');
                return;
            }

            const filtered = allUnits.filter(function (u) {
                const t = normalize(u.type);
                if (group === 'division') {
                    return t === 'division';
                }
                return t !== 'division';
            });

            unitSelect.append('<option value="">Select unit...</option>');
            filtered.forEach(function (u) {
                const label = u.id + ' — ' + u.name + (u.area ? (' (' + u.area + ')') : '');
                unitSelect.append($('<option>', { value: u.id, text: label }));
            });

            unitHelp.text(
                group === 'division'
                    ? filtered.length + ' division unit(s) loaded.'
                    : filtered.length + ' supporting unit(s) loaded.'
            );

            const oldUnitId = "{{ (string) old('unit_id', '') }}";
            if (oldUnitId) {
                unitSelect.val(oldUnitId).trigger('change');
            }
        }

        // ─── Role population ──────────────────────────────────────────────
        function populateRoles(unitId) {
            const roleSelect = $('#role_desig');
            const roleHelp  = $('#role_help');
            roleSelect.empty().append('<option value="">Loading roles...</option>');
            roleHelp.text('');
            lastRoles = [];

            if (!unitId) {
                roleSelect.empty().append('<option value="">Select unit first...</option>');
                return;
            }

            $.getJSON("{{ route('admin.accounts.roles') }}", { unit_id: unitId })
                .done(function (data) {
                    lastRoles = Array.isArray(data) ? data : [];
                    roleSelect.empty();

                    if (!lastRoles.length) {
                        roleSelect.append('<option value="">No roles configured for this unit</option>');
                        return;
                    }

                    roleSelect.append('<option value="">Select role...</option>');
                    lastRoles.forEach(function (role) {
                        const label = role.rol_level + ' — ' + role.rol_desig +
                                      ' (' + role.rol_desigshort + ', ' + role.rol_access +
                                      ', default ' + role.rol_auth + ')';
                        roleSelect.append($('<option>', { value: role.rol_desig, text: label }));
                    });

                    roleHelp.text('Select a role template. Authorization can be overridden below.');

                    const oldRole = @json((string) old('role_desig', ''));
                    if (oldRole) {
                        roleSelect.val(oldRole).trigger('change');
                    }
                })
                .fail(function () {
                    roleSelect.empty().append('<option value="">Error loading roles</option>');
                });
        }

        // ─── Sync auth from selected role ─────────────────────────────────
        function syncAuthFromRole() {
            const roleDesig = $('#role_desig').val();
            const authHelp  = $('#auth_help');

            authHelp.text('');
            if (!roleDesig) return;

            const role = lastRoles.find(function (r) {
                return String(r.rol_desig) === String(roleDesig);
            });
            if (!role) return;

            const suggested = normalize(role.rol_auth);
            if (['viewer', 'editor', 'approver'].includes(suggested)) {
                if (!@json((string) old('auth_level', ''))) {
                    $('#auth_' + suggested).prop('checked', true);
                    refreshAuthLabels();
                }
                authHelp.text('Suggested by role template: ' + suggested + '. You can override if needed.');
            } else {
                authHelp.text('Role template has no standard auth value. Select viewer / editor / approver manually.');
            }
        }

        // ─── Event bindings ───────────────────────────────────────────────
        $('#unit_group').on('change', function () { populateUnits(); });
        $('#unit_id').on('change', function () { populateRoles($(this).val()); });
        $('#role_desig').on('change', function () { syncAuthFromRole(); });

        // ─── Re-populate on page load (after validation failure) ──────────
        const oldGroup = "{{ (string) old('unit_group', '') }}";
        if (oldGroup) {
            $('#unit_group').val(oldGroup);
        }
        populateUnits();
    });
</script>
@endpush
