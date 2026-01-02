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

    <div class="container-fluid">
        <div class="card card-add-project">
            <div class="form-header">
                <h3><i class="fas fa-plus-circle mr-2 text-primary"></i> Add New Project</h3>
            </div>
            
            <form action="#" method="POST">
                @csrf
                <div class="card-body px-4 py-4">
                    
                    <div class="form-group row mb-4">
                        <label class="col-sm-2 custom-label col-form-label">Title</label>
                        <div class="col-sm-10">
                            <input type="text" name="title" class="form-control" placeholder="Enter project full title..." required>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-sm-2 custom-label col-form-label">Code</label>
                        <div class="col-sm-4">
                            <input type="text" name="code" class="form-control" placeholder="e.g. PRJ-123" maxlength="9">
                            <p class="helper-text">
                                <i class="fas fa-info-circle mr-1"></i> 
                                Only capital letters, numbers and '-' allowed. Max 9 characters.
                            </p>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-sm-2 custom-label col-form-label">Sponsor</label>
                        <div class="col-sm-10">
                            <input type="text" name="sponsor" class="form-control" placeholder="Name of sponsoring department or entity">
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-sm-2 custom-label col-form-label">Receipt Date</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="receipt_date" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="action-btns">
                    <button type="button" class="btn btn-default btn-cancel border shadow-sm" onclick="window.history.back();">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-save shadow-sm">
                        <i class="fas fa-check mr-1"></i> Save Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection