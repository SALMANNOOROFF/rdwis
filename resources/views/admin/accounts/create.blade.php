@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0 font-weight-bold text-dark">Create New Account</h1>
            <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Accounts
            </a>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">cen.accounts — System Admin Create</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.accounts.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_id">Unit</label>
                                    <select name="unit_id" id="unit_id" class="form-control" required>
                                        <option value="">Select unit...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->unt_id }}" {{ old('unit_id') == $unit->unt_id ? 'selected' : '' }}>
                                                {{ $unit->unt_id }} — {{ $unit->unt_name }} ({{ $unit->unt_area }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_level">Level / Role</label>
                                    <select name="role_level" id="role_level" class="form-control" required>
                                        <option value="">Select unit first...</option>
                                    </select>
                                    <small class="form-text text-muted" id="role_help"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username"
                                           class="form-control" value="{{ old('username') }}" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="alert alert-warning mb-0">
                                    Default password will be set to <strong>{{ config('auth.default_password') }}</strong>.
                                    User will be forced to change it on first login.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" name="name" id="name"
                                           class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rank">Rank</label>
                                    <input type="text" name="rank" id="rank"
                                           class="form-control" value="{{ old('rank') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Title (Dr, etc.)</label>
                                    <input type="text" name="title" id="title"
                                           class="form-control" value="{{ old('title') }}">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Create Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#unit_id').on('change', function () {
            const unitId = $(this).val();
            const roleSelect = $('#role_level');
            const roleHelp = $('#role_help');
            roleSelect.empty();
            roleSelect.append('<option value="">Loading roles...</option>');
            roleHelp.text('');

            if (!unitId) {
                roleSelect.empty().append('<option value="">Select unit first...</option>');
                return;
            }

            $.getJSON("{{ route('admin.accounts.roles') }}", { unit_id: unitId })
                .done(function (data) {
                    roleSelect.empty();
                    if (!data.length) {
                        roleSelect.append('<option value="">No roles configured for this unit</option>');
                        return;
                    }
                    roleSelect.append('<option value="">Select role...</option>');
                    data.forEach(function (role) {
                        const label = role.rol_level + ' — ' + role.rol_desig + ' (' + role.rol_desigshort + ', ' + role.rol_access + ', ' + role.rol_authprj + ')';
                        roleSelect.append(
                            $('<option>', {
                                value: role.rol_level,
                                text: label
                            })
                        );
                    });
                    roleHelp.text('Access and auth will be derived automatically from the selected role.');
                })
                .fail(function () {
                    roleSelect.empty().append('<option value="">Error loading roles</option>');
                });
        });
    });
</script>
@endpush
