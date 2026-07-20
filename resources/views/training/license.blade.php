@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color:var(--rd-text1); letter-spacing: -0.5px;">
                        <i class="fas fa-file-signature mr-2 text-warning"></i> Raise Licence / Fee Case
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('training.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius:6px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Queue
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="mx-auto" style="max-width: 1250px;">
                <form id="licenseForm" action="{{ route('training.license.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    {{-- Left Column: Part 1 & 3 --}}
                    <div class="col-md-5">
                        {{-- 1. Requester Details --}}
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold" style="color:var(--rd-accent); font-size: 0.9rem;">1. Requester Details</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                <div class="row">
                                    <div class="col-md-7 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Employee Name</label>
                                        <input type="text" name="employee_name" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Name" required>
                                    </div>
                                    <div class="col-md-5 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Employee ID</label>
                                        <input type="text" name="employee_id" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="ID" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Department</label>
                                        <input type="text" name="department" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Dept" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Designation</label>
                                        <input type="text" name="designation" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Desig" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Documentation --}}
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold" style="color:var(--rd-text1); font-size: 0.9rem;">3. Documentation</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Invoice / Quotation / Reference PDF</label>
                                        <div class="custom-file">
                                            <input type="file" name="attachment" class="custom-file-input" id="customFile">
                                            <label class="custom-file-label form-control-sm bg-dark border-secondary text-white" for="customFile">Choose file</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Remarks</label>
                                        <textarea name="remarks" class="form-control form-control-sm bg-dark border-secondary text-white" rows="4" placeholder="Additional notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 text-right pb-3 pr-4">
                                <button type="submit" class="btn btn-warning btn-sm px-4 font-weight-bold shadow-sm" style="border-radius:6px;">
                                    <i class="fas fa-paper-plane mr-2"></i> Submit Request
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Part 2 --}}
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold" style="color:var(--rd-accent); font-size: 0.9rem;">2. Licence / Subscription Details</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label extra-small text-uppercase tracking-wider border-bottom border-secondary pb-1 d-block mb-3">Licence Category</label>
                                        <div class="d-flex align-items-center">
                                            <div class="custom-control custom-radio mr-4">
                                                <input class="custom-control-input" type="radio" id="type_software" name="lic_type" value="software" checked>
                                                <label for="type_software" class="custom-control-label font-weight-bold text-white small">Software Licence</label>
                                            </div>
                                            <div class="custom-control custom-radio mr-4">
                                                <input class="custom-control-input" type="radio" id="type_service" name="lic_type" value="service">
                                                <label for="type_service" class="custom-control-label font-weight-bold text-white small">Service Fee</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" id="type_registration" name="lic_type" value="registration">
                                                <label for="type_registration" class="custom-control-label font-weight-bold text-white small">Registration Fee</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-secondary my-3">

                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Product / Service Name</label>
                                        <input type="text" name="product_name" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="e.g. Adobe Creative Cloud, ISO Certification" required>
                                    </div>
                                    <div class="col-md-8 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Vendor / Authority</label>
                                        <input type="text" name="vendor" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Vendor Name">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Licence Type</label>
                                        <select name="type_detail" class="form-control form-control-sm bg-dark border-secondary text-white">
                                            <option value="single">Single User</option>
                                            <option value="multi">Multi-User / Team</option>
                                            <option value="enterprise">Enterprise</option>
                                            <option value="perpetual">Perpetual (Onetime)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Duration</label>
                                        <select name="duration" class="form-control form-control-sm bg-dark border-secondary text-white">
                                            <option value="1">1 Year</option>
                                            <option value="2">2 Years</option>
                                            <option value="3">3 Years</option>
                                            <option value="lifetime">Lifetime</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Qty</label>
                                        <input type="number" name="qty" class="form-control form-control-sm bg-dark border-secondary text-white" value="1" min="1">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider font-weight-bold text-success">Est. Price (Total)</label>
                                        <input type="number" name="total_price" class="form-control form-control-sm bg-dark border-secondary text-white font-weight-bold" placeholder="0.00">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Purpose / Justification</label>
                                        <textarea name="justification" class="form-control form-control-sm bg-dark border-secondary text-white" rows="4" placeholder="Explain why this licence or fee is required..."></textarea>
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
        --rd-accent: #007bff;
        --rd-accent-rgb: 0, 123, 255;
        --rd-text1: #ffffff;
        --rd-text2: #a2a3b7;
    }
    .extra-small { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; }
    .text-accent { color: var(--rd-accent); }
</style>

<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@endsection
