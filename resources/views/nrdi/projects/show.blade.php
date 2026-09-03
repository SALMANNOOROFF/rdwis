@extends('welcome')

@section('content')


<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
            <h1 class="m-0 font-weight-bold text-dark" style="font-family:'Rajdhani',sans-serif; letter-spacing:1px;">PROJECT DETAILS</h1>
            <a href="{{ route('nrdi.projects.index') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between" style="gap: 12px;">
                        <div>
                            <div class="text-muted" style="font-size: 12px;">Project</div>
                            <div class="font-weight-bold" style="font-size: 16px;">
                                <span class="badge px-2 py-1 text-white mr-1" style="background-color: var(--rd-primary-600); font-size: 13px;">{{ $project->prj_code }}</span>
                                <span class="text-dark">{{ $project->prj_title }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted" style="font-size: 12px;">Status</div>
                            <span class="badge badge-success text-uppercase font-weight-bold px-2 py-1">{{ $project->prj_status }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Division</div>
                            <div class="font-weight-bold">{{ $project->unit?->unt_name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Sponsor</div>
                            <div class="font-weight-bold">{{ $project->prj_sponsor }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Budget</div>
                            <div class="font-weight-bold text-success">Rs. {{ number_format((float) ($project->prj_propcost ?? 0)) }}</div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Start</div>
                            <div class="font-weight-bold">{{ $project->prj_startdt ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">EDC</div>
                            <div class="font-weight-bold">{{ $project->prj_estenddt ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Spent / Balance</div>
                            <div class="font-weight-bold">
                                Rs. {{ number_format((float) $totalSpent) }}
                                <span class="text-muted">/</span>
                                Rs. {{ number_format((float) $balance) }}
                                <span class="text-muted font-weight-normal">({{ $spentPercentage }}%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="text-muted" style="font-size: 12px;">Scope</div>
                            <div style="white-space: pre-wrap;" class="text-dark">{{ $project->prj_scope ?? 'No scope defined.' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ASSIGNED TEAM / CURRENT PERSONNEL --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <h3 class="card-title font-weight-bold mb-0 text-dark" style="font-size: 14px;">
                        <i class="fas fa-users text-primary mr-1"></i> Current Assigned Team
                    </h3>
                    <span class="badge badge-pill badge-primary font-weight-bold px-2.5 py-1" style="font-size: 11px;">
                        {{ $team->count() }} {{ $team->count() === 1 ? 'Member' : 'Members' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($team->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 text-nowrap">
                                <thead class="bg-light text-muted" style="font-size: 12px;">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>Name</th>
                                        <th>CNIC</th>
                                        <th>Email / Contact</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($team as $idx => $emp)
                                        <tr>
                                            <td class="text-center align-middle font-weight-bold text-muted" style="font-size: 12px;">
                                                {{ $idx + 1 }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                                    <i class="fas fa-user-tie text-info mr-1"></i> {{ $emp->emp_name }}
                                                </div>
                                            </td>
                                            <td class="align-middle text-muted" style="font-size: 12px;">
                                                {{ $emp->emp_cnic ?? '-' }}
                                            </td>
                                            <td class="align-middle text-muted" style="font-size: 12px;">
                                                @if($emp->emp_email)
                                                    <span class="mr-2"><i class="fas fa-envelope mr-1 text-secondary"></i>{{ $emp->emp_email }}</span>
                                                @endif
                                                @if($emp->emp_mobile)
                                                    <span><i class="fas fa-phone mr-1 text-secondary"></i>{{ $emp->emp_mobile }}</span>
                                                @endif
                                                @if(!$emp->emp_email && !$emp->emp_mobile)
                                                    -
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-success px-2 py-0.5" style="font-size: 11px;">Active</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted" style="font-size: 13px;">
                            <i class="fas fa-user-slash text-muted mr-1"></i> No active employees currently assigned to this project.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-2">
                    <h3 class="card-title font-weight-bold mb-0 text-dark" style="font-size: 14px;">
                        <i class="fas fa-paperclip text-secondary mr-1"></i> Attachments
                    </h3>
                </div>
                <div class="card-body">
                    @if(($project->attachments?->count() ?? 0) > 0)
                        <ul class="mb-0">
                            @foreach($project->attachments as $a)
                                <li style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $a->jat_path }}">
                                    @if($a->jat_path)
                                        <a href="{{ \App\Facades\FileStorage::url($a->jat_path) }}" target="_blank" class="text-primary font-weight-500">
                                            <i class="fas fa-file-pdf mr-1 text-danger"></i> {{ $a->jat_type }} — {{ basename(str_replace('\\', '/', $a->jat_path)) }}
                                        </a>
                                    @else
                                        {{ $a->jat_type }} — <span class="text-muted">No file</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted" style="font-size: 13px;">No attachments uploaded for this project.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
