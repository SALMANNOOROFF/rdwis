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
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between" style="gap: 12px;">
                        <div>
                            <div class="text-muted" style="font-size: 12px;">Project</div>
                            <div class="font-weight-bold" style="font-size: 16px;">
                                {{ $project->prj_code }} <span class="text-muted">—</span> {{ $project->prj_title }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted" style="font-size: 12px;">Status</div>
                            <div class="font-weight-bold">{{ $project->prj_status }}</div>
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
                            <div class="font-weight-bold">{{ number_format((float) ($project->prj_propcost ?? 0)) }}</div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Start</div>
                            <div class="font-weight-bold">{{ $project->prj_startdt }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">EDC</div>
                            <div class="font-weight-bold">{{ $project->prj_estenddt }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted" style="font-size: 12px;">Spent / Balance</div>
                            <div class="font-weight-bold">
                                {{ number_format((float) $totalSpent) }}
                                <span class="text-muted">/</span>
                                {{ number_format((float) $balance) }}
                                <span class="text-muted">({{ $spentPercentage }}%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="text-muted" style="font-size: 12px;">Scope</div>
                            <div style="white-space: pre-wrap;">{{ $project->prj_scope }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold mb-0">Attachments</h3>
                </div>
                <div class="card-body">
                    @if(($project->attachments?->count() ?? 0) > 0)
                        <ul class="mb-0">
                            @foreach($project->attachments as $a)
                                <li style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $a->jat_path }}">
                                    {{ $a->jat_type }} — {{ $a->jat_path }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted">No attachments.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
