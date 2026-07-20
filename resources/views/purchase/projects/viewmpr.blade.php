@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        /* Main Theme Colors */
        :root {
            --admin-blue: #007bff;
            --admin-dark: #343a40;
            --light-bg: #f4f6f9;
        }

        .mpr-detail-card {
            background: #fff;
            margin: 15px;
            border-top: 4px solid var(--admin-blue);
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }

        /* Top Header Section */
        .mpr-main-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mpr-title-text { color: var(--admin-dark); font-weight: 700; margin: 0; }

        /* Summary Boxes Layout */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 15px;
            background: #fff;
        }

        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            min-height: 100px;
        }

        .summary-box strong { color: var(--admin-blue); display: block; margin-bottom: 5px; border-bottom: 1px solid #eee; }
        .data-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .data-label { color: #666; font-weight: 500; }

        /* Milestones Table Section */
        .milestone-section { padding: 0 15px; }
        .section-label { 
            background: var(--admin-dark); 
            color: #fff; 
            padding: 5px 15px; 
            font-size: 0.9rem; 
            font-weight: 600;
            display: inline-block;
            border-radius: 4px 4px 0 0;
        }

        .milestone-table { width: 100%; border-collapse: collapse; border: 1px solid #ddd; font-size: 0.85rem; }
        .milestone-table th { background: #e9ecef; padding: 8px; border: 1px solid #ddd; text-align: left; }
        .milestone-table td { padding: 8px; border: 1px solid #ddd; }
        .milestone-table tr:nth-child(even) { background: #f9f9f9; }

        /* Lower Grid (Progress & Notes) */
        .lower-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 250px;
            gap: 15px;
            padding: 15px;
        }

        .content-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            height: 300px;
            display: flex;
            flex-direction: column;
        }

        .box-header {
            background: #f4f6f9;
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
        }

        .box-body { padding: 10px; overflow-y: auto; font-size: 0.88rem; line-height: 1.5; color: #444; flex-grow: 1; }

        /* Buttons Styling */
        .btn-blue-sm { background: var(--admin-blue); color: #fff; border: none; padding: 4px 12px; border-radius: 3px; font-size: 0.8rem; }
        .btn-blue-sm:hover { background: #0056b3; color: #fff; }

        .btn-group-top .btn { margin-left: 5px; font-weight: 600; font-size: 0.8rem; }
    </style>

    <div class="content-wrapper pt-3">
    <section class="content">
        <div class="container-fluid">

            {{-- Back Button --}}
            <a href="{{ route('mpr.list') }}" class="btn btn-sm btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Project List
            </a>

            {{-- SECTION 1: PREPARE NEW MPR (FORM) --}}
            <div class="card card-success card-outline shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-edit mr-2"></i> Prepare MPR: {{ $project->prj_title }}
                    </h3>
                </div>
                
                <form action="{{ route('mpr.store', $project->prj_id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Report Date</label>
                                <input type="date" name="pgh_dt" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Physical Progress (%)</label>
                                <input type="number" name="pgh_percent" class="form-control" min="0" max="100" placeholder="0-100" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Report Headline</label>
                                <input type="text" name="pgh_intro" class="form-control" placeholder="e.g. Structure Completed" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Progress Details</label>
                            <textarea name="pgh_progress" class="form-control" rows="4" placeholder="Describe work done this month..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Issues / Bottlenecks</label>
                            <textarea name="pgh_issues" class="form-control" rows="2" placeholder="Any problems faced..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save mr-1"></i> Save Report
                        </button>
                    </div>
                </form>
            </div>

            {{-- SECTION 2: LAST SUBMITTED REPORT (READ ONLY) --}}
            @if($lastMpr)
            <div class="card card-info card-outline shadow">
                <div class="card-header">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-history mr-2"></i> Last Report ({{ \Carbon\Carbon::parse($lastMpr->pgh_dt)->format('d M, Y') }})
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-warning" style="font-size: 0.9rem;">{{ $lastMpr->pgh_percent }}% Achieved</span>
                    </div>
                </div>
                <div class="card-body" style="background-color: #f9f9f9;">
                    <h5 class="text-primary">{{ $lastMpr->pgh_intro }}</h5>
                    <hr>
                    <strong>Work Done:</strong>
                    <p>{{ $lastMpr->pgh_progress }}</p>
                    
                    @if($lastMpr->pgh_issues)
                    <strong class="text-danger">Issues:</strong>
                    <p class="text-danger">{{ $lastMpr->pgh_issues }}</p>
                    @endif
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> No previous reports found for this project.
            </div>
            @endif

        </div>
    </section>
</div>
</div>
@endsection