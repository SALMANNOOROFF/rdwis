@extends('welcome')

@section('content')
<div class="content-wrapper bg-light">
    <style>
        /* Arial 12pt Standard */
        body, .card-body, h3, h5, p, .btn {
            font-family: Arial, sans-serif !important;
        }
        
        .hub-title { color: #007bff; font-weight: 700; margin-bottom: 30px; }
        
        /* Interactive Cards */
        .menu-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-align: center;
            padding: 30px 20px;
            height: 100%;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0, 123, 255, 0.15);
            border-bottom: 4px solid #007bff;
        }
        
        .icon-box {
            width: 70px;
            height: 70px;
            background: #e7f1ff;
            color: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }
        
        .btn-hub {
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
            letter-spacing: 0.5px;
            padding: 8px 25px;
        }
    </style>

    <div class="container-fluid pt-5">
        <div class="text-center">
            <h3 class="hub-title"><i class="fas fa-boxes mr-2"></i> Purchase Items Management Hub</h3>
            <p class="text-muted">Select an action below to manage your inventory and demands.</p>
        </div>

        <div class="row mt-5 px-4">
            
            <!-- 1. Master Item Entry -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card menu-card">
                    <div class="icon-box"><i class="fas fa-database"></i></div>
                    <h5 class="font-weight-bold">Master Items</h5>
                    <p class="small text-muted">Register permanent items in the main database list.</p>
                    <a href="{{ route('items.master.create') }}" class="btn btn-primary btn-hub shadow-sm">Manage Master</a>
                </div>
            </div>

           

            <!-- 3. Bulk / Batch Entry -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card menu-card">
                    <div class="icon-box"><i class="fas fa-layer-group"></i></div>
                    <h5 class="font-weight-bold">Batch Entry</h5>
                    <p class="small text-muted">Add multiple items at once under a single group ID.</p>
                    <a href="{{ route('items.bulk.create') }}" class="btn btn-primary btn-hub shadow-sm">Create Batch</a>
                </div>
            </div>

            <!-- 4. Batch Records List -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card menu-card">
                    <div class="icon-box"><i class="fas fa-list-check"></i></div>
                    <h5 class="font-weight-bold">Batch List</h5>
                    <p class="small text-muted">View and manage all previously submitted batches.</p>
                    <a href="{{ route('items.batch.list') }}" class="btn btn-primary btn-hub shadow-sm">View History</a>
                </div>
            </div>

        </div>

        

    </div>
</div>
@endsection