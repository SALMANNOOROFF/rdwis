@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        .card-add-project {
            border-top: 3px solid #007bff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 30px auto;
            max-width: 1500px;
            border-radius: 8px;
        }
        .form-header {
            background-color: #f8f9fa;
            padding: 15px 25px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }
        .form-header h3 {
            margin: 0;
            font-size: 1.4rem;
            color: #444;
            font-weight: 600;
        }
        .custom-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .helper-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
            font-style: italic;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }
        .action-btns {
            background-color: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            border-radius: 0 0 8px 8px;
        }
        .btn-save {
            min-width: 120px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-cancel {
            min-width: 120px;
            margin-right: 15px;
        }
    </style>

  <div class="content-wrapper pt-3">
   <section class="content">
        <div class="container-fluid">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">Initiate New Project</h3>
                </div>

                {{-- FORM START --}}
                <form action="{{ route('save-project') }}" method="POST">
                    @csrf 
                    <div class="card-body">
                        
                        {{-- Row 1: Title and Sponsor --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Project Title <span class="text-danger">*</span></label>
                                <input type="text" name="prj_title" class="form-control" placeholder="Enter Project Name" required>
                            </div>

                            {{-- NEW FIELD: SPONSOR --}}
                            <div class="col-md-6 form-group">
                                <label>Sponsor / Funding Agency</label>
                                <input type="text" name="prj_sponsor" class="form-control" placeholder="e.g. World Bank, Govt of Punjab">
                            </div>
                        </div>

                        {{-- Row 2: Cost --}}
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Proposed Cost (Rs.)</label>
                                <input type="number" name="prj_propcost" class="form-control" placeholder="0.00">
                            </div>
                        </div>

                        {{-- Row 3: Scope --}}
                        <div class="form-group">
                            <label>Scope of Work</label>
                            <textarea name="prj_scope" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-arrow-right mr-1"></i> Save & Continue to Details
                        </button>
                    </div>
                </form>
                {{-- FORM END --}}

            </div>
        </div>
    </section>
</div>
@endsection