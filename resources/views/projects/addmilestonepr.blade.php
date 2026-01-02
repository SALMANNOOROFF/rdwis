@extends('welcome')

@section('content')
<div class="content-wrapper">

<style>
      

        /* Card Styling */
        .card {
            background-color: #fff;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
            width: 100%;
            
            border-top: 3px solid #007BFF; /* Info Cyan color */
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1rem 1.25rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 500;
            margin: 0;
            color: #1f2d3d;
        }

        .card-body { padding: 2rem; }

        /* Form Layout */
        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .form-group label {
            width: 140px;
            font-weight: 600;
            color: #495057;
            margin-right: 15px;
            text-align: right;
        }

        .input-wrapper { flex: 1; }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        select.form-control { cursor: pointer; }

        /* Footer Buttons */
        .card-footer {
            padding: 1rem 1.25rem;
            background-color: #f8f9fa;
            border-top: 1px solid rgba(0,0,0,.125);
            text-align: right;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            border-radius: .25rem;
            cursor: pointer;
            margin-left: 10px;
        }

        .btn-primary { background-color: #007BFF; border-color: #17a2b8; color: white; }
        .btn-primary:hover { background-color: #007BFF; border-color: #117a8b; }
       
        .btn-default { background-color: #f8f9fa; border-color: #ddd; color: #444; }
        .btn-default:hover { background-color: #e2e6ea; }

        @media (max-width: 576px) {
            .form-group { flex-direction: column; align-items: flex-start; }
            .form-group label { text-align: left; width: 100%; margin-bottom: 5px; }
        }
    </style>
  <div class="card">
        <div class="card-header">
            <h3 class="card-title">Milestone/Activity Detail</h3>
        </div>
       
        <form>
            <div class="card-body">
               
                <div class="form-group">
                    <label>Number</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" style="width: 100px;" placeholder="#">
                    </div>
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <div class="input-wrapper">
                        <select class="form-control" style="width: 200px;">
                            <option selected>Milestone</option>
                            <option>Activity</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" placeholder="Enter description">
                    </div>
                </div>

                <div class="form-group">
                    <label>Target Date</label>
                    <div class="input-wrapper">
                        <input type="date" class="form-control" style="width: 180px;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <div class="input-wrapper">
                        <select class="form-control" style="width: 200px;">
                            <option selected>Not started</option>
                            <option>Awaited</option>
                            <option>Held up</option>
                            <option>In progress</option>
                            <option>Completed</option>
                        </select>
                    </div>
                </div>

            </div> <div class="card-footer">
                <button type="button" class="btn btn-default">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

</div>


@endsection