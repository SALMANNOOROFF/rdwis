@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        .mpr-card { border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; }
        .mpr-card > .card-header { background: #fff; border-bottom: 1px solid #eef0f3; }
        .mpr-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
        .mpr-scroll { max-height: 420px; overflow-y: auto; padding-right: 6px; }
        .trail-item { border-left: 3px solid #cbd5e1; background: #f8fafc; border-radius: 0 6px 6px 0; padding: 7px 10px; margin-bottom: 7px; }
        .trail-item.forward { border-left-color: #16a34a; }
        .trail-item.return  { border-left-color: #f59e0b; }
        .trail-item.final   { border-left-color: #2563eb; }
        .trail-item.cancel  { border-left-color: #dc2626; }
        .trail-meta { font-size: 10.5px; color: #64748b; text-transform: uppercase; letter-spacing: .3px; font-weight: 700; }
        .trail-move { font-size: 12.5px; font-weight: 700; color: #1e293b; }
        .trail-note { font-size: 12px; color: #334155; white-space: pre-wrap; margin-top: 2px; }
        .hist-item { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 7px; background: #fff; }
        .hist-text { font-size: 12px; color: #334155; white-space: pre-wrap; line-height: 1.5; }
    </style>

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 10px;">
            <div>
                <h1 class="m-0 font-weight-bold text-primary" style="font-size: 1.3rem; font-family: 'Rajdhani', sans-serif; letter-spacing: 1px;">
                    <i class="fas fa-file-signature mr-1"></i> MONTHLY PROGRESS REPORT
                </h1>
                <small class="text-muted">
                    <strong>{{ $project->prj_code }}</strong> &mdash; {{ $project->prj_title }}
                    &middot; Route: {{ $routeService->describe() }}
                </small>
            </div>
            <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left mr-1"></i> Back to project
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small"><ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            {{-- ================= LEFT: CURRENT MPR ================= --}}
            <div class="col-lg-7 mb-3">
                <div class="card mpr-card shadow-sm">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                        <h3 class="card-title font-weight-bold m-0" style="font-size: 14px;">
                            @if($current)
                                <span class="badge badge-dark mr-1">{{ $current->pgh_group }}</span>
                                @php
                                    $st = strtolower(trim((string) $current->pgh_status));
                                    $stColor = $st === 'finalized' ? 'success'
                                            : ($st === 'cancelled' ? 'secondary'
                                            : ($st === 'under review' ? 'warning'
                                            : ($st === 'under finalization' ? 'info' : 'primary')));
                                @endphp
                                <span class="badge badge-{{ $stColor }}">{{ $current->pgh_status }}</span>
                                <span class="text-muted font-weight-normal ml-1" style="font-size: 12px;">
                                    with {{ $routeService->desigForLevel((int) $current->pgh_level) ?: ('level ' . $current->pgh_level) }}
                                </span>
                            @else
                                <i class="fas fa-plus-circle text-primary mr-1"></i> No MPR in progress
                            @endif
                        </h3>
                        <small class="text-muted">Current cycle: <strong>{{ $group }}</strong></small>
                    </div>

                    <form method="POST" action="{{ route('mpr.action', $project->prj_id) }}">
                        @csrf
                        <input type="hidden" name="action" id="mprAction" value="save">

                        <div class="card-body py-3">
                            @if($current)
                                <div class="mpr-label">Progress</div>
                                <textarea name="pgh_progress" rows="14" class="form-control"
                                          style="font-size: 13px; line-height: 1.6;"
                                          {{ $rights['save'] || $rights['forward'] || $rights['finalize'] ? '' : 'readonly' }}
                                          placeholder="1. ...">{{ old('pgh_progress', $current->pgh_progress) }}</textarea>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    Author: <strong>{{ $current->pgh_author }}</strong>
                                    &middot; Trail: <code>{{ $current->pgh_trail }}</code>
                                    &middot; Entry: {{ $current->pgh_dtg ? \Carbon\Carbon::parse($current->pgh_dtg)->format('d M, Y H:i') : '—' }}
                                </small>

                                <div class="mpr-label mt-3">Remarks / note for this action</div>
                                <textarea name="remarks" rows="2" class="form-control" style="font-size: 12.5px;"
                                          placeholder="Kept with the movement trail (who sent / returned, and why)">{{ old('remarks') }}</textarea>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-file-alt fa-2x mb-2 d-block" style="opacity: .3;"></i>
                                    @if($rights['create'])
                                        No MPR is open for this project. Start the <strong>{{ $group }}</strong> report below &mdash;
                                        it opens with the project's current remarks, exactly like the old system.
                                    @else
                                        No MPR is open for this project.
                                        @if(! $rights['on_route'])
                                            Your seat is not on the MPR route ({{ $routeService->describe() }}).
                                        @endif
                                    @endif
                                </div>
                                <div class="mpr-label">Remarks / note</div>
                                <textarea name="remarks" rows="2" class="form-control" style="font-size: 12.5px;">{{ old('remarks') }}</textarea>
                            @endif
                        </div>

                        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap bg-white py-2" style="gap: 8px;">
                            <small class="text-muted">
                                @if($current && $rights['at_my_level'])
                                    <i class="fas fa-user-check text-success mr-1"></i> This MPR is on your desk.
                                @elseif($current)
                                    <i class="fas fa-lock mr-1"></i> Currently with
                                    {{ $routeService->desigForLevel((int) $current->pgh_level) ?: ('level ' . $current->pgh_level) }}.
                                @endif
                            </small>
                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                @if($rights['create'])
                                    <button type="submit" class="btn btn-sm btn-primary px-4 font-weight-bold" onclick="setAct('create')">
                                        <i class="fas fa-plus mr-1"></i> Create MPR ({{ $group }})
                                    </button>
                                @endif
                                @if($rights['save'])
                                    <button type="submit" class="btn btn-sm btn-secondary px-3 font-weight-bold" onclick="setAct('save')">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                @endif
                                @if($rights['edit'])
                                    <button type="submit" class="btn btn-sm btn-outline-primary px-3 font-weight-bold" onclick="setAct('edit')">
                                        <i class="fas fa-pen mr-1"></i> Edit (new revision)
                                    </button>
                                @endif
                                @if($rights['cancel'])
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 font-weight-bold" onclick="return setAct('cancel', 'Cancel this progress?')">
                                        <i class="fas fa-ban mr-1"></i> Cancel
                                    </button>
                                @endif
                                @if($rights['return'])
                                    <button type="submit" class="btn btn-sm btn-warning text-white px-3 font-weight-bold" onclick="return setAct('return', 'Return this progress to the previous stop?')">
                                        <i class="fas fa-undo mr-1"></i> Return
                                    </button>
                                @endif
                                @if($rights['forward'])
                                    <button type="submit" class="btn btn-sm btn-success px-4 font-weight-bold" onclick="return setAct('forward', 'Forward this progress?')">
                                        <i class="fas fa-paper-plane mr-1"></i> Forward
                                    </button>
                                @endif
                                @if($rights['finalize'])
                                    <button type="submit" class="btn btn-sm btn-primary px-4 font-weight-bold" onclick="return setAct('finalize', 'Finalize this progress? It will also update the project remarks.')">
                                        <i class="fas fa-check-double mr-1"></i> Finalize
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================= RIGHT: TRAIL + PREVIOUS ================= --}}
            <div class="col-lg-5">
                {{-- MOVEMENT TRAIL --}}
                <div class="card mpr-card shadow-sm mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold m-0" style="font-size: 13.5px;">
                            <i class="fas fa-route text-primary mr-1"></i> Movement Trail
                        </h3>
                        <span class="badge badge-light border">{{ $trail->count() }}</span>
                    </div>
                    <div class="card-body py-2 mpr-scroll">
                        @forelse($trail as $t)
                            @php
                                $a = strtolower((string) $t->pgt_action);
                                $cls = str_contains($a, 'forward') ? 'forward'
                                     : (str_contains($a, 'return') ? 'return'
                                     : (str_contains($a, 'final') ? 'final'
                                     : (str_contains($a, 'cancel') ? 'cancel' : '')));
                            @endphp
                            <div class="trail-item {{ $cls }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="trail-move">
                                        {{ $t->pgt_action }}
                                        @if($t->pgt_from_desig || $t->pgt_to_desig)
                                            <span class="text-muted font-weight-normal">
                                                {{ $t->pgt_from_desig ?: '—' }} &rarr; {{ $t->pgt_to_desig ?: '—' }}
                                            </span>
                                        @endif
                                    </span>
                                    <span class="trail-meta">{{ $t->pgt_dtg ? \Carbon\Carbon::parse($t->pgt_dtg)->format('d M y H:i') : '' }}</span>
                                </div>
                                <div class="trail-meta">
                                    {{ $t->pgt_group }}
                                    @if($t->pgt_status_after) &middot; {{ $t->pgt_status_after }} @endif
                                </div>
                                @if(trim((string) $t->pgt_remarks) !== '')
                                    <div class="trail-note">{{ $t->pgt_remarks }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No movements recorded yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- PREVIOUS MPRs --}}
                <div class="card mpr-card shadow-sm">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold m-0" style="font-size: 13.5px;">
                            <i class="fas fa-layer-group text-success mr-1"></i> Previous MPRs
                        </h3>
                        <span class="badge badge-light border">{{ $history->count() }}</span>
                    </div>
                    <div class="card-body py-2 mpr-scroll">
                        @forelse($history as $h)
                            @php
                                $hs = strtolower(trim((string) $h->pgh_status));
                                $hc = $hs === 'finalized' ? 'success'
                                    : ($hs === 'cancelled' ? 'secondary'
                                    : ($hs === 'edited' ? 'light' : 'info'));
                            @endphp
                            <div class="hist-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $h->pgh_group ?: '—' }}</strong>
                                        <span class="badge badge-{{ $hc }} ml-1">{{ $h->pgh_status }}</span>
                                    </div>
                                    <small class="text-muted">{{ $h->pgh_dtg ? \Carbon\Carbon::parse($h->pgh_dtg)->format('d M, Y') : '' }}</small>
                                </div>
                                <small class="text-muted d-block">
                                    {{ $h->pgh_author }}
                                    &middot; level {{ $h->pgh_level }}
                                    @if($h->pgh_trail) &middot; <code>{{ $h->pgh_trail }}</code> @endif
                                </small>
                                @if(trim((string) $h->pgh_progress) !== '')
                                    <a href="javascript:void(0)" class="small font-weight-bold text-primary" onclick="toggleHist('h{{ $h->pgh_id }}')">
                                        <i class="fas fa-eye mr-1"></i> Show / hide
                                    </a>
                                    <div id="h{{ $h->pgh_id }}" class="hist-text mt-1" style="display: none;">{{ $h->pgh_progress }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No earlier MPR for this project.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setAct(action, confirmText) {
    if (confirmText && !confirm(confirmText)) { return false; }
    document.getElementById('mprAction').value = action;
    return true;
}
function toggleHist(id) {
    var el = document.getElementById(id);
    if (el) { el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none'; }
}
</script>
@endsection
