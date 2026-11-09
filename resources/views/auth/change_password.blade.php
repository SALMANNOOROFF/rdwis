@extends('welcome')

@section('content')
<div class="content-wrapper" style="background: #f8fafc; min-height: 100vh; padding: 24px 16px;">
    <div class="container-fluid" style="max-width: 720px; margin: 0 auto;">
        
        {{-- Breadcrumb & Title --}}
        <div class="mb-4">
            <div class="d-flex align-items-center text-muted small mb-1" style="font-size: 12px; gap: 6px;">
                <a href="{{ url('/') }}" class="text-decoration-none text-muted"><i class="fas fa-home mr-1"></i> Home</a>
                <span>/</span>
                <span class="text-muted">Helpdesk</span>
                <span>/</span>
                <span class="text-dark font-weight-bold">Change Password</span>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <div>
                    <h2 class="font-weight-bold text-dark mb-0" style="font-size: 24px; letter-spacing: -0.3px;">
                        <i class="fas fa-key text-warning mr-2"></i> Change Password
                    </h2>
                    <p class="text-muted small mb-0 mt-1">
                        Update your account password to keep your RDWIS profile secure.
                    </p>
                </div>
                <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-1.5" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left mr-1"></i> Dashboard
                </a>
            </div>
        </div>

        @php
            $defaultPassword = (string) config('auth.default_password', '12345');
            $user = auth()->user();
            $isDefaultPass = $user && \App\Models\CenAccount::verifyPassword((string) ($user->acc_username ?? ''), $defaultPassword, (string) ($user->acc_pass ?? ''));
        @endphp

        {{-- Advisory for default password users --}}
        @if($isDefaultPass)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 px-4 py-3" style="background: #fffbeb; border-left: 4px solid #f59e0b !important; border-radius: 8px;">
            <i class="fas fa-shield-alt text-warning mr-3" style="font-size: 22px;"></i>
            <div>
                <h6 class="mb-0 text-warning font-weight-bold" style="font-size: 14px; color: #b45309 !important;">DEFAULT PASSWORD IN USE</h6>
                <p class="mb-0 text-muted small">You are currently using the default system password. Please choose a strong personal password to continue.</p>
            </div>
        </div>
        @endif

        {{-- Success Alert --}}
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 px-4 py-3 alert-dismissible fade show" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; border-radius: 8px;" role="alert">
            <i class="fas fa-check-circle text-success mr-3" style="font-size: 22px;"></i>
            <div class="flex-grow-1">
                <h6 class="mb-0 text-success font-weight-bold" style="font-size: 14px;">PASSWORD UPDATED SUCCESSFULLY</h6>
                <p class="mb-0 text-muted small">{{ session('success') }}</p>
            </div>
            <button type="button" class="close text-muted" data-dismiss="alert" aria-label="Close" style="font-size: 18px; line-height: 1; padding: 12px 16px;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        {{-- Error Summary Alert --}}
        @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4 px-4 py-3 alert-dismissible fade show" style="background: #fef2f2; border-left: 4px solid #ef4444 !important; border-radius: 8px;" role="alert">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle text-danger mr-2" style="font-size: 16px;"></i>
                <h6 class="mb-0 text-danger font-weight-bold" style="font-size: 14px;">PLEASE CORRECT THE FOLLOWING ERRORS</h6>
            </div>
            <ul class="mb-0 pl-4 small text-danger" style="font-size: 13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close text-muted" data-dismiss="alert" aria-label="Close" style="font-size: 18px; line-height: 1; padding: 12px 16px;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        {{-- Change Password Form Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 14px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center" style="border-radius: 14px 14px 0 0;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 38px; height: 38px; background: #fef3c7; color: #d97706;">
                    <i class="fas fa-lock" style="font-size: 16px;"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 16px;">Update Your Password</h5>
                    <span class="text-muted small" style="font-size: 12px;">Logged in as: <strong>{{ $user?->acc_name ?? 'User' }}</strong> ({{ $user?->acc_username ?? '' }})</span>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('password.update') }}" id="changePasswordForm" autocomplete="off">
                    @csrf

                    {{-- Current Password --}}
                    <div class="form-group mb-3">
                        <label for="current_password" class="font-weight-bold text-dark" style="font-size: 13px;">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0 text-muted" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                    <i class="fas fa-lock" style="font-size: 13px;"></i>
                                </span>
                            </div>
                            <input type="password" 
                                   name="current_password" 
                                   id="current_password" 
                                   class="form-control border-left-0 border-right-0 @error('current_password') is-invalid @enderror" 
                                   style="border-color: #cbd5e1; font-size: 14px;"
                                   placeholder="Enter your current password" 
                                   autocomplete="current-password"
                                   required>
                            <div class="input-group-append">
                                <button type="button" 
                                        class="btn btn-light border border-left-0 text-muted toggle-password-btn" 
                                        data-target="current_password"
                                        title="Show / Hide Password"
                                        style="border-radius: 0 8px 8px 0; border-color: #cbd5e1;">
                                    <i class="fas fa-eye" style="font-size: 13px;"></i>
                                </button>
                            </div>
                        </div>
                        @error('current_password')
                            <small class="text-danger font-weight-bold mt-1 d-block" style="font-size: 12px;">
                                <i class="fas fa-times-circle mr-1"></i> {{ $message }}
                            </small>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="form-group mb-3">
                        <label for="password" class="font-weight-bold text-dark" style="font-size: 13px;">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0 text-muted" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                    <i class="fas fa-key" style="font-size: 13px;"></i>
                                </span>
                            </div>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="form-control border-left-0 border-right-0 @error('password') is-invalid @enderror" 
                                   style="border-color: #cbd5e1; font-size: 14px;"
                                   placeholder="Enter new password (min. 5 characters)" 
                                   autocomplete="new-password"
                                   minlength="5"
                                   required>
                            <div class="input-group-append">
                                <button type="button" 
                                        class="btn btn-light border border-left-0 text-muted toggle-password-btn" 
                                        data-target="password"
                                        title="Show / Hide Password"
                                        style="border-radius: 0 8px 8px 0; border-color: #cbd5e1;">
                                    <i class="fas fa-eye" style="font-size: 13px;"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted" style="font-size: 11.5px;">
                            <i class="fas fa-info-circle mr-1"></i> Password must be at least 5 characters and cannot be default ({{ $defaultPassword }}).
                        </small>
                        @error('password')
                            <small class="text-danger font-weight-bold mt-1 d-block" style="font-size: 12px;">
                                <i class="fas fa-times-circle mr-1"></i> {{ $message }}
                            </small>
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div class="form-group mb-4">
                        <label for="password_confirmation" class="font-weight-bold text-dark" style="font-size: 13px;">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0 text-muted" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                    <i class="fas fa-shield-alt" style="font-size: 13px;"></i>
                                </span>
                            </div>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="form-control border-left-0 border-right-0 @error('password_confirmation') is-invalid @enderror" 
                                   style="border-color: #cbd5e1; font-size: 14px;"
                                   placeholder="Re-enter new password" 
                                   autocomplete="new-password"
                                   minlength="5"
                                   required>
                            <div class="input-group-append">
                                <button type="button" 
                                        class="btn btn-light border border-left-0 text-muted toggle-password-btn" 
                                        data-target="password_confirmation"
                                        title="Show / Hide Password"
                                        style="border-radius: 0 8px 8px 0; border-color: #cbd5e1;">
                                    <i class="fas fa-eye" style="font-size: 13px;"></i>
                                </button>
                            </div>
                        </div>
                        @error('password_confirmation')
                            <small class="text-danger font-weight-bold mt-1 d-block" style="font-size: 12px;">
                                <i class="fas fa-times-circle mr-1"></i> {{ $message }}
                            </small>
                        @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: #f1f5f9 !important;">
                        <a href="{{ url('/') }}" class="btn btn-light border font-weight-bold text-secondary px-3 py-2" style="border-radius: 8px; font-size: 13.5px;">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success font-weight-bold px-4 py-2 shadow-sm" id="submitBtn" style="border-radius: 8px; font-size: 13.5px; background: #16a34a; border-color: #16a34a;">
                            <i class="fas fa-check-circle mr-1.5"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- Toggle Password Script & SweetAlert Toast --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show / Hide Password toggle
    document.querySelectorAll('.toggle-password-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            var icon = this.querySelector('i');
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // SweetAlert notification on successful update
    @if(session('success'))
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Password Updated!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'OK',
                timer: 4000,
                timerProgressBar: true
            });
        }
    @endif
});
</script>
@endsection
