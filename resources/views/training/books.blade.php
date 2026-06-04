@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color:var(--rd-text1); letter-spacing: -0.5px;">
                        <i class="fas fa-book-open mr-2 text-primary"></i> Raise Book Procurement Case
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('training.create') }}" class="btn btn-outline-info btn-sm shadow-sm mr-2" style="border-radius:6px;">
                        <i class="fas fa-chalkboard-teacher mr-1"></i> Training Case
                    </a>
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
                <form id="booksForm" action="{{ route('training.books.store') }}" method="POST" enctype="multipart/form-data">
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
                                        <label class="form-label extra-small text-uppercase tracking-wider">Quotations / Reference PDF</label>
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
                                <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius:6px;">
                                    <i class="fas fa-paper-plane mr-2"></i> Submit Request
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Part 2 --}}
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-3" style="background:var(--rd-surface);border-radius:10px;">
                            <div class="card-header border-0 pb-0 bg-transparent">
                                <h6 class="card-title font-weight-bold" style="color:var(--rd-accent); font-size: 0.9rem;">2. Procurement Type & Book Details</h6>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label extra-small text-uppercase tracking-wider border-bottom border-secondary pb-1 d-block mb-3">Procurement Purpose</label>
                                        <div class="d-flex align-items-center">
                                            <div class="custom-control custom-radio mr-4">
                                                <input class="custom-control-input" type="radio" id="type_general" name="proc_type" value="general" checked>
                                                <label for="type_general" class="custom-control-label font-weight-bold text-white small">General Procurement</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" id="type_linked" name="proc_type" value="linked">
                                                <label for="type_linked" class="custom-control-label font-weight-bold text-white small">Linked to Training Case</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="linked_case_section" class="row d-none mb-3 animated fadeIn">
                                    <div class="col-md-12">
                                        <div class="p-2 border border-secondary rounded" style="background: rgba(var(--rd-accent-rgb), 0.05);">
                                            <label class="form-label extra-small text-uppercase tracking-wider text-accent">Training Reference ID / Case No.</label>
                                            <input type="text" name="training_ref" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="e.g. TR-2026-045">
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-secondary my-3">

                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Book Name / Title</label>
                                        <input type="text" name="book_title" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Full Title" required>
                                    </div>
                                    <div class="col-md-8 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Author / Publisher</label>
                                        <input type="text" name="author" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Author Name">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Edition / ISBN</label>
                                        <input type="text" name="isbn" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="ISBN-13">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Quantity</label>
                                        <input type="number" name="qty" class="form-control form-control-sm bg-dark border-secondary text-white" value="1" min="1">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Est. Price (Each)</label>
                                        <input type="number" name="price" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="0.00">
                                    </div>
                                    <div class="col-md-5 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Source (Store/Link)</label>
                                        <input type="text" name="source" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Amazon / Local Book Store">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label extra-small text-uppercase tracking-wider">Justification for Purchase</label>
                                        <textarea name="justification" class="form-control form-control-sm bg-dark border-secondary text-white" rows="3" placeholder="Explain the requirement for this specific resource..."></textarea>
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
    .animated { animation-duration: 0.3s; }
</style>

<script>
$(document).ready(function() {
    $('input[name="proc_type"]').on('change', function() {
        if ($(this).val() === 'linked') {
            $('#linked_case_section').removeClass('d-none');
        } else {
            $('#linked_case_section').addClass('d-none');
        }
    });

    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@endsection
