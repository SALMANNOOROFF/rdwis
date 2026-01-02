@extends('welcome')

@section('content')

<style>
    /* Custom CSS for alignment */
    .form-group {
        margin-bottom: 0.8rem; /* Slightly tighter spacing like the image */
    }
    .col-form-label {
        font-weight: normal; /* Labels are not bold in the image */
        font-size: 14px;
        color: #333;
        padding-right: 0; /* Remove right padding to align closer to input */
    }
    .form-control {
        height: 30px; /* Smaller height to match the compact look */
        padding: 0.25rem 0.5rem;
        font-size: 13px;
        border-radius: 3px;
    }
    .align-right {
        text-align: right;
        padding-right: 15px; /* Add some spacing for right-aligned labels */
    }
    /* Gray button style */
    .btn-custom-gray {
        background-color: #e0e0e0;
        border: 1px solid #ccc;
        color: #333;
        min-width: 90px;
        font-size: 14px;
        padding: 4px 10px;
    }
    .btn-custom-gray:hover {
        background-color: #d0d0d0;
    }
    /* Disabled input style */
    .form-control:disabled {
        background-color: #f0f0f0;
    }
    /* Custom width utilities to fine-tune */
    .w-label-small { width: 60px; }
    .w-label-medium { width: 80px; }
</style>

<div class="content-wrapper">

    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Purchase Case Source and Type</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Purchase Case</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- TOP BUTTONS -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-body text-center py-3">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-primary active" id="btn-major" onclick="showForm('major')">
                            <input type="radio" name="options" autocomplete="off" checked> 
                            Major Purchase (With Quotes)
                        </label>
                        <label class="btn btn-outline-primary" id="btn-incidental" onclick="showForm('incidental')">
                            <input type="radio" name="options" autocomplete="off"> 
                            Incidental Exp. (Without Quotes)
                        </label>
                        <label class="btn btn-outline-primary" id="btn-tada" onclick="showForm('tada')">
                            <input type="radio" name="options" autocomplete="off"> 
                            TA/DA
                        </label>
                    </div>
                </div>
            </div>

            <!-- ======================================================= -->
            <!-- FORM 1: MAJOR PURCHASE -->
            <!-- ======================================================= -->
            <div id="form-major" class="purchase-form">
                <div class="card">
                    <div class="card-body">
                        <form action="#" method="POST">
                            @csrf
                            
                            <!-- ROW 1: Case Id | Date | Minute -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Case Id</label>
                                        <div class="col-9">
                                            <input type="text" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Date</label>
                                        <div class="col-9">
                                            <input type="date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Minute</label>
                                        <div class="col-9">
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ROW 2: Title (Full Width) -->
                            <div class="form-group row">
                                <label class="col-1 col-form-label" style="max-width: 80px;">Title</label> <!-- Fixed width for Title label -->
                                <div class="col" style="flex: 1;">
                                    <input type="text" class="form-control">
                                </div>
                            </div>

                            <!-- ROW 3: Head | For | Status -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Head</label>
                                        <div class="col-9">
                                            <select class="form-control">
                                                <option></option>
                                                <option>Option 1</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">For</label>
                                        <div class="col-9">
                                            <select class="form-control">
                                                <option></option>
                                                <option>Dept A</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Status</label>
                                        <div class="col-9 pt-1">
                                            <span style="font-weight: bold; font-size: 13px;">Draft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="row mt-3 justify-content-center">
                                <button type="submit" class="btn btn-custom-gray mr-3">Save</button>
                                <button type="button" class="btn btn-custom-gray">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ======================================================= -->
            <!-- FORM 2: INCIDENTAL EXPENSES -->
            <!-- ======================================================= -->
            <div id="form-incidental" class="purchase-form" style="display: none;">
                <div class="card">
                    <div class="card-body">
                        <form action="#" method="POST">
                            @csrf
                            
                            <!-- ROW 1 -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Case Id</label>
                                        <div class="col-9">
                                            <input type="text" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Date</label>
                                        <div class="col-9">
                                            <input type="date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Minute</label>
                                        <div class="col-9">
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ROW 2 -->
                            <div class="form-group row">
                                <label class="col-1 col-form-label" style="max-width: 80px;">Title</label>
                                <div class="col" style="flex: 1;">
                                    <input type="text" class="form-control">
                                </div>
                            </div>

                            <!-- ROW 3 -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Head</label>
                                        <div class="col-9">
                                            <select class="form-control">
                                                <option></option>
                                                <option>Incidental 1</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">For</label>
                                        <div class="col-9">
                                            <select class="form-control">
                                                <option></option>
                                                <option>Self</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Status</label>
                                        <div class="col-9 pt-1">
                                            <span style="font-weight: bold; font-size: 13px;">Draft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="row mt-3 justify-content-center">
                                <button type="submit" class="btn btn-custom-gray mr-3">Save</button>
                                <button type="button" class="btn btn-custom-gray">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ======================================================= -->
            <!-- FORM 3: TA/DA -->
            <!-- ======================================================= -->
            <div id="form-tada" class="purchase-form" style="display: none;">
                <div class="card">
                    <div class="card-body">
                        <form action="#" method="POST">
                            @csrf
                            
                            <!-- ROW 1 -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Case Id</label>
                                        <div class="col-9">
                                            <input type="text" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Date</label>
                                        <div class="col-9">
                                            <input type="date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Minute</label>
                                        <div class="col-9">
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ROW 2 -->
                            <div class="form-group row">
                                <label class="col-1 col-form-label" style="max-width: 80px;">Title</label>
                                <div class="col" style="flex: 1;">
                                    <input type="text" class="form-control">
                                </div>
                            </div>

                            <!-- ROW 3 -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Head</label>
                                        <div class="col-9">
                                            <select class="form-control">
                                                <option></option>
                                                <option>Travel</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">For</label>
                                        <div class="col-9">
                                            <select class="form-control">
                                                <option></option>
                                                <option>Officer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label align-right">Status</label>
                                        <div class="col-9 pt-1">
                                            <span style="font-weight: bold; font-size: 13px;">Draft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="row mt-3 justify-content-center">
                                <button type="submit" class="btn btn-custom-gray mr-3">Save</button>
                                <button type="button" class="btn btn-custom-gray">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- JavaScript to Handle Switching -->
<script>
    function showForm(type) {
        // 1. Hide all forms
        const forms = document.querySelectorAll('.purchase-form');
        forms.forEach(form => form.style.display = 'none');

        // 2. Show the selected form
        document.getElementById('form-' + type).style.display = 'block';

        // 3. Update Button Styles
        const buttons = ['btn-major', 'btn-incidental', 'btn-tada'];
        buttons.forEach(id => {
            document.getElementById(id).classList.remove('active');
        });
        document.getElementById('btn-' + type).classList.add('active');
    }
</script>

@endsection