@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        .card-history { border-top: 3px solid #007bff; }
        .table thead th {
            background-color: #f4f6f9; color: #495057; text-transform: uppercase;
            font-size: 0.85rem; letter-spacing: 0.5px; border-bottom-width: 2px;
        }
        /* Styling specifically for MPR */
        .mpr-desc { font-size: 0.9rem; color: #333; line-height: 1.5; white-space: pre-wrap; }
        .mpr-date { font-weight: 700; color: #007bff; }
    </style>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    @if(isset($viewType) && $viewType == 'mpr_list')
                        <h1 class="m-0 text-dark">
                            <i class="fas fa-file-invoice mr-2"></i> MPR History: <span class="text-primary">{{ $project->prj_code }}</span>
                        </h1>
                    @else
                        <h1 class="m-0 text-dark"><i class="fas fa-history mr-2"></i>Global Project History</h1>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-history shadow-sm">
                        
                        {{-- CONDITION 1: SHOW MPR LIST --}}
                        @if(isset($viewType) && $viewType == 'mpr_list')
                            <div class="card-header">
                                <h3 class="card-title">Monthly Progress Reports ({{ $mprHistory->count() }})</h3>
                                <div class="card-tools">
                                    <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Project
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped m-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%">Report Date</th>
                                                <th style="width: 55%">Progress Description</th>
                                                <th style="width: 15%">Author</th>
                                                <th class="text-center" style="width: 15%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($mprHistory as $mpr)
                                            <tr>
                                                <td>
                                                    <span class="mpr-date">
                                                        {{ \Carbon\Carbon::parse($mpr->pgh_dtg)->format('d M, Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="mpr-desc">{{ Str::limit($mpr->pgh_progress, 150) }}</div>
                                                    @if(strlen($mpr->pgh_progress) > 150)
                                                        <small class="text-muted">Click to view full</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <i class="fas fa-user-circle text-muted mr-1"></i> {{ $mpr->pgh_author }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success px-3 py-2">Submitted</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-folder-open fa-3x mb-3 text-light"></i><br>
                                                    No MPRs found for this project.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        {{-- CONDITION 2: SHOW GLOBAL LOGS (Old Logic) --}}
                        @else
                            <div class="card-header">
                                <h3 class="card-title">Detailed Audit Trail & Logs</h3>
                                <div class="card-tools">
                                    <button class="btn btn-sm btn-outline-secondary ml-2" onclick="window.print()">
                                        <i class="fas fa-print mr-1"></i> Print
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped m-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 18%">Date & Time</th>
                                                <th style="width: 10%">Month</th>
                                                <th style="width: 15%">Action Type</th>
                                                <th style="width: 35%">Description / Details</th>
                                                <th style="width: 12%">User</th>
                                                <th class="text-center" style="width: 10%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activities as $log)
                                            <tr>
                                                <td class="text-muted font-weight-light">
                                                    <i class="far fa-clock mr-1 small"></i>
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary badge-month">
                                                        {{ \Carbon\Carbon::parse($log->created_at)->format('M Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-weight-bold text-dark">{{ $log->pja_action }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light border mr-1">{{ $log->prj_code }}</span>
                                                    {{ $log->pja_details }}
                                                </td>
                                                <td>{{ $log->pja_user }}</td>
                                                <td class="text-center"><i class="fas fa-check-circle text-success"></i></td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">No logs found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection