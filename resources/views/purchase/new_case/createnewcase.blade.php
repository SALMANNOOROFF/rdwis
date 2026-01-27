@extends('welcome')

@section('content')

<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --bg-light: #f8f9fc;
    }

    .content-wrapper { background-color: var(--bg-light); padding: 20px; }

    /* Modern Card Styling */
    .custom-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        background: #fff;
        margin-bottom: 2rem;
    }

    .card-header-custom {
        padding: 1.25rem;
        border-bottom: 1px solid #e3e6f0;
        font-weight: bold;
        color: var(--primary-color);
        display: flex;
        align-items: center;
    }

    /* Segmented Switcher */
    .switcher-container {
        background: #eaecf4;
        padding: 5px;
        border-radius: 10px;
        display: inline-flex;
        margin-bottom: 25px;
    }

    .switcher-btn {
        padding: 8px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        border: none;
        color: var(--secondary-color);
        background: transparent;
    }

    .switcher-btn.active {
        background: #fff;
        color: var(--primary-color);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .form-label { font-weight: 600; font-size: 0.85rem; color: #4a4a4a; margin-bottom: 0.4rem; display: block; }

    .form-control-custom {
        border-radius: 8px;
        border: 1px solid #d1d3e2;
        padding: 0.6rem 0.75rem;
        font-size: 0.9rem;
        width: 100%;
        height: 40px;
    }

    .badge-draft {
        background-color: #fff3cd; color: #856404; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: bold; border: 1px solid #ffeeba;
    }

    .btn-save { background-color: var(--success-color); border: none; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; }
    .btn-cancel { background-color: #fff; border: 1px solid #d1d3e2; color: var(--secondary-color); padding: 10px 30px; border-radius: 8px; font-weight: 600; }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Purchase Management</h1>
        </div>

        <!-- Switcher -->
        <div class="text-center">
            <div class="switcher-container">
                <button class="switcher-btn active" id="btn-major" onclick="showForm('major')">Major Purchase</button>
                <button class="switcher-btn" id="btn-incidental" onclick="showForm('incidental')">Incidental Exp.</button>
                <button class="switcher-btn" id="btn-tada" onclick="showForm('tada')">TA / DA</button>
            </div>
        </div>

        <!-- FORM CONTAINER -->
        <div class="custom-card">
            <div class="card-header-custom">
                <i class="fas fa-file-invoice mr-2"></i> 
                <span id="form-title">New Major Purchase Case</span>
            </div>
            
            <div class="card-body p-4">
                {{-- Session Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <!-- MAJOR PURCHASE FORM -->
                <div id="form-major" class="purchase-form">
                    <form action="{{ route('purchase.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Case ID</label>
                                <input type="text" class="form-control-custom bg-light" value="{{ $nextId }}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="pcs_date" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Minute #</label>
                                <input type="number" name="pcs_minute" id="minute_number_input" class="form-control-custom" placeholder="Enter number" required>
                                <small id="status-hint" class="text-muted"></small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Current Status</label>
                                <div class="mt-2"><span class="badge-draft">DRAFT</span></div>
                            </div>
                        </div>

                        <!-- Title Row -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Case Title / Subject</label>
                                <input type="text" name="pcs_title" class="form-control-custom" placeholder="Enter Case Title" required>
                            </div>
                        </div>

                        <div class="row">
<!-- 1. Project Head Select -->
<div class="col-md-6 mb-3">
    <label class="form-label">Project Head</label>
    <select class="form-control-custom" name="pcs_hed_id" id="head_select_box" required>
        <option value="" selected disabled>Select Project Head</option>
        @foreach($heads as $head)
            <option value="{{ $head->hed_id }}">
                {{ $head->hed_code }} - {{ $head->hed_name }}
            </option>
        @endforeach
    </select>
</div>

<!-- 2. "For" Select (Now mirrors the Project Head) -->
<div class="col-md-6 mb-3">
    <label class="form-label">For (Budget Head)</label>
    <select class="form-control-custom" name="pcs_unt_id" id="unit_select_box" required>
        <option value="" selected disabled>Select Budget Head</option>
        @foreach($heads as $head)
            <option value="{{ $head->hed_id }}">
                {{ $head->hed_code }} - {{ $head->hed_name }}
            </option>
        @endforeach
    </select>
</div>

<!-- jQuery to sync selects -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#head_select_box').on('change', function() {
            var selectedHead = $(this).val();

            // Set the "For" select to the same value
            $('#unit_select_box').val(selectedHead);

            // If using a custom select plugin like select2, trigger update
            $('#unit_select_box').trigger('change');
        });
    });
</script>


</div>

                        <div class="text-right mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-cancel mr-2" onclick="history.back();">Cancel</button>
                            <button type="submit" class="btn btn-save">Create Case</button>
                        </div>
                    </form>
                </div>

                <!-- (Incidental & TADA forms kept as placeholders with same style) -->
                <div id="form-incidental" class="purchase-form" style="display: none;">
                    <p class="text-muted text-center py-5">Incidental Expenses Form logic goes here.</p>
                </div>

                <div id="form-tada" class="purchase-form" style="display: none;">
                    <p class="text-muted text-center py-5">TA / DA Form logic goes here.</p>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="dist/pages/jquery.js"></script>
<script>
    function showForm(type) {
        $('.purchase-form').hide();
        $('#form-' + type).show();
        $('.switcher-btn').removeClass('active');
        $('#btn-' + type).addClass('active');
        
        const titles = {
            'major': 'New Major Purchase Case (With Quotes)',
            'incidental': 'Incidental Expenses (Without Quotes)',
            'tada': 'TA / DA Claim Entry'
        };
        $('#form-title').text(titles[type]);
    }

    $(document).ready(function() {
        // AJAX: Fetch Minute Number based on Project Head
        $('#head_select_box').on('change', function() {
            var headId = $(this).val();
            var $input = $('#minute_number_input');
            var $hint = $('#status-hint');

            if(headId) {
                $hint.html('<i class="fas fa-sync fa-spin"></i> Fetching...');
                $.ajax({
                    url: '/get-next-minute/' + headId,
                    type: "GET",
                    success: function(data) {
                        $input.val(data.next_minute);
                        $hint.html('Last: <b>' + data.last_minute + '</b>. Sug.: <b>' + data.next_minute + '</b>.');
                        $input.css('border-color', '#28a745');
                        setTimeout(() => { $input.css('border-color', ''); }, 1000);
                    },
                    error: function() { $hint.html('<span class="text-danger">Error.</span>'); }
                });
            }
        });

        // Optional: Auto-sync Unit if same as Head logic (if applicable)
        // Note: Only if your Business logic allows it.
    });
</script>

@endsection