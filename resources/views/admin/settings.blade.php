@extends('welcome')

@section('content')
<div class="content-wrapper" style="background: #f1f5f9; min-height: 100vh; padding: 24px;">
    <div class="container-fluid max-w-7xl mx-auto">
        
        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px solid #cbd5e1;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fas fa-crown mr-1"></i> SUPERADMIN / GOD MODE
                    </span>
                    <span class="badge badge-primary text-white px-2 py-1 font-weight-bold" style="font-size: 11px;">
                        <i class="fas fa-network-wired mr-1"></i> WORKFLOW ENGINE 2.0
                    </span>
                </div>
                <h2 class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 26px; letter-spacing: 0.5px;">
                    <i class="fas fa-sliders-h text-primary mr-2"></i> RDWIS SYSTEM SETTINGS & DYNAMIC WORKFLOW FLOWS
                </h2>
                <p class="text-muted small mb-0 mt-1">
                    Configure dynamic financial approving authority limits and customize step-by-step case routing (Forward / Return chains) across all case types.
                </p>
            </div>
            <div>
                <a href="{{ route('godmode.index') }}" class="btn btn-outline-danger btn-sm font-weight-bold px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-radiation-alt mr-1"></i> God Mode Panel
                </a>
            </div>
        </div>

        {{-- Flash Success Alert --}}
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 px-4 py-3" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; border-radius: 8px;">
            <i class="fas fa-check-circle text-success mr-3" style="font-size: 22px;"></i>
            <div>
                <h6 class="mb-0 text-success font-weight-bold rajdhani" style="font-size: 15px;">SETTINGS SAVED SUCCESSFULLY</h6>
                <p class="mb-0 text-muted small">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        {{-- Settings Form --}}
        <form action="{{ route('admin.settings.update') }}" method="POST" id="rdwisSettingsForm">
            @csrf

            {{-- SECTION 1: FINANCIAL THRESHOLDS & TAX RULES --}}
            <div class="row">
                {{-- LEFT COLUMN: Approval Authority Limits Matrix --}}
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #e0f2fe; color: #0284c7;">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 17px;">APPROVAL THRESHOLDS MATRIX</h5>
                                    <span class="text-muted small" style="font-size: 11px;">Tiered spending limits for approving authorities</span>
                                </div>
                            </div>
                            <span class="badge badge-light text-muted border px-2 py-1" style="font-size: 10px;">PKR</span>
                        </div>

                        <div class="card-body p-4">
                            
                            {{-- 1. MD Threshold --}}
                            <div class="mb-4 p-3 rounded" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="pur_md_threshold" class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                        <i class="fas fa-user-tie text-primary mr-1"></i> MD (R&D) Approving Limit
                                    </label>
                                    <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 11px;">Tier 1 Limit</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Cases with estimated/base amount <strong>up to this limit</strong> are approved by MD (R&D). Cases exceeding this amount forward to DDG.
                                </p>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold bg-white text-muted" style="font-size: 13px;">PKR</span>
                                    </div>
                                    <input type="number" step="1000" min="0" name="pur_md_threshold" id="pur_md_threshold" class="form-control font-weight-bold text-primary rajdhani" style="font-size: 16px;" value="{{ old('pur_md_threshold', $mdThreshold) }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block font-italic">Default standard: Rs. 400,000 (4 Lakh)</small>
                            </div>

                            {{-- 2. DDG Threshold --}}
                            <div class="mb-4 p-3 rounded" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="pur_ddg_threshold" class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                                        <i class="fas fa-user-shield text-info mr-1"></i> DDG Approving Limit
                                    </label>
                                    <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 11px;">Tier 2 Limit</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Cases with amount <strong>up to this limit</strong> are approved by DDG. Cases exceeding this amount route to DG (NRDI).
                                </p>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold bg-white text-muted" style="font-size: 13px;">PKR</span>
                                    </div>
                                    <input type="number" step="1000" min="0" name="pur_ddg_threshold" id="pur_ddg_threshold" class="form-control font-weight-bold text-info rajdhani" style="font-size: 16px;" value="{{ old('pur_ddg_threshold', $ddgThreshold) }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block font-italic">Default standard: Rs. 1,000,000 (10 Lakh)</small>
                            </div>

                            {{-- 3. DG Authority Note --}}
                            <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 32px; height: 32px; font-size: 14px;">
                                        <i class="fas fa-award"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 14px;">DG (NRDI) Full Authority</h6>
                                        <span class="text-muted small">All purchase cases above DDG limit require DG (NRDI) terminal approval.</span>
                                    </div>
                                </div>
                                <span class="badge badge-success px-2.5 py-1 font-weight-bold" style="font-size: 11px;">Unlimited</span>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: GST & Display Settings --}}
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #fef3c7; color: #b45309;">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 17px;">TAX & DISPLAY BASIS</h5>
                                    <span class="text-muted small" style="font-size: 11px;">GST / SST calculation & table display preferences</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">

                            {{-- Option 1: Approval Authority Threshold Evaluation Basis --}}
                            <div class="mb-4">
                                <label class="font-weight-bold text-dark mb-1 rajdhani" style="font-size: 14px;">
                                    <i class="fas fa-percent text-warning mr-1"></i> Authority Limit Evaluation Basis
                                </label>
                                <p class="text-muted small mb-2">
                                    Choose whether spending limits (e.g. 4 Lakh for MD) are evaluated based on <strong>Without GST Price</strong> or <strong>With GST Total</strong>:
                                </p>

                                <div class="custom-control custom-radio p-2.5 mb-2 rounded" style="background: #f8fafc; border: 1.5px solid #cbd5e1;">
                                    <input type="radio" id="basisWithoutGst" name="pur_threshold_basis" value="without_gst" class="custom-control-input" {{ $thresholdBasis === 'without_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani" for="basisWithoutGst" style="font-size: 13px; cursor: pointer;">
                                        WITHOUT GST / BASE PRICE (RECOMMENDED)
                                    </label>
                                    <p class="text-muted small mb-0 mt-0.5 pl-1" style="font-size: 11px;">
                                        Evaluates against quote base cost without tax (e.g. 3.9L + GST = 4.5L is still approved by MD).
                                    </p>
                                </div>

                                <div class="custom-control custom-radio p-2.5 rounded" style="background: #f8fafc; border: 1.5px solid #cbd5e1;">
                                    <input type="radio" id="basisWithGst" name="pur_threshold_basis" value="with_gst" class="custom-control-input" {{ $thresholdBasis === 'with_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani" for="basisWithGst" style="font-size: 13px; cursor: pointer;">
                                        WITH GST / TOTAL GROSS PRICE
                                    </label>
                                    <p class="text-muted small mb-0 mt-0.5 pl-1" style="font-size: 11px;">
                                        Evaluates against gross total inclusive of taxes.
                                    </p>
                                </div>
                            </div>

                            <hr class="my-3" style="border-top: 1px dashed #cbd5e1;">

                            {{-- Option 2: Hub Tables Display Basis --}}
                            <div>
                                <label class="font-weight-bold text-dark mb-1 rajdhani" style="font-size: 14px;">
                                    <i class="fas fa-table text-primary mr-1"></i> Hub Tables List Amount Display
                                </label>
                                <p class="text-muted small mb-2">
                                    Control which amount is shown in the <strong>"Est. Amount"</strong> column in tables:
                                </p>

                                <div class="custom-control custom-radio p-2.5 mb-2 rounded" style="background: #f8fafc; border: 1.5px solid #cbd5e1;">
                                    <input type="radio" id="listWithoutGst" name="pur_list_amount_basis" value="without_gst" class="custom-control-input" {{ $listAmountBasis === 'without_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani" for="listWithoutGst" style="font-size: 13px; cursor: pointer;">
                                        SHOW WITHOUT GST AMOUNT (RECOMMENDED)
                                    </label>
                                </div>

                                <div class="custom-control custom-radio p-2.5 rounded" style="background: #f8fafc; border: 1.5px solid #cbd5e1;">
                                    <input type="radio" id="listWithGst" name="pur_list_amount_basis" value="with_gst" class="custom-control-input" {{ $listAmountBasis === 'with_gst' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark rajdhani" for="listWithGst" style="font-size: 13px; cursor: pointer;">
                                        SHOW WITH GST TOTAL AMOUNT
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: DYNAMIC PURCHASE WORKFLOW & ROUTING ENGINE --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #dcfce7; color: #15803d;">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 18px;">DYNAMIC WORKFLOW PIPELINE & ROUTING MATRIX</h5>
                            <span class="text-muted small" style="font-size: 11px;">Configure sequential user-to-user forward pathways and return rules for each case type</span>
                        </div>
                    </div>

                    {{-- Case Type Pills --}}
                    <ul class="nav nav-pills rajdhani font-weight-bold" id="workflowTypeTabs" role="tablist" style="gap: 6px;">
                        <li class="nav-item">
                            <a class="nav-link active px-3 py-1.5 rounded" id="tab-flow-default" data-toggle="pill" href="#flow-default" role="tab" style="font-size: 13px;">
                                <i class="fas fa-globe mr-1"></i> DEFAULT (UNIVERSAL)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-1.5 rounded" id="tab-flow-ps" data-toggle="pill" href="#flow-ps" role="tab" style="font-size: 13px;">
                                <i class="fas fa-shopping-cart mr-1"></i> PS (PROCUREMENT)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-1.5 rounded" id="tab-flow-pt" data-toggle="pill" href="#flow-pt" role="tab" style="font-size: 13px;">
                                <i class="fas fa-credit-card mr-1"></i> PT (PETTY)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-1.5 rounded" id="tab-flow-rb" data-toggle="pill" href="#flow-rb" role="tab" style="font-size: 13px;">
                                <i class="fas fa-file-invoice mr-1"></i> RB (RUNNING BILLS)
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="workflowTypeTabContent">
                        
                        {{-- 1. DEFAULT FLOW --}}
                        <div class="tab-pane fade show active" id="flow-default" role="tabpanel">
                            @include('admin._workflow_tab_content', ['typeKey' => 'DEFAULT', 'typeTitle' => 'Default (Universal Flow)', 'matrix' => $defaultMatrix, 'stageOptions' => $stageOptions])
                        </div>

                        {{-- 2. PS FLOW --}}
                        <div class="tab-pane fade" id="flow-ps" role="tabpanel">
                            @include('admin._workflow_tab_content', ['typeKey' => 'PS', 'typeTitle' => 'PS (Procurement Scrutiny Flow)', 'matrix' => $psMatrix, 'stageOptions' => $stageOptions])
                        </div>

                        {{-- 3. PT FLOW --}}
                        <div class="tab-pane fade" id="flow-pt" role="tabpanel">
                            @include('admin._workflow_tab_content', ['typeKey' => 'PT', 'typeTitle' => 'PT (Petty Cash Flow)', 'matrix' => $ptMatrix, 'stageOptions' => $stageOptions])
                        </div>

                        {{-- 4. RB FLOW --}}
                        <div class="tab-pane fade" id="flow-rb" role="tabpanel">
                            @include('admin._workflow_tab_content', ['typeKey' => 'RB', 'typeTitle' => 'RB (Running Bills / Services Flow)', 'matrix' => $rbMatrix, 'stageOptions' => $stageOptions])
                        </div>

                    </div>
                </div>
            </div>

            {{-- Action Save Sticky Footer --}}
            <div class="card border-0 shadow-sm sticky-bottom" style="border-radius: 12px; background: #ffffff; position: sticky; bottom: 20px; z-index: 100; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-dark font-weight-bold small rajdhani" style="font-size: 13.5px;">
                            ALL WORKFLOWS AND FINANCIAL LIMITS WILL APPLY DYNAMICALLY IN REAL-TIME TO ALL USERS.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-success font-weight-bold px-4 py-2 rajdhani" style="border-radius: 8px; font-size: 15px; letter-spacing: 0.5px; box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);">
                            <i class="fas fa-save mr-1.5"></i> SAVE ALL RDWIS SETTINGS & WORKFLOWS
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>
@endsection
