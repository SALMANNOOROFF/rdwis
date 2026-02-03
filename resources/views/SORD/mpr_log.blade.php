@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i> MPR Activity Log
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('sord.all_projects') }}" class="btn btn-secondary btn-sm shadow-sm font-weight-bold">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Projects
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped text-nowrap mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th>Date & Time</th>
                                <th>Project</th>
                                <th>Division</th>
                                <th>Action</th>
                                <th>Remarks</th>
                                <th>Performed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="small text-muted align-middle">
                                    {{ $log->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-primary">{{ $log->document->project->prj_code ?? 'N/A' }}</span>
                                    <br>
                                    <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                        {{ $log->document->project->prj_title ?? '' }}
                                    </small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-light border">{{ $log->fromUser->unit->unt_name ?? 'N/A' }}</span>
                                </td>
                                <td class="align-middle">
                                    @if($log->action_type == 'Returned')
                                        <span class="badge badge-danger px-2">Returned</span>
                                    @elseif($log->action_type == 'Forwarded')
                                        <span class="badge badge-info px-2">Forwarded</span>
                                    @elseif($log->action_type == 'Finalized')
                                        <span class="badge badge-success px-2">Finalized</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $log->action_type }}</span>
                                    @endif
                                </td>
                                <td class="align-middle text-wrap" style="max-width: 300px; min-width: 200px;">
                                    {{ Str::limit($log->notes, 100) }}
                                </td>
                                <td class="align-middle small">
                                    @if($log->fromUser)
                                        <div class="font-weight-bold text-dark">{{ $log->fromUser->role->rol_desig ?? 'Unknown Role' }}</div>
                                        <div class="text-muted text-xs">{{ $log->fromUser->acc_username }}</div>
                                    @else
                                        <i class="fas fa-robot text-muted mr-1"></i> System
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-3x mb-3 d-block text-gray-300"></i>
                                    No activity logs found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
