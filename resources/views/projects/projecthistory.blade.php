@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        .card-history { border-top: 3px solid #007bff; }
        .table thead th {
            background-color: #f4f6f9; color: #495057; text-transform: uppercase;
            font-size: 0.85rem; letter-spacing: 0.5px; border-bottom-width: 2px;
        }
        .badge-month { font-size: 0.85rem; padding: 5px 10px; font-weight: 500; min-width: 80px; }
        
        /* Dynamic Colors for Actions */
        .act-initiation { color: #28a745; font-weight: 600; } /* Green */
        .act-execution { color: #17a2b8; font-weight: 600; }  /* Teal */
        .act-attachment { color: #007bff; font-weight: 600; } /* Blue */
        .act-milestone { color: #ffc107; font-weight: 600; }  /* Yellow */
        .act-update { color: #6c757d; font-weight: 600; }     /* Grey */

        .table-hover tbody tr:hover { background-color: rgba(0,123,255,.05); }
        .desc-text { font-size: 0.9rem; color: #555; }
        .prj-code-tag { font-size: 0.75rem; background: #eee; padding: 2px 6px; border-radius: 4px; margin-right: 5px; color: #333; }
    </style>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-history mr-2"></i>Global Project History</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-history shadow-sm">
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
                                            {{-- 1. Date Time --}}
                                            <td class="text-muted font-weight-light">
                                                <i class="far fa-clock mr-1 small"></i>
                                                {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}
                                            </td>

                                            {{-- 2. Month Badge --}}
                                            <td>
                                                <span class="badge badge-secondary badge-month">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('M Y') }}
                                                </span>
                                            </td>

                                            {{-- 3. Action Type with Icon --}}
                                            <td>
                                                @if($log->pja_action == 'Initiation')
                                                    <span class="act-initiation"><i class="fas fa-plus-circle mr-1"></i> Initiated</span>
                                                @elseif($log->pja_action == 'Execution')
                                                    <span class="act-execution"><i class="fas fa-cogs mr-1"></i> Execution</span>
                                                @elseif($log->pja_action == 'Attachment')
                                                    <span class="act-attachment"><i class="fas fa-paperclip mr-1"></i> Upload</span>
                                                @elseif($log->pja_action == 'Milestone')
                                                    <span class="act-milestone"><i class="fas fa-flag mr-1"></i> Milestone</span>
                                                @else
                                                    <span class="act-update"><i class="fas fa-edit mr-1"></i> {{ $log->pja_action }}</span>
                                                @endif
                                            </td>

                                            {{-- 4. Description --}}
                                            <td>
                                                <span class="prj-code-tag">{{ $log->prj_code }}</span>
                                                <span class="desc-text">{{ $log->pja_details }}</span>
                                            </td>

                                            {{-- 5. User --}}
                                            <td>
                                                <span class="text-dark font-weight-bold small">
                                                    <i class="fas fa-user-circle text-muted mr-1"></i> {{ $log->pja_user }}
                                                </span>
                                            </td>

                                            {{-- 6. Status Icon --}}
                                            <td class="text-center">
                                                <i class="fas fa-check-circle text-success" title="Logged Successfully"></i>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fas fa-history fa-3x mb-3 text-light"></i><br>
                                                No history records found. Start working on projects to see logs here.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card-footer clearfix">
                            <span class="text-muted small">Showing latest activities</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection