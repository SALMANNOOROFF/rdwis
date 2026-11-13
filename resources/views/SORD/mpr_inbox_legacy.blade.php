@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        .inbox-card { border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; }
        .inbox-card > .card-header { background: #fff; border-bottom: 1px solid #eef0f3; }
        .inbox-table { font-size: 12.5px; }
        .inbox-table thead th { background: #f1f5f9; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; color: #475569; }
        .prog-cell { max-width: 420px; white-space: pre-wrap; font-size: 12px; color: #334155; }
    </style>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 10px;">
            <div>
                <h1 class="m-0 font-weight-bold text-primary" style="font-size: 1.3rem; font-family: 'Rajdhani', sans-serif; letter-spacing: 1px;">
                    <i class="fas fa-inbox mr-1"></i> {{ $title }}
                </h1>
                <small class="text-muted">
                    Route: {{ $route->describe() }} &middot; your stop: level {{ $myLevel }}
                    ({{ $route->desigForLevel($myLevel) ?: '—' }})
                </small>
            </div>
            <span class="badge badge-primary px-3 py-2" style="font-size: 12px;">{{ $rows->count() }} awaiting action</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small">{{ session('error') }}</div>
        @endif

        <div class="card inbox-card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 inbox-table">
                        <thead>
                            <tr>
                                <th style="width: 110px;">Cycle</th>
                                <th style="width: 110px;">Project</th>
                                <th>Progress</th>
                                <th style="width: 130px;">Status</th>
                                <th style="width: 120px;">From</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 90px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                @php
                                    $st = strtolower(trim((string) $r->pgh_status));
                                    $c = $st === 'under finalization' ? 'info' : ($st === 'under review' ? 'warning' : 'primary');
                                    $lastNote = optional(($trail[$r->pgh_id] ?? collect())->first())->pgt_remarks;
                                @endphp
                                <tr>
                                    <td class="font-weight-bold">{{ $r->pgh_group ?: '—' }}</td>
                                    <td>
                                        <strong>{{ $r->prj_code }}</strong>
                                        <div class="text-muted" style="font-size: 10.5px;">{{ $r->unt_namesh }}</div>
                                    </td>
                                    <td class="prog-cell">
                                        {{ \Illuminate\Support\Str::limit(trim((string) $r->pgh_progress), 220) }}
                                        @if(trim((string) $lastNote) !== '')
                                            <div class="text-muted mt-1" style="font-size: 11px;">
                                                <i class="fas fa-comment-dots mr-1"></i> {{ \Illuminate\Support\Str::limit($lastNote, 120) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-{{ $c }}">{{ $r->pgh_status }}</span></td>
                                    <td class="text-muted">
                                        {{ $r->pgh_author }}
                                        <div style="font-size: 10.5px;"><code>{{ $r->pgh_trail }}</code></div>
                                    </td>
                                    <td class="text-muted">{{ $r->pgh_dtg ? \Carbon\Carbon::parse($r->pgh_dtg)->format('d M, Y') : '—' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('mpr.view', $r->pgh_xprj_id) }}" class="btn btn-xs btn-outline-primary font-weight-bold px-2">
                                            <i class="fas fa-folder-open mr-1"></i> Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block" style="opacity: .3;"></i>
                                        Nothing is waiting at your stop.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
