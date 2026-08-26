@extends('welcome')

@section('content')
<div class="content-wrapper" style="background: #f8fafc; min-height: 100vh; padding: 24px;">
    <div class="container-fluid max-w-7xl mx-auto">
        
        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fas fa-crown mr-1"></i> SUPERADMIN / GOD MODE
                    </span>
                    <span class="badge badge-primary text-white px-2 py-1 font-weight-bold" style="font-size: 11px;">
                        <i class="fas fa-coins mr-1"></i> AUTHORITY & LIMITS
                    </span>
                </div>
                <h2 class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 26px; letter-spacing: 0.5px;">
                    FINANCIAL & HR HIRING AUTHORITY LIMITS
                </h2>
                <p class="text-muted small mb-0 mt-1">
                    Configure financial spending approval limits for purchase cases and grade/salary approval thresholds for HR contract appointments.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.settings.workflows') }}" class="btn btn-outline-primary btn-sm font-weight-bold px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-shopping-cart mr-1"></i> Purchase Workflows ➔
                </a>
            </div>
        </div>

        {{-- Top Floating AJAX Notification --}}
        <div id="ajaxToastNotification" class="alert alert-success border-0 shadow-lg d-none align-items-center mb-4 px-4 py-3" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; border-radius: 8px; position: sticky; top: 15px; z-index: 1050; animation: fadeInDown 0.3s ease;">
            <i class="fas fa-check-circle text-success mr-3" style="font-size: 22px;"></i>
            <div>
                <h6 class="mb-0 text-success font-weight-bold rajdhani" style="font-size: 15px;">SETTINGS SAVED IN REAL-TIME</h6>
                <p class="mb-0 text-muted small" id="ajaxToastMessage">Financial authority limits and HR hiring limits have been saved successfully.</p>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.settings.financial.update') }}" method="POST" id="financialSettingsForm">
            @csrf

            {{-- 1. PURCHASE FINANCIAL LIMITS MATRIX --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #e0f2fe; color: #0284c7;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 17px;">1. PURCHASE CASES FINANCIAL AUTHORITY MATRIX</h5>
                            <span class="text-muted small" style="font-size: 11px;">Spending approval thresholds for procurement cases</span>
                        </div>
                    </div>
                    <span class="badge badge-light text-muted border px-2 py-1" style="font-size: 10px;">PKR CURRENCY</span>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        {{-- MD Limit --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="pur_md_threshold" class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                        <i class="fas fa-user-tie text-primary mr-1"></i> MD (R&D) Limit
                                    </label>
                                    <span class="badge badge-info px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Tier 1</span>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 11.5px;">
                                    Cases <strong>up to this limit</strong> are approved by MD. Cases exceeding route to DDG.
                                </p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold bg-white text-muted" style="font-size: 12px;">PKR</span></div>
                                    <input type="number" step="1000" min="0" name="pur_md_threshold" id="pur_md_threshold" class="form-control font-weight-bold text-primary rajdhani" style="font-size: 15px;" value="{{ old('pur_md_threshold', $mdThreshold) }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- DDG Limit --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="pur_ddg_threshold" class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                        <i class="fas fa-user-shield text-info mr-1"></i> DDG Limit
                                    </label>
                                    <span class="badge badge-info px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Tier 2</span>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 11.5px;">
                                    Cases <strong>between MD limit and this limit</strong> are approved by DDG.
                                </p>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold bg-white text-muted" style="font-size: 12px;">PKR</span></div>
                                    <input type="number" step="1000" min="0" name="pur_ddg_threshold" id="pur_ddg_threshold" class="form-control font-weight-bold text-info rajdhani" style="font-size: 15px;" value="{{ old('pur_ddg_threshold', $ddgThreshold) }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- DG Limit Note --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded h-100 d-flex flex-column justify-content-between" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                            <i class="fas fa-crown text-success mr-1"></i> DG (NRDI)
                                        </label>
                                        <span class="badge badge-success px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Terminal</span>
                                    </div>
                                    <p class="text-muted small mb-2" style="font-size: 11.5px;">
                                        All purchase cases exceeding DDG spending limit require Director General terminal approval.
                                    </p>
                                </div>
                                <span class="badge badge-success py-1 font-weight-bold text-center rajdhani" style="font-size: 11px;">UNLIMITED ABOVE DDG</span>
                            </div>
                        </div>
                    </div>

                    {{-- GST & Table Basis --}}
                    <div class="row mt-2">
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold text-dark mb-1 rajdhani" style="font-size: 13px;">
                                <i class="fas fa-percent text-warning mr-1"></i> Limit Calculation Basis:
                            </label>
                            <div class="d-flex gap-2">
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" id="basisWithoutGst" name="pur_threshold_basis" value="without_gst" class="custom-control-input" {{ $thresholdBasis === 'without_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani small" for="basisWithoutGst">Without GST / Base Price (Recommended)</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="basisWithGst" name="pur_threshold_basis" value="with_gst" class="custom-control-input" {{ $thresholdBasis === 'with_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani small" for="basisWithGst">With GST / Total Price</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold text-dark mb-1 rajdhani" style="font-size: 13px;">
                                <i class="fas fa-table text-primary mr-1"></i> Table Amount Display:
                            </label>
                            <div class="d-flex gap-2">
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" id="listWithoutGst" name="pur_list_amount_basis" value="without_gst" class="custom-control-input" {{ $listAmountBasis === 'without_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani small" for="listWithoutGst">Without GST Base Amount</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="listWithGst" name="pur_list_amount_basis" value="with_gst" class="custom-control-input" {{ $listAmountBasis === 'with_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani small" for="listWithGst">With GST Total Amount</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. HR HIRING & CONTRACT AUTHORITY MATRIX --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #fef2f2; color: #dc2626;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 17px;">2. HR HIRING & CONTRACT APPOINTMENTS AUTHORITY MATRIX</h5>
                            <span class="text-muted small" style="font-size: 11px;">Define appointment approval limits by pay grade (SPS / BPS) and monthly salary ceiling</span>
                        </div>
                    </div>
                    <span class="badge badge-light text-muted border px-2 py-1" style="font-size: 10px;">GRADE & SALARY</span>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        {{-- MD HR Limit --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                        <i class="fas fa-user-tie text-primary mr-1"></i> MD (R&D) HR Approval
                                    </label>
                                    <span class="badge badge-info px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Tier 1 HR</span>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="hr_md_grade" class="small text-muted font-weight-bold mb-1 rajdhani">MAXIMUM APPROVED GRADE:</label>
                                    <select name="hr_md_grade" id="hr_md_grade" class="form-control form-control-sm font-weight-bold text-dark rajdhani">
                                        @foreach($gradesList as $gKey => $gDesc)
                                            <option value="{{ $gKey }}" {{ $hrMdGrade === $gKey ? 'selected' : '' }}>{{ $gDesc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="hr_md_salary_limit" class="small text-muted font-weight-bold mb-1 rajdhani">MAX MONTHLY SALARY (PKR):</label>
                                    <input type="number" step="5000" min="0" name="hr_md_salary_limit" id="hr_md_salary_limit" class="form-control form-control-sm font-weight-bold text-primary rajdhani" value="{{ old('hr_md_salary_limit', $hrMdSalary) }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- DDG HR Limit --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                        <i class="fas fa-user-shield text-info mr-1"></i> DDG HR Approval
                                    </label>
                                    <span class="badge badge-info px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Tier 2 HR</span>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="hr_ddg_grade" class="small text-muted font-weight-bold mb-1 rajdhani">MAXIMUM APPROVED GRADE:</label>
                                    <select name="hr_ddg_grade" id="hr_ddg_grade" class="form-control form-control-sm font-weight-bold text-dark rajdhani">
                                        @foreach($gradesList as $gKey => $gDesc)
                                            <option value="{{ $gKey }}" {{ $hrDdgGrade === $gKey ? 'selected' : '' }}>{{ $gDesc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="hr_ddg_salary_limit" class="small text-muted font-weight-bold mb-1 rajdhani">MAX MONTHLY SALARY (PKR):</label>
                                    <input type="number" step="5000" min="0" name="hr_ddg_salary_limit" id="hr_ddg_salary_limit" class="form-control form-control-sm font-weight-bold text-info rajdhani" value="{{ old('hr_ddg_salary_limit', $hrDdgSalary) }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- DG HR Authority Note --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded h-100 d-flex flex-column justify-content-between" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                            <i class="fas fa-crown text-success mr-1"></i> DG (NRDI) Authority
                                        </label>
                                        <span class="badge badge-success px-2 py-0.5 font-weight-bold" style="font-size: 10px;">Terminal</span>
                                    </div>
                                    <p class="text-muted small mb-2" style="font-size: 11.5px;">
                                        All senior contract appointments (SPS-09/10, BPS-19/20, Consultants) and salaries exceeding DDG limit require DG terminal approval.
                                    </p>
                                </div>
                                <span class="badge badge-success py-1 font-weight-bold text-center rajdhani" style="font-size: 11px;">SENIOR GRADES & SALARIES</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Save Sticky Footer --}}
            <div class="card border-0 shadow-sm sticky-bottom" style="border-radius: 12px; background: #ffffff; position: sticky; bottom: 20px; z-index: 100; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span class="text-dark font-weight-bold small rajdhani" style="font-size: 13.5px;">
                            FINANCIAL AND HR HIRING LIMITS WILL UPDATE DYNAMICALLY IN REAL-TIME VIA AJAX.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" id="btnSaveFinancial" class="btn btn-success font-weight-bold px-4 py-2 rajdhani" style="border-radius: 8px; font-size: 15px; letter-spacing: 0.5px; box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);">
                            <i class="fas fa-save mr-1.5" id="btnFinIcon"></i> <span id="btnFinText">SAVE ALL LIMITS SETTINGS</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

{{-- AJAX Form Handler Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('financialSettingsForm');
    const btnSave = document.getElementById('btnSaveFinancial');
    const btnIcon = document.getElementById('btnFinIcon');
    const btnText = document.getElementById('btnFinText');
    const toast = document.getElementById('ajaxToastNotification');
    const toastMsg = document.getElementById('ajaxToastMessage');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            btnSave.disabled = true;
            btnIcon.className = 'fas fa-spinner fa-spin mr-1.5';
            btnText.innerText = 'SAVING...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                btnSave.disabled = false;
                btnIcon.className = 'fas fa-save mr-1.5';
                btnText.innerText = 'SAVE ALL LIMITS SETTINGS';

                if (data.success) {
                    toastMsg.innerText = data.message || 'Financial authority limits and HR hiring limits saved in real-time!';
                    toast.classList.remove('d-none');
                    toast.classList.add('d-flex');
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    setTimeout(() => {
                        toast.classList.remove('d-flex');
                        toast.classList.add('d-none');
                    }, 4000);
                }
            })
            .catch(err => {
                btnSave.disabled = false;
                btnIcon.className = 'fas fa-save mr-1.5';
                btnText.innerText = 'SAVE ALL LIMITS SETTINGS';
                alert('An error occurred while saving settings via AJAX. Please try again.');
            });
        });
    }
});
</script>

<style>
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection
